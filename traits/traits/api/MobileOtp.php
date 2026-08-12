<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MobileOtp {
	public function sendMobileOtp() {
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[4]|max_length[30]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,whatsapp]');

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			$result = $this->user_model->get_all([
				'mobile'	=> $this->input->post('mobile'),
			])['rows'][0] ?? [];

			if ($result && $result['id'] !== $this->session->userdata('user_id')) {
				$this->json['error'] = _li('mobile_linked_with_account');
			} else {
				self::_executeOtp(
					$this->input->post('type') == 'mobile',
					false,
					$this->input->post('type') == 'whatsapp',
				);
			}
		}
	}

	public function verifyMobileOtp() {
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[4]|max_length[30]');
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[mobile,whatsapp]');

		self::_runFormValidation();

		if (!$this->json) {
			if (self::_verifyOtp(true)) {
				// update user mobile
				$this->user_model->edit($this->session->userdata('user_id'), [
					'mobile'			=> $this->input->post('mobile'),
					'mobile_verified'	=> 1
				]);

				self::_formatUser($this->session->userdata('user_id'));

				$this->json['success'] 	= _l('verified');
			} else {
				$this->json['error'] 	= _l('enter_valid_code');
			}
		}
	}
}
