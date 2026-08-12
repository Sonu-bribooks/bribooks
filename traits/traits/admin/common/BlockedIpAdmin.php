<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BlockedIpAdmin {
	public function blocked_ip($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'ip',
			'attempt',
			'date_modified',
			'actions',
		];

		if ($param1 == 'status') {
			$this->blocked_ip_model->edit($param2, [
				'attempt' => 0,
				'blocked' => 0
			]);
			redirect(base_url('admin/blocked_ip'), 'refresh');
		}

		$data['page_name'] 		= 'blocked_ip/index';
		$data['page_title'] 	= _l('blocked_ip');
		$data['action_ajax'] 	= base_url('admin/ajax_blocked_ip');

		$data['actions'] 		= [
			[
				'key'	=> 'unblocked',
				'type' 	=> 'unblocked',
				'url'	=> 'admin/blocked_ip/status/',
			]
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_blocked_ip() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'blocked'			=> 1
		];

		$results = $this->blocked_ip_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'ip'					=> $result['ip'],
				'attempt'				=> $result['attempt'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'blocked' => $result['blocked'] ?? 0],
			];
		}

		output_json($json);
	}
}
