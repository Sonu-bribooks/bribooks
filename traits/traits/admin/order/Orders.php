<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('invoice');
load_trait('admin/order/printer');
load_trait('admin/order/shipment');

use Dompdf\Dompdf;

trait Orders {
	use InvoiceDownload;

	private $currency_id = 47;

	private function _initOrderPageData($status = 'in_complete', $type = 'domestic') {
		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l(sprintf('%s_orders', $type));
		$data['page_title'] 	= _l($status);
		$data['order_type'] 	= $type;
		$data['navigation'] 	= 'nav';
		$data['status'] 		= ORDER_STATUS[$status] ?? 0;
		$data['action_ajax'] 	= base_url(sprintf('admin/%s/%s', $type == 'domestic' ? 'ajax_orders' : 'ajax_ge_orders', $data['status']));
		$data['action_print'] 	= base_url('admin/ajax_assign_order_to_printer/1');
		$data['action_reprint'] = base_url('admin/ajax_reprint_order/1');

		$data['timestamp_start']= strtotime('-30 days', time());
		$data['timestamp_end']	= time();

		$data['nav_base_url']	= base_url(sprintf('admin/%s/', $type == 'domestic' ? 'orders' : 'ge_orders'));
		$data['nav_tabs']['pre']= [
			[
				'color'	=> 'transparent',
				'url'	=> base_url('admin/all_orders'),
				'name'	=> _l('all_orders'),
				'id'	=> 'all_orders',
			],
			[
				'color'	=> $type == 'domestic' ? 'info' : 'danger',
				'url'	=> $type == 'domestic' ? base_url('admin/orders') : base_url('admin/ge_orders'),
				'name'	=> $type == 'domestic' ? _l('all_domestic') : _l('all_global'),
				'id'	=> $type == 'domestic' ? 'orders' : 'ge_orders',
			],
		];
		$data['nav_tabs']['post']= [
			[
				'color'	=> 'danger',
				'url'	=> $type == 'domestic' ? base_url('admin/auto_escalated_order') : base_url('admin/ge_auto_escalated_order'),
				'name'	=> _l('auto_escalated'),
				'id'	=> $type == 'domestic' ? 'auto_escalated_order' : 'ge_auto_escalated_order',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function all_orders($param1 = NULL, $param2 = NULL) {
		$data['page_name'] 		= 'orders';
		$data['heading'] 		= _l('orders');
		$data['page_title'] 	= _l('all_orders');
		$data['status'] 		= '';
		$data['action_ajax'] 	= base_url('admin/ajax_all_orders');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');

		$assignment_code = '';

		if ($this->printer_assignment_model->getByCode($param1)) {
			$assignment_code 		= $param1;
			$data['action_ajax'] 	= base_url('admin/ajax_all_orders/0/' . $assignment_code);
		}

		$site_code = '';

		if ($this->input->get('site_code')) {
			$site_code 				= $this->input->get('site_code');
			$data['action_ajax'] 	= base_url('admin/ajax_all_orders/0/0/' . $site_code);
		}

		$data['site_code']			= $site_code;
		$data['assignment_code']	= $assignment_code;

		$this->load->view('backend/index', $data);
	}

	public function orders($status = 'all_orders') {
		self::_initOrderPageData($status);
	}

	public function ajax_all_orders($status = 0, $assignment_code = '', $site_code = '') {
		$this->currency_id = '';
		self::ajax_orders($status, $assignment_code, $site_code);
	}

	public function ajax_orders($status = 0, $assignment_code = '', $site_code = '') {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'							=> (int)$this->input->get('start'),
			'limit'							=> (int)$this->input->get('length'),
			'search'						=> trim($this->input->get('search[value]')),
			'pickup_location_id'			=> $this->config->item('default_pickup_location_id'),
			'sort'							=> 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'							=> mb_strtoupper($this->input->get('order[0][dir]')),
			'ne_status'	 					=> 0
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

		if ($assignment_code) {
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

		if ($site_code) {
			$filter_data['site_code'] = $site_code;
		}

		if ($this->input->get('site_code')) {
			$filter_data['site_code'] = $this->input->get('site_code');
		}

		if ($this->input->get('order_country')) {
			$filter_data['order_country'] = $this->input->get('order_country');
		}

		if ($this->input->get('order_state')) {
			$filter_data['order_state'] = $this->input->get('order_state');
		}

		if ($this->input->get('customer_info')) {
			$filter_data['customer_info'] = $this->input->get('customer_info');
		}

		if ($status) {
			$filter_data['status'] = (int)$status;

			if ($status == 21) {
				$filter_data['sort'] = 'order.date_added';
				$filter_data['order'] = 'ASC';
			}
		}

		// printed and printed with ebook
		$filter_data['order_type'] = [1, 2];

		if (empty($assignment_code)) {
			$filter_data['option_type'] = [1];

			$filter_data['ne_option_type'] = [2];
		}

		$option_type = '2';

		// domestic orders
		if ($this->currency_id) {
			$filter_data['currency_id'] = $this->currency_id;
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id'], $filter_data);

			$site_info = $this->site_model->get($result['site_id']);

			// $printer_info = $this->user_model->get($result['assign_printer_id']);
			$printer_assign_info = $this->printer_assign_log_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'paperback'
			])['rows'];

			$printer_info = !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];

			$customer_info = $this->user_model->get($result['user_id']);

			$printer_assign_info = !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'#'					=> self::_renderCheckBox($result, $products),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> _order_code($result, $shipping_tracking_info, 'printed', 'paperback'),
				'customer'			=> (!empty($customer_info)) ? $customer_info['first_name'] . ' ' . $customer_info['last_name'] . '<br /><small>' . $customer_info['email'] . '<br />' . $customer_info['mobile'] . '</small><br />' . '<strong>(' . $customer_info['source'] . ')</strong>' : '',
				'product'			=> _op_name($products, $result),
				'weight_amount'		=> $result['weight'] . 'gm' . '<br>' . $result['currency_symbol'] . ' ' . $result['total'],
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'printer'		  	=> _o_printer($result, $printer_info, $printer_assign_info, 'paperback'),
				'history'			=> self::_getHistory($result['id']),
				'actions'			=> _oa_buttons($result, $products, $shipping_tracking_info, $assignment_code),
				'is_international'	=> ($result['currency_id'] !== '47') ? '1' : '',
				'is_black_white'	=> _is_option_type_exist($result['id'], $option_type) ? '1' : '',
				'is_clone'			=> !empty($result['parent_order_id']),
			];
		}

		output_json($json);
	}

