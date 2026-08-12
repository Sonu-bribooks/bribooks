<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait StudentInvite {
	public function getStudentInvite() {
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('user_id', _l('student_id'), [
			'trim',
			'required',
			'numeric',
			['student', [$this->validate_model, 'student']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_info = $this->student_model->get_all([
				'user_id' 			=> (int)$this->input->post('user_id'),
				'verification_code' => $this->input->post('code')
			])['rows'][0] ?? [])) {
				$this->json['error'] = _l('invalid_invite');
				return;
			}

			if (!empty($user_info['verified'])) {
				$this->json['error'] = _l('already_accepted_invite');
				return;
			}

			$this->student_model->edit($user_info['id'], [
				'email_verified'	=> 1,
			]);

			$site_info = $this->site_model->get($user_info['site_id']);

			$this->json['info'] = [
				'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'first_name'=> $user_info['first_name'],
				'last_name'	=> $user_info['last_name'],
				'email'		=> $user_info['email'],
				'mobile'	=> $user_info['mobile'],
				'grade'		=> $user_info['grade'],
				'section'	=> $user_info['section'],
				'school'	=> $site_info['name'],
			];
		}
	}

	public function sendStudentInviteOtp() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile,whatsapp]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);
		$this->form_validation->set_rules('user_id', _l('student_id'), [
			'trim',
			'required',
			'numeric',
			['student', [$this->validate_model, 'student']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			if (empty($user_info = $this->student_model->get_all([
				'user_id' 			=> (int)$this->input->post('user_id'),
				'verification_code' => $this->input->post('code')
			])['rows'][0] ?? [])) {
				$this->json['error'] = _l('invalid_invite');
				return;
			}

			if (
				!empty($user_mobile_info = $this->db->get_where('users', [
					'mobile' => $this->input->post('mobile'),
				])->row_array()) &&
				$user_mobile_info['id'] != $this->input->post('user_id')
			) {
				$this->json['error'] = 'This mobile is already registered with BriBooks';
				return;
			}
		}

		if (!$this->json) {
			$country_info 	= self::getCountry(true);
			$location 		= $country_info['country'];

			$site_info 		= $this->site_model->get($user_info['site_id']);
			$country_info 	= $this->country_model->get($site_info['country_id']);

			$result = $this->lead_model->add([
				'student_id'				=> (int)$user_info['id'],
				'country_id'				=> (int)$user_info['country_id'],
				'state_id'					=> (int)$user_info['state_id'],
				'city_id'					=> (int)$user_info['city_id'],
				'site_id'					=> $user_info['site_id'],
				'country_code'				=> $country_info['code'] ?? '',
				'name'						=> $this->input->post('name'),
				'email'						=> $user_info['email'],
				'mobile'					=> $this->input->post('mobile'),
				'timezone'					=> $this->input->post('timezone'),
				'location'					=> $location,
				'ip'						=> $this->input->ip_address(),
				'grade'						=> $user_info['grade'],
				'section'					=> $user_info['section'],
				'email_verified'			=> 1,
				'source'					=> $this->input->post('source') ?? '',
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
			]);

			$this->json['lead_id'] = $result;

			// true mobile otp and false email otp
			self::_executeOtp(
				$this->input->post('type') == 'mobile',
				false,
				$this->input->post('type') == 'whatsapp',
			);

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks');
		}
	}

	public function verifyStudentInviteOtp() {
		if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} elseif ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			// true mobile and false email otp verify
			if (self::_verifyOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$this->lead_model->edit($this->input->post('lead_id'), [
					'mobile_verified'	=> 1,
				]);

				$lead_info 	= $this->lead_model->get($this->input->post('lead_id'));
				$student_id = self::_updateStudent($lead_info);

				$this->json['success'] 		= _l('verified');
				$this->json['lead_id'] 		= $lead_info['lead_id'];
				$this->json['student_id'] 	= $student_id;
				$this->json['name'] 		= $lead_info['name'];

				// self::_formatUser($student_id);
				// self::_addToken($this->student_model->get($student_id));

				CI_Events::trigger('student_invite_accepted', [
					'student_id' 	=> (int)$student_id,
					'lead_id' 		=> (int)$lead_info['id'],
				]);

				CI_Events::trigger('access_log', [
					'module'	=> 'student_invite_accepted_' . (int)$lead_info['student_id']
				]);
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}

	private function _updateStudent($data = []) {
		$explode 	= explode(' ', ($data['name'] ?? ''), 2);
		$first_name = array_shift($explode);
		$last_name 	= array_shift($explode);

		if (!empty($data['mobile'])) {
			$username = strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['name']), 0, 4) .
				substr($data['mobile'], -4)
			));
		} else {
			$this->db->select_max('id');
			$last_user_id = $this->db->get('users')->row_array()['id'];
			$last_user_id++;

			$last_user_id = sprintf('%06d', $last_user_id);

			$username = strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['name']), 0, 2) .
				substr($last_user_id, -6)
			));
		}

		$password 			= uniqid();
		$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
		$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

		if (empty($data['site_id'])) {
			$site_info = $this->site_model->getSiteByName($data['location']);
			$data['site_id'] = $site_info['id'] ?? $this->config->item('default_site_id');
		}

		$this->student_model->edit($data['student_id'], [
			'first_name'	=> $first_name ?? '',
			'last_name'		=> $last_name ?? '',
			'slug'			=> get_user_slug($username),
			'username'		=> $username,
			'password'		=> $encoded_password,
			'mobile'		=> $data['mobile'] ?? '',
			'source'		=> $data['source'] ?? '',
			'location'		=> $data['location'],
			'ip'			=> $this->input->ip_address(),
			'timezone'		=> $data['timezone'] ?? '',
			'verification_code'	=> $verification_code,
			'verified'			=> 1,
			'mobile_verified'	=> $data['mobile_verified'],
		]);

		return $data['student_id'];
	}
}
