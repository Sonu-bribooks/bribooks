<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BMUserSignup {
	public function sendBMUserOtp() {
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

		$this->form_validation->set_rules('site_id', _l('site_id'),  'trim|required|numeric');
		$this->form_validation->set_rules('grade', _l('grade'),  'trim|required|numeric');
		$this->form_validation->set_rules('section', _l('section'),  'trim|required');
		$this->form_validation->set_rules('first_name', _l('first_name'),  'trim|required|min_length[2]|max_length[128]');
		$this->form_validation->set_rules('last_name', _l('last_name'),  'trim');

		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			// if (!self::_verifyCaptcha()) {
			// 	$this->json['error'] = _li('Invalid Captcha. Please try again.');
			// 	return;
			// }

			if (!empty($validate_mesaage = self::_validateBMUserLead($this->input->post()))) {
                $this->json['error'] = $validate_mesaage;
				return;
            }
		}

		if (!$this->json) {
			$this->load->model('briminds/user/BMUserLead_model', 'bm_user_lead_model');
			$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');

			if (
				!empty($this->input->post('bb_user_id')) &&
				!empty($this->input->post('code')) &&
				empty($school_code_info = $this->event_user_invite_code_model->get_all([
				'event_id'	  	=> $this->input->post('event_id') ?? 0,
				'school_id'	 	=> $this->input->post('bb_user_id'),
				'code'		  	=> $this->input->post('code'),
			])['rows'] ?? [])
			) {
				return $this->json['error'] = _li('invalid_code');
			}

			$result = $this->bm_user_lead_model->add([
                'bb_user_id'				=> $this->input->post('bb_user_id') ?? 0,
				'country_id'				=> (int)$this->input->post('country_id') ?? 0,
				'state_id'					=> (int)$this->input->post('state_id') ?? 0,
				'city_id'					=> (int)$this->input->post('city_id') ?? 0,
				'site_id'				    => $this->input->post('site_id') ?? 0,
				'first_name'			    => $this->input->post('first_name') ?? '',
				'last_name'				    => $this->input->post('last_name') ?? '',
				'grade'			            => $this->input->post('grade'),
				'section'				    => $this->input->post('section'),
				'email'						=> $this->input->post('email'),
				'mobile'					=> $this->input->post('mobile'),
				'ip_address'				=> $this->input->ip_address(),
				'timezone'					=> $this->input->post('timezone'),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
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

	public function verifyBMUserOtp() {
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('briminds/user/BMUserLead_model', 'bm_user_lead_model');
			$this->load->model('briminds/user/BMUser_model', 'bm_user_model');
			$this->load->model('localisation/Country_model', 'country_model');

			// true mobile and false email otp verify
			if (self::_verifyBMOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$lead_info = $this->bm_user_lead_model->get($this->input->post('lead_id'));

				if (!empty($lead_info)) {
					$country_info  = $this->country_model->get($lead_info['country_id'] ?? 0);

                    $user_id = $this->bm_user_model->add([
                        'role_id' 		 		=> 2,
                        'country_id' 		 	=> $lead_info['country_id'] ?? 0,
                        'state_id'				=> $lead_info['state_id'],
                        'city_id'				=> $lead_info['city_id'],
                        'site_id'				=> $lead_info['site_id'],
                        'first_name'			=> $lead_info['first_name'],
                        'last_name'			    => $lead_info['last_name'],
                        'grade'			        => $lead_info['grade'] ?? 0,
                        'section'			    => $lead_info['section'] ?? '',
                        'email'					=> $lead_info['email'],
                        'mobile'				=> $lead_info['mobile'],
                        'status'				=> 1,
						'ip_address'			=> $lead_info['ip_address'] ?? '',
                        'timezone'				=> $lead_info['timezone'],
                        'source'			    => $lead_info['utm_source'] ?? '',
                        'mobile_verified' 	    => in_array($this->input->post('type'), ['mobile', 'whatsapp']) ? 1 : 0,
                        'email_verified' 	    => $this->input->post('type') == 'email' ? 1 : 0
                    ]);

					$this->bm_user_lead_model->edit($lead_info['id'], [
						'user_id' 			=> $user_id,
						'verified' 		    => 1,
						'mobile_verified' 	=> in_array($this->input->post('type'), ['mobile', 'whatsapp']) ? 1 : 0,
						'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
					]);

					CI_Events::trigger('bm_user_signup', [
						'id' 	=> $user_id
					]);

					$this->json['success'] 	= _l('user_saved_successfully!');
					
				} else {
					$this->json['error'] 	= _l('lead_is_invalid');
				}
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}
    private function _validateBMUserLead($data = []) {
		$this->load->model('briminds/user/BMUser_model', 'bm_user_model');
		$this->load->model('briminds/user/BMUserLead_model', 'bm_user_lead_model');

		if (!empty($data['bb_user_id']) && !empty($lead_info = $this->bm_user_lead_model->get_all([
			'bb_user_id'         => $data['bb_user_id'],
			'verified'           => 1,
		])['rows'][0] ?? '')) {
			return _li('You_are_already_registered_with_BriMinds');
		}

        if (!empty($data['email']) && !empty($user_info = $this->bm_user_model->get_all([
			'email'           => $data['email'],
		])['rows'][0] ?? '')) {
			return _li('Your_email_is_already_registered_with_BriMinds');
		}

		if (!empty($data['mobile']) && !empty($user_info = $this->bm_user_model->get_all([
			'mobile'          => $data['mobile'],
		])['rows'][0] ?? '')) {
			return _li('Your_mobile_is_already_registered_with_BriMinds');;
		}
	}
}
