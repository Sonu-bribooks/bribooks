<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolSignup {
	public function schoolSignup() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('country', _l('country'), 'trim|required|min_length[2]|max_length[30]');

		self::_runFormValidation();

		if (!$this->json) {
			$result = $this->school_lead_model->add([
				'name'				=> $this->input->post('name'),
				'country'			=> $this->input->post('country') ?? '',
				'authorized_person'	=> $this->input->post('authorized_person'),
				'email'				=> $this->input->post('email'),
				'utm_source'		=> $this->input->post('utm_source') ?? '',
				'utm_medium'		=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'		=> $this->input->post('utm_campaign') ?? '',
			]);

			$this->alert_model->schoolLeadRegistration($result);
			$this->alert_model->schoolLeadShare($result);

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks Young Authors Fair. Someone from BriBooks team will get in touch with you shortly.');
		}
	}

	public function sendSchoolOtp() {
		// $this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		// $this->form_validation->set_rules('country', _l('country'), 'trim|required|min_length[2]|max_length[30]');
		$this->form_validation->set_rules('institute_type', _l('institute_type'), 'trim|in_list[1,2,3]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		empty($this->input->post('other_school')) &&  $this->form_validation->set_rules('school_name', _l('school_name'), 'trim|required|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);

		$this->form_validation->set_rules('school_head', _l('school_head'),  'trim|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('designation', _l('designation'),  'trim|required|min_length[3]|max_length[128]');
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
		empty($this->input->post('other_school')) && $this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim|min_length[3]');

		self::_runFormValidation();

		if (!$this->json && empty($this->input->post('grade'))) {
			$this->json['error'] = _li('Grades are required');
		}

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			$site_info = $this->site_model->get($this->input->post('site_id'));

			if ($site_info['verified'] == 1) {
				$this->json['error'] = _li('your_school_is_already_registered');
				return;
			}

			if (!empty($user_email_info = $this->db->get_where('users', [
				'email' => $this->input->post('email'),
			])->row_array())) {
				$this->json['error'] = _li('Your_Email_is_already_registered_with_BriBooks');
				return;
			}

			if (!empty($user_mobile_info = $this->db->get_where('users', [
				'mobile' => $this->input->post('mobile'),
			])->row_array())) {
				$this->json['error'] = _li('Your_Mobile_Number_is_already_registered_with_BriBooks');
				return;
			}
		}

		if (!$this->json) {
			$site_info 		= $this->site_model->get($this->input->post('site_id'));

			$result = $this->school_lead_model->add([
				'event_id'					=> (int)$this->input->post('event_id'),
				'site_id'					=> $site_info['id'] ?? $this->input->post('site_id'),
				'school_id'					=> $site_info['id'],
				'name'						=> $site_info['name'] ?? '',
				'country'					=> $this->input->post('country')
					? $this->input->post('country')
					: $this->config->item('site_country'),
				'state_id'					=> (int)$this->input->post('state_id'),
				'city_id'					=> (int)$this->input->post('city_id') ?? 0,
				'school_head'				=> $this->input->post('school_head') ?? $site_info['owner_name'],
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
				'site_type'					=> $this->input->post('institute_type') ?? 1,
			]);

			$this->json['lead_id'] = $result;

			// true mobile otp and false email otp
			self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['success'] = sprintf(_li('Thank you for showing your interest in BriBooks %s. Someone from BriBooks team will get in touch with you shortly.'), $event_info['name']);
		}
	}

	public function verifySchoolOtp() {
		if ($this->input->post('type') == 'mobile') {
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
			['lead', [$this->validate_model, 'schoolLead']]
		]);

		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim|min_length[3]');

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

				$lead_info = $this->school_lead_model->get($this->input->post('lead_id'));
				self::_updateSite($lead_info);

				$this->json['success'] 		= _l('verified');
				$this->json['lead_id'] 		= $lead_info['lead_id'];
				$this->json['site_id'] 		= $lead_info['site_id'];
				$this->json['school_name'] 	= $lead_info['name'];

				empty($lead_info['event_id']) && CI_Events::trigger('school_event_auto_enrol', [
					'site_id' 	=> (int)$lead_info['site_id'],
					'lead_id' 	=> (int)$lead_info['id'],
				]);

				!empty($lead_info['event_id']) && CI_Events::trigger('event_school_signup', [
					'event_id' 	=> (int)$lead_info['event_id'],
					'site_id' 	=> (int)$lead_info['site_id'],
					'lead_id' 	=> (int)$lead_info['id'],
				]);

				CI_Events::trigger('access_log', [
					'module'	=> 'school_event_auto_enrol_' . (int)$lead_info['site_id']
				]);
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}

	private function _updateSite($lead_info = []) {
		// update user account of particular site
		$school_user_info = $this->school_user_model->get_all([
			'site_id' => $lead_info['site_id']
		])['rows'][0] ?? [];

		if (empty($school_user_info)) {
			$school_user_id = $this->school_user_model->add([
				'first_name'		=> $lead_info['name'],
				'password'			=> sha1($lead_info['name'] . '' . rand()),
				'email'				=> $lead_info['email'],
				'mobile'			=> $lead_info['mobile'],
				'status'			=> 1,
				'role_id'			=> 9,
				'state_id'			=> (int)$lead_info['state_id'],
				'city_id'			=> (int)$lead_info['city_id'],
				'site_id'			=> (int)$lead_info['site_id'],
				'mobile_verified'	=> $lead_info['mobile_verified'],
				'email_verified'	=> $lead_info['email_verified'],
				'ip'				=> $lead_info['ip'],
				'timezone'			=> $lead_info['timezone'],
			]);
		} else {
			$this->school_user_model->edit($school_user_info['id'], [
				'first_name'		=> $lead_info['name'],
				'email' 			=> $lead_info['email'],
				'mobile' 			=> $lead_info['mobile'],
				'mobile_verified'	=> $lead_info['mobile_verified'],
				'email_verified'	=> $lead_info['email_verified'],
				'ip'				=> $lead_info['ip'],
				'timezone'			=> $lead_info['timezone'],
			]);

			$school_user_id = $school_user_info['id'] ?? 0;
		}

		$this->site_model->editById((int)$lead_info['site_id'], [
			'user_id'			=> $school_user_id,
			'owner_mobile'		=> $lead_info['mobile'],
			'owner_email'		=> $lead_info['email'],
			'state_id'			=> (int)$lead_info['state_id'],
			'city_id'			=> (int)$lead_info['city_id'],
			'name'				=> $lead_info['name'],
			'authorized_person'	=> $lead_info['authorized_person'],
			'owner_name'		=> $lead_info['school_head'],
			'verified'			=> 1,
			'date_verified'		=> date('Y-m-d H:i:s')
		]);

		$this->school_model->editBySite((int)$lead_info['site_id'], [
				'user_id'			=> $school_user_id
		]);

		self::_formatSchool($school_user_id);
	}

	private function _addSite(&$lead_info = []) {
		$parent_site_info = [];

		$event_info = $this->event_model->get($lead_info['event_id']);
		$parent_site_info = $this->site_model->get($event_info['parent_site_id']);

		$site_code = !empty($parent_site_info['site_code']) ? $parent_site_info['site_code'] : '';
		$_POST['site_code'] = $site_code = $site_code . $lead_info['id'];

		if (!$this->site_model->getByCode($site_code . $lead_info['id'])) {
			$site_id = $this->site_model->add('', [
				'license_total'		=> 500,
				'site_type'			=> $lead_info['site_type'],
				'name'				=> $lead_info['name'],
				'image' 			=> '',
				'parent_id'			=> $parent_site_info['id'] ?? 0,
				'payment_gateway'	=> $parent_site_info['payment_gateway'] ?? 'razorpay',
				'sms_gateway'		=> $parent_site_info['sms_gateway'] ?? 'textlocal',
				'email_alert'		=> '',
				'address'			=> '',
				'mobile_length'		=> $parent_site_info['mobile_length'] ?? 10,
				'country_code'		=> $parent_site_info['country_code'] ?? 'IN',
				'state_id'			=> $lead_info['state_id'],
				'city_id'			=> $lead_info['city_id'],
				'site_code'			=> !empty($parent_site_info['id']) ? $lead_info['id'] : ($site_code . $lead_info['id']),
				'discount_code'		=> $parent_site_info['discount_code'] ?? '',
				'discount_percentage' => $parent_site_info['discount_percentage'] ?? '',
				'currency_code'		=> $parent_site_info['currency_code'] ?? 'INR',
				'base_price'		=> $parent_site_info['base_price'] ?? 399.00,
				'ebook_price'		=> $parent_site_info['ebook_price'] ?? 299.00,
				'price_per_page'	=> $parent_site_info['price_per_page'] ?? 8.00,
				'free_page_limit'	=> $parent_site_info['free_page_limit'] ?? 80,
				'hard_cover_price'	=> $parent_site_info['hard_cover_price'] ?? 50.00,
				'tax'				=> $parent_site_info['tax'] ?? 0,
				'tax_text'			=> $parent_site_info['tax_text'] ?? 'GST',
				'timezone'			=> $parent_site_info['timezone'] ?? 'Asia/Kolkata',
				'owner_name'		=> $lead_info['school_head'],
				'authorized_person'	=> $lead_info['authorized_person'],
				'owner_email'		=> $lead_info['email'],
				'owner_mobile'		=> $lead_info['mobile'],
				'owner_password'	=> $lead_info['email'],
				'can_add_site'		=> 0,
				'status'			=> 1,
				'verified'			=> 1,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
				'date_verified'		=> date('Y-m-d H:i:s')
			]);

			$this->school_lead_model->edit($lead_info['id'], [
				'site_id' => $site_id
			]);

			// add to event
			if (!empty($lead_info['event_id'])) {
				$this->event_site_model->add([
					'event_id'	=> (int)$lead_info['event_id'],
					'site_id'	=> (int)$site_id,
				]);
			}

			$lead_info['site_id'] = $site_id;
		}
	}

	public function getEventSchool() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$site_info = $this->site_model->getSchoolBySiteId($this->input->post('site_id'));

			if (!empty($site_info) && $site_info['verified'] == 1) {
				$this->json['error'] = vsprintf('Congratulations! Your school is already registered with BriBooks. Please review the details below and click "OK" to confirm registration for this event. Contact Person Name - %s, Mobile - %s, Email - %s', [
					$site_info['authorized_person'],
					masked_mobile($site_info['mobile']),
					masked_email($site_info['email'])
				]);
			} elseif (!empty($site_info)) {
				$site_slug = preg_replace(['/[^\w\s]/', '/\s+/'], ['', ''], mb_strtolower($site_info['name']));

				if(strtolower($site_info['email']) == strtolower($site_slug.'@bribooks.com')) {
					$site_info['email'] = '';
				}

				if(strtolower($site_info['city']) == 'other') {
					$site_info['city'] = '';
				}

				$this->json['school'] = $site_info;
			} else {
				$this->json['error'] = _li('school_not_found');
			}
		}
	}

	public function verifySchoolEmail() {
		$this->form_validation->set_rules('uid', _l('uid'), 'trim|required|max_length[10]');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[200]');

		self::_runFormValidation();

		if (!$this->json) {
			$school_user_info = $this->school_user_model->get_all(['id' => $this->input->post('uid'),'verification_code' => $this->input->post('code')])['rows'][0] ?? [];

			if (!empty($school_user_info)) {
				$this->school_user_model->edit($school_user_info['id'], [
					'email_verified'	=> 1
				]);

				$this->json['success'] 		= _l('verified');
			} else {
				$this->json['error'] = _li('Invalid_verify_email_url');
			}
		}
	}

	public function saveSchoolLeaflet() {
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'schoolLead']]
		]);

		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site_id', [$this->validate_model, 'site']]
		]);

		$this->form_validation->set_rules('leaflets', _l('leaflets'), 'trim|required|numeric');
		$this->form_validation->set_rules('pincode', _l('pincode'), 'trim|required');
		$this->form_validation->set_rules('address', _l('address'),  'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			$this->school_lead_model->edit($this->input->post('lead_id'), [
				'leaflets'		=> $this->input->post('leaflets'),
				'utm_source'	=> $this->input->post('utm_source') ?? '',
			]);

			$this->site_model->editById($this->input->post('site_id'), [
				'pincode'	=> $this->input->post('pincode'),
				'address'	=> $this->input->post('address'),
			]);

			$this->json['success'] 		= _l('success');
		}
	}
}
