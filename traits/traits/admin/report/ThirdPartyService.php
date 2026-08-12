<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ThirdPartyService {
	private $_thirdparty_services = [
		'ses',
		'zoho',
		'2factor',
		'routemobile',
		'vonage',
		'onextel',
		// 'razorpay',
		// 'stripe',
		'signzy',
	];

	public function thirdparty_service($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'name',
			'reason',
			'status',
			'date_up',
			'date_down',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('thirdparty_service');
		$data['action_ajax'] 	= base_url('admin/ajax_thirdparty_service');

		$this->load->view('backend/index', $data);
	}

	public function ajax_thirdparty_service() {
		$this->load->library('Redis_lib');

		$json['data'] = [];

		$results = $this->_thirdparty_services;

		$json['recordsTotal'] 		= count($results);
		$json['recordsFiltered'] 	= count($results);

		foreach ($results ?? [] as $key => $result) {
			$cache_key 	= sprintf('%s_thirdparty_service_%s_status', ENVIRONMENT === 'production' ? 'live' : 'test', $result);
			$data 		= $this->redis_lib->get($cache_key);
			$format 	= 'M j, Y h:i A';

			$json['data'][] = [
				'sn'					=> 1 + $key,
				'name'					=> $result,
				'reason'				=> $data['reason'] ?? '',
				'status'				=> _sd($data['status'] ?? 1),
				'date_up'				=> !empty($data['date_up']) ? date($format, $data['date_up']) : '',
				'date_down'				=> !empty($data['date_down']) ? date($format, $data['date_down']) : '',
			];
		}

		output_json($json);
	}
}
