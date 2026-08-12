<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('invoice');

trait OrderExtra {
	private function _downloadZipInvoice($dir = NULL) {
		$this->load->library('zip');

		$this->zip->read_dir(
			$dir,
			FALSE,
			FCPATH . 'uploads/pdfs'
		);

		$this->zip->download($path . '.zip');
	}

	public function export_leader_board($site_id = 0) {
		$this->load->library('Royalty_lib', 'royalty_lib');
		$this->load->model('user/Bank_model', 'bank_model');

		$filter_data = [];

		if ($site_id) {
			$filter_data['site_id'] = (int)$site_id;
		}

		$result = $this->order_model->getTopSoldBooks($filter_data);

		$filename = 'leaderboard_' . (int)$site_id . '.csv';

		foreach ($result ?? [] as $rank => $item) {
			$bank_info = $this->bank_model->searchProductName([
				'user_id' => $item['user_id']
			])['rows'][0] ?? [];

			$user_info = $this->student_model->get($item['user_id']);

			$rankings[] = [
				'rank'			=> $rank + 1,
				'book_name'		=> ucfirst($item['name']),
				'author_name'	=> $item['author_name'],
				'mobile'		=> $user_info['mobile'],
				'royalty'		=> currency($this->royalty_lib->getBookTotalRoyality($item['id']), 0),
				'sold'			=> readable_format(!empty($item['quantity']) ? $item['quantity'] : 0),
				'account_holder' => $bank_info['name'] ?? '',
				'bank_name'		=> $bank_info['bank_name'] ?? '',
				'branch_name'	=> $bank_info['branch_name'] ?? '',
				'account_number' => $bank_info['account_number'] ?? '',
				'ifsc_code'		=> $bank_info['ifsc_code'] ?? '',
			];
		}

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($rankings[0]) ? array_keys($rankings[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($rankings, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function download_invoice($site_id = 0) {
		$results = $this->order_model->searchProductName([
			'ne_status'	=> 0
		])['rows'] ?? [];

		$dir = FCPATH . 'uploads/pdfs/invoice/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		foreach ($results as $key => $item) {
			$output = self::_orderInvoice($item['id'], true);

			file_put_contents(
				$dir . 'invoice_' . $item['id'] . '.pdf',
				$output
			);

			if (ENVIRONMENT !== 'production' && $key > 2) break;
		}

		self::_downloadZipInvoice($dir);
	}

	public function export_orders($currency_id = 47, $option_type = 1) {
		$json = [];

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
			$filter_data['assign_printer_id'] = (int)$this->input->get('assign_printer_id');
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

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = $this->input->get('site_id');
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

		$filter_data['option_type'] = [$option_type];

		$results = $this->order_model->searchProductName($filter_data)['rows'] ?? [];

		$orders = [];

		$sn = 1;

		foreach ($results as $order) {
			$filter_data = [];
			$filter_data['order_id'] = (int)$order['id'];
			$filter_data['printer_id'] = (int)$order['assign_printer_id'];
			$order_assign_log_result = $this->printer_assign_log_model->get_all($filter_data);

			$filter_data = [];
			$filter_data['order_id'] = (int)$order['id'];
			$order_comment_result = $this->order_comment_model->get_all($filter_data);

			$comments = '';

			if (!empty($order_comment_result['rows'])) {
				foreach ($order_comment_result['rows'] as $order_comment) {
					$comments .= $order_comment['description'] . "\n";
				}

				$comments = substr($comments, 0, -2);
			}

			$products = $this->order_model->getProducts($order['id'], $filter_data);

			$address_info = $this->address_model->getByID($order['address_id']);
			$user_info = $this->student_model->get($order['user_id']);

			$address = !empty($address_info) ? vsprintf('%s, %s, %s, %s, %s, %s, %s, - %s - %s', [
				$address_info['name'],
				$address_info['mobile'],
				$address_info['address'],
				$address_info['landmark'],
				$address_info['city'],
				$address_info['state'],
				$address_info['country'],
				$address_info['zipcode'],
				$address_info['type'],
			]) : '';

			$total = round($order['total'], 2);

			$printer_info = $this->user_model->get($order['assign_printer_id']);

			$shipping_info = json_decode($order['shipping_info'], true);

			$shipping_tracking_info = json_decode($order['shipping_tracking_info'], true);

			foreach ($products as $key => $product) {
				$option = json_decode($product['option'], true);

				$total_pages 	= $this->page_version_model->get_all([
					'book_id'	=> $product['product_id'],
					'version'	=> $product['version'],
				])['total'] ?? 0;

				$orders[] = [
					'sn'			=> $sn,
					'region'		=> strtolower($order['currency_code']) === 'inr'
						? _l('domestic')
						: _l('global'),
					'order_id'		=> $order['id'],
					'order_code'	=> $order['order_code'],
					'book_name'		=> $product['name'],
					'version'		=> $product['version'],
					'sku'			=> _o_b_code($product['product_id'], $product['version'], $option['name']),
					'isbn/sn'		=> !empty($product['isbn']) ? $product['isbn'] : $product['unique_id'],
					'option'		=> $option['name'],
					'pages'			=> $total_pages * 2 + 1,
					'author_name'	=> $product['author_name'],
					'status'		=> _os($order['status']),
					'quantity'		=> $product['quantity'],
					'address'		=> $address,
					'state'			=> $address_info['state'] ?? '',
					'city'			=> $address_info['city'] ?? '',
					'c_mobile'		=> $user_info['mobile'] ?? '',
					'c_email'		=> $user_info['email'] ?? '',
					'currency_code'	=> $order['currency_code'],
					'total'			=> $key == 0 ? $total : 0,
					'weight'		=> $product['weight'] . 'gm',
					'printer'		=> $printer_info['first_name'] ?? '',
					'awb_code'		=> $shipping_tracking_info['awb_code'] ?? '',
					'shipping_info'	=> $shipping_tracking_info['courier_name'] ?? ($shipping_info['courier_name'] ?? ''),
					'date_added'	=> $order['date_added'],
					'date_assigned'	=> $order_assign_log_result['rows'][0]['date_added'] ?? '',
					'comments'		=> $comments
				];

				$sn++;
			}
		}

		self::_downloadCsv($orders, 'orders_');

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

		return vsprintf('<a class="text-primary" data-toggle="tooltip" title="%s"><i class="fa fa-info-circle"></i> %s %s</a><i class="text-danger fa fa-exclamation-triangle" data-toggle="tooltip" title="%s"></i><br>%s', [
			implode("\n", $histories),
			$agent_info['first_name'] ?? '',
			$agent_info['last_name'] ?? '',
			implode("\n", array_slice($comments, 1)),
			$comments[0] ?? '',
		]);
	}

	private function _renderCheckBox($result = [], $products = []) {
		$quantity = array_reduce($products, function($acc = 0, $item = NULL) {
			$acc += $item['quantity'];

			return $acc;
		});

		return vsprintf('<input type="checkbox" class="select-me" value="%s" data-qty="%s">', [
			$result['id'],
			$quantity,
		]);
	}

	public function reprint_bulk_order($option_type = '1') {
		$json = [];

		if ($this->input->post('order_ids')) {
			$this->load->model('order/ReprintOrder_model', 'reprint_order_model');
			$this->load->model('order/OrderHistory_model', 'order_history_model');

			$filter_data = [];
        	$filter_data['option_type'] = [$option_type];

			foreach ($this->input->post('order_ids') as $order_id) {
				$order_info = $this->order_model->get($order_id);

				// Add order comment
				$this->order_comment_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'order_id' 		=> (int)$order_id,
					'description' 	=> $this->input->post('comment'),
					'status' 		=> $order_info['status'] ?? 1,
				]);

				// New assignment to other printer
				if (
					($this->input->post('use_different_printer') === true) &&
					$this->input->post('printer_id') != $order_info['assign_printer_id']
				) {
					$_POST['ids']	= [$order_id];

					$products		= $this->order_model->getProducts($order_id, $filter_data);
					$new_products	= [];

					foreach ($products as $item) {
						$new_products[] = $item;
					}

					if (empty($new_products)) {
						continue;
					}

					self::ajax_assign_order_to_printer($option_type, $new_products);
					continue;
				}

				if (empty($order_info['assign_printer_id'])) {
					continue;
				}

				// Assign to same printer in the reprint secrtion
				$this->order_model->edit($order_info['id'], [
					'status'			=> 10,
					'printing_status'	=> 0,
				]);

				$products = $this->order_model->getProducts($order_id, $filter_data);

				foreach ($products as $item) {
					if ($this->reprint_order_model->get_all([
						'version'		=> (int)$item['version'],
						'order_id'		=> (int)$item['order_id'],
						'product_id'	=> (int)$item['product_id'],
						'quantity'		=> (int)$item['quantity'],
						'option'		=> $item['option'],
						'status'		=> 1,
						'printer_id'	=> (int)$order_info['assign_printer_id'],
					])['total'] == 0) {
						$this->reprint_order_model->add([
							'version'		=> (int)$item['version'],
							'order_id'		=> (int)$item['order_id'],
							'product_id'	=> (int)$item['product_id'],
							'quantity'		=> (int)$item['quantity'],
							'option'		=> $item['option'],
							'status'		=> 1,
							'printer_id'	=> (int)$order_info['assign_printer_id'],
							'manager_id'	=> (int)$this->session->userdata('user_id'),
							'comment'		=> $this->input->post('comment'),
						]);
					} else {
						continue;
					}
				}

				if ($this->input->post('order_history')) {
					$this->order_history_model->add([
						'order_id' 		=> (int)$order_id,
						'description' 	=> $this->input->post('comment'),
						'status' 		=> $order_info['status'] ?? 1,
					]);
				}
			}
		}

		$json['success'] 	= _l('reprint_request_added');
		output_json($json);
	}

	public function sync_bulk_order() {
		$json = [];
		$count = 0;
		if ($this->input->post('order_ids')) {
			foreach ($this->input->post('order_ids') as $order_id) {
				$order_info = $this->order_model->get($order_id);

				if (($order_info['shipping_status'] == 0) && !in_array($order_info['status'], [0,4,9,15,91,92,93])) {
					$this->alert_model->invoiceOrderCron($order_info['id'], false);
					$count++;
				}
			}
		}

		$json['success'] 	= _l($count.' orders_sync_successfully!');
		output_json($json);
	}

	public function ajax_order_move_to_new() {
		$json = [];

		if (($order_id = $this->input->post('order_id')) && ($assignment_code = $this->input->post('assignment_code'))) {
			$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');

			if ($printer_assignment_info = $this->printer_assignment_model->getByCode($assignment_code)) {
				self::printer_assign_rollback_order($order_id, $printer_assignment_info['id']);

				$json['success'] 	= _l('order_move_to_new');
			}
		}

		output_json($json);
	}

	public function ajax_order_move_to_new_bulk() {
		$json = [];

		if (($order_ids = $this->input->post('order_ids')) && ($assignment_code = $this->input->post('assignment_code'))) {
			$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');

			if ($printer_assignment_info = $this->printer_assignment_model->getByCode($assignment_code)) {
				foreach ($order_ids as $order_id) {
					self::printer_assign_rollback_order($order_id, $printer_assignment_info['id']);
				}

				$json['success'] 	= _l('order_move_to_new');
			}
		}

		output_json($json);
	}

	private function printer_assign_rollback_order($order_id = '', $assignment_id = '') {
		if(empty($order_id) || empty($assignment_id))
			return;

		$this->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');
		$this->load->model('printer/PrinterAssignRollback_model', 'printer_assign_rollback_model');

		$this->printer_assign_log_model->editByAssignmentAndOrderId($order_id, $assignment_id, [
			'_deleted' 		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s')
		]);

		$this->order_model->edit($order_id, [
			'status' 			=> 1,
			'printing_status' 	=> 0,
			'assign_printer_id' => 0,
			'pickup_location_id'=> 1
		]);

		$this->printer_assign_rollback_model->add([
			'order_id' 		=> $order_id,
			'assignment_id' => $assignment_id
		]);

		return;
	}

	public function ajax_cancel_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$this->load->model('user/AuthorEarning_model', 'author_earning_model');
			$this->load->model('order/OrderHistory_model', 'order_history_model');

			$this->load->library('Royalty_lib', 'royalty_lib');

			$order_info = $this->order_model->get($order_id);

			// Cancel Author Earning
			$this->author_earning_model->cancelByOrderId($order_id);

			// Refund User Credit
			$this->royalty_lib->refundUserCredit($order_id, $this->input->post('comment'));

			// Add order comment
			$this->order_comment_model->add([
				'manager_id' 	=> (int)$this->session->userdata('user_id'),
				'order_id' 		=> (int)$order_id,
				'description' 	=> $this->input->post('comment'),
				'status' 		=> $order_info['status'] ?? 91,
			]);

			$this->db->update('order', [
				'status'		=> 91,
				'date_modified'	=> date('Y-m-d H:i:s')
			], [
				'id'			=> (int)$order_id
			]);

			$this->order_history_model->add([
				'order_id' 		=> (int)$order_id,
				'description' 	=> 'Order Cancelled',
				'status' 		=> $order_info['status'] ?? 91,
			]);

			if(!empty($order_info) && in_array($order_info['status'], [1,2,8,9,15,21,93,94])) {
				$this->load->library('Stock_lib', 'stock_lib');
				$this->stock_lib->refund($order_id);
			}

			if (!empty($event_orders = $this->event_order_model->get_all(['order_id' => $order_info['id']])['rows'] ?? [])) {
				$this->load->library('Ranking_lib', 'ranking_lib');
				foreach ($event_orders as $event_order) {
					$this->event_order_model->delete($event_order['id']);

					if (!empty($book_order_info = $this->event_order_model->get_all([
						'book_id' 	=> $event_order['book_id'],
						'order' 	=> 'DESC'
					])['rows'][0] ?? '')) {
						$this->ranking_lib->updateRank($book_order_info['order_id']);
					}
				}
			}

			$json['success'] 	= _l('order_cancel_request_added');
		}

