<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Bluedart {
	public $bluedart_allowed_history_status = [
		'025-S',
		'500-S',
		'536-T',
		'544-T',
		'000-T',
		'001-S',
		'002-S',
		'004-S',
		'008-T',
		'009-S',
		'015-S',
		'142-T',
		'028-S',
		'563-T',
		'016-RT',
		'019-RT',
		'034-RT',
		'000-RT',
		'123-RT',
		'596-T',
		'098-T'
	];

	public $bluedart_scan_status = [
		'025-S',
		'500-S',
		'536-T',
		'544-T',
		'000-T',
		'001-S',
		'002-S',
		'004-S',
		'008-T',
		'009-S',
		'015-S',
		'142-T',
		'028-S',
		'563-T',
		'016-RT',
		'019-RT',
		'034-RT',
		'000-RT',
		'123-RT',
		'596-T',
		'098-T'
	];

	public $bluedart_status_mapping = [];

	private function _initBluedart() {
		$this->bluedart_status_mapping = [
			'025-S' 	=> ORDER_STATUS['shipped'],
			'500-S' 	=> ORDER_STATUS['shipped'],
			'536-T' 	=> ORDER_STATUS['shipped'],
			'544-T' 	=> ORDER_STATUS['shipped'],
			'000-T' 	=> ORDER_STATUS['delivered'],
			'001-S' 	=> ORDER_STATUS['shipped'],
			'002-S' 	=> ORDER_STATUS['out_for_delivery'],
			'004-S' 	=> ORDER_STATUS['shipped'],
			'008-T' 	=> ORDER_STATUS['shipped'],
			'009-S' 	=> ORDER_STATUS['shipped'],
			'015-S' 	=> ORDER_STATUS['shipped'],
			'142-T' 	=> ORDER_STATUS['shipped'],
			'028-S' 	=> ORDER_STATUS['shipped'],
			'563-T' 	=> ORDER_STATUS['cancelled'],
			'016-RT' 	=> ORDER_STATUS['returned'],
			'019-RT' 	=> ORDER_STATUS['returned'],
			'034-RT' 	=> ORDER_STATUS['returned'],
			'000-RT' 	=> ORDER_STATUS['returned'],
			'123-RT' 	=> ORDER_STATUS['returned'],
			'596-T' 	=> ORDER_STATUS['cancelled'],
			'098-T' 	=> ORDER_STATUS['cancelled'],
		];
	}

	public function bluedart($token = '') {
		if ($token != '3QJuA82Us4tzBEoe2f5I') return false;

		log_kb([
			'Bluedart::Webhook Data:: '	=> $this->security->xss_clean($this->input->raw_input_stream),
			'Bluedart::Webhook IP:: ' 	=> $this->input->ip_address()
		]);

		$ip_address = ['165.72.200.13', '199.40.127.49', '156.137.9.65'];

		// if (!in_array($this->input->ip_address(), $ip_address)) return false;

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) return;

		self::_initBluedart();
		self::_formatBluedartOrder($data);

		if (strpos($data['order_id'], 'BB-') !== false && !empty($order_info = $this->order_model->getOrderByCode($data['order_id']))) {
			self::_updateOrder($data, $order_info, 'bluedart');
		} elseif (strpos($data['order_id'], 'BBM-') !== false) {
			self::_updateMedallionOrder($data, 'bluedart');
		} elseif (strpos($data['order_id'], 'BBS-') !== false) {
			self::_updateSchoolOrder($data, 'bluedart');
		}
	}

	private function _formatBluedartOrder(&$data = []) {
		$shipment_info = $this->db->get_where('shipment', [
			'awb_number' 		=> $data['statustracking'][0]['Shipment']['WaybillNo'] ?? 0,
			'vendor_name'		=> 'bluedart',
		])->row_array();

		if (empty($shipment_info)) return;

		if ($shipment_info['order_type'] == 'school') {
			$order_info = $this->school_order_model->get($shipment_info['order_id'] ?? 0);
		} elseif ($shipment_info['order_type'] == 'medallion') {
			$order_info = $this->medallion_order_model->get($shipment_info['order_id'] ?? 0);
		} else {
			$order_info = $this->order_model->get($shipment_info['order_id'] ?? 0);
		}

		$status_code = $data['statustracking'][0]['Shipment']['Scans']['ScanDetail'][0]['ScanCode'] . '-' . $data['statustracking'][0]['Shipment']['Scans']['ScanDetail'][0]['ScanGroupType'];

		$data['order_id'] = $order_info['order_code'] ?? '';
		$data['shipment_status'] = $status_code;
		$data['courier_name'] = 'bluedart';
		$data['awb'] = $shipment_info['awb_number'] ?? 0;
		$data['current_status_id'] = $status_code;
		$data['order_current_status_id'] = isset($this->bluedart_status_mapping[$status_code]) ? $this->bluedart_status_mapping[$status_code] : $status_code;
		$data['current_status'] = $status_code;
		$data['scans'] = [[
			'sr-status'	=> $status_code,
		]];
	}
}
