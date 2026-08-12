<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolSignupRequest {
	public function sendSchoolRequestOtp() {
		// $this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		// $this->form_validation->set_rules('country', _l('country'), 'trim|required|min_length[2]|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');

		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} else if ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');

		if(!empty($this->input->post('event_id')) && $this->input->post('event_id') != '9') {
			$this->form_validation->set_rules('school_head', _l('school_head'),  'trim|required|min_length[3]|max_length[128]');
			$this->form_validation->set_rules('designation', _l('designation'),  'trim|required|min_length[3]|max_length[128]');
			$this->form_validation->set_rules('section', _l('section'),  'trim|required|max_length[1]');
		}

		$this->form_validation->set_rules('grade[]', _l('grade'),  'trim|required|numeric');
		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim|min_length[3]');

		self::_runFormValidation();

		if (!$this->json && empty($this->input->post('grade'))) {
			$this->json['error'] = _li('Grades are required');
		}

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			if ($this->input->post('other_school') && ($school_info = $this->db->get_where('school_lead', [
				'name'				=> $this->input->post('other_school'),
				'state_id'			=> (int)$this->input->post('state_id'),
				'city_id'			=> (int)$this->input->post('city_id'),
				'mobile_verified'	=> 1,
			])->row_array())) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			if ($this->input->post('school_id') && ($school_info = $this->db->get_where('school_lead', [
				'school_id'			=> $this->input->post('school_id'),
				'mobile_verified'	=> 1,
			])->row_array())) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'email'			=> $this->input->post('email'),
				// 'role_id'		=> 9,
			])->row_array()) {
				$this->json['error'] = _li('already_registered_email_id');
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('mobile'),
				// 'role_id'		=> 9,
			])->row_array()) {
				$this->json['error'] = _li('already_registered_mobile_no');
				return;
			}
		}

		if (!$this->json) {
			if ($this->input->post('other_school')) {
				$school_name = $this->input->post('other_school');
			} else {
				if (!($school_info = $this->schoolinput_model->get($this->input->post('school_id')))) {
					$this->json['error'] = _l('select_school');
					return;
				}

				$school_name = $school_info['name'];
			}

			$school_id = ($this->input->post('other_school') ? 0 : ($school_info['id'] ?? 0));

			$site_id = 0;

			if ($this->input->post('site_code')) {
				$parent_site_info = $this->site_model->getByCode($this->input->post('site_code'));
				$site_id = $parent_site_info['id'] ?? 0;
			}

			$result = $this->school_lead_model->add([
				'event_id'					=> (int)$this->input->post('event_id') ?? 0,
				'site_id'					=> $site_id,
				'school_id'					=> $school_id,
				'name'						=> $school_name,
				'country'					=> $this->input->post('country')
					? $this->input->post('country')
					: $this->config->item('site_country'),
				'state_id'					=> (int)$this->input->post('state_id'),
				'city_id'					=> (int)$this->input->post('city_id'),
				'school_head'				=> $this->input->post('school_head') ?? '',
				'authorized_person'			=> $this->input->post('authorized_person') ?? '',
				'designation'				=> $this->input->post('designation') ?? '',
				'email'						=> $this->input->post('email') ?? '',
				'mobile'					=> $this->input->post('mobile') ?? '',
				'leaflets'					=> $this->input->post('leaflet') == 'Yes'
					? (int)$this->input->post('leaflet_count')
					: 0,
				'sections'					=> $this->input->post('section') ?? '',
				'grades'					=> json_encode(
					is_array($this->input->post('grade'))
						? $this->input->post('grade')
						: []
				),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
			]);

			$this->json['lead_id'] = $result;

			self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks Young Authors Fair. Someone from BriBooks team will get in touch with you shortly.');
		}
	}

	public function verifyRequestSchoolOtp() {
		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} else if ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'schoolLead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (self::_verifyOtp($this->input->post('type') == 'mobile')) {
				$lead_info = $this->school_lead_model->get($this->input->post('lead_id'));

				if ($this->input->post('type') == 'mobile') {
					$this->school_lead_model->edit($lead_info['id'], [
						'mobile_verified'	=> 1,
					]);
				} else if ($this->input->post('type') == 'email') {
					$this->school_lead_model->edit($lead_info['id'], [
						'email_verified'	=> 1,
					]);
				}

				$this->alert_model->schoolRequestAlert($lead_info['id']);
				ENVIRONMENT === 'production' && $this->alert_model->schoolLeadShare($lead_info['id']);

				$this->load->model('common/Cron_model', 'cron_model');
				$this->cron_model->add([
					'code'			=> 'approveSchoolRequestCron_' . $lead_info['id'],
					'action'		=> 'alert_model->approveSchoolRequestCron',
					'data'			=> [$lead_info['id']],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+24 hours'
						: '+2 minutes'
					)),
				]);

				$this->json['success'] 	= _l('verified');
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}

	// Deprecated
	public function requestSchoolSignup() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 10 digit mobile number'),
		]);
		$this->form_validation->set_rules('school_head', _l('school_head'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('designation', _l('designation'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('section', _l('section'),  'trim|required|max_length[1]');
		$this->form_validation->set_rules('grade[]', _l('grade'),  'trim|required|numeric');
		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);

		self::_runFormValidation();

		if (!$this->json && empty($this->input->post('grade'))) {
			$this->json['error'] = _li('Grades are required');
		}

		if (!$this->json) {
			if ($this->input->post('other_school') && ($school_info = $this->db->get_where('school_lead', [
				'name'				=> $this->input->post('other_school'),
				'state_id'			=> (int)$this->input->post('state_id'),
				'city_id'			=> (int)$this->input->post('city_id'),
				'mobile_verified'	=> 1,
			])->row_array())) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			if ($this->input->post('school_id') && ($school_info = $this->db->get_where('school_lead', [
				'school_id'			=> $this->input->post('school_id'),
				'mobile_verified'	=> 1,
			])->row_array())) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'email'			=> $this->input->post('email'),
				// 'role_id'		=> 9,
			])->row_array()) {
				$this->json['error'] = _li('already_registered_email_id');
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('mobile'),
				// 'role_id'		=> 9,
			])->row_array()) {
				$this->json['error'] = _li('already_registered_mobile_no');
				return;
			}
		}

		if (!$this->json) {
			if ($this->input->post('other_school')) {
				$school_name = $this->input->post('other_school');
			} else {
				if (!($school_info = $this->schoolinput_model->get($this->input->post('school_id')))) {
					$this->json['error'] = _l('select_school');
					return;
				}

				$school_name = $school_info['name'];
			}

			$school_id = ($this->input->post('other_school') ? 0 : ($school_info['id'] ?? 0));

			$lead_id = $this->school_lead_model->add([
				'school_id'					=> $school_id,
				'name'						=> $school_name,
				'country'					=> $this->input->post('country')
					? $this->input->post('country')
					: $this->config->item('site_country'),
				'state_id'					=> (int)$this->input->post('state_id'),
				'city_id'					=> (int)$this->input->post('city_id'),
				'school_head'				=> $this->input->post('school_head'),
				'authorized_person'			=> $this->input->post('authorized_person'),
				'designation'				=> $this->input->post('designation'),
				'email'						=> $this->input->post('email'),
				'mobile'					=> $this->input->post('mobile'),
				'leaflets'					=> $this->input->post('leaflet') == 'Yes'
					? (int)$this->input->post('leaflet_count')
					: 0,
				'sections'					=> $this->input->post('section'),
				'grades'					=> json_encode(
					is_array($this->input->post('grade'))
						? $this->input->post('grade')
						: []
				),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
			]);

			$this->json['lead_id'] = $lead_id;

			$lead_info = $this->school_lead_model->get($lead_id);

			$this->school_lead_model->edit($lead_info['id'], [
				'mobile_verified'	=> 1,
			]);

			if (!empty($lead_info['school_id'])) {
				$this->cron_model->add([
					'code'			=> 'approvedLeadExisting_' . $lead_info['id'],
					'action'		=> 'alert_model->approvedLeadExisting',
					'data'			=> [$lead_info['id']],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+24 hours'
						: '+2 minutes'
					)),
				]);

				$this->alert_model->schoolLeadRegistration($lead_info['id']);
			} else {
				$this->load->model('common/Cron_model', 'cron_model');
				$this->cron_model->add([
					'code'			=> 'approvedLead_' . $lead_info['id'],
					'action'		=> 'alert_model->approvedLead',
					'data'			=> [$lead_info['id']],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+24 hours'
						: '+2 minutes'
					)),
				]);

				$this->alert_model->otherSchoolLeadRegistration($lead_info['id']);
			}

			// self::_addSite($lead_info);

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks Young Authors Fair. Someone from BriBooks team will get in touch with you shortly.');
		}
	}
}
