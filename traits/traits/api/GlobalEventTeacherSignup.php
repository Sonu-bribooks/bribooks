<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait GlobalEventTeacherSignup {
	public function sendGlobalEventTeacherOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email]');
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]', [
			'min_length'	=> _li('The Class Teacher Name field must be atleast 3 characters in length'),
		]);

		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid mobile number'),
			'max_length'	=> _li('Please enter a valid mobile number'),
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


            self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['lead_id'] = $result;

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks');
		}
	}

	public function verifyGlobalEventTeacherOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile]');

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'teacherLead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
            if (!self::_verifyOtp($this->input->post('type') == 'mobile')) {
				return $this->json['error'] 	= _l('enter_valid_verification_code');
			}

			if (empty($lead_info = $this->teacher_lead_model->get($this->input->post('lead_id')))) {
				return $this->json['error'] 	= _l('invalid_url');
			}

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
		}
	}
}
