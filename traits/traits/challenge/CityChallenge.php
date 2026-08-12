<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CityChallenge {
	public function getCityChallenge() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]',
			['slug', [$this->validate_model, 'event_challenge_city_slug']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$challenge_info = $this->event_challenge_city_model->getBySlug($this->input->post('slug'));

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

	public function getCityChallenges() {
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
			$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');

			$results = $this->event_challenge_city_model->get_all([
				'type'		=> $this->input->post('type') ?? '',
				'event_id'	=> (int)$this->input->post('event_id'),
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
					$this->json['active_counter'] 	= $challenge_data;
				} else if ($item['display_date'] >= date('Y-m-d H:i:s') && $item['end_date'] >= date('Y-m-d H:i:s')) {
					$this->json['active_counter'] 	= $challenge_data;
				}

				$this->json['challenges'][] = $challenge_data;
			}

			array_multisort($sort_order, $this->json['challenges']);
		}
	}

	public function getCityChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_city_id', _l('event_challenge_city'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_city', [$this->validate_model, 'event_challenge_city']]
		]);

		$this->form_validation->set_rules('city_id', _l('city'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');

			// if (empty($this->input->post('search')) && $this->input->post('is_qualified')) {
			// 	return self::getEventQualifiedBook();
			// }

			$event_challenge_city_info = $this->event_challenge_city_model->get($this->input->post('event_challenge_city_id'));

			$rank_results = $this->ranking_lib->getCityRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_city_id'),
				$this->input->post('city_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('limit') ?? 0,
				$this->input->post('search')
			);

			if (!empty($event_challenge_city_info) && !empty($event_challenge_city_info['min_published'])) {
				$rank_data 		= array_values($rank_results['ranks']);
				$min_publishd 	= $event_challenge_city_info['min_published'] ?? 0;

				$this->json['ranks'] = array_map(function ($row) use ($min_publishd) {
					if (($row['score'] ?? 0) < $min_publishd) {
						$row['rank'] = 0;
					}
					return $row;
				}, $rank_data);
			} else {
				$this->json['ranks'] = array_values($rank_results['ranks']);
			}


			$this->json['total'] = $rank_results['total'];

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				$user_id = $this->input->post('user_id')
					? (int)$this->input->post('user_id')
					: (int)$this->session->userdata('user_id');
				$book_id = $this->input->post('book_id')
					? (int)$this->input->post('book_id')
					: 0;

				if (empty($this->json['user_rank'] = $this->ranking_lib->getUserCityRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_city_id'),
					$this->input->post('city_id'),
					$user_id,
					$book_id,
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoCityRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_city_id'),
						$this->input->post('city_id'),
						$user_id,
						$book_id,
					);
				} else {
					if (!empty(($event_challenge_city_info['min_published'] ?? 0))) {
						if (($this->json['user_rank']['score'] ?? 0) < ($event_challenge_city_info['min_published'] ?? 0)) {
							$this->json['user_rank']['rank'] = 0;
						}
					}
				}
			}

			$city_info = $this->city_model->get($this->input->post('city_id'));
			$state_info = $this->state_model->get($city_info['state_id']);

			$this->json['heading'] = sprintf('%s, %s', $city_info['name'] ?? '', $state_info['name'] ?? '');
			$this->json['state'] = $state_info['name'] ?? '';

			// if (!empty($this->input->post('search'))) {
			// 	$event_data = $this->json;
			// 	$this->json = [];

			// 	self::getEventQualifiedBook();

			// 	$this->json['ranks'] = array_merge($event_data['ranks'], $this->json['ranks']);
			// }
		}
	}

	public function getCityChallengeUpdate($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->ranking_lib->getCityUpdate($event_id, $event_challenge_city_id, $city_id, get_bb_user_id());
		}
	}
}