		output_json($json);
	}

	public function refund_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('orderid'))) {
			$this->load->model('order/OrderHistory_model', 'order_history_model');

			$order_info = $this->order_model->get($order_id);

			// Add order comment
			$this->order_comment_model->add([
				'manager_id' 	=> (int)$this->session->userdata('user_id'),
				'order_id' 		=> (int)$order_id,
				'description' 	=> 'Order Amount Refunded',
				'status' 		=> $order_info['status'] ?? 92,
			]);

			$this->db->update('order', [
				'status'		=> 92,
				'date_modified'	=> date('Y-m-d H:i:s')
			], [
				'id'			=> (int)$order_id
			]);

			$this->order_history_model->add([
				'order_id' 		=> (int)$order_id,
				'description' 	=> 'Order Amount Refunded',
				'status' 		=> $order_info['status'] ?? 92,
			]);

			$this->cron_model->add([
                'code'          => 'refundOrderCron_' . $order_id,
                'action'        => 'alert_model->refundOrderCron',
                'data'          => [$order_id],
                'site_id'       => $order_info['site_id'],
                'alert_date'    => date('Y-m-d H:i:00', strtotime('+1 minutes')),
            ]);

			$json['success'] 	= _l('order_refund_request_added');
		}

		output_json($json);
	}

	public function export_ebooks() {
		$json = [];

		$results = $this->book_model->get_all_domestic_books();

		$books = [];

		foreach ($results as $result) {
			$book_price = $this->book_model->getPrice($result['id']);

			if($book_price['total'] < 800)
				continue;

			$category_info = $this->category_model->get($result['category_id']);

			$total = $this->db->get_where('order_product',[
				'product_id' => $result['id'],
			])->num_rows() ;

			$books[] = [
				'book_id'			=> $result['id'],
				'user_id'			=> $result['user_id'],
				'book_name'			=> $result['name'],
				'theme'				=> $category_info['name'],
				'student_name'		=> trim(($result['first_name'] ?? '') . ' ' . ($result['last_name'] ?? '')),
				'author_name'		=> $result['author_name'],
				'mobile'			=> $result['mobile'],
				'email'				=> $result['email'],
				'date_added'		=> formatDate($result['date_added']),
				'date_published'	=> formatDate($result['date_published']),
				'date_approved'		=> ($result['date_approved']) ? formatDate($result['date_approved']) : '',
				'page_count' 		=> $book_price['total_pages'],
				'sold_book' 		=> $total,
				'book_price' 		=> $book_price['total'],
				'site_code' 		=> $result['site_code'],
				'book_url' 			=> USER_URL . 'bookstore/' . $result['slug'],
			];
		}

		self::_downloadCsv($books, 'ebooks_');

		output_json($json);
	}

	public function escalate_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$this->load->model('order/OrderHistory_model', 'order_history_model');
			$this->load->model('order/EscalatedOrders_model', 'escalated_orders_model');

			$order_ids = explode(',', $this->input->post('order_id'));

			foreach ($order_ids as $order_id) {
				$order_info = $this->order_model->get($order_id);

				// Add order comment
				$this->order_comment_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'order_id' 		=> (int)$order_id,
					'description' 	=> $this->input->post('comment'),
					'status' 		=> $order_info['status'] ?? 93,
				]);

				$this->db->update('order', [
					'status'		=> 93,
					'date_modified'	=> date('Y-m-d H:i:s')
				], [
					'id'			=> (int)$order_id
				]);

				$this->order_history_model->add([
					'order_id' 		=> (int)$order_id,
					'description' 	=> _l('order_escalated'),
					'status' 		=> $order_info['status'] ?? 93,
				]);

				$this->escalated_orders_model->add([
					'order_id' 		=> (int)$order_id,
					'description' 	=> $this->input->post('comment'),
					'order_status' 	=> $order_info['status'] ?? 93,
				]);

				$json['success'] 	= _l('order_escalated_request_added');
			}
		}

		output_json($json);
	}

	public function escalate_restore_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$this->load->model('order/OrderHistory_model', 'order_history_model');
			$this->load->model('order/EscalatedOrders_model', 'escalated_orders_model');

			$order_ids = explode(',', $this->input->post('order_id'));

			foreach ($order_ids as $order_id) {
				$order_info = $this->order_model->get($order_id);

				// Add order comment
				$this->order_comment_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'order_id' 		=> (int)$order_id,
					'description' 	=> $this->input->post('comment'),
					'status' 		=> $order_info['status'] ?? 93,
				]);

				$this->order_history_model->add([
					'order_id' 		=> (int)$order_id,
					'description' 	=> _l('order_escalated'),
					'status' 		=> $order_info['status'] ?? 93,
				]);

				$filter_data = [];
				$filter_data['order_id'] = (int)$order_id;
				$escalated_orders_results = $this->escalated_orders_model->get_all($filter_data);

				if (!empty($escalated_order_info = $escalated_orders_results['rows'][0])) {
					$this->db->update('order', [
						'status'		=> $escalated_order_info['order_status'],
						'date_modified'	=> date('Y-m-d H:i:s')
					], [
						'id'			=> (int)$order_id
					]);

					$this->escalated_orders_model->delete($escalated_order_info['id']);

					if (in_array($escalated_order_info['order_status'], [2, 8, 15])) {
						$this->load->library('Stock_lib', 'stock_lib');
						$this->stock_lib->orderFulfill($order_id);
					}
				}

				$json['success'] 	= _l('escalated_order_restore_request_added');
			}
		}

		output_json($json);
	}
}
