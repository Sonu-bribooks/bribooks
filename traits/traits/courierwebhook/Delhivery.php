<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Delhivery {
	public $delhivery_allowed_history_status = [
		'ud_in_transit',
		'ud_dispatched',
		'dl_delivered',
		'rt_in_transit',
		'dl_rto',
	];

	public $delhivery_scan_status = [
		'ud_in_transit',
		'ud_dispatched',
		'dl_delivered',
		'rt_in_transit',
		'dl_rto',
	];

	public $delhivery_status_mapping = [];

	private function _initDelhivery() {
		$this->delhivery_status_mapping = [
			'ud_in_transit'		=> ORDER_STATUS['shipped'],
			'ud_dispatched'		=> ORDER_STATUS['out_for_delivery'],
			'dl_delivered'		=> ORDER_STATUS['delivered'],
			'rt_in_transit'		=> ORDER_STATUS['returned'],
			'dl_rto'			=> ORDER_STATUS['returned'],
		];
	}

	public function delhivery($token = '') {
		log_kb([
			'delhivery::Webhook Data:: '=> $this->security->xss_clean($this->input->raw_input_stream),
			'delhivery::Webhook IP:: ' 	=> $this->input->ip_address()
		]);

		$ip = $this->input->ip_address();

		if ($token != 'e456c9ghe14fgh97104733fd18d47sd3eb9'){
			log_kb([
				'delhivery::Webhook::Token'=> _li('unauthorized'),
			]);

			return output_json([
				'success' => false
			]);
		}

		$allowed_ips = [
			'13.229.195.68',
			'18.139.238.62',
			'52.76.70.1',
			'3.108.106.65',
			'13.127.20.101',
			'13.126.12.240',
			'35.154.161.83',
			'3.6.106.39',
			'18.61.175.16'
		];

		if (!in_array($ip, $allowed_ips)) {
			log_kb([
				'delhivery::Webhook IP::Wrong' 	=> $ip
			]);

			exit(_li('wrong ip'));
		}

		$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

		if (empty($data)) {
			log_kb([
				'delhivery::Webhook::Data '=> _li('data_not_found'),
			]);

			return output_json([
				'success' => false
			]);
		}

		self::_initDelhivery();
		self::_formatDelhiveryOrder($data);

		if (empty($data['order_id'])) {
			log_kb([
				'delhivery::Webhook::Order'=> _li('order_not_found'),
			]);

			return output_json([
				'success' => false
			]);
		}

		if ($data['order_type'] == 'school') {
			self::_updateSchoolOrder($data, 'delhivery');
		} elseif ($data['order_type'] == 'medallion') {
			self::_updateMedallionOrder($data, 'delhivery');
		} else {
			$order_info = $this->order_model->getOrderByCode($data['order_id']);
			self::_updateOrder($data, $order_info, 'delhivery');
		}

		output_json([
			'success' => true
		]);
	}

	private function _formatDelhiveryOrder(&$data = []) {
		if (empty($data['Shipment']['AWB'] ?? '')) return;

		if (empty($shipment_info = $this->db->get_where('shipment', [
			'courier_order_id' 	=> $data['Shipment']['AWB'],
			'vendor_name'		=> 'delhivery',
		])->row_array())) return;

		if ($shipment_info['order_type'] == 'school') {
			$order_info = $this->school_order_model->get($shipment_info['order_id'] ?? 0);
		} elseif ($shipment_info['order_type'] == 'medallion') {
			$order_info = $this->medallion_order_model->get($shipment_info['order_id'] ?? 0);
		} else {
			$order_info = $this->order_model->get($shipment_info['order_id'] ?? 0);
		}

		$status_code_type = '';

		if (!empty($data['Shipment']['Status']['StatusType'] ?? '') && !empty($data['Shipment']['Status']['Status'] ?? '')) {
			$status_type  = strtolower(trim($data['Shipment']['Status']['StatusType']));
			$status_value = strtolower(trim(str_replace(' ', '_', $data['Shipment']['Status']['Status'])));

			$status_code_type = $status_type . '_' . $status_value;
		}

		$data['order_id']				   	= $order_info['order_code'] ?? '';
		$data['order_type']				   	= $shipment_info['order_type'];
		$data['shipment_status']			= $data['Shipment']['Status']['Status'] ?? '';
		$data['courier_name']			   	= 'delhivery';
		$data['awb']						= $data['Shipment']['AWB'] ?? '';
		$data['current_status_id']		  	= $status_code_type;
		$data['order_current_status_id']	= $this->delhivery_status_mapping[$status_code_type];
		$data['current_status']			 	= _l($data['Shipment']['Status']['Status'] ?? '');
		$data['scans'] = [[
			'sr-status'	=>$data['Shipment']['Status']['Status'] ?? '',
		]];
	}
}
