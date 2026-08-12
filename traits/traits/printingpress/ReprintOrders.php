<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ReprintOrders {
	public function reprint_new_order($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/reprint_orders';
		$data['heading'] 		= _l('reprint_orders');
		$data['page_title'] 	= _l('reprint_orders');
		$data['action_ajax'] 	= base_url('printingPress/reprint_ajax_order/1');
		$data['status'] 		= 1;

		$this->load->view('backend/index', $data);
	}

	public function reprint_ajax_order($status = 1) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'status'			=> (int)$status,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (in_array($this->session->userdata('role_id'), [12])) {
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

		$results = $this->reprint_order_model->reprintOrders($filter_data);

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

			if ($book_info['status'] == 1 && ($status == 2 || $status == 4)) {
				$download_link = self::_getDownloadLink($book_info['book_id'], $result['version'], $result['currency_code'] === 'INR');
			} else {
				$download_link = '';
			}

			$type = json_decode($result['option'], 1)['name'];
			$printer_info = !empty($result['assign_printer_id']) ? $this->user_model->get($result['assign_printer_id']) : '';

			$json['data'][] = [
				'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
					$result['ids'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'sku'				=> _o_b_code($result['id'], $result['version'], $type),
				'name'				=> vsprintf('%s v%s <br>Pages:: %s', [
					$result['name'],
					$result['version'],
					$total_pages * 2 + 1,
				]),
				'type'				=> $type,
				'quantity'			=> $result['quantity'],
				'download_link'		=> $download_link,
				'author_name'		=> $book_info['author_name'],
				'assign_date'		=> formatDate($result['date_added']),
				'printed'			=> (!empty($result['assign_printer_id']) && $result['printing_status'] !== '0') ? 'true' : 'false',
				'assign'          	=> !empty($result['assign_printer_id']) ? $printer_info['first_name'] . ' ' . $printer_info['last_name'] : 'NA',
				'actions'			=> '',
			];
		}

		output_json($json);
	}

	public function reprint_in_print_order($param1 = NULL, $param2 = NULL) {
		$this->load->model('user/Student_model', 'student_model');

		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/reprint_orders';
		$data['heading'] 		= _l('in_print_orders');
		$data['page_title'] 	= _l('in_print_orders');
		$data['action_ajax'] 	= base_url('printingPress/reprint_ajax_order/2');
		$data['status'] 		= 2;

		$data['printer_list'] 	= [];

		$this->load->view('backend/index', $data);
	}

	public function reprint_send_in_print($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->reprint_order_model->edit($id, [
				'status' 			=> 2,
				'date_in_print' 	=> date('Y-m-d H:i:s'),
			]);
		}

		redirect(base_url('printingPress/reprint_in_print_order'));
	}

	public function reprint_send_bulk_inprint() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->reprint_order_model->edit($id, [
					'status' 			=> 2,
					'date_in_print' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('printingPress/reprint_in_print_order');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function reprint_verify_print($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/reprint_orders';
		$data['heading'] 		= _l('verify_print');
		$data['page_title'] 	= _l('verify_print');
		$data['action_ajax'] 	= base_url('printingPress/reprint_ajax_order/4');
		$data['status'] 		= 4;

		$data['printer_list'] 	= [];

		$this->load->view('backend/index', $data);
	}

	public function reprint_send_verify_print($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->reprint_order_model->edit($id, [
				'status' 			=> 4,
				'date_verified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		redirect(base_url('printingPress/reprint_verify_print'));
	}

	public function reprint_send_bulk_verify_print() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->reprint_order_model->edit($id, [
					'status' 			=> 4,
					'date_verified' 	=> date('Y-m-d H:i:s'),
				]);
			}

			$json['redirect'] = base_url('printingPress/reprint_verify_print');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}

	public function reprint_printed_order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('printingPress') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/reprint_orders';
		$data['heading'] 		= _l('printed_orders');
		$data['page_title'] 	= _l('printed_order');
		$data['action_ajax'] 	= base_url('printingPress/reprint_ajax_order/3');
		$data['status'] 		= 3;

		$data['printer_list'] 	= [];

		$this->load->view('backend/index', $data);
	}

	public function reprint_send_printed($ids = '') {
		$ids = explode(',', $ids);

		foreach ($ids as $id) {
			$this->reprint_order_model->edit($id, [
				'status' 			=> 3,
				'date_printed' 		=> date('Y-m-d H:i:s'),
			]);

			$assign_info = $this->reprint_order_model->get($id);

			// if all books of this order are printed
			if (!$this->db->get_where('reprint_order', [
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

		redirect(base_url('printingPress/reprint_printed_order'));
	}

	public function reprint_send_bulk_prited() {
		$json = [];

		if ($this->input->post('ids')) {
			$ids = explode(',', $this->input->post('ids'));

			foreach ($ids as $id) {
				$this->reprint_order_model->edit($id, [
					'status' 			=> 3,
					'date_printed' 		=> date('Y-m-d H:i:s'),
				]);

				$assign_info = $this->reprint_order_model->get($id);

				// if all books of this order are printed
				if (!$this->db->get_where('reprint_order', [
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

			$json['redirect'] = base_url('printingPress/reprint_printed_order');
		} else {
			$json['error'] = _l('unknown_error');
		}

		output_json($json);
	}
}
