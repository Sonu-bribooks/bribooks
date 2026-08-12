<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MedallionAddress {
	public function medallion_address($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'user',
			'name',
			'type',
			'mobile',
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
			self::_validateMedallionAddressForm();

			$this->medallion_address_model->add($this->input->post());
			redirect(base_url('admin/medallion_address'), 'refresh');
		} elseif ($param1 == 'edit') {
			self::_validateMedallionAddressForm($param2);

			$this->medallion_address_model->edit($param2, $this->input->post());
			redirect(base_url('admin/medallion_address'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->medallion_address_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/medallion_address'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->medallion_address_model->delete($param2);
			redirect(base_url('admin/medallion_address'), 'refresh');
		}

		$data['page_name'] 		= 'medallion/address/index';
		$data['page_title'] 	= _l('medallion_address');
		$data['action_add'] 	= base_url('admin/medallion_address_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_medallion_address');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/medallion_address_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/medallion_address/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/medallion_address/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function medallion_address_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'medallion/address/form';
			$data['page_title'] 					= _l('medallion_address_add');
			$data['action'] 						= base_url('admin/medallion_address/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'medallion/address/form';
			$data['page_title'] 					= _l('medallion_address_edit');
			$data['action'] 						= base_url('admin/medallion_address/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$address_info 							= $this->medallion_address_model->get($param2);
			$user_info 								= $this->user_model->get($address_info['user_id']);
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
			'key'		=> 'user_id',
			'label'		=> _l('select_user'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['user_id'] ?? '',
				'label' => $user_info['first_name'] . ' ' . $user_info['last_name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_students'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_address_type'),
			'required'	=> true,
			'value'		=> $address_info['type'] ?? _l('home'),
			'options'	=> [
				[
					'label'	=> _l('home'),
					'value'	=> _l('home'),
				],
				[
					'label'	=> _l('office'),
					'value'	=> _l('office'),
				],
				[
					'label'	=> _l('other'),
					'value'	=> _l('other'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $address_info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'mobile',
			'label'		=> _l('mobile'),
			'required'	=> true,
			'value'		=> $address_info['mobile'] ?? '',
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

	public function ajax_medallion_address() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->medallion_address_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'user'					=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'mobile'				=> $result['mobile'],
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

	private function _validateMedallionAddressForm($id = 0) {
		$country_info = $this->country_model->get($this->input->post('country'));
		$_POST['country'] = $country_info['name'];

		$state_info = $this->state_model->get($this->input->post('state'));
		$_POST['state'] = $state_info['name'];

		$city_info = $this->city_model->get($this->input->post('city'));
		$_POST['city'] = $city_info['name'];

		if (!empty($address_info = $this->medallion_address_model->get_all([
			'user_id'	=> (int)$this->input->post('user_id')
		])['rows'][0] ?? []) && $address_info['id'] != $id) {
			$this->session->set_flashdata('error_message', _l('user_has_already_medallion_address'));
			redirect(base_url('admin/medallion'), 'refresh');
		}
	}
}
