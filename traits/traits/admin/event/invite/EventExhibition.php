<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventExhibition {
	private $_event_exhibition_filters = [];

	private function _initExhibitionFilters(&$data = []) {
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

		$data['filters'][]		= [
			'type'		=> 'select',
			'key'		=> 'user_type',
			'label'		=> _l('select_user_type'),
			'required'	=> false,
			'value'		=> 'student',
			'options'	=> [
				[
					'value' => 'user',
					'label' => _l('user'),
				],
				[
					'value' => 'school',
					'label' => _l('school'),
				],
			],
		];

		$this->_event_exhibition_filters = $data['filters'];
	}

	public function event_exhibition($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'type',
			'user_id',
			'site_id',
			'book_id',
			'award',
			'interview',
			'wall',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();

			$data['award'] 		= implode(',', $data['award']);
			$data['interview'] 	= implode(',', $data['interview']);
			$data['wall'] 		= implode(',', $data['wall']);

			$this->event_exhibition_model->add($param2, $data);
			redirect(base_url('admin/event_exhibition'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();

			$data['award'] 		= implode(',', $data['award']);
			$data['interview'] 	= implode(',', $data['interview']);
			$data['wall'] 		= implode(',', $data['wall']);

			$this->event_exhibition_model->edit($param2, $data);
			redirect(base_url('admin/event_exhibition'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_exhibition_model->delete($param2);
			redirect(base_url('admin/event_exhibition'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_exhibition');
		$data['action_add'] 	= base_url('admin/event_exhibition_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_exhibition');
		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_exhibition_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_exhibition/delete/',
			],
		];

		self::_initExhibitionFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_exhibition() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$json = self::_format_event_exhibition($filter_data);

		output_json($json);
	}

	private function _format_event_exhibition($filter_data = []) {
		$temp_data = [];
		self::_initExhibitionFilters($temp_data, $type);

		foreach ($this->_event_exhibition_filters as $key => $item) {
			if ($this->input->get($item['key'])) {
				$filter_data[$item['key']] = is_numeric($this->input->get($item['key']))
					? (int)$this->input->get($item['key'])
					: $this->input->get($item['key']);
			}
		}

		$results = $this->event_exhibition_model->get_all($filter_data);

		$data['data'] 				= [];
		$data['recordsTotal'] 		= $results['total'];
		$data['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$data['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'type'					=> $result['type'],
				'user_id'				=> $result['user_id'],
				'site_id'				=> $result['site_id'],
				'book_id'				=> $result['book_id'],
				'award'					=> $result['award'],
				'interview'				=> $result['interview'],
				'wall'					=> $result['wall'],
				'date_modified'			=> format_date($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		return $data;
	}

	public function event_exhibition_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_exhibition');
			$data['action'] 						= base_url('admin/event_exhibition/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_exhibition');
			$data['action'] 						= base_url('admin/event_exhibition/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_exhibition_model->get($param2);
			$event_info								= $this->event_model->get($info['event_id']);
			$user_info								= $this->user_model->get($info['user_id']);
			$site_info								= $this->site_model->get($info['site_id']);
			$book_info								= $this->book_model->get($info['book_id']);
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
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? 'user',
			'options'	=> [
				[
					'value' => 'user',
					'label' => _l('user'),
				],
				[
					'value' => 'school',
					'label' => _l('school'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'user_id',
			'label'		=> _l('select_user'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['user_id'] ?? '',
				'label' => $user_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_students'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'site_id',
			'label'		=> _l('select_site'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['site_id'] ?? '',
				'label' => $site_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_sites'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'book_id',
			'label'		=> _l('select_book'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['book_id'] ?? '',
				'label' => $book_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_books'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'required'	=> false,
			'label'		=> _l('awards'),
			'fields'	=> self::_renderExhibitionMultiFields($info, 'award'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'required'	=> false,
			'label'		=> _l('interview'),
			'fields'	=> self::_renderExhibitionMultiFields($info, 'interview'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'required'	=> false,
			'label'		=> _l('wall'),
			'fields'	=> self::_renderExhibitionMultiFields($info, 'wall'),
		];

		$this->load->view('backend/index', $data);
	}

	private function _renderExhibitionMultiFields($info = [], $type = '') {
		$fields 	= [];
		$items 		= explode(',', $info[$type] ?? '');
		$index 		= 0;

		foreach ($items as $item) {
			$fields[] = [[
				'type'		=> 'file',
				'key'		=> sprintf('%s[%d]', $type, $index),
				's3_bucket'	=> 'bbprivateimagesin',
				's3_region'	=> 'ap-south-1',
				'label'		=> '',
				'required'	=> false,
				'value'		=> $item,
			]];

			++$index;
		}

		return $fields;
	}
}
