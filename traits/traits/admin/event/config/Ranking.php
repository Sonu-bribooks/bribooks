<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Ranking {
	public $event_leagues 		= ['general', 'school', 'city', 'state', 'country'];
	public $event_league_types 	= ['school', 'teacher', 'user'];

	private function _getRanking($data = []) {
		$stage 				= $data['stage'] ?? 'ranking';
		$event_info 		= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		foreach ($this->event_league_types as $type) {
			foreach ($this->event_leagues as $league) {
				if ($type == $league) continue;

				$data['ranking_fields'][sprintf('%s_%s_challenge', $type, $league)] = self::_getRankingFields($event_info, $type, $league);
			}
		}

		$data['action'] = base_url('admin/ajax_event_ranking_crud/edit/' . $event_info['id']);

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}

	private function _getRankingFields($event_info = [], $type = 'school', $league = 'city') {
		$info = $this->{sprintf('event_challenge_%s_model', $league)}->get_all([
			'event_id'	=> $event_info['id'],
			'type'		=> $type
		])['rows'][0] ?? [];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('%s[%s][name]', $league, $type),
			'label'			=> _l('name'),
			'required'		=> true,
			'value'			=> $info['name'] ?? _l(sprintf('%s_%s_challenge', $type, $league)),
		];

		$data['fields'][] = [
			'type'			=> 'hidden',
			'key'			=> sprintf('%s[%s][type]', $league, $type),
			'label'			=> _l('type'),
			'required'		=> true,
			'value'			=> $type,
		];

		if ($type == 'user') {
			$data['fields'][] = [
				'type'			=> 'text',
				'key'			=> sprintf('%s[%s][book_sold]', $league, $type),
				'label'			=> _l('book_sold'),
				'required'		=> true,
				'value'			=> $info['book_sold'] ?? 1,
			];

			$data['fields'][] = [
				'type'			=> 'text',
				'key'			=> sprintf('%s[%s][max_book_sold]', $league, $type),
				'label'			=> _l('max_book_sold'),
				'required'		=> true,
				'value'			=> $info['max_book_sold'] ?? 10,
			];
		} else {
			$data['fields'][] = [
				'type'			=> 'text',
				'key'			=> sprintf('%s[%s][min_published]', $league, $type),
				'label'			=> _l('min_published'),
				'required'		=> true,
				'value'			=> $info['min_published'] ?? 1,
			];

			$data['fields'][] = [
				'type'			=> 'text',
				'key'			=> sprintf('%s[%s][max_published]', $league, $type),
				'label'			=> _l('max_published'),
				'required'		=> true,
				'value'			=> $info['max_published'] ?? 10,
			];
		}


		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> sprintf('%s[%s][display_date]', $league, $type),
			'label'		=> _l('display_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['display_date'] ?? date('Y-m-d H:i:s', strtotime('+10 minutes')),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> sprintf('%s[%s][start_date]', $league, $type),
			'label'		=> _l('start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['start_date'] ?? date('Y-m-d H:i:s', strtotime('+10 minutes')),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> sprintf('%s[%s][end_date]', $league, $type),
			'label'		=> _l('end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['end_date'] ?? date('Y-m-d H:i:s', strtotime('+30 days')),
		];

		return $this->load->view('backend/admin/event/stage/generic', $data, true);
	}

	public function ajax_event_ranking_crud($action = NULL, $event_id = 0) {
		$this->json = [];

		self::_validateRankingForm($action);

		if (empty($this->json['errors'])) {
			$data = $this->input->post();

			// pr($data, 1);

			foreach ($data as $league => $types) {
				$model = sprintf('event_challenge_%s_model', $league);

				foreach ($types as $type => $item) {
					$data = [
						'event_id'		=> (int)$event_id,
						'name'			=> $item['name'] ?? '',
						'type'			=> $type,
						'book_sold'		=> $item['book_sold'] ?? 0,
						'max_book_sold'	=> $item['max_book_sold'] ?? 0,
						'min_published'	=> $item['min_published'] ?? 0,
						'max_published'	=> $item['max_published'] ?? 0,
						'display_date'	=> date('Y-m-d H:i:s', strtotime($item['display_date'])),
						'start_date'	=> date('Y-m-d H:i:s', strtotime($item['start_date'])),
						'end_date'		=> date('Y-m-d H:i:s', strtotime($item['end_date'])),
					];

					log_kb($data);

					if (!empty($info = $this->{$model}->get_all([
						'event_id'	=> (int)$event_id,
						'type'		=> $type
					])['rows'][0] ?? [])) {
						$this->{$model}->edit($info['id'], $data);
					} else {
						$this->{$model}->add($data);
					}
				}
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateRankingForm($action = 'add') {
		foreach ($this->event_leagues as $league) {
			foreach ($this->event_league_types as $type) {
				if ($type == $league) continue;

				$this->form_validation->set_rules(sprintf('%s[%s][name]', $league, $type), _l(sprintf('%s_%s_name', $league, $type)), 'trim|required|min_length[3]|max_length[128]');

				if ($type == 'user') {
					$this->form_validation->set_rules(sprintf('%s[%s][book_sold]', $league, $type), _l(sprintf('%s_%s_book_sold', $league, $type)), 'trim|required|numeric');
					$this->form_validation->set_rules(sprintf('%s[%s][max_book_sold]', $league, $type), _l(sprintf('%s_%s_max_book_sold', $league, $type)), 'trim|required|numeric');

				} else {
					$this->form_validation->set_rules(sprintf('%s[%s][min_published]', $league, $type), _l(sprintf('%s_%s_min_published', $league, $type)), 'trim|required|numeric');
					$this->form_validation->set_rules(sprintf('%s[%s][max_published]', $league, $type), _l(sprintf('%s_%s_max_published', $league, $type)), 'trim|required|numeric');
				}

				($action == 'add') && $this->form_validation->set_rules(sprintf('%s[%s][start_date]', $league, $type), _l(sprintf('%s_%s_start_date', $league, $type)), [
					'trim',
					'required',
					['start_date', [$this->admin_validate_model, 'league_start_date']]
				]);
				$this->form_validation->set_rules(sprintf('%s[%s][end_date]', $league, $type), _l(sprintf('%s_%s_end_date', $league, $type)), [
					'trim',
					'required',
					['end_date', [$this->admin_validate_model, 'league_end_date']]
				]);
				($action == 'add') && $this->form_validation->set_rules(sprintf('%s[%s][display_date]', $league, $type), _l(sprintf('%s_%s_display_date', $league, $type)), [
					'trim',
					'required',
					['display_date', [$this->admin_validate_model, 'league_display_date']]
				]);
			}
		}

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
