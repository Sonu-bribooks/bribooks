<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/couriers/BaseCourier.php';

final class Aramex extends BaseCourier {
	public function __construct() {
		$this->api_url = 'https://ws.aramex.net/ShippingAPI.V2/';

		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('shipping/Courier_model');

		$this->courier_model = $this->CI->Courier_model;

		$this->courier = $this->courier_model->get_all([
			'status'		=> 1,
			'vendor_name'	=> 'aramex',
			'carrier_id'	=> 0,
			'is_domestic'	=> 0,
		])['rows'][0] ?? [];

		$this->account_number = ENVIRONMENT === 'production'
			? 'GGN10995'
			: '60531487'
		;

		$this->product_types = ENVIRONMENT === 'production' ? [
			'GB'	=> 'GPX',
			'US'	=> 'EPX',
		] : [];
	}

	private function _useCredentials(&$payload = [], $data = []) {
		if (ENVIRONMENT === 'production') {
			$payload['ClientInfo'] = [
				'UserName'			=> '',
				'Password'			=> '',
				'Version'			=> 'v1.0',
				'AccountNumber'		=> $this->account_number,
				'AccountPin'		=> '160344',
				'AccountEntity'		=> 'GGN',
				'AccountCountryCode'=> 'IN',
				'Source'			=> 24,
			];
		} else {
			$payload['ClientInfo'] = [
				'UserName'			=> 'test.api@aramex.com',
				'Password'			=> 'Aramex@12345',
				'Version'			=> 'v1.0',
				'AccountNumber'		=> $this->account_number,
				'AccountPin'		=> '654654',
				'AccountEntity'		=> 'BOM',
				'AccountCountryCode'=> 'IN',
				'Source'			=> 24,
			];
		}
	}

	public function getRates($data = [], $type = '') {
		$payload['OriginAddress'] = [
			'Line1'					=> $data['pickup_location']['address_1'],
			'Line2'					=> $data['pickup_location']['address_2'],
			'Line3'					=> $data['pickup_location']['address_3'],
			'City'					=> $data['pickup_location']['city'],
			'StateOrProvinceCode'	=> $data['pickup_location']['state'],
			'PostCode'				=> $data['pickup_location']['pincode'],
			'CountryCode'			=> 'IN',
		];

		$payload['ShipmentDetails'] = [
			'DescriptionOfGoods'	=> 'Books',
			'ChargeableWeight'		=> null,
			'GoodsOriginCountry' 	=> 'IN',
			'NumberOfPieces'		=> 1,
			'ProductGroup' 			=> 'EXP',
			'ProductType' 			=> $this->product_types[$data['country_code']] ?? 'PPX',
			'PaymentType' 			=> 'P',
			'PaymentOptions' 		=> '',
		];

		$payload['ShipmentDetails']['Dimensions'] = [
			'Length'	=> 10,
			'Width'		=> 10,
			'Height'	=> 10,
			'Unit'		=> 'cm'
		];
		$payload['ShipmentDetails']['ActualWeight'] = [
			'Unit'		=> 'KG',
			'Value'		=> round($data['weight'] / 1000, 2),
		];

		$payload['DestinationAddress'] = [
			'Line1'					=> $data['drop_location']['address'],
			'Line2'					=> '',
			'Line3'					=> '',
			'City'					=> $data['drop_location']['city'],
			'StateOrProvinceCode'	=> $data['drop_location']['state'],
			'PostCode'				=> $data['drop_location']['zipcode'],
			'CountryCode'			=> $data['country_code'],
		];

		$response = self::_curl(
			'RateCalculator/Service_1_0.svc/json/CalculateRate',
			$payload,
		);

		if (empty($response['HasErrors'])) {
			return [[
				'id'					=> $this->courier['id'],
				'name'					=> $this->courier['name'],
				'courier_name'			=> $this->courier['name'],
				'courier_id'			=> $this->courier['carrier_id'],
				'courier_company_id'	=> $this->courier['carrier_id'],
				'rate'					=> $response['TotalAmount']['Value'] ?? 0,
			]];
		} else {
			$this->error = $response['Message'] ?? '';
		}

		return false;
	}

