<?php defined('BASEPATH') or exit('No direct script access allowed');

trait PickupLocations {
	public function pickup_locations($param1 = null,$param2 = null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'name',
			'contact_name',
			'mobile',
			'email',
			'address',
			'status',
			'actions',
		];

		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');

		if ($param1 == 'add') {
			$this->pickup_location_model->add($this->input->post());
			redirect(base_url('admin/pickup_locations'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->pickup_location_model->edit($param2, $this->input->post());
			redirect(base_url('admin/pickup_locations'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->pickup_location_model->delete($param2);
			redirect(base_url('admin/pickup_locations'), 'refresh');
		}

		$data['page_name'] 		= 'pickup_locations/index';
		$data['page_title'] 	= _l('pickup_locations');
		$data['action_add'] 	= base_url('admin/pickup_location_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_pickup_locations');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/pickup_location_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/pickup_locations/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function pickup_location_form($param1 = null, $param2 = null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');

		if ($param1 == 'add') {
			$data['page_name'] 						= 'pickup_locations/form';
			$data['page_title'] 					= _l('add_pickup_location');
			$data['action'] 						= base_url('admin/pickup_locations/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'pickup_locations/form';
			$data['page_title'] 					= _l('edit_pickup_location');
			$data['action'] 						= base_url('admin/pickup_locations/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 							        = $this->pickup_location_model->get($param2);
			$state_info 							= $this->state_model->get($info['state_id']);
			$city_info 								= $this->city_model->get($info['city_id']);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'contact_name',
			'label'		=> _l('contact_name'),
			'required'	=> true,
			'value'		=> $info['contact_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'telephone',
			'label'		=> _l('telephone'),
			'required'	=> true,
			'value'		=> $info['telephone'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'mobile',
			'label'		=> _l('mobile'),
			'required'	=> true,
			'value'		=> $info['mobile'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'email',
			'label'		=> _l('email'),
			'required'	=> true,
			'value'		=> $info['email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'address_1',
			'label'		=> _l('address_1'),
			'required'	=> true,
			'value'		=> $info['address_1'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'address_2',
			'label'		=> _l('address_2'),
			'required'	=> true,
			'value'		=> $info['address_2'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'address_3',
			'label'		=> _l('address_3'),
			'required'	=> true,
			'value'		=> $info['address_3'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'pickup_location_name',
			'label'		=> _l('pickup_location_name'),
			'required'	=> true,
			'value'		=> $info['pickup_location_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'state_id',
			'label'		=> _l('select_state'),
			'required'	=> true,
			'value'		=> [
				'value' => $state_info['id'] ?? '',
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
				'value' => $city_info['id'] ?? '',
				'label' => $city_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_city'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'pincode',
			'label'		=> _l('pincode'),
			'required'	=> true,
			'value'		=> $info['pincode'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'     => $info['status'] ?? 1,
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

	public function ajax_pickup_locations() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');

		$results = $this->pickup_location_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'name'					=> $result['name'],
				'contact_name'			=> $result['contact_name'],
				'mobile'			    => $result['mobile'],
				'email'			        => $result['email'],
				'contact_name'			=> $result['contact_name'],
				'address'				=> $result['address_1'] . ', ' . $result['address_2'] . ', ' . $result['address_3'],
				'status'			    => _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
