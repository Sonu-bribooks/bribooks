<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('invoice');

trait Orders {
	use InvoiceDownload;

	public function order_details($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', _l('invalid_request.'));
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}

		$order_info = $this->order_model->get($id);

		if (empty($order_info) || $order_info['assign_printer_id'] != $this->session->userdata('user_id')) {
			$this->session->set_flashdata('error_message', _l('invalid_request.'));
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}

		$data['order_info'] 	= $order_info;
		$data['products'] 		= $this->order_model->getProducts($id);
		$data['address']  		= $this->address_model->getByID($order_info['address_id']);
		$data['user']	  		= $this->user_model->get($order_info['user_id']);
		$data['histories']		= $this->order_history_model->get_all([
			'order_id' => $id
		])['rows'] ?? [];
		$data['comments']		= $this->order_comment_model->get_all([
			'order_id' => $id
		])['rows'] ?? [];

		$data['page_name'] 		= 'order_details';
		$data['page_title'] 	= _l('order_details');

		$this->load->view('backend/index', $data);
	}

	public function orders($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/index';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('new_orders');
		$data['action_ajax'] 	= base_url('dropShipper/ajax_in_print_orders/1');

		$this->load->view('backend/index', $data);
	}

	public function all_orders($param1 = NULL, $param2 = NULL) {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'order/index';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('all_orders');
		$data['action_ajax'] 		= base_url('dropShipper/ajax_orders');
		$data['status'] 			= 0;

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

	public function new_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$bw_inprint_orders = $this->dropshipper_order_model->printerAssignData([
			'assign_printer_id'			=> $this->session->userdata('user_id'),
			'status'					=> 2,
			'option_type'				=> [2],
		])['total'];

		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= base_url('dropShipper/send_bulk_inprint');
		$data['in_print_text'] 		= _l('bulk_send_to_inprint');

		if (!empty($bw_inprint_orders)) {
			$data['in_print_attr'] 		= 'disabled';
			$data['in_print_action'] 	= 'javascript:void(0)';
			$data['in_print_text'] 		= 'First Clear Your In Print Tab Of B&W Order';
		}

		$data['page_name'] 		= 'order/index';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('new_orders');
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/1');
		// $data['action_event'] 	= base_url('dropShipper/ajax_order_event/1');
		$data['status'] 		= 1;

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

	public function in_print_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/index';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('in_print_orders');
		$data['action_ajax'] 	= base_url('dropShipper/ajax_order/2');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/2');
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
			'assignment_code',
			'assign_date',
			'history',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function send_in_print($ids = '') {
		$this->dropshipper_assignlog_model->editByOrderId($this->input->post('id'), [
			'status' 			=> 2,
			'date_in_print' 	=> date('Y-m-d H:i:s'),
		]);

		$this->order_model->edit($this->input->post('id'), [
			'status' 			=> 2,
		]);

		redirect(base_url('/dropShipper/in_print_order'));
	}

	public function send_bulk_inprint() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->dropshipper_assignlog_model->editByOrderId($id, [
					'status' 			=> 2,
					'date_in_print' 	=> date('Y-m-d H:i:s'),
				]);

				$this->order_model->editById($id, [
					'status' 			=> 2,
				]);
			}

			$json['redirect'] 	= base_url('/dropShipper/in_print_order');
		} else {
			$json['error'] 		= _l('unknown_error');
		}

		output_json($json);
	}

	public function send_bulk_qaqc() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->dropshipper_assignlog_model->editByOrderId($id, [
					'status' 			=> 4,
				]);

				$this->order_model->editById($id, [
					'status' 			=> 21,
				]);
			}

			$json['redirect'] 	= base_url('/dropShipper/in_print_order');
		} else {
			$json['error'] 		= _l('unknown_error');
		}

		output_json($json);
	}

	public function verify_print($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/index';
		$data['headeing'] 		= _li('QA/QC orders');
		$data['page_title'] 	= _li('QA/QC orders');
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/8');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/8');
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

	public function send_verify_print($ids = '') {
		$ids = explode(',', $this->input->post('id'));

		foreach ($ids as $id) {
			$this->dropshipper_assignlog_model->edit($id, [
				'status' 			=> 3,
				'date_verified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$order_ids = explode(',', $this->input->post('order_id'));

		foreach ($order_ids as $order_id) {
			$this->order_model->edit($order_id, [
				'status' 			=> 8,
			]);
		}

		redirect(base_url('/dropShipper/verify_print'));
	}

	public function send_bulk_verify_print() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));
			$order_ids = explode(',', $this->input->post('order_ids'));

			foreach ($order_ids as $order_id) {
				$this->order_model->edit($order_id, [
					'status' 			=> 8,
				]);
			}

			foreach ($ids as $id) {
				$this->dropshipper_assignlog_model->edit($id, [
					'status' 			=> 3,
					'date_verified' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('/dropShipper/verify_print');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function printed_order($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/index';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('new_orders');
		$data['action_ajax'] 	= base_url('dropShipper/ajax_order/3');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/3');
		$data['status'] 		= 3;

		$this->load->view('backend/index', $data);
	}

	public function send_printed($ids = '') {
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

		redirect(base_url('/dropShipper/printed_order'));
	}

	public function send_bulk_prited() {
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

			$json['redirect'] = base_url('/dropShipper/printed_order');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function submit_assign() {
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

	public function ajax_order_event($status = 1) {
		$json = [];

		$start_date = date('Y-m-d', strtotime($this->input->get('start')));
		$end_date 	= date('Y-m-d', strtotime($this->input->get('end')));

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

	public function afs($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('available_for_shipping');
		$data['page_title'] 	= _l('available_for_shipping');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/21');

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

	public function ready_to_ship($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true || !in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('ready_to_ship');
		$data['page_title'] 	= _l('ready_to_ship');
		$data['status'] 		= 9;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/9');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/9');

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

	public function shipped_orders($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true || !in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('shipped_orders');
		$data['page_title'] 	= _l('shipped_orders');
		$data['status'] 		= 21;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/3');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/3');

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

	public function delivered_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true || !in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('delivered_order');
		$data['page_title'] 	= _l('delivered_order');
		$data['status'] 		= 4;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/4');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/4');

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

	public function return_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true || !in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('return_order');
		$data['page_title'] 	= _l('return_order');
		$data['status'] 		= 15;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/15');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/15');

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

	public function reprint_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true || !in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('user/Student_model', 'student_model');

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('reprint_order');
		$data['page_title'] 	= _l('reprint_order');
		$data['status'] 		= 10;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_order/10');
		$data['action_event'] 	= base_url('dropShipper/ajax_order_event/10');

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

	public function escalated_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('dropShipper') != true || !in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l('delivered_order');
		$data['page_title'] 	= _l('delivered_order');
		$data['status'] 		= 93;
		$data['action_ajax'] 	= base_url('dropShipper/ajax_orders/93');
		// $data['action_event'] 	= base_url('dropShipper/ajax_order_event/93');

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

	public function ajax_status() {
		$json = [];

		$order_info = $this->order_model->get($this->input->post('order_id'));

		if ($order_info) {
			if ($this->input->post('status') == 21) {
				$this->order_model->edit($this->input->post('order_id'), [
					'status' => $this->input->post('status')
				]);
				$this->dropshipper_assignlog_model->editByOrderId($this->input->post('order_id'), [
					'status' 			=> 4
				]);
			}

			$json['success'] = _l('order_moves_to_afs');
		} else {
			$json['error'] = _l('invalid_order');
		}

		output_json($json);
	}

	public function ajax_orders($status = 0, $assignment_code = '', $site_code = '') {
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

			if (($status == 21 || $status == 8) && $filter_data['sort'] == 'order.#') {
				$filter_data['sort'] = 'order.date_added';
				$filter_data['order'] = 'ASC';
			}
		}

		// printed and printed with ebook
		$filter_data['order_type'] = [1];
		$filter_data['assign_printer_id'] = $this->session->userdata('user_id');

		if (empty($assignment_code)) {
			$filter_data['option_type'] = [1];

			$filter_data['ne_option_type'] = [2];
		}

		$option_type = '2';

		if (!empty($this->input->get('assign_date'))) {
			$filter_data['date_added'] = $this->input->get('assign_date');
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$bw_inprint_orders = $this->dropshipper_order_model->printerAssignData([
			'assign_printer_id'			=> $this->session->userdata('user_id'),
			'status'					=> 2,
			'option_type'				=> [2],
		])['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id'], $filter_data);
			$site_info = $this->site_model->get($result['site_id']);

			// $printer_info = $this->user_model->get($result['assign_printer_id']);
			$printer_assign_info = $this->dropshipper_assignlog_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'paperback'
			])['rows'];

			$printer_info = !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];

			$customer_info = $this->user_model->get($result['user_id']);

			$printer_assign_info = !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			if ($status == 0) {
				$json['data'][] = [
					'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
						$result['id'],
					]),
					'sn'				=> $filter_data['start'] + 1 + $key,
					'order_code'		=> $result['order_code'].'<br>'._dosb($result['status']),
					'product'			=> _dropshipper_op_name($products, $result),
					'weight'			=> $result['weight'] ?? 0 . ' gm',
					'status'			=> _sd($result['status']),
					'history'			=> self::_getHistory($result['id']),
					'order_date'		=> formatDate($result['date_added']),
				];
			} else {
				$json['data'][] = [
					'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
						$result['id'],
					]),
					'sn'				=> $filter_data['start'] + 1 + $key,
					'order_code'		=> $result['order_code'],
					'product'			=> _dropshipper_op_name($products, $result),
					'weight'			=> $result['weight'] ?? 0 . ' gm',
					'status'			=> _sd($result['status']),
					'order_date'		=> formatDate($result['date_added']),
					'history'			=> self::_getHistory($result['id']),
					'actions'			=> _co_action_btn($result, $status, $bw_inprint_orders),
				];
			}
		}

		output_json($json);
	}

	public function ajax_order($status = 1) {
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

		if (in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
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

		$filter_data['option_type'] = [1];

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
				$download_link = sprintf('<a href="%s" class="btn btn-primary btn-sm p-1">Download Book</a>', base_url('dropShipper/printBook/' . $book_info['book_id'] . '/' . $result['version']));
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
				'#'					=> vsprintf('<input type="checkbox" data-order="'.$result['order_ids'].'" class="select-me" value="%s">', [
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
				'assignment_code'	=> $result['assignment_code'],
				'assign_date'		=> date('M j, Y', strtotime($result['date_added'])),
				'history'          	=> self::_getHistory($result['order_ids']),
				'actions'			=> _action_btn($result, $status),
			];
		}

		output_json($json);
	}

	public function ajax_update_order() {
		$json = [];

		$request = $this->input->post();

		if (!empty($request['order_id']) &&
			!empty($request['weight']) &&
			!empty($order_info = $this->order_model->get($request['order_id']))
		) {
			$this->order_model->edit($order_info['id'], [
				'weight'   	=> $request['weight'] ?? $order_info['weight']
			]);

			$json['success'] 	= _l('order_updated_successfully');
		} else {
			$json['error'] 		= _l('something_went_wrong!');
		}

		output_json($json);
	}

	public function download_manifest($order_id = 0) {
		$dir = FCPATH . 'uploads/pdfs/invoice/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		self::_orderManifest($order_id);
	}

	public function download_invoice($order_id = 0) {
		$this->load->library('BriBooksShipping_lib');
		$this->bribooksshipping_lib->generateInvoice($order_id);
	}

	public function download_label($order_id = 0) {
		$this->load->library('BriBooksShipping_lib');
		$this->bribooksshipping_lib->generateLabel($order_id);
	}

	public function add_order_comment() {
		$json = [];

		if ($order_info = $this->order_model->get($this->input->post('order_id'))) {
			if (!empty($order_info['status'])) {
				$this->order_comment_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'order_id' 		=> (int)$this->input->post('order_id'),
					'description' 	=> $this->input->post('comment'),
					'status' 		=> $order_info['status'],
				]);

				$json['success'] 	= _l('order_comment_added');
			} else {
				$json['error'] 		= _l('order_not_processed_yet');
			}
		} else {
			$json['error'] = _l('order_not_found');
		}

		output_json($json);
	}

	private function _getHistory($order_id = 0) {
		$comments = $histories = [];


		foreach ($this->order_comment_model->get_all([
			'order_id'	=> $order_id,
		])['rows'] ?? [] as $item) {
			$agent_info = !empty($item['manager_id']) ? $this->user_model->get($item['manager_id']) : [];

			$comments[] = vsprintf('%s %s : %s - %s', [
				$agent_info['first_name'] ?? '',
				$agent_info['last_name'] ?? '',
				$item['description'],
				formatDate($item['date_added']),
			]);
		}

		$packing_info = $this->order_packing_log_model->get_all([
			'order_id'	=> $order_id
		])['rows'];

		$packing_info = !empty($packing_info[0]) ? $packing_info[0] : [];

		$agent_info = !empty($packing_info['user_id']) ? $this->user_model->get($packing_info['user_id']) : [];

		return vsprintf('<a class="text-primary" data-toggle="tooltip" title="%s"><i class="fa fa-info-circle"></i> %s %s</a><i class="text-danger fa fa-exclamation-triangle" data-toggle="tooltip" title="%s"></i><br>%s', [
			implode("\n", $histories),
			$agent_info['first_name'] ?? '',
			$agent_info['last_name'] ?? '',
			implode("\n", array_slice($comments, 1)),
			$comments[0] ?? '',
		]);
	}
}
