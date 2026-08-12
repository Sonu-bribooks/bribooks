<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SocialLogin {
	public function socialLogin() {
		// $this->form_validation->set_rules('profile', _l('profile'), 'trim|required|max_length[600]');
		$this->form_validation->set_rules('provider', _l('provider'), 'trim|required|in_list[google,microsoft]');

		self::_runFormValidation();

		if (!$this->json) {
			switch ($this->input->post('provider')) {
				case 'google':
					$data = self::_getGoogleProfile($this->input->post('profile'));
					break;
				case 'facebook':
					$data = self::_getFacebookeProfile($this->input->post('profile'));
					break;
				case 'microsoft':
					$data = self::_getMicrosoftProfile($this->input->post('profile'));
					break;
			}

			if (!empty($data)) {
				self::_doLogin($data);
			} else {
				$this->json['error'] = _l('invalid_login_data');
			}
		}
	}

	private function _getGoogleProfile($data = []) {
		$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . $data['id_token']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ch), true);
		curl_close($ch);

		if (
			!empty($result['sub']) &&
			($result['sub'] ?? '') === ($data['id'] ?? '')
		) {
			return [
				'name'		=> $data['name'],
				'email'		=> $data['email'],
				'timezone'	=> $data['timezone'] ?? '',
				'source'	=> 'google',
			];
		}

		return [];
	}

	private function _getMicrosoftProfile($data = []) {
		$ch = curl_init('https://graph.microsoft.com/v1.0/me');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . ($data['access_token'] ?? '')]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ch), true);
		curl_close($ch);

		log_kb(['microsoft Login::' => $result]);

		if (
			!empty($result['id']) &&
			($result['id'] ?? '') === ($data['id'] ?? '')
		) {
			return $data;
		}

		return [];
	}

	private function _getFacebookeProfile($data = []) {
		$ch = curl_init('https://graph.facebook.com/me?access_token=' . $data['accessToken']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ch), true);
		curl_close($ch);

		if (
			!empty($result['id']) &&
			($result['id'] ?? '') === ($data['id'] ?? '')
		) {
			return [
				'name'		=> $data['displayName'],
				'email'		=> $data['userPrincipalName'],
				'timezone'	=> $data['timezone'] ?? '',
				'source'	=> 'microsoft',
			];
		}

		return [];
	}
}
