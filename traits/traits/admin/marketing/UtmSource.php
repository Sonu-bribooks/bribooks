<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait UtmSource {
    public function utm_source($param1='', $param2=''){
        $data['fields'] = [
			'sn',
            'id',
			'key',
			'value',
			'actions',
		];

		if ($param1 == 'add') {
		
            foreach ($this->input->post('key') as $index => $value) {
				$this->utm_source_model->add([
					'key'          => $value,
					'value'          => $this->input->post('value')[$index] ?? '',
				]);
			}

            $this->session->set_flashdata('flash_message', 'Utm source added successfully!');
			redirect(base_url('admin/utm_source'), 'refresh');
		} elseif ($param1 == 'edit') {
			$utm_source_info     = $this->utm_source_model->get($param2);
           
			$this->utm_source_model->edit($param2, [
                'key'		=> $this->input->post('key')[0] ?? $utm_source_info['key'],
                'value'		=> $this->input->post('value')[0] ?? $utm_source_info['value'],
            ]);

			redirect(base_url('admin/utm_source'), 'refresh');

		} elseif ($param1 == 'delete') {
			$this->utm_source_model->delete($param2);
			redirect(base_url('admin/utm_source'), 'refresh');
		}

		$data['page_name'] 		= 'utm_source/index';
		$data['page_title'] 	= _l('utm_source');
		$data['action_add'] 	= base_url('admin/utm_source_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_utm_source');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/utm_source_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/utm_source/delete/',
			],
		];

		$this->load->view('backend/index', $data);
    }

    public function utm_source_form($param1 = NULL, $param2 = NULL) {
        $data['page_name'] 		= 'utm_source/form';
        $data['page_title'] 	= _l('utm_source_Add');
        $data['action'] 		= base_url('admin/utm_source/add');

        if ($param1 == 'edit') {
			$data['page_title'] 	= _l('utm_source_edit');
			$data['action'] 		= base_url('admin/utm_source/edit/' . (int)$param2);
			$data['id'] 			= (int)$param2;
			$data['details'] 		= $this->utm_source_model->get($param2);
		}
    
		$this->load->view('backend/index', $data);
	}

	public function ajax_utm_source() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->utm_source_model->get_all($filter_data);

        $json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'key'				    => $result['key'],
				'value'				    => $result['value'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}