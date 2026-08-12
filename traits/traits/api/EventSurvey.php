<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventSurvey {
	public function getSchoolSurveyData() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('event/EventSchoolSurvey_model', 'event_school_survey_model');
			$this->load->model('event/EventSchoolInviteCode_model', 'event_school_invite_code_model');

			$verification_info = $this->event_school_invite_code_model->get_all([
				'event_id'  => $this->input->post('event_id'),
				'site_id'   => $this->input->post('site_id'),
				'code'	  	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (!empty($verification_info)) {
				$info = $this->event_school_survey_model->get_all([
					'event_id'	=> (int)$this->input->post('event_id'),
					'site_id'	=> (int)$this->input->post('site_id') ?? 0,
				])['rows'][0] ?? [];

				if (($info['status'] ?? 0) != 0) {
					return $this->json['error'] = _li('Thanks! Your details are with us. If you have any queries, reach out to us at support@bribooks.com.');
				}

				$site_info  = $this->site_model->get($this->input->post('site_id') ?? 0);
				$state_info = $this->state_model->get($site_info['state_id'] ?? 0);
				$city_info  = $this->city_model->get($site_info['city_id'] ?? 0);

				$school_data = [
					'event_id'		  	=> $info['event_id'] ?? $this->input->post('event_id'),
					'site_id'		   	=> $info['site_id'] ?? $this->input->post('site_id'),
					'school_name'	   	=> $info['school_name'] ?? $site_info['name'],
					'authorized_person' => $info['authorized_person'] ?? $site_info['authorized_person'],
					'email'			 	=> $info['email'] ?? $site_info['owner_email'],
					'mobile'			=> $info['mobile'] ?? $site_info['owner_mobile'],
					'student_count'	 	=> $info['student_count'] ?? 0,
					'teacher_count'	 	=> $info['teacher_count'] ?? 0,
					'state'			 	=> $state_info['name'] ?? '',
					'city'			  	=> $city_info['name'] ?? '',
				];

				$this->json['school'] = $school_data;
			} else {
				$this->json['error'] = _li('Invalid url');
			}
		}
	}

	public function addSchoolSurveyData() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('student_count', _l('student_count'), 'trim|required|numeric');
		$this->form_validation->set_rules('teacher_count', _l('teacher_count'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('event/EventSchoolSurvey_model', 'event_school_survey_model');

			$info = $this->event_school_survey_model->get_all([
				'event_id'		=> (int)$this->input->post('event_id'),
				'site_id'		=> (int)$this->input->post('site_id'),
			])['rows'][0] ?? [];

			if (empty($info)) {
				$this->event_school_survey_model->add([
					'event_id'			=> $this->input->post('event_id') ?? 0,
					'site_id'			=> $this->input->post('site_id') ?? 0,
					'school_name'		=> $this->input->post('school_name') ?? '',
					'mobile'			=> $this->input->post('mobile') ?? '',
					'email'				=> $this->input->post('email') ?? '',
					'authorized_person'	=> $this->input->post('authorized_person') ?? '',
					'student_count'		=> $this->input->post('student_count') ?? 0,
					'teacher_count'		=> $this->input->post('teacher_count') ?? 0,
					'status'			=> 1,
				]);
			}

			$this->json['success'] = _l('details_saved_successfully');
		}
	}
}
