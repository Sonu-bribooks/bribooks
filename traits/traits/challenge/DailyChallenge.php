<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DailyChallenge {
	public function getDailyChallenge() {
		$this->form_validation->set_rules('event_challenge_daily_id', _l('event_challenge_daily'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_daily', [$this->validate_model, 'event_challenge_daily']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (!($event_info['start_date'] <= date('Y-m-d H:i:s'))) {
				$this->json['error'] = _l('event_not_started');
				return;
			}

			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeDaily_model', 'event_challenge_daily_model');

			$result = $this->event_challenge_daily_model->get($this->input->post('event_challenge_daily_id'));

			$challenge_data = [
				'id'		=> $result['id'],
				'name'		=> $result['name'],
				'start_date'=> $result['display_date'],
				'end_date'	=> $result['end_date'],
				'counter'	=> date('Y-m-d\TH:i:s\Z', strtotime('-330 minutes', strtotime($result['end_date']))),
				'locked'	=> $result['display_date'] > date('Y-m-d H:i:s'),
			];

			if ($result['display_date'] <= date('Y-m-d H:i:s') && $result['end_date'] >= date('Y-m-d H:i:s')) {
				$this->json['active_challenge'] = $challenge_data;
				$this->json['active_counter'] 	= $challenge_data;
			} else if ($result['display_date'] >= date('Y-m-d H:i:s') && $result['end_date'] >= date('Y-m-d H:i:s')) {
				$this->json['active_counter'] 	= $challenge_data;
			}

			$this->json['challenges'][] = $challenge_data;
		}
	}

	public function getDailyChallenges() {
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
			$this->load->model('event/EventChallengeDaily_model', 'event_challenge_daily_model');

			$results = $this->event_challenge_daily_model->get_all([
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

	public function getDailyChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_daily_id', _l('event_challenge_daily'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_daily', [$this->validate_model, 'event_challenge_daily']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeDaily_model', 'event_challenge_daily_model');

			$rank_results = $this->ranking_lib->getDailyRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_daily_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			$this->json['ranks'] = array_values($rank_results['ranks']);

			$this->json['total'] = $rank_results['total'];

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				if (empty($this->json['user_rank'] = $this->ranking_lib->getUserDailyRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_daily_id'),
					$this->input->post('user_id')
						? $this->input->post('user_id')
						: $this->session->userdata('user_id'),
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoDailyRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_daily_id'),
						$this->input->post('user_id')
							? $this->input->post('user_id')
							: $this->session->userdata('user_id')
					);
				}
			}

			$challenge_info = $this->event_challenge_daily_model->get($this->input->post('event_challenge_daily_id'));

			$this->json['heading'] = sprintf('%s', $challenge_info['name']);
		}
	}

	public function getDailyChallengeUpdate($event_id = 0, $event_challenge_daily_id = 0) {
		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->ranking_lib->getDailyUpdate($event_id, $event_challenge_daily_id, get_bb_user_id());
		}
	}
}
