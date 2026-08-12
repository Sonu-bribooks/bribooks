<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BlackWhiteOrders {
	public function bw_orders($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/index';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_new_orders');
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_in_print_orders/1');


		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	public function bw_new_order($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$color_inprint_orders = $this->printer_stats_model->printerAssignData([
			'assign_printer_id'			=> $this->session->userdata('user_id'),
			'status'					=> 2,
			'option_type'				=> [1],
		])['total'];

		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= base_url('printingPress/send_bulk_inprint');
		$data['in_print_text'] 		= _l('bulk_send_to_inprint');


		if (!empty($color_inprint_orders)) {
			$data['in_print_attr'] 		= 'disabled';
			$data['in_print_action'] 	= 'javascript:void(0)';
			$data['in_print_text'] 		= _l('first_clear_your_in_print_tab_of_color_order');
		}

		$data['page_name'] 			= 'bw_order/orders';
		$data['headeing'] 			= _l('bw_orders');
		$data['page_title'] 		= _l('b_&_w_new_orders');
		$data['action_ajax'] 		= base_url('printingPress/bw_ajax_order/1');
		$data['action_event'] 		= base_url('printingPress/bw_ajax_order_event/1');
		$data['status'] 		= 1;

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

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

		if (in_array($this->session->userdata('role_id'), [12,15])) {
			$filter_data['assign_printer_id'] = $this->session->userdata('user_id');
		}

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

		$results = $this->printer_stats_model->printerAssignData($filter_data);

		// pr($filter_data);
		// pr($results, 1);

		$json['recordsTotal'] 		= $results['total'];
		$json['record'] 			= $results;
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_version_model->getByVersion($result['id'], $result['version']);

			if (empty($book_info)) continue;

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			if ($book_info['status'] == 1) {
				$download_link = self::_getGreyDownloadLink($book_info['book_id'], $result['version'], $result['currency_code'] === 'INR');
			} else {
				$download_link = '';
			}

			$type = json_decode($result['option'], 1)['name'];
			$printer_info = !empty($result['assign_printer_id']) ? $this->user_model->get($result['assign_printer_id']) : '';

			$new_orders_count = $this->printer_stats_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 1,
			]) ?? 0;

			$in_print_orders_count = $this->printer_stats_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 2,
			]) ?? 0;

			$verify_orders_count = $this->printer_stats_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 4,
			]) ?? 0;

			$printed_orders_count = $this->printer_stats_model->printerStats([
				'book_id'			=> $result['id'],
				'version'			=> $result['version'],
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'option'			=> $type,
				'status'			=> 3,
			]) ?? 0;

			$json['data'][] = [
				'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
					$result['ids'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'book_id'			=> _o_b_code($result['id'], $result['version'], $type),
				'name'				=> vsprintf('%s v%s <br>Pages:: %s', [
					$book_info['name'],
					$result['version'],
					$total_pages * 2 + 1,
				]),
				'stats'				=> vsprintf('<span class="badge badge-info">%s new</span><br><span class="badge badge-warning">%s in print</span><br><span class="badge badge-danger">%s verify print</span><br> <span class="badge badge-success">%s printed</span>', [
					$new_orders_count,
					$in_print_orders_count,
					$verify_orders_count,
					$printed_orders_count,
				]),
				'type'				=> $type,
				'quantity'			=> $result['quantity'],
				'download_link'		=> $download_link,
				'author_name'		=> $book_info['author_name'],
				'assignment_code'	=> $result['assignment_code'],
				'assign_date'		=> date('M j, Y', strtotime($result['date_added'])),
				'printed'			=> (!empty($result['assign_printer_id']) && $result['printing_status'] !== '0') ? 'true' : 'false',
				'assign'          	=> !empty($result['assign_printer_id']) ? $printer_info['first_name'] . ' ' . $printer_info['last_name'] : 'NA',
				'actions'			=> '', // ['id' => $result['ids']],
			];
		}

		output_json($json);
	}

	public function bw_in_print_order($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/orders';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_in_print_order');
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/2');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/2');
		$data['status'] 		= 2;
		$data['last_download'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 1,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];
		$data['last_request'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 0,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];

		$data['printer_list'] 	= [];

		$this->load->view('backend/index', $data);
	}

	public function bw_send_in_print($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->printer_assign_log_model->editById($id, [
				'status' 			=> 2,
				'date_in_print' 	=> date('Y-m-d H:i:s'),
			]);
		}

		redirect(base_url('/printingPress/bw_in_print_order'));
	}

	public function bw_send_bulk_inprint() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->printer_assign_log_model->editById($id, [
					'status' 			=> 2,
					'date_in_print' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('/printingPress/bw_in_print_order');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function bw_partial_print($book_id = '', $type = '', $version = '') {
		return;
		$data['page_name'] 		= 'bw_order/printing_status';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_partial_print');

		$type = explode(' ', urldecode($type));
		$type = array_shift($type);

		$filter_data = [
			'status'	 		=> 1,
			'printing_status'	=> 0,
			'version'			=> $version,
			'type'				=> $type,
			'book_id'			=> $book_id
		];

		if (in_array($this->session->userdata('role_id'), [12,15])) {
			$filter_data['assign_printer_id'] = $this->session->userdata('user_id');
		}

		$data['results'] = $this->printer_stats_model->printerBookStatus($filter_data);

		$this->load->view('backend/index', $data);
	}

	public function bw_send_partial_in_print() {
		return;
		$json = [];

		if ($this->input->post()) {
			$product_id = $this->input->post('product_id');
			$order_ids 	= $this->input->post('ids');
			$type 		= $this->input->post('type');

			foreach ($order_ids as $key => $value) {
				$this->printer_assign_log_model->edit([
					'order_id' 			=> $value,
					'product_id' 		=> $product_id,
					'option' 			=> urldecode($type),
					'status' 			=> 1,
				], [
					'status' 			=> 2,
					'date_in_print' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('/printingPress/bw_in_print_order');
		} else {
			$json['error'] = _l('something_went_wrong');
		}

		output_json($json);
	}

	public function bw_verify_print($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/orders';
		$data['headeing'] 		= _l('bw_verify_print');
		$data['page_title'] 	= _l('b_&_w_verify_print');
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/4');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/4');
		$data['status'] 		= 4;
		$data['last_download'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 1,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];
		$data['last_request'] 	= $this->printer_zip_download_model->get_all([
			'status'			=> 0,
			'printer_id'		=> (int)$this->session->userdata('user_id'),
		])['rows'][0] ?? [];

		$data['printer_list'] 	= [];

		$this->load->view('backend/index', $data);
	}

	public function bw_send_verify_print($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->printer_assign_log_model->editById($id, [
				'status' 			=> 4,
				'date_verified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		redirect(base_url('/printingPress/bw_verify_print'));
	}

	public function bw_send_bulk_verify_print() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->printer_assign_log_model->editById($id, [
					'status' 			=> 4,
					'date_verified' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('/printingPress/bw_verify_print');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function bw_printed_order($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'bw_order/orders';
		$data['headeing'] 		= _l('bw_orders');
		$data['page_title'] 	= _l('b_&_w_printed_order');
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/3');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/3');
		$data['status'] 		= 3;

		$data['printer_list'] 	= [];

		$this->load->view('backend/index', $data);
	}

	public function bw_send_printed($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->printer_assign_log_model->editById($id, [
				'status' 			=> 3,
				'date_printed' 		=> date('Y-m-d H:i:s'),
			]);

			$assign_info = $this->printer_assign_log_model->get($id);

			// if all books of this order are printed
			if (!$this->db->get_where('printer_assign_logs', [
				'order_id'		=> (int)$assign_info['order_id'],
				'printer_id'	=> (int)$this->session->userdata('user_id'),
				'status !='		=> 3,
			])->row_array()) {
				$this->order_model->edit($assign_info['order_id'], [
					'printing_status' 	=> 1,
					'status'			=> 8,
				]);

				// $this->order_history_model->add([
				// 	'order_id' 		=> $assign_info['order_id'],
				// 	'description' 	=> _order_history(8),
				// 	'status' 		=> 8,
				// ]);
			}
		}

		redirect(base_url('/printingPress/bw_printed_order'));
	}

	public function bw_send_bulk_prited() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->printer_assign_log_model->editById($id, [
					'status' 			=> 3,
					'date_printed' 		=> date('Y-m-d H:i:s'),
				]);

				$assign_info = $this->printer_assign_log_model->get($id);

				// if all books of this order are printed
				if (!$this->db->get_where('printer_assign_logs', [
					'order_id'		=> (int)$assign_info['order_id'],
					'printer_id'	=> (int)$this->session->userdata('user_id'),
					'status !='		=> 3,
				])->row_array()) {
					$this->order_model->edit($assign_info['order_id'], [
						'printing_status' 	=> 1,
						'status'			=> 8,
					]);

					// $this->order_history_model->add([
					// 	'order_id' 		=> $assign_info['order_id'],
					// 	'description' 	=> _order_history(8),
					// 	'status' 		=> 8,
					// ]);
				}
			}

			$json['redirect'] = base_url('/printingPress/bw_printed_order');
		} else {
			$json['error'] = _l('unknown_error');
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
					$this->printer_assign_log_model->add([
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
			$total_copies = $this->printer_stats_model->printerStats([
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
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [15])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'bw_order/orders';
		$data['heading'] 		= _l('bw_available_for_shipping');
		$data['page_title'] 	= _l('b_&_w_available_for_shipping');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/21');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/21');

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	public function bw_ready_to_ship($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [15])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'bw_order/orders';
		$data['heading'] 		= _l('bw_ready_to_ship');
		$data['page_title'] 	= _l('b_&_w_ready_to_ship');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/9');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/9');

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	public function bw_shipped_orders($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [15])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'bw_order/orders';
		$data['heading'] 		= _l('bw_shipped_orders');
		$data['page_title'] 	= _l('b_&_w_shipped_orders');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/3');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/3');

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	public function bw_delivered_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [15])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'bw_order/orders';
		$data['heading'] 		= _l('bw_delivered_order');
		$data['page_title'] 	= _l('b_&_w_delivered_order');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/4');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/4');

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	public function bw_return_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [15])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'bw_order/orders';
		$data['heading'] 		= _l('bw_return_order');
		$data['page_title'] 	= _l('b_&_w_return_order');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/15');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/15');

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	public function bw_reprint_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [15])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'bw_order/orders';
		$data['heading'] 		= _l('bw_reprint_order');
		$data['page_title'] 	= _l('b_&_w_reprint_order');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('printingPress/bw_ajax_order/10');
		$data['action_event'] 	= base_url('printingPress/bw_ajax_order_event/10');

		$data['printer_list'] = in_array($this->session->userdata('role_id'), [1, 13])
			? $this->student_model->get_by_role_id_in([12,15])
			: [];

		$this->load->view('backend/index', $data);
	}

	private function _getGreyDownloadLink($book_id, $version, $mrp = 0) {
		return vsprintf('<a href="%s" class="btn btn-primary btn-sm p-1">%s</a>', [
			base_url(sprintf('printingPress/printGreyBook/%s/%s/0/1/%d', $book_id, $version, $mrp)),
			_l('download_book')
		]);
	}
}
