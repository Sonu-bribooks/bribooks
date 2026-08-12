<?php defined('BASEPATH') or exit('No direct script access allowed');

trait School {
	public function school_tags($param1 = null,$param2 = null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'date_added',
			'actions',
		];

		if ($param1 == 'add') {
			$this->school_tag_model->add($this->input->post());
			redirect(base_url('admin/school_tags'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->school_tag_model->edit($param2, $this->input->post());
			redirect(base_url('admin/school_tags'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->school_tag_model->delete($param2);
			redirect(base_url('admin/school_tags'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('school_tags');
		$data['action_add'] 	= base_url('admin/school_tags_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_school_tag');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/school_tags_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/school_tags/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function school_tags_form($param1=null,$param2=null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_school_tags');
			$data['action'] 						= base_url('admin/school_tags/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_school_tags');
			$data['action'] 						= base_url('admin/school_tags/edit/' . (int)$param2);
			$data['id'] 							= (int)$param2;
			$info 									= $this->school_tag_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_school_tag() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->school_tag_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'date_added'			=> format_date($result['date_added']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_school_tag($type = 'id') {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->school_tag_model->get_all($filter_data)['rows'] ?? [];

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
}
