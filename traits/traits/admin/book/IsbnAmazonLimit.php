<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait IsbnAmazonLimit {
	public function isbn_amazon_limit($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'event',
			'isbn_limit',
			'amazon_limit',
			'actions',
		];

		if ($param1 == 'add') {
			if (!empty($this->event_book_isbn_limit_model->get_all([
                'event_id' => $this->input->post('event_id')
            ])['rows'][0] ?? '')) {
                $this->session->set_flashdata('error_message', 'Limit for this event is already added!');
            } else {
                $this->event_book_isbn_limit_model->add([
					'event_id' 		=> $this->input->post('event_id'),
					'isbn_limit' 	=> $this->input->post('isbn_limit'),
					'amazon_limit' 	=> $this->input->post('amazon_limit'),
				]);
                $this->session->set_flashdata('flash_message', 'Limit added successfully!');
            }
			redirect(base_url('admin/isbn_amazon_limit'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->event_book_isbn_limit_model->edit($param2, [
				'isbn_limit' 	=> $this->input->post('isbn_limit'),
				'amazon_limit' 	=> $this->input->post('amazon_limit'),
			]);
			redirect(base_url('admin/isbn_amazon_limit'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_book_isbn_limit_model->delete($param2);
			redirect(base_url('admin/isbn_amazon_limit'), 'refresh');
		}

		$data['page_name'] 		= 'isbn_amazon_limit/index';
		$data['page_title'] 	= _l('isbn_amazon_limit');
		$data['action_add'] 	= base_url('admin/isbn_amazon_limit_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_isbn_amazon_limit');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/isbn_amazon_limit_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/isbn_amazon_limit/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function isbn_amazon_limit_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'isbn_amazon_limit/form';
			$data['page_title'] 					= _l('isbn_amazon_limit_add');
			$data['action'] 						= base_url('admin/isbn_amazon_limit/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'isbn_amazon_limit/form';
			$data['page_title'] 					= _l('isbn_amazon_limit_edit');
			$data['action'] 						= base_url('admin/isbn_amazon_limit/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_book_isbn_limit_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
		}

		$label_name = [
			'0' => 'Domestic',
			'1' => 'Global',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => in_array($info['event_id'], [0,1]) ? $label_name[$info['event_id']]  : ($event_info['name'] ?? ''),
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'isbn_limit',
			'label'		=> _l('isbn_limit'),
			'required'	=> true,
			'value'		=> $info['isbn_limit'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'amazon_limit',
			'label'		=> _l('amazon_limit'),
			'required'	=> true,
			'value'		=> $info['amazon_limit'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_isbn_amazon_limit() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$label_name = [
			'0' => 'Domestic',
			'1' => 'Global',
		];
		
		$results = $this->event_book_isbn_limit_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'event'					=> in_array($result['event_id'], [0,1]) ? $label_name[$result['event_id']]  : ($event_info['name'] ?? ''),
				'isbn_limit'			=> $result['isbn_limit'],
				'amazon_limit'			=> $result['amazon_limit'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