	public function bookOrder($data = []) {
		$order_items = [];

		$pickup_date = date('Y-m-d 15:30:00', strtotime(sprintf('+%d days', (date('w') > 4 ? 8 - date('w') : 1))));

		if (!empty($data['products'])) {
			foreach ($data['products'] as $key => $product) {
				$option = json_decode($product['option'], true);

				$order_items[] = [
					'PackageType'		=> 'Box',
					'Quantity' 			=> !empty($product['quantity']) ? $product['quantity'] : 1,
					'Weight' 			=> null,
					'CustomsValue'		=> [
						'CurrencyCode'	=> $data['currency_code'],
						'Value'			=> $product['total'],
					],
					'Comments' 			=> !empty($product['name']) ? ($product['name'] . ' By ' . $product['author_name']) : '',
					'GoodsDescription' 	=> $product['name'],
					'Reference' 		=> '',
					'CommodityCode' 	=> '49011010',
				];
			}
		}

		$payload['Pickup']['PickupAddress'] = [
			'Line1'					=> $data['pickup_location']['address_1'],
			'Line2'					=> $data['pickup_location']['address_2'],
			'Line3'					=> $data['pickup_location']['address_3'],
			'City'					=> $data['pickup_location']['city'],
			'StateOrProvinceCode'	=> $data['pickup_location']['state'],
			'PostCode'				=> $data['pickup_location']['pincode'],
			'CountryCode'			=> 'IN',
		];
		$payload['Pickup']['PickupContact'] = [
			'PersonName' 			=> $data['pickup_location']['contact_name'],
			'CompanyName' 			=> $data['pickup_location']['name'],
			'PhoneNumber1' 			=> $data['pickup_location']['mobile'],
			'PhoneNumber1Ext'		=> '',
			'PhoneNumber2'			=> $data['pickup_location']['telephone'],
			'PhoneNumber2Ext'		=> '',
			'CellPhone'				=> $data['pickup_location']['mobile'],
			'EmailAddress'			=> $data['pickup_location']['email'],
			'Type'					=> '',
		];

		$payload['Pickup']['PickupLocation'] 	= $data['pickup_location']['address_1'];
		$payload['Pickup']['PickupDate'] 		= sprintf('/Date(%s000+0530)/', strtotime($pickup_date));
		$payload['Pickup']['ReadyTime'] 		= sprintf('/Date(%s000+0530)/', strtotime($pickup_date));
		$payload['Pickup']['LastPickupTime'] 	= sprintf('/Date(%s000+0530)/', strtotime('+6 hours', strtotime($pickup_date)));
		$payload['Pickup']['ClosingTime'] 		= sprintf('/Date(%s000+0530)/', strtotime('+6 hours', strtotime($pickup_date)));
		$payload['Pickup']['Comments'] 			= '';
		$payload['Pickup']['Reference1'] 		= $data['pickup_location']['contact_name'];
		$payload['Pickup']['Reference2'] 		= '';
		$payload['Pickup']['Vehicle'] 			= '';
		$payload['Pickup']['Status'] 			= 'Ready';
		$payload['Pickup']['ExistingShipments'] = null;
		$payload['Pickup']['Branch'] 			= '';
		$payload['Pickup']['RouteCode'] 		= '';

		$payload['Pickup']['Shipments'][0]['Shipper']['PartyAddress'] = [
			'Line1'					=> $data['pickup_location']['address_1'],
			'Line2'					=> $data['pickup_location']['address_2'],
			'Line3'					=> $data['pickup_location']['address_3'],
			'City'					=> $data['pickup_location']['city'],
			'StateOrProvinceCode'	=> $data['pickup_location']['state'],
			'PostCode'				=> $data['pickup_location']['pincode'],
			'CountryCode'			=> 'IN',
		];
		$payload['Pickup']['Shipments'][0]['Shipper']['AccountNumber'] = $this->account_number;
		$payload['Pickup']['Shipments'][0]['Shipper']['Contact'] = [
			'PersonName' 			=> $data['pickup_location']['contact_name'],
			'CompanyName' 			=> $data['pickup_location']['name'],
			'PhoneNumber1' 			=> $data['pickup_location']['mobile'],
			'PhoneNumber1Ext'		=> '',
			'PhoneNumber2'			=> $data['pickup_location']['telephone'],
			'PhoneNumber2Ext'		=> '',
			'CellPhone'				=> $data['pickup_location']['mobile'],
			'EmailAddress'			=> $data['pickup_location']['email'],
			'Type'					=> '',
		];

		$payload['Pickup']['Shipments'][0]['Consignee']['AccountNumber'] = '';
		$payload['Pickup']['Shipments'][0]['Consignee']['PartyAddress'] = [
			'Line1'					=> $data['drop_location']['address'],
			'Line2'					=> '',
			'Line3'					=> '',
			'City'					=> $data['drop_location']['city'],
			'StateOrProvinceCode'	=> $data['drop_location']['state'],
			'PostCode'				=> $data['drop_location']['zipcode'],
			'CountryCode'			=> $data['drop_location']['country_code'],
		];
		$payload['Pickup']['Shipments'][0]['Consignee']['Contact'] = [
			'Department'			=> '',
			'PersonName' 			=> $data['drop_location']['name'],
			'Title'					=> '',
			'CompanyName' 			=> $data['drop_location']['name'],
			'PhoneNumber1' 			=> $data['drop_location']['mobile'],
			'PhoneNumber1Ext'		=> '',
			'PhoneNumber2'			=> $data['user']['mobile'],
			'PhoneNumber2Ext'		=> '',
			'FaxNumber'				=> '',
			'CellPhone'				=> $data['drop_location']['mobile'],
			'EmailAddress'			=> $data['user']['email'],
			'Type'					=> $data['drop_location']['type'],
		];

		$payload['Pickup']['Shipments'][0]['Details'] = [
			'Dimensions'			=> null,
			'ActualWeight'			=> [
				'Unit'				=> 'KG',
				'Value'				=> round($data['weight'] / 1000, 2),
			],
			'DescriptionOfGoods'	=> ($order_items[0]['Comments'] ?? '') . (count($order_items) > 1 ? '+' . (count($order_items) - 1) . 'items' : ''),
			'GoodsOriginCountry'	=> 'IN',
			'NumberOfPieces'		=> 1,
			'ProductGroup'			=> 'EXP',
			'ProductType'			=> $this->product_types[$data['drop_location']['country_code']] ?? 'PPX',
			'PaymentType'			=> 'P',
			'PaymentOptions'		=> '',
			'CustomsValueAmount'	=> [
				'CurrencyCode'		=> $data['currency_code'],
				'Value'				=> $data['total'],
			],
			'CashOnDeliveryAmount'	=> null,
			'InsuranceAmount'		=> null,
			'CashAdditionalAmount'	=> null,
			'ChargeableWeight'		=> null,
			'CashAdditionalAmountDescription'=> '',
			'CollectAmount'			=> null,
			'Services'				=> '',
			'Items'					=> $order_items,
			'AdditionalProperties'	=> [
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'ShipperTaxIdVATEINNumber',
					'Value' 		=> '000000000'
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'ConsigneeTaxIdVATEINNumber',
					'Value' 		=> '000000000'
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'TaxPaid',
					'Value' 		=> '1'
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'InvoiceDate',
					'Value' 		=> date('m/d/Y', strtotime($data['date_added']))
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'InvoiceNumber',
					'Value' 		=> $data['order_code']
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'TaxAmount',
					'Value' 		=> '0'
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'IOSS',
					'Value' 		=> 'IM0000000000'
				],
				[
					'CategoryName' 	=> 'CustomsClearance',
					'Name' 			=> 'ExporterType',
					'Value' 		=> 'UT'
				]
			]
		];

