<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventAwardGroup {
	public function event_award_group($action = NULL, $id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'type',
			'name',
			'thumb',
			'status',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			self::_validateEventTypeForm();

			$data = $this->input->post();

			if (is_array($data['event_award_ids'])) {
				$data['event_award_ids'] = implode(',', $data['event_award_ids']);
			}

			if (is_array($data['event_award_ids'])) {
				$data['event_award_ids'] = json_encode($data['event_award_ids']);
			}

			$this->event_award_group_model->add($data);
			redirect(base_url('admin/event_award_group'), 'refresh');
		} elseif ($action == 'edit') {
			self::_validateEventTypeForm($id);

			$data = $this->input->post();

			if (is_array($data['event_award_ids'])) {
				$data['event_award_ids'] = implode(',', $data['event_award_ids']);
			}

			if (is_array($data['event_award_ids'])) {
				$data['event_award_ids'] = json_encode($data['event_award_ids']);
			}

			$this->event_award_group_model->edit($id, $data);
			redirect(base_url('admin/event_award_group'), 'refresh');
		} elseif ($action == 'status') {
			$this->event_award_group_model->enableDisable($id, $this->input->post());
			redirect(base_url('admin/event_award_group'), 'refresh');
		} elseif ($action == 'delete') {
			$this->event_award_group_model->delete($id);
			redirect(base_url('admin/event_award_group'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_award_group');
		$data['action_add'] 	= base_url('admin/event_award_group_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_award_groups');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_award_group_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/event_award_group/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_award_group/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_award_group_form($action = NULL, $id = NULL) {
		if ($action == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_award_group_add');
			$data['action'] 						= base_url('admin/event_award_group/add');
		} elseif ($action == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_award_group_edit');
			$data['action'] 						= base_url('admin/event_award_group/edit/' . (int)$id);

			$data['id'] 							= (int)$id;
			$info 									= $this->event_award_group_model->get($id);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_event_awards?target=event_award_ids&input=checkbox&type='),
			'options'	=> [
				[
					'label'	=> _l('school'),
					'value'	=> 'school'
				],
				[
					'label'	=> _l('teacher'),
					'value'	=> 'teacher'
				],
				[
					'label'	=> _l('user'),
					'value'	=> 'user'
				],
				[
					'label'	=> _l('sub_user'),
					'value'	=> 'sub_user'
				],
			]
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'reference_id',
			'label'		=> _l('reference_id'),
			'required'	=> false,
			'value'		=> $info['reference_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'event_award_ids[]',
			'label'		=> _l('awards'),
			'required'	=> false,
			'value'		=> $info['event_award_ids'] ?? [],
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> true,
			'value'		=> $info['image'] ?? '',
		];


		$this->load->view('backend/index', $data);
	}

	public function ajax_event_award_groups() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_award_group_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'type'					=> $result['type'],
				'name'					=> $result['name'],
				'thumb'					=> $this->image_model->thumb($result['image']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_event_award_groups() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 30,
		];

		if ($this->input->get('search')) {
			$filter_data['search'] = $this->input->get('search');
		}

		if ($this->input->get('type')) {
			$filter_data['type'] = $this->input->get('type');
		}

		$results = $this->event_award_group_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
