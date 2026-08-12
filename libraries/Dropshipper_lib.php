<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Dropshipper_lib {
	public function __construct() {
		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('address/Address_model', 'address_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');
		$this->load->model('dropshipper/Dropshipper_model', 'dropshipper_model');
		$this->load->model('dropshipper/DropshipperAssignLog_model', 'dropshipper_assignlog_model');
		$this->load->model('dropshipper/DropshipperAssignment_model', 'dropshipper_assignment_model');

		$this->order_model					= $this->CI->order_model;
		$this->dropshipper_model		  	= $this->CI->dropshipper_model;
		$this->address_model			  	= $this->CI->address_model;
		$this->state_model					= $this->CI->state_model;
		$this->dropshipper_assignlog_model  = $this->CI->dropshipper_assignlog_model;
		$this->dropshipper_assignment_model = $this->CI->dropshipper_assignment_model;
		$this->pickup_location_model 		= $this->CI->pickup_location_model;
	}

	public function assignDropshipper($order_id = 0) {
		$order_info = $this->order_model->get($order_id);

		if (empty($order_info)) return;

		if ($order_info['order_type'] != 1) return ;

		$products = $this->order_model->getProducts($order_id);

		if (empty($products)) return;

		$colored_filtered 	  	= array_filter($products, function($item) { return $item['option_type'] == 1; });
		$bw_filtered		  	= array_filter($products, function($item) { return $item['option_type'] == 2; });

		$total_colored			= array_sum(array_column($colored_filtered, 'quantity'));
		$total_bw			 	= array_sum(array_column($bw_filtered, 'quantity'));

		// hybrid order exclude
		if ($total_bw > 0 && $total_colored > 0) {
			return ;
		}

		$option_type	= $total_bw > 0 ? 2 : 1 ;
		$total_quantity = $total_colored + $total_bw;
		$address_info   =  $this->address_model->getByID($order_info['address_id']);

		if (empty($address_info['state'])) {
			return ;
		}

		$state_info	= $this->state_model->get_all([
			'name' 		 	=> trim($address_info['state']),
			'country_code' 	=> 'IN'
		])['rows'][0] ?? '';

		if (empty($state_info)) {
			return ;
		}

		$all_states_dropshipper	= '';

		$dropshipper = $this->dropshipper_model->getDropShipperByState($state_info['id'] ?? 0) ?? [];

		if (empty($dropshipper)) {
			$all_states_dropshipper = $this->dropshipper_model->getDropShipperByState('all') ?? [];

			if (empty($all_states_dropshipper)) {
				return;
			}

			$dropshipper = $all_states_dropshipper;
		}

		// zone validation
		if (!self::_validateZone($dropshipper, $address_info)) return;

		if ($total_bw > 0 && $dropshipper['bw_limit'] == 0) {
			return ;
		}

		if ($total_colored > 0 && $dropshipper['colored_limit'] == 0) {
			return ;
		}

		if (($total_quantity > $dropshipper['limit'])) {
			return;
		}

		$bw_count			= $this->dropshipper_assignlog_model->getSumQuantity([
			'status' 		=> [1],
			'option_type' 	=> 2,
			'printer_id' 	=> $dropshipper['user_id']
		]);
		$color_count 		= $this->dropshipper_assignlog_model->getSumQuantity([
			'status' 		=> [1],
			'option_type' 	=> 1,
			'printer_id' 	=> $dropshipper['user_id']
		]);

		$total_colored_count 	= ($color_count + $total_colored);
		$total_bw_count 		= ($bw_count + $total_bw);

		if ($total_colored_count >= 1 && $total_colored_count > $dropshipper['colored_limit']) {
			return;
		}

		if ($total_bw_count >= 1 && $total_bw_count > $dropshipper['bw_limit']) {
			return;
		}

		$hardcover = $paperback = $black_white = 0;

		$this->order_model->editById($order_id, [
			'assign_printer_id' 	=> (int)$dropshipper['user_id'],
			'pickup_location_id' 	=> (int)$dropshipper['pickup_id'],
		]);

		$assignment_id = $this->dropshipper_assignment_model->add([
			'printer_id'	=> (int)$dropshipper['user_id'],
			'manager_id' 	=> (int)$this->session->userdata('user_id'),
			'option_type'	=> (int)$option_type,
		]);

		foreach ($products as $product) {
			$option = json_decode($product['option'], true);

			if (mb_strtolower($option['name']) === 'ebook') continue;

			$required_qauntity = $product['quantity'];

			if ($required_qauntity > 0) {
				$this->dropshipper_assignlog_model->add([
					'assignment_id' => (int)$assignment_id,
					'order_id' 		=> $order_id,
					'version' 		=> $product['version'],
					'product_id' 	=> $product['product_id'],
					'option' 		=> $product['option'],
					'quantity' 		=> (int)$required_qauntity,
					'printer_id'	=> (int)$dropshipper['user_id'],
					'manager_id' 	=> (int)$this->session->userdata('user_id')
				]);

				if (mb_strtolower($option['name']) == 'paperback') {
					$paperback += $product['quantity'];
				} elseif (mb_strtolower($option['name']) == 'black white') {
					$black_white += $product['quantity'];
				} else {
					$hardcover += $product['quantity'];
				}
			}
		}

		$this->dropshipper_assignment_model->edit($assignment_id, [
			'description' => json_encode([
				'hardcover'		=> $hardcover,
				'paperback'		=> $paperback,
				'black_white'	=> $black_white,
			]),
		]);
	}

	public function dropShipperCourierServices($order_id = 0) {
		$order_info 	= $this->order_model->get($order_id);
		$address_info 	= $this->address_model->getByID($order_info['address_id']);

		if (empty($order_id)) return;
		if (empty($order_info)) return;
		if (empty($address_info)) return;
		if (empty($pickup_location_info = $this->pickup_location_model->get($order_info['pickup_location_id']))) return;

		$country_info = $this->db->get_where('delivery_country', [
			'name'	=> $address_info['country']
		])->row_array();

		$filter_data = [
			'pickup_pincode'		=> $pickup_location_info['pincode'],
			'delivery_pincode'		=> $address_info['zipcode'],
			'drop_location'			=> $address_info,
			'pickup_location'		=> $pickup_location_info,
			'is_domestic'			=> strtolower($address_info['country']) === 'india',
			'country_code'			=> $country_info['country_code'] ?? '',
			'cod'					=> 0,
			'weight'				=> $order_info['weight'],
		];

		$couriers = [];

		# Shiprocket service availibility
		$results = _get_vendor_rates($filter_data, 'shiprocket') ?? [];

		log_kb([
			'Shiprocket service availibility' => $results
		]);

		foreach ($results as $item) {
			if (in_array(trim($item['courier_name']), BLOCKED_COURIERS)) continue;

			$couriers['shiprocket'][] = array_merge($item, [
				'vendor_name'	=> 'shiprocket',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate']['rate'] ?? $item['rate'],
			]);
		}

		// $results = _get_vendor_rates($filter_data, 'bluedart') ?? [];
		// $results = !empty($results) ? $results : [];
		//
		// foreach ($results as $item) {
		// 	if ($item['rate'] > 0) {
		// 		$couriers['bluedart'][] = array_merge($item, [
		// 			'vendor_name'	=> 'bluedart',
		// 			'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
		// 			'rate'			=> $item['rate'],
		// 		]);
		// 	}
		// }

		# BeingShips service availability
		// $results = _get_vendor_rates($filter_data, 'beingships') ?? [];
		// $results = !empty($results) ? $results : [];
		//
		// log_kb([
		// 	'BeingShips Partners' => $results
		// ]);
		//
		// foreach ($results as $item) {
		// 	$couriers['beingships'][] = array_merge($item, [
		// 		'vendor_name'	=> 'beingships',
		// 		'courier_name'	=> $item['name'],
		// 		'courier_id'	=> $item['id'] ?? $item['courier_id'],
		// 		'rate'			=> $item['rate'] ?? $item['total_charge'],
		// 	]);
		// }

		# Delhivery service availability
		// $results = _get_vendor_rates($filter_data, 'delhivery') ?? [];
		// $results = !empty($results) ? $results : [];
		//
		// log_kb([
		// 	'Delhivery Partners' => $results
		// ]);
		// foreach ($results as $item) {
		// 	$couriers['delhivery'][] = array_merge($item, [
		// 		'vendor_name'	=> 'delhivery',
		// 		'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
		// 		'rate'			=> $item['rate'],
		// 	]);
		// }

		log_kb(['dropShipperCourierServices' => $couriers]);

		return $couriers;
	}

	private function _validateZone($dropshipper = [], $address = []) {
		$zone_info = $this->db->get_where('pincode_zone', [
			'pincode'	=> $address['zipcode'],
		])->row_array();

		$dropshipper_zones = explode(',', $dropshipper['zone']);

		if (empty($zone_info)) return true;
		if (empty($dropshipper_zones)) return true;
		if (in_array($zone_info['zone'], $dropshipper_zones)) return true;

		return false;
	}
}
