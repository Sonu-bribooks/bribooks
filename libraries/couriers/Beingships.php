<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/couriers/BaseCourier.php';

final class BeingShips extends BaseCourier {
	public function __construct() {
		$this->api_url 	= 'https://beingships.com/api/';
		$this->api_key 	= ENVIRONMENT === 'production'
			? ''
			: '';

		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('shipping/Courier_model');

		$this->courier_model = $this->CI->Courier_model;

		$this->courier = $this->courier_model->get_all([
			'status'		=> 1,
			'vendor_name'	=> 'beingships',
			'carrier_id'	=> 0,
			'is_domestic'	=> 0,
		])['rows'][0] ?? [];
	}

	public function cleanMobile($string = '') {
		return preg_replace('/[^\d]/', '', trim($string)); // Removes special chars.
	}

	public function getRates($data = [], $type = '') {
		$payload = [
			'ApiKey'				=> $this->api_key,
			'PickupPincode' 		=> (int)($data['pickup_location']['pincode'] ?? ''),
			'DeliveryPincode' 		=> (int)($data['drop_location']['zipcode'] ?? ''),
			'OrderType' 			=> 'forward',
			'PaymentType' 			=> 'prepaid',
			'Weight' 				=> round($data['weight'] / 1000, 2),
			'Length' 				=> 10,
			'Breadth' 				=> 10,
			'Height' 				=> 10,
			'InvoiceAmount'			=> !empty($data['order']['total']) ? $data['order']['total'] : 1,
		];

		$response = self::_curl('rate-calculator', $payload);

		if (!empty($response['status'])) {
			return array_map(fn($item) => array_merge($item, [
				'courier_company_id' => $item['id'],
			]), $response['data']['partners'] ?? []);
		} else {
			$this->error = $response['Message'] ?? '';
		}

		return false;
	}

	private function _getSku($product = [], $key = 0) {
		if (!empty($product['sku'])) return $product['sku'];

		if (!empty($product['product_id'])) {
			$option = json_decode($product['option'], true);

			return 'BB_' . mb_strtoupper($option['name']) . '_' . $key . 'V' . $product['version'] . '_' . $product['product_id'];
		}

		return 'BB_' . $product['id'];
	}

