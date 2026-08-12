<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Shiprocket_lib {
	protected $CI;
	protected $error;
	public $raw = null;

	function __construct() {
		$this->CI = &get_instance();

		$this->api_url = 'https://apiv2.shiprocket.in/';

		if (empty(SHIPROCKET)) {
			$this->error = 'Invalid credentials';
			return false;
		}
	}

	function cleanMobile($string) {
		return preg_replace('/[^\d]/', '', trim($string)); // Removes special chars.
	}

	function bookOrder($order) {
		if (empty($order))
			return false; 

		if ($order['shipping_status'] != '0')
			return false;

		$order_items = [];

		if (!empty($order['products'])) {
			foreach ($order['products'] as $key => $product) {
				$option = json_decode($product['option'], true);

				$order_items[] = [
					'name' 			=> !empty($product['name']) ? ($product['name'] . ' By ' . $product['author_name']) : '',
					'sku' 			=> !empty($product['product_id'])
						? 'BB_' . mb_strtoupper($option['name']) . '_' . $key . 'V' . $product['version'] . '_' . $product['product_id']
						: '',
					'units' 		=> !empty($product['quantity']) ? $product['quantity'] : 1,
					'hsn' 			=> '49011010',
					'selling_price' => !empty($product['total']) ? round($product['total'] / $product['quantity'], 2) : 0,
					'tax' 			=> '',
					'discount'		=> ''
				];
			}
		}

		$sub_total = $order['subtotal'];

		if (
			mb_strtolower($order['currency_code']) !== 'inr' ||
			strtolower($order['address']['country']) != 'india'
		) {
			$international = true;
			$mobile = removeIsdCode(self::cleanMobile($order['address']['mobile']), $order['address']['country']);

			if(!empty($max_shipping_value = (double)(SHIPMENT_TOTAL_VALUE['shiprocket'][strtolower($order['address']['country'])]))) {
				$sub_total = ($order['subtotal'] > $max_shipping_value) ? $max_shipping_value : $order['subtotal'];

				$total_units = array_sum(array_column($order_items, 'units'));

				$shipping_cost_per_unit = !empty($order['shipping_cost']) ? round($order['shipping_cost'] / $total_units, 2) : 0;

				foreach ($order_items as &$order_item) {
					$unit_price = !empty($total_units) ? round($sub_total / $total_units, 2) : 0;
					$order_item['selling_price'] = !empty($shipping_cost_per_unit) ? round($unit_price - $shipping_cost_per_unit, 2) : $unit_price;
				}
			}
		} else {
			$international = false;
			$mobile = self::cleanMobile(!empty($order['address']['mobile'])
				? substr($order['address']['mobile'], -10)
				: substr($order['userData']['mobile'], -10))
			;
		}

		$data = [
			'order_id' 				=> $order['order_code'],
			'order_date' 			=> $order['date_added'],
			'currency' 				=> $order['currency_code'],
			'pickup_location' 		=> 'Primary', // Primary
			'channel_id' 			=> '',
			'comment' 				=> 'Reseller: BriBooks',
			'billing_customer_name' => !empty($order['address']['name']) ? $order['address']['name'] : $order['userData']['first_name'],
			'billing_last_name' 	=> '',
			'billing_address' 		=> substr(!empty($order['address']['address']) ? $order['address']['address'] : '', 0, 190),
			'billing_address_2' 	=> substr(!empty($order['address']['landmark']) ? $order['address']['landmark'] : '', 0, 190),
			'billing_city' 			=> substr(!empty($order['address']['city']) ? $order['address']['city'] : '', 0, 10),
			'billing_state' 		=> !empty($order['address']['state']) ? $order['address']['state'] : '',
			'billing_country' 		=> !empty($order['address']['country']) ? $order['address']['country'] : 'India',
			'billing_pincode' 		=> !empty($order['address']['zipcode']) ? $order['address']['zipcode'] : '',
			'billing_email' 		=> !empty($order['userData']['email']) ? $order['userData']['email'] : '',
			'billing_phone' 		=> $mobile,
			'shipping_is_billing' 	=> '1',
			'shipping_customer_name'=> !empty($order['address']['name']) ? $order['address']['name'] : $order['userData']['first_name'],
			'shipping_last_name' 	=> '',
			'shipping_address' 		=> substr(!empty($order['address']['address']) ? $order['address']['address'] : '', 0, 190),
			'shipping_address_2' 	=> substr(!empty($order['address']['landmark']) ? $order['address']['landmark'] : '', 0, 190),
			'shipping_city' 		=> substr(!empty($order['address']['city']) ? $order['address']['city'] : '', 0, 10),
			'shipping_state' 		=> !empty($order['address']['state']) ? $order['address']['state'] : '',
			'shipping_country' 		=> !empty($order['address']['country']) ? $order['address']['country'] : 'India',
			'shipping_pincode' 		=> !empty($order['address']['zipcode']) ? $order['address']['zipcode'] : '',
			'shipping_email' 		=> !empty($order['userData']['email']) ? $order['userData']['email'] : 'info@youbooks.co',
			'shipping_phone' 		=> $mobile,
			'order_items' 			=> $order_items ? $order_items : '',
			'payment_method' 		=> 'Prepaid',
			'shipping_charges' 		=> $order['shipping_cost'],
			'total_discount' 		=> $order['subtotal'] - $order['total'],
			'sub_total' 			=> !empty((double)$sub_total) ? ((double)($sub_total - $order['shipping_cost'])) : 1,
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

		$this->raw = $response;

		if (empty($response) || ($response->status == '0')) {
			$this->error = (!empty($response->message)) ? $response->message : 'Unable to create shipment';
			return false;
		}

		if (empty($response->order_id) || empty($response->shipment_id)) {
			$this->error = (!empty($response->message)) ? $response->message : 'Unable to create shipment';
			return false;
		}

		return $response;
	}

	function bookMedallionOrder($order = []) {
		if (empty($order)) return false;

		if ($order['shipping_status'] != 0) return false;

		$order_items = [];

		if (!empty($order['products'])) {
			foreach ($order['products'] as $key => $product) {
				$order_items[] = [
					'name' 			=> sprintf('%s for %s', $product['medallion_name'], $product['book_name']),
					'sku' 			=> sprintf('BBM_%s_%s', $key, $product['medallion_id']),
					'units' 		=> 1,
					'hsn' 			=> '83062120',
					'selling_price' => $product['subtotal'],
					'tax' 			=> '',
					'discount'		=> ''
				];
			}
		}

		$sub_total = $order['subtotal'];

		if (
			mb_strtolower($order['currency_code']) !== 'inr' ||
			strtolower($order['address']['country']) != 'india'
		) {
			$international = true;
			$mobile = removeIsdCode(self::cleanMobile($order['address']['mobile']), $order['address']['country']);

			if (!empty($max_shipping_value = (double)(SHIPMENT_TOTAL_VALUE['shiprocket'][strtolower($order['address']['country'])]))) {
				$sub_total = ($order['subtotal'] > $max_shipping_value)
					? $max_shipping_value
					: $order['subtotal'];

				$total_units = array_sum(array_column($order_items, 'units'));

				$shipping_cost_per_unit = !empty($order['shipping_cost'])
					? round($order['shipping_cost'] / $total_units, 2)
					: 0;

				foreach ($order_items as &$order_item) {
					$unit_price = !empty($total_units) ? round($sub_total / $total_units, 2) : 0;
					$order_item['selling_price'] = !empty($shipping_cost_per_unit)
						? round($unit_price - $shipping_cost_per_unit, 2)
						: $unit_price;
				}
			}
		} else {
			$international = false;
			$mobile = self::cleanMobile(!empty($order['address']['mobile'])
				? substr($order['address']['mobile'], -10)
				: substr($order['user']['mobile'], -10))
			;
		}

		$data = [
			'order_id' 				=> $order['order_code'],
			'order_date' 			=> $order['date_added'],
			'currency' 				=> $order['currency_code'],
			'pickup_location' 		=> 'Primary', // Primary
			'channel_id' 			=> '',
			'comment' 				=> 'Reseller: BriBooks',
			'billing_customer_name' => !empty($order['address']['name']) ? $order['address']['name'] : $order['user']['first_name'],
			'billing_last_name' 	=> '',
			'billing_address' 		=> substr(!empty($order['address']['address']) ? $order['address']['address'] : '', 0, 190),
			'billing_address_2' 	=> substr(!empty($order['address']['landmark']) ? $order['address']['landmark'] : '', 0, 190),
			'billing_city' 			=> substr(!empty($order['address']['city']) ? $order['address']['city'] : '', 0, 10),
			'billing_state' 		=> !empty($order['address']['state']) ? $order['address']['state'] : '',
			'billing_country' 		=> !empty($order['address']['country']) ? $order['address']['country'] : 'India',
			'billing_pincode' 		=> !empty($order['address']['zipcode']) ? $order['address']['zipcode'] : '',
			'billing_email' 		=> !empty($order['user']['email']) ? $order['user']['email'] : '',
			'billing_phone' 		=> $mobile,
			'shipping_is_billing' 	=> '1',
			'shipping_customer_name'=> !empty($order['address']['name']) ? $order['address']['name'] : $order['user']['first_name'],
			'shipping_last_name' 	=> '',
			'shipping_address' 		=> substr(!empty($order['address']['address']) ? $order['address']['address'] : '', 0, 190),
			'shipping_address_2' 	=> substr(!empty($order['address']['landmark']) ? $order['address']['landmark'] : '', 0, 190),
			'shipping_city' 		=> substr(!empty($order['address']['city']) ? $order['address']['city'] : '', 0, 10),
			'shipping_state' 		=> !empty($order['address']['state']) ? $order['address']['state'] : '',
			'shipping_country' 		=> !empty($order['address']['country']) ? $order['address']['country'] : 'India',
			'shipping_pincode' 		=> !empty($order['address']['zipcode']) ? $order['address']['zipcode'] : '',
			'shipping_email' 		=> !empty($order['user']['email']) ? $order['user']['email'] : 'info@youbooks.co',
			'shipping_phone' 		=> $mobile,
			'order_items' 			=> $order_items ? $order_items : '',
			'payment_method' 		=> 'Prepaid',
			'shipping_charges' 		=> $order['shipping_cost'],
			'total_discount' 		=> 0,
			'sub_total' 			=> (double)$sub_total,
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

		log_kb(['shiprocket::medallion::req:: ' => $data]);

		$response = self::_curl(($international ? 'international/' : '') . 'orders/create/adhoc', $data);

		$this->raw = $response;

		if (empty($response) || ($response->status == '0')) {
			$this->error = (!empty($response->message)) ? $response->message : 'Unable to create shipment';
			return false;
		}

		if (empty($response->order_id) || empty($response->shipment_id)) {
			$this->error = (!empty($response->message)) ? $response->message : 'Unable to create shipment';
			return false;
		}

		return $response;
	}

	function generateAWB($shipment_id = false, $courier_id = false) {
		if (!$shipment_id || !$courier_id)
			return false;

		$data = [
			'shipment_id' 	=> $shipment_id,
			'courier_id' 	=> $courier_id
		];

		$response = self::_curl('courier/assign/awb', $data);

		return $response;
	}

	function generateLabel($shipment_id = false) {
		if (!$shipment_id)
			return false;

		$data = [
			'shipment_id' => $shipment_id
		];

		$response = self::_curl('courier/generate/label', $data);

		return $response;
	}

	function generateInvoice($order_ids = false) {

		if (!$order_ids)
			return false;

		$data = [
			'ids' => $order_ids
		];

		$response = self::_curl('orders/print/invoice', $data);

		return $response;
	}

	function generateManifests($shipment_id = false) {
		if (!$shipment_id)
			return false;


		$data = [
			'shipment_id' => $shipment_id
		];

		$response = self::_curl('manifests/generate', $data);

		return $response;
	}

	function trackingDeatil($shipment_id = false) {
		if (!$shipment_id)
			return false;

		$response = self::_curl('courier/track/shipment/' . $shipment_id, NULL, 'GET');

		return $response;
	}

	function updateDeliveryAddress($order_id = false, $address = []) {
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

	function cancelOrder($order_ids = []) {
		if (empty($order_ids))
			return false;

		$data = [
			'ids' => $order_ids
		];

		$response = self::_curl('orders/cancel', $data);

		return $response;
	}

	function cancelShipment($awbs = []) {
		if (empty($awbs))
			return false;

		$data = [
			'awbs' => $awbs
		];

		$response = self::_curl('orders/cancel/shipment/awbs', $data);

		return $response;
	}

	function getAwbCode($shipment_id = '') {
		if (empty($shipment_id))
			return false;

		$response = self::_curl('courier/track/shipment/' . $shipment_id, null,'GET');
		return $response;
	}

	function generateToken() {
		$token_file = FCPATH . 'uploads/ship_token_file_briboo_kb_tok_file.php';

		if (!is_file($token_file) || (filemtime($token_file) + (9 * 24 * 3600)) < time()) {
			$data = [
				'email' => SHIPROCKET['email'],
				'password' => SHIPROCKET['password']
			];

			$response = self::_curl('auth/login', $data, 'POST', false);

			if (!empty($response) && !empty($token = $response->token)) {
				file_put_contents($token_file, $token);

				return $token;
			}
		} else {
			return file_get_contents($token_file);
		}
	}

	private function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
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
			$bearer_token = $this->generateToken();

			if (empty($bearer_token)) {
				$this->error = 'Invalid credentials';
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
			$this->error = 'Error in API Request.';
			return false;
		}

		log_kb(['shiprocket::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->error,
		]]);

		return json_decode($response);
	}

	function fetchAWB($order_id = false) {
		if (!$order_id)
			return false;

		$response = self::_curl('orders/show/' . $order_id, NULL, 'GET');

		return $response;
	}

	function fetchShipment($shipment_id = false) {
		if (empty($shipment_id))
			return false;

		$response = self::_curl('shipments/' . $shipment_id, NULL, 'GET');

		return $response;
	}
}
