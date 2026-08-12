<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Login {
	public function login() {
		
		$this->form_validation->set_rules('username', _l('username'), 'trim|required|min_length[5]|max_length[20]');
		$this->form_validation->set_rules('password', _l('password'), 'trim|required|min_length[8]|max_length[40]');

		self::_runFormValidation();

		if (!$this->json) {
			if (ENVIRONMENT == 'production') {
				if (!self::_verifyCaptcha()) {
					$this->json['error'] = _li('Invalid Captcha. Please try again.');
					return;
				}
			}

			if (
				$user_info = $this->db->get_where('users', [
					'username'	=> $this->input->post('username'),
					'password'	=> sha1(md5($this->input->post('password') . $this->config->item('password_salt'))),
				])->row_array()
			) {
				if (empty($user_info['verified'])) {
					$this->user_model->edit($user_info['id'], [
						'verified'			=> 1,
						'email_verified'	=> 1,
					]);
				}

				self::_formatUser($user_info['id']);
				self::_addToken($user_info);

				// $this->json['redirect'] = vsprintf('/resetpassword?code=%s', [
				// 	$user_info['verification_code'],
				// ]);
				// $this->json['error'] = _li('Account not verified yet! Check your email for verification link');
			} else {
				$this->json['error'] = _li('Invalid Username or Password');
			}
		}
	}

	private function _addToken($user_info = []) {
		$date = new DateTime();

		$token['user_id'] 	= $user_info['id'];
		$token['role_id'] 	= $user_info['role_id'];
		$token['iat'] 		= $date->getTimestamp();
		$token['exp'] 		= $date->getTimestamp() + LOGIN_SESSION_ACTIVE_DAYS * 24 * 3600;

		if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
			$this->json['user']['token'] = \Firebase\JWT\JWT::encode($token, $this->config->item('bb_secret_jwt_token'), 'HS256');
		} else {
			$this->json['user']['token'] = \Firebase\JWT\JWT::encode($token, $this->config->item('bb_secret_jwt_token'));
		}

		$this->user_token_model->add([
			'user_id'	=> $user_info['id'],
			'token'		=> $this->json['user']['token'],
		]);

		$this->input->set_cookie('bb_token', $this->json['user']['token'], LOGIN_SESSION_ACTIVE_DAYS * 24 * 3600);
	}

	public function updateUserPassword() {
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('password', _l('password'), 'trim|required|min_length[8]|max_length[40]');

		self::_runFormValidation();

		if (!$this->json) {
			if (
				$user_info = $this->db->get_where('users', [
					'verification_code'	=> $this->input->post('code'),
					// 'verified'			=> 0,
				])->row_array()
			) {
				$this->student_model->edit($user_info['id'], [
					'verified'			=> 1,
					'email_verified'	=> 1,
					'password'			=> sha1(md5($this->input->post('password') . $this->config->item('password_salt'))),
				]);
				$this->alert_model->resetPasswordAlert($user_info['id']);
				$this->json['success'] = _li('Password has been updated successfully. Please login again.');

				// self::_formatUser($user_info['id']);
				// self::_addToken($user_info);
			} else {
				$this->json['error'] = _li('Invalid password reset url');
			}
		}
	}

	public function forgotPassword() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|min_length[8]|max_length[255]');

		self::_runFormValidation();

		if (!$this->json) {
			if (
				$user_info = $this->db->get_where('users', [
					'email'		=> $this->input->post('email'),
					'role_id'	=> 2,
				])->row_array()
			) {
				$this->alert_model->forgotPasswordAlert($user_info['id']);
				$this->json['success'] = _li('We have sent a link to reset your password on your registered email address');
			} else {
				$this->json['error'] = _li('Email address does not exist!');
			}
		}
	}

	private function _doLogin($data = [], $alert = true) {
		$search_data = [
			'status'			=> 1,
			'_deleted'			=> 0,
		];

		if (in_array(($data['type'] ?? 'mobile'), ['mobile', 'whatsapp'])) {
			$search_data['mobile'] 			= $data['mobile'];
			$update_data['mobile_verified'] = 1;
		} else {
			$search_data['email'] 			= $data['email'];
			$update_data['email_verified'] 	= 1;
		}

		$this->db->group_start();
		$this->db->where('role_id', 2);
		$this->db->or_where('role_id', 9);
		$this->db->or_where('role_id', 3);
		$this->db->or_where('role_id', 11);
		$this->db->group_end();

		if ($user_info = $this->db->get_where('users', $search_data)->row_array()) {
			$this->user_model->edit($user_info['id'], $update_data);

			if ($user_info['role_id'] == 9) {
				self::_formatSchool($user_info['id']);
			} elseif ($user_info['role_id'] == 3) {
				self::_formatTeacher($user_info['id']);
			} elseif ($user_info['role_id'] == 11) {
				self::_formatReviewer($user_info['id']);
			} else {
				self::_formatUser($user_info['id']);
			}

			self::_addToken($user_info);

			return $user_info['id'] ?? 0;
		} else {
			$site_id = $data['site_id'] ?? 0;

			if (empty($site_id)) {
				$site_id = $this->config->item('default_site_id');
			}

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

			$user_id = $this->student_model->add([
				'first_name'	=> $first_name ?? '',
				'last_name'		=> $last_name ?? '',
				'parent_name'	=> $data['parent_name'] ?? '',
				'parent_email'	=> $data['parent_email'] ?? '',
				'slug'			=> get_user_slug($username),
				'username'		=> $username,
				'password'		=> $encoded_password,
				'mobile'		=> $data['mobile'] ?? '',
				'email'			=> $data['email'] ?? '',
				'source'		=> $data['source'] ?? '',
				'dob'			=> $data['dob'] ?? '',
				'country_id'	=> (int)($data['country_id'] ?? 0),
				'state_id'		=> (int)($data['state_id'] ?? 0),
				'city_id'		=> (int)($data['city_id'] ?? 0),
				'grade_id'		=> $data['grade'],
				'section_id'	=> $data['section'],
				'grade'			=> $data['grade'],
				'section'		=> $data['section'],
				'role_id'		=> 2,
				'site_id'		=> (int)$site_id,
				'status'		=> 1,
				'location'		=> $data['location'] ?? '',
				'referral_code'	=> mb_strtoupper(uniqid()),
				'verification_code'	=> $verification_code,
				'ip'				=> $this->input->ip_address(),
				'timezone'			=> $data['timezone'] ?? '',
				'mobile_verified'	=> in_array(($data['type'] ?? 'mobile'), ['mobile', 'whatsapp']),
				'email_verified'	=> in_array(($data['type'] ?? 'mobile'), ['email', 'email_link']),
				'parent_referral_id'=> (int)($data['parent_referral_id'] ?? 0)
			]);

			if ($alert) {
				$this->alert_model->signup($user_id, $data['source'] ?? '', ($data['event_id'] ?? 0));
			}

			self::_formatUser($user_id);
			self::_addToken($this->user_model->get($user_id));

			return $user_id;
		}
	}

	public function logout() {
		$this->input->set_cookie('bb_token', '', LOGIN_SESSION_ACTIVE_DAYS * 24 * 3600);

		$this->session->unset_userdata([
			'user_id',
			'user_email',
			'user_mobile',
			'user_role_id',
			'user_role',
			'user_name',
			'user_site',
			'shipping_address_id',
		]);
	}
}
