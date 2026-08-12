<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/couriers/BaseCourier.php';

final class Delhivery extends BaseCourier {
	public function __construct() {
		$this->api_url 	= ENVIRONMENT === 'production'
			? 'https://track.delhivery.com/api/'
			: 'https://staging-express.delhivery.com/api/';

		$this->api_key 	= ENVIRONMENT === 'production'
			? ''
			: '';

		$this->username 	= '';
		$this->password		= '';
		$this->warehouse	= '';

		$this->CI 		= &get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;

		$this->load->model('shipping/Courier_model');

		$this->courier_model = $this->CI->Courier_model;

		$this->courier = $this->courier_model->get_all([
			'status'		=> 1,
			'vendor_name'	=> 'delhivery',
			'is_domestic'	=> '1',
		])['rows'][0] ?? [];
	}

	public function cleanMobile($string = '') {
		return preg_replace('/[^\d]/', '', trim($string)); // Removes special chars.
	}

	public function getRates($data = [], $type = '') {
		if (empty($data)) return false;

		$response = self::_curl(sprintf('kinko/v1/invoice/charges/.json?md=S&ss=Delivered&d_pin=%s&o_pin=%s&cgm=%s&pt=Pre-paid',
			(int)($data['drop_location']['zipcode'] ?? ''),
			(int)($data['pickup_location']['pincode'] ?? ''),
			$data['weight']
		), [], 'GET');

		log_kb([
			'Delhivery::getRates::response' => $response
		]);

		$rates  = [];

		if (!empty($this->courier) && !empty($response[0]['status'])) {
			$rates[] = [
				'id'				=> $this->courier['id'],
				'name'				=> $this->courier['name'],
				'courier_name'		=> $this->courier['name'],
				'courier_id'		=> $this->courier['carrier_id'],
				'courier_company_id'=> $this->courier['carrier_id'],
				'rate'				=> $response[0]['total_amount'] ?? 0
			];

			return $rates;
		} else {
			$this->error = $response['error'] ?? 'Something went wrong in rate!';
		}

		return false;
	}

	public function bookOrder($data = []) {
		if (empty($data)) return false;

		if ($data['shipping_status'] != 0) return false;

		$order_items 			= [];
		$products_desc_array 	= [];
		$products_desc 			= '';

		$total_quantity 		= 0;

		if (!empty($data['products'])) {
			foreach ($data['products'] as $key => $product) {
				$total_quantity = ($total_quantity + ($product['quantity'] ?? 1));

				$products_desc_array[] = vsprintf('%s v%s (SKU %s) x %s', [
					$product['name'],
					$product['version'] ?? 1,
					$product['product_id'],
					($product['quantity'] ?? 1)
				]);
			}

			$products_desc = implode(' || ', $products_desc_array);

		}

		if (mb_strtolower($data['currency_code']) !== 'inr' || strtolower($data['drop_location']['country']) != 'india') {
			$mobile = removeIsdCode(self::cleanMobile($data['drop_location']['mobile']), $data['drop_location']['country']);
		} else {
			$mobile = self::cleanMobile(!empty($data['drop_location']['mobile'])
				? substr($data['drop_location']['mobile'], -10)
				: substr($data['user']['mobile'], -10))
			;
		}

		$payload = http_build_query([
			'format' 				=> 'json',
			'data' 					=> json_encode([
				'shipments' 		=> [
					[
						'name'              => !empty($data['drop_location']['name']) ? $data['drop_location']['name'] : $data['user']['first_name'],
						'add'               => $data['drop_location']['address'] ?? '',
						'city'              => $data['drop_location']['city'] ?? '',
						'state'             => $data['drop_location']['state'] ?? '',
						'country'           => $data['drop_location']['country'] ?? '',
						'pin'               => (string)($data['drop_location']['zipcode'] ?? ''),
						'address_type'      => $data['drop_location']['type'] ?? '',
						'phone'             => $mobile ?? '',
						'order'             => $data['order_code'],
						'payment_mode'      => 'Prepaid',
						'return_pin'        => $data['pickup_location']['pincode'] ?? '',
						'return_city'       => $data['pickup_location']['city'] ?? '',
						'return_phone'      => $data['pickup_location']['mobile'] ?? '',
						'return_add'        => trim(($data['pickup_location']['address_1'] ?? '') . ' ' . ($data['pickup_location']['address_2'] ?? '')),
						'return_state'      => $data['pickup_location']['state'] ?? '',
						'return_country'    => $data['pickup_location']['country'] ?? 'India',
						'products_desc'     => $products_desc ?? '',
						'hsn_code'          => '',
						'cod_amount'        => '',
						'order_date'        => null,
						'total_amount'      => $data['total'] ?? '',
						'seller_add'        => trim(($data['pickup_location']['address_1'] ?? '') . ' ' . ($data['pickup_location']['address_2'] ?? '')),
						'seller_name'       => get_settings('system_name'),
						'seller_inv'        => '',
						'quantity'          => $total_quantity ?? '',
						'waybill'           => '',
						'shipment_width'    => $data['breadth'] ?? '10',
						'shipment_height'   => $data['height'] ?? '10',
						'shipment_length'   => $data['length'] ?? '10',
						'weight'            => $data['weight'] ?? '',
						'shipping_mode'     => 'Surface',
					]
				],
				'pickup_location' 	=> [
					'name' 			=> $data['pickup_location']['pickup_location_name'] ?? $this->warehouse
				]
			])
		]);

		$response = self::_curl('cmu/create.json', $payload, 'POST');

		log_kb([
			'Delhivery::bookOrder::response' => $response
		]);

		if (!empty($response['success']) && !empty($response['packages'])) {
			$awb_number = $response['packages'][0]['waybill'] ?? '';

			return [
				'pickup' => array_merge($response, [
					'token_number'			=> $response['packages'][0]['waybill'] ?? '',
					'remark'				=> $response['packages'][0]['status'] . ': ' . $awb_number,
					'pickup_status'		 	=> 1,
				]),
				'order_id' 		=> $awb_number,
				'order_code' 	=> $data['order_code'],
				'order_type' 	=> $data['order_type'],
				'shipment_id' 	=> $awb_number,
				'awb_number' 	=> $awb_number,
				'awb_code' 		=> $awb_number,
				'token_number' 	=> $response['packages']['upload_wbn'] ?? '',
				'api_response'	=> $response,
			];
		} else {
			$this->error = $response['message'] ?? '';
		}
	}

