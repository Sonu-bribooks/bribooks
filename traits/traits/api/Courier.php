<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Courier {
	public function getCouriers() {
		$this->form_validation->set_rules('address_id', _l('address_id'), [
			'trim',
			'required',
			'numeric',
			['address', [$this->validate_model, 'address']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$address_info = $this->address_model->get($this->input->post('address_id'));
			self::_formatCouriers($address_info['zipcode'], $address_info['country']);
		}
	}

	public function setShippingCourier() {
		$this->form_validation->set_rules('address_id', _l('address_id'), [
			'trim',
			'required',
			'numeric',
			['address', [$this->validate_model, 'address']]
		]);
		$this->form_validation->set_rules('courier_id', _l('courier_id'), [
			'trim',
			'required',
			'numeric',
			['courier', [$this->validate_model, 'courier']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$courier_id = (int)$this->input->post('courier_id');

			if (empty($this->session->userdata('couriers')['data'])) {
				$this->json['couriers']['data'] = [];
				return;
			}

			$results = array_filter($this->session->userdata('couriers')['data'] ?? [], function($item) use($courier_id) {
				return $courier_id == $item['id'];
			});

			if (empty($results)) {
				$this->json['couriers']['data'] = [];
				return;
			}

			$this->session->set_userdata([
				'shipping_address_id'	=> (int)$this->input->post('address_id'),
				'shipping_courier_id'	=> $courier_id,
				'shipping_info'			=> array_shift($results),
			]);

			$this->session->set_tempdata('order_site_id', $this->config->item('site_id'), 3600);

			self::_getCart();

			$this->json['success'] = _l('shipping_courier_saved');
		}
	}

	private function _formatCouriers($zipcode = '', $country = 'india') {
		if (strtolower($country) === 'india') {
			self::_formatDomesticCouriers($zipcode, $country);
		} else {
			self::_formatInternationalCouriers($zipcode, $country);
		}

		$this->session->set_userdata('couriers', $this->json['couriers'] ?? []);
	}

	private function _formatDomesticCouriers($zipcode = '', $country = 'india') {
		$cart_total = $this->cart_lib->getTotal();

		if (empty($cart_total)) return;

		// BriBooksShipping india
		$this->load->library('BriBooksShipping_lib');

		$couriers = [];

		if (get_settings('bb_shipping')) {
			// $bribooks_rate_zone_wise = $this->bribooksshipping_lib->getRateZoneWise([
			// 	'pickup_postcode'	=> $this->config->item('pickup_zipcode'),
			// 	'delivery_postcode'	=> $zipcode,
			// 	'cod'				=> 0,
			// 	'weight'			=> $cart_total['weight'],
			// 	'country'			=> $country,
			// ])[0] ?? [];
			//
			// log_kb(['bribooks_rate_zone_wise' => $bribooks_rate_zone_wise]);
			//
			// if (!empty($bribooks_rate_zone_wise)) {
			// 	$bribooks_rate_zone_wise['rate'] = self::_formatRate($bribooks_rate_zone_wise['rate']);
			// 	$couriers[] = $bribooks_rate_zone_wise;
			// }

			if (empty($couriers)) {
				$results = self::_getCouriers([
					'pickup_postcode'	=> $this->config->item('pickup_zipcode'),
					'delivery_postcode'	=> $zipcode,
					'cod'				=> 0,
					'weight'			=> round($cart_total['weight'] / 1000, 2),
				]);

				if (!empty($results['data']['available_courier_companies'][0])) {
					$shiprocket_courier = $results['data']['available_courier_companies'][0];
					$shiprocket_courier['rate'] = self::_formatRate($shiprocket_courier['rate']);
					$shiprocket_courier['courier_name'] = _l('delivery_charges');
					$couriers[] = $shiprocket_courier;
				}
			}
		} else {
			$results = self::_getCouriers([
				'pickup_postcode'	=> $this->config->item('pickup_zipcode'),
				'delivery_postcode'	=> $zipcode,
				'cod'				=> 0,
				'weight'			=> round($cart_total['weight'] / 1000, 2),
			]);

			foreach ($results['data']['available_courier_companies'] ?? [] as &$courier) {
				$courier['rate'] = self::_formatRate($courier['rate']);
				$couriers[] = $courier;
			}
		}

		$bribooks_rate = [];

		if (empty($couriers)) {
			$bribooks_rate = $this->bribooksshipping_lib->getRate(
				$country,
				round($cart_total['weight'], 2)
			);

			log_kb(['bribooks_rate' => $bribooks_rate]);
		}

		$couriers_data = array_slice($couriers, 0, 3);

		if (!empty($bribooks_rate)) {
			$bribooks_rate['rate'] = self::_formatRate($bribooks_rate['rate']);
			$couriers_data = array_merge([$bribooks_rate], $couriers_data);
		}

		$this->json['couriers'] = [
			'data'				=> $couriers_data,
			'recommended'		=> $couriers_data[0]['id'] ?? 0,
		];
	}

	private function _formatInternationalCouriers($zipcode = '', $country = 'india') {
		$cart_total = $this->cart_lib->getTotal();

		if (empty($cart_total)) return;

		$total_without_shipping_cost = $cart_total['total'] - $cart_total['shipping_cost'];

		$country_info = $this->db->get_where('delivery_country', [
			'name'	=> $country
		])->row_array();

		if (empty($country_info['status'])) return [];

		// Global orders
		$user_country_code = $this->input->cookie('user_country_code');

		if (empty($user_country_code)) {
			$user_country_code = strtolower($this->config->item('site_country_code'));
		}

		$cart_items = $this->cart_lib->getItems();

		foreach ($cart_items as $item) {
			$author_info = $this->user_model->get($item['book']['user_id']);

			if (empty($author_info)) {
				$this->json['couriers']['data'] = [];
				return;
			}

			if (strtolower($this->config->item('site_country_code')) === 'in'
				&& strtolower(get_author_currency_code($author_info['id'])) !== 'inr'
			) {
				$this->json['couriers']['data'] = [];
				return;
			}
		}

		$ip_country_info = self::getCountry(true);

		if (
			strtoupper($country_info['country_code']) === strtoupper($ip_country_info['country_code']) &&
			!empty($country_info['free_shipping'])
		) {
			$this->json['couriers'] = [
				'data'				=> [[
					'id'  			=> 9876543210,
					'courier_name' 	=> _li('Free Delivery'),
					'rate' 			=> 0
				]],
				'recommended'		=> 9876543210
			];
			return;
		}

		if ($country_info) {
			$couriers = [];

			if (get_settings('bb_shipping')) {
				// get rate from aramex
				if (in_array($country_info['country_code'], SHIPPING_VENDORS['aramex'])) {
					$this->load->library('BriBooksShipping_lib');
					$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');

					$pickup_location_info = $this->pickup_location_model->get($this->config->item('default_pickup_location_id'));
					$address_info = $this->address_model->get($this->input->post('address_id'));

					$couriers = $this->bribooksshipping_lib->getVendorRates([
						'pickup_location'	=> $pickup_location_info,
						'drop_location'		=> $address_info,
						'country_code'		=> $country_info['country_code'],
						'weight'			=> $cart_total['weight'],
					], 'aramex');

					if (!empty($couriers)) {
						foreach ($couriers as &$courier) {
							$courier['rate'] = self::_formatRate($courier['rate'], $country_info, $total_without_shipping_cost);
						}
					}
				}

				if (empty($couriers)) {
					$results = self::_getCouriers([
						'pickup_postcode'	=> $this->config->item('pickup_zipcode'),
						'delivery_country'	=> $country_info['country_code'],
						'cod'				=> 0,
						'weight'			=> round($cart_total['weight'] / 1000, 2),
						// 'currency'			=> $this->config->item('site_currency_code'),
					], 'international');

					if (!empty($results['data']['available_courier_companies'][0])) {
						$shiprocket_courier = $results['data']['available_courier_companies'][0];
						$shiprocket_courier['id'] = $shiprocket_courier['courier_company_id'];
						$shiprocket_courier['rate'] = self::_formatRate($shiprocket_courier['rate']['rate'], $country_info, $total_without_shipping_cost);
						$shiprocket_courier['courier_name'] = _l('delivery_charges');
						$couriers[] = $shiprocket_courier;
					}
				}
			} else {
				$results = self::_getCouriers([
					'pickup_postcode'	=> $this->config->item('pickup_zipcode'),
					'delivery_country'	=> $country_info['country_code'],
					'cod'				=> 0,
					'weight'			=> round($cart_total['weight'] / 1000, 2),
					// 'currency'			=> $this->config->item('site_currency_code'),
				], 'international');

				foreach ($results['data']['available_courier_companies'] ?? [] as &$courier) {
					$courier['id'] = $courier['courier_company_id'];
					$courier['rate'] = self::_formatRate($courier['rate']['rate'], $country_info, $total_without_shipping_cost);
					$couriers[] = $courier;
				}
			}

			$this->load->library('BriBooksShipping_lib');

			$bribooks_rate = [];

			if (empty($couriers)) {
				$bribooks_rate = $this->bribooksshipping_lib->getRate(
					$country,
					round($cart_total['weight'], 2)
				);

				log_kb(['bribooks_rate' => $bribooks_rate]);
			}

			$couriers_data = array_slice($couriers, 0, 3);

			if (!empty($bribooks_rate)) {
				$bribooks_rate['rate'] = self::_formatRate($bribooks_rate['rate'], $country_info, $total_without_shipping_cost);
				$couriers_data = array_merge([$bribooks_rate], $couriers_data);
			}

			$this->json['couriers'] = [
				'data'				=> $couriers_data,
				'recommended'		=> $couriers_data[0]['id'] ?? 0,
			];
		} else {
			$this->json['couriers']['data'] = [];
		}
	}

	private function _getCouriers($data = [], $type = '') {
		if (empty($data['weight']))
			return;

		$cache_key = vsprintf('courier_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			implode('_', array_keys($data)),
			implode('_', array_values($data)),
			$type,
		]);

		$couriers = json_decode($this->cache->get($cache_key), true);

		log_kb(['Couriers::cache_data::' => $couriers]);

		if (empty($couriers)) {
			$token = self::_getShippingToken();

			$payload = http_build_query($data);

			$couriers = self::_curl(
				'https://apiv2.shiprocket.in/v1/external/' . ($type
					? ($type . '/')
					: ''
				) . 'courier/serviceability/?' . $payload,
				null,
				'GET',
				['Authorization: Bearer ' . $token]
			);

			log_kb([
				'couriers'	=> $couriers
			]);

			$this->db->insert('courier_serviceability_logs', [
				'user_id'		=> $this->session->userdata('user_id') ?? '0',
				'request'		=> json_encode($data),
				'response'		=> json_encode($couriers),
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->cache->save($cache_key, json_encode($couriers), 300);
		}

		return $couriers;
	}

	private function _formatRate($rate = 0, $delivery_country_info = [], $total = 0) {
		log_kb(['site_currency_code' => $this->config->item('site_currency_code')]);

		$delivery_country_code 	= $delivery_country_info['country_code'] ?? 'IN';
		$custom_duty 			= $delivery_country_info['custom_duty'] ?? 0;

		$rate += (HANDLING_COST[$delivery_country_code] ?? HANDLING_COST['GE']);

		$rate /= (
			strtolower($this->config->item('site_currency_code')) !== 'inr'
				? get_exchange_rate($this->config->item('site_currency_code'))
				: 1
		);

		if (!empty($total) && !empty($custom_duty)) {
			$rate += ($total * $custom_duty / 100);
		}

		// Add tax gst
		// $rate += ($rate * 0.18);

		return round($rate, 2);
	}
}
