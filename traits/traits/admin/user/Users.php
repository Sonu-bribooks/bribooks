<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Users {
	public function department($param1 = '', $param2 = '') {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->department_model->add($this->input->post());
			redirect(base_url('admin/department'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->department_model->edit($param2, $this->input->post());
			redirect(base_url('admin/department'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->department_model->enableDisable($param2);
			redirect(base_url('admin/department'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->department_model->delete($param2);
			redirect(base_url('admin/department'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('department');

		$data['action_add'] 	= base_url('admin/department_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_department');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/department_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/department/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/department/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function department_form($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_department');
			$data['action'] 						= base_url('admin/department/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('department');
			$data['action'] 						= base_url('admin/department/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->department_model->get($param2);
			$department_users 						= array_map(fn($item) => [
				'label'	=> $item['name'],
				'value'	=> $item['user_id'],
			], $this->department_model->getUsers($info['id']));
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'multi_select2',
			'key'		=> 'user_ids',
			'label'		=> _l('select_users'),
			'required'	=> false,
			'value'		=> $department_users ?? [],
			'ajax_url'	=> base_url('admin/ajax_search_system_users'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('enabled'),
				],
				[
					'value' => 0,
					'label' => _l('disabled'),
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_department() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->department_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function roles($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'type',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->role_model->add($this->input->post());
			redirect(base_url('admin/roles'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->role_model->edit($param2, $this->input->post());
			redirect(base_url('admin/roles'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->role_model->enableDisable($param2);
			redirect(base_url('admin/roles'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->role_model->delete($param2);
			redirect(base_url('admin/roles'), 'refresh');
		}

		$data['page_name'] 		= 'role/index';
		$data['page_title'] 	= _l('role');

		$data['action_add'] 	= base_url('admin/role_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_roles');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/role_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/roles/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/roles/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function role_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'role/form';

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('role_add');
			$data['action'] 		= base_url('admin/roles/add');
		} elseif ($param1 == 'edit') {
			$data['page_title'] 	= _l('role_edit');
			$data['country_id'] 	= (int)$param2;
			$data['action'] 		= base_url('admin/roles/edit/' . (int)$param2);
			$data['details'] 		= $this->role_model->get($param2);
		}

		$data['types'] = ['admin', 'printingPress', 'telecaller','dropShipper'];

		$sort_order = [];

		foreach ((new ReflectionClass('Admin'))->getMethods(ReflectionMethod::IS_PUBLIC) as $item) {
			if ($item->name == '__construct') continue;
			if (strpos($item->getFileName(), APPPATH) === false) continue;

			$key = basename($item->getFileName(), '.php');

			$data['permissions']['admin'][$key][] = $item->name;

			if (!in_array($key, $sort_order)) {
				$sort_order[] = $key;
			}
		}

		array_multisort($sort_order, $data['permissions']['admin']);

		// $data['permissions']['admin'] = array_filter(array_map(function ($item) {
		// 	if (!preg_match('/[a-z]+?[A-Z].+?/', $item->name)) return null;
		//
		// 	return $item->getFileName() . ':' . $item->name;
		// }, (new ReflectionClass('Admin'))->getMethods(ReflectionMethod::IS_PUBLIC)), function ($item) {
		// 	return !empty($item);
		// });
		//
		// sort($data['permissions']['admin']);

		// load_controller('Telecaller');
		//
		// $data['permissions']['logs'] = array_filter(array_map(function ($item) {
		// 	return 'Telecaller:' . $item->name;
		// }, (new ReflectionClass('Log'))->getMethods(ReflectionMethod::IS_PUBLIC)), function ($item) {
		// 	return $item !== 'Telecaller:__construct';
		// });
		//
		// sort($data['permissions']['telecaller']);

		// load_controller('PrintingPress');
		//
		// $data['permissions']['printing_press'] = array_filter(array_map(function ($item) {
		// 	return 'PrintingPress:' . $item->name;
		// }, (new ReflectionClass('Log'))->getMethods(ReflectionMethod::IS_PUBLIC)), function ($item) {
		// 	return $item !== 'PrintingPress:__construct';
		// });
		//
		// sort($data['permissions']['printing_press']);

		// load_controller('OfficeManager');
		//
		// $data['permissions']['office_manager'] = array_filter(array_map(function ($item) {
		// 	return 'OfficeManager:' . $item->name;
		// }, (new ReflectionClass('Log'))->getMethods(ReflectionMethod::IS_PUBLIC)), function ($item) {
		// 	return $item !== 'OfficeManager:__construct';
		// });
		//
		// sort($data['permissions']['office_manager']);

		// pr($data['permissions'], 1);

		$this->load->view('backend/index', $data);
	}

	public function ajax_roles() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->role_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function admins($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'email',
			'mobile',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			unset($_POST['files']);
			$this->admin_model->add($this->input->post());
			redirect(base_url('admin/admins'), 'refresh');
		} elseif ($param1 == 'edit') {
			unset($_POST['files']);
			$this->admin_model->edit($param2, $this->input->post());
			redirect(base_url('admin/admins'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->admin_model->enableDisable($param2);
			redirect(base_url('admin/admins'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->admin_model->delete($param2);
			redirect(base_url('admin/admins'), 'refresh');
		}

		$data['page_name'] 		= 'admin/index';
		$data['page_title'] 	= _l('admin');

		$data['action_add'] 	= base_url('admin/admin_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_admins');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/admin_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/admins/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/admins/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function admin_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] = 'admin/form';

		if ($param1 == 'add') {
			$data['page_title'] = _l('admin_add');
			$data['action'] 	= base_url('admin/admins/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 	= $this->admin_model->get($param2);
			$data['page_title'] = _l('admin_edit');
			$data['action'] 	= base_url('admin/admins/edit/' . (int)$param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_admins() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->admin_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['first_name'] . ' ' . $result['last_name'],
				'email'					=> $result['email'],
				'mobile'				=> $result['mobile'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function dropshippers($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'email',
			'mobile',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$user_data['first_name'] 		= html_escape($this->input->post('first_name'));
			$user_data['last_name']  		= html_escape($this->input->post('last_name'));
			$user_data['biography']  		= html_escape($this->input->post('biography'));
			$user_data['email']  			= html_escape($this->input->post('email'));
			$user_data['alternate_email']  	= html_escape($this->input->post('alternate_email'));
			$user_data['mobile']  			= html_escape($this->input->post('mobile'));
			$user_data['role_id']  			= _dropshipper_role();
			$user_data['status']  			= 1;

			$id = $this->dropshipper_model->add($user_data);

			if (!empty($id)) {
				$pickup_data['state_ids'] 		    = html_escape(implode(',',$this->input->post('state_ids') ?? []));
				$pickup_data['bw_printer']  		= html_escape($this->input->post('bw_printer') ?? 0);
				$pickup_data['sort_order']  		= html_escape($this->input->post('sort_order'));
				$pickup_data['colored_limit']  	    = html_escape($this->input->post('colored_limit'));
				$pickup_data['bw_limit']  			= html_escape($this->input->post('bw_limit') ?? 0);
				$pickup_data['limit']  			    = html_escape($this->input->post('limit') ?? 0);
				$pickup_data['pickup_id']  			= html_escape($this->input->post('pickup_id'));
				$pickup_data['user_id']  	        = $id;

				if ($this->input->post('all_states') == 'all') {
					$pickup_data['state_ids'] = 'all';
				}

				$this->dropshipper_model->add_dropshipper_pickup($id, $pickup_data);

				redirect(base_url('admin/dropshippers'), 'refresh');
			}
		} elseif ($param1 == 'edit') {
			$user_data['first_name'] 		= html_escape($this->input->post('first_name'));
			$user_data['last_name']  		= html_escape($this->input->post('last_name'));
			$user_data['biography']  		= html_escape($this->input->post('biography'));
			$user_data['email']  			= html_escape($this->input->post('email'));
			$user_data['alternate_email']  	= html_escape($this->input->post('alternate_email'));
			$user_data['mobile']  			= html_escape($this->input->post('mobile'));

			$this->dropshipper_model->edit($param2, $user_data);

			$pickup_data['state_ids'] 		    = html_escape(implode(',',$this->input->post('state_ids') ?? []));
			$pickup_data['bw_printer']  		= html_escape($this->input->post('bw_printer') ?? 0);
			$pickup_data['sort_order']  		= html_escape($this->input->post('sort_order'));
			$pickup_data['colored_limit']  		= html_escape($this->input->post('colored_limit'));
			$pickup_data['bw_limit']  			= html_escape($this->input->post('bw_limit') ?? 0);
			$pickup_data['limit']  				= html_escape($this->input->post('limit') ?? 0);
			$pickup_data['pickup_id']  			= html_escape($this->input->post('pickup_id'));

			if ($this->input->post('all_states') == 'all') {
				$pickup_data['state_ids'] = 'all';
			}

			$this->dropshipper_model->edit_dropshipper_pickup($param2, $pickup_data);

			redirect(base_url('admin/dropshippers'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->dropshipper_model->enableDisable($param2);
			redirect(base_url('admin/dropshippers'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->dropshipper_model->delete($param2);
			redirect(base_url('admin/dropshippers'), 'refresh');
		}

		$data['page_name'] 		= 'dropshipper/index';
		$data['page_title'] 	= _l('dropshippers');

		$data['action_add'] 	= base_url('admin/dropshipper_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_dropshippers');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/dropshipper_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/dropshippers/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/dropshippers/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_dropshippers() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->dropshipper_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$role_info = $this->role_model->get($result['role_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['first_name'] . ' ' . $result['last_name'],
				'email'					=> $result['email'],
				'mobile'				=> $result['mobile'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function dropshipper_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] = 'dropshipper/form';

		if ($param1 == 'add') {
			$data['page_title'] = _l('dropshipper_add');
			$data['action'] 	= base_url('admin/dropshippers/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 	= $this->dropshipper_model->get($param2);
			$data['page_title'] = _l('dropshipper_edit');
			$data['action'] 	= base_url('admin/dropshippers/edit/' . (int)$param2);
		}

		$data['states']         = $this->state_model->get_all([
			'country_code' 	=> 'IN',
			'sort' 			=> 'name',
			'order' 		=> 'ASC',
			'status' 		=> 1
		])['rows'] ?? [];

		$this->load->view('backend/index', $data);
	}

	public function system_users($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'role',
			'email',
			'mobile',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			unset($_POST['files']);
			$this->system_user_model->add($this->input->post());
			redirect(base_url('admin/system_users'), 'refresh');
		} elseif ($param1 == 'edit') {
			unset($_POST['files']);
			$this->system_user_model->edit($param2, $this->input->post());
			redirect(base_url('admin/system_users'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->system_user_model->enableDisable($param2);
			redirect(base_url('admin/system_users'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->system_user_model->delete($param2);
			redirect(base_url('admin/system_users'), 'refresh');
		}

		$data['page_name'] 		= 'systemuser/index';
		$data['page_title'] 	= _l('system_users');

		$data['action_add'] 	= base_url('admin/system_user_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_system_users');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/system_user_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/system_users/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/system_users/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function system_user_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] = 'systemuser/form';

		if ($param1 == 'add') {
			$data['page_title'] = _l('system_user_add');
			$data['action'] 	= base_url('admin/system_users/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 	= $this->system_user_model->get($param2);
			$data['page_title'] = _l('system_user_edit');
			$data['action'] 	= base_url('admin/system_users/edit/' . (int)$param2);
		}

		$states = $this->state_model->get_all(['status' => 1]);

		$data['states'] = $this->state_model->get_all(['status' => 1])['rows'] ?? [];

		$data['roles'] = array_filter($this->role_model->get_all()['rows'] ?? [], function ($item) {
			return !in_array($item['id'], [1, 2, 3, 4, 9]);
		});

		$this->load->view('backend/index', $data);
	}

	public function ajax_system_users() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->system_user_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$role_info = $this->role_model->get($result['role_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['first_name'] . ' ' . $result['last_name'],
				'role'					=> $role_info['name'] ?? '',
				'email'					=> $result['email'],
				'mobile'				=> $result['mobile'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status']],
			];
		}

		output_json($json);
	}

	public function students($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			$this->student_model->add($this->input->post());
			redirect(base_url('admin/students'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->student_model->edit($param2, $this->input->post());
			redirect(base_url('admin/students'), 'refresh');
		} elseif ($param1 == 'update_student_profile') {
			$this->student_model->edit($param2, [
				'site_id' => $this->input->post('site_id'),
				'country_id' => $this->input->post('country_id'),
				'state_id' => $this->input->post('state_id'),
				'city_id' => $this->input->post('city_id'),
				'grade' => $this->input->post('grade'),
				'section' => $this->input->post('section'),
				'grade_id' => $this->input->post('grade_id'),
				'section_id' => $this->input->post('section_id'),
			]);
			redirect(base_url('admin/students'), 'refresh');
		}  elseif ($param1 == 'status') {
			$this->student_model->enableDisable($param2);
			redirect(base_url('admin/students'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->student_model->delete($param2);
			redirect(base_url('admin/students'), 'refresh');
		}

		$data['sites'] 		= [];
		$data['country'] 	= $this->country_model->get_all()['rows'] ?? [];
		$data['site_id'] 	= (int)$this->input->get('site_id');
		$data['country_name'] = $this->input->get('country');

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> 0,
		]);
		array_unshift($data['country'], [
			'name'	=> _l('default'),
			'id'	=> ''
		]);

		$data['page_name'] 		= 'students/index';
		$data['page_title'] 	= _l('student');
		$data['action_ajax'] 	= base_url('admin/ajax_students');

		$this->load->view('backend/index', $data);
	}

	public function ajax_students() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}
		if ($this->input->get('country')) {
			$filter_data['location'] = $this->input->get('country');
		}

		$results = $this->student_model->get_all($filter_data);
		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_user_model->getEventNameByUserId($result['id']);

			$site_info = (!empty($result['site_id'])) ? $this->site_model->get($result['site_id']) : [];

			$event_name = $event_info['name'] ?? '';

			$subscription_plan_info = $result['subscription_plan_id']
				? $this->subscription_plan_model->get($result['subscription_plan_id'])
				: [];

			$books = $this->book_model->get_all([
				'user_id'	=> $result['id'],
			]);

			$published_books = count(array_filter($books['rows'] ?? [], function($item) {
				return $item['status'] == 1;
			}));

			$leads = $this->lead_model->get_all([
				'student_id' 	=> $result["id"]
			])["rows"];

			$bank_details = $this->bank_model->getByUid($result['id']);

			$leads = $leads[0] ?? [];

			$json['data'][] = [
				'result' => $result,
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'image'				=> _ui(
					empty($result['image'])
						? base_url('uploads/user_image/placeholder.png')
						: $this->config->item('s3_base_url') . 'public/' . $result['image']
				),
				'name'				=> vsprintf(_l(' %s<br> %s<br> %s<br><small> Grade: %s, Section: %s<br> %s (%s)<small>'), [
					$result['first_name'] . ' ' . $result['last_name'],
					$result['email'],
					$result['mobile'],
					!empty($result['grade']) ? $result['grade'] : '',
					!empty($result['section']) ? $result['section'] : '',
					$site_info['name'] ?? '',
					$site_info['id'] ?? '',
				]),
				'books'				=> vsprintf(_l('Total :: %s <br> Published:: %s'), [
					$books['total'],
					$published_books,
				]),
				'location'			=> $result['location'],
				'relation'			=> $result['relation'],
				'source'			=> $result['source'].'/'.(!empty($leads["utm_source"]) ? $leads["utm_source"] : '').(!empty($event_name) ? ('<br>' . $event_name) : ''),
				'subscription_plan'	=> $subscription_plan_info['name'] ?? '',
				'hard_copy'			=> $result['hard_copy'],
				'bank_account'		=> !empty($bank_details) ? 'Yes' : 'No',
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function student_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('student_add');
			$data['page_name'] 			= 'students/form';
		} elseif ($param1 == 'edit') {
			if ($this->input->post()) {
				$data = $this->input->post();

				$bank_details = [];

				if (!empty($data['bank_holder_name']) &&
					!empty($data['bank_name']) &&
					!empty($data['bank_branch_name']) &&
					!empty($data['bank_account_number']) &&
					!empty($data['bank_ifsc_code'])
				) {
					if ($data['bank_deleted'] === '1') {
						$this->db->where('user_id', $param2);
						$this->db->update('user_bank',  [
							'_deleted'		=> 1,
							'date_deleted'	=> date('Y-m-d H:i:s'),
						]);
					} else {
						$bank_details = [
							'user_id'		=> $param2,
							'name'			=> $data['bank_holder_name'],
							'bank_name'		=> $data['bank_name'],
							'branch_name'	=> $data['bank_branch_name'],
							'account_number'=> $data['bank_account_number'],
							'ifsc_code'		=> $data['bank_ifsc_code'],
							'status'		=> ($data['bank_deleted'] == 0) ? 1 : 0
						];

						if (empty($user_bank_info = $this->bank_model->getByUid($param2))) {
							$this->bank_model->add($bank_details);
						} else {
							$this->bank_model->edit($user_bank_info['id'], $bank_details);
						}
					}
				}

				$data = \array_diff_key($data, [
					'files' 				=> '',
					'bank_holder_name'		=> '',
					'bank_name'				=> '',
					'bank_branch_name'		=> '',
					'bank_account_number'	=> '',
					'bank_ifsc_code'		=> '',
					'bank_deleted'			=> '',
				]);

				$this->student_model->edit($param2, $data);

				redirect(base_url('admin/students'), 'refresh');
			}

			$data['student_id'] 	= (int)$param2;
			// $data['action'] 	= '/admin/students/'.$param2;
			$data['details'] 		= $this->user_model->get($param2);
			$data['bank_details'] 	= $this->bank_model->getByUid($param2);
			$data['page_title'] 	= _l('student_edit');
			$data['page_name'] 			= 'students/form';

		} elseif ($param1 == 'update_student_profile') {
			$data['page_name'] 		= 'students/update_form';
			$data['user_id'] 		= $param2;
			$data['action'] 		= site_url('admin/students/update_student_profile/' . (int)$param2);
			$data['details'] 		= $this->student_model->get($param2);
			$data['page_title'] 	= _l('student_profile_update');
		} elseif ($param1 == 'update_student_cred') {
			$data['page_name'] 		= 'students/update_cred_form';
			$data['user_id'] 		= $param2;
			$data['action'] 		= site_url('admin/students/edit/' . (int)$param2);
			$data['details'] 		= $this->student_model->get($param2);
			$data['page_title'] 	= _l('student_cred_update');
		}

		$this->load->view('backend/index', $data);
	}

	public function teachers($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$this->teacher_model->add();
			redirect(base_url('admin/teachers'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->teacher_model->edit($param2);
			redirect(base_url('admin/teachers'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->teacher_model->enableDisable($param2);
			redirect(base_url('admin/teachers'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->teacher_model->delete($param2);
			redirect(base_url('admin/teachers'), 'refresh');
		}

		$data['page_name'] = 'teacher/index';
		$data['page_title'] = _l('teacher');
		$data['teachers'] = $this->teacher_model->get_all(['teacher_id' => $param2]);
		$this->load->view('backend/index', $data);
	}

	public function teacher_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('common/Course_model', 'course_model');

		$data['teacher_id'] 		= $param2;
		$data['courses'] 			= $this->course_model->get_all()->result_array();
		$data['backup_teachers'] 	= $this->teacher_model->get_all();
		$data['page_name'] 			= 'teacher/form';

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('teacher_add');
			$data['action'] 		= base_url('admin/teachers/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 		= $this->teacher_model->get($param2);
			$data['page_title'] 	= _l('teacher_edit');
			$data['action'] 		= base_url('admin/teachers/edit/' . (int)$param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function telecallers($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Telecaller_model', 'telecaller_model');

		if ($param1 == 'add') {
			$this->telecaller_model->add();
			redirect(base_url('admin/telecallers'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->telecaller_model->edit($param2);
			redirect(base_url('admin/telecallers'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->telecaller_model->enableDisable($param2);
			redirect(base_url('admin/telecallers'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->telecaller_model->delete($param2);
			redirect(base_url('admin/telecallers'), 'refresh');
		}

		$data['page_name'] = 'telecaller/index';
		$data['page_title'] = _l('telecaller');
		$data['telecallers'] = $this->telecaller_model->get_all()['rows'];
		$this->load->view('backend/index', $data);
	}

	public function telecaller_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Telecaller_model', 'telecaller_model');

		if ($param1 == 'add_telecaller_form') {
			$data['page_name'] = 'telecaller/form';
			$data['page_title'] = _l('telecaller_add');
			$data['telecaller_id'] = 0;

			$data['action'] = base_url('admin/telecallers/add');

			$this->load->view('backend/index', $data);
		} elseif ($param1 == 'edit_telecaller_form') {
			$data['page_name'] = 'telecaller/form';
			$data['telecaller_id'] = $param2;
			$data['details'] = $this->telecaller_model->get($param2);
			$data['page_title'] = _l('telecaller_edit');

			$data['action'] = base_url('admin/telecallers/edit/' . (int)$param2);

			$this->load->view('backend/index', $data);
		}
	}

	public function printers($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'edit') {
			$this->printer_model->edit($param2);
			redirect(base_url('admin/printers'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->printer_model->enableDisable($param2);
			redirect(base_url('admin/printers'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->printer_model->delete($param2);
			redirect(base_url('admin/printers'), 'refresh');
		} elseif ($param1 == 'costing') {
			$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');
			$this->printer_costing_model->getByPrinterId($param2);
			redirect(base_url('admin/printers'), 'refresh');
		}

		$data['page_name'] 		= 'printer/view';
		$data['page_title'] 	= _l('printer');
		$data['action_ajax'] 	= base_url('admin/ajax_printers');

		$this->load->view('backend/index', $data);
	}

	public function ajax_printers() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->printer_model->get_all($filter_data);
		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'result' => $result,
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'name'				=> vsprintf(_l('%s <br> %s <br> %s'), [
					$result['first_name'] . ' ' . $result['last_name'],
					$result['email'],
					$result['mobile'],
				]),
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function printer_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'edit') {
			$printer_extra_detail_info = $this->printer_extra_details_model->getByPrinterId($param2);

			if ($this->input->post()) {
				$data = $this->input->post();
				$data = \array_diff_key($data, ["files" => ""]);
				$this->printer_model->edit($param2);

				if (!empty($data['pickup_location_id'])) {
					$printer_data = [];
					$printer_data['pickup_location_id'] = $data['pickup_location_id'];

					if (!empty($printer_extra_detail_info)) {
						$this->printer_extra_details_model->edit($printer_extra_detail_info['id'], $printer_data);
					} else {
						$printer_data['printer_id'] = $param2;
						$this->printer_extra_details_model->add($printer_data);
					}
				}

				redirect(base_url('admin/printers'), 'refresh');
			}

			$data['student_id'] 	= $param2;
			$data['details'] 		= $this->user_model->get($param2);
			$data['details']['pickup_location_id'] = $printer_extra_detail_info['pickup_location_id'] ?? '';
			$data['page_title'] 	= _l('printer_edit');
		}

		$data['page_name'] 			= 'printer/form';

		$this->load->view('backend/index', $data);
	}

	public function printer_costing($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'edit' && !empty($param2) && !empty($this->printer_model->get($param2))) {
			$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');

			$details = $this->printer_costing_model->getByPrinterId($param2);

			if ($data = $this->input->post()) {
				if (!empty($details)) {
					$this->printer_costing_model->edit($details['id'], $data);
				} else {
					$data['printer_id'] = $param2;
					$this->printer_costing_model->add($data);
				}

				redirect(base_url('admin/printers'), 'refresh');
			}

			$data['student_id'] 	= $param2;
			$data['details'] 		= $details;
			$data['action'] 		= base_url('admin/printer_costing/edit/'.$param2);
		}

		$data['page_name'] 			= 'printer/costing';
		$data['page_title'] 		= _l('printer_costing');

		$this->load->view('backend/index', $data);
	}

	public function ajax_search_students() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
			'student_verified'	=> 1
		];

		$results = $this->student_model->get_all($filter_data);

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> vsprintf(_l('%s (%s - %s - %s)'), [
					trim($result['first_name'] . ' ' . $result['last_name']),
					$result['id'],
					$result['email'],
					$result['mobile'],
				])
			];
		}

		output_json($json);
	}

	public function ajax_search_user_tag($type = 'id') {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->user_tag_model->get_all($filter_data)['rows'] ?? [];

		$json[] = [
			'id'				=> $type == 'id' ? 0 : _l('all'),
			'text'				=> _l('all'),
		];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $type == 'id' ? $result['id'] : $result['name'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}

	public function ajax_search_role() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->role_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['id']),
			];
		}

		output_json($json);
	}

	public function ajax_search_department() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->department_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['id']),
			];
		}

		output_json($json);
	}

	public function ajax_search_system_users() {
		$json = [];

		$filter_data = [
			'status'			=> 1,
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->system_user_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s, %s, %s (%s)', $result['name'], $result['mobile'], $result['email'], $result['id']),
			];
		}

		output_json($json);
	}
}
