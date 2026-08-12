<?php defined('BASEPATH') or exit('No direct script access allowed');

final class Gokwik {
	public function __construct() {
		$this->api_url = ENVIRONMENT === 'production'
			? 'https://api.gokwik.co/kwikship'
			: 'https://api-gw-v4.dev.gokwik.in/kwikship/dev';

		$this->CI		= &get_instance();
		$this->load	 	= $this->CI->load;

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->cache	= $this->CI->cache;

		$this->load->model('shipping/Courier_model');
		$this->courier_model = $this->CI->Courier_model;

		$this->courier = $this->courier_model->get_all([
			'status'		=> 1,
			'vendor_name'	=> 'gokwik',
			'carrier_id'	=> 0,
			'is_domestic'	=> 1,
		])['rows'][0] ?? [];
	}

	private function _authenticate() {
		$payload = ENVIRONMENT === 'production'
			? [
				'username' => 'bribooks',
				'password' => 'Y4S7wNNEXI1z8W',
			]
			: [
				'username' => 'dev-store-kwik-labs',
				'password' => 'rSsq3o22cYG07VWK3t1',
			];

		$response = self::_curl('/authToken', $payload, 'POST', false);

		log_kb([
			'Auth Token created : ' => json_encode($response)
		]);

		if (
			isset($response['status']) &&
			$response['status'] === 'SUCCESS' &&
			!empty($response['token'])
		) {
			self::_saveToken($response['token']);
			return $response['token'];
		}

		$this->error = $response['message'] ?? _li('Unable to generate token failed');

		return false;
	}

	private function _saveToken($token) {
		if (empty($token)) {
			return false;
		}

		$data = [
			'token'	 	 => $token,
			'expires_at' => time() + 86400, // 24 hrs
		];

		return $this->cache->save($this->key, json_encode($data), 86400);
	}

	private function _getToken() {
		$cached = $this->cache->get($this->key);

		if (!empty($cached)) {
			$data = json_decode($cached, true);

			if (
				!empty($data['token']) &&
				!empty($data['expires_at']) &&
				$data['expires_at'] > (time() + 300)
			) {
				return $data['token'];
			}
		}

		return self::_authenticate();
	}

	public function getRates($data = []) {
		log_kb(['Gokwik::getRates: ' => $data]);

		if (empty($data)) return [];

		$zone = self::_getZone($data['pickup_pincode'], $data['delivery_pincode']);

		log_kb(['Gokwik::zone: ' => $zone]);

		if (!$zone) return [];

		return self::_getRateCard($zone, $data);
	}

	private function _getZone($pickup_pincode, $delivery_pincode) {
		if (empty($delivery_pincode)) return false;

		$this->load->library('parsecsv');
		$this->parsecsv = $this->CI->parsecsv;
		$this->parsecsv->auto('assets/csv/courier/gokwik_zone.csv');

		$rows = $this->parsecsv->data;

		if (empty($rows)) return false;

		foreach ($rows as $row) {
			if (
				trim($row['Pickup Pincode']) == trim($pickup_pincode)
				&&
				trim($row['Delivery Pincode']) == trim($delivery_pincode)
			) {
				return strtoupper(trim($row['Standard Zone']));
			}
		}

		return false;
	}

	private function _getRateCard($zone, $data = []) {
		$forward = $additional = $rto = 0;

		if (empty($zone)) return false;

		$this->load->library('parsecsv');
		$this->parsecsv = $this->CI->parsecsv;
		$this->parsecsv->auto('assets/csv/courier/gokwik_rate.csv');

		$rows = $this->parsecsv->data;

		if (empty($rows)) return false;

		$rates = [];

		foreach ($rows as $key => $row) {
			$courier 	= $row['courier'];
			$rate 		= self::_calculateRate(
				$data['weight'],
				$row['fwd_' . $zone],
				$row['add_' . $zone],
			);

			$rates[] = [
				'id'					=> $key + 1,
				'name'				  	=> $courier,
				'courier_name'		  	=> $courier,
				'courier_id'			=> $this->courier['id'],
				'courier_company_id'	=> $row['id'],
				'rate'				  	=> round($rate, 2),
				'zone'				  	=> $zone,
			];
		}

		return $rates;
	}