		$payload['Pickup']['Shipments'][0]['ShippingDateTime'] = sprintf('/Date(%s000+0530)/', strtotime('+1 days', strtotime($pickup_date)));
		$payload['Pickup']['Shipments'][0]['DueDate'] = sprintf('/Date(%s000+0530)/', strtotime('+1 days', strtotime($pickup_date)));
		$payload['Pickup']['Shipments'][0]['Attachments'] = [];
		$payload['Pickup']['Shipments'][0]['ForeignHAWB'] = '';
		$payload['Pickup']['Shipments'][0]['TransportType'] = '';
		$payload['Pickup']['Shipments'][0]['PickupGUID'] = '';
		$payload['Pickup']['Shipments'][0]['Number'] = null;
		$payload['Pickup']['Shipments'][0]['ScheduledDelivery'] = null;
		$payload['Pickup']['PickupItems'][0] = [
			'ProductGroup' 		=> 'EXP',
			'ProductType' 		=> $this->product_types[$data['drop_location']['country_code']] ?? 'PPX',
			'NumberOfShipments'	=> 1,
			'PackageType' 		=> 'Box',
			'Payment' 			=> 'P',
			'ShipmentWeight' 	=> [
				'Unit' 			=> 'KG',
				'Value'			=> round($data['weight'] / 1000, 2),
			],
			'ShipmentVolume'	=> null,
			'NumberOfPieces'	=> 1,
			'CashAmount'		=> null,
			'ExtraCharges'		=> null,
			'ShipmentDimensions'=> [
					'Length'	=> 10,
					'Width'		=> 10,
					'Height'	=> 10,
					'Unit' 		=> 'cm'
			],
			'Comments'			=> '',
		];

