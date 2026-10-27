<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolChallengeGrade {

    public function getSchoolGradeChallenge() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]'
		]);

		self::_runFormValidation();

		if (!$this->json) {
            $this->load->model('event/EventChallengeGrade_model', 'event_challenge_grade_model');
			$challenge_info = $this->event_challenge_grade_model->getBySlug($this->input->post('slug'));

            if (empty($challenge_info)) {
                $this->json['error'] = _l('event_not_found');
				return;
            }

			$challenge_data = [
				'id'		=> $challenge_info['id'],
				'event_id'	=> $challenge_info['event_id'],
				'name'		=> $challenge_info['name'],
                'slug'		=> $challenge_info['slug'],
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

	public function getSchoolGradeChallenges() {
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

			$this->load->model('event/EventChallengeGrade_model', 'event_challenge_grade_model');

			$results = $this->event_challenge_grade_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id'),
			])['rows'] ?? [];

			$sort_order = $this->json['challenges'] = [];

			foreach ($results as $item) {
				$challenge_data = [
					'id'		=> $item['id'],
					'name'		=> $item['name'],
                    'slug'		=> $item['slug'],
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
	// public function getSchoolChallengeGradeRanks() {
	public function getSchoolChallengeGradeRanks() {
		// $this->form_validation->set_rules('event_id', _l('event'), [
		// 	'trim',
		// 	'required',
		// 	'numeric',
		// 	['event', [$this->validate_model, 'event']]
		// ]);

		// $this->form_validation->set_rules('event_challenge_grade_id', _l('event_challenge_grade'), [
		// 	'trim',
		// 	'required',
		// 	'numeric'
		// ]);

		// $this->form_validation->set_rules('school_id', _l('school'), [
		// 	'trim',
		// 	'required',
		// 	'numeric',
		// 	['site', [$this->validate_model, 'site']]
		// ]);

        $this->form_validation->set_rules('event_slug', _l('event_slug'), [
			'trim',
			'required',
			'min_length[5]',
			'max_length[255]'
		]);

		$this->form_validation->set_rules('site_id', _l('site'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
			$this->load->model('event/EventChallengeGrade_model', 'event_challenge_grade_model');
            $this->load->model('ranking/school/SchoolRankingGrade_model', 'ranking_grade_model');

            $site_id 	= $this->input->post('site_id');

            $challenge_info = $this->event_challenge_grade_model->getBySlug($this->input->post('event_slug'));
			if (empty($challenge_info)) return;

			if ($challenge_info['start_date'] > date('Y-m-d H:i:s') || $challenge_info['end_date'] < date('Y-m-d H:i:s')) {
				return;
			}

			if (empty($event_info = $this->event_model->get($challenge_info['event_id']))) {
				$this->json['error'] = _l('event_not_exist');
				return;
			}

			if (!($event_info['start_date'] <= date('Y-m-d H:i:s'))) {
				$this->json['error'] = _l('event_not_started');
				return;
			}
			
			if (empty($this->event_site_model->get_all([
				'event_id' 	=> $event_info['id'],
				'site_id' 	=> $site_id,
			])['rows'][0] ?? '')) {
				$this->json['error'] = _l('site_not_enrol_this_event');
				return;
			}

			if (empty($site_info = $this->site_model->get((int)$site_id))) {
				$this->json['error'] = _l('event_not_exist');
				return;
			}

            if (!empty($this->input->post('grade')) && ($this->input->post('grade') != 'all')) {
				$filter_data['grade'] = $this->input->post('grade');
			}

			if (!empty($this->input->post('search'))) {
				// $filter_data['search']  = $this->input->post('search');
                $search = strtolower(trim($this->input->post('search')));

                // grade, spaces aur hyphens normalize karein
                $search = preg_replace('/^grade\s*/i', '', $search);
                $search = preg_replace('/[\s-]+/', '', $search);

                // Grade number aur optional section extract karein
                if (preg_match('/^(\d+)(?:([a-z]))?$/i', $search, $matches)) {
                    $filter_data['grade'] = (int) $matches[1];

                    if (!empty($matches[2])) {
                        $filter_data['search'] = strtoupper($matches[2]);
                    }
                } else {
                    $filter_data['search'] = trim($this->input->post('search'));
                }
			}

			if (!empty($this->input->post('page'))) {
				$filter_data['page']  = $this->input->post('page');
			}

            $filter_data['event_challenge_grade_id']    = $challenge_info['id'];
			$filter_data['event_id']			        = $event_info['id'];
			$filter_data['school_id']			        = $site_info['id']; 

			$rank_results = $this->schoolranking_lib->getGradeRanks($filter_data);


			// $this->json['ranks'] = array_values($rank_results['ranks']);
			// $this->json['total'] = $rank_results['total'];

			$state_info 	= $this->state_model->get($site_info['state_id']);
			$city_info 		= $this->city_model->get($site_info['city_id']);

			// $this->json['heading'] 	= sprintf('%s, %s', $site_info['name'] ?? '', $state_info['name'] ?? '');
			// $this->json['state'] 	= $state_info['name'] ?? '';
			// $this->json['city'] 	= $city_info['name'] ?? '';

          $grade_result = $this->ranking_grade_model->get_all([
				'event_id' => (int)$event_info['id'],
				'school_id' => (int)$site_id,
				'event_challenge_grade_id' => (int)$challenge_info['id'],
			]);

			$grades_list = array_values(array_unique(array_column($grade_result['rows'], 'grade')));
			sort($grades_list);

			$student_total = $this->grade_rank_model->get_school_grade_totals([
				'event_id' 					=> (int) $event_info['id'],
				'school_id' 				=> (int) $site_id,
				'event_challenge_grade_id' 	=> (int) $challenge_info['id'],
			]);

            $data = [
				'school_id'					=> $site_info['id'],
				'school_name'				=> trim($site_info['name']),
				'address'					=> trim($site_info['address']),
				'state'		 				=> $state_info['name'] ?? '',
				'city'		 				=> $city_info['name'] ?? '',
				'pincode'					=> $site_info['pincode'] ?? '',
				'total_registered'			=> $student_total['total_students'] ?? 0,
				'total_published'			=> $student_total['total_published_author'] ?? 0,
				'grade'						=> $grades_list,
				'student'					=> $rank_results['ranks'],
			];

			$this->json['data'] 	= $data;
		}
	}

	public function getSchoolChallengeGradeUpdate($event_id = 0, $event_challenge_grade_id = 0, $school_id = 0) {
		if (!$this->json) {
			$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
			$this->schoolranking_lib->getSchoolGradeUpdate($event_id, $event_challenge_grade_id, $school_id, get_bb_user_id());
		}
	}

}