	private function _calculateRate($weight, $forward, $additional) {
		$weight = (float) $weight;
		$weight_kg  = $weight / 1000;

		if ($weight_kg <= 0.5) {
			return (float) $forward;
		}

		$remaining_weight	= $weight_kg - 0.5;
		$extra_slabs		= ceil($remaining_weight / 0.5);

		return (float) $forward + ($extra_slabs * (float) $additional);
	}

	public function bookOrder($data = []) {
		if (empty($data)) return false;

		if ($data['order_type'] == 'school') {
			$drop_location_name	 = (!empty($data['user']['authorized_person']) ? ($data['user']['authorized_person']) : 'The Principal');
			$address_1			 = $data['drop_location']['name'] . ', ' . $data['drop_location']['address'];
		} else {
			$drop_location_name	 = $data['drop_location']['name'];
			$address_1			 = $data['drop_location']['address'];
		}

		$payload_data = [
			'serviceType'			=> '',
			'handOverMode'		  	=> '',
			'returnShipmentFlag'	=> 'false',
			'Shipment' 				=> [
				'SaleOrderCode'	 	=> $data['order_code'] ?? '',
				'orderCode'		 	=> $data['order_id'] ?? '',
				'code'			  	=> $data['order_code'] ?? '',
				'customField'		=> [],
				'channelCode'		=> '',
				'channelName'		=> '',
				'invoiceCode'		=> '',
				'orderDate'		 	=> date('d-M-Y H:i:s'),
				'fullFilllmentTat'  => date('d-M-Y H:i:s', strtotime('+1 day')),
				'weight'			=> (string)(!empty($data['weight']) ? round((float) $data['weight'], 4) : 500),		//gm
				'length'			=> !empty($data['length']) ? (string) round($data['length'] * 10) : '100',	//cm → mm = * 10
				'height'			=> !empty($data['height']) ? (string) round($data['height'] * 10) : '50',
				'breadth'			=> !empty($data['breadth']) ? (string) round($data['breadth'] * 10) : '100',
				'source'			=> 'bribooks',
				'numberOfBoxes' 	=> '1',
				'items'		 		=> self::_prepareItems($data['products'] ?? []),
			],
			'deliveryAddressId'	 	=> '',
			'deliveryAddressDetails'=> [
				'name'			  	=> $drop_location_name,
				'phone'			 	=> $data['drop_location']['mobile'],
				'address1'		  	=> $address_1,
				'address2'		  	=> $data['drop_location']['landmark'],
				'pincode'			=> $data['drop_location']['zipcode'],
				'city'			  	=> $data['drop_location']['city'],
				'state'			 	=> $data['drop_location']['state'],
				'country'			=> $data['drop_location']['country'],
				'stateCode'		 	=> self::_getStateCode($data['drop_location']['state'] ?? ''),
				'countryCode'		=> $data['drop_location']['country_code'],
				'poc_name'		  	=> '',
				'poc_email'		 	=> '',
			],
			'pickupAddressId'			=> '',
			'pickupAddressDetails'  => [
				'name'			  	=> $data['pickup_location']['name'],
				'phone'			 	=> $data['pickup_location']['mobile'],
				'address1'		  	=> $data['pickup_location']['address_1'],
				'address2'		  	=> $data['pickup_location']['address_2'],
				'pincode'			=> $data['pickup_location']['pincode'],
				'city'			  	=> $data['pickup_location']['city'],
				'state'			 	=> $data['pickup_location']['state'],
				'country'			=> 'India',
				'stateCode'		 	=> 'HR',
				'countryCode'		=> 'IN',
				'poc_name'		  	=> '',
				'poc_email'		 	=> ''
			],
			'returnAddressId'		=> '',
			'returnAddressDetails' 	=> [
				'email'			 	=> '',
				'gstin'			 	=> '',
				'name'			  	=> $data['pickup_location']['name'],
				'phone'			 	=> $data['pickup_location']['mobile'],
				'address1'		  	=> $data['pickup_location']['address_1'],
				'address2'		  	=> $data['pickup_location']['address_2'],
				'pincode'			=> $data['pickup_location']['pincode'],
				'city'			  	=> $data['pickup_location']['city'],
				'state'			 	=> $data['pickup_location']['state'],
				'country'			=> 'India',
				'stateCode'		 	=> 'HR',
				'countryCode'		=> 'IN',
			],
			'currencyCode' 			=> $data['currency_code'],  //'INR',
			'paymentMode' 			=> 'PREPAID',	// COD  or  PREPAID
			'totalAmount' 			=> $data['total'],	//(float) array_sum(array_column($data['products'], 'total')),
			'collectableAmount' 	=> '0.00', //0.00 for PREPAID
			'courierName' 			=> ''
		];

		log_kb(['Gokwik::payload: ' => $payload_data]);

		$response = self::_curl('/waybill', $payload_data, 'POST');

		if (isset($response['status']) && $response['status'] === 'SUCCESS') {
			if (!empty($awb_number = $response['waybill'])) {
				$scheduled_pickup = date('Y-m-d 15:31:00', strtotime('+1 day'));

				return [
					'pickup' => array_merge($response, [
						'scheduled_date'		=> $scheduled_pickup,
						'token_number'		  	=> $response['waybill'] ?? '',
						'remark'				=> $response['status'] . ': ' . $awb_number,
						'scheduled_timestamp'	=> strtotime($scheduled_pickup),
						'pickup_status'		 	=> 1,
						'routing_code'		  	=> $response['routingCode'],
					]),
					'order_id'		  			=> $awb_number,  //$data['order_id'],	//for tracking_details awb id as courier_order_id value for gokwik
					'order_code'				=> $data['order_code'],
					'order_type'				=> $data['order_type'],
					'shipment_id'				=> $data['shipment_id'],
					'awb_number'				=> $awb_number,
					'awb_code'		  			=> $awb_number,
					'token_number'	  			=> $response['waybill'] ?? '',
					'courier_name'	  			=> $response['courierName'],
					'routing_code'	  			=> $response['routingCode'],
					'shipping_label'			=> $response['shippingLabel'],
					'api_response'	  			=> $response,
				];
			} else {
				$this->error = _li('Unable to create shipment');
				return false;
			}
		} else {
			$this->error = $response['message'] ?? _li('Failed to create shipment');
			return false;
		}
	}

