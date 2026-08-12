<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventChallengeGenre {
	public function event_challenges_genre($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'event',
			'type',
			'name',
			'url',
			'rank_limit',
			'book_sold',
			'book_published',
			'display_date',
			'start_date',
			'end_date',
			'actions',
		];

		if ($param1 == 'add') {
			$data 			= $this->input->post();
			$data['terms'] 	= $this->input->post('terms', FALSE);

			$this->event_challenge_genre_model->add($data);
			redirect(base_url('admin/event_challenges_genre'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data 			= $this->input->post();
			$data['terms'] 	= $this->input->post('terms', FALSE);

			$this->event_challenge_genre_model->edit($param2, $data);
			redirect(base_url('admin/event_challenges_genre'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_challenge_genre_model->delete($param2);
			redirect(base_url('admin/event_challenges_genre'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_challenges_genre');
		$data['action_add'] 	= base_url('admin/event_challenge_genre_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_challenges_genre');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_challenge_genre_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_challenges_genre/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_challenge_genre_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_challenge_genre');
			$data['action'] 						= base_url('admin/event_challenges_genre/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_challenge_genre');
			$data['action'] 						= base_url('admin/event_challenges_genre/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_challenge_genre_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);

			$event_name							 	= ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
		}

		$data['fields'][] = [
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
				[
					'value' => 'teacher',
					'label' => _l('teacher'),
				],
			],
		];

		$layouts = [];

		for ($i = 1; $i <= 10; $i++) {
			$layouts[] = [
				'value' => $i,
				'label' => $i,
			];
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'layout',
			'label'		=> _l('select_layout'),
			'required'	=> true,
			'value'		=> $info['layout'] ?? 0,
			'options'	=> $layouts,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'is_dark',
			'label'		=> _l('select_is_dark'),
			'required'	=> true,
			'value'		=> $info['is_dark'] ?? 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('light'),
				],
				[
					'value' => 1,
					'label' => _l('dark'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'event_logo',
			'label'		=> _l('event_logo'),
			'required'	=> true,
			'value'		=> $info['event_logo'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'background',
			'label'		=> _l('background'),
			'required'	=> true,
			'value'		=> $info['background'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'base_url',
			'label'		=> _l('base_url'),
			'required'	=> true,
			'value'		=> $info['base_url'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'slug',
			'label'		=> _l('slug'),
			'required'	=> true,
			'value'		=> $info['slug'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'book_sold',
			'label'		=> _l('book_sold'),
			'value'		=> $info['book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_book_sold',
			'label'		=> _l('max_book_sold'),
			'value'		=> $info['max_book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'min_published',
			'label'		=> _l('min_published(school/teacher)'),
			'value'		=> $info['min_published'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_published',
			'label'		=> _l('max_published(school/teacher)'),
			'value'		=> $info['max_published'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'rank_limit',
			'label'		=> _l('rank_limit'),
			'required'	=> true,
			'value'		=> $info['rank_limit'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'need_image',
			'label'		=> _l('need_image'),
			'required'	=> false,
			'value'		=> $info['need_image'] ?? 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('no'),
				],
				[
					'value' => 1,
					'label' => _l('yes'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'need_address',
			'label'		=> _l('need_address'),
			'required'	=> false,
			'value'		=> $info['need_address'] ?? 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('no'),
				],
				[
					'value' => 1,
					'label' => _l('yes'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'date_published',
			'label'		=> _l('date_published'),
			'required'	=> false,
			'value'		=> $info['date_published'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'display_date',
			'label'		=> _l('display_date'),
			'required'	=> true,
			'value'		=> $info['display_date'] ?? '',
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
			'type'		=> 'number',
			'key'		=> 'limit',
			'label'		=> _l('limit'),
			'required'	=> false,
			'value'		=> $info['limit'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'terms',
			'label'		=> _l('terms'),
			'required'	=> false,
			'value'		=> $info['terms'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_challenges_genre() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_challenge_genre_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'event'					=> $event_info['name'] ?? '',
				'type'					=> $result['type'],
				'name'					=> $result['name'],
				'url'					=> !empty($result['slug']) ? sprintf('<a href="%s" target="_blank">%s</a>', $result['base_url'] . $result['slug'], _l('visit')) : '',
				'rank_limit'			=> $result['rank_limit'],
				'book_sold'				=> $result['book_sold'],
				'book_published'		=> $result['date_published'],
				'display_date'			=> $result['display_date'],
				'start_date'			=> $result['start_date'],
				'end_date'				=> $result['end_date'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
