<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Schools {
	public function schools($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
            'id',
			'site_id',
			'name',
			'mobile',
			'email',
			'owner_name',
			'authorized_person',
			'site_type',
			'state',
			'city',
			'pincode',
			'tag',
			'designation',
			'verified',
			'actions',
		];

		if ($param1 == 'add') {

			if (!empty($validate_mesaage = self::_validateSchools($this->input->post()))) {
				$this->session->set_flashdata('error_message', $validate_mesaage);
				redirect(base_url('admin/schools'), 'refresh');
				return;
            }

            $country_info = $this->country_model->get($this->input->post('country_id'));

			if (!empty($country_info) && !empty($country_site_info = $this->site_model->getSiteByName($country_info['name']))) {
				$site_info = $this->site_model->get($country_site_info['id']);
			} else {
				$site_info = $this->site_model->get(1);
            }

			$insert_school_data = [
				'parent_id' 		  			=> 0,
				'site_id' 		  				=> $this->input->post('site_id') ?? 0,
				'name' 				  			=> trim($this->input->post('name')),
				'site_code' 		  			=> $site_info['site_code'] . '-add-' . uniqid(),
				'site_type' 		  			=> $this->input->post('site_type') ?? 1,
				'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
				'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
				'timezone' 			  			=> $site_info['timezone'] ?? '',
				'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
				'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
				'email_alert' 		  			=> $site_info['email_alert'] ?? '',
				'address' 			  			=> $this->input->post('address') ?? '',
				'landmark' 			  			=> $this->input->post('landmark') ?? '',
				'pincode' 			  			=> $this->input->post('zipcode') ?? '',
				'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
				'country_code' 		  			=> $site_info['country_code'] ?? '',
				'currency_code' 	  			=> $site_info['currency_code'] ?? '',
				'state_id' 			  			=> $this->input->post('state_id') ?? 0,
				'city_id' 			  			=> $this->input->post('city_id') ?? 0,
				'base_price' 		  			=> $site_info['base_price'] ?? 0,
				'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
				'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
				'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
				'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
				'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
				'tax' 				  			=> $site_info['tax'] ?? '',
				'tax_text' 			  			=> $site_info['tax_text'] ?? '',
				'owner_name' 	      			=> $this->input->post('owner_name') ?? '',
				'authorized_person'   			=> $this->input->post('authorized_person') ?? '',
				'owner_email' 		  			=> $this->input->post('email') ?? '',
				'owner_mobile' 	      			=> $this->input->post('mobile') ?? '',
				'alternate_authorized_person'   => $this->input->post('alternate_authorized_person') ?? '',
				'alternate_owner_email' 		=> $this->input->post('alternate_email') ?? '',
				'alternate_owner_mobile' 	    => $this->input->post('alternate_mobile') ?? '',
				'tag' 			  				=> $this->input->post('tag') ?? '',
				'designation' 			  		=> $this->input->post('designation') ?? '',
				'status' 			  			=> 1,
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
			];

			$school_id = $this->school_model->add($insert_school_data);

			if (!empty($school_id)) {
				$this->school_model->edit($school_id, [
					'site_code' => get_site_code_slug(trim($this->input->post('name'))) . '-' . $school_id
				]);
			}
            $this->session->set_flashdata('flash_message', 'Schools added successfully!');
			redirect(base_url('admin/schools'), 'refresh');
		} elseif ($param1 == 'edit') {

			$_POST['school_id'] = $param2;

			if (!empty($validate_mesaage = self::_validateSchools($this->input->post()))) {
				$this->session->set_flashdata('error_message', $validate_mesaage);
				redirect(base_url('admin/schools'), 'refresh');
				return;
            }

			$school_info 							= $this->school_model->get($param2);

			$this->school_model->edit($param2, [
                'name'                          => $this->input->post('name')                           ?? $school_info['name'],
                'owner_mobile'                  => $this->input->post('mobile')                         ?? $school_info['owner_mobile'],
                'owner_email'                   => $this->input->post('email')                          ?? $school_info['owner_email'],
                'owner_name'                    => $this->input->post('owner_name')                     ?? $school_info['owner_name'],
                'authorized_person'             => $this->input->post('authorized_person')              ?? $school_info['authorized_person'],
                'alternate_owner_mobile'        => $this->input->post('alternate_mobile')               ?? $school_info['alternate_owner_mobile'],
                'alternate_owner_email'         => $this->input->post('alternate_email')                ?? $school_info['alternate_owner_email'],
                'alternate_authorized_person'   => $this->input->post('alternate_authorized_person')    ?? $school_info['alternate_authorized_person'],
                'site_type'                     => $this->input->post('site_type')                      ?? $school_info['site_type'],
                'address'                       => $this->input->post('address')                        ?? $school_info['address'],
                'landmark'                      => $this->input->post('landmark')                       ?? $school_info['landmark'],
                'pincode'                       => $this->input->post('zipcode')                        ?? $school_info['pincode'],
                'city_id'                       => $this->input->post('city_id')                        ?? $school_info['city_id'],
                'state_id'                      => $this->input->post('state_id')                       ?? $school_info['state_id'],
                'tag'                           => $this->input->post('tag')                            ?? $school_info['tag'],
                'designation'                   => $this->input->post('designation')                    ?? $school_info['designation'],
            ]);

            $this->session->set_flashdata('flash_message', 'Schools updated successfully!');
			redirect(base_url('admin/schools'), 'refresh');
		} elseif ($param1 == 'status') {
			
			redirect(base_url('admin/schools'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->school_model->delete($param2);
			redirect(base_url('admin/schools'), 'refresh');
		}

		$data['page_name'] 		= 'schools/index';
		$data['page_title'] 	= _l('schools');
		$data['action_add'] 	= base_url('admin/school_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_schools');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/school_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/schools/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/schools/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function school_form($param1 = NULL, $param2 = NULL) {
        $city_info      = [];
        $state_info     = [];
        $country_info   = [];
        $school_info   = [];

		if ($param1 == 'add') {
			$data['page_name'] 						= 'schools/form';
			$data['page_title'] 					= _l('school_add');
			$data['action'] 						= base_url('admin/schools/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'schools/form';
			$data['page_title'] 					= _l('school_edit');
			$data['action'] 						= base_url('admin/schools/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$school_info 							= $this->school_model->get($param2);
			$city_info 								= $this->city_model->get($school_info['city_id']);
			$state_info 							= $this->state_model->get($school_info['state_id']);
			$country_info 							= $this->country_model->get_all([
                'code' => $school_info['country_code']
            ])['rows'][0] ?? '';
		}

        $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_id',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> [
				'value' => $country_info['id'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'state_id',
			'label'		=> _l('select_state'),
			'required'	=> true,
			'value'		=> [
				'value' => $school_info['state_id'] ?? '',
				'label' => $state_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_state'),
		];

        $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'city_id',
			'label'		=> _l('select_city'),
			'required'	=> true,
			'value'		=> [
				'value' => $school_info['city_id'] ?? '',
				'label' => $city_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_city'),
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'site_id',
			'label'		=> _l('site_id'),
			'required'	=> false,
			'value'		=> $school_info['site_id'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_site_type'),
			'required'	=> true,
			'value'		=> $school_info['site_type'] ?? _l('school'),
			'options'	=> [
				[
					'label'	=> _l('school'),
					'value'	=> _l('1'),
				],
				[
					'label'	=> _l('Nursery'),
					'value'	=> _l('2'),
				],
				[
					'label'	=> _l('parent_school_chain'),
					'value'	=> _l('3'),
				],
                [
					'label'	=> _l('community'),
					'value'	=> _l('4'),
				],
				[
					'label'	=> _l('primary_school'),
					'value'	=> _l('5'),
				],
				[
					'label'	=> _l('secondary_school'),
					'value'	=> _l('6'),
				],
                [
					'label'	=> _l('parent_site'),
					'value'	=> _l('7'),
				],
			],
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('school_name'),
			'required'	=> true,
			'value'		=> $school_info['name'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'owner_name',
			'label'		=> _l('owner_name'),
			'required'	=> false,
			'value'		=> $school_info['owner_name'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'authorized_person',
			'label'		=> _l('authorized_person'),
			'required'	=> false,
			'value'		=> $school_info['authorized_person'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'mobile',
			'label'		=> _l('mobile'),
			'required'	=> true,
			'value'		=> $school_info['owner_mobile'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'email',
			'label'		=> _l('email'),
			'required'	=> true,
			'value'		=> $school_info['owner_email'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'alternate_authorized_person',
			'label'		=> _l('alternate_authorized_person'),
			'required'	=> false,
			'value'		=> $school_info['alternate_authorized_person'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'alternate_mobile',
			'label'		=> _l('alternate_mobile'),
			'required'	=> false,
			'value'		=> $school_info['alternate_owner_mobile'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'alternate_email',
			'label'		=> _l('alternate_email'),
			'required'	=> false,
			'value'		=> $school_info['alternate_owner_email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'address',
			'label'		=> _l('address'),
			'required'	=> false,
			'value'		=> $school_info['address'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'landmark',
			'label'		=> _l('landmark'),
			'required'	=> false,
			'value'		=> $school_info['landmark'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'zipcode',
			'label'		=> _l('zipcode'),
			'required'	=> false,
			'value'		=> $school_info['pincode'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'tag',
			'label'		=> _l('tag'),
			'required'	=> false,
			'value'		=> $school_info['tag'] ?? 'unverified',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'designation',
			'label'		=> _l('designation'),
			'required'	=> false,
			'value'		=> $school_info['designation'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_schools() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->school_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$city_info      = $this->city_model->get($result['city_id']);
			$state_info     = $this->state_model->get($result['state_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'site_id'				=> $result['site_id'] ?? 0,
				'name'					=> $result['name'],
				'mobile'				=> $result['owner_mobile'] ?? '',
				'email'				    => $result['owner_email'] ?? '',
				'owner_name'			=> $result['owner_name'] ?? '',
				'authorized_person'	    => $result['authorized_person'] ?? '',
				'site_type'				=> $result['site_type'] ?? 0,
				'state'					=> $state_info['name'] ?? $result['state_id'],
				'city'					=> $city_info['name'] ?? $result['city_id'],
				'pincode'				=> $result['pincode'] ?? '',
				'tag'				    => $result['tag'] ?? '',
				'designation'			=> $result['designation'] ?? '',
				'verified'				=> _sd($result['verified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateSchools($data = []) {
		if (!empty($data['site_id'])) {
			if (!empty($data['email']) && !empty($user_email_info = $this->user_model->get_all([
				'email'                 => $data['email'],
			])['rows'][0] ?? '') && ($user_email_info['site_id'] != $data['site_id'] || $user_email_info['role_id'] != 9)) {
				return _li('This_email_is_already_registered_as_user');
			}

			if (!empty($data['mobile']) && !empty($user_mobile_info = $this->user_model->get_all([
				'mobile'                 => $data['mobile'],
			])['rows'][0] ?? '') && ($user_mobile_info['site_id'] != $data['site_id'] || $user_mobile_info['role_id'] != 9)) {
				return _li('This_mobile_is_already_registered_as_user');
			}
		} else {
			if (!empty($data['email']) && !empty($user_email_info = $this->user_model->get_all([
				'email'                 => $data['email'],
			])['rows'][0] ?? '')) {
				return _li('This_email_is_already_registered_as_user');
			}

			if (!empty($data['mobile']) && !empty($user_mobile_info = $this->user_model->get_all([
				'mobile'                 => $data['mobile'],
			])['rows'][0] ?? '')) {
				return _li('This_mobile_is_already_registered_as_user');
			}
		}

        if (!empty($data['email']) && !empty($site_info = $this->site_model->get_all([
			'owner_email'           => $data['email'],
			'site_id_ne'            => !empty($data['site_id']) ? $data['site_id'] : 0,
		])['rows'][0] ?? '')) {
			return _li('Your_email_is_already_registered__as_site');
		}

		if (!empty($data['mobile']) && !empty($site_info = $this->site_model->get_all([
			'owner_mobile'          => $data['mobile'],
			'site_id_ne'            => !empty($data['site_id']) ? $data['site_id'] : 0,
		])['rows'][0] ?? '')) {
			return _li('Your_mobile_is_already_registered_as_site');;
		}

		if (!empty($data['email']) && !empty($school_info = $this->school_model->get_all([
			'owner_email'           => $data['email'],
			'not_school_id'         => !empty($data['school_id']) ? $data['school_id'] : 0,
		])['rows'][0] ?? '')) {
			return _li('Your_email_is_already_registered__as_school');
		}

		if (!empty($data['mobile']) && !empty($site_info = $this->school_model->get_all([
			'owner_mobile'          => $data['mobile'],
			'not_school_id'         => !empty($data['school_id']) ? $data['school_id'] : 0,
		])['rows'][0] ?? '')) {
			return _li('Your_mobile_is_already_registered__as_school');;
		}
	}
}
