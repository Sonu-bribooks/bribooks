<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSEvent {
	private function _initEventFilters(&$data = []) {
		$data['filters'][] 		= [
			'type'		=> 'select2',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> false,
			'options'	=> [
				[
					'label' => _('enabled'),
					'value'	=> 1,
				],
				[
					'label' => _('disabled'),
					'value'	=> 0,
				],
			]
		];

		$this->_generic_filters = $data['filters'];
	}

	public function bs_event($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data = $this->input->post();
			$this->bs_event_model->add($data);

			redirect(base_url('admin/bs_event'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();
			$this->bs_event_model->edit($param2, $data);

			redirect(base_url('admin/bs_event'), 'refresh');
		} elseif ($param1 == 'status') {
			$data = $this->input->post();
			$this->bs_event_model->enableDisable($param2, $data);

			redirect(base_url('admin/bs_event'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_event_model->delete($param2);

			redirect(base_url('admin/bs_event'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'url',
			'slug',
			'start_date',
			'end_date',
			'user_reg_end_date',
			'school_reg_end_date',
			'exhibition_date',
			'status',
			'date_added',
			'date_modified',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event');
		$data['action_add'] 	= base_url('admin/bs_event_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_bs_event');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_event_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/bs_event/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_event/delete/',
			],
		];

		self::_initEventFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function bs_event_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event');
			$data['action'] 						= base_url('admin/bs_event/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_event');
			$data['action'] 						= base_url('admin/bs_event/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_event_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'slug',
			'label'		=> _l('slug'),
			'required'	=> true,
			'value'		=> $info['slug'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'url',
			'label'		=> _l('url'),
			'required'	=> true,
			'value'		=> $info['url'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'start_date',
			'label'		=> _l('start_date'),
			'required'	=> true,
			'value'		=> $info['start_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'end_date',
			'label'		=> _l('end_date'),
			'required'	=> true,
			'value'		=> $info['end_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'user_reg_end_date',
			'label'		=> _l('user_reg_end_date'),
			'required'	=> true,
			'value'		=> $info['user_reg_end_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'school_reg_end_date',
			'label'		=> _l('school_reg_end_date'),
			'required'	=> true,
			'value'		=> $info['school_reg_end_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'exhibition_date',
			'label'		=> _l('exhibition_date'),
			'required'	=> true,
			'value'		=> $info['exhibition_date'] ?? '',
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

	public function ajax_bs_event() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		self::_initEventFilters();
		self::_formatFilters($filter_data);

		$results = $this->bs_event_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'url'					=> render_url($result['url'], _l('preview')),
				'slug'					=> $result['slug'],
				'start_date'			=> formatDate($result['start_date']),
				'end_date'				=> formatDate($result['end_date']),
				'user_reg_end_date'		=> formatDate($result['user_reg_end_date']),
				'school_reg_end_date'	=> formatDate($result['school_reg_end_date']),
				'exhibition_date'		=> formatDate($result['exhibition_date']),
				'status'				=> _sd($result['status']),
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function ajax_bs_search_event() {
		$json = [];

		$filter_data = [
			'status'			=> 1,
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->bs_event_model->get_all($filter_data)['rows'] ?? [];

		$json[] = [
			'id'				=> 0,
			'text'				=> _l('generic'),
		];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