		$response = self::_curl(
			'Shipping/Service_1_0.svc/json/CreatePickup',
			$payload,
		);

		$has_error 		= $response['HasErrors'];
		$error_message 	= $response['Message'];

		if (empty($has_error)) {
			foreach ($response['ProcessedPickup']['ProcessedShipments'] ?? [] as $item) {
				if (!empty($item['HasErrors'])) {
					$has_error 		= $item['HasErrors'];
					$error_message 	= $item['Notifications'][0]['Message'] ?? '';
					break;
				}
			}
		}

		if (empty($has_error)) {
			return [
				'pickup'			=> array_merge($response['ProcessedPickup'], [
					'scheduled_date'		=> $pickup_date,
					'token_number'			=> $response['ProcessedPickup']['ID'],
					'remark'				=> '',
					'scheduled_timestamp'	=> strtotime($pickup_date),
					'pickup_status'			=> 1,
				]),
				'order_id'			=> $response['ProcessedPickup']['ProcessedShipments'][0]['ID'],
				'shipment_id'		=> $response['ProcessedPickup']['ProcessedShipments'][0]['ID'],
				'awb_code'			=> $response['ProcessedPickup']['ProcessedShipments'][0]['ID'],
			];
		} else {
			$this->error = $error_message;
		}
	}

	public function generateAWB($params = []) {

	}

	public function generatePickup($shipment_id = false) {

	}

	public function generateLabel($shipment_ids = false) {
		$payload['LabelInfo'] = [
			'ReportID'		=> 9729,
			'ReportType'	=> 'URL',
		];
		$payload['ShipmentNumber'] = is_array($shipment_ids) ? $shipment_ids[0] : $shipment_ids;

		$response = self::_curl(
			'Shipping/Service_1_0.svc/json/PrintLabel',
			$payload,
		);

		if (empty($response['HasErrors'])) {
			return [
				'label_url'	=> $response['ShipmentLabel']['LabelURL'],
			];
		} else {
			$this->error = $response['Message'];
		}
	}

	public function generateInvoice($order_ids = false) {

	}

	public function generateManifests($order_ids = false) {

	}

	public function cancelOrder($order_ids = false) {

	}

	public function cancelShipment($awbs = false) {

	}

	public function fetchAWB($order_id = false) {

	}

	public function trackingDeatil($shipment_id = false) {
		$payload['GetLastTrackingUpdateOnly'] = false;
		$payload['Shipments'] = [$shipment_id];

		$response = self::_curl(
			'Tracking/Service_1_0.svc/json/TrackShipments',
			$payload,
		);

		if (empty($response['HasErrors'])) {
			$data = $response['TrackingResults'][0]['Value'][0];

			if (!empty($data['UpdateCode']) &&
				($data['UpdateCode'] == 'SH005' || $data['UpdateCode'] == 'SH597')
			) {
				return [
					'order_status'	=> 4,
					'shipment_info'	=> [
						'status'	=> $data['Comments'] ?? '',
					]
				];
			}

			if (!empty($data['UpdateCode']) &&
				$data['UpdateCode'] == 'SH069'
			) {
				return [
					'order_status'	=> 15,
					'shipment_info'	=> [
						'status'	=> $data['Comments'] ?? '',
					]
				];
			}
		} else {
			$this->error = $response['Message'];
		}
	}

	public function trackingUrl($awb_number = 0) {
		return sprintf('https://www.aramex.com/in/en/track/track-results-new?type=EXP&ShipmentNumber=%s', $awb_number);
		return sprintf('https://www.aramex.com/us/en/track/results?source=aramex&ShipmentNumber=%s', $awb_number);
	}

	protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		self::_useCredentials($data);

		log_kb(['aramex::_curl::request:: ' => [
			'endpoint'		=> $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		if (is_array($data)) {
			$data = json_encode($data);
		}

		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
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

		log_kb(['aramex::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->error,
		]]);

		return json_decode($response, true);
	}
}
