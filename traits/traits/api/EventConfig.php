<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventConfig {
	private $_signup_fields = [
		'id' 		=> ['site', 'city', 'state'],
		'option'	=> ['grade', 'section', 'designation', 'validation', 'parent_name', 'parent_email'],
		'number'	=> ['site', 'city', 'state', 'grade'],
		'string'	=> ['first_name', 'last_name', 'name', 'designation', 'school_head', 'authorized_person', 'validation', 'email'],
		'char'		=> ['section'],
		'email'		=> ['email'],
		'mobile'	=> ['mobile'],
	];

	public function eventLandingPage() {
		if (!($this->json)) {
			$data = $this->input->post();

			if (!empty($event_info = $this->event_model->get_all([
				'slug' => $data['slug'] ?? ''
			])['rows'][0] ?? [])) {
				$cache_key = vsprintf('%s_%s', [
					(ENVIRONMENT === 'production' ? 'live' : 'test'),
					sprintf('event_landing_page_data_%s', $event_info['id']),
				]);

				$data = json_decode($this->cache->get($cache_key), true);

				if (!empty($data)) {
					return $this->json = $data;
				}

				$this->load->model('event/EventDeadlineExtension_model', 'event_deadline_extension_model');

				if (!empty($deadline_info = $this->event_deadline_extension_model->get_all([
					'event_id'	=> (int)$event_info['id'],
					'type'		=> 'user',
					'start'		=> 0,
					'limit'		=> 1,
				])['rows'][0] ?? [])) {
					$event_info['deadline_extension_end_date'] = $deadline_info['end_date'];
				}

				$landing_page_info = $this->event_landing_page_model->get_all([
					'event_id' => $event_info['id']
				])['rows'][0] ?? [];

				$config_info = $this->event_config_model->get_all([
					'event_id' => $event_info['id']
				])['rows'][0] ?? [];

				$partner_ids 	= explode(',', $config_info['partners'] ?? '');
				$partners 		= [];

				foreach ($partner_ids as $partner_id) {
					if (empty($partner_info = $this->event_partner_model->get($partner_id))) continue;

					$partners[] = [
						'id' 	=> $partner_info['id'],
						'name' 	=> $partner_info['name'],
						'logo'	=> vsprintf('%spublic/EventGallery/%s', [
							$this->config->item('cloudfront_url'),
							$partner_info['image']
						]),
					];
				}

				$award_types 	= json_decode($config_info['awards'] ?? '', true) ?? [];
				$user_grades 	= json_decode($config_info['grades'] ?? '', true) ?? [];
				$award_groups 	= [];

				foreach ($award_types as $type => $result) {
					foreach ($result as $item) {
						if (empty($item['awards'])) continue;

						$awards = [];

						foreach ($item['awards'] as $award_id) {
							$award_group_info = $this->event_award_group_model->get($award_id);

							$gifts = [];

							foreach (explode(',', $award_group_info['event_award_ids'] ?? '') as $award_group) {
								if (empty($award_info = $this->event_award_model->get($award_group))) continue;

								$gifts[] = [
									'id'	=> $award_info['id'],
									'name' 	=> $award_info['name'],
									'thumb' => vsprintf('%spublic/EventGallery/%s', [
										$this->config->item('cloudfront_url'),
										$award_info['image']
									]),
								];
							}

							$awards[] = [
								'reference_id'	=> $award_group_info['reference_id'] ?? '',
								'id'			=> $award_group_info['id'],
								'name' 			=> $award_group_info['name'],
								'thumb' 		=> vsprintf('%spublic/EventGallery/%s', [
									$this->config->item('cloudfront_url'),
									$award_group_info['image']
								]),
								'gifts' => $gifts
							];
						}

						$award_groups[$type][] = [
							'group'		=> $item['name'],
							'title' 	=> $item['title'] ?? '',
							'awards'	=> $awards,
						];
					}
				}

				$signup_form_result = $this->event_signup_form_model->get_all([
					'event_id'	=> $event_info['id']
				])['rows'][0] ?? [];

				$signup_form['school_form'] 				= json_decode($signup_form_result['school_form'] ?? '', true);
				$signup_form['community_school_form'] 		= json_decode($signup_form_result['community_school_form'] ?? '', true);
				$signup_form['teacher_form'] 				= json_decode($signup_form_result['teacher_form'] ?? '', true);
				$signup_form['user_form'] 					= json_decode($signup_form_result['user_form'] ?? '', true);
				$signup_form['page_info'] 					= json_decode($signup_form_result['page_info'] ?? '', true);
				$signup_form['user_signup_landing_page']	= json_decode($signup_form_result['user_landing_page'] ?? '', true);
				$signup_form['school_signup_landing_page']	= json_decode($signup_form_result['school_landing_page'] ?? '', true);
				$country_otp_validation_info				= json_decode($signup_form_result['country_otp_validation'] ?? '', true);

				$country_otp_info = [];

				if (!empty($country_otp_validation_info) && !empty($country_otp_validation_info['country'])) {
					foreach ($country_otp_validation_info['country'] as $country_id) {
						if (empty($country_info = $this->country_model->get($country_id))) continue;

						$country_otp_info[] = [
							'id' 			=> $country_info['id'],
							'name' 			=> $country_info['name'],
							'code' 			=> $country_info['code'],
							'validation' 	=> $country_otp_validation_info['validation'] ?? ''
						];
					}
				}

				$signup_form['country_otp_validation'] = $country_otp_info;

				$user_partners = [];

				foreach (($signup_form['user_signup_landing_page']['partners'] ?? []) as $user_partner_id) {
					if (empty($user_partner_info = $this->event_partner_model->get($user_partner_id ?? 0))) continue;

					$user_partners[] = [
						'id' 	=> $user_partner_info['id'],
						'name' 	=> $user_partner_info['name'],
						'logo'	=> vsprintf('%spublic/EventGallery/%s', [
							$this->config->item('cloudfront_url'),
							$user_partner_info['image']
						]),
					];
				}

				$signup_form['user_signup_landing_page']['partners'] = $user_partners;

				$user_awards = [];

				foreach (($signup_form['user_signup_landing_page']['awards'] ?? []) as $user_award_id) {
					if (empty($award_group_info = $this->event_award_group_model->get($user_award_id ?? 0))) continue;

					foreach (explode(',', $award_group_info['event_award_ids']) as $award_group) {
						if (empty($award_info = $this->event_award_model->get($award_group))) continue;

						$user_awards[] = [
							'id'	=> $award_info['id'],
							'name' 	=> $award_info['name'],
							'logo' 	=> vsprintf('%spublic/EventGallery/%s', [
								$this->config->item('cloudfront_url'),
								$award_info['image']
							]),
						];
					}
				}

				$signup_form['user_signup_landing_page']['awards'] = $user_awards;

				foreach (($signup_form['school_signup_landing_page']['images'] ?? []) as $index => $image) {
					$signup_form['school_signup_landing_page']['images'][$index] = vsprintf('%spublic/EventGallery/%s', [
						$this->config->item('cloudfront_url'),
						$image
					]);
				}

				unset(
					$event_info['date_added'],
					$event_info['date_modified'],
					$event_info['_deleted'],
					$event_info['date_deleted'],
					$event_info['direct_site_id'],
				);

				$this->json['event']			= $event_info;
				$this->json['award_groups']		= $award_groups;
				$this->json['user_grades']		= $user_grades;
				$this->json['signup_form']		= $signup_form;
				$this->json['event_logo']		= sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), ($config_info['event_logo'] ?? ''));
				$this->json['event_logo_dark']	= sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), ($config_info['logo_dark'] ?? ''));
				$this->json['event_logo_light']	= sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), ($config_info['logo_light'] ?? ''));
				$this->json['landing_page']	 	= isset($landing_page_info['landing_page']) ? json_decode($landing_page_info['landing_page']) : [];
				$this->json['partners']		 	= $partners;
				$this->json['term']			 	= isset($landing_page_info['term']) ? json_decode($landing_page_info['term']) : [];
				$this->json['thank_you']		= isset($landing_page_info['thank_you']) ? json_decode($landing_page_info['thank_you']) : [];

				$this->cache->save($cache_key, json_encode($this->json), ENVIRONMENT === 'production' ? 3600 * 24 * 3 : 3600);
			}
		}
	}

	public function eventSendOtp() {
		self::_setExistingSchoolId();
		self::_setExistingUserId();
		self::_validateEventSignupForm();

		if (!empty($this->json['errors'])) {
			$this->json['error'] = implode('/n', $this->json['errors']);
			return;
		}

		if (!$this->json) {
			self::_checkCrossRoleUser();
			self::_checkRegisteredInEvent();
		}

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate()) {
				return;
			}

			$data 	= $this->input->post();

			$method = sprintf('_eventAdd%sLead', ucwords($data['user_type']));

			if (method_exists($this, $method)) {
				$this->json['lead_id'] = (int)self::{$method}($this->input->post());
			}

			if (!empty($this->json['lead_id'])) {
				if (in_array($data['validation'], ['mobile', 'email', 'whatsapp'])) {
					self::_executeOtp(
						$this->input->post('type') == 'mobile',
						false,
						$this->input->post('type') == 'whatsapp',
					);
				} else {
					self::_executeEmailLink(array_merge($this->input->post(), ['lead_id' => $this->json['lead_id']]));
				}
			}
		}
	}

	private function _eventAddSchoolLead($data = []) {
		if (!empty($data['site_id']) && !empty($event_info = $this->event_site_model->getEventIdBySiteId($data['event_id'], $data['site_id']))) {
			return $this->json['success'] = sprintf(_li('This_school_is_already_registered_in_the %s'), ($this->event_model->get($event_info['event_id'])['name'] ?? ''));
		}

		$country_info 	= self::getCountry(true);
		$country_name 	= $country_info['country'];
		$school_info	= $this->school_model->get($data['school']);

		return $this->school_lead_model->add([
			'event_id'			=> (int)($data['event_id'] ?? 0),
			'site_type'			=> (int)($data['site_type'] ?? 1),
			'site_id'			=> (int)($school_info['site_id'] ?? 0),
			'school_id'			=> (int)($school_info['id'] ?? 0),
			'name'				=> $school_info['name'] ?? '',
			'country'			=> $country_name,
			'country_id'		=> (int)($data['country_id'] ?? 0),
			'state_id'			=> (int)($data['state_id'] ?? 0),
			'city_id'			=> (int)($data['city_id'] ?? 0),
			'school_head'		=> $data['school_head'] ?? '',
			'authorized_person'	=> $data['authorized_person'] ?? '',
			'designation'		=> $data['designation'] ?? '',
			'email'				=> $data['email'] ?? '',
			'mobile'			=> $data['mobile'] ?? '',
			'leaflets'			=> ($data['leaflet'] ?? '') == 'Yes' ? (int)($data['leaflet_count'] ?? 0) : 0,
			'no_of_students'	=> (int)($data['student_count'] ?? 0),
			'ip'				=> $this->input->ip_address(),
			'timezone'			=> $data['timezone'] ?? '',
			'utm_source'		=> $data['utm_source'] ?? '',
			'utm_medium'		=> $data['utm_medium'] ?? '',
			'utm_campaign'		=> $data['utm_campaign'] ?? '',
			'type'				=> $data['type'] ?? 'mobile',
		]);
	}

	private function _eventAddTeacherLead($data = []) {
		$teacher_filter = [
			'site_id'	=> (int)$data['site'],
			'grade'		=> (int)$data['grade'],
			'section'	=> $data['section'] ?? '',
		];

		if (!empty($this->teacher_model->get_all($teacher_filter)['rows'][0])) {
			$signup_info = $this->event_signup_form_model->get_all([
				'event_id'	=> $data['event_id']
			])['rows'][0] ?? [];

			$signup_info['teacher_form'] = json_decode($signup_info['teacher_form'], true);

			if (empty($signup_info['teacher_form']['section'])) {
				$this->json['error'] = sprintf(_l('Teacher_is_already_assigned_to_grade_%s'), $data['grade']);
			} else {
				$this->json['error'] = sprintf(_l('Teacher_is_already_assigned_to_grade_%s_%s'), $data['grade'], $data['section']);
			}
			return;

		}

		$country_info 	= self::getCountry(true);
		$country_name 	= $country_info['country'];
		$site_info		= $this->site_model->get($data['site']);

		return $this->teacher_lead_model->add([
			'teacher_id'		=> (int)($data['teacher_id'] ?? 0),
			'event_id'			=> (int)($data['event_id'] ?? 0),
			'country_id'		=> (int)($site_info['country_id'] ?? 0),
			'state_id'			=> (int)($data['state_id'] ?? 0),
			'city_id'			=> (int)($data['city_id'] ?? 0),
			'site_id'			=> (int)($site_info['id'] ?? 0),
			'country_code'		=> $site_info['country_code'] ?? '',
			'name'				=> $data['name'],
			'designation'		=> $data['designation'],
			'email'				=> $data['email'] ?? '',
			'mobile'			=> $data['mobile'] ?? '',
			'timezone'			=> $data['timezone'],
			'location'			=> $country_name,
			'ip'				=> $this->input->ip_address(),
			'grades'			=> $data['grade'] ?? 1,
			'sections'			=> $data['section'] ?? 'A',
			'source'			=> $data['source'] ?? '',
			'utm_source'		=> $data['utm_source'] ?? '',
			'utm_medium'		=> $data['utm_medium'] ?? '',
			'utm_campaign'		=> $data['utm_campaign'] ?? '',
			'type'				=> $data['type'] ?? 'mobile',
		]);
	}

	private function _eventAddUserLead($data = []) {
		$country_info 	= self::getCountry(true);
		$country_name 	= $country_info['country'];
		$site_info		= $this->site_model->get($data['site']);

		return $this->lead_model->add([
			'event_id'			=> (int)($data['event_id'] ?? 0),
			'site_type'			=> (int)($data['site_type'] ?? 1),
			'name'				=> $data['first_name'] . ' ' . ($data['last_name'] ?? ''),
			'parent_name'		=> $data['parent_name'] ?? '',
			'parent_email'		=> $data['parent_email'] ?? '',
			'source'			=> $data['source'] ?? '',
			'mobile'			=> $data['mobile'] ?? '',
			'email'				=> $data['email'] ?? '',
			'dob'				=> !empty($data['dob']) ? DateTime::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : '',
			'grade'				=> $data['grade'] ?? 1,
			'section' 			=> !empty($data['section']) ? $data['section'] : 'A',
			'grade_id'			=> $data['grade_id'] ?? 1,
			'section_id' 		=> !empty($data['section_id']) ? $data['section_id'] : 'A',
			'city_id'			=> (int)($data['city_id'] ?? 0),
			'state_id'			=> (int)($data['state_id'] ?? 0),
			'country_id'		=> (int)($data['country_id'] ?? 0),
			'location'			=> $country_name,
			'mobile_verified'	=> 0,
			'site_id'			=> (int)($site_info['id'] ?? 0),
			'ip'				=> $this->input->ip_address(),
			'timezone'			=> $data['timezone'] ?? '',
			'utm_source'		=> $data['utm_source'] ?? '',
			'utm_medium'		=> $data['utm_medium'] ?? '',
			'utm_campaign'		=> $data['utm_campaign'] ?? '',
			'parent_referral_id'=> $data['referral_id'] ?? 0,
			'type'				=> $data['type'] ?? 'mobile',
		]);
	}

	private function _executeEmailLink($data = []) {
		if (!empty($data)) {
			$form_info = $this->event_signup_form_model->get_all([
				'event_id'	=> (int)$data['event_id']
			])['rows'][0] ?? [];

			$event_info = $this->event_model->get($data['event_id']);

			$email_link = json_decode($form_info['email_link'], true);

			if (empty($email_link['subject']) || empty($email_link['message'])) return;

			$otp = self::_executeOtp(false);

			$variables = [
				'url'	=> sprintf('%s/events/%s/signup/verify/%s?lid=%s&code=%s',
				$event_info['url'],
				($data['user_type'] == 'user') ? 'student' : $data['user_type'],
				$event_info['slug'],
				$data['lead_id'],
				$otp),
			];

			$subject = $this->alert_model->formatCommonEmailSubject($email_link['subject'], $variables) ?? '';

			$content = $this->alert_model->formatCommonEmailContent($email_link['message'], $variables) ?? '';

			$message = $this->load->view('common/mail/templates/site/general', [
				'title'		=> $subject,
				'content'	=> $content,
			], true);

			$email  = $data['email'];

			$this->alert_model->email(
				$email,
				$subject,
				$message,
				[],
				ENVIRONMENT === 'production'
					? ['communication@bribooks.com']
					: [],
				[]
			);
		}
	}

	public function eventVerifyOtp() {
		$data = $this->input->post();

		$this->form_validation->set_rules('validation', _l('validation'), 'trim|required|in_list[email,mobile,email_link]');
		$this->form_validation->set_rules('user_type', _l('user_type'), 'trim|required|in_list[school,teacher,user]');

		if (($data['validation'] ?? 'mobile') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'required'		=> _li('Please enter a valid mobile number'),
				'numeric'		=> _li('Please enter a valid mobile number'),
				'min_length'	=> _li('Please enter a valid mobile number'),
				'max_length'	=> _li('Please enter a valid mobile number'),
			]);
		}

		if (($data['validation'] ?? 'mobile') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		if (($data['validation'] ?? 'mobile') != 'email_link') {
			$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		}

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, sprintf('%sLead', $this->input->post('user_type'))]]
		]);

		self::_runFormValidation(true);

		if (!empty($this->json['errors'])) {
			$this->json['error'] = implode('/n', $this->json['errors']);
		}

		if (!$this->json) {
			if ($this->input->post('validation') == 'email_link') {
				self::_formatPostData($this->input->post());
			}

			if (!$this->json) {
				if (self::_verifyOtp(in_array(($this->input->post('type') ?? ''), ['mobile', 'whatsapp']))) {
					$method = sprintf('_eventEnrol%s', ucwords($data['user_type']));

					if (method_exists($this, $method)) {
						self::{$method}($this->input->post());
					}

					$this->json['success'] 	= sprintf(_l('%s_added_successfully'), $this->input->post('user_type'));
				} else {
					$this->json['error'] 	= _l('please_enter_the_correct_verification_code');
				}
			}

		}
	}

	private function _formatPostData($data = []) {
		if ($data['user_type'] == 'school') {
			$lead_info = $this->school_lead_model->get($data['lead_id']);
		} elseif ($data['user_type'] == 'teacher') {
			$lead_info = $this->teacher_lead_model->get($data['lead_id']);
		} else {
			$lead_info = $this->lead_model->get($data['lead_id']);
		}

		if (($lead_info['email_verified'] == 1) || ($lead_info['mobile_verified'] == 1)) {
			return $this->json['error'] = _li('You_are_already_registered_in_this_event');
		}

		$_POST['email'] 	= $lead_info['email'] ?? '';
		$_POST['mobile'] 	= $lead_info['mobile'] ?? '';
	}

	private function _eventEnrolSchool($data = []) {
		$lead_info = $this->school_lead_model->get($data['lead_id']);

		if (!empty($lead_info)) {
			$site_id = $school_id = 0;

			if (empty($lead_info['site_id'])) {
				$lead_info['site_id'] = $site_id = self::_addSiteDetails($lead_info);
			} else {
				$lead_info['site_id'] = $site_id = self::_updateSiteDetails($lead_info);
			}

			if (empty($lead_info['school_id'])) {
				$school_id = self::_addSchoolDeatils($lead_info);
			} else {
				$school_id = self::_updateSchoolDetails($lead_info);
			}

			if (!empty($site_id)) {
				self::_updateSchoolUser($lead_info);

				if (
					!empty($lead_info['event_id']) &&
					empty($this->event_site_model->getEventIdBySiteId($lead_info['event_id'], $site_id))
				) {
					$this->event_site_model->add([
						'event_id'	=> (int)$lead_info['event_id'],
						'site_id'	=> (int)$site_id,
					]);
				}
			}

			$this->school_lead_model->edit($lead_info['id'], [
				'site_id' 			=> $site_id,
				'school_id' 		=> $school_id,
				'verified' 		    => 1,
				'mobile_verified' 	=> (($lead_info['type'] == 'mobile') || ($lead_info['type'] == 'whatsapp')) ? 1 : 0,
				'email_verified' 	=> (($lead_info['type'] == 'email') || ($lead_info['type'] == 'email_link')) ? 1 : 0,
			]);

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('event_signup_school_%d_%d', (int)$site_id, $lead_info['event_id'])
			]);

			CI_Events::trigger('event_signup', [
				'type'		=> 'school',
				'lead_id'	=> $lead_info['id'],
				'event_id'	=> $lead_info['event_id'],
			]);
		}
	}

	private function _eventEnrolTeacher($data = []) {
		$lead_info = $this->teacher_lead_model->get($data['lead_id']);

		$lead_info['mobile_verified'] 	= (($lead_info['type'] == 'mobile') || ($lead_info['type'] == 'whatsapp')) ? 1 : 0;
		$lead_info['email_verified'] 	= (($lead_info['type'] == 'email') || ($lead_info['type'] == 'email_link')) ? 1 : 0;

		if (!empty($lead_info['teacher_id'])) {
			$teacher_id = self::_updateTeacher($lead_info);
		} else {
			$teacher_id = self::_addTeacher($lead_info);
		}

		if (!empty($teacher_id)) {
			if (
				!empty($lead_info['event_id']) &&
				empty($this->event_teacher_model->getEventUserByUserId($lead_info['event_id'], $teacher_id))
			) {
				$this->event_teacher_model->add([
					'event_id'		=> (int)$lead_info['event_id'],
					'teacher_id'	=> (int)$teacher_id,
				]);
			}
		}

		$this->teacher_lead_model->edit($lead_info['id'], [
			'teacher_id' 		=> $teacher_id,
			'mobile_verified' 	=> (($lead_info['type'] == 'mobile') || ($lead_info['type'] == 'whatsapp')) ? 1 : 0,
			'email_verified' 	=> (($lead_info['type'] == 'email') || ($lead_info['type'] == 'email_link')) ? 1 : 0,
		]);

		$this->json['success'] 		= _l('verified');
		$this->json['lead_id'] 		= $lead_info['id'];
		$this->json['teacher_id'] 	= $teacher_id;
		$this->json['name'] 		= $lead_info['name'];

		self::_formatTeacher($teacher_id);

		CI_Events::trigger('access_log', [
			'module'	=> sprintf('event_signup_teacher_%d_%d', (int)$teacher_id, $lead_info['event_id'])
		]);

		CI_Events::trigger('event_signup', [
			'type'		=> 'teacher',
			'lead_id'	=> $lead_info['id'],
			'event_id'	=> $lead_info['event_id'],
		]);
	}

	private function _eventEnrolUser($data = []) {
		$lead_info = $this->lead_model->get($data['lead_id']);

		$lead_info['source'] 	= $lead_info['utm_medium'] ?? '';

		if (in_array(($lead_info['type'] ?? 'mobile'), ['mobile', 'whatsapp'])) {
			$user_info = $this->user_model->get_all([
				'mobile' => $lead_info['mobile']
			])['rows'][0] ?? [];
		} else {
			$user_info = $this->user_model->get_all([
				'email' => $lead_info['email']
			])['rows'][0] ?? [];
		}

		$user_id = self::_doLogin($lead_info, false);

		if (!empty($user_info)) {
			$this->student_model->edit($user_info['id'], [
				'site_id' 			=> $lead_info['site_id'],
				'country_id' 		=> $lead_info['country_id'],
				'state_id' 			=> $lead_info['state_id'],
				'city_id' 			=> $lead_info['city_id'],
				'grade' 			=> $lead_info['grade'],
				'section' 			=> $lead_info['section'],
				'grade_id' 			=> $lead_info['grade'],
				'section_id' 		=> $lead_info['section'],
				'mobile' 			=> $lead_info['mobile'],
				'dob' 				=> $lead_info['dob'],
				'parent_name' 		=> $lead_info['parent_name'],
				'parent_email' 		=> $lead_info['parent_email'] ?? '',
				'email' 			=> $lead_info['email'],
				'parent_referral_id'=> (int)($lead_info['parent_referral_id'] ?? 0),
				'mobile_verified' 	=> ($user_info['mobile'] == $lead_info['mobile']) ? 1 : 0,
				'email_verified' 	=> ($user_info['email'] == $lead_info['email']) ? 1 : 0,
			]);
		}

		$this->lead_model->edit($lead_info['id'], [
			'student_id'		=> (int)$user_id,
			'mobile_verified' 	=> (($lead_info['type'] == 'mobile') || ($lead_info['type'] == 'whatsapp')) ? 1 : 0,
			'email_verified' 	=> (($lead_info['type'] == 'email') || ($lead_info['type'] == 'email_link')) ? 1 : 0,
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

		// add to referrals
		if (
			!empty($lead_info['parent_referral_id']) &&
			$user_id &&
			empty($this->user_referral_model->get_all([
				'event_id' 		=> (int)$lead_info['event_id'],
				'referrer_id' 	=> (int)$lead_info['parent_referral_id'],
				'referral_id' 	=> (int)$user_id,
			])['rows'] ?? [])
		) {
			$this->user_referral_model->add([
				'event_id' 		=> (int)$lead_info['event_id'],
				'referrer_id' 	=> (int)$lead_info['parent_referral_id'],
				'referral_id' 	=> (int)$user_id,
			]);
		}

		CI_Events::trigger('access_log', [
			'module'	=> sprintf('event_signup_user_%d_%d', (int)$user_id, $lead_info['event_id'])
		]);

		CI_Events::trigger('event_signup', [
			'type'		=> 'user',
			'lead_id'	=> $lead_info['id'],
			'event_id'	=> $lead_info['event_id'],
		]);

		self::_parentAcknowledgeMessage($lead_info['id']);
	}

	private function _validateEventSignupForm() {
		if ($this->input->method() === 'options') return;

		$data 		= $this->input->post();
		$user_type 	= $data['user_type'] ?? 'school';
		$event_id 	= $data['event_id'] ?? 0;

		if (empty($event_id)) {
			$this->json['error'] = _l('event_id_not_found');
			return;
		}

		$form_info = $this->event_signup_form_model->get_all([
			'event_id'	=> (int)$event_id
		])['rows'][0] ?? [];

		if (empty($form_info)) {
			$this->json['error'] = _l('error_unknown');
			return;
		}

		$forms 	= json_decode($form_info[$user_type . '_form'], true);

		if (empty($forms)) {
			$this->json['error'] = _l('signup_is_not_active');
			return;
		}

		log_kb(['Event::validate::form::' => [
			$data,
			$forms,
			$this->_signup_fields,
		]]);

		foreach ($forms as $field => $name) {
			if (empty($name)) continue;

			if (in_array($field, $this->_signup_fields['option'])) continue;

			if (empty($this->input->post('last_name'))) {
				$validation = ['trim'];
			} else {
				$validation = ['trim', 'required'];
			}

			$validation_error = [];

			if (in_array($field, $this->_signup_fields['number'])) {
				$validation[] = 'numeric';
			}

			if (in_array($field, $this->_signup_fields['string'])) {
				$validation[] = 'min_length[2]';
				$validation[] = 'max_length[128]';
			}

			if (in_array($field, $this->_signup_fields['char'])) {
				$validation[] = 'exact_length[1]';
			}

			if (in_array($field, $this->_signup_fields['email'])) {
				$validation[] = 'valid_email';
			}

			if (in_array($field, $this->_signup_fields['mobile'])) {
				$validation[] = 'min_length[10]';
				$validation[] = 'max_length[15]';
				$validation_error = [
					'required'		=> _li('Please enter a valid mobile number'),
					'numeric'		=> _li('Please enter a valid mobile number'),
					'min_length'	=> _li('Please enter a valid mobile number'),
					'max_length'	=> _li('Please enter a valid mobile number'),
				];
			}

			$function = $field;

			if (!empty($data['id']) && in_array($field, ['email', 'mobile'])) {
				$function = 'existing_' . $function;
			}

			$validation[] = [$field, [$this->validate_model, $function]];

			$this->form_validation->set_rules($field, $name, $validation, $validation_error);
		}

		$this->form_validation->set_rules('event', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('user_type', _l('user_type'), 'trim|required|in_list[school,teacher,user]');
		$this->form_validation->set_rules('source', _l('source'),  'trim|min_length[1]');

		self::_runFormValidation(true);
	}

	private function _setExistingSchoolId() {
		if ($this->input->method() === 'options') return;

		if (!empty($this->input->post('code')) && !empty($this->input->post('school_id'))) {
			$school_info 	= $this->school_model->get($this->input->post('school_id'));
			$site_id 		= $school_info['site_id'];
		} elseif (!empty($this->input->post('site_id'))) {
			$site_info 	= $this->site_model->get($this->input->post('site_id'));
			$site_id 	= $site_info['id'] ?? 0;
		} else {
			return;
		}

		if (
			!empty($site_id) &&
			(($this->input->post('user_type')?? '') == 'school') &&
			!empty($user_info = $this->user_model->get_all([
				'site_id' 	=> $site_id,
				'role_id' 	=> 9,
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0] ?? [])
		) {
			$_POST['id'] = $user_info['id'];
		}
	}

	private function _setExistingUserId() {
		if ($this->input->method() === 'options') return;
		if (empty($this->input->post('validation'))) return;

		$filter_data = [
			'start'	=> 0,
			'limit'	=> 1,
		];

		if ($this->input->post('validation') == 'mobile') {
			$filter_data['mobile'] 	= $this->input->post('mobile');
		} else {
			$filter_data['email'] 	= $this->input->post('email');
		}

		if (empty($this->input->post('id')) &&
			(($this->input->post('user_type')?? '') != 'school') &&
			!empty($user_info = $this->user_model->get_all($filter_data)['rows'][0] ?? [])
		) {
			$_POST['id'] = $user_info['id'];
		}
	}

	private function _checkCrossRoleUser() {
		if ($this->input->method() === 'options') return;

		if (
			$this->input->post('id') &&
			$this->input->post('user_type') === 'user' &&
			!empty($user_info = $this->user_model->get($this->input->post('id'))) &&
			$user_info['role_id'] != 2
		) {
			$this->json['error'] = _li('This mobile/email is already linked with a BriBooks account.');
			return;
		}

		if (
			$this->input->post('id') &&
			$this->input->post('user_type') === 'school' &&
			!empty($user_info = $this->user_model->get($this->input->post('id'))) &&
			$user_info['role_id'] != 9
		) {
			$this->json['error'] = _li('This mobile/email is already linked with a BriBooks account.');
			return;
		}

		if (
			$this->input->post('id') &&
			$this->input->post('user_type') === 'teacher' &&
			!empty($user_info = $this->user_model->get($this->input->post('id'))) &&
			$user_info['role_id'] != 3
		) {
			$this->json['error'] = _li('This mobile/email is already linked with a BriBooks account.');
			return;
		}
	}

	private function _checkRegisteredInEvent() {
		if ($this->input->method() === 'options') return;
		if (empty($this->input->post('id'))) return;

		$user_info 	= $this->user_model->get($this->input->post('id'));
		$user_type 	= $this->input->post('user_type');
		$exists		= false;

		if (in_array($user_type, ['teacher', 'user'])) {
			$exists = $this->{sprintf('event_%s_model', $user_type)}->getEventUserByUserId($this->input->post('event_id'), $user_info['id']);
		} else {
			$exists = $this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $user_info['site_id']);
		}

		if (!empty($exists)) {
			$this->json['error'] = _li('You are already enrolled in this event.');
		}
	}

	private function _parentAcknowledgeMessage($lead_id = 0) {
		if (empty($lead_info = $this->lead_model->get($lead_id))) return;

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');

		$communication_kit_info = $this->event_communication_kit_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0]['parent_acknowledge'] ?? '';

		if (empty($communication_kit_info)) return;

		$communication_kit_info = json_decode($communication_kit_info, true);

		$age = calculate_age($lead_info['dob']);

		$format_message = array_values(array_filter($communication_kit_info, function($item) use ($age) {
			return (!empty($item['email']['age']) && ($age <= $item['email']['age']));
		}));

		$kit_info = [];

		if (!empty($format_message[0])) {
			$kit_info = $format_message[0];
		}

		if (empty($kit_info)) return;
		if (empty($kit_info['email']['subject'] ?? '')) return;
		if (empty($kit_info['email']['message'] ?? '')) return;

		$this->load->model('common/Cron_model', 'cron_model');

		$this->cron_model->add([
			'code'			=> sprintf('eventParentAcknowledgeSignup_%s_%s', $lead_info['event_id'], $lead_info['student_id'] ?? 0),
			'action'		=> 'alert_model->eventParentAcknowledgeSignup',
			'data'			=> [['lead_id' => $lead_info['id'], 'event_id' => $lead_info['event_id'], 'student_id' => $lead_info['student_id']]],
			'site_id'		=>  1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);
	}

	public function verifyParentAcknowledge() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');

		self::_runFormValidation(true);

		if (!$this->json) {
			if(empty($lead_info = $this->lead_model->get($this->input->post('lead_id') ))) {
				return $this->json['error'] 	= _l('invalid_lead');
			}

			$this->db->update('cron', [
				'status'		=> 1,
				'_deleted'		=> 1,
				'date_deleted'	=> date('Y-m-d H:i:s'),
			],[
				'code' => sprintf('deactivateEventUser_%s_%s', $lead_info['event_id'], $lead_info['student_id'] ?? 0)
			]);

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('verify_parent_acknowledge_%d_%d', $lead_info['event_id'], $lead_info['student_id'] ?? 0)
			]);

			return $this->json['success'] 	= _l('verify_successfully!');
		}
	}
}
