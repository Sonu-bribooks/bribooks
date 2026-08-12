<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('invoice');

trait EbookOrders {
	use InvoiceDownload;

	private $currency_id = 47;

	public function ebook_orders($currency_id = 0) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 			= 'ebook_order/index';
		$data['heading'] 			= _l('eBook_orders');
		$data['page_title'] 		= _l('eBook_orders');
		$data['navigation'] 		= 'nav';
		$data['status'] 			= 0;
		$data['action_ajax'] 		= base_url('admin/ajax_ebook_orders/' . $currency_id);

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_ebook_orders($currency_id = 0) {
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
			$filter_data['assign_printer_id'] = $this->input->get('assign_printer_id') == 'NA' ? 0 : (int)$this->input->get('assign_printer_id');
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

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
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

		if (!empty($assignment_code)) {
			$filter_data['assignment_code'] = $assignment_code;
		}

		if ($this->input->get('assignment_code')) {
			$filter_data['assignment_code'] = $this->input->get('assignment_code');
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = $this->input->get('site_id');
		}

		if (!empty($site_code)) {
			$filter_data['site_code'] = $site_code;
		}

		if ($this->input->get('site_code')) {
			$filter_data['site_code'] = $this->input->get('site_code');
		}

		if (!empty($status)) {
			$filter_data['status'] = (int)$status;

			if ($status == 21) {
				$filter_data['sort'] = 'order.date_added';
				$filter_data['order'] = 'ASC';
			}
		}

		// domestic orders
		if ($currency_id) {
			$filter_data['currency_id'] = $currency_id;
		}

		$filter_data['order_type'] = [2, 3];

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id']);

			$site_info = $this->site_model->get($result['site_id']);

			// $printer_info = $this->user_model->get($result['assign_printer_id']);
			$printer_assign_info = $this->printer_assign_log_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'ebook'
			])['rows'];

			$printer_info = !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];

			$customer_info = $this->user_model->get($result['user_id']);

			$printer_assign_info = !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'#'					=> self::_renderCheckBox($result, $products),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> _order_code($result, $shipping_tracking_info, 'ebook'),
				'customer'			=> (!empty($customer_info)) ? $customer_info['first_name'] . ' ' . $customer_info['last_name'] . '<br /><small>' . $customer_info['email'] . '<br />' . $customer_info['mobile'] . '</small><br />' . '<strong>(' . $customer_info['source'] . ')</strong>' : '',
				'product'			=> _op_name($products, $result, 'ebook'),
				'weight_amount'		=> $result['weight'] . 'gm' . '<br>' . $result['currency_symbol'] . ' ' . $result['total'],
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'printer'          	=> _o_printer($result, $printer_info, $printer_assign_info),
				'history'			=> self::_getHistory($result['id']),
				'actions'			=> '',
				'is_international'	=> ($result['currency_id'] !== '47') ? '1' : ''
			];
		}

		output_json($json);
	}
}