	public function generateAWB($payload = []) {
	}

	public function generatePickup($shipment_id = false) {
	}

	public function generateLabel($shipment_id = false) {
		$this->load->model('shipping/Shipment_model');
		$this->shipment_model = $this->CI->Shipment_model;
		$shipment_info = $this->shipment_model->get($shipment_id);

		if (empty($shipment_info)) return false;

		$tracking_info 	= json_decode($shipment_info['shipping_tracking_info'], true);
		$label_url 		= $tracking_info['shippingLabel'] ?? '';

		if (!empty($label_url)) {
			return ['label_url' => $label_url];
		} else {
			$this->error = _li('Label not generated');
			return false;
		}
	}

	public function generateInvoice($order_ids = false) {
	}

	public function generateManifests($order_ids = false) {
	}

	public function cancelOrder($order_ids = false) {
		$this->error = _li('Cancel Order not supported by GoKwik');
		return false;
	}

	public function cancelShipment($awbs = false) {
		if (empty($awbs)) {
			$this->error = _li('AWB required');
			return false;
		}

		$response = $this->_curl('/cancel', ['waybill' => $awbs], 'POST');

		if (isset($response['status']) && $response['status'] === 'SUCCESS') {
			return $response;
		} else {
			$this->error = $response['message'] ?? _li('Failed to cancel shipment');
			return false;
		}
	}

