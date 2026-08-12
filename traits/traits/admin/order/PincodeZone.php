<?php defined('BASEPATH') or exit('No direct script access allowed');

trait PincodeZone {
	public function pincode_zone($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'pincode',
			'zone',
			'city',
			'state',
			'actions',
		];

		if ($param1 == 'add') {
            if (!empty($this->pincode_zone_model->get_all([
                'pincode' => $this->input->post('pincode')
            ])['rows'] ?? [])) {
                $this->session->set_flashdata('error_message', _l('this_pincode_is_already_exist'));
			    redirect(base_url('admin/pincode_zone'), 'refresh');
            }

			$this->pincode_zone_model->add($this->input->post());
			redirect(base_url('admin/pincode_zone'), 'refresh');
		} elseif ($param1 == 'edit') {
            if (!empty($code_info = $this->pincode_zone_model->get_all([
                'id_ne'     => $param2,
                'pincode'   => $this->input->post('pincode')
            ])['rows'] ?? [])) {
                $this->session->set_flashdata('error_message', _l('this_pincode_is_already_exist'));
			    redirect(base_url('admin/pincode_zone'), 'refresh');
            }

			$this->pincode_zone_model->edit($param2, $this->input->post());
			redirect(base_url('admin/pincode_zone'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->pincode_zone_model->delete($param2);
			redirect(base_url('admin/pincode_zone'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('pincode_zone');
		$data['action_add'] 	= base_url('admin/pincode_zone_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_pincode_zone');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/pincode_zone_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/pincode_zone/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function pincode_zone_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_pincode_zone');
			$data['action'] 						= base_url('admin/pincode_zone/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('pincode_zone');
			$data['action'] 						= base_url('admin/pincode_zone/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->pincode_zone_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'pincode',
			'label'		=> _l('pincode'),
			'required'	=> true,
			'value'		=> $info['pincode'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'zone',
			'label'		=> _l('select_zone'),
			'required'	=> true,
			'value'		=> $info['zone'] ?? '',
			'options'	=> [
				[
					'value' => 'A',
					'label' => _l('A'),
				],
				[
					'value' => 'B',
					'label' => _l('B'),
				],
				[
					'value' => 'C',
					'label' => _l('C'),
				],
				[
					'value' => 'D',
					'label' => _l('D'),
				],
                [
					'value' => 'E',
					'label' => _l('E'),
				],
                [
					'value' => 'F',
					'label' => _l('F'),
				],
                [
					'value' => 'G',
					'label' => _l('G'),
				],
			],
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'city',
			'label'		=> _l('city'),
			'required'	=> true,
			'value'		=> $info['city'] ?? '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'state',
			'label'		=> _l('state'),
			'required'	=> true,
			'value'		=> $info['state'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_pincode_zone() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->pincode_zone_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'pincode'				=> $result['pincode'],
				'zone'					=> $result['zone'],
				'city'					=> $result['city'],
				'state'					=> $result['state'],
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_search_pincode_zone() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->pincode_zone_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['pincode'],
			];
		}

		output_json($json);
	}
}
