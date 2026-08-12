<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ForgotPasswordAlert {
	public function forgotPasswordAlert($id = 0){
		if ($info = $this->student_model->get($id)) {
			$site_id = $info['site_id'];

			$site_info = $this->site_model->get($site_id);

			// generate password and store in db
			$password = uniqid();
			$encoded_password = sha1(md5($password . $this->config->item('password_salt')));
			$verification_code = sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

			$this->student_model->edit($id, [
				'verification_code'	=> $verification_code
			]);

			$template = 'email_forgot_password';

			$data['title']			= self::formatEmailSubject($template, $site_id) ?? vsprintf(_li('%s: Reset your password'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'username'			=> $info['username'],
				'url'				=> vsprintf(USER_URL . 'resetpassword?uid=%s&code=%s', [
					$info['id'],
					$verification_code,
				])
			], $site_id);
			$data['link']			= '';
			$data['link_text']		= '';

			if(!empty($site_info['site_code']) && (strpos(strtolower($site_info['site_code']), ISRAEL_SITE_CODE) !== false)) {
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
			} else {
				$message 				= $this->load->view('common/mail/templates/2/general', $data, true);
			}

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function resetPasswordAlert($id = 0){
		if ($info = $this->student_model->get($id)) {
			$site_id = $info['site_id'];

			$site_info = $this->site_model->get($site_id);

			$template = 'email_reset_password';

			$data['title']			= self::formatEmailSubject($template, $site_id) ?? vsprintf(_li('%s: Your password has been changed successfully'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'username'			=> $info['username'],
				'url'				=> USER_URL
			], $site_id);
			$data['link']			= '';
			$data['link_text']		= '';

			if(!empty($site_info['site_code']) && (strpos(strtolower($site_info['site_code']), ISRAEL_SITE_CODE) !== false)) {
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
			} else {
				$message 				= $this->load->view('common/mail/templates/2/general', $data, true);
			}

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}
}
