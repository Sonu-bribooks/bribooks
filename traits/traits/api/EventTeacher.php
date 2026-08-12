<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventTeacher {
	public function getEventTeacher() {
		$this->form_validation->set_rules('user_id', _l('user_id'), [
			'trim',
			'required',
			'numeric',
			['user', [$this->validate_model, 'user']]
		]);
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('code', _l('code'), [
			'trim',
			'required',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_code_info = $this->event_user_invite_code_model->get_all([
				'event_id'	  => $this->input->post('event_id'),
				'user_id'	  => $this->input->post('user_id'),
				'code'		  => $this->input->post('code'),
			])['rows'][0] ?? [])) {
				return $this->json['error'] = _li('invalid_code');
			}

			if (empty($user_info = $this->user_model->get($this->input->post('user_id') ?? 0))) {
				return $this->json['error'] = _li('invalid_user');
			}

			$country_info = $this->country_model->get($user_info['country_id'] ?? 0);
			$state_info = $this->state_model->get($user_info['state_id'] ?? 0);
			$city_info  = $this->city_model->get($user_info['city_id'] ?? 0);

			$this->json['user'] = [
				'id'				=> $user_info['id'],
				'site_id'			=> $user_info['site_id'],
				'name'				=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
				'first_name'		=> ucwords($user_info['first_name']),
				'last_name'			=> ucwords($user_info['last_name']),
				'email'				=> $user_info['email'],
				'mobile'			=> $user_info['mobile'],
				'grade'				=> $user_info['grade'] ?? '',
				'section'			=> $user_info['section'] ?? '',
				'country_id'		=> $user_info['country_id'],
				'state_id'			=> $user_info['state_id'],
				'city_id'			=> $user_info['city_id'],
				'country'			=> $country_info['name'] ?? '',
				'state'				=> $state_info['name'] ?? '',
				'city'				=> $city_info['name'] ?? '',
			];
		}
	}
}
