<?php defined('BASEPATH') or exit('No direct script access allowed');

trait League {
	public function getEventLeagues() {
		if (!$this->json) {
			$challenge_types = [
				'general' 	=> $this->event_challenge_general_model,
				'genre'   	=> $this->event_challenge_genre_model,
				'city'		=> $this->event_challenge_city_model,
				'state'   	=> $this->event_challenge_state_model,
				'country' 	=> $this->event_challenge_country_model,
			];

			$all_challenges = [];

			foreach ($challenge_types as $type => $model) {
				$rows = $model->get_all([
					'event_id' 	=> (int)$this->input->post('event_id'),
					'type'	 	=> 'user',
				])['rows'] ?? [];

				$result = array_map(function ($item) use ($type) {
					return [
						'id'   => $item['id'],
						'name' => $item['name'],
						'type' => $type
					];
				}, $rows);

				$all_challenges = array_merge($all_challenges, $result);
			}

			$this->json['leagues'] = $all_challenges;
		}
	}

	public function getChallengeLegendRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('challenge_id', _l('challenge_id'), [
			'trim',
			'required',
			'numeric',
		]);

		$this->form_validation->set_rules('type', _l('type'), [
			'trim',
			'required',
			'in_list[school,city,state,general,genre,weekly]',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Redis_lib');
			$this->load->library('Ranking_lib');

			$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($this->input->post('type')));

			if (file_exists($model_file_path)) {
				$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($this->input->post('type'))), sprintf('ranking_%s_model', strtolower($this->input->post('type'))));

				$model_name = sprintf('ranking_%s_model', strtolower($this->input->post('type')));

				$page   = $this->input->post('page') ?? 0;
				$limit  = $this->input->post('limit') ?? 10;

				$filter = [
					'event_id'					=> (int)$this->input->post('event_id'),
					'challenge_id'				=> (int)$this->input->post('challenge_id'),
					'search'					=> $this->input->post('search'),
					'is_moved'					=> 1,
					'start'						=> $page > 0 ? ($page - 1) * $limit : 0,
					'limit'						=> $limit
				];

				if (!empty($this->input->post('city_id'))) {
					$filter['city_id'] = $this->input->post('city_id');
				}

				if (!empty($this->input->post('school_id'))) {
					$filter['school_id'] = $this->input->post('school_id');
				}

				if (!empty($this->input->post('state_id'))) {
					$filter['state_id'] = $this->input->post('state_id');
				}

				$rank_results = $this->ranking_lib->getLegendRanks(
					$this->input->post('type'),
					$this->input->post('event_id'),
					$this->input->post('challenge_id'),
					(int) $this->input->post(sprintf('%s_id', strtolower($this->input->post('type')))) ?? 0,
					$this->input->post('page') ?? 1,
					$this->input->post('limit') ?? 0,
					$this->input->post('search')
				);

				$this->json['ranks'] = array_values($rank_results['ranks']);
				$this->json['total'] = $rank_results['total'];

				if ($this->input->post('book_id') && ($this->session->userdata('user_id') || $this->input->post('user_id'))) {
					$user_id = $this->input->post('user_id')
						? (int)$this->input->post('user_id')
						: (int)$this->session->userdata('user_id');
					$book_id = $this->input->post('book_id')
						? (int)$this->input->post('book_id')
						: 0;

					$filter['user_id'] = $user_id;
					$filter['book_id'] = $book_id;

					$user_rank	  = $this->{$model_name}->get_all($filter)['rows'][0] ?? [];

					if (!empty($user_rank)) {
						$user_rank_key 	= self::_getLegendKey($this->input->post('type'), $user_rank);

						if (!empty($user_rank_key)) {
							$rank			   = $this->redis_lib->getRank($user_rank_key, $user_rank['id']) + 1;
							$user_rank['rank']  = $rank ?? 0;
						}
						$this->json['user_rank'] = $user_rank;
					}

				}
			} else {
				$this->json['ranks'] = [];
				$this->json['total'] = 0;
			}
		}
	}

	private function _getLegendKey($stage = 'city', $rank_info = []) {
		return vsprintf('live_legendary_%s_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$stage,
			$rank_info['event_id'],
			$rank_info[sprintf('event_challenge_%s_id', $stage)],
			$rank_info[sprintf('%s_id', $stage)],
		]);
	}
}
