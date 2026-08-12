<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait GlobalSchoolSignup {
	public function globalSendSchoolOtp() {
		$this->form_validation->set_rules('site_type', _l('site_type'), 'trim|in_list[1,2,3,4,5,6,7]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');

		empty($this->input->post('other_school')) &&  $this->form_validation->set_rules('school_name', _l('school_name'), 'trim|required|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid mobile number'),
			'max_length'	=> _li('Please enter a valid mobile number'),
		]);

		$this->form_validation->set_rules('school_head', _l('school_head'),  'trim|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('designation', _l('designation'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('state_id', _l('state_id'), [
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
		empty($this->input->post('other_school')) && $this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
		]);
		empty($this->input->post('other_school')) && $this->form_validation->set_rules('school_id', _l('school_id'), [
			'trim',
			'numeric',
		]);
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim|min_length[3]');

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			if (
				!empty($this->input->post('event_id')) &&
				!empty($this->input->post('site_id')) &&
				!empty($this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id')))
			) {
				$event_info = $this->event_model->get($this->input->post('event_id'));

				$this->json['error'] = _li('This_school_is_already_registered_in_the_') . $event_info['name'];
				return;
			}

			if (!empty($validate_mesaage = self::_validateGlobalSchoolLead($this->input->post()))) {
				$this->json['error'] = $validate_mesaage;
				return;
			}
		}

		if (!$this->json) {
			$result = $this->school_lead_model->add([
				'event_id'					=> (int)$this->input->post('event_id'),
				'site_id'					=> $this->input->post('site_id') ?? 0,
				'school_id'					=> $this->input->post('school_id') ?? 0,
				'name'						=> $this->input->post('school_name') ?? '',
				'country'					=> $this->input->post('country')
					? $this->input->post('country')
					: $this->config->item('site_country'),
				'state_id'					=> (int)$this->input->post('state_id') ?? 0,
				'city_id'					=> (int)$this->input->post('city_id') ?? 0,
				'school_head'				=> $this->input->post('school_head') ?? '',
				'authorized_person'			=> $this->input->post('authorized_person'),
				'designation'				=> $this->input->post('designation'),
				'email'						=> $this->input->post('email'),
				'mobile'					=> $this->input->post('mobile'),
				'leaflets'					=> $this->input->post('leaflet') == 'Yes'
					? (int)$this->input->post('leaflet_count')
					: 0,
				'no_of_students'			=> $this->input->post('student_count') ?? 0,
				'ip'						=> $this->input->ip_address(),
				'timezone'					=> $this->input->post('timezone'),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
				'site_type'					=> $this->input->post('site_type') ?? 1,
				'type'						=> $this->input->post('type') ?? '',
			]);

			$this->json['lead_id'] = $result;

			// true mobile otp and false email otp
			self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['success'] = sprintf(_li('Thank you for showing your interest in BriBooks. Someone from BriBooks team will get in touch with you shortly.'));
		}
	}

	public function globalVerifySchoolOtp() {
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'schoolLead']]
		]);
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim|min_length[3]');

		self::_runFormValidation();

		if (!$this->json) {
			// true mobile and false email otp verify
			if (!self::_verifyOtp($this->input->post('type') == 'mobile')) {
				return $this->json['error'] 	= _l('enter_valid_verification_code');
			}

			if (empty($lead_info = $this->school_lead_model->get($this->input->post('lead_id')))) {
				return $this->json['error'] 	= _l('invalid_url');
			}

			$site_id 	= 0;
			$school_id 	= 0;

			if (!empty($lead_info['site_id']) && !empty($lead_info['school_id'])) {
				$site_id 	= self::_updateGlobalSiteDetails($lead_info);
				$school_id 	= self::_updateGlobalSchoolDetails($lead_info);
			} elseif (empty($lead_info['site_id']) && !empty($lead_info['school_id'])) {
				$site_id = self::_addGlobalSiteDetails($lead_info);

				if (!empty($site_id)) {
					$lead_info['site_id'] = $site_id;
					$school_id = self::_updateGlobalSchoolDetails($lead_info);
				}
			} elseif (!empty($lead_info['site_id']) && empty($lead_info['school_id'])) {
				$site_id 	= self::_updateGlobalSiteDetails($lead_info);

				if (!empty($site_id)) {
					$lead_info['site_id'] = $site_id;
					$school_id 	= self::_addGlobalSchoolDeatils($lead_info);
				}
			} elseif (empty($lead_info['site_id']) && empty($lead_info['school_id'])) {
				$site_id = self::_addGlobalSiteDetails($lead_info);

				if (!empty($site_id)) {
					$lead_info['site_id'] = $site_id;
					$school_id = self::_addGlobalSchoolDeatils($lead_info);
				}
			}

			if (!empty($site_id)) {
				$lead_info['site_id'] = $site_id;
				self::_updateGlobalSchoolUser($lead_info);

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
				'verified' 			=> 1,
				'mobile_verified' 	=> $this->input->post('type') == 'mobile' ? 1 : 0,
				'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
			]);

			$this->json['success'] 		= _l('verified');
			$this->json['lead_id'] 		= $lead_info['lead_id'];
			$this->json['site_id'] 		= $lead_info['site_id'];
			$this->json['school_name'] 	= $lead_info['name'];

			$this->cron_model->add([
				'code'			=> 'eventSchoolSignupMail_' . $lead_info['event_id'] . '_' . $site_id,
				'action'		=> 'alert_model->eventSchoolSignupMail',
				'data'			=> [$site_id, $lead_info['event_id']],
				'site_id'		=> $site_id ?? 1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'school_event_auto_enrol_' . (int)$lead_info['school_id']
			]);
		}
	}
}
