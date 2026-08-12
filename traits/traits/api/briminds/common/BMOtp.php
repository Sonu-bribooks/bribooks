<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BMOtp {
	// public function sendBMOtp() {
	// 	$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email,whatsapp]');

	// 	if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
	// 		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
	// 			'min_length'	=> _li('Please enter a valid mobile number'),
	// 			'max_length'	=> _li('Please enter a valid mobile number'),
	// 		]);
	// 	} else {
	// 		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]', [
	// 			'valid_email'	=> _li('Please enter a valid email address'),
	// 		]);
	// 	}

	// 	self::_runFormValidation();

	// 	if (!$this->json) {
	// 		if (!self::_verifyCaptcha()) {
	// 			$this->json['error'] = _li('Invalid Captcha. Please try again.');
	// 			return;
	// 		}

	// 		if (!$this->spam_lib->validate(1)) {
	// 			return;
	// 		}

	// 		$search_data = [];

	// 		if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
	// 			$search_data['mobile'] = $this->input->post('mobile');
	// 		} else {
	// 			$search_data['email'] = $this->input->post('email');
	// 		}

	// 		$this->load->model('briminds/BMUser_model', 'bm_user_model');

	// 		if ($user_info = $this->bm_user_model->get_all($search_data)['rows'][0] ?? []) {
	// 			if (empty($user_info['status'])) {
	// 				$this->json['error'] = _l('account_disabled');
	// 			} elseif ($user_info['_deleted']) {
	// 				$this->json['error'] = _l('account_blocked');
	// 			} else {
	// 				self::_executeBMOtp(
	// 					$this->input->post('type') == 'mobile',
	// 					false,
	// 					$this->input->post('type') == 'whatsapp',
	// 				);
	// 			}
	// 		} else {
	// 			$this->json['error'] = _li('Seems like you are not yet registered with us. Please use the sign up page to register.');
	// 		}
	// 	}
	// }

	// public function verifyBMOtp() {
	// 	$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,email,whatsapp]');

	// 	if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
	// 		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
	// 			'min_length'	=> _li('Please enter a valid mobile number'),
	// 			'max_length'	=> _li('Please enter a valid mobile number'),
	// 			'numeric'		=> _li('Please enter a valid mobile number')
	// 		]);
	// 	} else {
	// 		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]', [
	// 			'valid_email'	=> _li('Please enter a valid email address'),
	// 		]);
	// 	}

	// 	$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

	// 	self::_runFormValidation();

	// 	if (!$this->json) {
	// 		if (!self::_verifyCaptcha()) {
	// 			$this->json['error'] = _li('Invalid Captcha. Please try again.');
	// 			return;
	// 		}

	// 		if (self::_verifyBMOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {

	// 			CI_Events::trigger('access_log', [
	// 				'module'	=> 'user_login'
	// 			]);

	// 			$this->json['success'] 	= _l('otp_successfully_verified');
	// 		} else {
	// 			$this->json['error'] 	= _l('please_enter_the_correct_verification_code');
	// 		}
	// 	}
	// }

	private function _executeBMOtp($mobile = false, $both = false, $whatsapp = false) {
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
				!in_array($this->input->post('mobile'), TESTING_MOBILES) && CI_Events::trigger('bm_user_otp', [
					'mobile' 	=> $this->input->post('mobile'),
					'otp' 		=> $otp,
					'type'		=> 'sms',
				]);
			}

			if ($whatsapp) {
				!in_array($this->input->post('mobile'), TESTING_MOBILES) && CI_Events::trigger('bm_user_otp', [
					'mobile' 	=> $this->input->post('mobile'),
					'otp' 		=> $otp,
					'type'		=> 'whatsapp',
				]);
			}

			if ((!$mobile && !$whatsapp && $this->input->post('email')) || $both) {
				CI_Events::trigger('bm_user_otp', [
					'email' 	=> $this->input->post('email'),
					'otp' 		=> $otp,
					'type'		=> 'email',
				]);
			}
		}

		if ($mobile) {
			$this->json['success'] 	= _li('The verification code has been sent to your mobile number');
		} elseif ($whatsapp) {
			$this->json['success'] 	= _li('The verification code has been sent to your WhatsApp');
		} else {
			$this->json['success'] 	= _li('The verification code has been sent to your email address');
		}

		if ($both) {
			$this->json['success'] 	= _li('We have sent an OTP on your email and phone number');
		}

		$type = 'email';

		if ($mobile) {
			$type = 'mobile';
		} elseif ($whatsapp) {
			$type = 'whatsapp';
		}

		$this->load->model('briminds/common/BMOtp_model', 'bm_otp_model');

		$this->bm_otp_model->add([
			'send_to'		=> ($mobile || $whatsapp)
				? $this->input->post('mobile')
				: $this->input->post('email'),
			'otp'			=> $otp,
			'type'			=> $type,
		]);

		return $otp;
	}

	private function _verifyBMOtp($mobile = false) {
		$this->load->model('briminds/common/BMOtp_model', 'bm_otp_model');

		if ($this->bm_otp_model->get([
			'send_to'		=> $mobile
				? $this->input->post('mobile')
				: $this->input->post('email'),
			'otp'			=> $this->input->post('otp'),
		])) {
			$this->bm_otp_model->edit([
				'send_to'		=> $mobile
					? $this->input->post('mobile')
					: $this->input->post('email'),
				'otp'			=> $this->input->post('otp'),
			]);

			return true;
		} else {
			return false;
		}
	}
}
