<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GlobalEventSignup {
	public function globalEventSendSignupOtp() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('state_id', _l('state'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		$this->form_validation->set_rules('city_id', _l('city'), [
			'trim',
			'required',
			'numeric',
		]);
		$this->form_validation->set_rules('school_name', _l('school_name'),  'trim|required|min_length[1]');

        $this->form_validation->set_rules('grade_id', _l('grade'), [
            'trim',
            'numeric'
        ]);
        $this->form_validation->set_rules('section_id', _l('section'), [
            'trim',
        ]);

		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile]');

		$event_info = $this->event_model->get($this->input->post('event_id'));

		if (($this->input->post('type') == 'mobile')) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|numeric|min_length[6]|max_length[18]', [
				'min_length'	=> _li('Please enter a valid mobile number'),
				'max_length'	=> _li('Please enter a valid mobile number'),
			]);
		}

		if ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('user_id', _l('user_id'), [
			'trim',
			'numeric'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate()) {
				return;
			}

			$user_email_info = $this->db->get_where('users', [
				'email'		=> $this->input->post('email'),
				'_deleted'	=> 0,
			])->row_array();

			if (!empty($user_email_info) && !empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_email_info['id']))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
			}

			if (!empty($user_email_info) && ($user_email_info['role_id'] == 9 || $user_email_info['role_id'] == 3)) {
				$this->json['error'] = _li('Your_email_is_already_registered_with_us');
			}

			if (!empty($this->input->post('user_id')) && !empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $this->input->post('user_id')))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
			}

			if (!empty(trim($this->input->post('mobile')))) {
				$user_info = $this->db->get_where('users', [
					'mobile'	=> $this->input->post('mobile'),
					'_deleted'	=> 0,
				])->row_array();

				if (!empty($user_info) && $user_info['role_id'] == 9) {
					$this->json['error'] = _li('your_mobile_is_already_registered_with_us');
				}

				if (!empty($user_info) && $user_info['email'] != $this->input->post('email')) {
					$this->json['error'] = _li('This_mobile_is_already_registered_with_this_email_address ') . masked_email($user_info['email']);
				}

				if (!empty($user_info) && ($user_info['email'] == $this->input->post('email')) && !empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))) {
					$this->json['error'] = _li('You_are_already_part_of_this_event');
				}
			}

		}

		if (!$this->json) {
			self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['lead_id'] = self::_addLead();
		}
	}

	public function globalEventVerifySignupOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile]');

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyOtp($this->input->post('type') == 'mobile')) {
				return $this->json['error'] 	= _l('enter_valid_verification_code');
			}

			if (empty($lead_info = $this->lead_model->get($this->input->post('lead_id')))) {
				return $this->json['error'] 	= _l('invalid_url');
			}

			$user_info = $this->db->get_where('users', [
				'email' => $lead_info['email'],
				'_deleted'	=> 0
			])->row_array();

			if (!empty($user_info)) {
				if (
					!empty($lead_info['event_id']) &&
					!empty($user_info['id']) &&
					!empty($this->event_user_model->getEventUserByUserId($lead_info['event_id'], $user_info['id']))
				) {
					$this->json['success'] 	= _l( 'You_are_already_registered_in_event');
					return;
				}

				$explode = explode(' ', ($lead_info['name'] ?? ''), 2);

				$first_name = array_shift($explode);
				$last_name = array_shift($explode);

				$password = uniqid();
				$verification_code = sha1(md5(($user_info['username'] ?? '') . $password . $this->config->item('password_salt')));

				$this->student_model->edit($user_info['id'], [
					'first_name'	=> $first_name ?? '',
					'last_name'		=> $last_name ?? '',
					'parent_name'	=> $lead_info['parent_name'] ?? '',
					'mobile'		=> $lead_info['mobile'] ?? $user_info['mobile'],
					'email'			=> $lead_info['email'] ?? $user_info['email'],
					// 'source'		=> $lead_info['source'] ?? '',
					'country_id'	=> (int)($lead_info['country_id'] ?? 0),
					'state_id'		=> (int)($lead_info['state_id'] ?? 0),
					'city_id'		=> (int)($lead_info['city_id'] ?? 0),
					'grade_id'		=> $lead_info['grade'] ?? $user_info['grade'],
					'section_id'	=> $lead_info['section'] ?? $user_info['grade'],
					'grade'			=> $lead_info['grade'] ?? $user_info['grade'],
					'section'		=> $lead_info['section'] ?? $user_info['grade'],
					'role_id'		=> 2,
					'site_id'		=> (int)$lead_info['site_id'],
					'status'		=> 1,
					'ip'			=> $this->input->ip_address(),
					'timezone'		=> $lead_info['timezone'] ?? '',
					'verification_code'	=> $verification_code,
					'mobile_verified'	=> ($this->input->post('type') ?? 'mobile') == 'mobile',
					'email_verified'	=> ($lead_info['email'] == $user_info['email'])? $user_info['email_verified'] : 0,
				]);

				$user_id = $user_info['id'];

				$this->alert_model->signup($user_id, $lead_info['utm_medium'] ?? '', $lead_info['event_id'] ?? 0);

				self::_formatUser($user_info['id']);
				self::_addToken($this->student_model->get($user_info['id']));
			} else {
				$lead_info['source'] = $lead_info['utm_medium'] ?? '';

				$user_id = self::_doLogin($lead_info + [
					'type' => $this->input->post('type')
				]);
			}

			$this->lead_model->edit($lead_info['id'], [
				'student_id'		=> (int)$user_id,
				'mobile_verified' 	=> $this->input->post('type') == 'mobile' ? 1 : 0,
				'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'event_user_signup_' . (int)$this->input->post('event_id')
			]);

			// add to event
			if (
				!empty($lead_info['event_id']) &&
				$user_id &&
				empty($this->event_user_model->getEventUserByUserId($lead_info['event_id'], $user_id))
			) {

				$event_user_id = $this->event_user_model->add([
					'event_id'	=> (int)$lead_info['event_id'],
					'user_id'	=> (int)$user_id,
				]);
			}

			$this->json['success'] 	= _l('you_are_successfully_verified');
		}
	}
}
