<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CertificateType {
	public function certificate_types($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'name',
			'type',
			'quantity',
			'achievement',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->certificate_type_model->add($this->input->post());
			redirect(base_url('admin/certificate_types'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->certificate_type_model->edit($param2, $this->input->post());
			redirect(base_url('admin/certificate_types'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->certificate_type_model->delete($param2);
			redirect(base_url('admin/certificate_types'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('certificate_types');
		$data['action_add'] 	= base_url('admin/certificate_types_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_certificate_type');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/certificate_types_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/certificate_types/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function certificate_types_form($param1=null,$param2=null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_certificate_types');
			$data['action'] 						= base_url('admin/certificate_types/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_certificate_types');
			$data['action'] 						= base_url('admin/certificate_types/edit/' . (int)$param2);
			$data['id'] 							= (int)$param2;
			$certificate_type_info 					= $this->certificate_type_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $certificate_type_info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'type',
			'label'		=> _l('type'),
			'required'	=> true,
			'value'		=> $certificate_type_info['type'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'quantity',
			'label'		=> _l('quantity'),
			'min'		=> 1,
			'step'		=> 1,
			'max'		=> '',
			'required'	=> true,
			'value'		=> $certificate_type_info['quantity'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'achievement',
			'label'		=> _l('achievement'),
			'required'	=> false,
			'value'		=> $certificate_type_info['achievement'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'	 => $template_info['status'] ?? 1,
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

		$this->load->view('backend/index', $data);
	}

	public function ajax_certificate_type() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->certificate_type_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'quantity'				=> $result['quantity'],
				'achievement'			=> $result['achievement'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_certificate_type() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->certificate_type_model->get_all($filter_data)['rows'] ?? [];

		$json[] = [
			'id'				=> 0,
			'text'				=> _l('generic'),
		];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
