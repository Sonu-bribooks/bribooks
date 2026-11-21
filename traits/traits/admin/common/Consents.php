<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Consents {
	public function consent($param1 = '', $param2 = '') {
		// if ($this->session->userdata('admin_login') != true) {
		// 	redirect(base_url('login'), 'refresh');
		// }

		$data['fields'] = [
			'sn',
			'id',
			'type',
			'title',
            'version',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->consent_model->add($this->input->post());
			redirect(base_url('admin/consent'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->consent_model->edit($param2, $this->input->post());
			redirect(base_url('admin/consent'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->consent_model->delete($param2);
			redirect(base_url('admin/consent'), 'refresh');
		}

		$data['page_name'] = 'generic/index';
		$data['page_title'] = _l('consent');

		$data['action_add'] 	= base_url('admin/consent_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_consent');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/consent_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/consent/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function consent_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'generic/form';

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('consent_add');
			$data['action'] 		= base_url('admin/consent/add');
		} elseif ($param1 == 'edit') {
			$data['page_title'] 	= _l('consent_edit');
			$data['consent_id'] 	= (int)$param2;
			$data['action'] 		= base_url('admin/consent/edit/' . (int)$param2);
			$info 			 		= $this->consent_model->get($param2);
		}

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'type',
			'label'		=> _l('type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'title',
			'label'		=> _l('title'),
			'required'	=> true,
			'value'		=> $info['title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'content',
			'label'		=> _l('content'),
			'required'	=> true,
			'value'		=> $info['content'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'version',
			'label'		=> _l('version'),
			'required'	=> true,
			'value'		=> $info['version'] ?? '',
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

	public function ajax_consent() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->consent_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'type'					=> $result['type'],
				'title'					=> $result['title'],
				'version'				=> $result['version'],
                'status'				=> $result['status'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}
}