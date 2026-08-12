<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AppOtp {
	public function sendAppOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email]');

		if ($this->input->post('type') == 'mobile') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
			]);
		} else {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (!$this->spam_lib->validate(1)) {
				return;
			}

			$search_data = [
				// 'role_id'			=> ($this->input->post('login_type') == 'school') ? 9 : 2,
				// 'status'			=> 1,
				// '_deleted'		=> 0,
			];

			if ($this->input->post('type') == 'mobile') {
				$search_data['mobile'] = $this->input->post('mobile');
				// $search_data['mobile_verified'] = 1;
			} else {
				$search_data['email'] = $this->input->post('email');
				// $search_data['email_verified'] = 1;
			}

			$this->db->group_start();
			$this->db->where('role_id', 2);
			$this->db->group_end();

			if ($user_info = $this->db->get_where('users', $search_data)->row_array()) {
				if (empty($user_info['status'])) {
					$this->json['error'] = _l('account_disabled');
				} elseif ($user_info['_deleted']) {
					$this->json['error'] = _l('account_blocked');
				} elseif ($this->input->post('type') == 'mobile' && empty($user_info['mobile_verified'])) {
					$this->json['error'] = _l('mobile_number_not_verfied');
				} elseif ($this->input->post('type') == 'email' && empty($user_info['email_verified'])) {
					$this->json['error'] = _l('email_address_not_verfied');
				} else {
					self::_executeAppOtp($this->input->post('type') == 'mobile');
				}
			} else {
				$this->json['error'] = _li('Seems like you are not yet registered with us. Please use the sign up page to register.');
			}

			if (!empty($this->json['error'])) {
				$this->json['error'] = [
					'is_user_exist' => !empty($user_info) ? 1 : 0,
					'error' 		=> $this->json['error']
				];
			}
		}
	}

	private function _executeAppOtp($mobile = false, $both = false) {
		// Hit the sms Api
		if (
			in_array($this->input->post('mobile'), TESTING_MOBILES) ||
			in_array($this->input->post('email'), TESTING_EMAILS)
		) {
			$otp = DEFAULT_OTP;
		} else {
			$otp = $this->default_otp
				? $this->default_otp
				: mt_rand(100000, 999999);
		}

		if (!$this->default_otp) {
			if ($mobile || $both) {
				$message = str_replace('{otp}', $otp, (((strlen($this->input->post('mobile')) == 12) && (substr($this->input->post('mobile'), 0, 2) == 91)) ? get_settings('sms_otp') : 'OTP for BriBooks is {otp}. Valid for 10 min.'));

				$ch = curl_init(vsprintf('https://2factor.in/API/V1/%s/SMS/%s/%s/ver_code_app_autocopy?var1=%s', [
					SMS['2factor']['api_key'],
					$this->input->post('mobile'),
					$otp,
					$this->input->post('hash'),
				]));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$response = curl_exec($ch);

				log_kb(['sms res' => $response]);
			}

			if ((!$mobile && $this->input->post('email')) || $both) {
				$this->alert_model->validationOtp(
					$this->input->post('email'),
					_l('Your login Verification code for ') . get_settings('system_name'),
					$otp
				);
			}
		}

		if ($mobile) {
			$this->json['success'] 	= sprintf(_li('Validation Code sent to %s'), $this->input->post('mobile'));
		} else {
			$this->json['success'] 	= _li('Validation Code sent to your Email ID');
		}

		if ($both) {
			$this->json['success'] 	= _li('We have sent an OTP on your email and phone number');
		}

		$type = 'email';

		if ($mobile) {
			$type = 'mobile';
		}

		$country_info = self::getCountry(true);

		$this->otp_model->add([
			'mobile'		=> !$mobile
				? $this->input->post('email')
				: $this->input->post('mobile'),
			'otp'			=> $otp,
			'type'			=> $type,
			'country_code'	=> $country_info['country_code'] ?? '',
		]);
	}
}
