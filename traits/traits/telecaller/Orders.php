<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Orders {
	public function orders($param1 = NULL, $param2 = NULL) {
		$data['page_name'] 			= 'order/index';
		$data['heading'] 			= _l('orders');
		$data['page_title'] 		= _l('orders');
		$data['navigation'] 		= 'nav';
		$data['status'] 			= 0;
		$data['action_ajax'] 		= base_url('telecaller/ajax_orders');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_orders($status = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'sort'				=> 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'ne_status'	 		=> 0
		];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('filter_printer_id'))) {
			$filter_data['assign_printer_id'] = (int)$this->input->get('filter_printer_id');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('assign_printer_id')) {
			$filter_data['assign_printer_id'] = $this->input->get('assign_printer_id') == 'NA'
				? 0
				: (int)$this->input->get('assign_printer_id');
		}

		if ($this->input->get('printing_status')) {
			$filter_data['printing_status'] = $this->input->get('printing_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = (int)$this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('book_id')) {
			$filter_data['book_id'] = (int)$this->input->get('book_id');
		}

		if ($this->input->get('book_slug')) {
			$filter_data['book_slug'] = $this->input->get('book_slug');
		}

		if ($this->input->get('book_isbn')) {
			$filter_data['book_isbn'] = $this->input->get('book_isbn');
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('quantity_le')) {
			$filter_data['quantity_le'] = (int)$this->input->get('quantity_le');
		}

		if ($this->input->get('quantity_ge')) {
			$filter_data['quantity_ge'] = (int)$this->input->get('quantity_ge');
		}

		if ($this->input->get('page_count_le')) {
			$filter_data['page_count_le'] = (int)$this->input->get('page_count_le');
		}

		if ($this->input->get('page_count_ge')) {
			$filter_data['page_count_ge'] = (int)$this->input->get('page_count_ge');
		}

		if ($this->input->get('stock_status')) {
			$filter_data['stock_status'] = $this->input->get('stock_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('ext_transaction_id')) {
			$filter_data['ext_transaction_id'] = $this->input->get('ext_transaction_id');
		}

		if ($this->input->get('order_code')) {
			$filter_data['order_code'] = $this->input->get('order_code');
		}

		if ($this->input->get('has_isbn')) {
			$filter_data['has_isbn'] = $this->input->get('has_isbn') == 2 ? 0 : 1;
		}

		if ($this->input->get('has_amazon_url')) {
			$filter_data['has_amazon_url'] = $this->input->get('has_amazon_url') == 2 ? 0 : 1;
		}

		if ($this->input->get('mobile')) {
			$filter_data['mobile'] = $this->input->get('mobile');
		}

		if ($this->input->get('email')) {
			$filter_data['email'] = $this->input->get('email');
		}

		if ($this->input->get('name')) {
			$filter_data['name'] = $this->input->get('name');
		}

		if ($this->input->get('assignment_code')) {
			$filter_data['assignment_code'] = $this->input->get('assignment_code');
		}

		if ($this->input->get('currency_id')) {
			$filter_data['currency_id'] = $this->input->get('currency_id');
		}

		if ($status) {
			$filter_data['status'] = (int)$status;
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id']);

			$printer_info = $this->user_model->get($result['assign_printer_id']);
			$printer_assign_info = $this->printer_assign_log_model->get_all([
				'order_id'	=> $result['id'],
			])['rows'];

			$customer_info = $this->user_model->get($result['user_id']);

			$printer_assign_info = !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> _order_code($result, $shipping_tracking_info),
				'customer'			=> $customer_info['first_name'] . ' ' . $customer_info['last_name'] . ' <small> <br />' . $customer_info['email'] . '</small>' . ' <small>' . $customer_info['mobile'] . '</small>',
				'product'			=> _op_name($products, $result),
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'printer'          	=> _o_printer($result, $printer_info, $printer_assign_info),
				'history'			=> self::_getHistory($result['id']),
				'actions'			=> vsprintf('<button type="button" class="btn btn-warning btn-sm btn-comment" data-toggle="modal" data-target="#commentModel" data-id="%s">%s</button>', [
					$result['id'],
					_l('add_comment'),
				]),
			];
		}

		output_json($json);
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

	public function order_details($id = false) {
		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('telecaller/orders'), 'refresh');
		}

		$order_info = $this->order_model->get($id);

		if (empty($order_info)) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('telecaller/orders'), 'refresh');
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

		$data['page_name'] 		= 'order/order_info';
		$data['page_title'] 	= _l('Order Details');

		$this->load->view('backend/index', $data);
	}

	private function _getHistory($order_id = 0) {
		$comments = $histories = [];

		foreach ($this->order_history_model->get_all([
			'order_id'	=> $order_id,
		])['rows'] ?? [] as $item) {
			$histories[] = vsprintf('%s - %s', [
				$item['description'],
				formatDate($item['date_added']),
			]);
		}

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

		return vsprintf('<a class="text-primary" data-toggle="tooltip" title="%s"><i class="fa fa-info-circle"></i> %s %s</a><i class="text-danger fa fa-exclamation-triangle" data-toggle="tooltip" title="%s"></i> %s', [
			implode("\n", $histories),
			$agent_info['first_name'] ?? '',
			$agent_info['last_name'] ?? '',
			implode("\n", array_slice($comments, 1)),
			$comments[0] ?? '',
		]);
	}
}
