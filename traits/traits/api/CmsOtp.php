<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CmsOtp {
	public function sendCmsOtp() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');

		self::_runFormValidation();

		if (!$this->json) {
			$result = $this->user_model->get_all([
				'email'	 => $this->input->post('email'),
			]);

			if ($result['total'] == 0) {
				$this->json['error'] = _li('email_not_found');
			} else {
				self::_executeOtp();
			}
		}
	}

	public function verifyCmsOtp() {
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		self::_runFormValidation();

		if (!$this->json) {
			if (self::_verifyOtp()) {

				$user_info = $this->user_model->get_all([
					'email'	 => $this->input->post('email'),
				])['rows'][0] ?? [];

				log_kb(['user_info' => $user_info]);

				$this->json['user'] = [
					'id' 					=> $user_info['id'],
					'user_email'			=> $user_info['email'],
					'address_id'			=> $user_info['address_id'],
					'user_mobile'			=> $user_info['mobile'],
					'image'					=> $user_info['image'],
					'name'					=> trim($user_info['first_name'] . ' ' . $user_info['last_name']),
					'user_site'				=> $user_info['site_id'] ?? 0,
					'school'				=> $school_info['name'] ?? 0,
					'state_id'				=> $user_info['state_id'] ?? 0,
					'state'					=> $state_info['name'] ?? '',
					'city_id'				=> $user_info['city_id'] ?? 0,
					'city'					=> $city_info['name'] ?? '',
					'grade_id'				=> $user_info['grade_id'] ?? 0,
					'grade'					=> $grade_info['name'] ?? '',
					'section_id'			=> $user_info['section_id'] ?? 0,
					'section'				=> $section_info['name'] ?? '',
					'need_update'			=> $need_update,
					'slug'					=> $user_info['slug'] ?? '',
					'age'					=> $user_info['age'] ?? '',
					'relation'				=> $user_info['relation'] ?? '',
					'biography'				=> $user_info['biography'] ?? '',
					'role_id'				=> $user_info['role_id'],
					'role'					=> get_user_role_by_id($user_info['role_id']),
					'notification'			=> $user_info['notification'],
					'show_country'			=> $user_info['show_country'],
					'referral_code'			=> $user_info['referral_code'],
					'subscription_plan_id'	=> $user_info['subscription_plan_id'] ?? 0,
					'has_bank'				=> $this->bank_model->get_all([
						'user_id'	=> $user_info['id'],
					])['total'] != 0,
				];

				self::_addToken($user_info);

				$this->json['success'] 	= _l('verified');
			} else {
				$this->json['error'] 	= _l('enter_valid_code');
			}
		}
	}
}