	public function bookOrder($data = []) {
		if (empty($data)) return false;

		if ($data['shipping_status'] != 0) return false;

		$order_items = [];

		if (!empty($data['products'])) {
			foreach ($data['products'] as $key => $product) {
				$order_items[] = [
					'Name' 			=> !empty($product['name']) ? ($product['name'] . ' By ' . $product['author_name']) : '',
					'SKU' 			=> self::_getSku($product, $key),
					'QTY' 			=> !empty($product['quantity']) ? $product['quantity'] : 1,
					'HSN' 			=> '49011010',
					'Amount' 		=> !empty($product['total']) ? round($product['total'] / ($product['quantity'] ?? 1), 2) : 0,
				];
			}
		}

		if (mb_strtolower($data['currency_code']) !== 'inr' || strtolower($data['drop_location']['country']) != 'india') {
			$international = true;
			$mobile = removeIsdCode(self::cleanMobile($data['drop_location']['mobile']), $data['drop_location']['country']);
		} else {
			$international = false;
			$mobile = self::cleanMobile(!empty($data['drop_location']['mobile'])
				? substr($data['drop_location']['mobile'], -10)
				: substr($data['user']['mobile'], -10))
			;
		}

		if (ENVIRONMENT !== 'production') {
			$data['drop_location']['name'] = 'Test Order';
		}

		$payload['OrderDetails'][] = [
			'OrderNumber' 			=> $data['order_code'],
			'OrderType' 			=> 'forward',
			'PaymentType' 			=> 'prepaid',
			'OrderDate' 			=> $data['date_added'],
			'Weight' 				=> round($data['weight'] / 1000, 2) ? round($data['weight'] / 1000, 2) : '0.5',
			'Length' 				=> 10,
			'Breadth' 				=> 10,
			'Height' 				=> 10,
			'InvoiceAmount' 		=> $data['total'],
			'CollectableAmount' 	=> 0,
			'Addresses'				=> [
				'ShippingAddress'	=> [
					'CustomerName' 	=> !empty($data['drop_location']['name']) ? $data['drop_location']['name'] : $data['user']['first_name'],
					'AddressLine1' 	=> $data['drop_location']['address'] ?? '',
					'AddressLine2' 	=> $data['drop_location']['landmark'] ?? '',
					'City' 			=> $data['drop_location']['city'] ?? '',
					'State' 		=> $data['drop_location']['state'] ?? '',
					'Pincode' 		=> (int)($data['drop_location']['zipcode'] ?? ''),
					'Contact' 		=> $mobile ?? '',
					'Email'			=> $data['user']['email'] ?? '',
				],
				'BillingAddress'	=> [
					'CustomerName' 	=> !empty($data['drop_location']['name']) ? $data['drop_location']['name'] : $data['user']['first_name'],
					'AddressLine1' 	=> $data['drop_location']['address'] ?? '',
					'AddressLine2' 	=> $data['drop_location']['landmark'] ?? '',
					'City' 			=> $data['drop_location']['city'] ?? '',
					'State' 		=> $data['drop_location']['state'] ?? '',
					'Pincode' 		=> (int)($data['drop_location']['zipcode'] ?? ''),
					'Contact' 		=> $mobile ?? '',
					'Email'			=> $data['user']['email'] ?? '',
				],
				'PickupAddress'		=> [
					'WarehouseName' => $data['pickup_location']['name'],
					'ContactName' 	=> $data['pickup_location']['contact_name'],
					'AddressLine1' 	=> $data['pickup_location']['address_1'],
					'AddressLine2' 	=> $data['pickup_location']['address_2'],
					'City' 			=> $data['pickup_location']['city'],
					'State' 		=> $data['pickup_location']['state'],
					'Pincode' 		=> $data['pickup_location']['pincode'],
					'Contact' 		=> $data['pickup_location']['mobile'],
					'Email' 		=> $data['pickup_location']['email'],
				],
			],
			'ProductDetails' 		=> $order_items ? $order_items : [],
			'ShippingCharge' 		=> $data['shipping_cost'],
			'EwayBill' 				=> '',
			'GstNumber' 			=> '',
			'CodCharge' 			=> 0,
			'Discount' 				=> 0,
		];

		$payload['ApiKey'] = $this->api_key;

		$response = self::_curl('order-create', $payload);

		if (!empty($response[0]['status'])) {
			return [
				'order_id'			=> $response[0]['order_id'] ?? '',
				'shipment_id'		=> $response[0]['order_id'] ?? '',
			];
		} else {
			$this->error = $response['message'] ?? '';
		}
	}

	public function generateAWB($data = []) {
		$shipment_id 	= $data['shipment_id'] ?? false;
		$courier_id 	= $data['courier_id'] ?? false;

		if (!$shipment_id || !$courier_id) return false;

		$payload = [
			'ApiKey'			=> $this->api_key,
			'OrderID' 			=> $shipment_id,
			'CourierPartnerId' 	=> (int)$courier_id
		];

		$response = self::_curl('order-ship', $payload);

		if (empty($response['status'])) {
			$this->error = $this->api_error = $response['message'] ?? 'Unable to generate AWB';
			return false;
		}

		$results['status'] 					= true;
		$results['awb_number'] 				= $response['data']['awb_number'];
		$results['courier'] 				= $response['data']['courier'];
		$results['courier_keyword'] 		= $response['data']['courier_keyword'];
		$results['route_code'] 				= $response['data']['route_code'];
		$results['api_response'] 			= $response;

		return $results;
	}

