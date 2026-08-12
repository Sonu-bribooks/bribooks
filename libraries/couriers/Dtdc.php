<?php defined('BASEPATH') or exit('No direct script access allowed');

final class Dtdc {
	private $api_url;
	private $customer_code;
	private $api_key;
	public $error;
	protected $courier_type = DTDC_COURIERS;

	public function __construct() {
		if (ENVIRONMENT === 'production') {
			$this->api_url = 'https://dtdcapi.shipsy.io/api/';
		} else {
			$this->api_url = 'https://demodashboardapi.shipsy.in/api/';
		}

		$this->customer_code = ENVIRONMENT === 'production'
			? 'LL1661'
			: 'GL017'
		;

		$this->api_key = ENVIRONMENT === 'production'
			? ''
			: ''
		;

		$this->CI 		= &get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;

		$this->load->model('shipping/Courier_model');

		$this->courier_model = $this->CI->Courier_model;

		$this->courier = $this->courier_model->get_all([
			'status'		=> 1,
			'vendor_name'	=> 'dtdc',
			'carrier_id'	=> 0,
			'is_domestic'	=> '1',
		])['rows'] ?? [];
	}

	public function getRates($data = [], $type = '') {
		if (empty($data)) return 0;

		$rates  = [];

		foreach ($this->courier as $courier) {
			$rates[] = [
				'id'				=> $courier['id'],
				'name'				=> $courier['name'],
				'courier_name'		=> $courier['name'],
				'courier_id'		=> $courier['carrier_id'],
				'courier_company_id'=> $courier['carrier_id'],
				'rate'				=> self::_calculateRate(
					$data['delivery_pincode'],
					$data['weight'],
					$data['order_type'] ?? 'book',
					$courier['name'] == $this->courier_type[0] ? 'priority_zone_wise.csv' : 'surface_zone_wize.csv'
				)
			];
		}

		return $rates;
	}

	private function _calculateRate($pincode = '', $weight = 0, $order_type = 'book', $csv_name) {
		if (empty($pincode)) return 0;

		$this->CI 	= &get_instance();
		$this->load = $this->CI->load;

		$this->load->library('parsecsv');

		$this->parsecsv = $this->CI->parsecsv;

		if ((in_array($order_type, ['school', 'medallion']))) {
			$this->parsecsv->auto('assets/csv/courier/'. $csv_name);
		} else {
			$this->parsecsv->auto('assets/csv/courier/'. $csv_name);
		}

		$rows 		= $this->parsecsv->data;
		$rate_data 	= [];

		foreach ($rows as $item) {
			if ($item['PINCODE'] == trim($pincode)) {
				$rate_data[] = $item;
			}
		}

		if (empty($rate_data)) return 0;

		$rate_info 	= array_values($rate_data)[0] ?? [];
		$rate 		= 0;

		if (!empty($rate_info)) {
			if ($weight >= 10000) {
				// Weight slab for weights 10kg and above
				$weight_slab	= ceil($weight / 10000);
				$base_price	 	= $weight_slab * $rate_info['rate_3'] ?? 0;
			} elseif ($weight >= 3000) {
				// Weight slab for weights between 3kg and 10kg
				$weight_slab	= ceil($weight / 3000);
				$base_price	 	= $weight_slab * $rate_info['rate_2'] ?? 0;
			} else {
				// Weight slab for weights below 3kg
				$weight_slab	= ceil($weight / 500);
				$base_price	 	= 0;

				$slab_array 	= range(1, $weight_slab);

				foreach ($slab_array as $index => $slab) {
					if ($index == 0) {
						$base_price += $rate_info['rate_1'] ?? 0;
					} else {
						$base_price += $rate_info['rate_1_add'] ?? 0;
					}
				}
			}

			// Calculate GST and total rate
			$gst_amount = ($base_price * $rate_info['gst']) / 100;
			$rate	   	= $gst_amount + $base_price;
		}

		return $rate;
	}

