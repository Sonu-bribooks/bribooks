<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CommonLogin {
	public function sendOtp() {
		return;
		$json = [];

		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!($row  = $this->db->get_where('users', [
			'email'		=> $this->input->post('email'),
			'role_id'	=> 2
		])->row_array())) {
			$json['error'] = _li('You haven\'t registered yet!');
		}

		if (!$json) {
			// Hit the sms Api
			// Config set for global sms gateway
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($row['site_id'] ?? 0);

			if (
				in_array($this->input->post('mobile'), TESTING_MOBILES)
				|| in_array($this->input->post('email'), TESTING_EMAILS)
			) {
				$otp = DEFAULT_OTP;
			} else {
				$otp = mt_rand(100000, 999999);
			}

			// !in_array($this->input->post('mobile'), TESTING_MOBILES) && $this->alert_model->sms(
			// 	$this->input->post('mobile'),
			// 	str_replace('{otp}', $otp, get_settings('sms_otp'))
			// );

			!in_array($this->input->post('email'), TESTING_EMAILS) && $this->alert_model->validationOtp(
				$this->input->post('email'),
				_l('validation_code'),
				$otp
			);

			$this->otp_model->add([
				'mobile'		=> $this->input->post('email'),
				'otp'			=> $otp,
			]);

			$json['error'] 		= $this->session->flashdata('error_message');
			$json['success'] 	= $this->session->flashdata('flash_message');

			$json['success'] 	= _l('validation_code_sent_to_your_email');

			$json['error'] 		= empty($json['error']) ? false : $json['error'];
		}

		output_json($json);
	}

	public function validateOtp() {
		return;
		$json = [];

		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('otp', _l('validation_code'), 'trim|required|numeric|exact_length[6]');
		$this->input->post('school_code') && $this->form_validation->set_rules('school_code', _l('school_code'), [
			'trim',
			'required',
			['school_code', [$this->validate_model, 'schoolCode']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		$login_data = [
			'email'		=> $this->input->post('email'),
			'role_id'	=> 2
		];

		if ($this->input->post('school_code') && ($site_info = $this->site_model->getByCode($this->input->post('school_code')))) {
			$login_data['site_id'] = $site_info['id'];
		}

		if (!($row  = $this->db->get_where('users', $login_data)->row_array())) {
			$json['error'] = _l('error_unauthorized');
		}

		if (!$json) {
			// Config set for global sms gateway
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($row['site_id'] ?? 0);

			if ($this->otp_model->get([
				'mobile'		=> $this->input->post('email'),
				'otp'			=> $this->input->post('otp'),
			])) {
				$this->otp_model->edit([
					'mobile'		=> $this->input->post('email'),
					'otp'			=> $this->input->post('otp'),
				]);

				$this->db->order_by('date_added', 'DESC');
				$query = $this->db->get_where('users', [
					'email'			=> $this->input->post('email'),
					'role_id'		=> 2,
					'site_id'		=> $row['site_id'] ?? 0,
				]);

				if ($row = $query->row()) {
					// $this->session->set_userdata('quiz_uid', $row->id);
					$this->session->set_userdata('user_id', $row->id);
					$this->session->set_userdata('user_email', $row->email);
					$this->session->set_userdata('role_id', $row->role_id);
					$this->session->set_userdata('role', get_user_role('user_role', $row->id));
					$this->session->set_userdata('name', $row->first_name . ' ' . $row->last_name);

					$this->session->set_userdata('user_site', $row->site_id ?? 0);

					if ($row->role_id == 2) {
						$json['success'] = _l('email_verified');

						$this->session->set_userdata('user_login', '1');

						$json['redirect'] = base_url('home/parent_dashboard');
					} else {
						$json['error'] = _l('error_unauthorized');
					}
				} else {
					$json['error'] = _l('error_unauthorized');
				}
			} else {
				$json['error'] = _l('your_validation_code_is_expired_or_invalid');
			}
		}

		output_json($json);
	}

	public function code($code = NULL) {
		if ($code && ($row = $this->user_model->loginWithCode([
			'code'		=> (string)$code
		])) && ($user_info = $this->user_model->get($row['user_id']))) {
			$this->session->set_userdata('user_id', $user_info['id']);
			$this->session->set_userdata('role_id', $user_info['role_id']);
			$this->session->set_userdata('role', get_user_role('user_role', $user_info['id']));
			$this->session->set_userdata('name', $user_info['first_name'] . ' ' . $user_info['last_name']);
			$this->session->set_userdata('user_site', $user_info['site_id'] ?? 0);
			$this->session->set_userdata('user_login', '1');

			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($user_info['site_id'] ?? 0);

			$this->input->set_cookie('login_code', '', 4 * 3600);

			self::icodeRegister([
				'first_name'	=> $user_info['first_name'],
				'last_name'		=> $user_info['last_name'] ? $user_info['last_name'] : $user_info['first_name'],
				'email'			=> $user_info['email'],
				'grade'			=> (int)$user_info['grade'],
				// 'school_code'	=> mb_strtolower(explode('-', $this->config->item('site_code'), 2)[0]),
				'school_code'	=> mb_strtolower($this->config->item('site_code')),
				'password'		=> $user_info['password'],
				'country_code'	=> str_replace('+', '', $this->config->item('site_tel_code')),
			]);

			self::eventRegister([
				'first_name'	=> $user_info['first_name'],
				'last_name'		=> $user_info['last_name'] ? $user_info['last_name'] : $user_info['first_name'],
				'email'			=> $user_info['email'],
				'mobile'		=> $user_info['mobile'],
				'grade'			=> (int)$user_info['grade'],
				'password'		=> $user_info['password'],
			]);

			redirect(site_url('home/parent_dashboard'), 'refresh');
		} else {
			$this->session->set_flashdata('flash_message', _li('Invalid Code'));

			redirect(site_url(), 'refresh');
		}
	}

	private function icodeRegister($data = []) {
		return;

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setHeader([
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/userRegister', [
			'firstName'		=> $data['first_name'],
			'lastName'		=> $data['last_name'],
			'email'			=> $data['email'],
			'grade'			=> (int)$data['grade'],
			'schoolCode'	=> $data['school_code'] ?? 'in',
			'password'		=> $data['password'] ?? '123456',
			'countryCode'	=> $data['country_code'] ?? 91,
			'gameId'		=> $data['game_id'] ?? 801,
		])->rows();

		log_message('KB', 'Icode register:: ' . print_r([$data, $result], 1));

		self::icodeLogin($data);
	}

	private function icodeLogin($data = []) {
		return;

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setHeader([
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/userLogin', [
			'email'			=> $data['email'],
			'password'		=> $data['password'] ?? '123456',
		]);

		log_message('KB', 'Icode login:: ' . print_r([
			$result->rows(),
		], 1));

		$this->load->helper('cookie');

		$cookie1 = http_parse_cookie($result->resHeaders()['Set-Cookie'][0] ?? '');
		$cookie2 = http_parse_cookie($result->resHeaders()['Set-Cookie'][1] ?? '');

		set_cookie('USERID', $cookie1['USERID'] ?? '', 4 * 3600, GAME_COOKIE_DOMAIN);
		set_cookie('SESSION', $cookie2['SESSION'] ?? '', 4 * 3600, GAME_COOKIE_DOMAIN);
		set_cookie('lang', 'en', time() + 4 * 3600, GAME_COOKIE_DOMAIN);
	}
}
