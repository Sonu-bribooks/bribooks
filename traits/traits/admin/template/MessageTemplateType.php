<?php defined('BASEPATH') or exit('No direct script access allowed');

trait MessageTemplateType {
	public function message_template_type($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'actions',
		];

		if ($param1 == 'add') {
			$this->message_template_type_model->add($this->input->post());
			redirect(base_url('admin/message_template_type'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->message_template_type_model->edit($param2, $this->input->post());
			redirect(base_url('admin/message_template_type'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->message_template_type_model->delete($param2);
			redirect(base_url('admin/message_template_type'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('message_template_type');
		$data['action_add'] 	= base_url('admin/message_template_type_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_message_template_type');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/message_template_type_form/edit/',
			],
			[
				'key'	=> 'view_india',
				'url'	=> 'admin/message_template_view/1/',
			],
			[
				'key'	=> 'view_global',
				'url'	=> 'admin/message_template_view/2/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/message_template_type/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function message_template_type_form($param1=null,$param2=null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_message_template_type');
			$data['action'] 						= base_url('admin/message_template_type/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_message_template_type');
			$data['action'] 						= base_url('admin/message_template_type/edit/' . (int)$param2);
			$data['id'] 							= (int)$param2;
			$template_type_info 					= $this->message_template_type_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $template_type_info['name'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_message_template_type($region = false) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->message_template_type_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_message_template_type() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->message_template_type_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