	public function bookOrder($data = []) {
		if (empty($data)) return false;

		$courier_id	 	= $data['courier_id'];
		$courier_info   = $this->courier_model->get($courier_id) ?? '';

		if ($data['order_type'] == 'school') {
			$drop_location_name 	= (!empty($data['user']['authorized_person']) ? ($data['user']['authorized_person']) : 'The Principal');
			$address_1 				= $data['drop_location']['name'] . ', ' . $data['drop_location']['address'];
		} else {
			$drop_location_name 	= $data['drop_location']['name'];
			$address_1 				= $data['drop_location']['address'];
		}

		$service_type = '';

		if (isset($courier_info['name']) && $courier_info['name'] == $this->courier_type[0]) {
			$service_type = 'B2C PRIORITY';
		} elseif (isset($courier_info['name']) && $courier_info['name'] == $this->courier_type[1]) {
			$service_type = 'B2C SMART EXPRESS';
		} else {
			return false;
		}

		if (isset($courier_info['name']) && $courier_info['name'] == $this->courier_type[0]) {
			$service_type = 'B2C PRIORITY';
		} elseif (isset($courier_info['name']) && $courier_info['name'] == $this->courier_type[1]) {
			$service_type = 'B2C SMART EXPRESS';
		} else {
			return false;
		}

		$descriptions = array_map(function($value) {
			$parts = [];

			if (!empty($value['name'])) {
				$parts[] = $value['name'];
			}

			if (!empty($value['version'])) {
				$parts[] = $value['version'];
			}

			if (!empty($value['product_id'])) {
				$parts[] = 'sku( ' . $value['product_id'] . ')-';
			}

			if (isset($value['quantity'])) {
				$parts[] = 'qty(' . ($value['quantity'] ?? 1) . ')';
			}

			return implode('-', $parts);
		}, $data['products']);

		$payload = ['consignments' => [[
			'customer_code'	 	=> $this->customer_code,
			'service_type_id'   => $service_type,
			'load_type'		 	=> 'NON-DOCUMENT',
			'consignment_type'  => 'Forward',
			'description'	   	=> $descriptions[0] . '-(' . $data['order_code'] ?? '' . ')',
			'dimension_unit'	=> 'cm',
			'length'			=> !empty($data['length']) ? $data['length'] : '10',
			'width'				=> !empty($data['breadth']) ? $data['breadth'] : '10',
			'height'			=> !empty($data['height']) ? $data['height'] : '10',
			'weight_unit'	   	=> 'kg',
			'weight'			=> round($data['weight'] / 1000, 2),
			'declared_value'	=> $data['total'],
			'num_pieces'		=> '1',
			'origin_details'	=> [
				'name'				=> $data['pickup_location']['name'],
				'phone'			 	=> $data['pickup_location']['mobile'],
				'alternate_phone'   => $data['pickup_location']['telephone'],
				'address_line_1'	=> $data['pickup_location']['address_1'],
				'address_line_2'	=> $data['pickup_location']['address_2'],
				'pincode'		   	=> $data['pickup_location']['pincode'],
				'city'			  	=> $data['pickup_location']['city'],
				'state'			 	=> $data['pickup_location']['state']
			],
			'destination_details' => [
				'name'					=> $drop_location_name,
				'phone'					=> $data['drop_location']['mobile'],
				'alternate_phone'		=> '',
				'address_line_1'		=> $address_1,
				'address_line_2'		=> $data['drop_location']['landmark'],
				'pincode'				=> $data['drop_location']['zipcode'],
				'city'					=> $data['drop_location']['city'],
				'state'					=> $data['drop_location']['state']
			],
			'customer_reference_number' => $data['id'],
			'commodity_id'			  => 'Books',
			'reference_number'		  => '',
		]]];

		$response = self::_curl(
			'customer/integration/consignment/softdata',
			$payload,
			'POST',
			$this->api_key
		);

		if (isset($response['error'])) {
			$this->error = $response['error']['message'] ?? 'Unknown error';
			return false;
		}

		if (isset($response['data']) && $response['data'][0]['success'] == false) {
			$this->error = $response['data'][0]['message'] ?? 'Unknown error';
			return false;
		}

		if ($response['data'][0]['success']) {
			if (!empty($awb_number = $response['data'][0]['reference_number'])) {
				$scheduled_pickup = date('Y-m-d 15:31:00', strtotime('+1 day'));

				return [
					'pickup' => array_merge($response['data'], [
						'scheduled_date'		=> $scheduled_pickup,
						'token_number'		  	=> $response['data'][0]['reference_number'] ?? '',
						'remark'				=> $response['status'] . ': ' . $awb_number,
						'scheduled_timestamp'   => strtotime($scheduled_pickup),
						'pickup_status'		 	=> 1,
					]),
					'order_id'	  	=> $data['order_id'],
					'order_code'	=> $data['order_code'],
					'order_type'	=> $data['order_type'],
					'shipment_id'   => $data['shipment_id'],
					'awb_number'	=> $awb_number,
					'awb_code'	  	=> $awb_number,
					'token_number'  => $response['data'][0]['reference_number'] ?? '',
					'api_response'  => $response,
				];
			} else {
				$this->error = 'Unable to create shipment';
				return false;
			}
		} else {
			$this->error = $response['data'][0]['message'] ?? 'Unable to create shipment';
			return false;
		}
	}

	public function generateLabel($shipment_id = false) {
		$this->CI 		= &get_instance();
		$this->load 	= $this->CI->load;

		$this->load->model('shipping/Shipment_model');
		$this->shipment_model = $this->CI->Shipment_model;

		$shipment_info = $this->shipment_model->get($shipment_id);

		if (empty($shipment_info)) return false;

		$payload = [
			'reference_number'  => $shipment_info['awb_number'],
		];

		$response = self::_curl(
			'customer/integration/consignment/shippinglabel/stream?reference_number=' . $shipment_info['awb_number'] . '&label_code=SHIP_LABEL_4X6&label_format=base64',
			$payload,
			'GET',
			$this->api_key
		);

		if (empty($response['HasErrors'])) {
			$base64_data 	= $response['label'];
			$pdf_data 		= base64_decode($base64_data);

			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="' . time() . '_label.pdf"');
			header('Content-Length: ' . strlen($pdf_data));

			echo $pdf_data;
			exit;
		} else {
			$this->error = $response['Message'];
		}
	}

	public function trackingUrl($awb_number = 0) {
		return sprintf('https://txk.dtdc.com/ctbs-tracking/customerInterface.tr?submitName=showCITrackingDetails&cType=Consignment&cnNo=%s', $awb_number);
	}

	protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		log_kb(['bluedart::_curl::request:: ' => [
			'endpoint' 		=> $this->api_url . $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		$headers = [
			'Content-Type: application/json'
		];

		if ($token) {
			$headers[] = 'api-key: ' . $this->api_key;
		}

		$ch = curl_init();

		if (!empty($data)) {
			$data = json_encode($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt($ch, CURLOPT_URL, $this->api_url . $endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);

		$err = curl_error($ch);
		curl_close($ch);

		if ($err) {
			$this->error = 'CURL Error: ' . $err;
			return false;
		}

		log_kb(['DTDC::_curl::response:: ' => [
			'endpoint'  => $endpoint,
			'response'  => $response,
			'error'	 	=> $this->error,
		]]);

		return json_decode($response, true);
	}
}