	public function bulk_order_update() {
		$json = [];

		$order_ids 	= $this->input->post('ids');
		$status 	= $this->input->post('status');

		if (in_array($status, [2, 3, 4, 8, 9, 10, 15, 21])) {
			foreach ($order_ids as $order_id) {
				$order_info = $this->order_model->get($order_id);

				if ($order_info['status'] == $status) continue;

				if (empty($this->order_history_model->get_all([
					'order_id' 		=> $order_info['id'],
					'status' 		=> $status,
					'start'			=> 0,
					'limit'			=> 1
				])['rows'][0])) {
					$this->order_history_model->add([
						'order_id' 		=> $order_info['id'],
						'description' 	=> _order_history($status),
						'status' 		=> $status
					]);
				}

				$this->order_model->edit($order_info['id'], [
					'status'		=> (int)$status,
				]);

				if ($status == 8) {
					$this->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');

					$this->printer_assign_log_model->editByOrderId($order_info['id'], [
						'status' 			=> 3,
						'date_printed'		=> date('Y-m-d H:i:s')
					]);

					$this->order_model->edit($order_info['id'], [
						'printing_status'	=> 1
					]);
				} elseif ($status == 4) {
					$this->order_model->edit($order_info['id'], [
						'date_completed'	=> date('Y-m-d H:i:s')
					]);

					$this->load->library('Royalty_lib', 'royalty_lib');
					$this->royalty_lib->generateCredit($order_info['id']);

					$this->cron_model->add([
						'code'		  	=> 'deliveredOrderCron_' . $order_info['id'],
						'action'		=> 'alert_model->deliveredOrderCron',
						'data'		  	=> [$order_info['id']],
						'site_id'	   	=> $order_info['site_id'],
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);
				}

				CI_Events::trigger('system_access_log', [
					'method'	=> 'bulk_order_update_' . (int)$order_info['id'] . '_' . $status,
				]);
			}
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

	public function booked_orders($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/booked';
		$data['heading'] 		= _l('orders');
		$data['page_title'] 	= _l('booked_orders');
		$data['action_ajax'] 	= base_url('admin/ajax_booked_orders');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_booked_orders() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'status'			=> '2'
		];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id']);
			$product_name = '';

			if (!empty($products)) {
				foreach ($products as $product) {
					$type  = json_decode($product['option'], 1);
					$is_author_copy = ($result['user_id'] == $product['user_id']) ? 'true' : 'false';
					$product_name .= '<p>' . $product['name'] . ' by ' . $product['author_name'] . '<br /> <small><strong>Qty </strong>' . $product['quantity'] . '<br /> <strong> cover Type </strong> : <span class="' . ($type['name'] == 'Paperback' ? 'text-success' : 'text-primary bg-warning') . '"> <b>' . $type['name'] . '</b></span></small> </small> </p>';
				}
			}

			$printer_name = $this->user_model->get($result['assign_printer_id']);

			$json['data'][] = [
				'sn'				=> self::_renderCheckBox($result),
				'orders'			=> self::_renderCheckBox($result),
				'order_code'		=> _sd($result['shipping_status']) . _ourl($result['id'], $result['order_code']),
				'customer'			=> $result['first_name'] . ' ' . $result['last_name'] . ' <small> <br />' . $result['email'] . '</small>' . ' <small>' . $result['mobile'] . '</small>',
				'product'			=> $product_name,
				'weight_dimension'	=> $result['weight'] . 'gm',
				'order_amount'		=> $result['currency_symbol'] . ' ' . $result['total'] . '<small class="badge badge-success">Prepaid</small>',
				'order_date'		=> formatDate($result['date_added']),
				'printed'			=> ($result['printing_status'] !== '0') ? 'true' : 'false',
				'history'			=> self::_getHistory($result['id']),
				'assign'		  	=> ($result['assign_printer_id']) ? $printer_name['first_name'] . ' ' . $printer_name['last_name'] : 'NA',
				'status'			=> _sd($result['status']),
				'actions'			=> ''
			];
		}

		output_json($json);
	}

	public function order_details($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}

		$order_info = $this->order_model->get($id);

		if (empty($order_info)) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
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

		$data['page_name'] 		= 'order/order_details';
		$data['page_title'] 	= _l('Order Details');

		$this->load->view('backend/index', $data);
	}

