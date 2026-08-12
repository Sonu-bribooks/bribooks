<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DeactivateUser {
	private $_deactivate_user_filters = [];

	private function _initDeactivateUserFilters(&$data = [], $type = 'user') {
		$data['filters'][] 		= [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$this->_deactivate_user_filters = $data['filters'];
	}

	public function deactivate_user($action = NULL, $id = 0) {
		$this->load->model('user/DeactivateUser_model', 'deactivate_user_model');

		$data['fields'] = [
			'sn',
			'event',
			'name',
			'status',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			redirect(base_url('admin/deactivate_user'), 'refresh');
		} elseif ($action == 'edit') {
			// $this->deactivate_user_model->edit($id, $this->input->post());
			redirect(base_url('admin/deactivate_user'), 'refresh');
		} elseif ($action == 'status') {
			$info = $this->deactivate_user_model->get($id);

			if (!empty($info)) {
				$this->db->update('users', [
					'_deleted'		=> 0,
					'date_modified'	=> date('Y-m-d H:i:s'),
				],[
					'id' 	=> $info['user_id'],
				]);

				$this->db->update('event_user', [
					'_deleted'		=> 0,
					'date_modified'	=> date('Y-m-d H:i:s'),
				],[
					'event_id' 	=> $info['event_id'],
					'user_id' 	=> $info['user_id'],
				]);

				$this->deactivate_user_model->edit($id, [
					'status' => 0
				]);
			}

			redirect(base_url('admin/deactivate_user'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('deactivate_user');
		$data['action_ajax'] 	= base_url('admin/ajax_deactivate_users');

		$data['actions'] 		= [
			[
				'key'	=> 'restore',
				'type' 	=> 'confirm',
				'url'	=> 'admin/deactivate_user/status/',
			],
		];

		self::_initDeactivateUserFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function ajax_deactivate_users() {
		$temp_data = [];

		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'status'			=> 1,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		self::_initDeactivateUserFilters($temp_data, $type);

		foreach ($this->_deactivate_user_filters as $key => $item) {
			if ($this->input->get($item['key'])) {
				$filter_data[$item['key']] = is_numeric($this->input->get($item['key']))
					? (int)$this->input->get($item['key'])
					: $this->input->get($item['key']);
			}
		}

		if (!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		if (!empty($this->input->get('user_id'))) {
			$filter_data['user_id'] = $this->input->get('user_id');
		}

		$this->load->model('user/DeactivateUser_model', 'deactivate_user_model');

		$results = $this->deactivate_user_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id'] ?? 0);
			$user_info = $this->db->get_where('users', [
				'id' => $result['user_id'] ?? 0
			])->row_array();

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'event'					=> $event_info['name'] ?? '',
				'name'					=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
