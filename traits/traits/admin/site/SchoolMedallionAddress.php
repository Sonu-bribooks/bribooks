<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolMedallionAddress {
	public function school_medallion_address($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'school_name',
			'type',
			'coordinator_mobile',
			'leader_mobile',
			'zipcode',
			'address',
			'landmark',
			'city',
			'state',
			'country',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			self::_validateSchoolMedallionAddressForm();

			$this->school_medallion_address_model->add($this->input->post());
			redirect(base_url('admin/school_medallion_address'), 'refresh');
		} elseif ($param1 == 'edit') {
			self::_validateSchoolMedallionAddressForm($param2);

			$this->school_medallion_address_model->edit($param2, $this->input->post());
			redirect(base_url('admin/school_medallion_address'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->school_medallion_address_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/school_medallion_address'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->school_medallion_address_model->delete($param2);
			redirect(base_url('admin/school_medallion_address'), 'refresh');
		}

		$data['page_name'] 		= 'medallion/address/index';
		$data['page_title'] 	= _l('school_medallion_address');
		$data['action_add'] 	= base_url('admin/school_medallion_address_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_school_medallion_address');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/school_medallion_address_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/school_medallion_address/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/school_medallion_address/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function school_medallion_address_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'medallion/address/form';
			$data['page_title'] 					= _l('school_medallion_address_add');
			$data['action'] 						= base_url('admin/school_medallion_address/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'medallion/address/form';
			$data['page_title'] 					= _l('school_medallion_address_edit');
			$data['action'] 						= base_url('admin/school_medallion_address/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$address_info 							= $this->school_medallion_address_model->get($param2);
			$site_info 								= $this->site_model->get($address_info['site_id']);
			$country_info 							= $this->country_model->get_all([
				'name' 			=> $address_info['country']
			])['rows'][0] ?? [];
			$state_info 							= $this->state_model->get_all([
				'name' 			=> $address_info['state'],
				'country_id' 	=> $country_info['id'],
			])['rows'][0] ?? [];
			$city_info 								= $this->city_model->get_all([
				'name' 			=> $address_info['city'],
				'state_id' 		=> $state_info['id'],
			])['rows'][0] ?? [];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'site_id',
			'label'		=> _l('select_site'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['site_id'] ?? '',
				'label' => $site_info['name'] . ' ' . $site_info['authorized_person'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_sites'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'coordinator_name',
			'label'		=> _l('coordinator_name'),
			'required'	=> true,
			'value'		=> $address_info['coordinator_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'leader_name',
			'label'		=> _l('leader_name'),
			'required'	=> true,
			'value'		=> $address_info['leader_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'coordinator_mobile',
			'label'		=> _l('coordinator_mobile'),
			'required'	=> true,
			'value'		=> $address_info['coordinator_mobile'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'leader_mobile',
			'label'		=> _l('leader_mobile'),
			'required'	=> true,
			'value'		=> $address_info['leader_mobile'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'leader_email',
			'label'		=> _l('leader_email'),
			'required'	=> true,
			'value'		=> $address_info['leader_email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'coordinator_email',
			'label'		=> _l('coordinator_email'),
			'required'	=> true,
			'value'		=> $address_info['coordinator_email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'leader_designation',
			'label'		=> _l('select_leader_designation'),
			'required'	=> true,
			'value'		=> $address_info['leader_designation'] ?? '',
			'options'	=> [
				[
					'label' => _l('principal'),
					'value' => _l('principal'),
				],
				[
					'label' => _l('director'),
					'value' => _l('director'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'coordinator_designation',
			'label'		=> _l('select_coordinator_designation'),
			'required'	=> true,
			'value'		=> $address_info['coordinator_designation'] ?? '',
			'options'	=> [
				[
					'label' => _l('coordinator'),
					'value' => _l('coordinator'),
				],
				[
					'label' => _l('librarian'),
					'value' => _l('librarian'),
				],
				[
					'label' => _l('english_hod'),
					'value' => _l('english_hod'),
				],
				[
					'label' => _l('vice_principal'),
					'value' => _l('vice_principal'),
				],
				[
					'label' => _l('others'),
					'value' => _l('others'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'zipcode',
			'label'		=> _l('zipcode'),
			'required'	=> true,
			'value'		=> $address_info['zipcode'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> [
				'value' => $country_info['id'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'state',
			'label'		=> _l('state'),
			'required'	=> true,
			'value'		=> [
				'value' => $state_info['id'] ?? '',
				'label' => $state_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_state'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'city',
			'label'		=> _l('city'),
			'required'	=> true,
			'value'		=> [
				'value' => $city_info['id'] ?? '',
				'label' => $city_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_city'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'address',
			'label'		=> _l('address'),
			'required'	=> true,
			'value'		=> $address_info['address'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'landmark',
			'label'		=> _l('landmark'),
			'required'	=> false,
			'value'		=> $address_info['landmark'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $address_info['status'] ?? 1,
			'options'	=> [
				[
					'label'	=> _l('enable'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('disable'),
					'value'	=> 0,
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_school_medallion_address() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->school_medallion_address_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$site_info = $this->site_model->get($result['site_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'school_name'			=> $result['school_name'],
				'type'					=> $result['type'],
				'coordinator_mobile'	=> $result['coordinator_mobile'],
				'leader_mobile'			=> $result['leader_mobile'],
				'zipcode'				=> $result['zipcode'],
				'address'				=> $result['address'],
				'landmark'				=> $result['landmark'],
				'city'					=> $result['city'],
				'state'					=> $result['state'],
				'country'				=> $result['country'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateSchoolMedallionAddressForm($id = 0) {
		$site_info = $this->site_model->get($this->input->post('site_id'));
		$_POST['school_name'] = $site_info['name'];
		
		$country_info = $this->country_model->get($this->input->post('country'));
		$_POST['country'] = $country_info['name'];

		$state_info = $this->state_model->get($this->input->post('state'));
		$_POST['state'] = $state_info['name'];

		$city_info = $this->city_model->get($this->input->post('city'));
		$_POST['city'] = $city_info['name'];

		if (!empty($address_info = $this->school_medallion_address_model->get_all([
			'user_id'	=> (int)$this->input->post('user_id')
		])['rows'][0] ?? []) && $address_info['id'] != $id) {
			$this->session->set_flashdata('error_message', _l('user_has_already_school_medallion_address'));
			redirect(base_url('admin/medallion'), 'refresh');
		}
	}
}
