<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventSignup {
	public function eventSendSignupOtp() {
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
		!empty($this->input->post('city_id')) && $this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('source', _l('source'),  'trim|required|min_length[1]');

		if (!empty($this->input->post('event_id')) && $this->input->post('event_id') == 10) {
			$this->form_validation->set_rules('grade_id', _l('grade'), [
				'trim',
				'required',
				'numeric'
			]);
			$this->form_validation->set_rules('section_id', _l('section'), [
				'trim',
				'required'
			]);
		}

		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile,whatsapp]');

		$event_info = $this->event_model->get($this->input->post('event_id'));

		if (in_array($this->input->post('type'), ['mobile', 'whatsapp']) || (!empty($event_info) && $event_info['country_code'] == 'IN')) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
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

			$user_mobile_info = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('mobile'),
			])->row_array();

			if (!empty($user_mobile_info) && $user_mobile_info['role_id'] == 9) {
				$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks_on_school_account');
			}

			if (!empty($user_mobile_info) && $user_mobile_info['role_id'] == 3) {
				$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks_on_teacher_account');
			}

			if (!empty($user_mobile_info) && !empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_mobile_info['id']))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
			}

			if (!empty($this->input->post('user_id')) && !empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $this->input->post('user_id')))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
			}

			$user_info = $this->db->get_where('users', [
				'email'			=> $this->input->post('email'),
			])->row_array();

			if (!empty($user_info) && $user_info['role_id'] == 9) {
				$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks_on_school_account');
			}

			if (!empty($user_info) && $user_info['role_id'] == 3) {
				$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks_on_teacher_account');
			}

			if (!empty($user_info) && $user_info['mobile'] != $this->input->post('mobile')) {
				$this->json['error'] = _li('Your_email_already_registered_with_this_mobile_') . masked_mobile($user_info['mobile']);
			}

			if (!empty($user_info) && ($user_info['mobile'] == $this->input->post('mobile')) && !empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
			}
		}

		if (!$this->json) {
			self::_executeOtp(
				$this->input->post('type') == 'mobile',
				false,
				$this->input->post('type') == 'whatsapp',
			);

			$this->json['lead_id'] = self::_addLead();
		}
	}

	public function eventVerifySignupOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile,whatsapp]');

		if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		}

		if ($this->input->post('type') == 'email') {
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
			if (self::_verifyOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$lead_info = $this->lead_model->get($this->input->post('lead_id'));

				$user_info = $this->db->get_where('users', [
					'mobile' => $lead_info['mobile'],
				])->row_array();

				if (!empty($user_info)) {

					$explode = explode(' ', ($lead_info['name'] ?? ''), 2);

					$first_name = array_shift($explode);
					$last_name = array_shift($explode);

					$password = uniqid();
					$verification_code = sha1(md5(($user_info['username'] ?? '') . $password . $this->config->item('password_salt')));

					$user_update = [
						'first_name'	=> $first_name ?? '',
						'last_name'		=> $last_name ?? '',
						'parent_name'	=> $lead_info['parent_name'] ?? '',
						'parent_email'	=> $lead_info['parent_email'] ?? '',
						'mobile'		=> $lead_info['mobile'] ?? '',
						'email'			=> $lead_info['email'] ?? '',
						'country_id'	=> (int)($lead_info['country_id'] ?? 0),
						'state_id'		=> (int)($lead_info['state_id'] ?? 0),
						'city_id'		=> (int)($lead_info['city_id'] ?? 0),
						'grade_id'		=> $lead_info['grade'],
						'section_id'	=> $lead_info['section'],
						'grade'			=> $lead_info['grade'],
						'section'		=> $lead_info['section'],
						'role_id'		=> 2,
						'site_id'		=> (int)$lead_info['site_id'],
						'status'		=> 1,
						'ip'			=> $this->input->ip_address(),
						'timezone'		=> $lead_info['timezone'] ?? '',
						'mobile_verified'	=> ($lead_info['type'] ?? 'mobile') == 'mobile',
						'email_verified'	=> ($lead_info['email'] == $user_info['email'])? $user_info['email_verified'] : 0,
					];

					if (empty($user_info['verification_code'])) {
						$user_update['verification_code'] = $verification_code;
					}

					$this->student_model->edit($user_info['id'], $user_update);

					$user_id = $user_info['id'];

					$this->alert_model->signup($user_id, $lead_info['utm_medium'] ?? '', $lead_info['event_id']);

					self::_formatUser($user_info['id']);
					self::_addToken($this->student_model->get($user_info['id']));
				} else {
					$lead_info['source'] = $lead_info['utm_medium'] ?? '';

					$user_id = self::_doLogin($lead_info + [
						'type' => $this->input->post('type')
					]);
				}

				if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
					$this->lead_model->edit($lead_info['id'], [
						'student_id'		=> (int)$user_id,
						'mobile_verified'	=> 1,
					]);
				} else if ($this->input->post('type') == 'email') {
					$this->lead_model->edit($lead_info['id'], [
						'student_id'		=> (int)$user_id,
						'email_verified'	=> 1,
					]);
				}

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

				$this->json['success'] 	= _l('otp_successfully_verified');
			} else {
				$this->json['error'] 	= _l('please_enter_the_correct_verification_code');
			}
		}
	}

	public function verifyEventSignup() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('country_id', _l('country'),  'trim|numeric|max_length[3]');
		$this->form_validation->set_rules('site_id', _l('site'),  'trim|numeric|min_length[1]');

		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {
			$this->input->post('state_id') && $this->student_model->edit($this->session->userdata('user_id'), [
				'state_id'		=> (int)$this->input->post('state_id'),
				'city_id'		=> (int)$this->input->post('city_id'),
				'grade_id'		=> $this->input->post('grade_id'),
				'section_id'	=> $this->input->post('section_id'),
				'grade'			=> $this->input->post('grade_id'),
				'section'		=> $this->input->post('section_id'),
			]);

			$this->input->post('country_id') && $this->student_model->edit($this->session->userdata('user_id'), [
				'country_id'	=> (int)$this->input->post('country_id'),
				'site_id'		=> (int)$this->input->post('site_id'),
			]);

			if(empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $this->session->userdata('user_id')))) {
				$this->event_user_model->add([
					'event_id'	=> (int)$this->input->post('event_id'),
					'user_id'	=> (int)$this->session->userdata('user_id')
				]);

				$this->load->model('common/Cron_model', 'cron_model');
				$this->cron_model->add([
					'code'			=> 'signupCron_' . $this->session->userdata('user_id'),
					'action'		=> 'alert_model->signupCron',
					'data'			=> [$this->session->userdata('user_id')],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);
			}

			$this->json['success'] 	= _l('updated_successfully');
		}
	}

	public function sendOtpForUserEnrol() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
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
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('source', _l('school_name'),  'trim|required|min_length[1]');

		$this->form_validation->set_rules('grade_id', _l('grade'), [
			'trim',
			'required',
			'numeric'
		]);
		$this->form_validation->set_rules('section_id', _l('section'), [
			'trim',
			'required'
		]);

		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile,whatsapp]');

		$event_info = $this->event_model->get($this->input->post('event_id'));

		if (in_array($this->input->post('type'), ['mobile', 'whatsapp']) || (!empty($event_info) && $event_info['country_code'] == 'IN')) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
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

			if (empty($user_info = $this->student_model->get($this->input->post('user_id') ?? 0))) {
				return $this->json['error'] = _li('invalid_user');
			}

			if (!empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
				return;
			}
		}

		if (!$this->json) {
			self::_executeOtp(
				$this->input->post('type') == 'mobile',
				false,
				$this->input->post('type') == 'whatsapp',
			);

			$this->json['lead_id'] = self::_addLead();
		}
	}

	public function enrolUserInEvent() {
		$this->form_validation->set_rules('user_id', _l('user_id'), 'trim|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|min_length[8]|max_length[255]');
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
		!empty($this->input->post('city_id')) && $this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
		]);
		$this->form_validation->set_rules('grade_id', _l('grade'), [
			'trim',
			'required',
			'numeric'
		]);
		$this->form_validation->set_rules('section_id', _l('section'), [
			'trim',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (self::_verifyOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				if (empty($student_info = $this->student_model->get($this->input->post('user_id') ?? 0))) {
					return $this->json['error'] = _li('invalid_user');
				}

				if (empty($lead_info = $this->lead_model->get($this->input->post('lead_id') ?? 0))) {
					return $this->json['error'] = _li('invalid_lead');
				}

				if (!empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $student_info['id']))) {
					$this->json['error'] = _li('You_are_already_part_of_this_event');
					return;
				}

				$this->load->model('user/UserSource_model', 'user_source_model');

				if (!empty($lead_info['name'])) {
					$explode 	= explode(' ', ($lead_info['name'] ?? ''), 2);
					$first_name = array_shift($explode);
					$last_name 	= array_shift($explode);
				} else {
					$first_name = $student_info['first_name'];
					$last_name 	= $student_info['last_name'];
				}

				$edit_data = [
					'state_id'				=> (int)$lead_info['state_id'],
					'city_id'				=> (int)$lead_info['city_id'],
					'site_id'				=> (int)$lead_info['site_id'],
					'grade_id'				=> $lead_info['grade_id'] ?? 0,
					'section_id'			=> $lead_info['section_id'] ?? '',
					'grade'					=> $lead_info['grade_id'] ?? 0,
					'section'				=> $lead_info['section_id'] ?? '',
					'first_name'			=> $first_name,
					'last_name'				=> $last_name,
				];

				$this->student_model->edit($student_info['id'], $edit_data);

				if (in_array($lead_info['type'], ['mobile', 'whatsapp'])) {
					$this->lead_model->edit($lead_info['id'], [
						'student_id'		=> (int)$student_info['id'],
						'mobile_verified'	=> 1,
					]);
				} else if ($lead_info['type'] == 'email') {
					$this->lead_model->edit($lead_info['id'], [
						'student_id'		=> (int)$student_info['id'],
						'email_verified'	=> 1,
					]);
				}

				if (!empty($this->user_source_model->get_all([
					'user_id' => $student_info['id']
				])['rows'][0] ?? '')) {
					$this->user_source_model->editByStudentId($student_info['id'], [
						'lead_id'			=> (int)$this->input->post('lead_id') ?? 0,
						'utm_source'		=> $lead_info['utm_source'] ?? '',
						'utm_medium'		=> $lead_info['utm_medium'] ?? '',
						'utm_campaign'		=> $lead_info['utm_campaign'] ?? '',
					]);
				} else {
					$this->user_source_model->add([
						'user_id'			=> (int)$student_info['id'],
						'lead_id'			=> (int)$this->input->post('lead_id') ?? 0,
						'utm_source'		=> $lead_info['utm_source'] ?? '',
						'utm_medium'		=> $lead_info['utm_medium'] ?? '',
						'utm_campaign'		=> $lead_info['utm_campaign'] ?? '',
					]);
				}

				if (
					empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $student_info['id']))
				) {
					$this->event_user_model->add([
						'event_id'	=> (int)$this->input->post('event_id'),
						'user_id'	=> (int)$student_info['id']
					]);

					$this->load->model('common/Cron_model', 'cron_model');

					$this->cron_model->add([
						'code'			=> 'eventUserSignup_' . $this->input->post('event_id') . '_' . $student_info['id'],
						'action'		=> 'alert_model->eventUserSignup',
						'data'			=> [['event_id' => (int)$this->input->post('event_id'), 'type' => 'user', 'lead_id' => ($this->input->post('lead_id') ?? 0)]],
						'site_id'		=> 1,
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);
				}

				if ($student_info['site_id'] != ($lead_info['site_id'] ?? 0)) {
					CI_Events::trigger('access_log', [
						'module'	=> 'event_user_school_jump' . (int)$student_info['id'] . '_old_site_'.(int)$student_info['site_id'] . "_new_site_".(int)$lead_info['site_id']
					]);
				}

				self::_formatUser($student_info['id']);
				self::_addToken($this->student_model->get($student_info['id']));

				$this->json['success'] = _l('profile_successfully_saved');
			} else {
				$this->json['error'] 	= _l('please_enter_the_correct_verification_code');
			}
		}
	}

	public function enrolUserInCategoryEvent() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json && !empty($user_info = $this->student_model->get($this->session->userdata('user_id') ?? 0))) {

			if (!empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))) {
				$this->json['error'] = _li('You_are_already_part_of_this_event');
				return;
			}

			$this->lead_model->add([
				'event_id'			=> (int)$this->input->post('event_id'),
				'site_id'			=> (int)$user_info['site_id'],
				'site_type'			=> 1,
				'student_id'		=> (int)$user_info['id'],
				'city_id'			=> (int)$user_info['city_id'],
				'state_id'			=> (int)$user_info['state_id'],
				'country_id'		=> (int)$user_info['country_id'],
				'name'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'parent_name'		=> '',
				'grade_id'			=> $user_info['grade_id'] ?? 0,
				'section_id'		=> $user_info['section_id'] ?? '',
				'grade'				=> $user_info['grade'] ?? 0,
				'section'			=> $user_info['section'] ?? '',
				'mobile'			=> $user_info['mobile'] ?? '',
				'email'				=> $user_info['email'] ?? '',
				'mobile_verified'	=> $user_info['mobile_verified'],
				'email_verified'	=> $user_info['email_verified'],
				'location'			=> $user_info['location'],
				'ip'				=> $this->input->ip_address(),
				'timezone'			=> $this->input->post('timezone') ?? '',
				'source'			=> $this->input->post('source') ?? '',
				'utm_source'		=> $this->input->post('utm_source') ?? '',
				'utm_medium'		=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'		=> $this->input->post('utm_campaign') ?? '',
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'enrol_user_in_sdg_' . (int)$user_info['id']."_".(int)$this->input->post('event_id')
			]);

			if (
				empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))
			) {
				$this->event_user_model->add([
					'event_id'	=> (int)$this->input->post('event_id'),
					'user_id'	=> (int)$user_info['id']
				]);

				$this->load->model('common/Cron_model', 'cron_model');

				$this->cron_model->add([
					'code'			=> 'categoryEventSignupCron_' . $this->session->userdata('user_id'),
					'action'		=> 'alert_model->categoryEventSignupCron',
					'data'			=> [$this->session->userdata('user_id')],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);
			}

			$this->json['success'] = _l('you_are_enroled_in_event_successfully!');
		}
	}

	public function getSiteByEvent() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($event_site_info = $this->event_site_model->getDataByEventId($this->input->post('event_id')))) {
				$this->json['schools'] 	= $event_site_info;
			}
		}
	}

	public function enrolUserBookInEvent() {
		$this->form_validation->set_rules('user_id', _l('user_id'), [
			'trim',
			'required',
			'numeric'
		]);
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('book_id', _l('book'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('state_id', _l('state'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		!empty($this->input->post('city_id')) && $this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('grade_id', _l('grade'), [
			'trim',
			'required',
			'numeric'
		]);
		$this->form_validation->set_rules('section_id', _l('section'), [
			'trim',
			'required',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_code_info = $this->event_user_invite_code_model->get_all([
				'event_id'	  => $this->input->post('event_id'),
				'user_id'	  => $this->input->post('user_id'),
				'code'		  => $this->input->post('code'),
			])['rows'])) {
				return $this->json['error'] = _li('invalid_code');
			}

			if (empty($student_info = $this->student_model->get($this->input->post('user_id')))) {
				return $this->json['error'] = _li('invalid_student');
			}

			if (empty($book_info = $this->book_model->get($this->input->post('book_id'))) || ($book_info['user_id'] != $student_info['id'])) {
				return $this->json['error'] = _li('invalid_book');
			}

			if (
				empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $student_info['id']))
			) {
				$this->event_user_model->add([
					'event_id'	=> (int)$this->input->post('event_id'),
					'user_id'	=> (int)$student_info['id']
				]);
			}

			if (empty($this->event_book_model->get_all([
				'book_id'	  => $book_info['id'],
			])['rows'])) {
				$this->event_book_model->add([
					'event_id'	=> (int)$this->input->post('event_id'),
					'book_id'	  => (int)$book_info['id'],
				]);

				if (!empty($products = $this->order_product_model->get_all([
					'product_id'	 => $book_info['id']
				])['rows'] ?? [])) {

					$order_ids = [];

					foreach ($products as $product) {
						$order_info = $this->order_model->get($product['order_id']);

						if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {
							if (empty($this->event_order_model->get_all([
								'event_id'		=> $this->input->post('event_id'),
								'book_id'		=> $book_info['id'],
								'order_id'		=> $order_info['id']
							])['rows'][0] ?? '')) {
								$event_order_id = $this->event_order_model->add([
									'event_id'		=> $this->input->post('event_id'),
									'order_id'		=> $order_info['id'],
									'book_id'		=> $book_info['id'],
									'quantity'		=> $product['quantity']
								]);

								$this->event_order_model->edit($event_order_id, [
									'date_added'	=> $order_info['date_added']
								]);
							}

							$order_ids[] = $order_info['id'];
						}
					}

					if (!empty($order_ids)) {
						$this->load->library('GenericCertificate_lib');
						$this->load->library('Ranking_lib', 'ranking_lib');

						rsort($order_ids);

						$this->ranking_lib->updateRank($order_ids[0]);
						$this->genericcertificate_lib->createCertificate($order_ids[0], false);

						if (!empty($certficates = $this->certificate_model->get_all([
							'event_id'	 => 0,
							'book_id'	 => $book_info['id']
						])['rows'] ?? [])) {

							$this->db->where_in('id', array_column($certficates, 'id'));
							$this->db->update('certificates',  [
								'_deleted'		=> 1,
								'date_deleted'	=> date('Y-m-d H:i:s'),
							]);
						}
					}
				}
			}

			$this->student_model->edit($student_info['id'], [
				'site_id' 			=> $this->input->post('site_id'),
				'state_id' 			=> $this->input->post('state_id'),
				'city_id' 			=> $this->input->post('city_id'),
				'grade' 			=> $this->input->post('grade_id'),
				'section' 			=> $this->input->post('section_id'),
				'grade_id' 			=> $this->input->post('grade_id'),
				'section_id' 		=> $this->input->post('section_id'),
			]);

			$event_info = $this->event_model->get($this->input->post('event_id'));

			$this->json['success'] = sprintf(_li('Congratulations! Your book has been successfully enrolled in %s!'), ($event_info['label'] ?? 'event'));
		}
	}
}
