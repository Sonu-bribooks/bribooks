<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSEventChallenge {
	private function _initEventChallengeFilters(&$data = []) {
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

	public function bs_event_challenge($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data 			= $this->input->post();
			$data['terms'] 	= $this->input->post('terms', FALSE);
			$this->bs_event_challenge_model->add($data);

			redirect(base_url('admin/bs_event_challenge'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data 			= $this->input->post();
			$data['terms'] 	= $this->input->post('terms', FALSE);
			$this->bs_event_challenge_model->edit($param2, $data);

			redirect(base_url('admin/bs_event_challenge'), 'refresh');
		} elseif ($param1 == 'status') {
			$data = $this->input->post();
			$this->bs_event_challenge_model->enableDisable($param2, $data);

			redirect(base_url('admin/bs_event_challenge'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_event_challenge_model->delete($param2);

			redirect(base_url('admin/bs_event_challenge'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'url',
			'slug',
			'min',
			'max',
			'limit',
			'start_date',
			'end_date',
			'display_date',
			'status',
			'date_added',
			'date_modified',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event');
		$data['action_add'] 	= base_url('admin/bs_event_challenge_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_bs_event_challenge');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_event_challenge_form/edit/',
			],
			[
				'key'	=> 'build_rank',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_event_challenge_build_rank/',
			],
			[
				'key'	=> 'rebuild_rank',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_event_challenge_rebuild_rank/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/bs_event_challenge/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_event_challenge/delete/',
			],
		];

		self::_initEventChallengeFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function bs_event_challenge_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_challenge');
			$data['action'] 						= base_url('admin/bs_event_challenge/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_event_challenge');
			$data['action'] 						= base_url('admin/bs_event_challenge/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_event_challenge_model->get($param2);
			$event_info 							= $this->bs_event_model->get($info['event_id']);
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
			'ajax_url'	=> base_url('admin/ajax_bs_search_event'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? 'user',
			'options'	=> [
				[
					'value' => 'school',
					'label' => _l('school'),
				],
				[
					'value' => 'city',
					'label' => _l('city'),
				],
				[
					'value' => 'state',
					'label' => _l('state'),
				],
				[
					'value' => 'country',
					'label' => _l('country'),
				],
			],
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
			'key'		=> 'slug',
			'label'		=> _l('slug'),
			'required'	=> true,
			'value'		=> $info['slug'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'base_url',
			'label'		=> _l('base_url'),
			'required'	=> true,
			'value'		=> $info['base_url'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'event_logo',
			'label'		=> _l('event_logo'),
			'required'	=> false,
			'value'		=> $info['event_logo'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'min',
			'label'		=> _l('min'),
			'value'		=> $info['min'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max',
			'label'		=> _l('max'),
			'value'		=> $info['max'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'limit',
			'label'		=> _l('limit'),
			'value'		=> $info['limit'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'formula',
			'label'		=> _l('formula'),
			'value'		=> $info['formula'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'show_formula',
			'label'		=> _l('show_formula'),
			'value'		=> $info['show_formula'] ?? '',
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
			'type'		=> 'textarea',
			'key'		=> 'terms',
			'label'		=> _l('terms'),
			'required'	=> false,
			'value'		=> $info['terms'] ?? '',
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

	public function ajax_bs_event_challenge() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		self::_initEventChallengeFilters();
		self::_formatFilters($filter_data);

		$results = $this->bs_event_challenge_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'url'					=> render_url($result['base_url'] . $result['slug'] , _l('preview')),
				'slug'					=> $result['slug'],
				'min'					=> $result['min'],
				'max'					=> $result['max'],
				'limit'					=> $result['limit'],
				'start_date'			=> formatDate($result['start_date']),
				'end_date'				=> formatDate($result['end_date']),
				'display_date'			=> formatDate($result['display_date']),
				'status'				=> _sd($result['status']),
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function bs_event_challenge_build_rank($id = 0) {
		$this->load->library('Redis_lib');

		$challenge_info = $this->bs_event_challenge_model->get($id);

		$results = $this->bs_startup_model->get_all([
			'event_id'	=> $challenge_info['event_id'],
			'start' 	=> 0,
			'limit'		=> 1000,
		])['rows'] ?? [];

		$rank_key = sprintf('%s:ranking:%s:%s',
			ENVIRONMENT === 'production' ? 'production' : 'test',
			$challenge_info['event_id'],
			$challenge_info['id']
		);

		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);

		$base_formula = !empty($challenge_info['formula']) ? $challenge_info['formula'] : 'round((jury + vote + (xp / total_xp * 100)) / 3, 3) * 1000';

		foreach ($results as $item) {
			if (empty($item['status']) || empty($item['jury'])) continue;

			$values = [
				'jury' 		=> $item['jury'],
				'vote' 		=> $item['vote'],
				'xp' 		=> $item['xp'],
				'total_xp' 	=> $item['total_xp'],
			];

			$formula = $base_formula;

			foreach ($values as $key => $value) {
				$formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/', $value, $formula);
			}

			if (empty($formula)) continue;

			eval('$score = ' . $formula . ';');

			$score_updated_at = date('Y-m-d H:i:s');

			if (!empty($rank_info = $this->bs_user_ranking_country_model->get_all([
				'event_id'				=> $challenge_info['event_id'],
				'event_challenge_id'	=> $challenge_info['id'],
				'country_id'			=> $item['country_id'],
				'user_id'				=> $item['user_id'],
				'startup_id'			=> $item['id'],
			])['rows'][0])) {
				$rank_id = $rank_info['id'];
				$score_updated_at = $rank_info['score_updated_at'];
				$this->bs_user_ranking_country_model->edit($rank_id, [
					'score'	=> $score,
				]);
			} else {
				$rank_id = $this->bs_user_ranking_country_model->add([
					'event_id'				=> $challenge_info['event_id'],
					'event_challenge_id'	=> $challenge_info['id'],
					'country_id'			=> $item['country_id'],
					'user_id'				=> $item['user_id'],
					'startup_id'			=> $item['id'],
					'startup_name'			=> $item['name'],
					'founder_name'			=> $item['founder_name'],
					'slug'					=> $item['slug'],
					'score'					=> $score,
					'score_updated_at'		=> $score_updated_at,
				]);
			}

			$new_score = $score . (99999999999 - (int)strtotime($score_updated_at));
			$this->redis_lib->updateRank(
				$rank_key,
				-$new_score,
				(string)$rank_id
			);

			log_kb(compact('new_score', 'score', 'rank_key', 'formula', 'base_formula', 'values'));
		}

		success_message(_li('rank_builded_successfully'));

		redirect(base_url('admin/bs_event_challenge'), 'refresh');
	}

	public function bs_event_challenge_rebuild_rank($id = 0) {
		$this->load->library('Redis_lib');
		$challenge_info = $this->bs_event_challenge_model->get($id);

		$results = $this->bs_user_ranking_country_model->get_all([
			'start' 				=> 0,
			'limit'					=> 1000,
			'event_id'				=> $challenge_info['event_id'],
			'event_challenge_id'	=> $challenge_info['id'],
		])['rows'] ?? [];

		$rank_key = sprintf('%s:ranking:%s:%s',
			ENVIRONMENT === 'production' ? 'production' : 'test',
			$challenge_info['event_id'],
			$challenge_info['id']
		);

		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);

		$base_formula = !empty($challenge_info['formula']) ? $challenge_info['formula'] : 'round((jury + vote + (xp / total_xp * 100)) / 3, 3) * 1000';

		foreach ($results as $item) {
			$startup_info = $this->bs_startup_model->get($item['startup_id']);

			// $score = round(($startup_info['jury'] + $startup_info['vote'] + ($startup_info['xp'] / $startup_info['total_xp'] * 100)) / 3, 1) * 10;
			// $score = round(($startup_info['jury'] + ($startup_info['vote'] / 100) + ($startup_info['xp'] / $startup_info['total_xp'] * 100)) / 3, 1) * 10;

			$values = [
				'jury' 		=> $startup_info['jury'],
				'vote' 		=> $startup_info['vote'],
				'xp' 		=> $startup_info['xp'],
				'total_xp' 	=> $startup_info['total_xp'],
			];

			$formula = $base_formula;

			foreach ($values as $key => $value) {
				$formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/', $value, $formula);
			}

			if (empty($formula)) continue;

			eval('$score = ' . $formula . ';');

			$new_score = $score . (99999999999 - (int)strtotime($item['score_updated_at']));
			$this->bs_user_ranking_country_model->edit($item['id'], [
				'score'	=> $score,
			]);
			$this->redis_lib->updateRank(
				$rank_key,
				-$new_score,
				(string)$item['id']
			);

			log_kb(compact('new_score', 'score', 'item', 'rank_key', 'formula', 'base_formula', 'values'));
		}

		success_message(_li('rank_rebuilded_successfully'));

		redirect(base_url('admin/bs_event_challenge'), 'refresh');
	}
}
