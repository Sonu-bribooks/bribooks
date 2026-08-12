<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolEarlyAccess {
	public function sendSchoolOtpForEarlyAccess() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('school_name', _l('school_name'), 'trim|required|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);

		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
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

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate()) {
				return;
			}

			if (!empty($site_info = $this->site_model->getSiteByWhere(['owner_mobile' => $this->input->post('mobile')])) && ($site_info['id'] != $this->input->post('site_id')) && ($site_info['verified'] == 1)) {
				$this->json['error'] = 'Congratulations! Your school is already registered with BriBooks. Please review the details below and click "OK" to confirm registration for this event. Contact Person Name - '. $site_info['authorized_person'].", Mobile - ".masked_mobile($site_info['mobile']).", Email - ".masked_email($site_info['email']);
				return;
			}

			if (!empty($site_info_email = $this->site_model->getSiteByWhere(['owner_email' => $this->input->post('email')])) && ($site_info_email['id'] != $this->input->post('site_id')) && ($site_info['verified'] == 1)) {
				$this->json['error'] = 'Congratulations! Your school is already registered with BriBooks. Please review the details below and click "OK" to confirm registration for this event. Contact Person Name - '. $site_info['authorized_person'].", Mobile - ".masked_mobile($site_info['mobile']).", Email - ".masked_email($site_info['email']);
				return;
			}

			if (!empty($this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id')))) {
				$site_info = $this->site_model->getSchoolBySiteId($this->input->post('site_id'));
				$this->json['error'] = 'Congratulations! Your school is already registered with BriBooks. Please review the details below and click "OK" to confirm registration for this event. Contact Person Name - '. $site_info['authorized_person'].", Mobile - ".masked_mobile($site_info['mobile']).", Email - ".masked_email($site_info['email']);
				return;
			}

			if (!empty($site_info = $this->site_model->getSchoolBySiteId($this->input->post('site_id'))) && ($site_info['verified'] == 1)) {
				$this->json['error'] = 'Congratulations! Your school is already registered with BriBooks. Please review the details below and click "OK" to confirm registration for this event. Contact Person Name - '. $site_info['authorized_person'].", Mobile - ".masked_mobile($site_info['mobile']).", Email - ".masked_email($site_info['email']);
				return;
			}
		}

		if (!$this->json) {
			$site_info_data = $this->site_model->get($this->input->post('site_id'));

			if (!empty($site_info_data) && ($site_info_data['owner_mobile']) != $this->input->post('mobile')) {
				$result = $this->school_lead_model->add([
					'event_id'					=> (int)$this->input->post('event_id'),
					'site_id'					=> $this->input->post('site_id'),
					'school_id'					=> 0,
					'name'						=> $this->input->post('school_name'),
					'country'					=> $this->input->post('country')
						? $this->input->post('country')
						: $this->config->item('site_country'),
					'state_id'					=> (int)$this->input->post('state_id'),
					'city_id'					=> (int)$this->input->post('city_id'),
					'school_head'				=> $this->input->post('school_head') ?? '',
					'authorized_person'			=> $this->input->post('authorized_person'),
					'designation'				=> $this->input->post('designation'),
					'email'						=> $this->input->post('email'),
					'mobile'					=> $this->input->post('mobile'),
					'leaflets'					=> 0,
					'sections'					=> (int)$this->input->post('section'),
					'grades'					=> json_encode(
						is_array($this->input->post('grade')) ? $this->input->post('grade') : []
					),
					'utm_source'				=> $this->input->post('utm_source') ?? '',
					'utm_medium'				=> $this->input->post('utm_medium') ?? '',
					'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
					'site_type'					=> $this->input->post('institute_type') ?? 1,
				]);

				$this->json['lead_id'] = $result;

				self::_executeOtp($this->input->post('type') == 'mobile');
			}

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks Young Authors Fair. Someone from BriBooks team will get in touch with you shortly.');
		}
	}

	public function updateSchool() {
		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} elseif ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|numeric|exact_length[6]');

		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id')))) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			$site_info = $this->site_model->get($this->input->post('site_id'));

			if (!empty($this->input->post('otp'))) {
				if (self::_verifyOtp($this->input->post('type') == 'mobile')) {
					$this->db->select('*');
					$this->db->where('email', $site_info['owner_email']);
					$this->db->where('role_id', 9);
					$user_info =  $this->db->get('users')->row_array();

					if (!empty($user_info)) {
						$this->db->update('users', [
							'email' 			=> $this->input->post('email'),
							'mobile' 			=> $this->input->post('mobile'),
							'mobile_verified'	=> 1,
							'email_verified'	=> 1
						], [
							'id' => $user_info['id']
						]);
					} else {
						$this->site_model->addSchoolUser($this->input->post('site_id'), [
							'name'				=> $site_info['name'],
							'owner_password'	=> sha1($site_info['owner_name']."".rand()),
							'owner_email'		=> $this->input->post('email'),
							'owner_mobile'		=> $this->input->post('mobile'),
							'status'			=> (int)$site_info['status'],
							'state_id'			=> $this->input->post('state_id'),
							'city_id'			=> $this->input->post('city_id'),
							'mobile_verified'	=> 1,
							'email_verified'	=> 1
						]);

						$lead_info  = $this->school_lead_model->getSchoolLeadByWhere([ 'site_id' => $this->input->post('site_id')]);

						if (empty($lead_info)) {
							$this->school_lead_model->add([
								'event_id'					=> (int)$this->input->post('event_id'),
								'site_id'					=> $site_info['id'],
								'school_id'					=> 0,
								'name'						=> $site_info['name'],
								'country'					=> $this->input->post('country')
									? $this->input->post('country')
									: $this->config->item('site_country'),
								'state_id'					=> (int)$this->input->post('state_id'),
								'city_id'					=> (int)$this->input->post('city_id'),
								'school_head'				=> $this->input->post('school_head') ?? '',
								'authorized_person'			=> $this->input->post('authorized_person'),
								'designation'				=> $this->input->post('designation') ?? '',
								'email'						=> $this->input->post('email') ?? '',
								'mobile'					=> $this->input->post('mobile') ?? '',
								'sections'					=> $this->input->post('sections') ?? '',
								'grades'					=> $this->input->post('grades') ?? '',
								'utm_source'				=> $this->input->post('utm_source') ?? '',
								'utm_medium'				=> $this->input->post('utm_medium') ?? '',
								'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
								'site_type'					=> $this->input->post('institute_type') ?? 1,
							]);
						}
					}

					self::updateSite();

					CI_Events::trigger('event_school_signup', [
						'event_id' 	=> (int)$this->input->post('event_id'),
						'site_id' 	=> (int)$this->input->post('site_id')
					]);

					CI_Events::trigger('access_log', [
						'module'	=> 'event_school_signup_' . (int)$this->input->post('site_id')
					]);

					$this->json['success'] 	= _l('verified');
					$this->json['school_name'] 	= $site_info['name'];
				} else {
					$this->json['error'] 	= _l('enter_valid_verification_code');
				}
			}else{
				self::updateSite();

				$this->json['success'] 	= _l('verified');
				$this->json['school_name'] 	= $site_info['name'];
			}

		}
	}

	public function updateSite() {
		$this->db->update('site', [
			'owner_mobile'		=> $this->input->post('mobile'),
			'owner_email'		=> $this->input->post('email'),
			'state_id'			=> (int)$this->input->post('state_id'),
			'city_id'			=> (int)$this->input->post('city_id'),
			'name'				=> $this->input->post('school_name'),
			'authorized_person'	=> $this->input->post('authorized_person'),
			'owner_name'		=> $this->input->post('school_head'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'verified'			=> 1
		], [
			'id'			=> (int)$this->input->post('site_id')
		]);

		if (empty($this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id')))) {
			return $this->event_site_model->add([
				'event_id'	=> (int)$this->input->post('event_id'),
				'site_id'	=> (int)$this->input->post('site_id')
			]);
		}
	}

	public function getSchoolsByEvent() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		if ($this->input->post('site_type') != 4) {
			$this->form_validation->set_rules('state_id', _l('state_id'), [
				'trim',
				'required',
				'numeric'
			]);

			$this->form_validation->set_rules('city_id', _l('city_id'), [
				'trim',
				'required',
				'numeric'
			]);
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($this->input->post('state_id'))) {
				$filter_data['state_id'] =  $this->input->post('state_id');
			}

			if (!empty($this->input->post('city_id'))) {
				$filter_data['city_id'] =  $this->input->post('city_id');
			}

			if (!empty($this->input->post('referral_id'))) {
				$filter_data['verified'] = 1;
			}

			if (!empty($this->input->post('verified'))) {
				$filter_data['verified'] =  (int)$this->input->post('verified');
			}

			if (!empty($this->input->post('site_type'))) {
				$filter_data['site_type'] = $this->input->post('site_type');
			} else {
				$filter_data['site_type'] = 1;
			}

			$filter_data['sort'] 	= 'site.name';
			$filter_data['order'] 	= 'ASC';

			$school_info 		= [];
			$direct_school_info = [];

			if (!empty($event_site_info = $this->event_site_model->getDataByEventId($this->input->post('event_id'), $filter_data))) {
				$school_info 	= $event_site_info;
			}

			$event_info = $this->event_model->get($this->input->post('event_id'));

			if ($this->input->post('type') != 'school') {
				if (!empty($event_info['direct_site_id'])) {
					$site_info = $this->site_model->get($event_info['direct_site_id']);

					$direct_school_info = [
						'site_id' 		=> $site_info['id'] ?? '',
						'event_id'	 	=> $event_info['id'],
						'name' 			=> $site_info['name'] ?? '',
						'site_code' 	=> $site_info['site_code'] ?? '',
						'state_id' 		=> $site_info['state_id'] ?? '',
						'city_id' 		=> $site_info['city_id'] ?? '',
					];
				}
			}

			if (!empty($this->input->post('referral_id'))) {
				$this->json['schools'][] = $direct_school_info;

				if (!empty($school_info)) {
					$this->json['schools'] = $this->json['schools'] + $school_info;
				}
			} elseif (($this->input->post('type') == 'school') || ($this->input->post('site_type') == 4)) {
				$this->json['schools'] = $school_info;
			} else {
				$this->json['schools'][] = $direct_school_info;

				if (!empty($school_info)) {
					$this->json['schools'] = $this->json['schools'] + $school_info;
				}
			}
		}
	}

	public function enrolUserEvent() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);

		self::_runFormValidation();

		if (!$this->json) {
			return $this->json;

			$this->db->select('*');
			$this->db->where('role_id', 2);
			$this->db->where('(users.mobile = "' . $this->input->post('mobile') . '" OR users.email = "' . $this->input->post('email') . '")');
			$user_info = $this->db->get('users')->row_array();

			if ($user_info) {
				$site_info = $this->site_model->get($this->input->post('site_id'));
				$user_events = $this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']);

				if (empty($user_events)) {
					$this->event_user_model->add([
						'event_id' => $this->input->post('event_id'),
						'user_id'  => $user_info['id']
					]);
				}

				if (!empty($site_info)) {
					$this->student_model->edit($user_info['id'], [
						'site_id' 	=> $site_info['id'],
						'state_id' 	=> $site_info['state_id'],
						'city_id' 	=> $site_info['city_id']
					]);

					CI_Events::trigger('event_student_signup', [
						'event_id' 	=> $this->input->post('event_id'),
						'site_id' 	=>$this->input->post('site_id')
					]);
					CI_Events::trigger('access_log', [
						'module'	=> 'event_student_signup_ok_' . $user_info['id']
					]);

					$this->load->model('common/Cron_model', 'cron_model');
					$this->cron_model->add([
						'code'			=> 'enrolUserEventCron_' . $this->input->post('event_id') . '_' . $user_info['id'],
						'action'		=> 'alert_model->enrolUserEventCron',
						'data'			=> [$this->input->post('event_id'), $user_info['id']],
						'site_id'		=> $this->session->userdata('site_id') ?? 1,
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);
				}
			} else {
				$this->json['error'] 	= _l('student_not_found');
			}
		}
	}

	public function enrolSchoolEvent() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$site_events = $this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id'));

			$this->db->update('site', [
				'verified'	=> 1
			], [
				'id'			=> $this->input->post('site_id')
			]);

			if (empty($site_events)) {
				CI_Events::trigger('event_school_signup', [
					'event_id' 	=> (int)$this->input->post('event_id'),
					'site_id' 	=> (int)$this->input->post('site_id')
				]);

				CI_Events::trigger('access_log', [
					'module'	=> 'event_school_signup_ok_' . (int)$this->input->post('site_id')
				]);

				$this->event_site_model->add([
					'event_id' => $this->input->post('event_id'),
					'site_id'  => $this->input->post('site_id')
				]);

				$this->json['success'] 	= _l('success');
			} else {
				$this->json['error'] 	= _l('school_is_already_registered');
			}
		}
	}

	public function verifyGeneralSchoolOtp() {
		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} elseif ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|numeric|exact_length[6]');

		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id')))) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			$site_info = $this->site_model->get($this->input->post('site_id'));

			if (!empty($this->input->post('otp'))) {
				if (self::_verifyOtp($this->input->post('type') == 'mobile')) {
					$this->db->select('*');
					$this->db->where('email', $site_info['owner_email']);
					$this->db->where('role_id', 9);
					$user_info =  $this->db->get('users')->row_array();

					if (!empty($user_info)){
						$this->db->update('users', [
							'email' => $this->input->post('email'),
							'mobile' => $this->input->post('mobile'),
							'mobile_verified'	=> 1,
							'email_verified'	=> 1
						], [
							'id' => $user_info['id']
						]);
					} else {
						$this->site_model->addSchoolUser($this->input->post('site_id'), [
							'name'				=> $site_info['name'],
							'owner_password'	=> sha1($site_info['owner_name']."".rand()),
							'owner_email'		=> $this->input->post('email'),
							'owner_mobile'		=> $this->input->post('mobile'),
							'status'			=> (int)$site_info['status'],
							'state_id'			=> $this->input->post('state_id'),
							'city_id'			=> $this->input->post('city_id'),
							'mobile_verified'	=> 1,
							'email_verified'	=> 1
						]);
						$lead_info  = $this->school_lead_model->getSchoolLeadByWhere([ 'site_id' => $this->input->post('site_id')]);

						if(empty($lead_info)){

							$this->school_lead_model->add([
								'event_id'					=> (int)$this->input->post('event_id'),
								'site_id'					=> $site_info['id'],
								'school_id'					=> 0,
								'name'						=> $site_info['name'],
								'country'					=> $this->input->post('country')
									? $this->input->post('country')
									: $this->config->item('site_country'),
								'state_id'					=> (int)$this->input->post('state_id'),
								'city_id'					=> (int)$this->input->post('city_id'),
								'school_head'				=> $this->input->post('school_head') ?? '',
								'authorized_person'			=> $this->input->post('authorized_person'),
								'designation'				=> $this->input->post('designation') ?? '',
								'email'						=> $this->input->post('email') ?? '',
								'mobile'					=> $this->input->post('mobile') ?? '',
								'sections'					=> $this->input->post('sections') ?? '',
								'grades'					=> $this->input->post('grades') ?? '',
								'utm_source'				=> $this->input->post('utm_source') ?? '',
								'utm_medium'				=> $this->input->post('utm_medium') ?? '',
								'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
								'site_type'					=> $this->input->post('institute_type') ?? 1,
							]);
						}
					}

					self::updateSite();

					$this->json['success'] 	= _l('verified');
					$this->json['school_name'] 	= $site_info['name'];
				} else {
					$this->json['error'] 	= _l('enter_valid_verification_code');
				}
			} else {
				self::updateSite();

				$this->json['success'] 	= _l('verified');
				$this->json['school_name'] 	= $site_info['name'];
			}

			CI_Events::trigger('event_school_signup', [
				'event_id' 	=> (int)$this->input->post('event_id'),
				'site_id' 	=> (int)$this->input->post('site_id')
			]);
		}
	}
}
