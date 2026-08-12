<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BeingShips {
	public $beingships_allowed_history_status = [
		'pickup_scheduled',
		'picked_up',
		'in_transit',
		'out_for_delivery',
		'delivered',
		'ndr',
		'rto_in_transit',
		'rto_delivered',
		'cancelled',
	];

	public $beingships_scan_status = [
		'picked_up',
		'in_transit',
		'out_for_delivery',
		'delivered',
		'ndr',
		'rto_in_transit',
		'rto_delivered',
	];

	public $beingships_status_mapping = [];

	private function _initBeingships() {
		$this->beingships_status_mapping = [
			'picked_up'			=> ORDER_STATUS['shipped'],
			'in_transit'		=> ORDER_STATUS['shipped'],
			'out_for_delivery'	=> ORDER_STATUS['out_for_delivery'],
			'delivered'			=> ORDER_STATUS['delivered'],
			'ndr'				=> ORDER_STATUS['undelivered'],
			'rto_in_transit'	=> ORDER_STATUS['returned'],
			'rto_delivered'		=> ORDER_STATUS['returned'],
		];
	}

	public function beingships($token = '') {
		log_kb([
			'BeingShips::Webhook Data:: ' 	=> $this->security->xss_clean($this->input->raw_input_stream),
			'BeingShips::Webhook IP:: ' 	=> $this->input->ip_address()
		]);

		if ($token != '834276dftybvpncsdyutuytu126634') exit(_li('unauthorized'));

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) return;

		self::_initBeingships();
		self::_formatBeingShipsOrder($data);

		if (empty($data['order_id'])) exit(_li('order_not_found'));

		if (!empty($order_info = $this->order_model->getOrderByCode($data['order_id']))) {
			self::_updateOrder($data, $order_info, 'beingships');
		} elseif (strpos($data['order_id'], 'BBM-') !== false) {
			self::_updateMedallionOrder($data, 'beingships');
		} elseif (strpos($data['order_id'], 'BBS-') !== false) {
			self::_updateSchoolOrder($data, 'beingships');
		}
	}

	private function _formatBeingShipsOrder(&$data = []) {
		$shipment_info = $this->db->get_where('shipment', [
			'courier_order_id' 	=> $data['order_id'],
			'vendor_name'		=> 'beingships',
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
		$data['shipment_status'] = $data['status_code'];
		$data['courier_name'] = 'beingships';
		$data['awb'] = $data['awb_number'];
		$data['current_status_id'] = $data['status_code'];
		$data['order_current_status_id'] = $this->beingships_status_mapping[$data['status_code']];
		$data['current_status'] = _l($data['current_status']);
		$data['scans'] = [[
			'sr-status'	=> $data['status_code'],
		]];
	}
}
