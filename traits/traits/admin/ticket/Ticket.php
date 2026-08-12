<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('admin/ticket/config');

trait Ticket {
	use
		TicketStatus,
		TicketPriority,
		TicketCategory
	;

	private function _initTicketFilters(&$data = []) {
		$data['filters'][] 		= [
			'type'		=> 'select2',
			'key'		=> 'status_id',
			'label'		=> _l('select_status'),
			'required'	=> false,
			'ajax_url'	=> base_url('admin/ajax_search_ticket_status'),
		];

		$data['filters'][] 		= [
			'type'		=> 'select2',
			'key'		=> 'priority_id',
			'label'		=> _l('select_priority'),
			'required'	=> false,
			'ajax_url'	=> base_url('admin/ajax_search_ticket_priority'),
		];

		$data['filters'][] 		= [
			'type'		=> 'multi_select2',
			'key'		=> 'department_ids',
			'label'		=> _l('select_department'),
			'required'	=> false,
			'ajax_url'	=> base_url('admin/ajax_search_department'),
		];

		$this->_generic_filters = $data['filters'];
	}

	public function ticket($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$_POST['code'] = mb_strtoupper(uniqid());
			$_POST['agent_id'] = $this->session->userdata('user_id');
			$this->ticket_model->add($this->input->post());
			redirect(base_url('admin/ticket'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->ticket_model->edit($param2, $this->input->post());
			redirect(base_url('admin/ticket'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->ticket_model->delete($param2);
			redirect(base_url('admin/ticket'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'ticket_no',
			'user_type',
			'student/school',
			'escalated_by',
			'assigned_department',
			'category',
			'subject',
			'description',
			'priority',
			'status',
			'date_added',
			'date_modified',
			'date_closed',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('ticket');
		$data['action_add'] 	= base_url('admin/ticket_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_tickets');

		$data['actions'] 		= [
			[
				'key'	=> 'view',
				'url'	=> 'admin/ticket_details/',
			],
			[
				'key'	=> 'edit',
				'url'	=> 'admin/ticket_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/ticket/delete/',
			],
		];

		self::_initTicketFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function ticket_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_ticket');
			$data['action'] 						= base_url('admin/ticket/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_ticket');
			$data['action'] 						= base_url('admin/ticket/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->ticket_model->get($param2);
			$model 		= sprintf('%s_model', mb_strtolower($info['user_type']));
			$user_info 	= !empty($info['user_id']) ? $this->{$model}->get($info['user_id']) : [];
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'user_type',
			'label'		=> _l('select_user_type'),
			'required'	=> true,
			'value'		=> $info['user_type'] ?? 'student',
			'options'	=> [
				[
					'value' => 'student',
					'label' => _l('student'),
				],
				[
					'value' => 'school',
					'label' => _l('unregistered_school'),
				],
				[
					'value' => 'site',
					'label' => _l('registered_school'),
				],
				[
					'value' => 'teacher',
					'label' => _l('teacher'),
				],
				[
					'value' => 'other',
					'label' => _l('other'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'user_id',
			'label'		=> _l('select_user'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['user_id'] ?? '',
				'label' => $user_info['name'] ?? $user_info['first_name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_ticket_user?includes=user_type'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'department_id',
			'label'		=> _l('assign_department'),
			'required'	=> true,
			'value'		=> $info['department_id'] ?? '',
			'options'	=> array_map(fn($item) => [
				'label' => $item['name'],
				'value' => $item['id'],
			], $this->department_model->get_all(['start' => 0, 'limit' => 100])['rows'] ?? []),
		];

		$categories = $this->ticket_category_model->get_all([
			'parent_id' => 0,
			'start' 	=> 0,
			'limit' 	=> 100
		])['rows'] ?? [];

		$sub_categories = [];

		foreach ($categories as $key => $category) {
			$results = $this->ticket_category_model->get_all([
				'parent_id' => $category['id'],
				'start' 	=> 0,
				'limit' 	=> 100
			])['rows'] ?? [];

			foreach ($results as $key => $item) {
				$sub_categories[] = [
					'label'	=> sprintf('%s > %s', $category['name'], $item['name']),
					'value'	=> $item['id'],
				];
			}
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'category_id',
			'label'		=> _l('select_category'),
			'required'	=> true,
			'value'		=> $info['category_id'] ?? '',
			'options'	=> $sub_categories,
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'subject',
			'label'		=> _l('subject'),
			'required'	=> true,
			'value'		=> $info['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'description',
			'label'		=> _l('description'),
			'required'	=> true,
			'value'		=> $info['description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'priority_id',
			'label'		=> _l('select_priority'),
			'required'	=> true,
			'value'		=> $info['priority_id'] ?? '1',
			'options'	=> array_map(fn($item) => [
				'label' => $item['name'],
				'value' => $item['id'],
			], $this->ticket_priority_model->get_all(['start' => 0, 'limit' => 100])['rows'] ?? []),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status_id',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status_id'] ?? '1',
			'options'	=> array_map(fn($item) => [
				'label' => $item['name'],
				'value' => $item['id'],
			], $this->ticket_status_model->get_all(['start' => 0, 'limit' => 100])['rows'] ?? []),
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_tickets($status_id = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]') ?? ''),
		];

		if (!empty($status_id)) {
			$filter_data['status_id'] = (int)$status_id;
		}

		if (
			strpos(mb_strtolower($this->session->userdata('role')), 'admin') === false &&
			strpos(mb_strtolower($this->session->userdata('role')), 'support') === false
		) {
			$filter_data['department_ids'] = $this->session->userdata('department_ids');
		}

		self::_initTicketFilters();
		self::_formatFilters($filter_data);
	
		$results = $this->ticket_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$model 		= sprintf('%s_model', mb_strtolower($result['user_type']));
			$user_info 	= !empty($result['user_id']) ? $this->{$model}->get($result['user_id']) : ['name' => _l('other')];
			$agent_info = $this->user_model->get($result['agent_id']);
			$department_info= $this->department_model->get($result['department_id']);
			$category_info 	= $this->ticket_category_model->get($result['category_id']);
			$parent_info 	= $this->ticket_category_model->get($category_info['parent_id']);
			$priority_info 	= $this->ticket_priority_model->get($result['priority_id']);
			$status_info	= $this->ticket_status_model->get($result['status_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'ticket_no'				=> $result['code'],
				'user_type'				=> $result['user_type'],
				'student/school'		=> $user_info ? ($user_info['name'] ?? $user_info['first_name'] ?? '-')     : '-',
				'escalated_by'			=> $agent_info['first_name'] ?? '-',
				'assigned_department'	=> $department_info['name'] ?? '-',
				'category'				=> sprintf('%s > %s', $parent_info['name'] ?? '-', $category_info['name'] ?? '-'),
				'subject'				=> $result['subject'],
				'description'			=> $result['description'],
				'priority'				=> sprintf('<span class="badge badge-%s">%s</span>', $priority_info['color'], $priority_info['name']),
				'status'				=> sprintf('<span class="badge badge-%s">%s</span>', $status_info['color'], $status_info['name']),
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'date_closed'			=> formatDate($result['date_closed']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ticket_details($id = 0) {
		$data['page_name'] 		= 'ticket/details';
		$data['page_title'] 	= _l('ticket');

		$data['info']			= $info = $this->ticket_model->get($id);

		if (empty($data['info'])) {
			$this->session->set_flashdata('error_message', _l('invalid_ticket'));
		}

		$model 		= sprintf('%s_model', mb_strtolower($info['user_type']));

		$data['user_info'] 		= !empty($info['user_id']) ? $this->{$model}->get($info['user_id']) : ['name' => _l('other')];
		$data['agent_info'] 	= $this->user_model->get($info['agent_id']);
		$data['department_info']= $this->department_model->get($info['department_id']);
		$data['category_info']	= $this->ticket_category_model->get($info['category_id']);
		$data['parent_info']	= $this->ticket_category_model->get($data['category_info']['parent_id']);
		$data['priority_info'] 	= $this->ticket_priority_model->get($info['priority_id']);
		$data['status_info']	= $this->ticket_status_model->get($info['status_id']);

		$data['fields'] 		= [
			'sn',
			'id',
			'agent',
			'reply',
			'status',
			'date_modified',
			'actions',
		];

		$data['action_ajax']	= base_url('admin/ajax_ticket_histories/' . $info['id']);
		$data['action_reply']	= base_url('admin/ajax_ticket_history_crud/add');

		$data['actions'] 		= [
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm_ajax',
				'url'	=> 'admin/ajax_ticket_history_crud/delete/',
			],
		];

		$data['statuses']		= $this->ticket_status_model->get_all(['start' => 0, 'limit' => 100])['rows'] ?? [];

		$this->load->view('backend/index', $data);
	}

	public function ajax_ticket_histories($ticket_id = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'ticket_id'			=> (int)$ticket_id,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->ticket_history_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$agent_info 	= $this->user_model->get($result['agent_id']);
			$status_info	= $this->ticket_status_model->get($result['status_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'agent'					=> $agent_info['first_name'],
				'reply'					=> $result['message'],
				'status'				=> sprintf('<span class="badge badge-%s">%s</span>', $status_info['color'], $status_info['name']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_ticket_history_crud($param1 = null, $param2 = 0) {
		if ($param1 == 'add') {
			$_POST['agent_id'] = $this->session->userdata('user_id');
			$this->ticket_history_model->add($this->input->post());

			self::_updateTicket();
		} elseif ($param1 == 'edit') {
			$this->ticket_history_model->edit($param2, $this->input->post());

			self::_updateTicket();
		} elseif ($param1 == 'delete') {
			$this->ticket_history_model->delete($param2);
		}

		output_json(['success' => _l('modified_successfully')]);
	}

	public function ajax_search_ticket() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->ticket_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}

	public function ajax_search_ticket_user() {
		$json 		= [];
		$user_type 	= 'student';

		if ($this->input->get('user_type')) {
			$user_type = $this->input->get('user_type');
		}

		if (in_array($user_type, ['other'])) {
			$json[] = [
				'id'	=> 0,
				'text'	=> _l('other')
			];
			return output_json($json);
		}

		if (!in_array($user_type, ['student', 'school', 'teacher', 'site'])) {
			return output_json($json);
		}

		$model = sprintf('%s_model', mb_strtolower($user_type));

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->{$model}->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> in_array($user_type, ['student', 'teacher'])
					? vsprintf('%s %s (%s / %s) - %s', [
						$result['first_name'],
						$result['last_name'],
						$result['mobile'],
						$result['email'],
						$result['id'],
					])
					: vsprintf('%s, %s, %s (%s / %s) - %s', [
						$result['name'],
						$result['owner_name'],
						$result['authorized_person'],
						$result['owner_email'],
						$result['owner_email'],
						$result['id'],
					])
				,
			];
		}

		output_json($json);
	}

	private function _updateTicket() {
		$status_info = $this->ticket_status_model->get($this->input->post('status_id'));

		if (empty($status_info)) return;

		$data = [
			'status_id'	=> (int)$status_info['id'],
		];

		if (strpos(mb_strtolower($status_info['name']), 'close') !== false) {
			$data['date_closed'] 	= date('Y-m-d H:i:s');
			$data['closure_note'] 	= $this->input->post('message');
		}

		$this->ticket_model->edit($this->input->post('ticket_id'), $data);
	}
}
