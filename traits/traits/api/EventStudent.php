<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventStudent {
	public function getEventSite() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($site_info = $this->site_model->get($this->input->post('site_id')))) {
				return $this->json['error'] = _li('school_is_invalid');
			}

			$country_info 	= $this->country_model->get($site_info['country_id'] ?? 0);
			$state_info 	= $this->state_model->get($site_info['state_id'] ?? 0);
			$city_info  	= $this->city_model->get($site_info['city_id'] ?? 0);

			$this->json['school'] = [
				'id'							=> $site_info['id'],
				'parent_id'						=> $site_info['parent_id'],
				'image'							=> !empty($site_info['image']) ? $this->config->item('s3_base_url') . 'public/SiteImages/' . $site_info['image'] : '',
				'site_id'						=> $site_info['id'],
				'name'							=> $site_info['name'],
				'site_code'						=> $site_info['site_code'],
				'site_type'						=> $site_info['site_type'],
				'country_code'					=> $site_info['country_code'],
				'currency_code'					=> $site_info['currency_code'],
				'authorized_person'				=> $site_info['authorized_person'],
				'email'							=> '',
				'mobile'						=> '',
				'alternate_authorized_person'   => '',
				'alternate_email'			   	=> '',
				'alternate_mobile'			  	=> '',
				'country_id'					=> $site_info['country_id'],
				'state_id'					  	=> $site_info['state_id'],
				'city_id'					   	=> $site_info['city_id'],
				'country'						=> $country_info['name'] ?? '',
				'state'						 	=> $state_info['name'] ?? '',
				'city'						  	=> $city_info['name'] ?? '',
				'verified'					  	=> $site_info['verified'],
			];
		}
	}

	public function getEventSites() {
		$this->form_validation->set_rules('site_type', _l('site_type'), 'trim|in_list[1,2,3,4,5,6,7,8,9]');
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim');
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('state_id', _l('state'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		!empty($this->input->post('city_id')) && $this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		!empty($this->input->post('site_id')) && $this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [];

			if ($this->input->post('country_id')) {
				$filter_data['country_id'] = (int)$this->input->post('country_id');
			}

			if ($this->input->post('city_id')) {
				$filter_data['city_id'] = (int)$this->input->post('city_id');
			}

			if ($this->input->post('state_id')) {
				$filter_data['state_id'] = (int)$this->input->post('state_id');
			}

			if ($this->input->post('site_id')) {
				$filter_data['site_id'] = (int)$this->input->post('site_id');
			}

			// if ($this->input->post('event_id')) {
			// 	$filter_data['event_id'] = (int)$this->input->post('event_id');
			// }

			if ($this->input->post('site_code')) {
				$filter_data['site_code'] = $this->input->post('site_code');
			}

			if ($this->input->post('site_type')) {
				$filter_data['site_type'] = (int)$this->input->post('site_type');
			}

			if ($this->input->post('parent_id')) {
				$filter_data['parent_id'] = (int)$this->input->post('parent_id');
			}

			$this->json['schools'] = [];

			if (($this->input->post('type') ?? '') == 'user') {
				$event_id 	= (int)$this->input->post('event_id');
				$event_info = $this->event_model->get($event_id);

				if (!empty($event_info['direct_site_id'])) {
					$site_info = $this->site_model->get($event_info['direct_site_id']);

					$direct_site_info = [
						'id'			=> $site_info['id'],
						'parent_id'	 	=> $site_info['parent_id'],
						'site_id' 		=> $site_info['id'] ?? '',
						'event_id'	 	=> $event_info['id'],
						'name' 			=> $site_info['name'] ?? '',
						'site_code' 	=> $site_info['site_code'] ?? '',
						'site_type'	 	=> $site_info['site_type'],
						'country_code'  => $site_info['country_code'],
						'currency_code' => $site_info['currency_code'],
						'country_id' 	=> $site_info['country_id'] ?? '',
						'state_id' 		=> $site_info['state_id'] ?? '',
						'city_id' 		=> $site_info['city_id'] ?? '',
						'state'		 	=> '',
						'city'		  	=> '',
					];

					$this->json['schools'][] = $direct_site_info;
				}
			}

			if (!empty($schools = $this->site_model->get_all($filter_data)['rows'] ?? [])) {
				$results = array_map(function($school) {
					$country_info 	= $this->country_model->get($school['country_id'] ?? 0);
					$state_info 	= $this->state_model->get($school['state_id'] ?? 0);
					$city_info  	= $this->city_model->get($school['city_id'] ?? 0);

					return [
						'id'							=> $school['id'],
						'parent_id'					 	=> $school['parent_id'],
						'site_id'					   	=> $school['id'],
						'name'						 	=> $school['name'],
						'site_code'					 	=> $school['site_code'],
						'site_type'					 	=> $school['site_type'],
						'country_code'				  	=> $school['country_code'],
						'currency_code'				 	=> $school['currency_code'],
						'authorized_person'			 	=> '',
						'email'						 	=> '',
						'mobile'						=> '',
						'alternate_authorized_person'   => '',
						'alternate_email'			   	=> '',
						'alternate_mobile'			  	=> '',
						'country_id'					=> $school['country_id'],
						'state_id'					  	=> $school['state_id'],
						'city_id'					   	=> $school['city_id'],
						'country'						=> $country_info['name'] ?? '',
						'state'						 	=> $state_info['name'] ?? '',
						'city'						  	=> $city_info['name'] ?? '',
						'verified'					  	=> $school['verified'],
					];
				}, $schools);

				if (!empty($results)) {
					$this->json['schools'] = array_merge($this->json['schools'], $results);
				}
			}
		}
	}

	public function getEventStudent() {
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

			if (
				!empty($this->input->post('type')) &&
				strtolower($this->input->post('type')) == 'referral'
			) {
				$referral_count = $this->user_referral_model->get_all([
					'event_id' 		=> (int)$user_code_info['event_id'],
					'referrer_id' 	=> (int)$user_code_info['user_id'],
				])['total'] ?? 0;

				if ($referral_count >= $user_code_info['referral_limit'] ) {
					return $this->json['error'] = 'Referral limit reached. Need help? support@bribooks.com';
				}
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