	public function cancel($order_id = false) {
		if (!$order_id) return false;

		$order_info = $this->order_model->get($order_id);

		if (!$order_info['shipping_status']) return false;

		$courier_data = json_decode($order_info['shipping_tracking_info'], true);

		if (empty($courier_data) || empty($courier_data['shipment_id'])) return false;

		$this->load->library('couriers/shiprocket_lib');

		if (!empty($courier_data['order_id'])) {
			$cancel_order_response = $this->shiprocket_lib->cancelOrder(array($courier_data['order_id']));
		}

		if (!empty($courier_data['awb_code'])) {
			$response = $this->shiprocket_lib->cancelShipment(array($courier_data['awb_code']));
		}
	}

	public function ajax_order_products() {
		if ($order_info = $this->order_model->get($this->input->post('order_id'))) {
			$json['products'] = [];

			$results = $this->order_model->getProducts($this->input->post('order_id'));

			foreach ($results as $item) {
				$book_info = $this->book_model->get($item['product_id']);

				$versions = [];

				for ($i = 1; $i <= $book_info['version']; $i++) {
					$versions[] = $i;
				}

				$json['products'][] = array_merge($item, [
					'versions'	=> $versions
				]);
			}

			$json['printer_id'] = $order_info['assign_printer_id'];
		} else {
			$json['error'] = _l('invalid_order_id');
		}

		output_json($json);
	}

