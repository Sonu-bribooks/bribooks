<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Lesson {
	public function lesson($action = NULL, $id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'name',
			'description',
			'video_thumb',
			'video_url',
			'sort_order',
			'status',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			self::_validateLessonForm();

			$this->lesson_model->add($this->input->post());
			redirect(base_url('admin/lesson'), 'refresh');
		} elseif ($action == 'edit') {
			self::_validateLessonForm($id);

			$this->lesson_model->edit($id, $this->input->post());
			redirect(base_url('admin/lesson'), 'refresh');
		} elseif ($action == 'status') {
			$this->lesson_model->enableDisable($id, $this->input->post());
			redirect(base_url('admin/lesson'), 'refresh');
		} elseif ($action == 'delete') {
			$this->lesson_model->delete($id);
			redirect(base_url('admin/lesson'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('lesson');
		$data['action_add'] 	= base_url('admin/lesson_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_lessons');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/lesson_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/lesson/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/lesson/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function lesson_form($action = NULL, $id = NULL) {
		if ($action == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('lesson_add');
			$data['action'] 						= base_url('admin/lesson/add');
		} elseif ($action == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('lesson_edit');
			$data['action'] 						= base_url('admin/lesson/edit/' . (int)$id);

			$data['id'] 							= (int)$id;
			$info 									= $this->lesson_model->get($id);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('event'),
			'required'	=> false,
			'value'		=> $info['event_id'] ?? '',
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'description',
			'label'		=> _l('description'),
			'required'	=> true,
			'value'		=> $info['description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'video_thumb',
			'label'		=> _l('video_thumb'),
			'required'	=> true,
			'value'		=> $info['video_thumb'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'video_url',
			'label'		=> _l('video_url'),
			'required'	=> true,
			'value'		=> $info['video_url'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'sort_order',
			'label'		=> _l('sort_order'),
			'required'	=> true,
			'value'		=> $info['sort_order'] ?? '',
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

	public function ajax_lessons() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->lesson_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'name'					=> $result['name'],
				'description'			=> $result['description'],
				'video_thumb'			=> $result['video_thumb'],
				'video_url'				=> $result['video_url'],
				'sort_order'			=> $result['sort_order'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateLessonForm($id = 0) {

	}

	public function ajax_search_lessons() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->lesson_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
