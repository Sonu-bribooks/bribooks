<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolLead {
	public function createSchooLead() {
		$this->form_validation->set_rules('site_type', _l('site_type'), 'trim');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('school_name', _l('school_name'), 'trim|required|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid mobile number'),
			'max_length'	=> _li('Please enter a valid mobile number'),
		]);

		$this->form_validation->set_rules('school_head', _l('school_head'),  'trim|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('designation', _l('designation'),  'trim|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('section', _l('section'),  'trim|required|max_length[1]');
		$this->form_validation->set_rules('grade[]', _l('grade'),  'trim|required|numeric');
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

			$school_info = $this->school_model->get($this->input->post('school_id') ?? 0);

			if (!empty($school_info) && !empty($lead_info = $this->school_lead_model->get_all([
				'school_id'			 	=> $school_info['id'],
				'email_mobile_verified' => 1,
				'source'				=> $this->input->post('utm_source') ?? '',
			])['rows'][0] ?? '')) {
				$this->json['error'] = 'Your application form has already been submitted. If you have queries, please contact schools@bribooks.com ';
				return;
			}

			// if (!empty($email_info = $this->school_model->get_all([
			//	 'owner_email'   => $this->input->post('email'),
			//	 'not_school_id' => $school_info['id'] ?? ''
			// ])['rows'][0] ?? '')) {
			//	 $this->json['error'] = _li('This_Email_is_already_registered_with_BriBooks');
			//	 return;
			// }

			// if (!empty($alternate_email_info = $this->school_model->get_all([
			//	 'alternate_owner_email'	 => $this->input->post('email'),
			//	 'not_school_id'			 => $school_info['id'] ?? ''
			// ])['rows'][0] ?? '')) {
			//	 $this->json['error'] = _li('This_Email_is_already_registered_with_BriBooks');
			//	 return;
			// }

			// if (!empty($mobile_info = $this->school_model->get_all([
			//	 'owner_mobile'	  => $this->input->post('mobile'),
			//	 'not_school_id'	 => $school_info['id'] ?? ''
			// ])['rows'][0] ?? '')) {
			//	 $this->json['error'] = _li('This_Mobile_Number_is_already_registered_with_BriBooks');
			//	 return;
			// }

			// if (!empty($alternate_mobile_info = $this->school_model->get_all([
			//	 'alternate_owner_mobile'	=> $this->input->post('mobile'),
			//	 'not_school_id'			 => $school_info['id'] ?? ''
			// ])['rows'][0] ?? '')) {
			//	 $this->json['error'] = _li('This_Mobile_Number_is_already_registered_with_BriBooks');
			//	 return;
			// }

			if (!empty($lead_email_info = $this->school_lead_model->get_all([
				'email'				 => $this->input->post('email'),
				'email_mobile_verified' => 1,
				'source'				=> $this->input->post('utm_source') ?? '',
			])['rows'][0] ?? '')) {
				$this->json['error'] = _li('This_Email_is_already_registered_with_BriBooks');
				return;
			}

			if (!empty($lead_mobile_info = $this->school_lead_model->get_all([
				'mobile'				=> $this->input->post('mobile'),
				'email_mobile_verified' => 1,
				'source'				=> $this->input->post('utm_source') ?? '',
			])['rows'][0] ?? '')) {
				$this->json['error'] = _li('This_Mobile_is_already_registered_with_BriBooks');
				return;
			}

			if (!empty($user_email_info = $this->user_model->get_all([
				'email'				 => $this->input->post('email'),
				])['rows'][0] ?? '')) {
				$this->json['error'] = _li('This_Email_is_already_registered_with_BriBooks');
				return;
			}

			if (!empty($user_mobile_info = $this->user_model->get_all([
				'mobile'				=> $this->input->post('mobile'),
			])['rows'][0] ?? '')) {
				$this->json['error'] = _li('This_Mobile_is_already_registered_with_BriBooks');
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
				'state_id'					=> (int)$this->input->post('state_id'),
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
				'sections'					=> $this->input->post('section'),
				'grades'					=> json_encode(
					is_array($this->input->post('grade')) ? $this->input->post('grade') : []
				),
				'ip'						=> $this->input->ip_address(),
				'timezone'					=> $this->input->post('timezone'),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
				'site_type'					=> $this->input->post('site_type') ?? 1,
			]);

			$this->json['lead_id'] = $result;

			// true mobile otp and false email otp
			self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['success'] = sprintf(_li('Thank you for showing your interest in BriBooks. Someone from BriBooks team will get in touch with you shortly.'));
		}
	}
	public function verifySchoolLead() {
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'schoolLead']]
		]);


		self::_runFormValidation();

		if (!$this->json) {
			// true mobile and false email otp verify
			if (self::_verifyOtp($this->input->post('type') == 'mobile')) {
				$lead_info = $this->school_lead_model->get($this->input->post('lead_id'));

				if ($this->input->post('type') == 'mobile') {
					$this->school_lead_model->edit($lead_info['id'], [
						'mobile_verified'	=> 1,
					]);
				} elseif ($this->input->post('type') == 'email') {
					$this->school_lead_model->edit($lead_info['id'], [
						'email_verified'	=> 1,
					]);
				}

				$this->cron_model->add([
					'code'			=> 'sendSchoolLeadWelcomeMail_' . $lead_info['id'],
					'action'		=> 'alert_model->sendSchoolLeadWelcomeMail',
					'data'			=> [$lead_info['id']],
					'alert_date'	=> date('Y-m-d H:i:s'),
				]);

				$this->json['success'] 		= 'Thank you for your response. BriBooks team will get in touch with you shortly!';
				$this->json['lead_id'] 		= $lead_info['lead_id'];
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}
}
