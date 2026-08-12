<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait GlobalTeacherSignup {
	public function sendGlobalTeacherOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email]');
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);

		$this->form_validation->set_rules('designation', _l('designation'),  'trim|in_list[librarian,english_teacher,class_teacher]');
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
				'grade'		=> (int)$this->input->post('grade'),
			];

			if (!empty($this->input->post('teacher_id'))) {
				$teacher_filter['teacher_id_ne'] = $this->input->post('teacher_id');
			}

			if (!empty($this->teacher_model->get_all($teacher_filter)['rows'][0])) {
				$this->json['error'] = sprintf(_l('Teacher_is_already_assigned_to_Grade_%s'), (int)$this->input->post('grade'));
				return;
			}

			if (!empty($this->input->post('teacher_id'))) {
				if (!empty($this->input->post('email')) && !empty($user_email_info = $this->db->get_where('users', [
					'email' => $this->input->post('email'),
				])->row_array()) && ($user_email_info['id'] !=  $this->input->post('teacher_id'))) {
					$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks');
					return;
				}

				if (!empty($this->input->post('mobile')) && !empty($user_mobile_info = $this->db->get_where('users', [
					'mobile' => $this->input->post('mobile'),
				])->row_array()) && ($user_mobile_info['id'] !=  $this->input->post('teacher_id'))) {
					$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks');
					return;
				}
			} else {
				if (!empty($this->input->post('email')) && !empty($user_email_info = $this->db->get_where('users', [
					'email' => $this->input->post('email'),
				])->row_array())) {
					$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks');
					return;
				}

				if (!empty($this->input->post('mobile')) && !empty($user_mobile_info = $this->db->get_where('users', [
					'mobile' => $this->input->post('mobile'),
				])->row_array())) {
					$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks');
					return;
				}
			}

			if (
				!empty($user_info = $this->db->get_where('users', [
					'email'		=> $this->input->post('email'),
					'role_id'	=> 3,
					'status'	=> 1,
					'_deleted'	=> 0,
				])->row_array()) &&
				!empty($this->event_teacher_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))
			) {
				$this->json['error'] 	= _l( 'You_are_already_enrolled_in_this_event');
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
				'grades'					=> is_array($this->input->post('grade')) ? implode(',', $this->input->post('grade')) : $this->input->post('grade'),
				'sections'					=> is_array($this->input->post('section')) ? implode(',', $this->input->post('section')) : $this->input->post('section'),
				'source'					=> $this->input->post('source') ?? '',
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
			]);

			$this->json['lead_id'] = $result;

			if (!empty($result)) {
				$password 			= uniqid();
				$verification_code 	= sha1(md5(time() . $password . $this->config->item('password_salt')));

				$this->lead_verification_code_model->add([
					'event_id'  => $this->input->post('event_id'),
					'lead_id'   => $result,
					'type'	  => 'teacher',
					'code'	  => $verification_code
				]);

				self::_sendEmailVerifyLink(
					$this->input->post('email'),
					$this->input->post('event_id'),
					$result,
					$verification_code,
					'teacher'
				);
			}

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks');
		}
	}

	public function verifyGlobalTeacherOtp() {
		$this->form_validation->set_rules('code', _l('code'), 'trim|required');

		$this->form_validation->set_rules('lid', _l('lid'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'teacherLead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$lead_info = $this->teacher_lead_model->get($this->input->post('lid'));

			if (!empty($valid_teacher = $this->lead_verification_code_model->get_all([
				'lead_id'   => $this->input->post('lid'),
				'code'	  => $this->input->post('code'),
				'type'	  => 'teacher'
			])['rows'][0] ?? '') && !empty($lead_info)) {

				if (
					!empty($user_info = $this->db->get_where('users', [
						'email'		=> $lead_info['email'],
						'role_id'	=> 3,
						'status'	=> 1,
						'_deleted'	=> 0,
					])->row_array()) &&
					!empty($this->event_teacher_model->getEventUserByUserId($lead_info['event_id'], $user_info['id']))
				) {
					$this->json['success'] 	= _l( 'You_are_already_enrolled_in_this_event');
					return;
				}

				$lead_info['mobile_verified'] = $this->input->post('type') == 'mobile' ? 1 : 0;
				$lead_info['email_verified']  = $this->input->post('type') == 'email' ? 1 : 0;

				if (!empty($lead_info['teacher_id'])) {
					$teacher_id = self::_updateGlobalTeacher($lead_info);
				} else {
					$teacher_id = self::_addGlobalTeacher($lead_info);
				}

				$this->teacher_lead_model->edit($lead_info['id'], [
					'teacher_id'		=> (int)$teacher_id,
					'mobile_verified' 	=> $this->input->post('type') == 'mobile' ? 1 : 0,
					'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
				]);

				$this->json['success'] 		= _l('verified');
				$this->json['lead_id'] 		= $lead_info['id'];
				$this->json['teacher_id'] 	= $teacher_id;
				$this->json['name'] 		= $lead_info['name'];

				self::_formatGlobalTeacher($teacher_id);

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
				$this->json['error'] 	= _l('invalid_url');
			}
		}
	}

	private function _updateGlobalTeacher($data = []) {
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

		$teacher_info = $this->teacher_model->get($data['teacher_id']);

		$this->teacher_model->edit($data['teacher_id'], [
			'first_name'	=> $first_name ?? '',
			'last_name'		=> $last_name ?? '',
			'slug'			=> get_user_slug($username),
			'username'		=> $username,
			'password'		=> $encoded_password,
			'email'			=> $data['email'] ?? ($teacher_info['email'] ?? ''),
			'mobile'		=> $data['mobile'] ?? ($teacher_info['mobile'] ?? ''),
			'source'		=> $data['source'] ?? '',
			'location'		=> $data['location'],
			'ip'			=> $this->input->ip_address(),
			'timezone'		=> $data['timezone'] ?? '',
			'verification_code'	=> $verification_code,
			'verified'			=> 1,
			'email_verified'	=> $data['email_verified'] ?? ($teacher_info['email_verified'] ?? 0),
			'mobile_verified'	=> $data['mobile_verified'] ?? ($teacher_info['mobile_verified'] ?? 0)
		]);

		return $data['teacher_id'];
	}

	private function _addGlobalTeacher($data = []) {
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
			'grade_id'		=> $data['grades'],
			'section'		=> $data['sections'],
			'section_id'	=> $data['sections'],
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

	private function _formatGlobalTeacher($user_id = 0) {
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
}
