<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolChallenge {
	public function getSchoolChallenge() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]',
			['slug', [$this->validate_model, 'event_challenge_school_slug']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$challenge_info = $this->event_challenge_school_model->getBySlug($this->input->post('slug'));

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

	public function getSchoolChallenges() {
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
			$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');

			$results = $this->event_challenge_school_model->get_all([
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
					$this->json['active_counter'] 	= $challenge_data;
				} else if ($item['display_date'] >= date('Y-m-d H:i:s') && $item['end_date'] >= date('Y-m-d H:i:s')) {
					$this->json['active_counter'] 	= $challenge_data;
				}

				$this->json['challenges'][] = $challenge_data;
			}

			array_multisort($sort_order, $this->json['challenges']);
		}
	}

	public function getSchoolChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_school_id', _l('event_challenge_school'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_school', [$this->validate_model, 'event_challenge_school']]
		]);

		$this->form_validation->set_rules('school_id', _l('school'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');

			// if (empty($this->input->post('search')) && $this->input->post('is_qualified')) {
			// 	self::getEventQualifiedBook();
			// 	$this->json['league_qualified'] = TRUE;
			// 	return;
			// }

			$rank_results = $this->ranking_lib->getSchoolRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_school_id'),
				$this->input->post('school_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search'),
				$this->input->post('limit') ?? 10
			);

			$this->json['ranks'] = array_values($rank_results['ranks']);
			// $this->json['league_qualified'] = TRUE;

			// if (empty($this->input->post('search')) && empty($this->json['ranks'])) {
			// 	$this->json['league_qualified'] = FALSE;
			// } elseif (empty($this->input->post('search')) && !empty($this->json['ranks'])) {
			// 	$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');

			// 	$challenge_info = $this->event_challenge_school_model->get($this->input->post('event_challenge_school_id'));

			// 	if (!empty($challenge_info) && !empty($challenge_info['conditions'])) {
			// 		$challenge_condition_info = (array)json_decode($challenge_info['conditions']) ?? [];

			// 		$school_published_count =  count($this->event_book_model->get_all([
			// 			'event_id'	=> $this->input->post('event_id'),
			// 			'site_id'	=> $this->input->post('school_id'),
			// 		])['rows'] ?? []);

			// 		if ($school_published_count < ($challenge_condition_info['min_published'] ?? 0)) {
			// 			$this->json['league_qualified'] = FALSE;
			// 		}
			// 	}
			// }

			// if (empty($this->input->post('search')) && empty($this->json['ranks'])) {
			// 	$school_published_count =  count($this->event_book_model->get_all([
			// 		'event_id'	=> $this->input->post('event_id'),
			// 		'site_id'	=> $this->input->post('school_id'),
			// 	])['rows'] ?? []);

			// 	if ($school_published_count < 4) {
			// 		$this->json['is_qualified'] = FALSE;
			// 	}
			// }

			$this->json['total'] = $rank_results['total'];

			if (empty($this->input->post('search')) && ($this->session->userdata('user_id') || $this->input->post('user_id'))) {
				$user_id = $this->input->post('user_id')
					? (int)$this->input->post('user_id')
					: (int)$this->session->userdata('user_id');
				$book_id = $this->input->post('book_id')
					? (int)$this->input->post('book_id')
					: 0;

				if (empty($this->json['user_rank'] = $this->ranking_lib->getUserSchoolRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_school_id'),
					$this->input->post('school_id'),
					$user_id,
					$book_id,
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoSchoolRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_school_id'),
						$this->input->post('school_id'),
						$user_id,
						$book_id
					);
				}
			}

			$school_info = $this->site_model->get($this->input->post('school_id'));
			$city_info = $this->city_model->get($school_info['city_id']);
			$state_info = $this->state_model->get($school_info['state_id']);

			if ($this->input->post('event_id') == YABWF_EVENT_ID) {
				$this->json['city'] = 'Bhavans Kuwait';
			} else {
				$this->json['city'] = $city_info['name'] ?? '';
			}

			$this->json['heading'] 	= sprintf('%s', $school_info['name'] ?? '');
			$this->json['state_id'] = $state_info['id'] ?? 0;
			$this->json['state'] 	= $state_info['name'] ?? '';

			// if (!empty($this->input->post('search'))) {
			// 	$event_data = $this->json;
			// 	$this->json = [];

			// 	self::getEventQualifiedBook();

			// 	$this->json['ranks'] = array_merge($event_data['ranks'] ?? [], $this->json['ranks'] ?? []);
			// }
		}
	}

	public function getSchoolChallengeUpdate($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->ranking_lib->getSchoolUpdate($event_id, $event_challenge_school_id, $school_id, get_bb_user_id());
		}
	}
}
