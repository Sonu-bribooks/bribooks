<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Dompdf\Dompdf;

final class BriBooksShipping_lib {
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
		$this->load->model('user/User_model');
		$this->load->model('shipping/Shipment_model');
		$this->load->model('book/BookVersion_model');
		$this->load->model('order/OrderHistory_model');
		$this->load->model('shipping/PickupData_model');
		$this->load->model('order/OrderPackingLog_model');
		$this->load->model('order/OrderClone_model');

		$this->load->model('school/SchoolOrder_model');
		$this->load->model('school/SchoolOrderHistory_model');
		$this->load->model('school/SchoolOrderPackingLog_model');
		$this->load->model('school/School_model');

		$this->load->model('common/Site_model');

		$this->load->model('medallion/MedallionOrder_model');
		$this->load->model('medallion/MedallionAddress_model');
		$this->load->model('medallion/MedallionOrderHistory_model');
		$this->load->model('medallion/MedallionOrderPackingLog_model');

		$this->Indiapost_model 						= $this->CI->Indiapost_model;
		$this->courier_model 						= $this->CI->Courier_model;
		$this->order_model 							= $this->CI->Order_model;
		$this->address_model 						= $this->CI->Address_model;
		$this->pickup_location_model 				= $this->CI->PickupLocation_model;
		$this->user_model 							= $this->CI->User_model;
		$this->shipment_model 						= $this->CI->Shipment_model;
		$this->book_version_model 					= $this->CI->BookVersion_model;
		$this->order_history_model 					= $this->CI->OrderHistory_model;
		$this->pickup_data_model 					= $this->CI->PickupData_model;
		$this->order_packing_log_model 				= $this->CI->OrderPackingLog_model;
		$this->order_clone_model 					= $this->CI->OrderClone_model;
		$this->school_order_model 					= $this->CI->SchoolOrder_model;
		$this->school_order_history_model 			= $this->CI->SchoolOrderHistory_model;
		$this->school_order_packing_log_model 		= $this->CI->SchoolOrderPackingLog_model;
		$this->site_model 							= $this->CI->Site_model;
		$this->school_model 						= $this->CI->School_model;
		$this->medallion_order_model 				= $this->CI->MedallionOrder_model;
		$this->medallion_address_model 				= $this->CI->MedallionAddress_model;
		$this->medallion_order_history_model 		= $this->CI->MedallionOrderHistory_model;
		$this->medallion_order_packing_log_model 	= $this->CI->MedallionOrderPackingLog_model;
	}

	// deprecated
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
						'courier_name' 	=> _li('Shipping Charges'),
						'rate' 			=> $price
					];
				}
			}
		}
	}

	// deprecated
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

	// deprecated
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

	public function getVendorRates($data = [], $vendor = 'shiprocket') {
		$filter_data = [
			'pickup_pincode'		=> $data['pickup_location']['pincode'],
			'delivery_pincode'		=> $data['drop_location']['zipcode'],
			'drop_location'			=> $data['drop_location'],
			'pickup_location'		=> $data['pickup_location'],
			'is_domestic'			=> strtolower($data['country_code']) === 'in',
			'country_code'			=> $data['country_code'] ?? '',
			'cod'					=> 0,
			'weight'				=> $data['weight'],
		];

		$couriers = [];

		$results = _get_vendor_rates($filter_data, $vendor);
		$results = !empty($results) ? $results : [];

		foreach ($results as $item) {
			$couriers[] = array_merge($item, [
				'vendor_name'	=> $vendor,
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate']['rate'] ?? $item['rate'],
			]);
		}

		return $couriers;
	}

	public function getCourierServiceability($order_id = 0, $order_type = 'book') {
		if ($order_type == 'medallion') {
			$order_info 	= $this->medallion_order_model->get($order_id);
			$address_info 	= $this->medallion_address_model->get($order_info['address_id']);
		} else if ($order_type == 'school') {
			$order_info 	= $this->school_order_model->get($order_id);
			$address_info 	= $this->school_model->getSchoolAddress($order_info['school_id']);
		} else {
			$order_info 	= $this->order_model->get($order_id);
			$address_info 	= $this->address_model->getByID($order_info['address_id']);
		}

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
			'country_code'			=> substr($country_info['country_code'] ?? '', 0, 2),
			'cod'					=> 0,
			'weight'				=> $order_info['weight'],
			'order_type'			=> $order_type
		];

		$couriers = [];

		# Shiprocket service availability
		$results = _get_vendor_rates($filter_data, 'shiprocket') ?? [];

		log_kb([
			'Shiprocket service availability' => $results
		]);

		foreach ($results as $item) {
			if (in_array(trim($item['courier_name']), BLOCKED_COURIERS)) continue;

			$couriers['shiprocket'][] = array_merge($item, [
				'vendor_name'	=> 'shiprocket',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate']['rate'] ?? $item['rate'],
			]);
		}

		# BeingShips service availability
		// $results = _get_vendor_rates($filter_data, 'beingships') ?? [];
		// $results = !empty($results) ? $results : [];
		//
		// log_kb([
		// 	'BeingShips Partners' => $results
		// ]);

		// foreach ($results as $item) {
		// 	if (in_array(trim($item['courier_name']), BLOCKED_COURIERS)) continue;
		//
		// 	$couriers['beingships'][] = array_merge($item, [
		// 		'vendor_name'	=> 'beingships',
		// 		'courier_name'	=> $item['name'],
		// 		'courier_id'	=> $item['id'] ?? $item['courier_id'],
		// 		'rate'			=> $item['rate'] ?? $item['total_charge'],
		// 	]);
		// }

		# Aramex service availability
		$results = _get_vendor_rates($filter_data, 'aramex') ?? [];
		$results = !empty($results) ? $results : [];

		foreach ($results as $item) {
			$couriers['aramex'][] = array_merge($item, [
				'vendor_name'	=> 'aramex',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate'],
			]);
		}

		# Bluedart service availability
		// $results = _get_vendor_rates($filter_data, 'bluedart') ?? [];
		// $results = !empty($results) ? $results : [];
		//
		// foreach ($results as $item) {
		// 	$couriers['bluedart'][] = array_merge($item, [
		// 		'vendor_name'	=> 'bluedart',
		// 		'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
		// 		'rate'			=> $item['rate'],
		// 	]);
		// }

		# DTDC service availability
		$results = _get_vendor_rates($filter_data, 'dtdc') ?? [];
		$results = !empty($results) ? $results : [];
		foreach ($results as $item) {
			$couriers['dtdc'][] = array_merge($item, [
				'vendor_name'	=> 'dtdc',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate'],
			]);
		}

		# DTDC Business service availability
		$results = _get_vendor_rates($filter_data, 'dtdcb2b') ?? [];
		$results = !empty($results) ? $results : [];
		foreach ($results as $item) {
			$couriers['dtdcb2b'][] = array_merge($item, [
				'vendor_name'	=> 'dtdcb2b',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate'],
			]);
		}

		# Delhivery service availability
		$results = _get_vendor_rates($filter_data, 'delhivery') ?? [];
		$results = !empty($results) ? $results : [];

		log_kb([
			'Delhivery Partners' => $results
		]);

		foreach ($results as $item) {
			$couriers['delhivery'][] = array_merge($item, [
				'vendor_name'	=> 'delhivery',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate'],
			]);
		}

		# Gokwik service availability
		$results = _get_vendor_rates($filter_data, 'gokwik') ?? [];
		$results = !empty($results) ? $results : [];

		log_kb([
			'Gokwik Partners' => $results
		]);

		foreach ($results as $item) {
			$couriers['gokwik'][] = array_merge($item, [
				'vendor_name'	=> 'gokwik',
				'courier_id'	=> $item['id'] ?? $item['courier_company_id'],
				'rate'			=> $item['rate'],
			]);
		}

		log_kb(['GetCourierServiceability::couriers:' => $couriers]);

		return $couriers;
	}

	public function processOrderShipment($order_id = false, $courier_id = false, $pickup_location_id = false, $vendor = '', $order_type = 'book') {
		$this->error = '';

		log_kb([
			'method' => 'processOrderShipment'
		]);

		if (empty($order_id) || empty($courier_id) || empty($pickup_location_id))
			return;

		if (empty(_is_order_shippable($order_id, $order_type))) {
			$this->error = 'Unable to book order.';
			return false;
		}

		if ($order_type == 'medallion') {
			$order_info 	= $this->medallion_order_model->get($order_id);
			$address_info 	= $this->medallion_address_model->get($order_info['address_id']);
		} else if ($order_type == 'school') {
			$order_info 	= $this->school_order_model->get($order_id);
			$address_info 	= $this->school_model->getSchoolAddress($order_info['school_id']);
		} else {
			$order_info 	= $this->order_model->get($order_id);
			$address_info 	= $this->address_model->getByID($order_info['address_id']);
		}

		if (empty($order_info)) {
			$this->error = _l('invalid_order_id');
			return false;
		}

		if ($order_info['shipping_status'] != 0) {
			$this->error = _l('already_booked');
			return false;
		}

		if (empty($address_info)) {
			$this->error = _l('invalid_address');
			return false;
		}

		if ($pickup_location_id != $order_info['pickup_location_id']) {
			$this->error = _li('Invalid pickup location.');
			return false;
		}

		if (empty($pickup_location_info = $this->pickup_location_model->get($pickup_location_id))) {
			$this->error = _li('Invalid pickup location.');
			return false;
		}

		$country_info = $this->db->get_where('delivery_country', [
			'name'	=> $address_info['country']
		])->row_array();

		$filter_data = [
			'pickup_pincode'		=> $pickup_location_info['pincode'],
			'delivery_pincode'		=> $address_info['zipcode'],
			'drop_location'			=> $address_info,
			'pickup_location'		=> $pickup_location_info,
			'is_domestic'			=> strtolower($address_info['country']) === 'india',
			'country_code'			=> substr($country_info['country_code'] ?? '', 0, 2),
			'cod'					=> 0,
			'weight'				=> $order_info['weight'],
			'courier_id'			=> $courier_id,
		];
		$results = _get_vendor_rates($filter_data, $vendor) ?? [];

		$results = array_values(array_filter($results, function($item) use($courier_id) {
			return ($item['id'] ?? $item['courier_company_id']) == $courier_id;
		}));
		$shipping_info = $results[0] ?? [];

		log_kb([
			'processOrderShipment' 	=> 'processOrderShipment',
			'results' 				=> $results,
			'shipping_info' 		=> $shipping_info,
		]);

		log_kb(compact('courier_id', 'results', 'shipping_info'));

		if (empty($shipping_info['courier_company_id'])) {
			$this->error = _li('invalid_courier');
			return false;
		}

		if (!empty($shipment_info = $this->shipment_model->get_all([
			'order_id'				=> $order_id,
			'courier_id'			=> $shipping_info['courier_company_id'] ?? 0,
			'order_type'			=> $order_type,
		])['rows'][0] ?? [])) {
			$shipment_id = $shipment_info['id'];
		} else {
			$save = [
				'vendor_name'			=> $vendor,
				'order_id'				=> $order_id,
				'order_type'			=> $order_type,
				'user_id'				=> $order_info['user_id'],
				'courier_id'			=> $shipping_info['courier_company_id'] ?? 0,
				'pickup_location_id'	=> $pickup_location_id,
				'currency_code'			=> $order_info['currency_code'],
				'order_total_amount'	=> $order_info['total'],
				'shipped_by'			=> $this->session->userdata('user_id') ?? ''
			];

			// don't add if exists shipment
			$shipment_id = $this->shipment_model->add($save);
		}

		$response = [];

		if (!empty($shipment_id)) {
			if (empty($response = self::generateAwb($shipment_id, $order_type, $courier_id))) {
				$this->error = $this->error ?? 'Unable to generate AWB';
				return false;
			}
		}

		log_kb([
			'processOrderShipment' 	=> 'processOrderShipment',
			'generateAwb' 			=> $response,
		]);

		if ($order_type == 'medallion') {
			$this->medallion_order_model->editById($order_id, [
				'shipping_info'			=> json_encode($shipping_info + [
					'bb_shipment_id'	=> $shipment_id
				])
			]);
		} elseif ($order_type == 'school') {
			$this->school_order_model->editById($order_id, [
				'shipping_info'			=> json_encode($shipping_info + [
					'bb_shipment_id'	=> $shipment_id
				])
			]);
		} else {
			$this->order_model->editById($order_id, [
				'shipping_info'			=> json_encode($shipping_info + [
					'bb_shipment_id'	=> $shipment_id
				])
			]);
		}

		return $response;
	}

	public function generateAwb($shipment_id = false, $order_type = 'book', $courier_id = 0) {
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
			$this->error = _li('Error:: awb is existing.');
			return false;
		}

		if ($order_type == 'medallion') {
			$order_info 	= $this->medallion_order_model->get($shipment_info['order_id']);
			$address_info 	= $this->medallion_address_model->get($order_info['address_id'] ?? 0);
			$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
		} else if ($order_type == 'school') {
			$order_info 	= $this->school_order_model->get($shipment_info['order_id']);
			$address_info 	= $this->school_model->getSchoolAddress($order_info['school_id'] ?? 0);
			$user_info 		= $address_info;
		} else {
			$order_info 	= $this->order_model->get($shipment_info['order_id']);
			$address_info 	= $this->address_model->getByID($order_info['address_id'] ?? 0);
			$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
		}

		if (empty($order_info)) {
			$this->error = 'Invalid order.';
			return false;
		}

		if ($order_info['shipping_status'] != 0) {
			$this->error = _li('Error:: shipping status found.');
			return false;
		}

		if (empty($user_info)) {
			$this->error = 'Invalid user.';
			return false;
		}

		if (empty($address_info)) {
			$this->error = 'Invalid address.';
			return false;
		}

		if (empty($pickup_location_info = $this->pickup_location_model->get($order_info['pickup_location_id']))) {
			$this->error = 'Invalid pickup location.';
			return false;
		}

		$type = (strtolower($address_info['country']) === 'india') ? '' : 'international';

		if ($order_type == 'medallion') {
			$products = $this->medallion_order_model->getProducts($order_info['id']);
		} elseif ($order_type == 'school') {
			$products = $this->school_order_model->getProducts($order_info['id']);
		} else {
			$products = $this->order_model->getProducts($order_info['id']);

			$products = array_filter($products, function($item) {
				$option = json_decode($item['option'], true);
				return mb_strtolower($option['name']) != 'ebook';
			});
		}

		if (empty($products)) {
			$this->error = 'Invalid order.';
			return false;
		}

		$country_info = $this->db->get_where('delivery_country', [
			'name'	=> $address_info['country']
		])->row_array();

		$order_info['shipment_id'] = $shipment_id;

		if ($order_type == 'book') {
			// clone orders
			if (!empty($order_info['parent_order_id'])) {
				$parent_order_info = $this->order_model->get($order_info['parent_order_id']);

				$clone_orders_count = $this->order_clone_model->get_all([
					'parent_order_id' => $order_info['parent_order_id'],
				])['total'] ?? 0;

				$order_info['total'] = $parent_order_info['total'] / $clone_orders_count;
			}
		}

		$data = $order_info + [
			'order_id' 			=> $shipment_info['order_id'],
			'order_type' 		=> $shipment_info['order_type'],
			'products' 			=> $products,
			'user'				=> $user_info,
			'drop_location'		=> array_merge(
				$address_info,
				['country_code' => substr($country_info['country_code'] ?? '', 0, 2)]
			),
			'pickup_location' 	=> $pickup_location_info,
			'courier_id'		=> $courier_id,
		];

		if (strtolower($address_info['country']) !== 'india') {
			$data['total'] -= $data['shipping_cost'];
			$data['shipping_cost'] = 0;
		}

		log_kb([
			'generateAWB' 	=> 'processOrderShipment_generateAWB',
			'payload' 		=> $data,
		]);

		// book order
		if (empty($vendor_response = self::_callVendorEndpoint(
			$data,
			$shipment_info['vendor_name'],
			'bookOrder'
		))) {
			if ($this->error) {
				$this->shipment_model->edit($shipment_id, ['message' => $this->error]);
				return;
			}
		}

		if (empty($awb_response = self::_callVendorEndpoint(
			[
				'shipment_id' 	=> $vendor_response['shipment_id'],
				'courier_id' 	=> $shipment_info['courier_id']
			],
			$shipment_info['vendor_name'],
			'generateAWB'
		))) {
			if ($this->error) {
				$this->shipment_model->edit($shipment_id, ['message' => $this->error]);
				return;
			}
		}

		if (empty($pickup_response = self::_callVendorEndpoint(
			$vendor_response['shipment_id'],
			$shipment_info['vendor_name'],
			'generatePickup'
		))) {
			$this->error && $this->shipment_model->edit($shipment_id, ['message' => $this->error]);
		}

		if (empty($pickup_response) && !empty($vendor_response['pickup'])) {
			$pickup_response = $vendor_response['pickup'];
		}

		log_kb([
			'vendor_response::' => $vendor_response,
			'awb_response::' 	=> $awb_response,
			'pickup_response::' => $pickup_response
		]);

		$api_response = [];

		if (isset($vendor_response['api_response'])) {
			$api_response = $vendor_response['api_response'];
		} elseif (isset($awb_response['api_response'])) {
			$api_response = $awb_response['api_response'];
			unset($awb_response['api_response']);
		}

		if (!empty($pickup_response['routing_code'])) {
			$awb_response['routing_code'] = $pickup_response['routing_code'];
		}

		$this->shipment_model->edit($shipment_id, [
			'courier_order_id'		=> $vendor_response['order_id'] ?? '',
			'courier_shipment_id'	=> $vendor_response['shipment_id'] ?? '',
			'awb_number'			=> $awb_response['awb_number'] ?? ($vendor_response['awb_code'] ?? ''),
			'shipment_info' 		=> !empty($awb_response) ? json_encode($awb_response) : '',
			'shipping_tracking_info'=> json_encode($api_response),
			'status'				=> 1
		]);

		if (empty($vendor_response['awb_code']) && !empty($awb_response['awb_number'])) {
			$vendor_response['awb_code'] = $awb_response['awb_number'];
		}

		$log_history_data = [];

		if ($order_type == 'medallion') {
			$model_name  		= 'medallion_order_model';
			$packing_log_model  = 'medallion_order_packing_log_model';
			$history_model  	= 'medallion_order_history_model';

			$log_history_data['medallion_order_id'] = $order_info['id'];
		} elseif ($order_type == 'school') {
			$model_name  		= 'school_order_model';
			$packing_log_model  = 'school_order_packing_log_model';
			$history_model  	= 'school_order_history_model';

			$log_history_data['school_order_id'] = $order_info['id'];
		} else {
			$model_name  		= 'order_model';
			$packing_log_model  = 'order_packing_log_model';
			$history_model  	= 'order_history_model';

			$log_history_data['order_id'] = $order_info['id'];
		}

		if (!empty($awb_response['assigned_date_time'])) {
			$this->{$model_name}->editById($order_info['id'], [
				'status'					=> 9,
				'shipping_status' 			=> 1,
				'shipping_tracking_info' 	=> json_encode($vendor_response),
			]);

			if ($order_type == 'medallion') {
				$this->db->update('medallion_order', [
					'status'					=> 9,
					'shipping_status' 			=> 1,
				], [
					'parent_id'			=> (int)$order_info['id']
				]);
			}
		} elseif (!empty($vendor_response['awb_code'])) {
			$this->{$model_name}->editById($order_info['id'], [
				'status'					=> 9,
				'shipping_status' 			=> 1,
				'shipping_tracking_info' 	=> json_encode($vendor_response),
			]);

			if ($order_type == 'medallion') {
				$this->db->update('medallion_order', [
					'status'					=> 9,
					'shipping_status' 			=> 1,
				], [
					'parent_id'			=> (int)$order_info['id']
				]);
			}

			$this->error = '';
			$awb_response['awb_number'] = $vendor_response['awb_code'];

			$this->shipment_model->edit($shipment_id, ['message' => 'Already Booked.']);
		} else {
			$this->{$model_name}->editById($order_info['id'], [
				'shipping_tracking_info' 	=> json_encode($vendor_response),
			]);
		}

		if (!empty($pickup_response)) {
			$this->pickup_data_model->add([
				'shipment_id'			=> (int)$shipment_id,
				'courier_shipment_id'	=> $vendor_response['shipment_id'],
				'pickup_location_id'	=> (int)$order_info['pickup_location_id'],
				'scheduled_date'		=> $pickup_response['scheduled_date'],
				'token_number'			=> $pickup_response['token_number'],
				'remark'				=> $pickup_response['remark'],
				'scheduled_timestamp'	=> $pickup_response['scheduled_timestamp'],
				'status'				=> $pickup_response['pickup_status'],
			]);
		}

		$this->{$packing_log_model}->add($log_history_data + [
			'user_id'	=> (int)$this->session->userdata('user_id')
		]);

		$this->{$history_model}->add($log_history_data + [
			'description' 	=> _li('Your order has been prepared and is ready to ship'),
			'status' 		=> 9,
		]);

		return $awb_response;
	}

	public function generateLabel($order_id = 0, $format = 'thermal', $order_type = 'book') {
		ini_set('pcre.backtrack_limit', '5000000');

		$order_shipment_data = [];

		if ($order_type == 'medallion') {
			$order_info 	= $this->medallion_order_model->get($order_id);
		} else if ($order_type == 'school') {
			$order_info 	= $this->school_order_model->get($order_id);
		} else {
			$order_info 	= $this->order_model->get($order_id);
		}

		if (empty($order_info)) return;
		if (empty($order_info['shipping_status'])) return;
		if (empty($shipment_info = self::getShipmentData($order_id, $order_type))) return

		$order_shipment_data[] 	= $shipment_info;

		if (!empty($response = self::_callVendorEndpoint(
			$shipment_info->shipment->id,
			$shipment_info->shipment->vendor_name,
			'generateLabel'
		))) {
			if (!empty($response['label_url'])) {
				header('Location: ' . $response['label_url']);
				exit;
			}
		} else {
			if ($this->error) {
				exit($this->error);
			}
		}

		$html = $this->load->view('backend/admin/order/order_label', [
			'shipments' => array($shipment_info),
			'format' 	=> $format
		], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->set_paper([0, 0, 296, 450]);

		$dompdf->render();

		$file = 'uploads/label/' . date('Y-m-d') . '-' . @$ids[0] . '.pdf';

		$dompdf->stream($file);
	}

	public function getShipmentData($order_id = false, $order_type = 'book') {
		if ($order_type == 'medallion') {
			$order_info 	= $this->medallion_order_model->get($order_id);
			$address_info 	= $this->medallion_address_model->get($order_info['address_id'] ?? 0);
			$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
			$products 		= $this->medallion_order_model->getProducts($order_id);
		} elseif ($order_type == 'school') {
			$order_info 	= $this->school_order_model->get($order_id);
			$address_info 	= $this->school_model->getSchoolAddress($order_info['school_id'] ?? 0);
			$user_info 		= $address_info;
			$products 		= $this->school_order_model->getProducts($order_id);
		} else {
			$order_info 	= $this->order_model->get($order_id);
			$address_info 	= $this->address_model->getByID($order_info['address_id'] ?? 0);
			$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
			$products 		= $this->order_model->getProducts($order_id);
		}

		log_kb([
			'getShipmentData::order' => $order_info
		]);

		if (empty($order_id))
			return false;

		if (empty($order_info)) {
			return false;
		}

		if ($order_info['shipping_status'] == '0' || empty($order_info['shipping_info'])) {
			return false;
		}

		if (empty($user_info)) {
			return false;
		}

		if (empty($products)) {
			return false;
		}

		if (empty($address_info)) {
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

		$order = new stdClass();
		$order->order_id = $order_info['order_code'];
		$order->order_date = strtotime($order_info['date_added']);
		$order->shipping_fname = $address_info['name'];
		$order->shipping_lname = '';
		$order->shipping_company_name = '';
		$order->shipping_address = $address_info['address'];
		$order->shipping_address_2 = $address_info['landmark'] ?? '';
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
		$order->package_length = $order_info['length'] ?? '10';
		$order->package_height = $order_info['height'] ?? '10';
		$order->package_breadth = $order_info['breadth'] ?? '10';
		$order->channels_brand_logo = '';
		$order->currency_code = $order_info['currency_code'];
		$order->currency_symbol = $order_info['currency_symbol'];

		$product_arr = [];

		foreach ($products as $product) {
			$product_data = [];

			if ($order_type == 'book') {
				$book_info = $this->book_version_model->getByVersion($product['product_id'], $product['version']);

				if (empty($book_info)) continue;

				$option = json_decode($product['option'], true);

				$product_data = [
					'product_name'		=> $book_info['name'] ?? '',
					'product_sku'		=> _o_b_code($book_info['book_id'], $book_info['version'], $option['name']),
					'product_qty'		=> (int)$product['quantity'],
					'product_price'		=> $product['price'],
				];
			} else {
				$product_data = [
					'product_name'		=> $product['name'] ?? '',
					'product_sku'		=> '',
					'product_qty'		=> (int)$product['quantity'],
					'product_price'		=> $product['total'],
				];
			}

			$product_arr[] = (object) $product_data;
		}

		$products = (object)$product_arr;

		$courier_shipment_info = !empty($shipment_info['shipment_info']) ? json_decode($shipment_info['shipment_info'], 1) : [];

		$courier_info = $this->courier_model->get_all(['carrier_id' => $shipment_info['courier_id']])['rows'] ?? [];

		$shipment = new stdClass();
		$shipment->id = $shipment_info['courier_shipment_id'];
		$shipment->vendor_name = $shipment_info['vendor_name'];
		$shipment->awb_number = $shipment_info['awb_number'];
		$shipment->routing_code = $courier_shipment_info['routing_code'] ?? '';
		$shipment->shipment_info_1 = '';
		$shipment->is_rto_different = '';
		$shipment->shipment_date = strtotime($shipment_info['date_added']);

		$courier = new stdClass();
		$courier->id = $courier_shipment_info['id'] ?? 0;
		$courier->code = $courier_shipment_info['courier_company_id'] ?? 0;
		$courier->carrier_id = $courier_shipment_info['courier_company_id'] ?? 0;
		$courier->carrier_code = $courier_shipment_info['courier_company_id'] ?? 0;
		$courier->display_name = $courier_shipment_info['courier_name'] ?? $courier_info[0]['display_name'];
		$courier->vendor_name = $shipment_info['vendor_name'] ?? 0;

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
		$warehouse->gst_number = $pickup_location_info['gst_number'];
		$warehouse->support_phone = $pickup_location_info['mobile'];
		$warehouse->support_email = $pickup_location_info['email'];
		$warehouse->hide_label_products = '';
		$warehouse->hide_label_address = '';
		$warehouse->hide_label_pickup_mobile = '';
		$warehouse->logo = base_url('assets/images/logo-outline-black.png');

		$rto_warehouse = $warehouse;

		$user = new stdClass();
		$user->id = '';
		$user->support_category = '';

		$company = new stdClass();
		$company->cmp_logo = '';

		$channel_brand_logo = [];

		$return = [
			'order' 				=> $order,
			'products'				=> $products,
			'shipment'				=> $shipment,
			'courier'				=> $courier,
			'warehouse'				=> $warehouse,
			'rto_warehouse'			=> $rto_warehouse,
			'company'				=> $company,
			'user'					=> $user,
			'channels_brand_logo' 	=> (object)$channel_brand_logo
		];

		return (object)$return;
	}

	public function generateInvoice($order_id = 0, $order_type = 'book') {
		if ($order_type == 'medallion') {
			$order_info 	= $this->medallion_order_model->get($order_id);
			$address_info 	= $this->medallion_address_model->get($order_info['address_id'] ?? 0);
			$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
			$products 		= $this->medallion_order_model->getProducts($order_info['id']);
		} elseif ($order_type == 'school') {
			$order_info 	= $this->school_order_model->get($order_id);
			$address_info 	= $this->school_model->getSchoolAddress($order_info['school_id'] ?? 0);
			$user_info 		= $address_info;
			$products 		= $this->school_order_model->getProducts($order_info['id']);
		} else {
			$order_info 	= $this->order_model->get($order_id);
			$address_info 	= $this->address_model->getByID($order_info['address_id'] ?? 0);
			$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
			$products 		= $this->order_model->getProducts($order_info['id']);
		}

		if (empty($order_info)) return;
		if (empty($user_info)) return;
		if (empty($address_info)) return;

		$shipping_info = json_decode($order_info['shipping_info'], true);

		if (empty($shipment_info = $this->shipment_model->get($shipping_info['bb_shipment_id']))) return;

		if (!empty($response = self::_callVendorEndpoint(
			$shipment_info['courier_order_id'],
			$shipment_info['vendor_name'],
			'generateInvoice'
		))) {
			if (!empty($response['invoice_url'])) {
				header('Location: ' . $response['invoice_url']);
				exit;
			}
		} else {
			if ($this->error) {
				exit($this->error);
			}
		}

		// clone orders
		if (!empty($order_info['parent_order_id'])) {
			$parent_order_info = $this->order_model->get($order_info['parent_order_id']);

			$clone_orders_count = $this->order_clone_model->get_all([
				'parent_order_id' => $order_info['parent_order_id'],
			])['total'] ?? 0;

			$order_info['total'] = $parent_order_info['total'] / $clone_orders_count;
		}

		$data['order'] 		= $order_info;
		$data['address'] 	= $address_info;
		$data['products'] 	= $products;
		// $data['products'] 	= $this->order_model->getProducts($order_info['id']);

		$shipping_tracking_info = !empty($order_info['shipping_tracking_info'])
			? json_decode($order_info['shipping_tracking_info'], 1)
			: []
		;

		$data['awb_number'] = $shipping_tracking_info['awb_code'] ?? '';

		if ($order_type == 'school') {
			$html = $this->load->view('common/invoice/doc_invoice_order_print', $data, true);
		} else {
			$html = $this->load->view('common/invoice/invoice_order_print', $data, true);
		}

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->set_paper([0, 0, 296, 450]);

		$dompdf->render();

		$file 	= 'invoice_' . $order_info['order_code'] . '_' . date('Y_m_d_H_i_s', strtotime($order_info['date_added'])) . '.pdf';

		$dompdf->stream($file);
	}

	public function generateManifest($order_ids = false) {
		if (empty($order_ids))
			return false;

		$data['orders'] 		= [];

		$html = $this->load->view('common/invoice/manifest_order_print', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->set_paper([0, 0, 296, 450]);

		$dompdf->render();

		$file 	= 'manifest_' . date('Y_m_d_H_i_s', strtotime($order_info['date_added'])) . '.pdf';

		$dompdf->stream($file);
	}

	public function trackOrder($shipment_id = 0) {
		if (empty($shipment_info = $this->shipment_model->get($shipment_id))) return;

		if (!empty($response = self::_callVendorEndpoint(
			$shipment_info['courier_order_id'],
			$shipment_info['vendor_name'],
			'trackingDeatil'
		))) {
			return $response;
		}

		return false;
	}

	public function trackingUrl($shipment_id = 0) {
		if (empty($shipment_info = $this->shipment_model->get($shipment_id))) return;

		if (!empty($response = self::_callVendorEndpoint(
			$shipment_info['awb_number'],
			$shipment_info['vendor_name'],
			'trackingUrl'
		))) {
			return $response;
		}

		return false;
	}

	private function _callVendorEndpoint($data = '', $vendor_name = '', $vendor_method = '') {
		$this->error = '';

		log_kb(compact(['data', 'vendor_name', 'vendor_method']));

		$vendor_class	= ucfirst($vendor_name);
		$vendor_object	= strtolower($vendor_name);

		$this->load->library(sprintf('couriers/%s', $vendor_class));

		$response = false;

		if ((new ReflectionClass($vendor_class))->hasMethod($vendor_method)) {
			$response = $this->CI->{$vendor_object}->{$vendor_method}($data);
		}

		$this->error = $this->CI->{$vendor_object}->error ?? '';

		log_kb([
			'_callVendorEndpoint' => [$response , $this->error]
		]);

		return $response;
	}
}
