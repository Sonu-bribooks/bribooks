<?php defined('BASEPATH') or exit('No direct script access allowed');

trait LeagueAward {
	public function league_award($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'type',
			'challenge',
            'challenge_id',
			'rank',
			'award',
            'thumb',
            'display_order',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->league_award_model->add($this->input->post());
			redirect(base_url('admin/league_award'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->league_award_model->edit($param2, $this->input->post());
			redirect(base_url('admin/league_award'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->league_award_model->delete($param2);
			redirect(base_url('admin/league_award'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('league_award');
		$data['action_add'] 	= base_url('admin/league_award_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_league_awards');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/league_award_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/league_award/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function league_award_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_league_award');
			$data['action'] 						= base_url('admin/league_award/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_league_award');
			$data['action'] 						= base_url('admin/league_award/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$award_info 							= $this->league_award_model->get($param2);
			$event_info 							= $this->event_model->get($award_info['event_id']);
			$challenge_info 						= !empty($award_info['challenge_id']) && !empty($award_info['type'])
				? $this->{sprintf('event_challenge_%s_model', $award_info['type'])}->get($award_info['challenge_id'])
				: [];

			$event_name							 	= ($award_info['event_id'] == 0) ? "Generic" : $event_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> false,
			'value'		=> [
				'value' => $award_info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_challenge_type'),
			'required'	=> false,
			'value'		=> $award_info['type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
			'options'	=> CHALLENGE_TYPES,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_id',
			'label'		=> _l('select_challenge'),
			'required'	=> false,
			'value'		=> $award_info['challenge_id'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'award_type',
			'label'		=> _l('select_award_type'),
			'required'	=> true,
			'value'	 => $award_info['award_type'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('book_rank'),
					'value'	=> 'book_rank',
				],
				[
					'label'	=> _l('book_sold'),
					'value'	=> 'book_sold',
				],
			],
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'rank',
			'label'		=> _l('rank_name'),
			'required'	=> true,
			'value'		=> $award_info['rank'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'min_rank',
			'label'		=> _l('min_rank'),
			'required'	=> false,
			'value'		=> $award_info['min_rank'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_rank',
			'label'		=> _l('max_rank'),
			'required'	=> false,
			'value'		=> $award_info['max_rank'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'award',
			'label'		=> _l('award'),
			'required'	=> true,
			'value'		=> $award_info['award'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> false,
			'value'		=> $award_info['image'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'sold_count',
			'label'		=> _l('sold_count'),
			'required'	=> false,
			'value'		=> $award_info['sold_count'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'display_order',
			'label'		=> _l('display_order'),
			'required'	=> true,
			'value'		=> $award_info['display_order'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'	 => $award_info['status'] ?? 1,
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

	public function ajax_league_awards() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->league_award_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info 	= $this->event_model->get($result['event_id']);
			$challenge_info = !empty($result['challenge_id']) && !empty($result['type'])
				? $this->{sprintf('event_challenge_%s_model', $result['type'])}->get($result['challenge_id'])
				: [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'type'					=> $result['type'],
				'challenge'				=> !empty($result['challenge_id'])
					? sprintf('%s (%s-%s)', $challenge_info['name'] ?? '', $result['challenge_id'], $result['type'])
					: '',
                'challenge_id'			=> $result['challenge_id'] ?? '',
				'rank'			        => $result['rank'],
				'award'				    => $result['award'],
                'thumb'					=> $this->image_model->thumb($result['image']),
                'display_order'         => $result['display_order'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