	public function ajax_reprint_order($option_type = '1') {
		$json = [];

		if ($this->input->post('order_id') && $this->input->post('product')) {
			$this->load->model('order/ReprintOrder_model', 'reprint_order_model');

			$order_info = $this->order_model->get($this->input->post('order_id'));

			// Add order comment
			$this->order_comment_model->add([
				'manager_id' 	=> (int)$this->session->userdata('user_id'),
				'order_id' 		=> (int)$this->input->post('order_id'),
				'description' 	=> $this->input->post('comment'),
				'status' 		=> $order_info['status'] ?? 1,
			]);

			$filter_data = [];
			$filter_data['option_type'] = [$option_type];

			// New assignment to other printer
			if (
				$this->input->post('use_different_printer') &&
				$this->input->post('printer_id') != $order_info['assign_printer_id']
			) {
				$_POST['ids'] = [
					$this->input->post('order_id'),
				];

				$products 			= $this->order_model->getProducts($this->input->post('order_id'), $filter_data);
				$new_products 		= [];
				$reprint_products 	= $this->input->post('product');

				foreach ($products as $item) {
					if (!empty($reprint_products[$item['product_id']])) {
						$new_products[] = array_merge($item, [
							'quantity'	=> (int)$reprint_products[$item['product_id']]
						]);
					}
				}

				self::ajax_assign_order_to_printer($option_type, $new_products);
				return;
			}

			if (empty($order_info['assign_printer_id'])) {
				output_json([
					'error' => _l('printer_not_assigned')
				]);
				return;
			}

			// Assign to same printer in the reprint secrtion
			$this->order_model->edit($order_info['id'], [
				'status'			=> 10,
				'printing_status'	=> 0,
			]);

			$products = $this->order_model->getProducts($this->input->post('order_id'), $filter_data);
			$reprint_products = $this->input->post('product');

			foreach ($products as $item) {
				if (!empty($reprint_products[$item['product_id']])) {
					if ($this->reprint_order_model->get_all([
						'version'		=> (int)$item['version'],
						'order_id'		=> (int)$item['order_id'],
						'product_id'	=> (int)$item['product_id'],
						'quantity'		=> (int)$reprint_products[$item['product_id']],
						'option'		=> $item['option'],
						'status'		=> 1,
						'printer_id'	=> (int)$order_info['assign_printer_id'],
					])['total'] == 0) {
						$this->reprint_order_model->add([
							'version'		=> (int)$item['version'],
							'order_id'		=> (int)$item['order_id'],
							'product_id'	=> (int)$item['product_id'],
							'quantity'		=> (int)$reprint_products[$item['product_id']],
							'option'		=> $item['option'],
							'status'		=> 1,
							'printer_id'	=> (int)$order_info['assign_printer_id'],
							'manager_id'	=> (int)$this->session->userdata('user_id'),
							'comment'		=> $this->input->post('comment'),
						]);
					} else {
						output_json([
							'error' => _l('request_reprint_for_other_quantity')
						]);
						return;
					}
				}
			}

			if ($this->input->post('order_history')) {
				$this->load->model('order/OrderHistory_model', 'order_history_model');

				$this->order_history_model->add([
					'order_id' 		=> (int)$this->input->post('order_id'),
					'description' 	=> $this->input->post('comment'),
					'status' 		=> $order_info['status'] ?? 1,
				]);
			}

			$json['success'] 	= _l('reprint_request_added');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		output_json($json);
	}

	public function change_order_version() {
		$json = [];

		if ($this->input->post('order_id') && $this->input->post('product')) {
			$this->load->model('order/OrderProduct_model', 'order_product_model');
			$order_info = $this->order_model->get($this->input->post('order_id'));

			if (!empty($order_info['printing_status']) || $order_info['printing_status'] == 8) {
				output_json([
					'error' => _L('already_printed')
				]);
				return;
			}

			$products = $this->order_model->getProducts($this->input->post('order_id'));
			$change_products = $this->input->post('product');

			foreach ($products as $item) {
				if (!empty($change_products[$item['product_id']])) {
					$printing_status = $this->printer_assign_log_model->get_all([
						'order_id'		=> $this->input->post('order_id'),
						'product_id'	=> $item['product_id'],
					])['rows'][0] ?? [];

					if ($printing_status && $printing_status['status'] > 1) {
						$json['error'] = _l('sent_to_the_printer');
						break;
					}
				}
			}

			$this->load->library('Stock_lib', 'stock_lib');
			$this->stock_lib->refund($this->input->post('order_id'));

			foreach ($products as $item) {
				if (!empty($change_products[$item['product_id']])) {
					$printing_status = $this->printer_assign_log_model->get_all([
						'order_id'		=> $this->input->post('order_id'),
						'product_id'	=> $item['product_id'],
					])['rows'][0] ?? [];

					if ($printing_status && $printing_status['status'] > 1) {
						$json['error'] = _l('sent_to_the_printer');
						break;
					}

					$this->order_product_model->edit($item['order_id'], $item['product_id'], [
						'version'	=> (int)$change_products[$item['product_id']],
					]);

					if ($stock_history_info = $this->book_stock_history_model->get_all([
						'order_id'			=> (int)$item['order_id'],
						'book_id'			=> (int)$item['product_id'],
						'version'			=> (int)$item['version']
					])['rows'][0] ?? []) {
						log_kb(['Stock History Deleted Due to Change Version:: ' => $item]);

						$this->book_stock_history_model->delete($stock_history_info['id']);
					}

					if ($printing_status) {
						$this->printer_assign_log_model->editById($printing_status['id'], [
							'version'	=> (int)$change_products[$item['product_id']],
						]);
					}
				}
			}

			if (empty($json)) {
				// $this->order_model->edit($this->input->post('order_id'), ['status' => 1]);
				$user_info = $this->user_model->get($order_info['assign_printer_id']);

				if ($order_info['pickup_location_id'] == $this->config->item('default_pickup_location_id')) {
					$this->stock_lib->orderFulfill($this->input->post('order_id'), $this->config->item('default_pickup_location_id') );
				}

				if ($this->input->post('order_history')) {
					$this->load->model('order/OrderHistory_model', 'order_history_model');

					$this->order_history_model->add([
						'order_id' 		=> (int)$this->input->post('order_id'),
						'description' 	=> $this->input->post('comment'),
						'status' 		=> $order_info['status'] ?? 1,
					]);
				}

				$json['success'] 	= _l('change_version_request_added');
			}
		} else {
			$json['error'] 		= _l('error_unknown');
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
}
