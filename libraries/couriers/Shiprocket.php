<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/couriers/BaseCourier.php';

final class Shiprocket extends BaseCourier {
	public function __construct() {
		$this->api_url = 'https://apiv2.shiprocket.in/';
		$this->api_error = '';

		if (empty(SHIPROCKET)) {
			$this->error = $this->api_error = 'Invalid credentials';
			return false;
		}
	}

	public function cleanMobile($string = '') {
		return preg_replace('/[^\d]/', '', trim($string)); // Removes special chars.
	}

	public function getRates($data = [], $type = '') {
		if (empty($data))
			return false;

		$payload = http_build_query($data);

		$response = self::_curl(($type ? ($type . '/') : '' ) . 'courier/serviceability/?' . $payload, null, 'GET');

		return $response['data']['available_courier_companies'] ?? [];
	}

	private function _getSku($product = [], $key = 0) {
		if (!empty($product['sku'])) return $product['sku'];

		if (!empty($product['product_id'])) {
			$option = json_decode($product['option'], true);

			return 'BB_' . mb_strtoupper($option['name']) . '_' . $key . 'V' . $product['version'] . '_' . $product['product_id'];
		}

		return 'BB_' . $product['id'];
	}

	public function bookOrder($order = []) {
		if (empty($order))
			return false;

		if ($order['shipping_status'] != 0)
			return false;

		$order_items = [];

		if (!empty($order['products'])) {
			foreach ($order['products'] as $key => $product) {
				$order_items[] = [
					'name' 			=> !empty($product['name']) ? ($product['name'] . ' By ' . ($product['author_name'] ?? '')) : '',
					'sku' 			=> self::_getSku($product, $key),
					'units' 		=> !empty($product['quantity']) ? $product['quantity'] : 1,
					'hsn' 			=> '49011010',
					'selling_price' => !empty($product['total']) ? round($product['total'] / ($product['quantity'] ?? 1), 2) : 0,
					'tax' 			=> '',
					'discount'		=> ''
				];
			}
		}

		if (mb_strtolower($order['currency_code']) !== 'inr' || strtolower($order['drop_location']['country']) != 'india') {
			$international = true;
			$mobile = removeIsdCode(self::cleanMobile($order['drop_location']['mobile']), $order['drop_location']['country']);
		} else {
			$international = false;
			$mobile = self::cleanMobile(!empty($order['drop_location']['mobile'])
				? substr($order['drop_location']['mobile'], -10)
				: substr($order['user']['mobile'], -10))
			;
		}

		if (ENVIRONMENT !== 'production') {
			$order['drop_location']['name'] = 'Test Order';
		}

		$data = [
			'order_id' 				=> $order['order_code'],
			'order_date' 			=> $order['date_added'],
			'currency' 				=> $order['currency_code'],
			'pickup_location' 		=> $order['pickup_location']['pickup_location_name'] ?? 'Primary', // Primary
			'channel_id' 			=> '',
			'comment' 				=> 'Reseller: ' . get_settings('system_name'),
			'billing_customer_name' => !empty($order['drop_location']['name']) ? $order['drop_location']['name'] : $order['user']['first_name'],
			'billing_last_name' 	=> '',
			'billing_address' 		=> substr(!empty($order['drop_location']['address']) ? $order['drop_location']['address'] : '', 0, 190),
			'billing_address_2' 	=> substr(!empty($order['drop_location']['landmark']) ? $order['drop_location']['landmark'] : '', 0, 190),
			'billing_city' 			=> substr(!empty($order['drop_location']['city']) ? $order['drop_location']['city'] : '', 0, 10),
			'billing_state' 		=> !empty($order['drop_location']['state']) ? $order['drop_location']['state'] : '',
			'billing_country' 		=> !empty($order['drop_location']['country']) ? $order['drop_location']['country'] : 'India',
			'billing_pincode' 		=> !empty($order['drop_location']['zipcode']) ? $order['drop_location']['zipcode'] : '',
			'billing_email' 		=> !empty($order['user']['email']) ? $order['user']['email'] : '',
			'billing_phone' 		=> $mobile,
			'shipping_is_billing' 	=> '1',
			'shipping_customer_name'=> !empty($order['drop_location']['name']) ? $order['drop_location']['name'] : $order['user']['first_name'],
			'shipping_last_name' 	=> '',
			'shipping_address' 		=> substr(!empty($order['drop_location']['address']) ? $order['drop_location']['address'] : '', 0, 190),
			'shipping_address_2' 	=> substr(!empty($order['drop_location']['landmark']) ? $order['drop_location']['landmark'] : '', 0, 190),
			'shipping_city' 		=> substr(!empty($order['drop_location']['city']) ? $order['drop_location']['city'] : '', 0, 10),
			'shipping_state' 		=> !empty($order['drop_location']['state']) ? $order['drop_location']['state'] : '',
			'shipping_country' 		=> !empty($order['drop_location']['country']) ? $order['drop_location']['country'] : 'India',
			'shipping_pincode' 		=> !empty($order['drop_location']['zipcode']) ? $order['drop_location']['zipcode'] : '',
			'shipping_email' 		=> !empty($order['user']['email']) ? $order['user']['email'] : 'info@bripublish.com',
			'shipping_phone' 		=> $mobile,
			'order_items' 			=> $order_items ? $order_items : '',
			'payment_method' 		=> 'Prepaid',
			'shipping_charges' 		=> $order['shipping_cost'],
			'total_discount' 		=> 0,
			'sub_total' 			=> round(($order['total'] - $order['shipping_cost']), 2),
			'weight' 				=> round($order['weight'] / 1000, 2) ? round($order['weight'] / 1000, 2) : '0.5',
			'length' 				=> '10',
			'breadth' 				=> '10',
			'height' 				=> '10',
			'customer_gstin' 		=> '',
			'vat_number' 			=> '000000000',
			'ioss' 					=> 'IM0000000000',
		];

		if ($international) {
			$data['purpose_of_shipment'] = 1;
		}

		$response = self::_curl(($international ? 'international/' : '') . 'orders/create/adhoc', $data);

		if (empty($response) || empty($response['status']) || empty($response['order_id']) || empty($response['shipment_id'])) {
			$this->error = $this->api_error = (!empty($response['message'])) ? $response['message'] : 'Unable to create order';
			return false;
		}

		$results['status'] 				= true;
		$results['order_id'] 			= $response['order_id'];
		$results['shipment_id'] 		= $response['shipment_id'];
		$results['status'] 				= $response['status'];
		$results['status_code'] 		= $response['status_code'];
		$results['awb_code'] 			= $response['awb_code'] ?? '';
		$results['courier_company_id'] 	= $response['courier_company_id'];
		$results['courier_name'] 		= $response['courier_name'];

		return $results;
	}

