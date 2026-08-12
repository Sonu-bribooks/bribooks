<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventChallengeCountry {
	public function event_challenges_country($param1 = null, $param2 = null) {
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
			'need_invite',
			'display_date',
			'start_date',
			'end_date',
			'actions',
		];

		if ($param1 == 'add') {
			$data 			= $this->input->post();
			$data['terms'] 	= $this->input->post('terms', FALSE);

			$this->event_challenge_country_model->add($data);
			redirect(base_url('admin/event_challenges_country'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data 			= $this->input->post();
			$data['terms'] 	= $this->input->post('terms', FALSE);

			$this->event_challenge_country_model->edit($param2, $data);
			redirect(base_url('admin/event_challenges_country'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_challenge_country_model->delete($param2);
			redirect(base_url('admin/event_challenges_country'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_challenges_country');
		$data['action_add'] 	= base_url('admin/event_challenge_country_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_challenges_country');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_challenge_country_form/edit/',
			],
			[
				'key'	=> 'build_rank',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_challenges_country_build_rank/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_challenges_country/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_challenge_country_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_challenge_country');
			$data['action'] 						= base_url('admin/event_challenges_country/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_challenge_country');
			$data['action'] 						= base_url('admin/event_challenges_country/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_challenge_country_model->get($param2);
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
			'required'	=> false,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_book_sold',
			'label'		=> _l('max_book_sold'),
			'value'		=> $info['max_book_sold'] ?? '',
			'required'	=> false,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'min_published',
			'label'		=> _l('min_published(school/teacher)'),
			'value'		=> $info['min_published'] ?? '',
			'required'	=> false,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_published',
			'label'		=> _l('max_published(school/teacher)'),
			'required'	=> false,
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
			'type'		=> 'select',
			'key'		=> 'need_invite',
			'label'		=> _l('need_invite'),
			'required'	=> false,
			'value'		=> $info['need_invite'] ?? 0,
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
			'key'		=> 'is_moved',
			'label'		=> _l('is_moved'),
			'required'	=> false,
			'value'		=> $info['is_moved'] ?? 0,
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

	public function ajax_event_challenges_country() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_challenge_country_model->get_all($filter_data);

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
				'need_invite'			=> $result['need_invite'],
				'display_date'			=> $result['display_date'],
				'start_date'			=> $result['start_date'],
				'end_date'				=> $result['end_date'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function event_challenges_country_build_rank($challenge_id = 0) {
		$challenge_info = $this->event_challenge_country_model->get($challenge_id);

		if (empty($challenge_info)) {
			$this->session->set_flashdata('error_message', _li('event_challenge_country_not_found'));
			redirect(base_url('admin/event_challenges_country'), 'refresh');
		}

		if (strtotime($challenge_info['end_date']) < time()) {
			$this->session->set_flashdata('error_message', _li('event_challenge_country_is_not_running'));
			redirect(base_url('admin/event_challenges_country'), 'refresh');
		}

		// if (strtotime('+2 days', strtotime($challenge_info['start_date'])) < time()) {
		// 	$this->session->set_flashdata('error_message', _li('build_rank_only_for_next_2_days_after_start_challenge'));
		// 	redirect(base_url('admin/event_challenges_country'), 'refresh');
		// }

		$this->cron_model->add([
			'code'		=> 'buildRankCountry_' . (int)$challenge_id,
			'site_id'	=> 1,
			'action'	=> 'alert_model->buildRankCron',
			'data'		=> [[
				'event_id'		=> (int)$challenge_info['event_id'],
				'challenge_id'	=> (int)$challenge_id,
				'type'			=> 'country',
			]],
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);

		$this->session->set_flashdata('flash_message', _li('event_challenges_country_build_rank_is_added'));
		redirect(base_url('admin/event_challenges_country'), 'refresh');
	}
}
