<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait UserAwardsAddress {
	public function award_address($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'user',
			'name',
			'type',
			'mobile',
			'email',
			'zipcode',
			'address',
			'landmark',
			'ship_status',
			'date_shipped',
			'actions',
		];

		if ($param1 == 'add') {
			$this->user_award_address_model->add($this->input->post());
			redirect(base_url('admin/award_address'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->user_award_address_model->edit($param2, $this->input->post());
			redirect(base_url('admin/award_address'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->user_award_address_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/award_address'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->user_award_address_model->delete($param2);
			redirect(base_url('admin/award_address'), 'refresh');
		}

		$events = $this->event_model->get_all([
			'start' => 9
		])['rows'] ?? [];

		$data['page_name'] 		= 'award/index';
		$data['events'] 		= $events;
		$data['page_title'] 	= _l('award_address');
		$data['action_add'] 	= base_url('admin/award_address_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_award_address');
		$data['action_export'] 	= base_url('admin/export_award_address');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/award_address_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/award_address/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/award_address/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function award_address_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'award/form';
			$data['page_title'] 					= _l('award_address_add');
			$data['action'] 						= base_url('admin/award_address/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'award/form';
			$data['page_title'] 					= _l('award_address_edit');
			$data['action'] 						= base_url('admin/award_address/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$address_info 							= $this->user_award_address_model->get($param2);
			$user_info 								= $this->user_model->get($address_info['user_id']);
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

	public function ajax_award_address() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('ship_status') == '0' || $this->input->get('ship_status') == '1') {
			$filter_data['ship_status'] = $this->input->get('ship_status');
		}

		if (!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}
		
		$results = $this->user_award_address_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'user'					=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'mobile'				=> $result['mobile'],
				'email'					=> $result['email'],
				'zipcode'				=> $result['zipcode'],
				'address'				=> $result['address'],
				'landmark'				=> $result['landmark'],
				'ship_status'			=> _sd($result['ship_status']),
				'date_shipped'			=> formatDate($result['date_shipped']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function export_award_address() {
		$json = [];

		$filter_data = [
			'ship_status' => '0'
		];

		if (!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->user_award_address_model->get_all($filter_data)['rows'] ?? [];

		$awards = [];

		foreach ($results as $result) {

			if (!empty($result['name']) && !empty($result['email']) && !empty($result['mobile'])) {
				$user_info = $this->user_model->get($result['user_id']);
	
				$awards[] = [
					'request_id'		=> $result['id'],
					'event_id'			=> $result['event_id'],
					'user_id'			=> $result['user_id'],
					'user_name'			=> !empty($user_info) ? ($user_info['first_name'] . " " . $user_info['last_name']) : "",
					'name'				=> $result['name'] ?? '',
					'email'				=> $result['email'] ?? '',
					'mobile'			=> $result['mobile'] ?? '',
					'address'			=> $result['address'] ?? '',
					'zipcode'			=> $result['zipcode'] ?? '',
					'landmark'			=> $result['landmark'] ?? ''
				];
	
				$this->user_award_address_model->edit($result['id'], [
					'ship_status' 	=> 1,
					'date_shipped' 	=> date('Y-m-d H:i:s')
				]);
			}

		}

		self::_downloadCsv($awards, 'awards_address');

		output_json($json);
	}
}
