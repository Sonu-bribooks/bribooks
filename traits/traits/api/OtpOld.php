<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait OtpOld {
	public function sendOtp() {
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|exact_length[' . $this->config->item('site_mobile_length') . ']');
		// $this->form_validation->set_rules('country_code', _l('country_code'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('student_name', _l('student_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('student_grade', _l('student_grade'), 'trim|required|greater_than_equal_to[1]|less_than_equal_to[12]');
		$this->form_validation->set_rules('utm_source', _l('utm_source'), 'trim|max_length[255]');
		$this->form_validation->set_rules('utm_medium', _l('utm_medium'), 'trim|max_length[255]');
		$this->form_validation->set_rules('utm_campaign', _l('utm_campaign'), 'trim|max_length[255]');

		// $this->form_validation->set_rules('student_age', _l('student_age'), [
		// 	'trim',
		// 	'required',
		// 	['student_age', [$this->validate_model, 'studentAge']]
		// ]);

		// $this->form_validation->set_rules('programs', _l('programs'), [
		// 	'trim',
		// 	'required',
		// 	'numeric',
		// 	['programs', [$this->validate_model, 'program']]
		// ]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			if ($this->student_model->get_all([
				'email' => $this->input->post('email')
			])->row_array() && !in_array($this->input->post('email'), TESTING_EMAILS)) {
				$this->json['error'] = _li('You are already registered with this Email');
			} else {
				self::executeOtp();

				$this->json['lead_id'] 	= self::addLead();
			}

			$this->json['error'] 	= empty($this->json['error']) ? false : $this->json['error'];
		}
	}

	public function sendSchoolOtp() {
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('name', _l('school_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('country', _l('country'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('city', _l('city'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('utm_source', _l('utm_source'), 'trim|max_length[255]');
		$this->form_validation->set_rules('utm_medium', _l('utm_medium'), 'trim|max_length[255]');
		$this->form_validation->set_rules('utm_campaign', _l('utm_campaign'), 'trim|max_length[255]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			self::executeOtp(false);

			$this->json['lead_id'] 	= self::addSchoolLead();

			$this->json['error'] 	= empty($this->json['error']) ? false : $this->json['error'];
		}
	}

	private function executeOtp($mobile = false) {
		// Hit the sms Api
		if (
			in_array($this->input->post('mobile'), TESTING_MOBILES) ||
			in_array($this->input->post('email'), TESTING_EMAILS)
		) {
			$otp = 333333;
		} else {
			$otp = $this->default_otp ? $this->default_otp : mt_rand(100000, 999999);
		}

		if (!$this->default_otp) {
			if ($mobile) {
				!in_array($this->input->post('mobile'), TESTING_MOBILES) && $this->alert_model->sms(
					$this->input->post('mobile'),
					str_replace('{otp}', $otp, get_settings('sms_otp'))
				);

				$this->otp_model->add([
					'mobile'		=> $this->input->post('mobile'),
					'otp'			=> $otp,
				]);
			}

			if ($this->input->post('email')) {
				$this->alert_model->validationOtp(
					$this->input->post('email'),
					_l('validation_code'),
					$otp
				);
			}
		}

		$this->json['error'] 	= $this->session->flashdata('error_message');
		$this->json['success'] 	= $this->session->flashdata('flash_message');

		if ($mobile) {
			$this->json['success'] 	= _li('Validation Code sent to ' . $this->input->post('mobile'));
		} else {
			$this->json['success'] 	= _li('Validation Code sent to your Email ID');
		}

		if ($this->default_otp) {
			$this->json['default_otp'] 	= $otp;
		}

		$this->otp_model->add([
			'mobile'		=> $this->input->post('email') ? $this->input->post('email') : $this->input->post('mobile'),
			'otp'			=> $otp,
		]);
	}

	public function resendOtp() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			self::executeOtp();
		}
	}

	public function resendMobileOtp() {
		// $this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), ['trim','required','numeric',['lead', [$this->validate_model, 'lead']]]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$lead_info = $this->lead_model->get($this->input->post('lead_id'));
			if($lead_info) {
				$this->load->model('common/Site_model', 'site_model');
				$this->site_model->initConfig($lead_info['site_id']);

				if($this->input->post('lead_id') && $this->input->post('update_mobile') == '1') {
					$this->lead_model->edit($this->input->post('lead_id'), [
						'update_mobile'	=> $this->input->post('mobile'),
					]);
				}

				self::executeOtp(true);
			} else {
				$this->json['error'] = _l('invalid_lead');
			}
		}
	}

	public function resendSchoolOtp() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			self::executeOtp(false);
		}
	}

	public function validateSchoolOtp() {
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|exact_length[' . $this->config->item('site_mobile_length') . ']');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'schoolLead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			/*$school_info = $this->school_lead_model->get($this->input->post('lead_id'));
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($school_info['site_id']);*/

			if ($this->otp_model->get([
				'mobile'		=> $this->input->post('email'),
				'otp'			=> $this->input->post('otp'),
			])) {
				$this->otp_model->edit([
					'mobile'		=> $this->input->post('email'),
					'otp'			=> $this->input->post('otp'),
				]);

				$this->school_lead_model->edit($this->input->post('lead_id'), [
					'email_verified'	=> 1,
				]);

				$this->json['success'] = _l('email_verified');
			} else {
				$this->json['error'] = _l('enter_valid_email_code');
			}

			// if ($this->otp_model->get([
			// 	'mobile'		=> $this->input->post('mobile'),
			// 	'otp'			=> $this->input->post('otp'),
			// ])) {
			// 	$this->otp_model->edit([
			// 		'mobile'		=> $this->input->post('mobile'),
			// 		'otp'			=> $this->input->post('otp'),
			// 	]);
			//
			// 	$this->school_lead_model->edit($this->input->post('lead_id'), [
			// 		'mobile_verified'	=> 1,
			// 	]);
			//
			// 	$this->json['success'] = _l('otp_successfully_verified');
			// } else {
			// 	$this->json['error'] = _l('please_enter_the_correct_verification_code');
			// }
		}
	}

	public function validateOtp() {
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|exact_length[' . $this->config->item('site_mobile_length') . ']');
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			if ($this->otp_model->get([
				'mobile'		=> $this->input->post('email'),
				'otp'			=> $this->input->post('otp'),
			])) {
				$this->otp_model->edit([
					'mobile'		=> $this->input->post('email'),
					'otp'			=> $this->input->post('otp'),
				]);

				$lead_info = $this->lead_model->get($this->input->post('lead_id'));

				/*$this->lead_model->edit($this->input->post('lead_id'), [
					'mobile_verified'	=> 1,
				]);*/

				// Add student using lead information
				// $lead_info = $this->lead_model->get($this->input->post('lead_id'));
				//
				// $names = explode(' ', $lead_info['name'], 2);
				//
				// $student_id = $this->lead_model->addStudent([
				// 	'first_name'		=> array_shift($names),
				// 	'last_name'			=> array_shift($names),
				// 	'lead_id'			=> $lead_info['id'],
				// 	'parent_name'		=> $lead_info['parent_name'],
				// 	'course_id'			=> $lead_info['course_id'],
				// 	'schedule_id'		=> 0,
				// 	'email'				=> $lead_info['email'],
				// 	'mobile'			=> $lead_info['mobile'],
				// 	'grade'				=> $lead_info['grade'],
				// 	'location'			=> $lead_info['location'],
				// ]);

				// $this->load->model('user/User_model', 'user_model');
				//
				// $code = $this->user_model->addLoginCode([
				// 	'user_id'	=> $student_id
				// ]);

				// $this->json['redirect'] = site_url('login/code/' . $code);
				// $this->json['emis'] 	= EMI_CHARGE;

				$this->json['success'] 	= _l('otp_successfully_verified');
				$this->json['amount'] 	= self::_getFormattedAmount($lead_info);
				$this->json['lead_id'] 	= (int)$lead_info['id'];
				$this->json['redirect'] = self::_generatePaymentLink(
					$this->input->post('lead_id'),
					'premium'
				);
			} else {
				$this->json['error'] 	= _l('please_enter_the_correct_verification_code');
			}
		}
	}

	public function validateMobileOtp() {
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|exact_length[' . $this->config->item('site_mobile_length') . ']');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		// $this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$lead_info = $this->lead_model->get($this->input->post('lead_id'));
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($lead_info['site_id']);

			if ($this->otp_model->get([
				'mobile'		=> $this->input->post('mobile'),
				'otp'			=> $this->input->post('otp'),
			])) {
				$this->otp_model->edit([
					'mobile'		=> $this->input->post('mobile'),
					'otp'			=> $this->input->post('otp'),
				]);

				$this->lead_model->edit($this->input->post('lead_id'), [
					'mobile'			=> $this->input->post('mobile'),
					/*'mobile_verified'	=> 1,*/
				]);

				$this->json['success'] 	= _l('verified');
			} else {
				$this->json['error'] 	= _l('enter_valid_otp');
			}
		}
	}
}
