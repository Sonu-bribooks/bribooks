<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class BriBooksShippingOld_lib {
	public function __construct() {
		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->library('Pricing_lib');

		$this->pricing_lib = $this->CI->pricing_lib;

		$this->load->model('shipping/Indiapost_model');
		$this->load->model('shipping/Courier_model');
		$this->load->model('order/Order_model');
		$this->load->model('address/Address_model');
		$this->load->model('shipping/PickupLocation_model');
		$this->load->model('user/Student_model');
		$this->load->model('shipping/Shipment_model');
		$this->load->model('book/BookVersion_model');
		$this->load->model('order/OrderHistory_model');
		$this->load->model('shipping/PickupData_model');
		$this->load->model('order/OrderPackingLog_model');

		$this->Indiapost_model = $this->CI->Indiapost_model;
		$this->courier_model = $this->CI->Courier_model;
		$this->order_model = $this->CI->Order_model;
		$this->address_model = $this->CI->Address_model;
		$this->pickup_location_model = $this->CI->PickupLocation_model;
		$this->student_model = $this->CI->Student_model;
		$this->shipment_model = $this->CI->Shipment_model;
		$this->book_version_model = $this->CI->BookVersion_model;
		$this->order_history_model = $this->CI->OrderHistory_model;
		$this->pickup_data_model = $this->CI->PickupData_model;
		$this->order_packing_log_model = $this->CI->OrderPackingLog_model;
	}

	public function getRate($country = 'India', $weight = 0, $price = 0, $count = 0, $i = 0) {
		if ($weight >= 0) {
			if ($count > 0) {
				$count = 1;
			}

			if ($rate_info = $this->Indiapost_model->get($country)) {
				$rates 	= json_decode($rate_info['rates'], true);
				$weight = $weight - $rates[$count]['weight'];
				$price 	= $price + $rates[$count]['rate'];
				// round(((18 / 100) * $price + 150 ))

				if ($weight > 0) {
					$count++;
					$i++;

					if (strtolower($country) == 'india') {
						$weight = ($i > 100) ? 0 : $weight;
					} else {
						$weight = ($i > 5000) ? 0 : $weight;
					}

					return self::getRate($country, $weight, $price, $count, $i);
				} else {
					// percentage +150rs own charge
					$price = $price + (strtolower($country) == 'india' ? 0 : 80);

					return [
						'id'  			=> 9876543210,
						// 'weight' 	=> $weight,
						'courier_name' 	=> get_settings('system_name') . ' Shipping',
						'rate' 			=> $price
					];
				}
			}
		}
	}

	public function normalRate($country = 'India', $weight = 0, $price = 0, $count = 0, $surcharge = 0, $i = 0) {
		if ($weight > 0) {
			$rate_info = $this->Indiapost_model->get($country);
			if ($count > 0) {
				$count = 1;
			}

			if ($count == 0){
				$surcharge += ($weight /20) * $rate_info['normal_surcharge'];
			}

			if ($rate_info) {
				$rates 		= json_decode($rate_info['nomal_rate'], true);
				$weight 	= $weight - $rates[$count]['weight'];
				$price 		= $price + $rates[$count]['rate'];

				if ($weight > 0) {
					$count++;
					$i++;

					if (strtolower($country) == 'india') {
						$weight = ($i > 100) ? 0 : $weight;
					} else {
						$weight = ($i > 5000) ? 0 : $weight;
					}

					return self::normalRate($country, $weight, $price, $count, $surcharge, $i);
				} else {
					$price = $price + $surcharge + (strtolower($country) == 'india' ? 0 : 240);

					return [
						'id'  			=> 98765432211,
						'courier_name' 	=> get_settings('system_name') . ' Shipping',
						'rate' 			=> $price,
						'surcharge'		=> $surcharge
					];
				}
			} else {
				return false;
			}
		}
	}

	public function getRateZoneWise($params = []) {
		if (empty($params)) return;

		$weight_slab = _get_weight_slab($params['weight']);

		$filter_data = [
			'status'		=> 1,
			'weight'		=> $weight_slab,
			'sort'			=> 'courier.courier_order',
			'order'			=> 'ASC'
		];

		$results = $this->courier_model->get_all($filter_data)['rows'] ?? [];

		$couriers = [];

		if (!empty($results)) {
			$pricing = $this->pricing_lib;

			foreach ($results as $courier) {
				$pricing->setPlan('default');
				$pricing->setCourier($courier['id']);
				$pricing->setOrigin($params['pickup_postcode']);
				$pricing->setDestination($params['delivery_postcode']);
				$pricing->setType('prepaid');
				$pricing->setAmount($params['total'] ?? 0);
				$pricing->setWeight($params['weight']);
				$pricing->setLength(10);
				$pricing->setBreadth(10);
				$pricing->setHeight(10);

				$shipping_cost = $pricing->calculateCost();

				if (!empty($shipping_cost['total'])) {
					$couriers[] = [
						'courier' 		=> $courier,
						'courier_id'	=> $courier['id'],
						'courier_name'	=> get_settings('system_name') . ' Shipping',
						'charges'		=> $shipping_cost['total'],
						'rate'			=> $shipping_cost['total']
					];
				}
			}

			array_multisort(array_column($couriers, 'charges'), SORT_DESC, $couriers);
		}

		log_kb([
			'weight_slab' 	=> $weight_slab,
			'params' 		=> $params,
			'couriers' 		=> $couriers,
		]);

		return $couriers;
	}

	public function getCourierServiceability($order_id = false) {
		if (empty($order_id)) return;

		$order_info = $this->order_model->get($order_id);

		if (empty($order_info)) return;

		// $weight_slab = _get_weight_slab($order_info['weight']);
		// pr($weight_slab, 1);

		$address_info = $this->address_model->get($order_info['address_id']);
		$country_info = $this->db->get_where('delivery_country', [
			'name'	=> $address_info['country']
		])->row_array();

		if (empty($address_info)) return;

		$pickup_location_info = $this->pickup_location_model->get($order_info['pickup_location_id']);

		if (empty($pickup_location_info)) return;

		$courier_ids = [];

		$filter_data = [
			'pickup_pincode'	=> $pickup_location_info['pincode'],
			'delivery_pincode'	=> $address_info['zipcode'],
			'is_domestic'		=> strtolower($address_info['country']) === 'india',
			'delivery_country'	=> $country_info['country_code'] ?? '',
			'cod'				=> 0,
			'weight'			=> $order_info['weight'],
		];

		$zones_data = [];

		# Shiprocket service availibility #
		$shiprocket_couriers = _get_service_ability($filter_data, 'shiprocket');

		if (!empty($shiprocket_couriers->data) &&
			!empty($shiprocket_couriers = $shiprocket_couriers->data->available_courier_companies)
		) {
			$vendor_name = 'shiprocket';

			foreach ($shiprocket_couriers as $courier) {
				if (!empty($courier_info = $this->courier_model->get_all([
					'status'		=> 1,
					'vendor_name'	=> $vendor_name,
					'carrier_id'	=> $courier->courier_company_id
				])['rows'][0] ?? [])) {
					$courier_ids[] = $courier_info['id'];
					$zones_data[$courier_info['id']] = SHIPMENT_ZONES[$vendor_name][$courier->zone] ?? $courier->zone;
				}
			}
		}

		# Bluedart service availibility #
		$bluedart_couriers = _get_service_ability($filter_data, 'bluedart');

		if (!empty($bluedart_couriers)) {
			$vendor_name = 'bluedart';

			$pricing = $this->pricing_lib;

			foreach ($bluedart_couriers['rows'] ?? [] as $courier) {
				$courier_ids[] = $courier['id'];
				$pricing->setOrigin($pickup_location_info['pincode']);
				$pricing->setDestination($address_info['zipcode']);
				$zones_data[$courier_info['id']] = $pricing->calculateZone();
			}
		}

		# Aramex service availibility #
		$bluedart_couriers = _get_service_ability($filter_data, 'aramex');

		if (!empty($bluedart_couriers)) {
			$vendor_name = 'aramex';

			$pricing = $this->pricing_lib;

			foreach ($bluedart_couriers['rows'] ?? [] as $courier) {
				$courier_ids[] = $courier['id'];
				$pricing->setOrigin($pickup_location_info['pincode']);
				$pricing->setDestination($address_info['zipcode']);
				$zones_data[$courier_info['id']] = $pricing->calculateZone();
			}
		}

		$filter_data = [
			'status'		=> 1,
			/*'weight'		=> $weight_slab,*/
			'sort'			=> 'courier.courier_order',
			'order'			=> 'ASC',
			'courier_ids'	=> $courier_ids
		];

		$couriers = $this->courier_model->get_all($filter_data);

		// pr($filter_data);
		// log_kb(['couriers' => $couriers]);

		$all_couriers = [];

		if (!empty($couriers['rows'])) {
			$i = 0;

			$pricing = $this->pricing_lib;

			foreach ($couriers['rows'] as $courier) {
				$pricing->setPlan('default');
				$pricing->setCourier($courier['id']);
				$pricing->setOrigin($pickup_location_info['pincode']);
				$pricing->setDestination($address_info['zipcode']);
				$pricing->setType('prepaid');
				$pricing->setAmount($order_info['total']);
				$pricing->setWeight($order_info['weight']);
				$pricing->setLength(10);
				$pricing->setBreadth(10);
				$pricing->setHeight(10);
				$pricing->setZone($zones_data[$courier['id']] ?? '');

				$shipping_cost = $pricing->calculateCost();

				if (!empty($shipping_cost['total'])) {
					$all_couriers[$i] 						= $courier;
					$all_couriers[$i]['courier_id'] 		= $courier['id'];
					$all_couriers[$i]['charges'] 			= $shipping_cost['total'];
					$all_couriers[$i]['charges_breakup'] 	= $shipping_cost;

					$i++;
				}
			}

			$all_couriers = json_decode(json_encode($all_couriers));

			array_multisort(array_column($all_couriers, 'charges'), SORT_ASC, $all_couriers);
		}

		return $all_couriers;
	}

	public function processOrderShipment($order_id = false, $courier_id = false, $pickup_location_id = false) {
		$this->error = '';

		if (empty($order_id) || empty($courier_id) || empty($pickup_location_id))
			return;

		if (empty(_is_order_shippable($order_id))) {
			$this->error = 'Unable to book order.';
			return false;
		}

		if (empty($order_info = $this->order_model->get($order_id))) {
			$this->error = 'Invalid order.';
			return false;
		}

		if ($order_info['shipping_status'] != 0) {
			$this->error = 'Already Booked.';
			return false;
		}

		// if (empty($courier_info = $this->courier_model->get($courier_id))) {
		// 	$this->error = 'Invalid courier.';
		// 	return false;
		// }

		if (empty($address_info = $this->address_model->get($order_info['address_id']))) {
			$this->error = 'Invalid order.';
			return false;
		}

		if ($pickup_location_id != $order_info['pickup_location_id']) {
			$this->error = 'Invalid pickup location.';
			return false;
		}

		if (empty($pickup_location_info = $this->pickup_location_model->get($pickup_location_id))) {
			$this->error = 'Invalid pickup location.';
			return false;
		}

		$filter_data = [
			'pickup_pincode'	=> $pickup_location_info['pincode'],
			'delivery_pincode'	=> $address_info['zipcode'],
			'cod'				=> 0,
			'weight'			=> $order_info['weight'],
		];

		// $vendor_name = strtolower($courier_info['vendor_name']);
		//
		// if (empty($shipping_info = _get_shipping_info($filter_data, $vendor_name))) {
		// 	$this->error = 'Invalid shipping info.';
		// 	return false;
		// }

		$save = [
			'order_id'				=> $order_id,
			'user_id'				=> $order_info['user_id'],
			'courier_id'			=> $courier_id,
			'pickup_location_id'	=> $pickup_location_id,
			'currency_code'			=> $order_info['currency_code'],
			'order_total_amount'	=> $order_info['total'],
			'shipped_by'			=> $this->session->userdata('user_id') ?? ''
		];

		if ($vendor_name == 'bluedart') {
			$save['pricing_info'] = json_encode($shipping_info[$vendor_name][$courier_info['id']]) ?? '';
			$save['zone'] = $shipping_info[$vendor_name][$courier_info['id']]['zone'] ?? '';

		} else {
			$save['pricing_info'] = json_encode($shipping_info[$vendor_name][$courier_info['carrier_id']]) ?? '';
			$save['zone'] = $shipping_info[$vendor_name][$courier_info['carrier_id']]['zone'] ?? '';
		}

		$shipment_id = $this->shipment_model->add($save);

		$response = [];

		if (!empty($shipment_id)) {
			if (empty($response = self::generateAwb($shipment_id))) {
				$this->error = $this->error ?? 'Unable to generate AWB';
				return false;
			}
		}

		if (!empty($shipping_info = $shipping_info[$vendor_name][$courier_info['carrier_id']] ?? [])) {
			$this->order_model->editById($order_id, [
				'shipping_info'			=> json_encode($shipping_info + [
					'bb_courier_id'		=> $courier_info['id'],
					'bb_shipment_id'	=> $shipment_id
				])
			]);
		}

		return $response;
	}

	public function generateAwb($shipment_id = false) {
		$this->error = '';

		if (empty($shipment_id)) {
			$this->error = 'Invalid Shipment ID';
			return false;
		}

		//get shipping details for this order
		$shipment_info = $this->shipment_model->get($shipment_id);

		if (empty($shipment_info)) {
			$this->error = 'Shipment Not Found';
			return false;
		}

		if (!empty($shipment_info['awb_number'])) {
			$this->error = 'Already Booked.';
			return false;
		}

		if (empty($order_info = $this->order_model->get($shipment_info['order_id']))) {
			$this->error = 'Invalid order.';
			return false;
		}

		if ($order_info['shipping_status'] != '0') {
			$this->error = 'Already Booked.';
			return false;
		}

		if (empty($user_info = $this->student_model->get($order_info['user_id']))) {
			$this->error = 'Invalid user.';
			return false;
		}

		if (empty($courier_info = $this->courier_model->get($shipment_info['courier_id']))) {
			$this->error = 'Invalid courier.';
			return false;
		}

		if (empty($address_info = $this->address_model->get($order_info['address_id']))) {
			$this->error = 'Invalid order.';
			return false;
		}

		if (empty($pickup_location_info = $this->pickup_location_model->get($order_info['pickup_location_id']))) {
			$this->error = 'Invalid pickup location.';
			return false;
		}

		$type = (strtolower($address_info['country']) === 'india') ? '' : 'international';

		if (empty(allow_bb_shipping_module($type))) {
			$this->error = 'Shipping module disabled.';
			return false;
		}

		$products = $this->order_model->getProducts($order_info['id']);

		$products = array_filter($products, function($item) {
			$option = json_decode($item['option'], true);
			return mb_strtolower($option['name']) != 'ebook';
		});

		if (empty($products)) {
			$this->error = 'Invalid order.';
			return false;
		}

		$pricing = $this->pricing_lib;
		$pricing->setPlan('default');
		$pricing->setCourier($courier_info['id']);
		$pricing->setOrigin($pickup_location_info['pincode']);
		$pricing->setDestination($address_info['zipcode']);
		$pricing->setType('prepaid');
		$pricing->setAmount($order_info['total']);
		$pricing->setWeight($order_info['weight']);
		$pricing->setLength(10);
		$pricing->setBreadth(10);
		$pricing->setHeight(10);
		$pricing->setZone($shipment_info['zone'] ?? '');

		$shipping_cost = $pricing->calculateCost();

		if (empty($shipping_cost['total'])) {
			$this->error = 'Invalid pricing.';
			return false;
		}

		$order_info['shipment_id'] = $shipment_id;
		$data = $order_info + [
			'products' 	=> $products,
			'address'	=> $address_info,
			'userData'	=> $user_info,
			'pickupLocationInfo'	=> $pickup_location_info,
		];

		$response = [];

		// Step 1. Ship Order (Create Order)
		switch (strtolower($courier_info['vendor_name'])) {
			case 'shiprocket':
				$this->load->library('couriers/Shiprocket');
				$shiprocket = new Shiprocket();

				if (empty($order_response = $shiprocket->bookOrder($data))) {
					$this->error = $shiprocket->api_error ?? 'Invalid shipment.';
					$this->shipment_model->edit($shipment_id, ['message' => $this->error]);
					return false;
				}

				if (!empty($order_response['shipment_id']) && empty($response = $shiprocket->generateAWB($order_response['shipment_id'], $courier_info['carrier_id']))) {
					$this->error = $shiprocket->api_error ?? 'Invalid shipment.';
					$this->shipment_model->edit($shipment_id, ['message' => $this->error]);
					// return false;
				}

				$pickup_response = $shiprocket->generatePickup($order_response['shipment_id']);

				break;

			case 'bluedart':
				$this->load->library('couriers/Bluedart');
				$bluedart = new Bluedart();

				if (empty($order_response = $bluedart->bookOrder($shipment_id))) {
					$this->error = 'Invalid shipment.';
					$this->shipment_model->edit($shipment_id, ['message' => 'Invalid shipment.']);
					return false;
				}

				if (!empty($order_response['shipment_id']) && empty($response = $bluedart->generateAWB($data))) {
					$this->error = $bluedart->api_error ?? 'Invalid shipment.';
					$this->shipment_model->edit($shipment_id, ['message' => $this->error]);
				}

				$pickup_response = $response;

				break;

			default:
				break;
		}

		log_kb([
			'bookOrder::' 		=> $order_response,
			'generateAWB::' 	=> $response,
			'generatePickup::' 	=> $pickup_response
		]);

		$api_response = [];

		if (isset($response['api_response'])) {
			$api_response = $response['api_response'];
			unset($response['api_response']);
		}

		if (!empty($pickup_response['routing_code'])) {
			$response['routing_code'] = $pickup_response['routing_code'];
		}

		$this->shipment_model->edit($shipment_id, [
			'vendor_name'			=> $courier_info['vendor_name'] ?? '',
			'courier_order_id'		=> $order_response['order_id'] ?? '',
			'courier_shipment_id'	=> $order_response['shipment_id'] ?? '',
			'awb_number'			=> $response['awb_number'] ?? ($order_response['awb_code'] ?? ''),
			'shipment_info' 		=> !empty($response) ? json_encode($response) : '',
			'shipping_tracking_info'=> json_encode($api_response),
			'status'				=> 1
		]);

		if (empty($order_response['awb_code']) && !empty($response['awb_number'])) {
			$order_response['awb_code'] = $response['awb_number'];
		}

		if (!empty($response['assigned_date_time'])) {
			$this->order_model->editById($order_info['id'], [
				'status'					=> '9',
				'shipping_status' 			=> '1',
				'shipping_tracking_info' 	=> json_encode($order_response),
			]);
		} elseif (!empty($order_response['awb_code'])) {
			$this->order_model->editById($order_info['id'], [
				'status'					=> '9',
				'shipping_status' 			=> '1',
				'shipping_tracking_info' 	=> json_encode($order_response),
			]);

			$this->error = '';
			$response['awb_number'] = $order_response['awb_code'];

			$this->shipment_model->edit($shipment_id, ['message' => 'Already Booked.']);
		} else {
			$this->order_model->editById($order_info['id'], [
				/*'shipping_status' 			=> '9',*/
				'shipping_tracking_info' 	=> json_encode($order_response),
			]);
		}

		if (!empty($pickup_response)) {
			$pickup_data = [];
			$pickup_data['shipment_id'] = $shipment_id;
			$pickup_data['courier_shipment_id'] = $order_response['shipment_id'];
			$pickup_data['pickup_location_id'] = $order_info['pickup_location_id'];
			$pickup_data['scheduled_date'] = $pickup_response['scheduled_date'];
			$pickup_data['token_number'] = $pickup_response['token_number'];
			$pickup_data['remark'] = $pickup_response['remark'];
			$pickup_data['scheduled_timestamp'] = $pickup_response['scheduled_timestamp'];
			$pickup_data['status'] = $pickup_response['pickup_status'];

			$this->pickup_data_model->add($pickup_data);
		}

		$this->order_packing_log_model->add([
			'order_id'	=> $order_info['id'],
			'user_id'	=> $this->session->userdata('user_id'),
		]);

		$this->order_history_model->add([
			'order_id' 		=> $order_info['id'],
			'description' 	=> 'Custom Order Booked',
			'status' 		=> 9,
		]);

		return $response;
	}

	public function generateLabel($ids = false, $format = 'thermal') {
		$dir = FCPATH . 'uploads/label/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		ini_set('pcre.backtrack_limit', '5000000');

		$shipment_data = [];

		foreach ($ids as $key => $id) {
			if (!empty($order_info = $this->order_model->get($id))) {
				if (($order_info['shipping_status'] == '1') && ($order = self::getShipmentData($id))) {
					$shipment_data[] = $order;
				}
			}
		}

		// pr(_get_label_barcode('12345', 85, 30), 1);

		$html = $this->load->view('backend/admin/order/order_label', array('shipments' => $shipment_data, 'format' => $format), true);

		// print_r($html); die;

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		// $dompdf->setPaper('A4', 'potrait');

		$dompdf->set_paper(array(0,0,296,450));

		// Render the HTML as PDF
		$dompdf->render();

		$file = 'uploads/label/'.date('Y-m-d').'-'.@$ids[0].'.pdf';
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return base_url($file);
	}

	public function getShipmentData($order_id = false) {
		if (empty($order_id))
			return false;

		if (empty($order_info = $this->order_model->get($order_id))) {
			return false;
		}
		if ($order_info['shipping_status'] == '0' || empty($order_info['shipping_info'])) {
			return false;
		}

		if (empty($user_info = $this->student_model->get($order_info['user_id']))) {
			return false;
		}

		if (empty($products = $this->order_model->getProducts($order_id))) {
			return false;
		}

		if (empty($address_info = $this->address_model->get($order_info['address_id']))) {
			return false;
		}

		if (empty($pickup_location_info = $this->pickup_location_model->get($order_info['pickup_location_id']))) {
			return false;
		}

		$shipping_info = json_decode($order_info['shipping_info'], true);

		if (empty($shipping_info['bb_shipment_id'])) {
			return false;
		}

		if (empty($shipment_info = $this->shipment_model->get($shipping_info['bb_shipment_id']))) {
			return false;
		}

		if (empty($shipment_info['awb_number']) || empty($shipment_info['courier_shipment_id'])) {
			return false;
		}

		if (empty($courier_info = $this->courier_model->get($shipment_info['courier_id']))) {
			return false;
		}

		$order = new stdClass();
		$order->order_id = $order_info['order_code'];
		$order->order_date = strtotime($order_info['date_added']);
		$order->shipping_fname = $address_info['name'];
		$order->shipping_lname = '';
		$order->shipping_company_name = '';
		$order->shipping_address = $address_info['address'];
		$order->shipping_address_2 = '';
		$order->shipping_city = $address_info['city'];
		$order->shipping_state = $address_info['state'];
		$order->shipping_country = $address_info['country'];
		$order->shipping_phone = $address_info['mobile'];
		$order->shipping_zip = $address_info['zipcode'];
		$order->order_payment_type = 'prepaid';
		$order->order_amount = $order_info['total'];
		$order->package_weight = $order_info['weight'];
		$order->shipping_charges = $order_info['shipping_cost'];
		$order->cod_charges = '';
		$order->tax_amount = '';
		$order->discount = '';
		$order->package_length = '10';
		$order->package_height = '10';
		$order->package_breadth = '10';
		$order->channels_brand_logo = '';
		$order->currency_code = $order_info['currency_code'];
		$order->currency_symbol = $order_info['currency_symbol'];

		$product_arr = [];

		foreach ($products as $product) {
			$book_info = $this->book_version_model->getByVersion($product['product_id'], $product['version']);

			if (empty($book_info)) continue;

			$option = json_decode($product['option'], true);

			$product_arr[] = (object) [
				'product_name'		=> $book_info['name'],
				'product_sku'		=> _o_b_code($book_info['book_id'], $book_info['version'], $option['name']),
				'product_qty'		=> (int)$product['quantity'],
				'product_price'		=> $product['price'],
			];
		}

		$products = (object)$product_arr;

		$shipment_info_json = !empty($shipment_info['shipment_info']) ? json_decode($shipment_info['shipment_info'], 1) : [];

		$shipment = new stdClass();
		$shipment->id = $shipment_info['courier_shipment_id'];
		$shipment->awb_number = $shipment_info['awb_number'];
		$shipment->routing_code = $shipment_info_json['routing_code'] ?? '';
		$shipment->shipment_info_1 = '';
		$shipment->is_rto_different = '';
		$shipment->shipment_date = strtotime($shipment_info['date_added']);

		$courier = new stdClass();
		$courier->id = $courier_info['id'];
		$courier->code = $courier_info['code'];
		$courier->carrier_id = $courier_info['carrier_id'];
		$courier->carrier_code = $courier_info['carrier_code'];
		$courier->display_name = $courier_info['display_name'];
		$courier->vendor_name = $courier_info['vendor_name'];

		$warehouse = new stdClass();
		$warehouse->name = $pickup_location_info['name'];
		$warehouse->contact_name = $pickup_location_info['contact_name'];
		$warehouse->address_1 = $pickup_location_info['address_1'];
		$warehouse->address_2 = $pickup_location_info['address_2'];
		$warehouse->city = $pickup_location_info['city'];
		$warehouse->state = $pickup_location_info['state'];
		$warehouse->country = 'India';
		$warehouse->zip = $pickup_location_info['pincode'];
		$warehouse->phone = $pickup_location_info['mobile'];
		$warehouse->gst_number = '06AABCY5072A1ZN';
		$warehouse->support_phone = ''; //$pickup_location_info['mobile'];
		$warehouse->support_email = ''; //$pickup_location_info['email'];
		$warehouse->hide_label_products = '';
		$warehouse->hide_label_address = '';
		$warehouse->hide_label_pickup_mobile = '';
		$warehouse->logo = base_url('assets/images/logo-black.png');

		$rto_warehouse = $warehouse;

		$user = new stdClass();
		$user->id = '';
		$user->support_category = '';

		$company = new stdClass();
		$company->cmp_logo = '';

		$channel_brand_logo = [];

		$return = [
			'order' 		=> $order,
			'products' 		=> $products,
			'shipment' 		=> $shipment,
			'courier' 		=> $courier,
			'warehouse'		=> $warehouse,
			'rto_warehouse' => $rto_warehouse,
			'company' 		=> $company,
			'user' 			=> $user,
			'channels_brand_logo' => (object)$channel_brand_logo
		];

		// pr($return, 1);

		return (object)$return;
	}

	public function generateInvoice($id = false) {
		$dir = FCPATH . 'uploads/pdfs/invoice/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$info 			= $this->order_model->get($id);
		$user_info 		= $this->student_model->get($info['user_id']);
		$address_info 	= $this->address_model->getByID($info['address_id']);

		$data['order'] 		= $info;
		$data['address'] 	= $address_info;
		$data['products'] 	= $this->order_model->getProducts($info['id']);

		$shipping_tracking_info = !empty($info['shipping_tracking_info']) ? json_decode($info['shipping_tracking_info'], 1) : [];

		$data['awb_number'] = $shipping_tracking_info['awb_code'] ?? '';

		$html = $this->load->view('common/invoice/invoice_order_print', $data, true);

		// print_r($html); die;

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		// $dompdf->setPaper('A4', 'potrait');

		$dompdf->set_paper([0, 0, 296, 450]);

		// Render the HTML as PDF
		$dompdf->render();

		$file 	= 'uploads/pdfs/invoice/' . date('Y_m_d_H_i_s', strtotime($info['date_added'])) . '.pdf';

		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return base_url($file);
	}

	public function generateManifest($order_id = false) {
		if (empty($order_id))
			return false;

		if (empty($order_info = $this->order_model->get($order_id))) {
			return false;
		}

		$shipping_info = json_decode($order_info['shipping_info'], true);

		if (empty($shipping_info['bb_shipment_id'])) {
			return false;
		}

		if (empty($shipment_info = $this->shipment_model->get($shipping_info['bb_shipment_id']))) {
			return false;
		}

		if (empty($shipment_info['awb_number']) || empty($shipment_info['courier_order_id'])) {
			return false;
		}

		if (empty($courier_info = $this->courier_model->get($shipment_info['courier_id']))) {
			return false;
		}

		$response = [];

		switch (strtolower($courier_info['vendor_name'])) {
			case 'shiprocket':
				$this->load->library('couriers/Shiprocket');
				$shiprocket = new Shiprocket();

				if (empty($response = $shiprocket->generateManifests($shipment_info['courier_order_id']))) {
					$this->error = $shiprocket->api_error ?? 'Unable to generate manifest';
					return false;
				}
				break;

			default:
				break;
		}

		return $response['manifest_url'] ?? '';
	}
}
