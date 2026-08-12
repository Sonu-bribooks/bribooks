<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('allow_bb_shipping_module')) {
	function allow_bb_shipping_module($type) {
		if (!empty($type)) return false;

		$CI	=&	get_instance();
		$CI->db->where('key', 'shipping_module');
		$is_shipping_module = $CI->db->get('settings')->row()->value;

		if($is_shipping_module == 1)
			return true;

		return false;
	}
}

if (!function_exists('_get_weight_slab')) {
	function _get_weight_slab($weight = 0) {
		if (empty($weight)) return false;

		$CI	=&	get_instance();

		$CI->db->select('weight');
		$CI->db->where('weight >= ', $weight);
		$CI->db->where('_deleted', 0);
		$CI->db->order_by('weight', 'ASC');

		return $CI->db->get('courier')->row_array();
	}
}

if (!function_exists('_get_vendor_rates')) {
	function _get_vendor_rates($data = [], $vendor_name = '') {
		if (empty($data) || empty($vendor_name)) return false;

		$CI	=&	get_instance();

		$couriers = [];

		$vendor_name = strtolower($vendor_name);

		switch ($vendor_name) {
			case 'shiprocket':
				$CI->load->library('couriers/Shiprocket');

				if ($data['is_domestic']) {
					$payload = [
						'pickup_postcode'	=> $data['pickup_pincode'],
						'delivery_postcode'	=> $data['delivery_pincode'],
						'cod'				=> 0,
						'weight'			=> round($data['weight'] / 1000, 2),
					];
				} else {
					$payload = [
						'pickup_postcode'	=> $data['pickup_pincode'],
						'delivery_country'	=> $data['country_code'],
						'cod'				=> 0,
						'weight'			=> round($data['weight'] / 1000, 2),
					];
				}

				$couriers = $CI->shiprocket->getRates($payload, $data['is_domestic'] ? '' : 'international');

				break;
			case 'bluedart':
				return [];
				if (empty($data['is_domestic'])) return [];

				$CI->load->library('couriers/Bluedart');
				$couriers = $CI->bluedart->getRates($data);

				log_kb([
					'Bluedart-courier' => $couriers
				]);

				break;
			case 'aramex':
				if ($data['is_domestic']) return [];

				$CI->load->library('couriers/Aramex');
				$couriers = $CI->aramex->getRates($data);

				break;
			case 'dtdc':
				if (empty($data['is_domestic'])) return [];

				$CI->load->library('couriers/Dtdc');
				$couriers = $CI->dtdc->getRates($data);

				break;
			case 'dtdcb2b':
				if (empty($data['is_domestic'])) return [];

				$CI->load->library('couriers/Dtdcb2b');
				$couriers = $CI->dtdcb2b->getRates($data);

				break;
			case 'beingships':
				if (empty($data['is_domestic'])) return [];

				$CI->load->library('couriers/Beingships');
				$couriers = $CI->beingships->getRates($data);

				break;
			case 'delhivery':
				if (empty($data['is_domestic'])) return [];

				$CI->load->library('couriers/Delhivery');
				$couriers = $CI->delhivery->getRates($data);

				break;
			case 'gokwik':
				if (empty($data['is_domestic'])) return [];

				$CI->load->library('couriers/Gokwik');
				$couriers = $CI->gokwik->getRates($data);

				break;
			default:
				break;
		}

		return $couriers;
	}
}

if (!function_exists('_get_shipping_info')) {
	function _get_shipping_info($data = [], $vendor_name = '') {
		if (empty($data) || empty($vendor_name)) return false;

		$CI	=&	get_instance();

		$couriers = [];

		$vendor_name = strtolower($vendor_name);

		switch ($vendor_name) {
			case 'shiprocket':
				$CI->load->library('couriers/shiprocket_lib');

				$couriersArr = $CI->shiprocket_lib->getServiceability([
					'pickup_postcode'	=> $data['pickup_pincode'],
					'delivery_postcode'	=> $data['delivery_pincode'],
					'cod'				=> 0,
					'weight'			=> round($data['weight'] / 1000, 2),
				]);

				if(!empty($couriersArr = $couriersArr->data->available_courier_companies ?? [])) {
					foreach ($couriersArr as $courier) {
						$data = [
							'id'						=> $courier->id,
							'etd'						=> $courier->etd,
							'rate'						=> $courier->rate,
							'zone'						=> SHIPMENT_ZONES[$vendor_name][$courier->zone] ?? $courier->zone,
							'courier_name'				=> $courier->courier_name,
							'courier_company_id'		=> $courier->courier_company_id,
							'estimated_delivery_days'	=> $courier->estimated_delivery_days,
							'vendor_name'				=> $vendor_name
						];

						$couriers[$vendor_name][$courier->courier_company_id] = $data;
					}
				}

				break;

			default:
				break;
		}

		return $couriers;
	}
}

if (!function_exists('_is_order_shippable')) {
	function _is_order_shippable($order_id = 0, $order_type = 'book') {
		if (empty($order_id)) return false;

		$CI	=&	get_instance();

		if (($order_type == 'medallion')) {
			$CI->load->model('medallion/MedallionOrder_model', 'medallion_order_model');

			$order_info = $CI->medallion_order_model->get($order_id);
		} else if (($order_type == 'school')) {
			$CI->load->model('school/SchoolOrder_model', 'school_order_model');

			$order_info = $CI->school_order_model->get($order_id);

			$order_info['address_id'] = (!empty($order_info['address']) && !empty($order_info['zipcode'])) ? 1 : 0;
		} else {
			$CI->load->model('order/Order_model', 'order_model');

			$order_info = $CI->order_model->get($order_id);
		}

		if (($order_info['address_id'] != 0) && in_array($order_info['status'], [21]))
			return true;

		return false;
	}
}

if (!function_exists('_get_label_barcode')) {
	function _get_label_barcode($data = '', $width = '', $height = '') {
		if (empty($data) || empty($width) || empty($height)) return false;

		$dir = FCPATH . 'uploads/label/png/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = 'uploads/label/png/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			$width ?? 0,
			$height ?? 0,
			'black',
			array(1, 1, 1, 1)
		)->setBackgroundColor('white');

		// return $bobj->getHtmlDiv();

		file_put_contents(FCPATH . $file, $bobj->getPngData());
		return base_url($file);
	}
}

if (!function_exists('_is_banned_country')) {
	function _is_banned_country($country_code = '') {
		$CI	=&	get_instance();

		return !empty($CI->db->get_where('delivery_country', [
			'country_code' 	=> $country_code,
			'status'		=> 0
		])->row());
	}
}
