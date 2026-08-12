<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EmailOtp {
	public function sendEmailOtp() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');

		self::_runFormValidation();

		if (!$this->json) {
			if (!$this->spam_lib->validate(1)) {
				return;
			}

			$result = $this->user_model->get_all([
				'email'				=> $this->input->post('email'),
				// 'role_id'			=> 2,
				'email_verified'	=> 1,
			]);

			if (
				$result['total'] > 0 &&
				$result['rows'][0]['id'] != $this->session->userdata('user_id')
			) {
				$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks');
			} else {
				self::_executeOtp();
			}
		}
	}

	public function verifyEmailOtp() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		self::_runFormValidation();

		if (!$this->json) {
			if (self::_verifyOtp()) {
				// update user email
				$this->user_model->edit($this->session->userdata('user_id'), [
					'email'				=> $this->input->post('email'),
					'email_verified'	=> 1,
				]);

				self::_formatUser($this->session->userdata('user_id'));

				$this->json['success'] 	= _l('verified');
			} else {
				$this->json['error'] 	= _l('enter_valid_code');
			}
		}
	}
}
