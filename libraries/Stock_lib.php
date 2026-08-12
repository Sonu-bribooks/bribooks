<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Stock_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('order/Order_model');
		$this->load->model('user/Student_model');
		$this->load->model('book/BookStock_model');
		$this->load->model('book/BookStockHistory_model');
		$this->load->model('printer/PrinterExtraDetails_model');
		$this->load->model('order/OrderComment_model');

		$this->book_model = $this->CI->Book_model;
		$this->order_model = $this->CI->Order_model;
		$this->student_model = $this->CI->Student_model;
		$this->book_stock_model = $this->CI->BookStock_model;
		$this->book_stock_history_model = $this->CI->BookStockHistory_model;
		$this->printer_extra_details_model = $this->CI->PrinterExtraDetails_model;
		$this->order_comment_model = $this->CI->OrderComment_model;
	}

	public function orderFulfill($order_id = 0, $change_version_request = '') {
		$order_info = $this->order_model->get($order_id);

		if ($order_info['pickup_location_id'] != $this->config->item('default_pickup_location_id')) return;

		if (empty($change_version_request) && $order_info['status'] > 1) return;

		log_kb([
			'Order Fulfill:: ' => $order_info
		]);

		$pickup_location_id = $this->config->item('default_pickup_location_id');

		if (!empty($printer_id = $order_info['assign_printer_id'])) {
			$printer_extra_detail_info = $this->printer_extra_details_model->getByPrinterId($printer_id);
			$pickup_location_id = $printer_extra_detail_info['pickup_location_id'] ?? 1;
		}

		// if all order quantity of all books are meet then move to afs
		$products = $this->order_model->getProducts($order_id);

		foreach ($products as $product) {
			$option = json_decode($product['option'], 1);

			if (mb_strtolower($option['name']) === 'ebook') continue;
			if (mb_strtolower($option['name']) === 'audio book') continue;

			$stock_info = $this->book_stock_model->get_all([
				'book_id'	=> (int)$product['product_id'],
				'version'	=> (int)$product['version'],
				'option'	=> $option['name'],
				'pickup_location_id' => $pickup_location_id
			])['rows'][0] ?? [];

			// If stock not found then create empty stock
			if (empty($stock_info)) {
				log_kb(['Stock Not found:: ' => [
					'book_id'	=> (int)$product['product_id'],
					'version'	=> (int)$product['version'],
					'option'	=> $option['name'],
				]]);

				$stock_id = $this->book_stock_model->add([
					'book_id'	=> (int)$product['product_id'],
					'version'	=> (int)$product['version'],
					'option'	=> $option['name'],
					'quantity'	=> 0,
					'pickup_location_id' => $pickup_location_id
				]);

				$stock_info = $this->book_stock_model->get($stock_id);
			}

			$stock_quantity = $stock_info['quantity'] ?? 0;

			$update_data = [
				'order_id'		=> (int)$order_id,
				'book_id'		=> (int)$product['product_id'],
				'version'		=> (int)$product['version'],
				'option'		=> $option['name'],
				'quantity'		=> (int)$stock_quantity,
				'quantity_order'=> (int)$product['quantity'],
				'quantity_hold'	=> $stock_quantity > 0
					? (int)($stock_quantity >= $product['quantity'] ? $product['quantity'] : $stock_quantity)
					: 0,
				'hold_date'		=> date('Y-m-d H:i:s'),
				'status'		=> $stock_quantity >= $product['quantity'] ? 1 : 0,
				'pickup_location_id' => $pickup_location_id
			];

			if ($stock_quantity >= $product['quantity']) {
				$update_data['release_date'] = date('Y-m-d H:i:s');
				$update_data['quantity_fulfill'] = $product['quantity'];
			}

			log_kb(['Stock History Add:: ' => $update_data]);

			if (!empty($stock_history_info = $this->book_stock_history_model->get_all([
				'order_id'			=> (int)$order_id,
				'book_id'			=> (int)$product['product_id'],
				'version'			=> (int)$product['version'],
				'option'			=> $option['name'],
				'pickup_location_id'=> $pickup_location_id
			])['rows'][0] ?? [])) {
				log_kb(['Stock History Add: Duplicate Entry:: ' => [
					$update_data,
					$stock_history_info
				]]);
				continue;
			}

			$this->book_stock_history_model->add($update_data);

			$this->book_stock_model->edit($stock_info['id'], [
				'quantity'	=> (int)($stock_quantity - $product['quantity'])
			]);
		}

		if ($this->book_stock_history_model->get_all([
			'order_id'			=> (int)$order_id,
			'ne_status'			=> 1,
			'pickup_location_id'=> $pickup_location_id
		])['total'] === 0) {
			log_kb(['Order orderFulfill Moving To Afs:: ' => $order_info]);

			$this->order_model->edit($order_id, [
				'status'	=> 21,
			]);
		}
	}

	public function stockFulfill($stock_quantity = 0, $book_id = 0, $version = 1, $input_option = 'paperback', $assignment_id = 0, $pickup_location_id = 1) {
		$stock_info = $this->book_stock_model->get_all([
			'book_id'				=> (int)$book_id,
			'version'				=> (int)$version,
			'option'				=> $input_option,
			'pickup_location_id' 	=> (int)$pickup_location_id
		])['rows'][0] ?? [];

		$filter_data = [
			'in_status'	=> [1, 2, 8],
			'book_id'	=> (int)$book_id,
			'version'	=> (int)$version,
			'option'	=> $input_option,
			'sort'		=> 'order.date_added',
			'order'		=> 'ASC',
		];

		if (!empty($pickup_location_id) && ($pickup_location_id != 1)) {
			$filter_data['pickup_location_id'] = (int)$pickup_location_id;
		} else {
			$filter_data['pickup_location_id'] = [0, 1];
		}

		// get all oldest orders for this book id and move to afs
		$orders = $this->order_model->searchProductName($filter_data)['rows'] ?? [];

		log_kb(['stockFulfill::orders: ' => [
			'orders' 				=> $orders,
			'stock_quantity' 		=> $stock_quantity,
			'book_id' 				=> $book_id,
			'version' 				=> $version,
			'input_option' 			=> $input_option,
			'assignment_id' 		=> $assignment_id,
			'pickup_location_id' 	=> $pickup_location_id,
		]]);

		$exclude = [];

		foreach ($orders as $order) {
			if (!in_array($order['id'], $exclude)) {
				$exclude[] = $order['id'];

				$products = $this->order_model->getProducts($order['id']);

				foreach ($products as $product) {
					$option = json_decode($product['option'], 1);

					if (
						$product['product_id'] == $book_id &&
						$product['version'] == $version &&
						mb_strtolower($option['name']) == mb_strtolower($input_option)
					) {
						if (($stock_history_info = $this->book_stock_history_model->get_all([
							'order_id'			=> (int)$order['id'],
							'book_id'			=> (int)$product['product_id'],
							'version'			=> (int)$product['version'],
							'option'			=> $option['name'],
							'ne_status'			=> 1,
							'pickup_location_id'=> $pickup_location_id
						])['rows'][0] ?? []) && $stock_quantity > 0) {
							$required_quantity = $stock_history_info['quantity_hold'] > 0
								? ($stock_history_info['quantity_order'] - $stock_history_info['quantity_hold'])
								: $stock_history_info['quantity_order'];

							if ($stock_quantity >= $required_quantity) {
								log_kb(['Stock Fulfill Fully:: ' => $stock_history_info]);

								$this->book_stock_history_model->edit($stock_history_info['id'], [
									'status'		=> 1,
									'release_date'	=> date('Y-m-d H:i:s'),
									'assignment_id'	=> (int)$assignment_id,
								]);
							} else {
								log_kb(['Stock Fulfill Partially:: ' => $stock_history_info]);

								$this->book_stock_history_model->edit($stock_history_info['id'], [
									'status'			=> 2,
									'quantity_fulfill'	=> (int)($stock_history_info['quantity_fulfill'] + $stock_quantity),
									'quantity_hold'		=> (int)($stock_history_info['quantity_hold'] + $stock_quantity),
									'assignment_id'		=> (int)$assignment_id,
								]);
							}

							$stock_quantity -= $required_quantity;
						}
					}
				}

				// Move to afs if a single book quantity is fulfilled partial or fully
				if ($this->book_stock_history_model->get_all([
					'order_id'			=> (int)$order['id'],
					'ne_status'			=> 1,
					'pickup_location_id'=> (int)$pickup_location_id
				])['total'] === 0) {
					log_kb(['Order stockFulfill Moving To Afs:: ' => $order]);

					$this->order_model->edit($order['id'], [
						'status'	=> 21,
					]);

					CI_Events::trigger('order_moved_to_afs', [
						'order_id'	=> $order['id']
					]);
				}
			}
		}
	}

	public function refund($order_id = 0) {
		$order_info = $this->order_model->get($order_id);

		$pickup_location_id = 1;

		if (!empty($printer_id = $order_info['assign_printer_id'])) {
			$printer_extra_detail_info = $this->printer_extra_details_model->getByPrinterId($printer_id);
			$pickup_location_id = $printer_extra_detail_info['pickup_location_id'] ?? 1;
		}

		$products = $this->order_model->getProducts($order_id);

		log_kb(['Order refund :: ' => $products]);

		foreach ($products as $product) {
			$option = json_decode($product['option'], 1);

			$stock_info = $this->book_stock_model->get_all([
				'book_id'				=> (int)$product['product_id'],
				'version'				=> (int)$product['version'],
				'option'				=> $option['name'],
				'pickup_location_id' 	=> (int)$pickup_location_id
			])['rows'][0] ?? [];

			if ($stock_history_info = $this->book_stock_history_model->get_all([
				'order_id'			=> (int)$order_id,
				'book_id'			=> (int)$product['product_id'],
				'version'			=> (int)$product['version'],
				'option'			=> $option['name'],
				'pickup_location_id'=> (int)$pickup_location_id
			])['rows'][0] ?? []) {
				$this->book_stock_model->edit($stock_info['id'], [
					'quantity'	=> (int)($stock_info['quantity'] + $product['quantity'])
				]);

				log_kb(['Order refund :: ' => $stock_history_info]);

				// Stock history cancelled
				$this->book_stock_history_model->edit($stock_history_info['id'], [
					'status'	=> 3
				]);
			}
		}
	}

	public function subtract_stock($order_id = 0) {
		$order_info = $this->order_model->get($order_id);

		if ($order_info['pickup_location_id'] != $this->config->item('default_pickup_location_id')) return;

		$pickup_location_id = $this->config->item('default_pickup_location_id');

		if (!empty($printer_id = $order_info['assign_printer_id'])) {
			$printer_extra_detail_info = $this->printer_extra_details_model->getByPrinterId($printer_id);
			$pickup_location_id = $printer_extra_detail_info['pickup_location_id'] ?? 1;
		}

		$products = $this->order_model->getProducts($order_id);

		foreach ($products as $product) {
			$option = json_decode($product['option'], 1);

			if(!empty($stock_info = $this->book_stock_model->get_all([
				'book_id'				=> (int)$product['product_id'],
				'version'				=> (int)$product['version'],
				'option'				=> $option['name'],
				'pickup_location_id' 	=> (int)$pickup_location_id
			])['rows'][0] ?? [])) {
				// Add order comment
				$this->order_comment_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'order_id' 		=> (int)$order_id,
					'description' 	=> 'Escalated Order Assigned',
					'status' 		=> $order_info['status'],
				]);

				$this->book_stock_model->edit($stock_info['id'], [
					'quantity'	=> (int)($stock_info['quantity'] - $product['quantity'])
				]);
			}
		}
	}
}
