<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Shiprocket {
	public $shiprocket_allowed_history_status = [
		19, // OUT FOR DELIVERY
		15, // RTO INITIATED
		// 6, // Shipped
		51, // PICKED UP
		20, // IN TRANSIT
		36, // UNDELIVERED
		37, // DELIVERY DELAYE
	];

	public $shiprocket_scan_status = [
		6, // Shipped
		7, // Delivered
		15, // Pickup Rescheduled
		17, // Out For Delivery
		18, // In Transit
		19, // Out For Pickup
		21, // Undelivered
		22, // Delayed
		26, // Fulfilled
		42, // Picked Up
		48, // Reached Warehouse
		49, // Custom Cleared
		50, // In Flight
		51, // Handover to Courier
		54, // In Transit Overseas
		56, // Reached Overseas Warehouse
		57, // Custom Cleared Overseas
	];

	public $shiprocket_status_mapping = [];

	private function _initShiprocket() {
		$this->shiprocket_status_mapping = [
			51	=> ORDER_STATUS['shipped'],
			20	=> ORDER_STATUS['shipped'],
			6	=> ORDER_STATUS['shipped'],
			7	=> ORDER_STATUS['delivered'],
			15	=> ORDER_STATUS['returned'],
			19	=> ORDER_STATUS['out_for_delivery'], // out for delivery,
			36	=> ORDER_STATUS['undelivered'],
		];
	}

	public function shiprocket() {
		if ($this->input->get_request_header('x-api-key') != 'shipbbtok56ffgf@37%~8*^') return;

		log_kb([
			'Shiprocket::Webhook Data:: ' 	=> $this->security->xss_clean($this->input->raw_input_stream),
			'Shiprocket::Webhook Token:: ' 	=> $this->input->get_request_header('x-api-key')
		]);

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) return;

		self::_initShiprocket();
		self::_formatShiprocketOrder($data);

		if (!empty($order_info = $this->order_model->getOrderByCode($data['order_id']))) {
			self::_updateOrder($data, $order_info, 'shiprocket');
		} elseif (strpos($data['order_id'], 'BBM-') !== false) {
			self::_updateMedallionOrder($data, 'shiprocket');
		} elseif (strpos($data['order_id'], 'BBS-') !== false) {
			self::_updateSchoolOrder($data, 'shiprocket');
		}
	}

	private function _formatShiprocketOrder(&$data = []) {
		$data['order_current_status_id'] = $this->shiprocket_status_mapping[$data['current_status_id']];
	}
}
