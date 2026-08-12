<?php defined('BASEPATH') or exit('No direct script access allowed');

final class Bluedart {
	private $api_url;
	private $licence_key;
	private $tracking_licence_key;
	private $login_id;
	private $customer_code;
	private $area_code;
	private $version;
	private $tracking_api_version;

	private $api_type;
	public $error;
	protected $courier_type = BLUEDART_COURIERS;

	public function __construct() {
		if (ENVIRONMENT === 'production') {
			$this->api_url = 'https://apigateway.bluedart.com/in/transportation/';
		} else {
			$this->api_url = 'https://apigateway-sandbox.bluedart.com/in/transportation/';
		}

		$this->CI 		= &get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;

		$this->load->model('shipping/Courier_model');

		$this->courier_model = $this->CI->Courier_model;

		$this->courier = $this->courier_model->get_all([
			'status'		=> 1,
			'vendor_name'	=> 'bluedart',
			'carrier_id'	=> 0,
			'is_domestic'	=> '1',
		])['rows'] ?? [];

		$this->login_id = ENVIRONMENT === 'production'
			? ''
			: ''
		;

		$this->licence_key = ENVIRONMENT === 'production'
			? ''
			: ''
		;

		$this->api_type = ENVIRONMENT === 'production'
			? 'S'
			: 'S'
		;

		$this->customer_code = ENVIRONMENT === 'production'
			? '988551'
			: '988551'
		;

		$this->version = ENVIRONMENT === 'production'
			? '1.3'
			: '1.3'
		;

		$this->area_code = 'GGN';

		$this->account_number = ENVIRONMENT === 'production'
			? ''
			: ''
		;

		$this->product_types = ENVIRONMENT === 'production' ? [
			'GB'	=> 'GPX',
		] : [];
	}

