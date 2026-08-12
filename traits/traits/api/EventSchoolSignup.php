<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventSchoolSignup {
	public function sendEventSchoolOtp() {
		$this->form_validation->set_rules('site_type', _l('site_type'), 'trim|in_list[1,2,3,4,5,6,7]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');

		empty($this->input->post('other_school')) &&  $this->form_validation->set_rules('school_name', _l('school_name'), 'trim|required|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
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

            if (!empty($validate_mesaage = self::_validateSchoolLead($this->input->post()))) {
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
			self::_executeOtp(
				$this->input->post('type') == 'mobile',
				false,
				$this->input->post('type') == 'whatsapp',
			);

			$this->json['success'] = sprintf(_li('Thank you for showing your interest in BriBooks. Someone from BriBooks team will get in touch with you shortly.'));
		}
	}

	public function verifyEventSchoolOtp() {
		if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid mobile number'),
				'max_length'	=> _li('Please enter a valid mobile number'),
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
			if (self::_verifyOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$lead_info = $this->school_lead_model->get($this->input->post('lead_id'));

				if (!empty($lead_info)) {
					$site_id 	= 0;
					$school_id 	= 0;

					if (!empty($lead_info['site_id']) && !empty($lead_info['school_id'])) {
						$site_id 	= self::_updateSiteDetails($lead_info);
						$school_id 	= self::_updateSchoolDetails($lead_info);
					} elseif (empty($lead_info['site_id']) && !empty($lead_info['school_id'])) {
						$site_id = self::_addSiteDetails($lead_info);

						if (!empty($site_id)) {
							$lead_info['site_id'] = $site_id;
							$school_id = self::_updateSchoolDetails($lead_info);
						}
					} elseif (!empty($lead_info['site_id']) && empty($lead_info['school_id'])) {
						$site_id 	= self::_updateSiteDetails($lead_info);

						if (!empty($site_id)) {
							$lead_info['site_id'] = $site_id;
							$school_id 	= self::_addSchoolDeatils($lead_info);
						}
					} elseif (empty($lead_info['site_id']) && empty($lead_info['school_id'])) {
						$site_id = self::_addSiteDetails($lead_info);

						if (!empty($site_id)) {
							$lead_info['site_id'] = $site_id;
							$school_id = self::_addSchoolDeatils($lead_info);
						}
					}

					if (!empty($site_id)) {
						$lead_info['site_id'] = $site_id;
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
						'mobile_verified' 	=> in_array($this->input->post('type'), ['mobile', 'whatsapp']) ? 1 : 0,
						'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
					]);

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
				} else {
					$this->json['error'] 	= _l('lead_is_invalid');
				}
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}

	private function _updateSchoolUser($lead_info = []) {
		// update user account of particular site
		$school_user_info = $this->school_user_model->get_all([
			'site_id' => $lead_info['site_id']
		])['rows'][0] ?? [];

		if (!empty($lead_info['mobile'])) {
			$username = strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $lead_info['name']), 0, 4) .
				substr($lead_info['mobile'], -4)
			));
		} else {
			$this->db->select_max('id');
			$last_user_id = $this->db->get('users')->row_array()['id'];
			$last_user_id++;

			$last_user_id = sprintf('%06d', $last_user_id);

			$username = strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $lead_info['name']), 0, 2) .
				substr($last_user_id, -6)
			));
		}

		$password 			= uniqid();
		$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

		if (empty($school_user_info)) {
			$school_user_id = $this->school_user_model->add([
				'first_name'		=> $lead_info['name'],
				'password'			=> sha1($lead_info['name'] . '' . rand()),
				'slug'				=> get_user_slug($username),
				'username'			=> $username,
				'email'				=> $lead_info['email'],
				'mobile'			=> $lead_info['mobile'],
				'status'			=> 1,
				'role_id'			=> 9,
				'state_id'			=> (int)$lead_info['state_id'],
				'city_id'			=> (int)$lead_info['city_id'],
				'site_id'			=> (int)$lead_info['site_id'],
				'mobile_verified' 	=> (($lead_info['type'] == 'mobile') || ($lead_info['type'] == 'whatsapp')) ? 1 : 0,
				'email_verified' 	=> (($lead_info['type'] == 'email') || ($lead_info['type'] == 'email_link')) ? 1 : 0,
				'ip'				=> $lead_info['ip'],
				'timezone'			=> $lead_info['timezone'],
				'location'			=> $lead_info['country'],
				'source'			=> $lead_info['utm_source'] ?? '',
				'verification_code'	=> $verification_code ?? '',
				'lead_id'			=> $lead_info['id'] ?? 0,
			]);
		} else {
			$update_data = [
				'first_name'		=> $lead_info['name'],
				'email' 			=> $lead_info['email'],
				'mobile' 			=> $lead_info['mobile'],
				'mobile_verified' 	=> (($lead_info['type'] == 'mobile') || ($lead_info['type'] == 'whatsapp')) ? 1 : $school_user_info['mobile_verified'],
				'email_verified' 	=> (($lead_info['type'] == 'email') || ($lead_info['type'] == 'email_link')) ? 1 : $school_user_info['email_verified'],
				'ip'				=> $lead_info['ip'],
				'timezone'			=> $lead_info['timezone'],
				'source'			=> $lead_info['utm_source'] ?? $school_user_info['utm_source'],
				'location'			=> $lead_info['country'],
			];

			if (empty($school_user_info['username'])) {
				$update_data['username']	= $username ?? '';
			}

			if (empty($school_user_info['verification_code'])) {
				$update_data['verification_code']	= $verification_code ?? '';
			}

			$this->school_user_model->edit($school_user_info['id'], $update_data);

			$school_user_id = $school_user_info['id'] ?? 0;
		}

		if ($school_user_id) {
			$this->site_model->editById((int)$lead_info['site_id'], [
				'user_id'			=> $school_user_id
			]);

			$this->school_model->editBySite((int)$lead_info['site_id'], [
				'user_id'			=> $school_user_id
			]);
		}

		self::_formatSchool($school_user_id);
		self::_addToken($this->user_model->get($school_user_id));
	}

	private function _updateSiteDetails($lead_info) {
		$update_site_data = [
			'name' 				  			=> trim($lead_info['name']),
			'state_id' 			  			=> $lead_info['state_id'] ?? 0,
			'city_id' 			  			=> $lead_info['city_id'] ?? 0,
			'owner_name' 	      			=> !empty($lead_info['school_head']) ? trim($lead_info['school_head']) : '',
			'authorized_person'   			=> !empty($lead_info['authorized_person']) ? trim($lead_info['authorized_person']) : '',
			'owner_email' 		  			=> $lead_info['email'] ?? '',
			'owner_mobile' 	      			=> $lead_info['mobile'] ?? '',
			'verified' 			  			=> 1,
		];

		$this->site_model->editById($lead_info['site_id'], $update_site_data);

		return $lead_info['site_id'];
	}

	private function _updateSchoolDetails($lead_info) {
		$update_school_data = [
			'site_id' 				  		=> $lead_info['site_id'] ?? 0,
			'name' 				  			=> trim($lead_info['name']),
			'state_id' 			  			=> $lead_info['state_id'] ?? 0,
			'city_id' 			  			=> $lead_info['city_id'] ?? 0,
			'owner_name' 	      			=> !empty($lead_info['school_head']) ? trim($lead_info['school_head']) : '',
			'authorized_person'   			=> !empty($lead_info['authorized_person']) ? trim($lead_info['authorized_person']) : '',
			'owner_email' 		  			=> $lead_info['email'] ?? '',
			'owner_mobile' 	      			=> $lead_info['mobile'] ?? '',
			'designation' 			  		=> $lead_info['designation'] ?? '',
			'verified' 			  			=> 1,
			'date_verified'					=> date('Y-m-d H:i:s')
		];

		$this->school_model->edit($lead_info['school_id'], $update_school_data);

		return $lead_info['school_id'];
	}

	private function _addSiteDetails($lead_info = []) {
		$site_info = [];

		if (empty($country_site_info = $this->site_model->getSiteByName($lead_info['country']))) {
			$country_site_info = $this->site_model->getSiteByName('Global');
		}

		if (!empty($country_site_info)) {
			$site_info = $this->site_model->get($country_site_info['id']);
		}

		$school_info = $this->school_model->get($lead_info['school_id']);

		$site_id = $this->site_model->addSite([
			'license_total'			=> 500,
			'name'					=> $lead_info['name'],
			'site_type'				=> $lead_info['site_type'],
			'image' 				=> '',
			'parent_id'				=> $site_info['id'] ?? 0,
			'payment_gateway'		=> $site_info['payment_gateway'] ?? 'razorpay',
			'sms_gateway'			=> $site_info['sms_gateway'] ?? 'textlocal',
			'email_alert'			=> $site_info['email_alert'] ?? '',
			'address'				=> $school_info['address'] ?? '',
			'landmark'				=> '',
			'pincode'				=> $school_info['pincode'] ?? '',
			'mobile_length' 	 	=> $site_info['mobile_length'],
			'country_code' 		 	=> $site_info['country_code'],
			'country_id' 		 	=> $site_info['country_id'] ?? 0,
			'state_id'				=> $lead_info['state_id'],
			'city_id'				=> $lead_info['city_id'],
			'site_code' 			=> $site_info['site_code'] . '-lead-' . uniqid(),
			'discount_code' 	  	=> $site_info['discount_code'] ?? '',
			'discount_percentage' 	=> $site_info['discount_percentage'],
			'timezone' 			  	=> $site_info['timezone'],
			'currency_code'			=> $site_info['currency_code'] ?? '',
			'base_price' 		  	=> $site_info['base_price'] ?? '',
			'ebook_price' 		  	=> $site_info['ebook_price'] ?? '',
			'audio_book_price' 		=> $site_info['audio_book_price'] ?? '',
			'black_white_price' 	=> $site_info['black_white_price'] ?? '',
			'black_white_price_per_page' 	=> $site_info['black_white_price_per_page'] ?? '',
			'price_per_page' 	  	=> $site_info['price_per_page'] ?? '',
			'free_page_limit' 	  	=> $site_info['free_page_limit'] ?? '',
			'hard_cover_price' 	  	=> $site_info['hard_cover_price'] ?? '',
			'paperback_price' 	  	=> $site_info['paperback_price'] ?? '',
			'tax' 				  	=> $site_info['tax'] ?? 0,
			'tax_text' 			  	=> $site_info['tax_text'] ?? '',
			'owner_email'			=> $lead_info['email'],
			'owner_mobile'			=> $lead_info['mobile'],
			'authorized_person'		=> $lead_info['authorized_person'],
			'owner_name'			=> $lead_info['school_head'] ?? '',
			'can_add_site'			=> 0,
			'status'				=> 1,
			'verified'				=> 1,
			'date_verified'			=> date('Y-m-d H:i:s')
		]);

		if (!empty($site_id)) {
			$this->site_model->editById($site_id, [
				'site_code' => get_site_code_slug(trim($lead_info['name'])) . "-" . $site_id
			]);
		}

		return $site_id;
	}

	private function _addSchoolDeatils($lead_info = []) {
		if (!empty($lead_info)) {
			$site_info = [];

			if (empty($country_site_info = $this->site_model->getSiteByName($lead_info['country']))) {
				$country_site_info = $this->site_model->getSiteByName('Global');
			}

			if (!empty($country_site_info)) {
				$site_info = $this->site_model->get($country_site_info['id']);
			}

			$insert_school_data = [
				'parent_id' 		  			=> $lead_info['parent_id'] ?? 0,
				'site_id' 		  				=> $lead_info['site_id'] ?? 0,
				'name' 				  			=> trim($lead_info['name']),
				'site_code' 		  			=> $site_info['site_code'] . "-lead-" . uniqid(),
				'site_type' 		  			=> $lead_info['site_type'] ?? 1,
				'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
				'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
				'timezone' 			  			=> $site_info['timezone'] ?? '',
				'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
				'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
				'email_alert' 		  			=> $site_info['email_alert'] ?? '',
				'address' 			  			=> $lead_info['address'] ?? '',
				'landmark' 			  			=> $lead_info['landmark'] ?? '',
				'pincode' 			  			=> $lead_info['zipcode'] ?? '',
				'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
				'country_code' 		  			=> $site_info['country_code'] ?? '',
				'currency_code' 	  			=> $site_info['currency_code'] ?? '',
				'country_id' 		 			=> $site_info['country_id'] ?? 0,
				'state_id' 			  			=> $lead_info['state_id'] ?? 0,
				'city_id' 			  			=> $lead_info['city_id'] ?? 0,
				'base_price' 		  			=> $site_info['base_price'] ?? 0,
				'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
				'audio_book_price' 				=> $site_info['audio_book_price'] ?? '',
				'black_white_price' 			=> $site_info['black_white_price'] ?? '',
				'black_white_price_per_page' 	=> $site_info['black_white_price_per_page'] ?? '',
				'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
				'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
				'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
				'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
				'tax' 				  			=> $site_info['tax'] ?? '',
				'tax_text' 			  			=> $site_info['tax_text'] ?? '',
				'owner_name' 	      			=> !empty($lead_info['school_head']) ? trim($lead_info['school_head']) : '',
				'authorized_person'   			=> !empty($lead_info['authorized_person']) ? trim($lead_info['authorized_person']) : '',
				'owner_email' 		  			=> $lead_info['email'] ?? '',
				'owner_mobile' 	      			=> $lead_info['mobile'] ?? '',
				'alternate_authorized_person'   => $lead_info['alternate_authorized_person'] ?? '',
				'alternate_owner_email' 		=> $lead_info['alternate_email'] ?? '',
				'alternate_owner_mobile' 	    => $lead_info['alternate_mobile'] ?? '',
				'designation' 			  		=> $lead_info['designation'] ?? '',
				'status' 			  			=> 1,
				'verified' 			  			=> 1,
				'date_verified'					=> date('Y-m-d H:i:s'),
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
			];

			$school_id = $this->school_model->add($insert_school_data);

			if (!empty($school_id)) {
				$this->school_model->edit($school_id, [
					'site_code' => get_site_code_slug(trim($lead_info['name'])) . "-" . $school_id
				]);
			}

			return $school_id;
		}
	}

    private function _validateSchoolLead($data = []) {
		if (!empty($data['site_id'])) {
			if (!empty($data['email']) && !empty($user_email_info = $this->user_model->get_all([
				'email'                 => $data['email'],
			])['rows'][0] ?? '') && ($user_email_info['site_id'] != $data['site_id'] || $user_email_info['role_id'] != 9)) {
				return _li('This_email_is_already_registered_with_BriBooks');
			}

			if (!empty($data['mobile']) && !empty($user_mobile_info = $this->user_model->get_all([
				'mobile'                 => $data['mobile'],
			])['rows'][0] ?? '') && ($user_mobile_info['site_id'] != $data['site_id'] || $user_mobile_info['role_id'] != 9)) {
				return _li('This_mobile_is_already_registered_with_BriBooks');
			}
		} else {
			if (!empty($data['email']) && !empty($user_email_info = $this->user_model->get_all([
				'email'                 => $data['email'],
			])['rows'][0] ?? '')) {
				return _li('This_email_is_already_registered_with_BriBooks');
			}

			if (!empty($data['mobile']) && !empty($user_mobile_info = $this->user_model->get_all([
				'mobile'                 => $data['mobile'],
			])['rows'][0] ?? '')) {
				return _li('This_mobile_is_already_registered_with_BriBooks');
			}
		}

        if (!empty($data['email']) && !empty($site_info = $this->site_model->get_all([
			'owner_email'           => $data['email'],
			'site_id_ne'            => !empty($data['site_id']) ? $data['site_id'] : 0,
		])['rows'][0] ?? '')) {
			return _li('Your_email_is_already_registered_with_BriBooks');
		}

		if (!empty($data['mobile']) && !empty($site_info = $this->site_model->get_all([
			'owner_mobile'          => $data['mobile'],
			'site_id_ne'            => !empty($data['site_id']) ? $data['site_id'] : 0,
		])['rows'][0] ?? '')) {
			return _li('Your_mobile_is_already_registered_with_BriBooks');;
		}
	}
}
