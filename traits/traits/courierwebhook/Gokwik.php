<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Gokwik {
	public $gokwik_allowed_history_status = [
		'PICKUP COMPLETED',
		'IN TRANSIT',
		'REACHED AT DESTINATION CENTER',
		'MISROUTED',
		'OUT FOR DELIVERY',
		'DELIVERED',
		'UNDELIVERED',
		'RTO INITIATED',
		'RTO IN TRANSIT',
		'RTO OUT FOR DELIVERY',
		'RTO DELIVERED',
		'RTO UNDELIVERED',
		'RETURN PICKUP INITIATED',
		'RETURN PICKUP COMPLETED',
		'RETURN IN TRANSIT',
		'RETURN OUT FOR DELIVERY',
		'RETURN DELIVERED',
		'RETURN UNDELIVERED',
		'RETURN CANCELLED',
		'RETURN PICKUP FAILED',
		'PICKUP ERROR',
		'PICKUP FAILED',
		'LOST',
		'DAMAGED',
		'DISPOSED OFF',
		'QC FAILED',
		'CANCELLED',
	];

	public $gokwik_scan_status = [];

	protected $gokwik_status_mapping = [];

	private function _initGokwik() {
		$this->gokwik_status_mapping = [
			'PICKUP COMPLETED'				  	=> ORDER_STATUS['shipped'],
			'IN TRANSIT'						=> ORDER_STATUS['shipped'],
			'REACHED AT DESTINATION CENTER'	 	=> ORDER_STATUS['shipped'],
			'REACHED AT SELLER CITY'			=> ORDER_STATUS['shipped'],
			'MISROUTED'						 	=> ORDER_STATUS['shipped'],

			'OUT FOR DELIVERY'				  	=> ORDER_STATUS['out_for_delivery'],

			'DELIVERED'						 	=> ORDER_STATUS['delivered'],

			'UNDELIVERED'					   	=> ORDER_STATUS['undelivered'],

			'RTO INITIATED'					 	=> ORDER_STATUS['returned'],
			'RTO IN TRANSIT'					=> ORDER_STATUS['returned'],
			'RTO OUT FOR DELIVERY'			  	=> ORDER_STATUS['returned'],
			'RTO DELIVERED'					 	=> ORDER_STATUS['returned'],
			'RTO UNDELIVERED'				   	=> ORDER_STATUS['returned'],

			'RETURN PICKUP INITIATED'		   	=> ORDER_STATUS['returned'],
			'RETURN PICKUP COMPLETED'		   	=> ORDER_STATUS['returned'],
			'RETURN IN TRANSIT'				 	=> ORDER_STATUS['returned'],
			'RETURN OUT FOR DELIVERY'		   	=> ORDER_STATUS['returned'],
			'RETURN DELIVERED'				  	=> ORDER_STATUS['returned'],
			'RETURN UNDELIVERED'				=> ORDER_STATUS['returned'],
			'RETURN PICKUP FAILED'			  	=> ORDER_STATUS['returned'],
			'RETURN CANCELLED'				  	=> ORDER_STATUS['returned'],

			'CANCELLED'						 	=> ORDER_STATUS['cancelled'],

			// 'PICKUP ERROR'					  	=> ORDER_STATUS['escalated'],
			// 'PICKUP FAILED'					 	=> ORDER_STATUS['escalated'],
			// 'LOST'							  	=> ORDER_STATUS['escalated'],
			// 'DAMAGED'						   	=> ORDER_STATUS['escalated'],
			// 'DISPOSED OFF'					  	=> ORDER_STATUS['escalated'],
			// 'QC FAILED'						 	=> ORDER_STATUS['escalated'],
		];
	}

	public function gokwik() {
		$token = $this->input->get_request_header('GK-API-KEY');

		if ($token !== 'goKwikToken_6zwhBpZQecPc2wClCUcQ5d1K') {
			return false;
		}

		log_kb([
			'GoKwik::Webhook Data:: '	   	=> $this->security->xss_clean($this->input->raw_input_stream),
			'GoKwik::Webhook IP:: '		 	=> $this->input->ip_address()
		]);

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) return;

		self::_initGokwik();
		self::_formatGokwikOrder($data);

		if (empty($data['order_id'])) exit(_li('order_not_found'));

		$order_info = $this->order_model->getOrderByCode($data['order_id']);

		if (empty($order_info)) return;

		if (!empty($order_info)) {
			self::_updateOrder($data, $order_info, 'gokwik');
		} elseif (strpos($data['order_id'], 'BBM-') !== false) {
			self::_updateMedallionOrder($data, 'gokwik');
		} elseif (strpos($data['order_id'], 'BBS-') !== false) {
			self::_updateSchoolOrder($data, 'gokwik');
		}
	}

	private function _formatGokwikOrder(&$data = []) {
		$shipment_info = $this->db->get_where('shipment', [
			'awb_number'	 	=> $data['awb'] ?? 0,
			'vendor_name'		=> 'gokwik',
		])->row_array();

		if (empty($shipment_info)) return;

		if ($shipment_info['order_type'] == 'school') {
			$order_info = $this->school_order_model->get($shipment_info['order_id'] ?? 0);
		} elseif ($shipment_info['order_type'] == 'medallion') {
			$order_info = $this->medallion_order_model->get($shipment_info['order_id'] ?? 0);
		} else {
			$order_info = $this->order_model->get($shipment_info['order_id'] ?? 0);
		}

		if (empty($order_info)) return;

		$status = strtoupper(trim($data['status']));

		$data['order_id']		   	= $order_info['order_code'] ?? '';
		$data['courier_name']	   	= 'gokwik';
		$data['shipment_status']	= $status;
		$data['current_status_id']  = $status;
		$data['current_status']	 	= $status;

		$data['order_current_status_id'] = $this->gokwik_status_mapping[$status];

		$data['description']		= $data['description'] ?? '';
		$data['shipper_name']	   	= $data['shipper_name'] ?? '';

		$data['scans'] = [
			[
				'sr-status'	 	=> $status,
				'description'   => $data['description'],
				'scan_time'	 	=> $data['status_datetime'] ?? date('Y-m-d H:i:s')
			]
		];
	}
}
