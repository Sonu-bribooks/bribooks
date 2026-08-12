<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait UserAwardAddress {
	public function getAwardAddress() {

        $this->form_validation->set_rules('uid', _l('uid'), 'trim|required|numeric');
        $this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
            $this->load->model('user/UserAwardAddress_model', 'user_award_address_model');

            $user_info = $this->user_model->get_all(['user_id' => $this->input->post('uid'), 'verification_code' => $this->input->post('code')])['rows'][0] ?? [];

			if (!empty($user_info)	) {

                $addresses = $this->user_award_address_model->get_all([
                    'user_id'	=> (int)$this->input->post('uid'),
                    'event_id'	=> (int)$this->input->post('event_id') ?? 0,
                ])['rows'][0] ?? [];

                if (!empty($addresses)) {
					if ($addresses['status'] != 0) {
						$this->json['error'] = _li('Address already submitted. <br/>For any changes mail at support@bribooks.com.');
					}

                    $address_data = [
                        'uid'       => $addresses['user_id'],
                        'name'      => $addresses['name'] ?? ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
                        'email'     => $addresses['email'] ?? $user_info['email'],
                        'mobile'    => $addresses['mobile'] ?? $user_info['mobile'],
                        'address'   => $addresses['address'] ?? NULL,
                        'zipcode'   => $addresses['zipcode'] ?? NULL,
                        'landmark'  => $addresses['landmark'] ?? NULL,
                        'type'      => $addresses['type'] ?? NULL
                    ];

                    $this->json['address'] = $address_data;
                } else {
                    $this->json['error'] = _li('Invalid url');
                }
            } else {
				$this->json['error'] = _li('Invalid url');
            }
		}
	}

	public function addAwardAddress() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[4]|max_length[128]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[10]|max_length[15]',[
			'min_length' => 'Please enter a valid 10 digit mobile number',
			'max_length' => 'Please enter a valid 15 digit mobile number'
		]);
        $this->form_validation->set_rules('email', _l('email'), 'trim|required');
		$this->form_validation->set_rules('address', _l('address'), 'trim|required|min_length[4]|max_length[255]');

		if (mb_strtolower($this->input->post('country')) !== 'india') {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|min_length[4]|max_length[10]');
		} else {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|numeric|exact_length[6]');
		}

		$this->form_validation->set_rules('landmark', _l('landmark'), 'trim');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[Office,Other,Home]');
		self::_runFormValidation();

		if (!$this->json) {
		    $this->load->model('user/UserAwardAddress_model', 'user_award_address_model');

			$address_info = $this->user_award_address_model->get_all([
				'user_id'	=> (int)$this->input->post('uid'),
				'event_id'	=> (int)$this->input->post('event_id') ?? 0,
			])['rows'][0] ?? '';

			if (!empty($address_info)) {
				$this->user_award_address_model->edit($address_info['id'] , [
					'name'	    => $this->input->post('name'),
					'mobile'	=> $this->input->post('mobile'),
					'email'	    => $this->input->post('email'),
					'zipcode'	=> $this->input->post('zipcode'),
					'address'	=> $this->input->post('address'),
					'landmark'	=> $this->input->post('landmark'),
					'type'		=> $this->input->post('type'),
					'status'	=> 1,
				]);

				$this->json['success'] = _l('address_saved_successfully');
			} else {
				$this->json['error'] = _li('Invalid User');
			}
		}
	}

	public function deleteAwardAddress() {
		$this->load->model('user/UserAwardAddress_model', 'user_award_address_model');

		$this->form_validation->set_rules('address_id', _l('address_id'), [
			'trim',
			'required',
			'numeric',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->user_award_address_model->delete($this->input->post('address_id'));
			$this->json['success'] = _l('address_deleted_successfully');
		}
	}

	public function getSchoolBySiteCode() {
		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('site_code'), 'trim|required|min_length[4]');

		self::_runFormValidation();

		if (!$this->json) {

			$site_info = $this->site_model->get_all([
				'id'			=> (int)$this->input->post('site_id'),
				'site_codes'	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (!empty($site_info)) {
				$this->load->model('school/SchoolAwardAddress_model', 'school_award_address_model');

				$address_info = $this->school_award_address_model->get_all([
					'site_id'		=> (int)$this->input->post('site_id'),
				])['rows'][0] ?? [];

				if (!empty($address_info)) {
					$state_info = $this->state_model->get($address_info['state_id']);
					$city_info = $this->city_model->get($address_info['city_id']);
					$this->json['school'] = [
						'name'			=> $address_info['name'] ?? '',
						'site_id'		=> $address_info['site_id'] ?? 0,
						'mobile'		=> $address_info['mobile'] ?? '',
						'email'			=> $address_info['email'] ?? '',
						'address'		=> $address_info['address'] ?? '',
						'zipcode'		=> $address_info['zipcode'] ?? '',
						'landmark'		=> $address_info['landmark'] ?? '',
						'delivery'		=> $address_info['delivery'] ?? '',
						'state_id'		=> $address_info['state_id'] ?? 0,
						'city_id'		=> $address_info['city_id'] ?? 0,
						'state'			=> $state_info['name'] ?? '',
						'city'			=> $city_info['name'] ?? ''
					];
				} else {
					if (!empty($this->input->post('eid') ?? 0)) {
						if ($this->db->get_where('school_details_nyaf_guest', ['site_id'	=> $this->input->post('site_id'), 'event_id'	=> $this->input->post('eid')])->row_array()) {
							$this->json['error'] = _li('Details already submitted');
							return;
						}
					}

					$state_info = $this->state_model->get($site_info['state_id']);
					$city_info = $this->city_model->get($site_info['city_id']);
					$this->json['school'] = [
						'name'			=> $site_info['name'] ?? '',
						'site_id'		=> $site_info['id'] ?? 0,
						'mobile'		=> $site_info['owner_mobile'] ?? '',
						'email'			=> $site_info['owner_email'] ?? '',
						'state_id'		=> $site_info['state_id'] ?? 0,
						'city_id'		=> $site_info['city_id'] ?? 0,
						'state'			=> $state_info['name'] ?? '',
						'city'			=> $city_info['name'] ?? ''
					];
				}
			} else {
				$this->json['error'] = _li('Invalid url');
			}
		}
	}

	public function getSchoolAwardAddress() {

        $this->form_validation->set_rules('school_id', _l('school_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
            $this->load->model('school/SchoolAwardAddress_model', 'school_award_address_model');

			if (empty($school_code_info = $this->event_school_invite_code_model->get_all([
				'event_id'	  	=> $this->input->post('event_id') ?? 0,
				'school_id'	 	=> $this->input->post('school_id'),
				'code'		  	=> $this->input->post('code'),
			])['rows'])) {
				return $this->json['error'] = _li('invalid_url');
			}

            $info = $this->school_model->get($this->input->post('school_id'));

			if (!empty($info)	) {
                $addresses = $this->school_award_address_model->get_all([
                    'school_id'	=> (int)$this->input->post('school_id'),
                    'event_id'	=> (int)$this->input->post('event_id') ?? 0,
                ])['rows'][0] ?? [];

                if (!empty($addresses)) {
					if ($addresses['status'] != 0) {
						$this->json['error'] = _li('Address already submitted. <br/>For any changes mail at support@bribooks.com.');
					}

					$state_id = !empty($addresses['state_id'])
						? $addresses['state_id']
						: (!empty($info['state_id']) ? $info['state_id'] : 0);

					$city_id = !empty($addresses['city_id'])
						? $addresses['city_id']
						: (!empty($info['city_id']) ? $info['city_id'] : 0);

					$state_info = $this->state_model->get($state_id);
					$city_info 	= $this->city_model->get($city_id);

                    $address_data = [
                        'school_id'       		=> $addresses['school_id'] ?? 0,
                        'site_id'       		=> $addresses['site_id'] ?? 0,
                        'name'      			=> $addresses['name'] ?? ucwords($info['name']),
                        'authorized_person'     => $addresses['authorized_person'] ?? ucwords($info['authorized_person']),
                        'email'     			=> $addresses['email'] ?? $info['owner_email'],
                        'mobile'    			=> $addresses['mobile'] ?? $info['owner_mobile'],
                        'address'   			=> $addresses['address'] ?? ($info['address'] ?? NULL),
                        'zipcode'   			=> $addresses['zipcode'] ?? ($info['zipcode'] ?? NULL),
                        'landmark'  			=> $addresses['landmark'] ?? ($info['landmark'] ?? NULL),
                        'city_id'      			=> $state_info['id'] ?? 0,
                        'state_id'      		=> $city_info['id'] ?? 0,
						'country'      			=> $state_info['country'] ?? NULL,
						'state'      			=> $state_info['name'] ?? NULL,
                        'city'      			=> $city_info['name'] ?? NULL,
                        'type'      			=> $addresses['type'] ?? NULL,
                    ];

                    $this->json['address'] = $address_data;
                } else {
                    $this->json['error'] = _li('Invalid url');
                }
            } else {
				$this->json['error'] = _li('Invalid url');
            }
		}
	}

	public function addSchoolAwardAddress() {

		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'required',
			'numeric',
		]);
		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
		]);
		// $this->form_validation->set_rules('school_id', _l('school_id'), [
		// 	'trim',
		// 	'required',
		// 	'numeric',
		// 	['school', [$this->validate_model, 'school']]
		// ]);
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[4]|max_length[128]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[10]|max_length[15]',[
			'min_length' => 'Please enter a valid 10 digit mobile number',
			'max_length' => 'Please enter a valid 15 digit mobile number'
		]);
        $this->form_validation->set_rules('email', _l('email'), 'trim|required');
		$this->form_validation->set_rules('address', _l('address'), 'trim|required|min_length[4]|max_length[255]');

		if (mb_strtolower($this->input->post('country')) !== 'india') {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|min_length[4]|max_length[10]');
		} else {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|numeric|exact_length[6]');
		}

		$this->form_validation->set_rules('landmark', _l('landmark'), 'trim');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[Office,Other,Home]');
		self::_runFormValidation();

		if (!$this->json) {
		    $this->load->model('school/SchoolAwardAddress_model', 'school_award_address_model');

			if (!empty($this->input->post('site_id'))) {
				$address_info = $this->school_award_address_model->get_all([
					'site_id'		=> (int)$this->input->post('site_id'),
				])['rows'][0] ?? [];
			} else if (!empty($this->input->post('school_id'))) {
				$address_info = $this->school_award_address_model->get_all([
					'school_id'		=> (int)$this->input->post('school_id'),
				])['rows'][0] ?? [];
			}

			if (!empty($address_info)) {
				$this->school_award_address_model->edit($address_info['id'] , [
					'state_id'				=> $this->input->post('state_id') ?? 0,
					'city_id'				=> $this->input->post('city_id') ?? 0,
					'name'	    			=> $this->input->post('name'),
					'authorized_person'	    => $this->input->post('authorized_person') ?? '',
					'mobile'				=> $this->input->post('mobile'),
					'email'	    			=> $this->input->post('email'),
					'zipcode'				=> $this->input->post('zipcode') ?? '',
					'address'				=> $this->input->post('address') ?? '',
					'landmark'				=> $this->input->post('landmark') ?? '',
					'type'					=> $this->input->post('type') ?? '',
					'delivery'				=> $this->input->post('delivery_date') ?? '',
					'slot'					=> $this->input->post('slot') ?? '',
					'status'				=> 1,
				]);
			} else {
				$this->school_award_address_model->add([
					'site_id'				=> $this->input->post('site_id') ?? 0,
					'state_id'				=> $this->input->post('state_id') ?? 0,
					'city_id'				=> $this->input->post('city_id') ?? 0,
					'name'	    			=> $this->input->post('name'),
					'authorized_person'	    => $this->input->post('authorized_person') ?? '',
					'mobile'				=> $this->input->post('mobile'),
					'email'	    			=> $this->input->post('email'),
					'zipcode'				=> $this->input->post('zipcode'),
					'address'				=> $this->input->post('address'),
					'landmark'				=> $this->input->post('landmark'),
					'type'					=> $this->input->post('type'),
					'delivery'				=> $this->input->post('delivery_date'),
					'slot'					=> $this->input->post('slot') ?? '',
					'status'				=> 1,
				]);
			}
			$this->json['success'] = _l('address_saved_successfully');
		}
	}
}
