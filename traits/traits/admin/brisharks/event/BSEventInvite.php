<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSEventInvite {
	private function _initEventInviteFilters(&$data = []) {
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

	public function bs_event_invite($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data = $this->input->post();
			$this->bs_event_invite_model->add($data);

			redirect(base_url('admin/bs_event_invite'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();
			$this->bs_event_invite_model->edit($param2, $data);

			redirect(base_url('admin/bs_event_invite'), 'refresh');
		} elseif ($param1 == 'status') {
			$data = $this->input->post();
			$this->bs_event_invite_model->enableDisable($param2, $data);

			redirect(base_url('admin/bs_event_invite'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_event_invite_model->delete($param2);

			redirect(base_url('admin/bs_event_invite'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'event',
			'name',
			'url',
			'start_date',
			'end_date',
			'status',
			'date_added',
			'date_modified',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_invite');
		$data['action_add'] 	= base_url('admin/bs_event_invite_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_bs_event_invite');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_event_invite_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/bs_event_invite/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_event_invite/delete/',
			],
		];

		self::_initEventInviteFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function bs_event_invite_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_invite');
			$data['action'] 						= base_url('admin/bs_event_invite/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_event_invite');
			$data['action'] 						= base_url('admin/bs_event_invite/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_event_invite_model->get($param2);
			$event_info 							= $this->bs_event_model->get($info['event_id']);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_bs_search_event'),
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
			'key'		=> 'slug',
			'label'		=> _l('slug'),
			'required'	=> true,
			'value'		=> $info['slug'] ?? '',
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

	public function ajax_bs_event_invite() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		self::_initEventInviteFilters();
		self::_formatFilters($filter_data);

		$results = $this->bs_event_invite_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->bs_event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event'					=> sprintf('%s - %s', $event_info['name'], $event_info['id']),
				'name'					=> $result['name'],
				'url'					=> render_url(vsprintf('https://%s.brisharks.com/batch/%s/', [
					ENVIRONMENT === 'production' ? 'www' : 'uat',
					$result['slug'],
				]), _l('preview')),
				'status'				=> _sd($result['status']),
				'start_date'			=> formatDate($result['start_date']),
				'end_date'				=> formatDate($result['end_date']),
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'date_closed'			=> formatDate($result['date_closed']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

}
