<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BookIsbn {
	public function book_isbn($param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$data['fields'] = [
			'sn',
			'book_id',
			'book_name',
			'author_name',
			'version',
			'copies',
			'location',
			'source',
			'actions',
		];

		if ($param2 == 'edit') {
			$book_info = $this->book_model->get($param3);

			if ($this->input->post('isbn')) {
				$book_isbn_info = $this->book_model->get_all([
					'isbn'	=> $this->input->post('isbn'),
				])['rows'][0] ?? [];

				if ($book_isbn_info && $book_isbn_info['id'] != $book_info['id']) {
					$this->session->set_flashdata('error_message', _li('already_used_isbn_number'));
					redirect(base_url('admin/book_isbn'), 'refresh');
				}

				if (empty($book_info['isbn'])) {
					$this->alert_model->isbnAssignAlert($book_info['id']);
				}
			}

			$this->book_model->edit($param3, $this->input->post());

			$this->book_version_model->editByBookId(
				$book_info['id'],
				$book_info['version'],
				$this->input->post()
			);

			if (empty($book_info['amazon_url']) && !empty($this->input->post('amazon_url'))) {
				$this->alert_model->amazonBookPublishAlert($book_info['id']);
			}

			$this->session->set_flashdata('success', 'Assigned Successfully !');

			if ($param1 == 'amazon') {
				redirect(base_url('admin/book_isbn/amazon'), 'refresh');
			} else {
				redirect(base_url('admin/book_isbn'), 'refresh');
			}
		} 

		$events = $this->event_model->get_all()['rows'] ?? [];

		$data['page_name'] 		= 'books/book_isbn';
		$data['events'] 		= $events;
		$data['page_title'] 	= _l('book_isbn');
		$data['action_ajax'] 	= base_url('admin/ajax_book_isbn/isbn');
		$action_url 			= 'admin/book_isbn_form/isbn/edit/';
		
		if ($param1 == 'amazon') {
			$data['page_title'] 	= _l('book_amazon_assign');
			$data['action_ajax'] 	= base_url('admin/ajax_book_isbn/amazon');
			$action_url 			= 'admin/book_isbn_form/amazon/edit/';
		}

		$data['actions'] 		= [
			[
				'key'	=> _l('assign_isbn_amazon'),
				'url'	=> $action_url,
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function book_isbn_form($param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if ($param2 == 'edit') {
			$data['page_name'] 		= 'books/book_isbn_form';
			$data['page_title'] 	= _l('book_isbn_amazon_edit');
			$data['action'] 		= base_url('admin/book_isbn/isbn/edit/' . (int)$param3);

			if ($param1 == 'amazon') {
				$data['action'] 		= base_url('admin/book_isbn/amazon/edit/' . (int)$param3);
			}

			$data['id'] 			= (int)$param3;
			$data['details'] 		= $this->book_model->get($param3);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_book_isbn($type = 'isbn') {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($type == 'amazon') {
			$filter_data['empty_amazon'] = 1;
		} else {
			$filter_data['empty_isbn'] = 1;
		}

        if (!empty($this->input->get('event_id'))) {
            $filter_data['event_id']    = $this->input->get('event_id');
		}
		
		$resultsss = $this->bookstore_model->get_all($filter_data);
		$this->load->model('admin/Dashboard_model', 'dashboard_model');
		$results = $this->dashboard_model->book_isbn_amazon_books($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info 	= $this->user_model->get($result['user_id']);
			$event_info = $this->event_model->get($result['event_id'] ?? 0);

			// $json['data'][] = [
			// 	'sn'					=> $filter_data['start'] + 1 + $key,
			// 	'book_id'				=> $result['id'],
			// 	'book_name'				=> $result['name'],
			// 	'author_name'			=> $result['author_name'],
			// 	'version'				=> $result['version'],
			// 	'copies'				=> $result['sold'],
			// 	'location'				=> $user_info['location'] ?? '',
			// 	'source'				=> $user_info['source'] ?? '',
			// 	'actions'				=> ['id' => $result['id'], 'status' => $result['status']?? 0],
			// ];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'book_id'				=> $result['book_id'],
				'book_name'				=> $result['book_name'],
				'author_name'			=> $result['author_name'],
				'version'				=> $result['version'],
				'copies'				=> $result['sold'],
				'location'				=> $user_info['location'] ?? '',
				'source'				=> !empty($event_info) ? $event_info['label'] : strtoupper($result['book_region']),
				'actions'				=> ['id' => $result['book_id'], 'status' => $result['status']?? 0],
			];
		}

		output_json($json);
	}

	public function export_book_isbn() {
		$json = [];

		$filter_data = [
			'ship_status' => '0'
		];

		if (!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->event_book_isbn_limit_model->get_all($filter_data)['rows'] ?? [];

		$awards = [];

		foreach ($results as $result) {

			if (!empty($result['name']) && !empty($result['email']) && !empty($result['mobile'])) {
				$user_info = $this->user_model->get($result['user_id']);
	
				$awards[] = [
					'request_id'		=> $result['id'],
					'event_id'			=> $result['event_id'],
					'user_id'			=> $result['user_id'],
					'user_name'			=> !empty($user_info) ? ($user_info['first_name'] . ' ' . $user_info['last_name']) : '',
					'name'				=> $result['name'] ?? '',
					'email'				=> $result['email'] ?? '',
					'mobile'			=> $result['mobile'] ?? '',
					'address'			=> $result['address'] ?? '',
					'zipcode'			=> $result['zipcode'] ?? '',
					'landmark'			=> $result['landmark'] ?? ''
				];
	
				$this->event_book_isbn_limit_model->edit($result['id'], [
					'ship_status' 	=> 1,
					'date_shipped' 	=> date('Y-m-d H:i:s')
				]);
			}

		}

		self::_downloadCsv($awards, 'awards_address');

		output_json($json);
	}
}
