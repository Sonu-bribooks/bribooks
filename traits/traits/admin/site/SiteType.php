<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SiteType {
	public function site_types($param1 = null,$param2 = null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'date_added',
			'actions',
		];

		if ($param1 == 'add') {
			$this->site_type_model->add($this->input->post());
			redirect(base_url('admin/site_types'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->site_type_model->edit($param2, $this->input->post());
			redirect(base_url('admin/site_types'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->site_type_model->delete($param2);
			redirect(base_url('admin/site_types'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('site_types');
		$data['action_add'] 	= base_url('admin/site_type_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_site_types');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/site_type_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/site_types/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function site_type_form($param1=null,$param2=null) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_site_type');
			$data['action'] 						= base_url('admin/site_types/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_site_type');
			$data['action'] 						= base_url('admin/site_types/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->site_type_model->get($param2);
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

	public function ajax_site_types() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->site_type_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'date_added'			=> formatDate($result['date_added']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}
}
