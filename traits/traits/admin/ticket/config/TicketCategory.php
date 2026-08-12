<?php defined('BASEPATH') or exit('No direct script access allowed');

trait TicketCategory {
	public function ticket_category($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'parent',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->ticket_category_model->add($this->input->post());
			redirect(base_url('admin/ticket_category'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->ticket_category_model->edit($param2, $this->input->post());
			redirect(base_url('admin/ticket_category'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->ticket_category_model->delete($param2);
			redirect(base_url('admin/ticket_category'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('ticket_category');
		$data['action_add'] 	= base_url('admin/ticket_category_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_ticket_category');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/ticket_category_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/ticket_category/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ticket_category_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_ticket_category');
			$data['action'] 						= base_url('admin/ticket_category/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('ticket_category');
			$data['action'] 						= base_url('admin/ticket_category/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->ticket_category_model->get($param2);
			$parent_info 							= $this->ticket_category_model->get($info['parent_id']);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'parent_id',
			'label'		=> _l('select_parent'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['parent_id'] ?? '',
				'label' => $parent_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_ticket_category'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_ticket_category() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->ticket_category_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$parent_info = $this->ticket_category_model->get($result['parent_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'type'					=> $result['type'],
				'name'					=> $result['name'],
				'parent'				=> $parent_info['name'] ?? '',
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_search_ticket_category() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->ticket_category_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