	public function generateAWB($data = []) {
	}

	public function generateLabel($shipment_id = false) {
		$this->CI 		= &get_instance();
		$this->load 	= $this->CI->load;

		$this->load->model('shipping/Shipment_model');
		$this->shipment_model = $this->CI->Shipment_model;

		$shipment_info = $this->shipment_model->get($shipment_id);

		log_kb([
			'Delhivery::generateLabel::shipment_info' => $shipment_info
		]);

		if (empty($shipment_info)) return false;
		if (empty($shipment_info['awb_number'])) return false;

		$response = self::_curl(sprintf('p/packing_slip?wbns=%s&pdf=true&pdf_size=4R',
			$shipment_info['awb_number']
		), [], 'GET');

		log_kb([
			'Delhivery::generateLabel::response' => $response
		]);

		if (empty($response['packages'])) {
			$this->error = $this->api_error = 'Unable to generate label';
			return false;
		}

		$result['status'] 		= true;
		$result['label_url']	= '';

		if (!empty($response['packages'])) {
			$result['label_url'] 	= $response['packages'][0]['pdf_download_link'] ?? '';
		}

		return $result;
	}

	public function generatePickup($shipment_id = false) {
	}

	public function generateInvoice($shipment_ids = false) {
	}

	public function generateManifests($order_ids = false) {
	}

	public function cancelOrder($order_ids = false) {
	}

	public function cancelShipment($awbs = false) {
	}

	public function fetchAWB($order_id = false) {
	}

	public function trackingDeatil($awb_number = false) {
		if (empty($awb_number)) return false;

		log_kb([
			'Delhivery::trackingDeatil::awb_number' => $awb_number
		]);

		$response = self::_curl(sprintf('v1/packages/json/?waybill=%s&ref_ids=',
			$awb_number
		), [], 'GET');

		log_kb([
			'Delhivery::trackingDeatil::response' => $response
		]);

		if (!empty($response['ShipmentData'][0]['Shipment']['Status'] ?? [])) {
			if (strtolower($response['ShipmentData'][0]['Shipment']['Status']['Status']) == 'delivered') {
				return [
					'order_status'	=> 4,
					'shipment_info'	=> [
						'awb_code'	=> $response['ShipmentData'][0]['Shipment']['AWB'],
						'status'	=> $response['ShipmentData'][0]['Shipment']['Status']['Status'] ?? '',
					]
				];
			} elseif (strtolower($response['ShipmentData']['Shipment']['Status']['Status']) == 'rto') {
				return [
					'order_status'	=> 15,
					'shipment_info'	=> [
						'awb_code'	=> $response['ShipmentData'][0]['Shipment']['AWB'],
						'status'	=> $response['ShipmentData'][0]['Shipment']['Status']['Status'] ?? '',
					]
				];
			}
			return $response;
		} else {
			$this->error = $response['error'] ?? '';
		}
	}

	public function trackingUrl($awb_number = 0) {
		return sprintf('https://www.delhivery.com/track-v2/package/%s', $awb_number);
	}

	protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		if (is_array($data)) {
			$data = json_encode($data);
		}

		log_kb(['Delhivery::_curl::request:: ' => [
			'endpoint'		=> $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		$headers = [
			'Content-Type: application/json',
			'Authorization: Token ' . $this->api_key,
		];

		$ch = curl_init();

		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt_array($ch, [
			CURLOPT_URL 			=> $this->api_url . $endpoint,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> '',
			CURLOPT_MAXREDIRS 		=> 10,
			CURLOPT_TIMEOUT 		=> 30,
			CURLOPT_SSL_VERIFYHOST 	=> 0,
			CURLOPT_SSL_VERIFYPEER 	=> 0,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> $method,
			CURLOPT_HTTPHEADER 		=> $headers,
		]);

		$response = curl_exec($ch);
		$err = curl_error($ch);

		curl_close($ch);

		if (!empty($err)) {
			$this->error = $this->api_error = 'Error in API Request.';
			return false;
		}

		log_kb(['Delhivery::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->error,
		]]);

		return json_decode($response, true);
	}
}
