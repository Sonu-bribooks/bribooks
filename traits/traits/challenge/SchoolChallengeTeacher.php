<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolChallengeTeacher {
	public function getSchoolChallengeTeacherRanks() {
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
			$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
			$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');

			$rank_results = $this->teacherranking_lib->getSchoolRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_school_id'),
				$this->input->post('school_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			if ($this->input->post('user_id')) {
				$this->json['user_rank'] = $this->teacherranking_lib->getTeacherSchoolRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_school_id'),
					$this->input->post('school_id'),
					$this->input->post('user_id')
				);
			}

			$this->json['ranks'] = array_values($rank_results['ranks']);
			$this->json['total'] = $rank_results['total'];

			$school_info 	= $this->site_model->get($this->input->post('school_id'));
			$state_info 	= $this->state_model->get($school_info['state_id']);
			$city_info 		= $this->city_model->get($school_info['city_id']);

			$this->json['heading'] 	= sprintf('%s, %s', $school_info['name'] ?? '', $state_info['name'] ?? '');
			$this->json['state'] 	= $state_info['name'] ?? '';
			$this->json['city'] 	= $city_info['name'] ?? '';
		}
	}

	public function getSchoolChallengeTeacherUpdate($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		if (!$this->json) {
			$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
			$this->teacherranking_lib->getSchoolUpdate($event_id, $event_challenge_school_id, $school_id, get_bb_user_id());
		}
	}
}
