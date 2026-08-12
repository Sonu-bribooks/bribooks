<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolSignupOther {
	public function sendSignupSchoolOtherOtp() {
		$this->form_validation->set_rules('institute_type', _l('institute_type'), 'trim|in_list[1,2,3]');
		$this->form_validation->set_rules('event_id', _l('event_id'),  'trim|numeric|max_length[3]');

		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} else if ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('school_name', _l('school_name'),  'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'),  'trim|required|min_length[3]|max_length[128]');
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

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'email'			=> $this->input->post('email')
			])->row_array()) {
				$this->json['error'] = _li('already_registered_email_id');
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('mobile')
			])->row_array()) {
				$this->json['error'] = _li('already_registered_mobile_no');
				return;
			}

			if ($school_lead_others_info = $this->db->get_where('school_lead_others', [
				'email'				=> $this->input->post('email'),
				'email_verified'	=> 1
			])->row_array()) {
				$this->json['error'] = _li('already_registered_email_id');
				return;
			}

			if ($school_lead_others_info = $this->db->get_where('school_lead_others', [
				'mobile'			=> $this->input->post('mobile'),
				'mobile_verified'	=> 1
			])->row_array()) {
				$this->json['error'] = _li('already_registered_mobile_no');
				return;
			}
		}

		if (!$this->json) {
			$result = $this->db->insert('school_lead_others', [
				'event_id'					=> (int)$this->input->post('event_id'),
				'name'						=> $this->input->post('school_name'),
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
				'sections'					=> (int)$this->input->post('section'),
				'grades'					=> json_encode(
					is_array($this->input->post('grade'))
						? $this->input->post('grade')
						: []
				),
				'utm_source'				=> $this->input->post('utm_source') ?? '',
				'utm_medium'				=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'				=> $this->input->post('utm_campaign') ?? '',
				'site_type'					=> $this->input->post('institute_type') ?? 1,
				'date_added'				=> date('Y-m-d H:i:s'),
				'date_modified'				=> date('Y-m-d H:i:s')
			]);

			$lead_id = $this->db->insert_id();

			$this->json['lead_id'] = $lead_id;

			// true mobile otp and false email otp
			self::_executeOtp($this->input->post('type') == 'mobile');

			$this->json['success'] = _li('Thank you for showing your interest in BriBooks Young Authors Fair. Someone from BriBooks team will get in touch with you shortly.');
		}
	}

	public function verifySignupSchoolOtherOtp() {
		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} else if ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			$school_lead_others_info = $this->db->get_where('school_lead_others', [
				'id' => $this->input->post('lead_id')
			])->row_array();

			// true mobile and false email otp verify
			if (!empty($school_lead_others_info) && self::_verifyOtp($this->input->post('type') == 'mobile')) {
				if ($this->input->post('type') == 'mobile') {
					$this->db->update('school_lead_others', [
						'status'			=> 1,
						'mobile_verified'	=> 1,
						'date_modified' 	=> date('Y-m-d H:i:s')
					], [
						'id'				=> $school_lead_others_info['id']
					]);
				} else if ($this->input->post('type') == 'email') {
					$this->db->update('school_lead_others', [
						'status'			=> 1,
						'email_verified'	=> 1,
						'date_modified'		=> date('Y-m-d H:i:s')
					], [
						'id'				=> $school_lead_others_info['id']
					]);
				}

				$this->json['success'] 	= _l('verified');
				$this->json['school_name'] 	= $school_lead_others_info['name'];
			} else {
				$this->json['error'] 	= _l('enter_valid_verification_code');
			}
		}
	}
}
