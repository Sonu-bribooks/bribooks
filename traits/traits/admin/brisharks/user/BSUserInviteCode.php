<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSUserInviteCode {
	public function bs_user_invite_code($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'type',
			'event_id',
			'bb_user',
			'bb_user_id',
			'user_id',
			'code',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();
			$data['event_id'] 	= 1;
			$data['code'] 		= sha1(md5(($data['bb_user_id']) . $this->config->item('password_salt') . $data['event_id'] . uniqid()));;
			$this->bs_user_invite_code_model->add($data);
			redirect(base_url('admin/bs_user_invite_code'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();
			$this->bs_user_invite_code_model->edit($param2, $data);
			redirect(base_url('admin/bs_user_invite_code'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->bs_user_invite_code_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/bs_user_invite_code'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_user_invite_code_model->delete($param2);
			redirect(base_url('admin/bs_user_invite_code'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('bs_user_invite_code');
		$data['action_add'] 	= base_url('admin/bs_user_invite_code_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_bs_user_invite_code');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_user_invite_code_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/bs_user_invite_code/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_user_invite_code/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function bs_user_invite_code_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('bs_user_invite_code_add');
			$data['action'] 						= base_url('admin/bs_user_invite_code/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('bs_user_invite_code_edit');
			$data['action'] 						= base_url('admin/bs_user_invite_code/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_user_invite_code_model->get($param2);
			$user_info 								= $this->user_model->get($info['bb_user_id']);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'bb_user_id',
			'label'		=> _l('select_user'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['bb_user_id'] ?? '',
				'label' => $user_info['first_name'] . ' ' . $user_info['last_name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_students'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
			'options'	=> [
				[
					'label'	=> _l('enable'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('disable'),
					'value'	=> 0,
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_bs_user_invite_code() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->bs_user_invite_code_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['bb_user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'type'					=> $result['type'],
				'event_id'				=> $result['event_id'],
				'bb_user'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'bb_user_id'			=> $result['bb_user_id'],
				'user_id'				=> $result['user_id'],
				'code'					=> $result['code'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
