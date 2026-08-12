<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolAwardsAddress {
	public function school_award_address($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'state',
			'city',
			'site',
			'name',
			'type',
			'mobile',
			'email',
			'zipcode',
			'address',
			'landmark',
			'delivery_date',
			'ship_status',
			'date_shipped',
			'actions',
		];

		if ($param1 == 'add') {

            if (!empty($this->school_award_address_model->get_all([
                'site_id' => $this->input->post('site_id')
            ])['rows'][0] ?? '')) {
                $this->session->set_flashdata('error_message', 'Address for this site is already added!');
            } else {
                $this->school_award_address_model->add($this->input->post());
                $this->session->set_flashdata('flash_message', 'Address added successfully!');
            }
			redirect(base_url('admin/school_award_address'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->school_award_address_model->edit($param2, $this->input->post());
			redirect(base_url('admin/school_award_address'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->school_award_address_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/school_award_address'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->school_award_address_model->delete($param2);
			redirect(base_url('admin/school_award_address'), 'refresh');
		}

		$data['page_name'] 		= 'award/school_index';
		$data['page_title'] 	= _l('school_award_address');
		$data['action_add'] 	= base_url('admin/school_award_address_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_school_award_address');
		$data['action_export'] 	= base_url('admin/export_school_award_address');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/school_award_address_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/school_award_address/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/school_award_address/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function school_award_address_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'award/school_form';
			$data['page_title'] 					= _l('school_award_address_add');
			$data['action'] 						= base_url('admin/school_award_address/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'award/school_form';
			$data['page_title'] 					= _l('school_award_address_edit');
			$data['action'] 						= base_url('admin/school_award_address/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$address_info 							= $this->school_award_address_model->get($param2);
			$state_info 							= $this->state_model->get($address_info['state_id']);
			$city_info 								= $this->city_model->get($address_info['city_id']);
			$site_info 								= $this->site_model->get($address_info['site_id']);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'state_id',
			'label'		=> _l('select_state'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['state_id'] ?? '',
				'label' => $state_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_state'),
		];

        $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'city_id',
			'label'		=> _l('select_city'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['city_id'] ?? '',
				'label' => $city_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_city'),
		];

        $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'site_id',
			'label'		=> _l('select_site'),
			'required'	=> true,
			'value'		=> [
				'value' => $address_info['site_id'] ?? '',
				'label' => $site_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_sites'),
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
			'key'		=> 'email',
			'label'		=> _l('email'),
			'required'	=> true,
			'value'		=> $address_info['email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'zipcode',
			'label'		=> _l('zipcode'),
			'required'	=> true,
			'value'		=> $address_info['zipcode'] ?? '',
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
			'key'		=> 'delivery',
			'label'		=> _l('delivery_date'),
			'required'	=> true,
			'value'		=> $address_info['delivery_date'] ?? '',
			'options'	=> [
				[
					'label'	=> '10th June to 16th June 2024',
					'value'	=> '10th June to 16th June 2024',
				],
				[
					'label'	=> '17th June to 23rd June 2024',
					'value'	=> '17th June to 23rd June 2024',
				],
				[
					'label'	=> '24th June to 30th June 2024',
					'value'	=> '24th June to 30th June 2024',
				],
			],
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

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'ship_status',
			'label'		=> _l('select_ship_status'),
			'required'	=> true,
			'value'		=> $address_info['ship_status'] ?? 1,
			'options'	=> [
				[
					'label'	=> _l('shipped'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('not_shipped'),
					'value'	=> 0,
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_school_award_address() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('ship_status') == 0 || $this->input->get('ship_status') == 1) {
			$filter_data['ship_status'] = $this->input->get('ship_status');
		}

		$results = $this->school_award_address_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$site_info  = $this->site_model->get($result['site_id']);
			$state_info = $this->state_model->get($result['state_id']);
			$city_info  = $this->city_model->get($result['city_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'state'					=> $state_info['name'] ?? 'NA',
				'city'					=> $city_info['name'] ?? 'NA',
				'site'					=> $site_info['name'] ?? 'NA',
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'mobile'				=> $result['mobile'],
				'email'					=> $result['email'],
				'zipcode'				=> $result['zipcode'],
				'address'				=> $result['address'],
				'landmark'				=> $result['landmark'],
				'delivery_date'			=> $result['delivery'],
				'ship_status'			=> _sd($result['ship_status']),
				'date_shipped'			=> formatDate($result['date_shipped']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function export_school_award_address() {
		$json = [];

		$results = $this->school_award_address_model->get_all([
			'ship_status' => 0
		])['rows'] ?? [];

		$awards = [];

		foreach ($results as $result) {

			if (!empty($result['name']) && !empty($result['email']) && !empty($result['mobile'])) {
				$site_info  = $this->site_model->get($result['site_id']);
				$state_info = $this->state_model->get($result['state_id']);
				$city_info  = $this->city_model->get($result['city_id']);
	
				$awards[] = [
					'request_id'		=> $result['id'],
					'site_id'			=> $result['site_id'],
					'state_id'			=> $result['state_id'],
					'city_id'			=> $result['city_id'],
					'state'		        => $state_info['name'] ?? "",
					'city'		        => $city_info['name'] ?? "",
					'school_name'		=> $site_info['name'] ?? "",
					'name'				=> $result['name'] ?? '',
					'email'				=> $result['email'] ?? '',
					'mobile'			=> $result['mobile'] ?? '',
					'address'			=> $result['address'] ?? '',
					'zipcode'			=> $result['zipcode'] ?? '',
					'landmark'			=> $result['landmark'] ?? '',
					'delivery_date'		=> $result['delivery'] ?? ''
				];
	
				$this->school_award_address_model->edit($result['id'], [
					'ship_status' 	=> 1,
					'date_shipped' 	=> date('Y-m-d H:i:s')
				]);
			}
		}

		self::_downloadCsv($awards, 'school_awards_address');

		output_json($json);
	}
}