	public function generateAWB($params = []) {
		$shipment_id 	= $params['shipment_id'] ?? false;
		$courier_id 	= $params['courier_id'] ?? false;

		if (!$shipment_id || !$courier_id)
			return false;

		$data = [
			'shipment_id' 	=> $shipment_id,
			'courier_id' 	=> $courier_id
		];

		$response = self::_curl('courier/assign/awb', $data);

		if (empty($response) || empty($response['awb_assign_status']) || empty($response['response']['data']['awb_code']) || !empty($response['response']['data']['awb_assign_error'])) {
			$this->error = $this->api_error = $response['message'] ?? ($response['response']['data']['awb_assign_error'] ?? 'Unable to generate AWB');
			return false;
		}

		$results['status'] 					= true;
		$results['awb_number'] 				= $response['response']['data']['awb_code'];
		$results['assigned_date_time'] 		= $response['response']['data']['assigned_date_time']['date'] ?? '';
		$results['invoice_no'] 				= $response['response']['data']['invoice_no'];
		$results['routing_code'] 			= $response['response']['data']['routing_code'] ?? '';
		$results['weight'] 					= $response['response']['data']['applied_weight'] ?? '';
		$results['charges'] 				= $response['response']['data']['freight_charges'] ?? '';
		$results['api_response'] 			= $response;

		return $results;
	}

	public function generateLabel($shipment_ids = false) {
		if (!$shipment_ids)
			return false;

		$data = [
			'shipment_id' => !is_array($shipment_ids) ? [$shipment_ids] : $shipment_ids
		];

		$response = self::_curl('courier/generate/label', $data);

		if (empty($response['label_url'])) {
			$this->error = $this->api_error = $response['response'] ?? 'Unable to generate label';
			return false;
		}

		$result['status'] 		= true;

		if (is_array($response['label_url'])) {
			$result['label_url'] 	= $response['label_url'][0] ?? '';
		} else {
			$result['label_url'] 	= $response['label_url'] ?? '';
		}

		return $result;
	}

	public function generateInvoice($order_ids = false) {
		if (!$order_ids)
			return false;

		$data = [
			'ids' => !is_array($order_ids) ? [$order_ids] : $order_ids
		];

		$response = self::_curl('orders/print/invoice', $data);

		if (empty($response['invoice_url'])) {
			$this->error = $this->api_error = $response['message'] ?? 'Unable to generate invoice';
			return false;
		}

		$result['status'] 		= true;
		$result['invoice_url'] 	= $response['invoice_url'];

		return $result;
	}

	public function generateManifests($order_ids = false) {
		if (!$order_ids)
			return false;

		$data = [
			'order_ids' => !is_array($order_ids) ? [$order_ids] : $order_ids
		];

		$response = self::_curl('manifests/print', $data);

		if (empty($response['manifest_url'])) {
			$this->error = $this->api_error = $response['message'] ?? 'Unable to generate manifest';
			return false;
		}

		$result['status'] 		= true;
		$result['manifest_url'] = $response['manifest_url'];

		return $result;
	}