	public function generateLabel($shipment_ids = false) {
		if (empty($shipment_ids)) return false;

		$shipment_id = is_array($shipment_ids) ? $shipment_ids[0] : $shipment_ids;

		$payload = [
			'ApiKey'			=> $this->api_key,
			'OrderID' 			=> (int)$shipment_id,
		];

		$response = self::_curl('generate-label', $payload);

		if (empty($response['label'])) {
			$this->error = $this->api_error = $response['response'] ?? 'Unable to generate label';
			return false;
		}

		$result['status'] = true;

		if (is_array($response['label'])) {
			$result['label_url'] 	= $response['label'][0] ?? '';
		} else {
			$result['label_url'] 	= $response['label'] ?? '';
		}

		return $result;
	}

	public function generatePickup($shipment_id = false) {
	}

	public function generateInvoice($shipment_ids = false) {
		if (empty($shipment_ids)) return false;

		$shipment_id = is_array($shipment_ids) ? $shipment_ids[0] : $shipment_ids;

		$payload = [
			'ApiKey'			=> $this->api_key,
			'OrderID' 			=> (int)$shipment_id,
		];

		$response = self::_curl('generate-invoice', $payload);

		if (empty($response['invoice'])) {
			$this->error = $this->api_error = $response['response'] ?? 'Unable to generate invoice';
			return false;
		}

		$result['status'] = true;

		if (is_array($response['invoice'])) {
			$result['invoice_url'] 	= $response['invoice'][0] ?? '';
		} else {
			$result['invoice_url'] 	= $response['invoice'] ?? '';
		}

		return $result;
	}

	public function generateManifests($order_ids = false) {
		if (empty($shipment_ids)) return false;

		$shipment_id = is_array($shipment_ids) ? $shipment_ids[0] : $shipment_ids;

		$payload = [
			'ApiKey'			=> $this->api_key,
			'OrderID' 			=> (int)$shipment_id,
		];

		$response = self::_curl('generate-manifest', $payload);

		if (empty($response['manifest'])) {
			$this->error = $this->api_error = $response['response'] ?? 'Unable to generate manifest';
			return false;
		}

		$result['status'] = true;

		if (is_array($response['manifest'])) {
			$result['manifest_url'] 	= $response['manifest'][0] ?? '';
		} else {
			$result['manifest_url'] 	= $response['manifest'] ?? '';
		}

		return $result;
	}

	public function cancelOrder($order_ids = false) {
	}

	public function cancelShipment($awbs = false) {
	}

	public function fetchAWB($order_id = false) {
	}

	public function trackingDeatil($shipment_id = false) {
		$payload['ApiKey'] 		= $this->api_key;
		$payload['OrderID'] 	= (int)$shipment_id;

		$response = self::_curl('order-track-by-id', $payload);

		if (!empty($response['StatusCode'])) {
			// pickup_scheduled, picked_up, in_transit, out_for_delivery, delivered, ndr ,rto_in_transit, rto_delivered,cancelled

			if ($response['StatusCode'] == 'delivered') {
				return [
					'order_status'	=> 4,
					'shipment_info'	=> [
						'awb_code'	=> $response['AWBNumber'],
						'status'	=> $response['StatusCode'] ?? '',
					]
				];
			} elseif ($response['StatusCode'] == 'rto_in_transit') {
				return [
					'order_status'	=> 15,
					'shipment_info'	=> [
						'awb_code'	=> $response['AWBNumber'],
						'status'	=> $response['StatusCode'] ?? '',
					]
				];
			}
		} else {
			$this->error = $response['Message'];
		}

		return false;
	}

	public function trackingUrl($awb_number = 0) {
		return sprintf('https://beingships.com/track-order/%s', $awb_number);
	}

	protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = FALSE) {
		if (is_array($data)) {
			$data = json_encode($data);
		}

		log_kb(['BeingShips::_curl::request:: ' => [
			'endpoint'		=> $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		$headers = [
			'Content-Type: application/json',
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

		log_kb(['BeingShips::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->error,
		]]);

		return json_decode($response, true);
	}
}
