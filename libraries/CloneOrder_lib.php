<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class CloneOrder_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('order/Order_model');
		$this->load->model('address/Address_model');
		$this->load->model('address/AddressLog_model');
		$this->load->model('order/OrderProduct_model');
		$this->load->model('book/Book_model');
		$this->load->model('book/BookStock_model');
		$this->load->model('book/BookStockHistory_model');
		$this->load->model('order/Payment_model');
		$this->load->model('order/OrderClone_model');
		$this->load->model('order/OrderHistory_model');
		$this->load->model('printer/PrinterExtraDetails_model');

		$this->load->library('Discount_lib');
		$this->load->library('Stock_lib');

		$this->order_model = $this->CI->Order_model;
		$this->address_model = $this->CI->Address_model;
		$this->address_log_model = $this->CI->AddressLog_model;
		$this->order_product_model = $this->CI->OrderProduct_model;
		$this->book_model = $this->CI->Book_model;
		$this->book_stock_model = $this->CI->BookStock_model;
		$this->book_stock_history_model = $this->CI->BookStockHistory_model;
		$this->payment_model = $this->CI->Payment_model;
		$this->order_clone_model = $this->CI->OrderClone_model;
		$this->order_history_model = $this->CI->OrderHistory_model;
		$this->printer_extra_details_model = $this->CI->PrinterExtraDetails_model;

		$this->discount_lib = $this->CI->discount_lib;
		$this->stock_lib = $this->CI->stock_lib;
	}

	public function cloneOrderCreated($data = []) {
		$order_id 		= $data['order_id'];
		$order_products = $data['data']['products'];
		$data 			= $data['data'];

		$order_info = $this->order_model->get($order_id);

		if (empty($order_info)) return;

		$address_info = $this->address_model->getByID($order_info['address_id']);

		if (empty($address_info)) return;

		$pickup_location_id = 1;

		if (!empty($printer_id = $order_info['assign_printer_id'])) {
			$printer_extra_detail_info 	= $this->printer_extra_details_model->getByPrinterId($printer_id);
			$pickup_location_id 		= $printer_extra_detail_info['pickup_location_id'] ?? 1;
		}

		$old_address_info = [
			'name'		=> $address_info['name'],
			'mobile'	=> $address_info['mobile'],
			'zipcode'	=> $address_info['zipcode'],
			'address'	=> $address_info['address'],
			'landmark'	=> $address_info['landmark'],
			'city'		=> $address_info['city'],
			'state'		=> $address_info['state']
		];

		$new_address_info = [
			'name'		=> $data['customer_name'],
			'mobile'	=> $data['customer_mobile'],
			'zipcode'	=> $data['customer_zipcode'],
			'address'	=> $data['customer_address'],
			'landmark'	=> $data['customer_landmark'],
			'city'		=> $data['customer_city'] ?? '',
			'state'		=> $data['customer_state'] ?? ''
		];

		$edit_address 			= array_diff_assoc($new_address_info, $old_address_info);

		$order_product_results 	= $this->order_product_model->getOrderProductByOrderId($order_id);

		if (empty($order_product_results)) return;

		$parent_quantity = array_sum(array_column($order_product_results, 'quantity'));

		$reprint_books = $order_product_data = [];

		foreach ($order_products as $key => $item) {
			if (!empty($item['checkbox']) && $item['checkbox'] == '1') {
				$book_info = $this->book_model->get($item['book_id']);

				if (empty($book_info)) continue;

				$book_price = $this->book_model->getPrice($item['book_id']);

				$order_product_info = $this->order_product_model->get_all([
					'order_id'		=> $order_id,
					'book_id'		=> $item['book_id']
				]);

				if (
					!empty($order_product_info = $order_product_info['rows'][0]) &&
					(
						mb_strtolower($order_info['currency_code']) !== 'inr' ||
						strtolower($address_info['country']) != 'india'
					)
				) {
					$subtotal = (!empty($order_product_info['subtotal']))
						? round($order_product_info['subtotal'] / $order_product_info['quantity'], 2)
						: 0
					;

					$book_price['price'] 		= $order_product_info['price'];
					$book_price['total'] 		= (!empty($order_product_info['total']))
						? round($order_product_info['total'] / $order_product_info['quantity'], 2)
						: 0
					;
					$book_price['ppp_total'] 	= (!empty($order_product_info['ppp_total']))
						? round($order_product_info['ppp_total'] / $order_product_info['quantity'], 2)
						: 0
					;
				} else {
					$book_price = $this->discount_lib->applyAuthorDiscount(
						$item['book_id'],
						$book_price,
						$item['quantity'],
					);

					$subtotal = (
						$book_price['total'] +
						($this->config->item('site_paperback_price') ?? 0)
					) * $item['quantity'];
				}

				$weight = (
					$book_price['total_pages'] * BOOK_WEIGHT['page'] * 2 +
					BOOK_WEIGHT['cover']['paperback']
				) * $item['quantity'];

				$order_product_data[] = [
					'version'			=> (int)$book_info['version'],
					'product_id'		=> (int)$item['book_id'],
					'quantity'			=> (int)$item['quantity'],
					'price'				=> (double)$book_price['price'],
					'credit'			=> 0,
					'used_credit'		=> 0,
					'credit_discount'	=> '0.00',
					'ppp_total'			=> (double)($book_price['ppp_total'] * $item['quantity']),
					'subtotal'			=> (double)$subtotal,
					'total'				=> (double)($book_price['total'] * $item['quantity']),
					'weight'			=> (double)$weight,
					'option'			=> $order_product_info['option'] ?? '{"name":"Paperback","price":0}',
					'option_type'		=> $order_product_info['option_type'] ?? '1'
				];

				if ($item['need_stock']) {
					$reprint_books[] = [
						'product_id'			=> (int)$item['book_id'],
						'version'				=> (int)$book_info['version'],
						'quantity' 				=> (int)$item['quantity'],
						'reprint' 				=> (int)$item['need_stock'],
					];
				}
			}
		}

		$ppp_total 		= array_sum(array_column($order_product_data, 'ppp_total'));
		$subtotal 		= array_sum(array_column($order_product_data, 'subtotal'));
		$total 			= array_sum(array_column($order_product_data, 'total'));
		$weight 		= array_sum(array_column($order_product_data, 'weight'));
		$clone_quantity = array_sum(array_column($order_product_data, 'quantity'));

		// get status of order before the cloning
		$first_clone_order = $this->db
			->order_by('id', 'ASC')
			->get_where('order_clone', [
				'parent_order_id'	=> (int)$order_id,
			])
			->row_array()
		;

		$parent_order_status = $new_order_status = !empty($first_clone_order)
			? $first_clone_order['order_status']
			: $order_info['status']
		;

		if ($new_order_status == 15) {
			$new_order_status = !empty($reprint_books) ? 1 : 21;
		}

		log_kb([
			'Clone' => compact(['new_order_status', 'parent_order_status'])
		]);

		$order_data = [
			'parent_order_id'		=> $order_id,
			'order_code'			=> $data['new_order_code'],
			'site_id'				=> $order_info['site_id'],
			'user_id'				=> $order_info['user_id'],
			'address_id'			=> $order_info['address_id'],
			'currency_id'			=> $order_info['currency_id'],
			'currency_code'			=> $order_info['currency_code'],
			'currency_symbol'		=> $order_info['currency_symbol'],
			'coupon_id'				=> 0,
			'ppp_total'				=> (double)$ppp_total,
			'credit_discount'		=> '0.00',
			'tax'					=> '0.00',
			'shipping_cost'			=> '0.00',
			'subtotal'				=> '0.00',
			'total'					=> '0.00',
			'weight'				=> round($weight, 2) + BOOK_WEIGHT['packing'],
			'shipping_info'			=> $order_info['shipping_info'],
			'ip'					=> $order_info['ip'],
			'provider'				=> $order_info['provider'],
			'status'				=> (int)$new_order_status,
			'order_type'			=> 1,
			'ext_order_id'			=> $order_info['ext_order_id'],
			'ext_transaction_id'	=> $order_info['ext_transaction_id'],
			'ext_raw_data'			=> $order_info['ext_raw_data'],
		];

		$clone_order_id = $this->order_model->add($order_data);

		if (!empty($shipping_cost = $order_info['shipping_cost']) || ($shipping_cost != '0.00')) {
			$shipping_cost = (double)(round(($shipping_cost / $parent_quantity), 2) * $clone_quantity);
		}

		$order_history_info = $this->order_history_model->get_all([
			'order_id'	  	=> $order_id,
			'description'   => _l('clone_order_created'),
			'order'		 	=> 'ASC'
		])['rows'][0] ?? [];

		$order_history_results = $this->order_history_model->get_all([
			'order_id'	  	=> $order_id,
			'in_status'		=> [4, 15]
		])['rows'] ?? [];

		$order_clone_data = [
			'parent_order_id'	=> (int)$order_id,
			'clone_order_id'	=> (int)$clone_order_id,
			'products'			=> json_encode($order_products),
			'currency_code'		=> $order_info['currency_code'],
			'currency_symbol'	=> $order_info['currency_symbol'],
			'shipping_cost'		=> $shipping_cost,
			'subtotal'			=> (double)($subtotal + $shipping_cost),
			'total'				=> (double)($total + $shipping_cost),
			'weight'			=> round($weight, 2) + BOOK_WEIGHT['packing'],
			'shipment_type'		=> ((empty($order_history_info) && empty($order_history_results)) || in_array((int)$order_history_info['status'], [1, 2, 8, 9, 21])) ? 1 : 2,
			'order_status'		=> $order_info['status'],
			'manager_id'		=> (int)$this->session->userdata('user_id'),
			'date_added'		=> date('Y-m-d H:i:s'),
			'date_modified'		=> date('Y-m-d H:i:s'),
		];

		$order_comment_data = [
			'manager_id'		=> (int)$this->session->userdata('user_id'),
			'order_id'			=> (int)$order_id,
			'description'		=> 'Clone Order Created',
			'status'			=> $order_info['status'],
			'date_added'		=> date('Y-m-d H:i:s'),
			'date_modified'		=> date('Y-m-d H:i:s'),
		];

		$order_history_data = [
			'order_id'			=> (int)$order_id,
			'description'		=> 'Clone Order Created',
			'status'			=> $order_info['status'],
			'date_added'		=> date('Y-m-d H:i:s'),
			'date_modified'		=> date('Y-m-d H:i:s'),
		];

		$payment_info = $this->payment_model->get_all(['order_id' => $order_id]);
		$payment_info = !empty($payment_info['rows'][0]) ? $payment_info['rows'][0] : [];

		$payment_data = [
			'site_id'			=> $payment_info['site_id'],
			'user_id'			=> $payment_info['user_id'],
			'order_id'			=> (int)$clone_order_id,
			'currency_id'		=> $payment_info['currency_id'],
			'currency_code'		=> $payment_info['currency_code'],
			'currency_symbol'	=> $payment_info['currency_symbol'],
			'provider'			=> $payment_info['provider'],
			'amount'			=> '0.00',
			'status'			=> 1,
			'date_added'		=> date('Y-m-d H:i:s'),
			'date_modified'		=> date('Y-m-d H:i:s'),
		];

		if ($clone_order_id) {
			if (!empty($edit_address)) {
				$this->address_model->edit($order_info['address_id'], $edit_address);

				$this->address_log_model->add([
					'address_id'	=> $order_info['address_id'],
					'old_address'	=> json_encode($address_info),
					'manager_id'	=> (int)$this->session->userdata('user_id')
				]);
			}

			if (
				in_array((int)$order_info['status'], [1, 2, 3, 9, 15, 21, 94]) &&
				empty($this->order_clone_model->getByIds(['parent_order_id' => $order_id])) &&
				$new_order_status != 21 && $parent_order_status != 15
			) {
				// bug
				log_kb(['Clone::Refund Stock' => [$order_info, $parent_order_status, $new_order_status]]);
				$this->stock_lib->refund($order_id);
			}

			$this->db->insert('order_clone', $order_clone_data);

			if (!empty($order_product_data)) {
				foreach ($order_product_data as &$order_product) {
					$order_product['order_id'] = $clone_order_id;

					$this->db->insert('order_product', $order_product);
				}
			}

			$this->db->insert('order_comment', $order_comment_data);
			$this->db->insert('order_history', $order_history_data);
			$this->db->insert('payment', $payment_data);

			$this->order_model->editById($order_id, [
				'status'			=> 94
			]);

			// bug
			log_kb([
				'Clone::orderFulfill' => compact(['new_order_status', 'parent_order_status'])
			]);

			if ($new_order_status != 21) {
				$this->stock_lib->orderFulfill($clone_order_id, true);

				if (
					!empty($reprint_books) &&
					$parent_order_status == 15
				) {
					foreach ($order_product_data as $item) {
						$reprint_quantity = array_filter($reprint_books, function ($reprint_book) use($item) {
							return $reprint_book['product_id'] == $item['product_id'] && $reprint_book['version'] == $item['version'];
						})[0]['reprint'] ?? 0;

						if (!empty($stock_history_info = $this->book_stock_history_model->get_all([
							'order_id'			=> (int)$clone_order_id,
							'book_id'			=> (int)$item['product_id'],
							'version'			=> (int)$item['version'],
							'option'			=> 'paperback',
							'pickup_location_id'=> $pickup_location_id
						])['rows'][0] ?? [])) {
							log_kb(['Clone::Stock History Hold:: ' => [
								$stock_history_info
							]]);

							$update_data = [
								'quantity_hold'	=> (int)($item['quantity'] - $reprint_quantity),
								'hold_date'		=> date('Y-m-d H:i:s'),
							];

							if (empty($reprint_quantity)) {
								$update_data['status'] 			= 1;
								$update_data['release_date'] 	= date('Y-m-d H:i:s');
							}

							$this->book_stock_history_model->edit($stock_history_info['id'], $update_data);
						}

						if (!empty($stock_info = $this->book_stock_model->get_all([
							'book_id'				=> $item['product_id'],
							'version'				=> $item['version'],
							'option'				=> 'paperback',
							'pickup_location_id' 	=> (int)$pickup_location_id
						])['rows'][0] ?? [])) {
							log_kb(['Clone::Stock Update:: ' => [
								$stock_info
							]]);
							$this->book_stock_model->edit($stock_info['id'], [
								'quantity'			=> (int)($stock_info['quantity'] + $item['quantity'] - $reprint_quantity),
							]);
						}
					}
				}
			}
		}
	}
}
