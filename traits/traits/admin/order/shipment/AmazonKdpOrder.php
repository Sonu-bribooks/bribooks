<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AmazonKdpOrder {
	public function amazon_kdp_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'shipment/amazon_kdp_order';
		$data['heading'] 		= _l('amazon_kdp_order');
		$data['page_title'] 	= _l('amazon_kdp_order');
		$data['status'] 		= '';
		$data['action_ajax'] 	= base_url('admin/ajax_amazon_kdp_order');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_amazon_kdp_order() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'sort'				=> 'amazon_kdp_order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		$results = $this->amazon_kdp_order_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'book_name'			=> $result['book_name'],
				'author_name'		=> $result['author_name'],
				'quantity'			=> $result['quantity'],
				'isbn'				=> $result['isbn'],
				'marketplace'		=> $result['marketplace'],
				'currency_code'		=> $result['currency'],
				'price'				=> $result['price_without_tax'],
				'order_date'		=> $result['order_date'],
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'is_duplicate'		=> ($result['is_duplicate'] == '1') ? '1' : ''
			];
		}

		output_json($json);
	}

	public function export_amazon_book_published() {
		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		$results = $this->amazon_book_model->get_all($filter_data);

		$amazon_book_published = [];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_model->get($result['book_id']);
			if(empty($book_info))
				continue;

			$student_info = $this->student_model->get($book_info['user_id']);
			if(empty($student_info))
				continue;

			$site_info = $this->site_model->get($student_info['site_id']);
			if(empty($site_info))
				continue;

			$state_info = $this->state_model->get($site_info['state_id']);

			$city_info = $this->city_model->get($site_info['city_id']);

			$data = [];
			$data['School Name'] = $site_info['name'];
			$data['School State'] = $state_info['name'];
			$data['School City'] = $city_info['name'];
			$data['Author Name'] = $book_info['author_name'];
			$data['Book Name'] = $book_info['name'];
			$data['KDP URL'] = $book_info['amazon_url'];
			$data['Book Published Date'] = $book_info['date_published'];

			$amazon_book_published[] = $data;
		}

		self::_downloadCsv($amazon_book_published, 'amazon_book_published_');

		output_json([]);
	}
}
