<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Dtdc {
	public $dtdc_allowed_history_status = [
		'BKD',
		'DLV',
		'RTO',
		'STOPDLV',
		'IPMF',
		'OPMF',
		'ORMF',
		'IBMD',
		'OBMD',
		'IBMN',
		'OBMN',
		'IMBM',
		'OMBM',
		'IRBO',
		'ORBO',
		'CDIN',
		'CDOUT',
		'NONDLV',
	];

	public $dtdc_scan_status = [
		'BKD',
		'DLV',
		'RTO',
		'STOPDLV',
		'IPMF',
		'OPMF',
		'ORMF',
		'IBMD',
		'OBMD',
		'IBMN',
		'OBMN',
		'IMBM',
		'OMBM',
		'IRBO',
		'ORBO',
		'CDIN',
		'CDOUT',
		'NONDLV',
	];

	public $dtdc_status_mapping = [];

	private function _initDtdc() {
		$this->dtdc_status_mapping = [
			'BKD'	  => ORDER_STATUS['shipped'],   // Shipped
			'DLV'	  => ORDER_STATUS['delivered'],   // Delivered
			'RTO'	  => ORDER_STATUS['returned'],  // Return
			'STOPDLV' => ORDER_STATUS['cancelled'],  // Cancelled
			'IPMF'	  => ORDER_STATUS['shipped'],
			'OPMF'	  => ORDER_STATUS['shipped'],
			'ORMF'	  => ORDER_STATUS['shipped'],
			'IBMD'	  => ORDER_STATUS['shipped'],
			'OBMD'	  => ORDER_STATUS['shipped'],
			'IBMN'	  => ORDER_STATUS['shipped'],
			'OBMN'	  => ORDER_STATUS['shipped'],
			'IMBM'	  => ORDER_STATUS['shipped'],
			'OMBM'	  => ORDER_STATUS['shipped'],
			'IRBO'	  => ORDER_STATUS['shipped'],
			'ORBO'	  => ORDER_STATUS['shipped'],
			'CDIN'	  => ORDER_STATUS['shipped'],
			'CDOUT'	  => ORDER_STATUS['shipped'],
			'OUTDLV'  => ORDER_STATUS['out_for_delivery'],
			'NONDLV'  => ORDER_STATUS['undelivered'],
		];
	}

	public function dtdc($token = '') {
		if ($token != '3QJuA82Us4tzBEoe2f5I') return false;

		log_kb([
			'Dtdc::Webhook Data:: '	=> $this->security->xss_clean($this->input->raw_input_stream),
			'Dtdc::Webhook IP:: ' 	=> $this->input->ip_address()
		]);

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) return;

		self::_initDtdc();
		self::_formatDtdcOrder($data);

		if (empty($data['order_id'])) exit(_li('order_not_found'));

		if (!empty($order_info = $this->order_model->getOrderByCode($data['order_id']))) {
			self::_updateOrder($data, $order_info, 'dtdc');
		} elseif (strpos($data['order_id'], 'BBM-') !== false) {
			self::_updateMedallionOrder($data, 'dtdc');
		} elseif (strpos($data['order_id'], 'BBS-') !== false) {
			self::_updateSchoolOrder($data, 'dtdc');
		}
	}

	private function _formatDtdcOrder(&$data = []) {
		$shipment_info = $this->db->get_where('shipment', [
			'awb_number' 	=> $data['shipment']['strShipmentNo'] ?? 0,
			'vendor_name'	=> 'dtdc',
		])->row_array();

		if (empty($shipment_info)) {
			$shipment_info = $this->db->get_where('shipment', [
				'awb_number' 	=> $data['shipment']['strShipmentNo'] ?? 0,
				'vendor_name'	=> 'dtdcb2b',
			])->row_array();
		}

		if (empty($shipment_info)) return;

		if ($shipment_info['order_type'] == 'school') {
			$order_info = $this->school_order_model->get($shipment_info['order_id'] ?? 0);
		} elseif ($shipment_info['order_type'] == 'medallion') {
			$order_info = $this->medallion_order_model->get($shipment_info['order_id'] ?? 0);
		} else {
			$order_info = $this->order_model->get($shipment_info['order_id'] ?? 0);
		}

		$status_code = array_column($data['shipmentStatus'], 'strAction')[0];

		$data['order_id'] = $order_info['order_code'] ?? '';
		$data['shipment_status'] = $status_code;
		$data['courier_name'] = 'dtdc';
		$data['awb'] = $data['shipment']['strShipmentNo'] ?? 0;
		$data['current_status_id'] = $status_code;
		$data['order_current_status_id'] = $this->dtdc_status_mapping[$status_code];
		$data['current_status'] = $status_code;
		$data['scans'] = [[
			'sr-status'	=> $status_code,
		]];
	}
}
