<?php defined('BASEPATH') or exit('No direct script access allowed');

trait OnlineStats {
	public function online_stats($param1 = null,$param2 = null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'user',
			'browser',
			'url',
			'referer',
			'date_added',
			'date_modified',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('online_stats');
		$data['action_add'] 	= '';
		$data['action_ajax'] 	= base_url('admin/ajax_online_stats');

		$data['actions'] 		= [];

		$this->load->view('backend/index', $data);
	}

	public function ajax_online_stats_old() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->online_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id'] ?? 0);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'user'					=> sprintf('%s/%s', $result['user_id'], ($user_info['first_name'] . ' ' . $user_info['last_name'])),
				'browser'				=> sprintf('%s/%s/%s', $result['ip'], $result['platform'], $result['browser']),
				'url'					=> $result['url'],
				'referer'				=> $result['referer'],
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> [],
			];
		}

		output_json($json);
	}

	public function ajax_online_stats() {
		$this->load->library('Online_lib');

		$results = $this->online_lib->get();

		$json['recordsTotal'] 		= count($results);
		$json['recordsFiltered'] 	= count($results);

		foreach ($results ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id'] ?? 0);

			$json['data'][] = [
				'sn'					=> $key + 1 + $key,
				'user'					=> sprintf('%s/%s', $result['user_id'], ($user_info['first_name'] . ' ' . $user_info['last_name'])),
				'browser'				=> sprintf('%s/%s/%s', $result['ip'], $result['platform'], $result['browser']),
				'url'					=> $result['url'],
				'referer'				=> $result['referer'],
				'date_added'			=> date('M j, Y h:i A', $result['time']),
				'date_modified'			=> date('M j, Y h:i A', $result['time']),
				'actions'				=> [],
			];
		}

		output_json($json);
	}
}
