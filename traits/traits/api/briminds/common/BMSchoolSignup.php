<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BMSchoolSignup {
	public function sendBMSchoolOtp() {
		$this->form_validation->set_rules('country_id', _l('country_id'), [
			'trim',
			'required',
			'numeric',
			['country', [$this->validate_model, 'country']]
		]);

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

		$this->form_validation->set_rules('school_name', _l('school_name'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('school_head', _l('school_head'),  'trim|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('designation', _l('designation'),  'trim|required|min_length[3]|max_length[128]');

		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!empty($validate_mesaage = self::_validateBMSchoolLead($this->input->post()))) {
                $this->json['error'] = $validate_mesaage;
				return;
            }
		}

		if (!$this->json) {
			$this->load->model('briminds/school/BMSchoolLead_model', 'bm_school_lead_model');
			$this->load->model('event/EventSchoolInviteCode_model', 'event_school_invite_code_model');

			if (
				!empty($this->input->post('bb_school_id')) &&
				!empty($this->input->post('code')) &&
				empty($school_code_info = $this->event_school_invite_code_model->get_all([
				'event_id'	  	=> $this->input->post('event_id') ?? 0,
				'school_id'	 	=> $this->input->post('bb_school_id'),
				'code'		  	=> $this->input->post('code'),
			])['rows'] ?? [])
			) {
				return $this->json['error'] = _li('invalid_code');
			}

			$result = $this->bm_school_lead_model->add([
				'bb_site_id'				=> $this->input->post('bb_site_id') ?? 0,
				'bb_school_id'				=> $this->input->post('bb_school_id') ?? 0,
				'country_id'				=> (int)$this->input->post('country_id') ?? 0,
				'state_id'					=> (int)$this->input->post('state_id') ?? 0,
				'city_id'					=> (int)$this->input->post('city_id') ?? 0,
				'name'						=> $this->input->post('school_name') ?? '',
				'school_head'				=> $this->input->post('school_head') ?? '',
				'authorized_person'			=> $this->input->post('authorized_person'),
				'designation'				=> $this->input->post('designation'),
				'email'						=> $this->input->post('email'),
				'mobile'					=> $this->input->post('mobile'),
				'ip'						=> $this->input->ip_address(),
				'timezone'					=> $this->input->post('timezone'),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
				'site_type'					=> $this->input->post('site_type') ?? 1,
			]);

			$this->json['lead_id'] = $result;

			// true mobile otp and false email otp
			self::_executeBMOtp(
				$this->input->post('type') == 'mobile',
				false,
				$this->input->post('type') == 'whatsapp',
			);

			$this->json['success'] = sprintf(_li('Thank you for showing your interest in BriMinds. Someone from BriMinds team will get in touch with you shortly.'));
		}
	}

	public function verifyBMSchoolOtp() {
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('briminds/school/BMSchoolLead_model', 'bm_school_lead_model');
			$this->load->model('briminds/school/BMSite_model', 'bm_site_model');
			$this->load->model('briminds/user/BMUser_model', 'bm_user_model');
			$this->load->model('localisation/Country_model', 'country_model');

			// true mobile and false email otp verify
			if (self::_verifyBMOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$lead_info = $this->bm_school_lead_model->get($this->input->post('lead_id'));

				if (!empty($lead_info)) {
					$country_info  = $this->country_model->get($lead_info['country_id'] ?? 0);

					$site_id = $this->bm_site_model->add([
						'country_id' 		 	=> $lead_info['country_id'] ?? 0,
						'state_id'				=> $lead_info['state_id'],
						'city_id'				=> $lead_info['city_id'],
						'country_code'			=> $country_info['code'] ?? '',
						'name'					=> $lead_info['name'],
						'site_code' 			=> 'lead-' . uniqid(),
						'owner_email'			=> $lead_info['email'],
						'owner_mobile'			=> $lead_info['mobile'],
						'authorized_person'		=> $lead_info['authorized_person'],
						'owner_name'			=> $lead_info['school_head'] ?? '',
						'status'				=> 1,
						'verified'				=> 1,
						'date_verified'			=> date('Y-m-d H:i:s')
					]);

					if (!empty($site_id)) {
						$this->bm_site_model->edit($site_id, [
							'site_code' => get_site_code_slug(trim($lead_info['name'])) . "-" . $site_id
						]);

						$user_id = $this->bm_user_model->add([
							'role_id' 		 		=> 9,
							'site_id' 		 		=> $site_id,
							'country_id' 		 	=> $lead_info['country_id'] ?? 0,
							'state_id'				=> $lead_info['state_id'],
							'city_id'				=> $lead_info['city_id'],
							'first_name'			=> $lead_info['name'],
							'email'					=> $lead_info['email'],
							'mobile'				=> $lead_info['mobile'],
							'status'				=> 1,
							'mobile_verified' 	=> in_array($this->input->post('type'), ['mobile', 'whatsapp']) ? 1 : 0,
							'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
						]);
					}

					$this->bm_school_lead_model->edit($lead_info['id'], [
						'site_id' 			=> $site_id,
						'school_id' 		=> 0,
						'verified' 		    => 1,
						'mobile_verified' 	=> in_array($this->input->post('type'), ['mobile', 'whatsapp']) ? 1 : 0,
						'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
					]);

					CI_Events::trigger('bm_school_signup', [
						'id' 	=> $site_id
					]);

					CI_Events::trigger('bm_after_school_signup', [
						'id' 	=> $site_id
					]);

					$this->json['success'] 	= _l('site_saved_successfully!');
					
				} else {
					$this->json['error'] 	= _l('lead_is_invalid');
				}
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}
    private function _validateBMSchoolLead($data = []) {
		$this->load->model('briminds/school/BMSite_model', 'bm_site_model');
		$this->load->model('briminds/school/BMSchoolLead_model', 'bm_school_lead_model');

		if (!empty($data['bb_school_id']) && !empty($lead_info = $this->bm_school_lead_model->get_all([
			'bb_school_id'          => $data['bb_school_id'],
			'verified'           	=> 1,
		])['rows'][0] ?? '')) {
			return _li('You_are_already_registered_with_BriMinds');
		}

		if (!empty($data['bb_site_id']) && !empty($lead_info = $this->bm_school_lead_model->get_all([
			'bb_site_id'         => $data['bb_site_id'],
			'verified'           => 1,
		])['rows'][0] ?? '')) {
			return _li('You_are_already_registered_with_BriMinds');
		}

        if (!empty($data['email']) && !empty($site_info = $this->bm_site_model->get_all([
			'owner_email'           => $data['email'],
		])['rows'][0] ?? '')) {
			return _li('Your_email_is_already_registered_with_BriMinds');
		}

		if (!empty($data['mobile']) && !empty($site_info = $this->bm_site_model->get_all([
			'owner_mobile'          => $data['mobile'],
		])['rows'][0] ?? '')) {
			return _li('Your_mobile_is_already_registered_with_BriMinds');;
		}
	}
}
