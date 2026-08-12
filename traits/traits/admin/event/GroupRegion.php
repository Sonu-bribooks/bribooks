<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GroupRegion {
	public function group_region($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'country',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->group_region_model->add([
				'name' 			=> $this->input->post('name'),
				'country_id' 	=> $this->input->post('country_id'),
				'state' 		=> implode(',', $this->input->post('state_id')),
			]);
			redirect(base_url('admin/group_region'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->group_region_model->edit($param2, [
				'name' 			=> $this->input->post('name'),
				'country_id' 	=> $this->input->post('country_id'),
				'state' 		=> implode(',', $this->input->post('state_id')),
			]);
			redirect(base_url('admin/group_region'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->group_region_model->delete($param2);
			redirect(base_url('admin/group_region'), 'refresh');
		}

		$data['page_name'] = 'group_region/index';
		$data['page_title'] = _l('group_region');

		$data['action_add'] 	= base_url('admin/group_region_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_group_region');


		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/group_region_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/group_region/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function group_region_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] = _l('group_region_add');
			$data['action'] 	= base_url('admin/group_region/add');
		} elseif ($param1 == 'edit') {
			$data['state_id'] 	= (int)$param2;
			$data['action'] 	= base_url('admin/group_region/edit/' . (int)$param2);
			$data['details'] 	= $this->group_region_model->get($param2);
			$data['page_title'] = _l('group_region_edit');
		}

		$data['page_name'] 	= 'group_region/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_group_region() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->group_region_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'country'				=> $result['country'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}
}
