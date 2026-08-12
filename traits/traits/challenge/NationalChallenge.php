<?php defined('BASEPATH') or exit('No direct script access allowed');

trait NationalChallenge {
	public function getNationalChallenge() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]',
			['slug', [$this->validate_model, 'event_challenge_country_slug']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$challenge_info = $this->event_challenge_country_model->getBySlug($this->input->post('slug'));

			$challenge_data = [
				'id'		=> $challenge_info['id'],
				'event_id'	=> $challenge_info['event_id'],
				'name'		=> $challenge_info['name'],
				'layout'	=> $challenge_info['layout'],
				'is_dark'	=> $challenge_info['is_dark'],
				'event_logo'=> format_gallery_url($challenge_info['event_logo']),
				'background'=> format_gallery_url($challenge_info['background']),
				'start_date'=> $challenge_info['display_date'],
				'end_date'	=> $challenge_info['end_date'],
				'counter'	=> date('Y-m-d\TH:i:s\Z', strtotime('-330 minutes', strtotime($challenge_info['end_date']))),
				'locked'	=> $challenge_info['display_date'] > date('Y-m-d H:i:s'),
				'limit'		=> $challenge_info['limit'],
				'terms'		=> html_entity_decode($challenge_info['terms'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
			];

			$this->json['challenge'] = $challenge_data;
		}
	}

	public function getNationalChallenges() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (!($event_info['start_date'] <= date('Y-m-d H:i:s'))) {
				$this->json['error'] = _l('event_not_started');
				return;
			}

			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');

			$results = $this->event_challenge_country_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id'),
				'type'		=> $this->input->post('type') ?? 'user',
			])['rows'] ?? [];

			$sort_order = $this->json['challenges'] = [];

			foreach ($results as $item) {
				$challenge_data = [
					'id'		=> $item['id'],
					'name'		=> $item['name'],
					'start_date'=> $item['display_date'],
					'end_date'	=> $item['end_date'],
					'counter'	=> date('Y-m-d\TH:i:s\Z', strtotime('-330 minutes', strtotime($item['end_date']))),
					'locked'	=> $item['display_date'] > date('Y-m-d H:i:s'),
				];

				$sort_order[] = $item['display_date'];

				if ($item['display_date'] <= date('Y-m-d H:i:s') && $item['end_date'] >= date('Y-m-d H:i:s')) {
					$this->json['active_challenge'] = $challenge_data;
					$this->json['active_counter'] = $challenge_data;
				} else if ($item['display_date'] >= date('Y-m-d H:i:s') && $item['end_date'] >= date('Y-m-d H:i:s')) {
					$this->json['active_counter'] = $challenge_data;
				}

				$this->json['challenges'][] = $challenge_data;
			}

			array_multisort($sort_order, $this->json['challenges']);
		}
	}

	public function getNationalChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_country_id', _l('event_challenge_country'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_country', [$this->validate_model, 'event_challenge_country']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');

			$rank_results = $this->ranking_lib->getCountryRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_country_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search'),
				$this->input->post('limit') ?? 0
			);

			$this->json['ranks'] = array_values($rank_results['ranks']);

			$this->json['total'] = $rank_results['total'];

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				$user_id = $this->input->post('user_id')
					? (int)$this->input->post('user_id')
					: (int)$this->session->userdata('user_id');
				$book_id = $this->input->post('book_id')
					? (int)$this->input->post('book_id')
					: 0;

				if (empty($this->json['user_rank'] = $this->ranking_lib->getUserCountryRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_country_id'),
					$user_id,
					$book_id,
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoCountryRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_country_id'),
						$user_id,
						$book_id,
					);
				}
			}

			$this->json['updated_rank'] = $this->ranking_lib->getCountryLastUpdatedRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_country_id'),
			);

			$event_info = $this->event_model->get($this->input->post('event_id'));
			$country_info = $this->country_model->get($event_info['country_id']);

			if ($this->input->post('event_id') == YABWF_EVENT_ID) {
				$this->json['heading'] = 'Bhavans Kuwait';
			} else {
				$this->json['heading'] = sprintf('%s', $country_info['name'] ?? '');
			}
		}
	}

	public function getNationalBestSeller() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_country_id', _l('event_challenge_country'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_country', [$this->validate_model, 'event_challenge_country']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');

			$rank_results = $this->ranking_lib->getCountryRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_country_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			// array_multisort(array_column($rank_results['ranks'], 'score'), SORT_DESC, $rank_results['ranks']);

			$results = array_values($rank_results['ranks']);

			$this->json['total'] = $rank_results['total'];

			$rankings = array_map(function($item, $key) {
				return [
					'id'			=> $item['id'],
					'rank'			=> $item['rank'],
					/*'rank'			=> (int)((($this->input->post('page') ?? 1) - 1) * 10) + (int)($key + 1),*/
					'name'			=> ucfirst($item['book_name']),
					'cover_image'	=> $item['book_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['book_slug'],
					'state'			=> '',
					'city'			=> '',
					'school'		=> '',
					'grade'			=> '',
					'section'		=> '',
					'royalty'		=> 0,
					'sold'			=> $item['score'],
					'total_sold'	=> $item['score'],
					'amazon_url'	=> $item['amazon_url'] ?? '',
					'message'		=> $item['message'] ?? '',
				];
			}, $results, array_keys($results));

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				if(empty($this->json['user_rank'] = $this->ranking_lib->getUserCountryRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_country_id'),
					$this->input->post('user_id')
						? $this->input->post('user_id')
						: $this->session->userdata('user_id')
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoCountryRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_country_id'),
						$this->input->post('user_id')
							? $this->input->post('user_id')
							: $this->session->userdata('user_id')
					);
				}
			}

			if ($this->input->post('page') == 1) {
				$top_rankers = array_filter($rankings, function($item) {
					return $item['rank'] < 4;
				});

				$rankings = array_filter($rankings, function($item) {
					return $item['rank'] > 3;
				});

				foreach ($top_rankers as $item) {
					if ($item['rank'] == 1) {
						$first_rank = $item;
					} elseif ($item['rank'] == 2) {
						$second_rank = $item;
					} elseif ($item['rank'] == 3) {
						$third_rank = $item;
					}
				}

				$this->json['top_rankers'] = [
					'first'		=> $first_rank ?? 0,
					'second'	=> $second_rank ?? 0,
					'third'		=> $third_rank ?? 0,
				];
			}

			$this->json['rankings'] = array_values($rankings);
		}
	}

	public function getNationalChallengeUpdate($event_id = 0, $event_challenge_country_id = 0) {
		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->ranking_lib->getCountryUpdate($event_id, $event_challenge_country_id, get_bb_user_id());
		}
	}
}
