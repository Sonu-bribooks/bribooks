<?php defined('BASEPATH') or exit('No direct script access allowed');

trait TicketPriority {
	public function ticket_priority($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'color',
			'response_duration',
			'resolution_duration',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->ticket_priority_model->add($this->input->post());
			redirect(base_url('admin/ticket_priority'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->ticket_priority_model->edit($param2, $this->input->post());
			redirect(base_url('admin/ticket_priority'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->ticket_priority_model->delete($param2);
			redirect(base_url('admin/ticket_priority'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('ticket_priority');
		$data['action_add'] 	= base_url('admin/ticket_priority_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_ticket_priority');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/ticket_priority_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/ticket_priority/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ticket_priority_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_ticket_priority');
			$data['action'] 						= base_url('admin/ticket_priority/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('ticket_priority');
			$data['action'] 						= base_url('admin/ticket_priority/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->ticket_priority_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'color',
			'label'		=> _l('select_color'),
			'required'	=> true,
			'value'		=> $info['color'] ?? 'info',
			'options'	=> [
				[
					'value' => 'info',
					'label' => _l('info'),
				],
				[
					'value' => 'warning',
					'label' => _l('warning'),
				],
				[
					'value' => 'success',
					'label' => _l('success'),
				],
				[
					'value' => 'danger',
					'label' => _l('danger'),
				],
				[
					'value' => 'primary',
					'label' => _l('primary'),
				],
				[
					'value' => 'secondary',
					'label' => _l('secondary'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'response_duration',
			'label'		=> _l('response_duration(hours)'),
			'required'	=> true,
			'value'		=> $info['response_duration'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'resolution_duration',
			'label'		=> _l('resolution_duration(hours)'),
			'required'	=> true,
			'value'		=> $info['resolution_duration'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_ticket_priority() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->ticket_priority_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'color'					=> sprintf('<span class="badge badge-%s">%s</span>', $result['color'], $result['color']),
				'response_duration'		=> $result['response_duration'],
				'resolution_duration'	=> $result['resolution_duration'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_search_ticket_priority() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->ticket_priority_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
