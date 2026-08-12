<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait TeacherSignup {
	public function sendTeacherOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email,whatsapp]');
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);

		$this->form_validation->set_rules('designation', _l('designation'),  'trim|required|in_list[librarian,english_teacher,class_teacher]');
		$this->form_validation->set_rules('grade[]', _l('grade'),  'trim|required|numeric');
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
		$this->input->post('site_id') && $this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
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

			$teacher_filter = [
				'site_id'	=> (int)$this->input->post('site_id'),
				'site_id'	=> (int)$this->input->post('site_id'),
				'grade'		=> (int)$this->input->post('grade_id'),
				'section'	=> $this->input->post('section_id'),
			];

			if (!empty($this->input->post('teacher_id'))) {
				$teacher_filter['teacher_id_ne'] = $this->input->post('teacher_id');
			}

			if (!empty($this->teacher_model->get_all($teacher_filter)['rows'][0])) {
				$this->json['error'] = sprintf(_l('Teacher_is_already_assigned_to_Grade_%s%s'), (int)$this->input->post('grade_id'), (string)$this->input->post('section_id'));
				return;
			}

			if (!empty($this->input->post('teacher_id'))) {
				if (!empty($user_email_info = $this->db->get_where('users', [
					'email' => $this->input->post('email'),
				])->row_array()) && ($user_email_info['id'] !=  $this->input->post('teacher_id'))) {
					$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks');
					return;
				}

				if (!empty($user_mobile_info = $this->db->get_where('users', [
					'mobile' => $this->input->post('mobile'),
				])->row_array()) && ($user_mobile_info['id'] !=  $this->input->post('teacher_id'))) {
					$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks');
					return;
				}
			} else {
				if (!empty($user_email_info = $this->db->get_where('users', [
					'email' => $this->input->post('email'),
				])->row_array())) {
					$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks');
					return;
				}

				if (!empty($user_mobile_info = $this->db->get_where('users', [
					'mobile' => $this->input->post('mobile'),
				])->row_array())) {
					$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks');
					return;
				}
			}
		}

		if (!$this->json) {
			$country_info 	= self::getCountry(true);
			$location 		= $country_info['country'];

			$site_info 		= $this->input->post('site_id') ? $this->site_model->get($this->input->post('site_id')) : [];
			$country_info 	= $this->country_model->get($this->input->post('country_id'));

			if (empty($this->input->post('site_id'))) {
				$site_id = $this->site_model->getSiteByName($location);
				$site_id = $site_info['id'] ?? $this->config->item('default_site_id');
			} else {
				$site_id = $site_info['id'];
			}

			$result = $this->teacher_lead_model->add([
				'teacher_id'				=> (int)$this->input->post('teacher_id') ?? 0,
				'event_id'					=> (int)$this->input->post('event_id') ?? 0,
				'country_id'				=> (int)$this->input->post('country_id'),
				'state_id'					=> (int)$this->input->post('state_id'),
				'city_id'					=> (int)$this->input->post('city_id'),
				'site_id'					=> $site_id,
				'country_code'				=> $country_info['code'] ?? '',
				'name'						=> $this->input->post('name'),
				'designation'				=> $this->input->post('designation'),
				'email'						=> $this->input->post('email'),
				'mobile'					=> $this->input->post('mobile'),
				'timezone'					=> $this->input->post('timezone'),
				'location'					=> $location,
				'ip'						=> $this->input->ip_address(),
				'grades'					=> implode(',', is_array($this->input->post('grade')) ? $this->input->post('grade') : []),
				'sections'					=> implode(',', is_array($this->input->post('section')) ? $this->input->post('section') : []),
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
				$this->input->post('type') == 'whatsapp'
			);

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks');
		}
	}

	public function verifyTeacherOtp() {
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
			['lead', [$this->validate_model, 'teacherLead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			// true mobile and false email otp verify
			if (self::_verifyOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$lead_info = $this->teacher_lead_model->get($this->input->post('lead_id'));

				if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
					$this->teacher_lead_model->edit($lead_info['id'], [
						'mobile_verified'	=> 1,
					]);
				} elseif ($this->input->post('type') == 'email') {
					$this->teacher_lead_model->edit($lead_info['id'], [
						'email_verified'	=> 1,
					]);
				}

				$lead_info = $this->teacher_lead_model->get($this->input->post('lead_id'));

				if (!empty($lead_info['teacher_id'])) {
					$teacher_id = self::_updateTeacher($lead_info);
				} else {
					$teacher_id = self::_addTeacher($lead_info);
				}

				$this->teacher_lead_model->edit($lead_info['id'], [
					'teacher_id' 	=> $teacher_id,
				]);

				$this->json['success'] 		= _l('verified');
				$this->json['lead_id'] 		= $lead_info['lead_id'];
				$this->json['teacher_id'] 	= $teacher_id;
				$this->json['name'] 		= $lead_info['name'];

				self::_formatTeacher($teacher_id);

				if (empty($lead_info['event_id'])) {
					CI_Events::trigger('teacher_event_auto_enrol', [
						'teacher_id' 	=> (int)$teacher_id,
						'lead_id' 		=> (int)$lead_info['id'],
					]);

					CI_Events::trigger('access_log', [
						'module'	=> 'teacher_event_auto_enrol_' . (int)$lead_info['teacher_id']
					]);
				} else {
					// add to event
					if (
						!empty($lead_info['event_id']) &&
						$teacher_id &&
						empty($this->event_teacher_model->getEventUserByUserId($lead_info['event_id'], $teacher_id))
					) {
						$this->event_teacher_model->add([
							'event_id'		=> (int)$lead_info['event_id'],
							'teacher_id'	=> (int)$teacher_id,
						]);
					}

					CI_Events::trigger('access_log', [
						'module'	=> 'teacher_event_enrol_' . (int)$lead_info['teacher_id']
					]);

					$this->cron_model->add([
						'code'			=> 'signupTeacherCron_' . $teacher_id,
						'action'		=> 'alert_model->signupTeacherCron',
						'data'			=> [$teacher_id, $lead_info['event_id']],
						'site_id'		=> $lead_info['site_id'] ?? 1,
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);
				}
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}

	private function _updateTeacher($data = []) {
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
			$site_info 			= $this->site_model->getSiteByName($data['location']);
			$data['site_id'] 	= $site_info['id'] ?? $this->config->item('default_site_id');
		}

		$this->teacher_model->edit($data['teacher_id'], [
			'first_name'		=> $first_name ?? '',
			'last_name'			=> $last_name ?? '',
			'slug'				=> get_user_slug($username),
			'username'			=> $username,
			'password'			=> $encoded_password,
			'mobile'			=> $data['mobile'] ?? '',
			'email'				=> $data['email'] ?? '',
			'grade'				=> $data['grades'] ?? 1,
			'section'			=> $data['sections'] ?? 'A',
			'grade_id'			=> $data['grades'] ?? 1,
			'section_id'		=> $data['sections'] ?? 'A',
			'city_id'			=> (int)($data['city_id'] ?? 0),
			'state_id'			=> (int)($data['state_id'] ?? 0),
			'country_id'		=> (int)($data['country_id'] ?? 0),
			'site_id'			=> (int)($data['site_id'] ?? 0),
			'source'			=> $data['source'] ?? '',
			'location'			=> $data['location'],
			'ip'				=> $this->input->ip_address(),
			'timezone'			=> $data['timezone'] ?? '',
			'verification_code'	=> $verification_code,
			'verified'			=> 1,
			'email_verified'	=> $data['email_verified'],
			'mobile_verified'	=> $data['mobile_verified'],
		]);

		return $data['teacher_id'];
	}

	private function _addTeacher($data = []) {
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

		$teacher_id = $this->teacher_model->add([
			'first_name'	=> $first_name ?? '',
			'last_name'		=> $last_name ?? '',
			'slug'			=> get_user_slug($username),
			'username'		=> $username,
			'password'		=> $encoded_password,
			'mobile'		=> $data['mobile'] ?? '',
			'email'			=> $data['email'] ?? '',
			'source'		=> $data['source'] ?? '',
			'site_id'		=> (int)$data['site_id'],
			'country_id'	=> (int)($data['country_id'] ?? 0),
			'state_id'		=> (int)($data['state_id'] ?? 0),
			'city_id'		=> (int)($data['city_id'] ?? 0),
			'grade'			=> $data['grades'],
			'section'		=> $data['sections'],
			'section_id'	=> $data['grades'],
			'role_id'		=> 3,
			'status'		=> 1,
			'location'		=> $data['location'],
			'ip'			=> $this->input->ip_address(),
			'timezone'		=> $data['timezone'] ?? '',
			'referral_code'	=> mb_strtoupper(uniqid()),
			'verification_code'	=> $verification_code,
			'mobile_verified'	=> $data['mobile_verified'],
			'email_verified'	=> $data['email_verified'],
		]);

		return $teacher_id;
	}

	private function _formatTeacher($user_id = 0) {
		if (
			$user_id &&
			!empty($user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 3,
				'status'	=> 1,
			])->row_array())
		) {
			$user = [
				'user_id'		=> $user_info['id'],
				'user_email'	=> $user_info['email'],
				'user_mobile'	=> $user_info['mobile'],
				'user_role_id'	=> $user_info['role_id'],
				'user_role'		=> get_user_role_by_id($user_info['role_id']),
				'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'user_site'		=> $user_info['site_id'] ?? 0,
				'user_site_id'	=> $user_info['site_id'] ?? 0,
			];

			$this->session->set_userdata($user);

			$site_info 		= $this->site_model->get($user_info['site_id']);
			$country_info 	= $this->country_model->get($user_info['country_id']);
			$state_info 	= $this->state_model->get($user_info['state_id']);
			$city_info 		= $this->city_model->get($user_info['city_id']);

			$this->json['user'] = [
				'id' 					=> $user_info['id'],
				'user_email'			=> $user_info['email'],
				'address_id'			=> $user_info['address_id'],
				'user_mobile'			=> $user_info['mobile'],
				'image'					=> $user_info['image'],
				'grade'					=> $user_info['grade'],
				'section'				=> $user_info['section'],
				'name'					=> trim($user_info['first_name'] . ' ' . $user_info['last_name']),
				'user_site'				=> $user_info['site_id'] ?? 0,
				'school'				=> $site_info['name'] ?? 0,
				'country_id'			=> $country_info['id'] ?? 0,
				'country_code'			=> $country_info['code'] ?? '',
				'state_id'				=> $state_info['state_id'] ?? 0,
				'state'					=> $state_info['name'] ?? '',
				'city_id'				=> $city_info['id'] ?? 0,
				'city'					=> $city_info['name'] ?? '',
				'role_id'				=> $user_info['role_id'],
				'role'					=> get_user_role_by_id($user_info['role_id']),
				'site_code'				=> $site_info['site_code'] ?? '',
				'site_type'				=> $site_info['site_type'] ?? '1',
				'contact_person_name'	=> $site_info['authorized_person'] ?? '',
				'verification_code'		=> $user_info['verification_code'] ?? '',
			];
		}
	}

	public function getTeacherInvite() {
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('user_id', _l('teacher_id'), [
			'trim',
			'required',
			'numeric',
			['teacher', [$this->validate_model, 'teacher']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_info = $this->teacher_model->get_all([
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

			$this->teacher_model->edit($user_info['id'], [
				'email_verified'	=> 1,
			]);

			$site_info = $this->site_model->get($user_info['site_id']);

			$this->json['info'] = [
				'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'email'		=> $user_info['email'],
				'grade'		=> $user_info['grade'],
				'section'	=> $user_info['section'],
				'school'	=> $site_info['name'],
			];
		}
	}

	public function sendTeacherInviteOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email,whatsapp]');
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);
		$this->form_validation->set_rules('user_id', _l('teacher_id'), [
			'trim',
			'required',
			'numeric',
			['teacher', [$this->validate_model, 'teacher']]
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

			if (empty($user_info = $this->teacher_model->get_all([
				'user_id' 			=> (int)$this->input->post('user_id'),
				'verification_code' => $this->input->post('code')
			])['rows'][0] ?? [])) {
				$this->json['error'] = _l('invalid_invite');
				return;
			}

			if (!empty($user_mobile_info = $this->db->get_where('users', [
				'mobile' => $this->input->post('mobile'),
			])->row_array())) {
				$this->json['error'] = 'This mobile is already registered with BriBooks';
				return;
			}
		}

		if (!$this->json) {
			$country_info 	= self::getCountry(true);
			$location 		= $country_info['country'];

			$site_info 		= $this->site_model->get($user_info['site_id']);
			$country_info 	= $this->country_model->get($site_info['country_id']);

			$result = $this->teacher_lead_model->add([
				'teacher_id'				=> (int)$user_info['id'],
				'country_id'				=> (int)$user_info['country_id'],
				'state_id'					=> (int)$user_info['state_id'],
				'city_id'					=> (int)$user_info['city_id'],
				'site_id'					=> $user_info['site_id'],
				'country_code'				=> $country_info['code'] ?? '',
				'name'						=> $this->input->post('name'),
				'designation'				=> 'class_teacher',
				'email'						=> $user_info['email'],
				'mobile'					=> $this->input->post('mobile'),
				'timezone'					=> $this->input->post('timezone'),
				'location'					=> $location,
				'ip'						=> $this->input->ip_address(),
				'grades'					=> $user_info['grade'],
				'sections'					=> $user_info['section'],
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
				$this->input->post('type') == 'whatsapp'
			);

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks');
		}
	}
}
