<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Address {
	public function address($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'username',
			'name',
			'mobile',
			'type',
			'address',
			'landmark',
			'city',
			'state',
			'country',
			'zipcode',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->address_model->add([
				'user_id'	   	=> $this->input->post('user_id') ?? 0,
				'name'		  	=> $this->input->post('name') ?? '',
				'mobile'		=> $this->input->post('mobile') ?? '',
				'type'		  	=> $this->input->post('type') ?? '',
				'address'	   	=> $this->input->post('address') ?? '',
				'landmark'	  	=> $this->input->post('landmark') ?? '',
				'city_id'	   	=> $this->input->post('city_id') ?? 0,
				'state_id'	  	=> $this->input->post('state_id') ?? 0,
				'country_id'	=> $this->input->post('country_id') ?? 0,
				'city'		  	=> $this->city_model->get($this->input->post('city_id') ?? 0)['name'] ?? '',
				'state'		 	=> $this->state_model->get($this->input->post('state_id') ?? 0)['name'] ?? '',
				'country'	   	=> $this->country_model->get($this->input->post('country_id') ?? 0)['name'] ?? '',
				'zipcode'	   	=> $this->input->post('zipcode')
			]);
			$this->session->set_flashdata('flash_message', 'Address added successfully!');
			redirect(base_url('admin/address'), 'refresh');
		} elseif ($param1 == 'edit') {
			$address_info 							= $this->address_model->get($param2);

			$this->address_model->edit($param2, [
				'name'		  => $this->input->post('name')	   ?? $address_info['name'],
				'mobile'		=> $this->input->post('mobile')	 ?? $address_info['mobile'],
				'type'		  => $this->input->post('type')	   ?? $address_info['type'],
				'address'	   => $this->input->post('address')	?? $address_info['address'],
				'landmark'	  => $this->input->post('landmark')   ?? $address_info['landmark'],
				'city_id'	   => $this->input->post('city_id')	?? $address_info['city_id'],
				'state_id'	  => $this->input->post('state_id')   ?? $address_info['state_id'],
				'country_id'	=> $this->input->post('country_id') ?? $address_info['country_id'],
				'city'		  => $this->city_model->get($this->input->post('city_id') ?? 0)['name'] ?? $address_info['city'],
				'state'		 => $this->state_model->get($this->input->post('state_id') ?? 0)['name'] ?? $address_info['state'],
				'country'	   => $this->country_model->get($this->input->post('country_id') ?? 0)['name'] ?? $address_info['country'],
				'zipcode'	   => $this->input->post('zipcode') ?? $address_info['zipcode']
			]);
			redirect(base_url('admin/address'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->address_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/address'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->address_model->delete($param2);
			redirect(base_url('admin/address'), 'refresh');
		}

		$data['page_name'] 		= 'address/index';
		$data['page_title'] 	= _l('address');
		$data['action_add'] 	= base_url('admin/address_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_address');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/address_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/address/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/address/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function address_form($param1 = NULL, $param2 = NULL) {
		$user_info	  = [];
		$city_info	  = [];
		$state_info	 = [];
		$country_info   = [];
		$address_info   = [];

		if ($param1 == 'add') {
			$data['page_name'] 						= 'address/form';
			$data['page_title'] 					= _l('address_add');
			$data['action'] 						= base_url('admin/address/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'address/form';
			$data['page_title'] 					= _l('address_edit');
			$data['action'] 						= base_url('admin/address/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$address_info 							= $this->address_model->get($param2);
			$user_info 								= $this->user_model->get($address_info['user_id']);
			$city_info 								= $this->city_model->get($address_info['city_id']);
			$state_info 							= $this->state_model->get($address_info['state_id']);
			$country_info 							= $this->country_model->get($address_info['country_id']);

			if (empty($address_info['city_id']) || empty($address_info['state_id']) || empty($address_info['country_id'])) {
				$city_info 			= $this->city_model->get_all(['name' => trim($address_info['city'])])['rows'][0] ?? '';
				$state_info 		= $this->state_model->get_all(['name' => trim($address_info['state'])])['rows'][0] ?? '';
				$country_info 		= $this->country_model->get_all(['name' => trim($address_info['country'])])['rows'][0] ?? '';

				$address_info['country_id'] = $country_info['id'] ?? 0;
				$address_info['state_id'] 	= $state_info['id'] ?? 0;
				$address_info['city_id'] 	= $city_info['id'] ?? 0;
			}
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'user_id',
			'label'		=> _l('select_user'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['user_id'] ?? '',
				'label' => $user_info['first_name'] . ' ' . $user_info['last_name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_students'),
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
			'type'		=> 'select2',
			'key'		=> 'city_id',
			'label'		=> _l('select_city'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['city_id'] ?? '',
				'label' => $city_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_city'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'state_id',
			'label'		=> _l('select_state'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['state_id'] ?? '',
				'label' => $state_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_state'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_id',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['country_id'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'zipcode',
			'label'		=> _l('zipcode'),
			'required'	=> true,
			'value'		=> $address_info['zipcode'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_address() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->address_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info	  	= $this->user_model->get($result['user_id']);
			$city_info	  	= $this->city_model->get($result['city_id']);
			$state_info	 	= $this->state_model->get($result['state_id']);
			$country_info   = $this->country_model->get($result['country_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'username'				=> ucfirst($user_info['first_name'] . ' ' . $user_info['last_name']) ?? 'NA',
				'city'					=> $city_info['name'] ?? $result['city'],
				'state'					=> $state_info['name'] ?? $result['state'],
				'country'				=> $country_info['name'] ?? $result['country'],
				'name'					=> $result['name'],
				'mobile'				=> $result['mobile'],
				'type'					=> $result['type'],
				'address'				=> $result['address'],
				'landmark'				=> $result['landmark'],
				'zipcode'				=> $result['zipcode'],
				'status'				=> _sd($result['ship_status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