	public function generatePickup($shipment_id = false) {
		if (!$shipment_id)
			return false;

		$data = [
			'shipment_id' => $shipment_id
		];

		$response = self::_curl('courier/generate/pickup', $data);

		$results = [];

		if (!empty($response) && $response['response']) {
			$results['status'] 			= true;
			$results['pickup_status'] 	= !empty($response['pickup_status']) ? 1 : 0;
			$results['scheduled_date'] 	= $response['response']['pickup_scheduled_date'] ?? null;
			$results['token_number'] 	= $response['response']['pickup_token_number'] ?? '';
			$results['remark'] 			= $response['response']['data'] ?? '';
			$results['scheduled_timestamp'] = !empty($response['response']['pickup_scheduled_date']) ? strtotime($response['response']['pickup_scheduled_date']) : 0;

			if (!empty($response['response']['others'])) {
				$others = json_decode($response['response']['others'], 1);
				$results['routing_code'] = $others['routing_code'] ?? '';
			}
		}

		return $results;
	}

	public function trackingDeatil($shipment_id = false) {
		if (!$shipment_id)
			return false;

		$response = self::_curl('courier/track/shipment/' . $shipment_id, NULL, 'GET');

		$data = $response['tracking_data']['shipment_track'][0] ?? [];

		if ($response['tracking_data']['shipment_status'] == 7) {
			return [
				'order_status'	=> 4,
				'shipment_info'	=> [
					'awb_code'	=> $data['awb_code'],
					'status'	=> $data['current_status'] ?? '',
				]
			];
		}

		if (
			!empty($data['current_status']) &&
			$data['current_status'] == 'rto delivered'
		) {
			return [
				'order_status'	=> 15,
				'shipment_info'	=> [
					'awb_code'	=> $data['awb_code'],
					'status'	=> $data['current_status'] ?? '',
				]
			];
		}

		return false;
	}

	public function updateDeliveryAddress($order_id = false, $address = []) {
		if (!$order_id)
			return false;

		$data = [
			'order_id' 					=> $order_id,
			'shipping_customer_name' 	=> (!empty($address['customer_name']))?$address['customer_name']:'Abhishek',
			'shipping_phone' 			=> (!empty($address['phone']))?$address['phone']:'9818651520',
			'shipping_address' 			=> (!empty($address['address']))?$address['address']:'SVA Appartment',
			'shipping_address_2' 		=> (!empty($address['address_2']))?$address['address_2']:'Talab',
			'shipping_city' 			=> (!empty($address['city']))?$address['city']:'Ghitorni',
			'shipping_state' 			=> (!empty($address['state']))?$address['state']:'Delhi',
			'shipping_country' 			=> (!empty($address['country']))?$address['country']:'India',
			'shipping_pincode' 			=> (!empty($address['pincode']))?$address['pincode']:'110020',
			'shipping_email' 			=> '',
			'billing_alternate_phone' 	=> '',
		];

		$response = self::_curl('orders/address/update', $data);

		return $response;
	}

	public function cancelOrder($order_ids = false) {
		if (empty($order_ids))
			return false;

		$data = [
			'ids' => $order_ids
		];

		$response = self::_curl('orders/cancel', $data);

		return $response;
	}

	public function cancelShipment($awbs = []) {
		if (empty($awbs))
			return false;

		$data = [
			'awbs' => $awbs
		];

		$response = self::_curl('orders/cancel/shipment/awbs', $data);

		return $response;
	}

	public function getAwbCode($shipment_id = false) {
		if (empty($shipment_id))
			return false;

		$response = self::_curl('courier/track/shipment/' . $shipment_id, null,'GET');
		return $response;
	}

	public function trackingUrl($awb_number = 0) {
		return sprintf('https://shiprocket.co/tracking/%s', $awb_number);
	}

	public function generateToken() {
		$token_file = FCPATH . 'uploads/ship_token_file_briboo_kb_tok_file.php';

		if (!is_file($token_file) || (filemtime($token_file) + (9 * 24 * 3600)) < time()) {
			$data = [
				'email' 	=> SHIPROCKET['email'],
				'password' 	=> SHIPROCKET['password']
			];

			$response = self::_curl('auth/login', $data, 'POST', false);

			if (!empty($response) && !empty($token = $response['token'])) {
				file_put_contents($token_file, $token);

				return $token;
			}
		} else {
			return file_get_contents($token_file);
		}
	}

	protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		if (is_array($data)) {
			$data = json_encode($data);
		}

		log_kb(['shiprocket::_curl::request:: ' => [
			'endpoint'		=> $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		if ($token) {
			$bearer_token = self::generateToken();

			if (empty($bearer_token)) {
				$this->error = $this->api_error = 'Invalid credentials';
				return false;
			}

			$headers = [
				'Content-Type: application/json',
				'Authorization: Bearer ' . $bearer_token
			];
		} else {
			$headers = [
				'Content-Type: application/json',
			];
		}

		$ch = curl_init();

		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt_array($ch, [
			CURLOPT_URL 			=> $this->api_url . 'v1/external/' . $endpoint,
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

		log_kb(['shiprocket::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->error,
		]]);

		return json_decode($response, true);
	}

	public function fetchAWB($order_id = false) {
		if (!$order_id)
			return false;

		$response = self::_curl('orders/show/' . $order_id, NULL, 'GET');

		return $response;
	}
}
