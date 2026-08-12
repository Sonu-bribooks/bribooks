<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BlackWhiteOrders {
	public function bw_orders($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_new_orders');
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders');

		$this->load->view('backend/index', $data);
	}

	public function bw_all_order($param1 = NULL, $param2 = NULL) {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';

		$data['page_name'] 		= 'bw_order/index';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('b_&_w_all_orders');
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders');
		$data['status'] 		= 0;

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'history',
			'order_date',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_new_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$color_inprint_orders = $this->dropshipper_order_model->printerAssignData([
			'assign_printer_id'			=> $this->session->userdata('user_id'),
			'status'					=> 2,
			'option_type'				=> [1],
		])['total'];

		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= base_url('dropShipper/send_bulk_inprint');
		$data['in_print_text'] 		= _l('bulk_send_to_inprint');


		if (!empty($color_inprint_orders)) {
			$data['in_print_attr'] 		= 'disabled';
			$data['in_print_action'] 	= 'javascript:void(0)';
			$data['in_print_text'] 		= _l('first_clear_your_in_print_tab_of_color_order');
		}

		$data['page_name'] 			= 'bw_order/index';
		$data['headeing'] 			= _l('bw_orders');
		$data['page_title'] 		= _l('b_&_w_new_orders');
		$data['action_ajax'] 		= base_url('dropShipper/bw_ajax_orders/1');
		$data['action_event'] 		= base_url('dropShipper/bw_ajax_order_event/1');
		$data['status'] 			= 1;

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_ajax_order($status = 1) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'status'			=> (int)$status,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$filter_data['assign_printer_id'] = $this->session->userdata('user_id');

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('assign_date'))) {
			$filter_data['date_added'] = $this->input->get('assign_date');
		}

		$filter_data['option_type'] = [2];

		$results = $this->dropshipper_order_model->printerAssignData($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['record'] 			= $results;
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info 		= $this->book_version_model->getByVersion($result['id'], $result['version']);

			if (empty($book_info)) continue;

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			if ($book_info['status'] == 1) {
				$download_link = vsprintf('<a href="%s" class="btn btn-primary btn-sm p-1">%s</a>', [
					base_url('dropShipper/printGreyBook/' . $book_info['book_id'] . '/' . $result['version']),
					_l('download_book'),
				]);
			} else {
				$download_link = '';
			}

			$type = json_decode($result['option'], 1)['name'];
			$printer_info = !empty($result['assign_printer_id']) ? $this->user_model->get($result['assign_printer_id']) : '';

			$new_orders_count = $this->dropshipper_order_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 1,
			]) ?? 0;

			$in_print_orders_count = $this->dropshipper_order_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 2,
			]) ?? 0;

			$verify_orders_count = $this->dropshipper_order_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 4,
			]) ?? 0;

			$printed_orders_count = $this->dropshipper_order_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 3,
			]) ?? 0;

			$json['data'][] = [
				'#'					=> vsprintf('<input type="checkbox" data-order="' . $result['order_ids'] . '" class="select-me" value="%s">', [
					$result['ids'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'book_id'			=> _o_b_code($result['id'], $result['version'], $type),
				'name'				=> vsprintf('%s v%s <br>Pages:: %s', [
					$book_info['name'],
					$result['version'],
					$total_pages * 2 + 1,
				]),
				'type'				=> $type,
				'quantity'			=> $result['quantity'],
				'download_link'		=> $download_link,
				'author_name'		=> $book_info['author_name'],
				'order_code'	    => $result['order_code'],
				'assign_date'		=> date('M j, Y', strtotime($result['date_added'])),
				'history'           => self::_getHistory($result['order_ids']),
				'actions'			=> _action_btn($result, $status),
			];
		}

		output_json($json);
	}

	public function bw_in_print_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_in_print_order');
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_order/2');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/2');
		$data['status'] 		= 2;
		$data['last_download'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 1,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];
		$data['last_request'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 0,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];

		$data['fields'] = [
			'#',
			'sn',
			'book_id',
			'name',
			'author_name',
			'download_link',
			'type',
			'quantity',
			'order_code',
			'assign_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_send_in_print($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->dropshipper_assignlog_model->edit($id, [
				'status' 			=> 2,
				'date_in_print' 	=> date('Y-m-d H:i:s'),
			]);
		}

		redirect(base_url('/dropShipper/bw_in_print_order'));
	}

	public function bw_send_bulk_inprint() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->dropshipper_assignlog_model->edit($id, [
					'status' 			=> 2,
					'date_in_print' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('/dropShipper/bw_in_print_order');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function bw_verify_print($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['headeing'] 		= _l('bw_verify_print');
		$data['page_title'] 	= _l('B & W QA/QC');
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/8');
		$data['status'] 		= 8;
		$data['last_download'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 1,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];
		$data['last_request'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 0,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_send_verify_print($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->dropshipper_assignlog_model->edit($id, [
				'status' 			=> 4,
				'date_verified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		redirect(base_url('/dropShipper/bw_verify_print'));
	}

	public function bw_send_bulk_verify_print() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->dropshipper_assignlog_model->edit($id, [
					'status' 			=> 4,
					'date_verified' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('/dropShipper/bw_verify_print');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function bw_printed_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_printed_order');
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_order/3');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/3');
		$data['status'] 		= 3;

		$this->load->view('backend/index', $data);
	}

	public function bw_send_printed($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->dropshipper_assignlog_model->edit($id, [
				'status' 			=> 3,
				'date_printed' 		=> date('Y-m-d H:i:s'),
			]);

			$assign_info = $this->dropshipper_assignlog_model->get($id);

			// if all books of this order are printed
			if (!$this->db->get_where('dropshipper_assign_logs', [
				'order_id'		=> (int)$assign_info['order_id'],
				'printer_id'	=> (int)$this->session->userdata('user_id'),
				'status !='		=> 3,
			])->row_array()) {
				$this->order_model->edit($assign_info['order_id'], [
					'printing_status' 	=> 1,
					'status'			=> 8,
				]);
			}
		}

		redirect(base_url('/dropShipper/bw_printed_order'));
	}

	public function bw_send_bulk_prited() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->dropshipper_assignlog_model->edit($id, [
					'status' 			=> 3,
					'date_printed' 		=> date('Y-m-d H:i:s'),
				]);

				$assign_info = $this->dropshipper_assignlog_model->get($id);

				// if all books of this order are printed
				if (!$this->db->get_where('dropshipper_assign_logs', [
					'order_id'		=> (int)$assign_info['order_id'],
					'printer_id'	=> (int)$this->session->userdata('user_id'),
					'status !='		=> 3,
				])->row_array()) {
					$this->order_model->edit($assign_info['order_id'], [
						'printing_status' 	=> 1,
						'status'			=> 8,
					]);
				}
			}

			$json['redirect'] 	= base_url('/dropShipper/bw_printed_order');
		} else {
			$json['error'] 		= _l('unknown_error');
		}

		output_json($json);
	}

	public function bw_submit_assign() {
		if ($this->input->post()) {
			$ids = $this->input->post('ids');

			foreach ($ids as $key => $value) {
				if ($this->order_model->edit($value, [
					'assign_printer_id' => $this->input->post('reviewer_id')
				])) {
					$this->dropshipper_assignlog_model->add([
						'printer_id' 	=> $this->input->post('reviewer_id'),
						'order_id' 		=> $value,
						'manager_id' 	=> $this->session->userdata('user_id')
					]);
				};
			}
		}
	}

	public function bw_ajax_order_event($status = 1) {
		$json = [];

		$start_date = date('Y-m-d', strtotime($this->input->get('start')));
		$end_date = date('Y-m-d', strtotime($this->input->get('end')));

		for ($order_date = $start_date; $order_date <= $end_date; $order_date = date('Y-m-d', strtotime('+1 day', strtotime($order_date)))) {
			$total_copies = $this->dropshipper_order_model->printerStats([
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'status'			=> $status,
				'date_added'		=> date('Y-m-d', strtotime($order_date))
			]) ?? 0;

			$start = date('Y-m-d H:i:s', strtotime($order_date . ' 00:00:00'));
			$end = date('Y-m-d H:i:s', strtotime($order_date. ' 24:00:00'));

			if (empty($total_copies)) continue;

			$json[] = [
				'id'				=> $order_date,
				'class_id'			=> '',
				'title'				=> vsprintf('%s:: %s', [
					($status == 4 ? _l('in_verify') : _l('printed')),
					$total_copies,
				]),
				'start'				=> $start,
				'end'				=> $end,
				'slot'				=> $order_date,
				'className'			=> 'bg-' . ($status == 4 ? 'info' : 'green'),
				'cellColor' 		=> '',
				'description'		=> vsprintf('%s:: %s', [
					($status == 4 ? _l('in_verify') : _l('printed')),
					$total_copies,
				]),
			];
		}

		output_json($json);
	}

	public function bw_afs($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_available_for_shipping');
		$data['page_title'] 	= _l('b_&_w_available_for_shipping');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/21');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/21');

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_ready_to_ship($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_ready_to_ship');
		$data['page_title'] 	= _l('b_&_w_ready_to_ship');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/9');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/9');

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_shipped_orders($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_shipped_orders');
		$data['page_title'] 	= _l('b_&_w_shipped_orders');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/3');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/3');

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_delivered_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_delivered_order');
		$data['page_title'] 	= _l('b_&_w_delivered_order');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/4');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/4');

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_return_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_return_order');
		$data['page_title'] 	= _l('b_&_w_return_order');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/15');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/15');

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_escalated_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_delivered_order');
		$data['page_title'] 	= _l('b_&_w_delivered_order');
		$data['status'] 		= 93;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/93');
		// $data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/4');

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'order_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function bw_reprint_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true ) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['heading'] 		= _l('bw_reprint_order');
		$data['page_title'] 	= _l('b_&_w_reprint_order');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/bw_ajax_orders/10');
		$data['action_event'] 	= base_url('dropShipper/bw_ajax_order_event/10');

		$this->load->view('backend/index', $data);
	}

	public function bw_ajax_orders($status = 0, $assignment_code = '', $site_code = '') {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'sort'				=> 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'ne_status'	 		=> 0,
		];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if ($status) {
			$filter_data['status'] = (int)$status;

			if ($status == 21 && $filter_data['sort'] == 'order.#') {
				$filter_data['sort'] = 'order.date_added';
				$filter_data['order'] = 'ASC';
			}
		}

		$color_inprint_orders = $this->dropshipper_order_model->printerAssignData([
			'assign_printer_id'			=> $this->session->userdata('user_id'),
			'status'					=> 2,
			'option_type'				=> [1],
		])['total'];

		// printed and printed with ebook
		$filter_data['order_type'] = [1];
		$filter_data['assign_printer_id'] = $this->session->userdata('user_id');

		if (empty($assignment_code)) {
			$filter_data['option_type'] = [2];

			$filter_data['ne_option_type'] = [1];
		}

		$option_type = '2';

		if (!empty($this->input->get('assign_date'))) {
			$filter_data['date_added'] = $this->input->get('assign_date');
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products 				= $this->order_model->getProducts($result['id'], $filter_data);
			$site_info 				= $this->site_model->get($result['site_id']);
			$printer_assign_info 	= $this->dropshipper_assignlog_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'paperback'
			])['rows'];

			$printer_info 			= !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];
			$customer_info 			= $this->user_model->get($result['user_id']);
			$printer_assign_info 	= !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];
			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';


			$json['data'][] = [
				'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
					$result['id'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> vsprintf('<a href="%s">%s</a><br>%s', [
					base_url('dropShipper/order_details/' . $result['id']),
					$result['order_code'],
					_dosb($result['status'])
				]),
				'product'			=> _dropshipper_op_name($products, $result),
				'weight'			=> $result['weight'] ?? 0 . ' gm',
				'status'			=> _sd($result['status']),
				'order_date'		=> formatDate($result['date_added']),
				'history'			=> self::_getHistory($result['id']),
				'actions'			=> _co_action_btn($result, $status, $color_inprint_orders),
			];
		}

		output_json($json);
	}
}
