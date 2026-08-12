<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Aramex {
	public $aramex_allowed_history_status = [
		'SH012',
		'SH047',
		'SH278',
		'SH382',
		'SH069',
		'SH495',
		'SH022',
		'SH247',
		'SH033',
		'SH043',
		'SH294',
		'SH281', // Shipment Out for Delivery
		'SH004', // Out for Delivery - Partial
		'SH281', // Customs' Documents Out for Delivery
	];

	public $aramex_scan_status = [
		'SH012',
		'SH047',
		'SH278',
		'SH382',
		'SH314',
		'SH022',
		'SH005',
		'SH597',
		'SH069',
		'SH495',
		'SH247',
		'SH033',
		'SH043',
		'SH294',
		'SH281', // Shipment Out for Delivery
		'SH004', // Out for Delivery - Partial
		'SH281', // Customs' Documents Out for Delivery
	];

	public $aramex_status_mapping = [];

	private function _initAramex() {
		$this->aramex_status_mapping = [
			'SH012'	=> ORDER_STATUS['shipped'],
			'SH047'	=> ORDER_STATUS['shipped'],
			'SH278'	=> ORDER_STATUS['shipped'],
			'SH382'	=> ORDER_STATUS['shipped'],
			'SH314'	=> ORDER_STATUS['shipped'],
			'SH022'	=> ORDER_STATUS['shipped'],
			'SH005'	=> ORDER_STATUS['delivered'],
			'SH597'	=> ORDER_STATUS['delivered'],
			'SH069'	=> ORDER_STATUS['returned'],
			'SH495'	=> ORDER_STATUS['returned'],
			'SH247'	=> ORDER_STATUS['returned'],
			'SH033'	=> ORDER_STATUS['undelivered'],
			'SH043'	=> ORDER_STATUS['undelivered'],
			'SH294'	=> ORDER_STATUS['undelivered'],
			'SH281'	=> ORDER_STATUS['out_for_delivery'],
		];
	}

	public function aramex() {
		log_kb([
			'Aramex::Webhook Data:: ' 	=> $this->security->xss_clean($this->input->raw_input_stream),
			'Aramex::Webhook IP:: ' 	=> $this->input->ip_address()
		]);

		if (!in_array($this->input->ip_address(), [
			'94.185.237.64',
			'135.196.189.192',
			'87.86.187.192',
			'135.196.96.32',
			'69.210.67.48',
			'94.185.237.110',
		])) exit(_li('unauthorized'));

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) return;

		self::_initAramex();
		self::_formatAramexOrder($data);

		if (empty($data['order_id'])) exit(_li('order_not_found'));

		if (!empty($order_info = $this->order_model->getOrderByCode($data['order_id']))) {
			self::_updateOrder($data, $order_info, 'aramex');
		} elseif (strpos($data['order_id'], 'BBM-') !== false) {
			self::_updateMedallionOrder($data, 'aramex');
		} elseif (strpos($data['order_id'], 'BBS-') !== false) {
			self::_updateSchoolOrder($data, 'aramex');
		}
	}

	private function _formatAramexOrder(&$data = []) {
		$shipment_info = $this->db->get_where('shipment', [
			'courier_order_id'	=> $data['Value']['WaybillNumber'],
			'vendor_name'		=> 'aramex',
		])->row_array();

		if (empty($shipment_info)) return;

		if ($shipment_info['order_type'] == 'school') {
			$order_info = $this->school_order_model->get($shipment_info['order_id'] ?? 0);
		} elseif ($shipment_info['order_type'] == 'medallion') {
			$order_info = $this->medallion_order_model->get($shipment_info['order_id'] ?? 0);
		} else {
			$order_info = $this->order_model->get($shipment_info['order_id'] ?? 0);
		}

		$data['order_id'] = $order_info['order_code'] ?? '';
		$data['shipment_status'] = $data['Value']['UpdateCode'];
		$data['courier_name'] = 'aramex';
		$data['awb'] = $data['Value']['WaybillNumber'];
		$data['current_status_id'] = $data['Value']['UpdateCode'];
		$data['order_current_status_id'] = $this->aramex_status_mapping[$data['Value']['UpdateCode']];
		$data['current_status'] = $data['Value']['Comments'];
		$data['scans'] = [[
			'sr-status'	=> $data['Value']['UpdateCode'],
		]];
	}
}