	public function getRates($data = [], $type = '') {
		if (empty($data)) return false;

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
					$courier['name'] == $this->courier_type[0] ? 'air_etail_zone.csv' : 'dart_plus_zone.csv'
				)
			];
		}

		return $rates;
	}

	private function _calculateRate($pincode = '', $weight = 0, $order_type = 'book', $csv_name ='') {
		if (empty($pincode)) return 0;

		$this->load->library('parsecsv');

		$this->parsecsv = $this->CI->parsecsv;

		if ((in_array($order_type, ['school', 'medallion']))) {
			$this->parsecsv->auto('assets/csv/courier/KzUMyBvFDA7L5jo7/bluedart/' . $csv_name);
		} else {
			$this->parsecsv->auto('assets/csv/courier/KzUMyBvFDA7L5jo7/bluedart/' . $csv_name);
		}

		$rows = $this->parsecsv->data;

		$rate_data = array_filter($rows, function($item) use ($pincode) {
			return $item['PINCODE'] === trim($pincode);
		});

		$rate = 0;

		if (!empty($rate_data)) {
			$rate_info = array_values($rate_data)[0] ?? [];

			if (!empty($rate_info)) {
				$weight_slab = ($weight > 500) ? ceil($weight / 500) : 1;
				$rate = $weight_slab * $rate_info['PRICE'];
			}
		}

		return $rate;
	}

	public function bookOrder($data = []) {
		if (empty($data)) return false;

		$order_items = [];

		$pickup_date = (strtotime(date('Y-m-d 15:30:00', strtotime('+1 day'))) * 1000);

		$courier_id	 	= $data['courier_id'];
		$courier_info   = $this->courier_model->get($courier_id) ?? '';

		if (isset($courier_info['name']) && $courier_info['name'] == $this->courier_type[0]) {
			$pack_type = '';
		} elseif (isset($courier_info['name']) && $courier_info['name'] == $this->courier_type[1]) {
			$pack_type = 'L';
		} else {
			return false;
		}

		if (!empty($data['products'])) {
			foreach ($data['products'] as $key => $product) {
				$order_items[] = [
					'name' 			=> $product['name'] ?? '',
					'sku' 			=> $product['product_id'] ?? '',
					'quantity' 		=> $product['quantity'] ?? 1,
					'selling_price' => !empty($product['total']) ? round($product['total'] / ($product['quantity'] ?? 1), 2) : 0,
				];
			}
		}

		$payload = [
			'Request' => [
				'Consignee' => [
					'ConsigneeAddress1' 			=> $data['drop_location']['address'],
					'ConsigneeAddress2' 			=> $data['drop_location']['landmark'],
					'ConsigneeAddress3' 			=> $data['drop_location']['city'] . ' ,' . $data['drop_location']['state'],
					'ConsigneeMobile' 				=> $data['drop_location']['mobile'],
					'ConsigneeName' 				=> $data['drop_location']['name'],
					'ConsigneePincode' 				=> $data['drop_location']['zipcode'],
					'ConsigneeAttention' 			=> '',
					'ConsigneeEmailID' 				=> $data['user']['email'],
					'ConsigneeGSTNumbe' 			=> '',
					'ConsigneeLatitude' 			=> '',
					'ConsigneeLongitude' 			=> '',
					'ConsigneeMaskedContactNumber' 	=> ''
				],
				'Returnadds' => [
					'ReturnAddress1' 	=> $data['pickup_location']['address_1'],
					'ReturnAddress2' 	=> $data['pickup_location']['address_2'],
					'ReturnAddress3' 	=> $data['pickup_location']['address_3'],
					'ReturnPincode' 	=> $data['pickup_location']['pincode'],
					'ReturnMobile' 		=> $data['pickup_location']['mobile'],
					'ReturnContact' 	=> $data['pickup_location']['name'],
				],
				'Services' => [
					'ActualWeight' 			=> round($data['weight'] / 1000, 2),
					'CollectableAmount' 	=> '0',
					'SubProductCode' 		=> 'P',
					'Commodity' 			=> $order_items,
					'CreditReferenceNo' 	=> $data['id'],
					'DeclaredValue' 		=> $data['total'],
					'Dimensions' 			=> [
						'Breadth' 	=> !empty($data['breadth']) ? $data['breadth'] : '10',
						'Count' 	=> '1',
						'Height' 	=> !empty($data['height']) ? $data['height'] : '10',
						'Length' 	=> !empty($data['length']) ? $data['length'] : '10',
					],
					'PickupDate' 			=> '/Date(' . $pickup_date . ')/',
					'PickupTime' 			=> '1100',
					'PieceCount' 			=> '1',
					'ProductCode' 			=> 'A',
					'ProductType' 			=> 0,
					'PDFOutputNotRequired' 	=> true,
					'RegisterPickup' 		=> true,
					'PackType' 				=> $pack_type,
					'SpecialInstruction' 	=> '',
				],
				'Shipper' => [
					'CustomerAddress1' 	=> $data['pickup_location']['address_1'],
					'CustomerAddress2' 	=> $data['pickup_location']['address_2'],
					'CustomerAddress3' 	=> $data['pickup_location']['address_3'],
					'CustomerCode' 		=> $this->customer_code,
					'CustomerMobile' 	=> $data['pickup_location']['mobile'],
					'CustomerName' 		=> $data['pickup_location']['name'],
					'CustomerPincode' 	=> $data['pickup_location']['pincode'],
					'IsToPayCustomer' 	=> false,
					'OriginArea' 		=> $this->area_code,
					'Sender' 			=> $data['pickup_location']['name'],
					'VendorCode' 		=> $this->customer_code
				],
			],
			'Profile' => [
				'Api_type' 		=> $this->api_type,
				'LoginID' 		=> $this->login_id,
				'LicenceKey' 	=> $this->licence_key,
			]
		];

		log_kb([
			'BLUEDART::payload' => $payload
		]);

		$response = self::_curl(
			'waybill/v1/GenerateWayBill',
			$payload,
		);

		log_kb([
			'BLUEDART::response' => $response
		]);

		if (isset($response['status']) && $response['status'] == 400) {
			$this->error = $response['error-response'][0]['Status'][0]['StatusInformation'] ?? 'Bad request';
			return false;
		}

		if (empty($response['GenerateWayBillResult']) || empty($response['GenerateWayBillResult']['Status'][0])) {
			$this->error = 'Unable to create shipment';
			return false;
		}

		if (isset($response['error-response'])) {
			$this->error = $response['error-response']['StatusInformation'] ?? '';
			return false;
		}

		if (!empty($awb_number = $response['GenerateWayBillResult']['AWBNo'])) {
			$pickup_date_response 	= str_replace(
				['/', 'Date', '(' , ')'],
				' ',
				$response['GenerateWayBillResult']['ShipmentPickupDate']
			);
			$schedule_date 			= explode('+', $pickup_date_response);
			$scheduled_pickup 		= !empty($schedule_date) ?  date('Y-m-d h:i:s', $schedule_date[0]/1000) : '';

			return [
				'pickup'			=> array_merge($response['GenerateWayBillResult'], [
					'scheduled_date'		=> $scheduled_pickup,
					'token_number'			=> $response['GenerateWayBillResult']['TokenNumber'] ?? '',
					'remark'				=> $response['GenerateWayBillResult']['Status'][0]['StatusInformation'] . ': ' . $awb_number,
					'scheduled_timestamp'	=> strtotime($scheduled_pickup),
					'pickup_status'			=> 1,
					'routing_code'			=> $response['GenerateWayBillResult']['DestinationArea'] . ' ' . $response['GenerateWayBillResult']['DestinationLocation'] . ' ' . $response['GenerateWayBillResult']['ClusterCode'],
				]),

				'order_id' 		=> $data['order_id'],
				'order_code' 	=> $data['order_code'],
				'order_type' 	=> $data['order_type'],
				'shipment_id' 	=> $data['shipment_id'],
				'awb_number' 	=> $awb_number,
				'awb_code' 		=> $awb_number,
				'token_number' 	=> $response['GenerateWayBillResult']['TokenNumber'] ?? '',
				'api_response' => $response,

			];
		}
	}

	public function generateAWB($order = []) {

	}

	public function generatePickup($shipment_id = false) {

	}

	public function generateLabel($shipment_ids = false) {

	}

	public function generateInvoice($order_ids = false) {

	}

	public function trackingDeatil($awb_number = false) {
		if (empty($awb_number)) {
			$this->error = 'Invalid Order';
			return false;
		}

		$url = sprintf(
			'https://api.bluedart.com/servlet/RoutingServlet?handler=tnt&action=custawbquery&loginid=GGN70711&awb=awb&numbers=%s&format=json&lickey=%s&verno=%s&scan=1',
			$awb_number,
			$this->tracking_licence_key,
			$this->tracking_api_version
		);

		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL 			=> $url,
			CURLOPT_SSL_VERIFYHOST	=> 0,
			CURLOPT_SSL_VERIFYPEER	=> 0,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> '',
			CURLOPT_MAXREDIRS 		=> 10,
			CURLOPT_TIMEOUT 		=> 30,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> 'GET',
			CURLOPT_HTTPHEADER 		=> [
				'Content-Type: application/json'
			],
			CURLOPT_POSTFIELDS 		=> 0,
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		$result = !empty($response) ? json_decode($response) : [];

		if (empty($result->GenerateWayBillResult) || empty($result->GenerateWayBillResult->Status[0])) {
			$return[$ord['shipment_id']] = [
				'error' 	=> 'Unable to create shipment',
				'request' 	=> $post_data
			];

			return $return;
		}

		if ($result->GenerateWayBillResult->IsError == '1') {
			if (
				!empty($ord['shipment_id']) &&
				($ord['shipment_id'] == $result->GenerateWayBillResult->CCRCRDREF) &&
				!empty($result->GenerateWayBillResult->Status[0]->StatusInformation) &&
				(strpos(strtolower($result->GenerateWayBillResult->Status[0]->StatusInformation), 'waybill already genereated for this creditreferenceno') !== false)
			) {
				$error_str = str_replace(
					['Waybill already genereated for this CreditReferenceNo. Waybill No : ', ' Dest Area :', ' Dest Scrcd :'],
					['', ',', ' / '],
					$result->GenerateWayBillResult->Status[0]->StatusInformation
				);

				$error = explode(',', $error_str);

				if (count($error) == 2) {
					$return[$ord['shipment_id']] = [
						'order_id' 		=> $ord['id'],
						'shipment_id' 	=> $ord['shipment_id'],
						'awb_code' 		=> $error[0],
						'status' 		=> 'NEW',
						'status_code' 	=> '1',
						'courier_name' 	=> 'Bluedart',
						'pdf_url' 		=> '',
						'route_code' 	=> $error[1],
						'request' 		=> $post_data
					];
				}
			} else {
				$return[$ord['shipment_id']] = [
					'error' 	=> $result->GenerateWayBillResult->Status[0]->StatusInformation,
					'request' 	=> $post_data
				];
			}

			return $return;
		}

		$this->error = 'Unable to track shipment';

		return false;
	}

	public function trackingUrl($awb_number = 0) {
		return sprintf('https://bluedart.com/?%s', $awb_number);
	}

	public function generateToken() {
		$token_file = FCPATH . 'uploads/bluedart_ship_token_file_briboo_kb_tok_file.php';

		if (!is_file($token_file) || (filemtime($token_file) + (20 * 3600)) < time()) {
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'ClientID: GuNQVYDAMy9bqa0pVvi1UXv0ZQsLGEJ1',
				'clientSecret: ddTcx4QTymIikktH',
			];

			$ch = curl_init();

			if (!empty($data)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, []);
			}

			curl_setopt_array($ch, [
				CURLOPT_URL 			=> $this->api_url . 'token/v1/login',
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_ENCODING 		=> '',
				CURLOPT_MAXREDIRS 		=> 10,
				CURLOPT_TIMEOUT 		=> 30,
				CURLOPT_SSL_VERIFYHOST 	=> 0,
				CURLOPT_SSL_VERIFYPEER 	=> 0,
				CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST 	=> 'GET',
				CURLOPT_HTTPHEADER 		=> $headers,
			]);

			$result = curl_exec($ch);

			$err = curl_error($ch);

			curl_close($ch);

			if (!empty($err)) {
				$this->error = 'Error in API Request.';
				return false;
			}

			$response = json_decode($result, true);

			if (!empty($response) && !empty($token = $response['JWTToken'])) {
				file_put_contents($token_file, $token);

				return $token;
			}

			return false;
		} else {
			return file_get_contents($token_file);
		}
	}

	protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		log_kb(['bluedart::_curl::request:: ' => [
			'endpoint'		=> $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		if (is_array($data)) {
			$data = json_encode($data);
		}

		if ($token) {
			$jwt_token = self::generateToken();

			log_kb([
				'BLUEDART-TOKEN' => $jwt_token
			]);

			if (empty($jwt_token)) {
				$this->error = 'Invalid credentials';
				return false;
			}

			$headers = [
				'Content-Type: application/json',
				'JWTToken: ' . $jwt_token,
			];
		} else {
			$headers = [
				'Content-Type: application/json'
			];
		}

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
			$this->error = 'Error in API Request.';
			return false;
		}

		log_kb(['bluedart::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->error,
		]]);

		return json_decode($response, true);
	}
}