	public function fetchAWB($order_id = false) {
		$this->error = _li('Fetch AWB not supported by GoKwik');
		return false;
	}

	public function trackingDeatil($awbs = false) {
		if (empty($awbs)) {
			$this->error = _li('AWB required');
			return false;
		}

		$awbs = $awbs . ',';

		$response 	= self::_curl('/waybillDetails', ['waybills' => $awbs], 'GET');
		$status 	= strtoupper($response['waybillDetails'][0]['currentStatus'] ?? '');

		if (empty($status)) return false;

		if ($status == 'DELIVERED') {
			return [
				'order_status'	=> ORDER_STATUS['delivered'],
				'shipment_info'	=> [
					'status'	=> $status ?? '',
				]
			];
		} elseif (strpos($status, 'RETURN') !== false) {
			return [
				'order_status'	=> ORDER_STATUS['returned'],
				'shipment_info'	=> [
					'status'	=> $status ?? '',
				]
			];
		} elseif (strpos($status, 'CANCEL') !== false) {
			return [
				'order_status'	=> ORDER_STATUS['cancelled'],
				'shipment_info'	=> [
					'status'	=> $status ?? '',
				]
			];
		}

		return false;
	}

	public function trackingUrl($awb_number = false) {
		return sprintf('https://api.gokwik.co/kwikship/track/%s', $awb_number);
	}

	public function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		log_kb(['Gokwik::_curl::request:: ' => [
			'endpoint' 		=> $this->api_url . $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		$ch = curl_init();

		$headers = [
			'Content-Type: application/json'
		];

		if ($token) {
			$access_token = self::_getToken();

			if (!$access_token) {
				throw new Exception('Unable to generate token');
			}

			$headers[] = 'Authorization:' . $access_token;
		}

		$url = $this->api_url . $endpoint;

		if ($method === 'GET' && !empty($data)) {
			$url .= '?' . http_build_query($data);
		}

		curl_setopt_array($ch, [
			CURLOPT_URL 			=> $url,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_HTTPHEADER 		=> $headers,
			CURLOPT_TIMEOUT 		=> 30
		]);

		if ($method === 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		$raw_response = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch));
		}

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		log_kb(['Gokwik::response: ' =>  $raw_response]);

		curl_close($ch);

		$response = json_decode($raw_response, true);

		return $response;

	}

	private function _prepareItems($products = []) {
		if (empty($products) || !is_array($products)) {
			return [];
		}

		return array_map(function ($product) {
			$name = $product['name'] ?? 'Product';
			$qty  = (int) ($product['quantity'] ?? 1);
			$description = '';

			if (!empty($product['name'])) {
				$description .= $product['name'];
			}

			if (!empty($product['version'])) {
				$description .= ($description ? ' - ' : '') . $product['version'];
			}

			if (!empty($product['product_id'])) {
				$description .= ($description ? ' - ' : '') . 'sku(' . $product['product_id'] . ')';
			}

			$description .= ($description ? ' - ' : '') . 'qty(' . $qty . ')';

			return [
				'name'		 	=> $name,
				'description'  	=> $description,
				'quantity'	 	=> $qty,
				'skuCode'	  	=> 'sku(' . $product['product_id'] . ')' ?? '',
				'itemPrice'		=> (float) ($product['price'] ??  0),
				'brand'			=> $product['brand'] ?? '',
				'color'			=> $product['color'] ?? '',
				'category'	 	=> $product['category'] ?? '',
				'size'		 	=> $product['size'] ?? '',
				'item_details' 	=> $product['item_details'] ?? '',
				'ean'		  	=> $product['ean'] ?? '',
				'imageURL'	 	=> $product['image'] ?? '',
				'tags'		 	=> $product['tags'] ?? '',
				'hsnCode'	  	=> $product['hsn_code'] ?? '',
			];
		}, $products);
	}

	private function _getStateCode($name) {
		return $this->CI->db
			->select('code')
			->where('name', trim($name))
			->where('_deleted', 0)
			->get('state')
			->row()->code;
	}
}
