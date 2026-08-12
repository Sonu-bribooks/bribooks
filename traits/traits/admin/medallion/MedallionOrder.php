<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MedallionOrder {
	public function medallion_orders_crud($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			self::validateMedallionOrderForm();

			$this->medallion_order_model->add($this->input->post());
		} elseif ($param1 == 'edit') {
			self::validateMedallionOrderForm($param2);

			$this->medallion_order_model->edit($param2, $this->input->post());
		}

		redirect(base_url('admin/medallion_orders'), 'refresh');
	}

	public function medallion_orders_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'medallion/orders/form';
			$data['page_title'] 					= _l('medallion_order_add');
			$data['action'] 						= base_url('admin/medallion_orders_crud/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'medallion/orders/form';
			$data['page_title'] 					= _l('medallion_order_edit');
			$data['action'] 						= base_url('admin/medallion_orders_crud/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->medallion_order_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
			$book_info 								= $this->book_model->get($info['book_id']);
			$medallion_info 						= $this->medallion_model->get($info['medallion_id']);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'book_id',
			'label'		=> _l('select_book'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['book_id'] ?? '',
				'label' => $book_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_books'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'medallion_id',
			'label'		=> _l('select_medallion'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['medallion_id'] ?? '',
				'label' => $medallion_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_medallions'),
		];

		$this->load->view('backend/index', $data);
	}

	public function medallion_orders($param1 = 1, $param2 = 'user') {
		$data['page_name'] 		= 'medallion/orders/index';
		$data['page_title'] 	= _l('medallion_orders');
		$data['action_add'] 	= base_url('admin/medallion_orders_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_medallion_orders/' . (int)$param1 . '/' . $param2);
		$data['nav']			= (int)$param1;
		$data['medallion_type']	= $param2;
		$data['events']			= $this->event_model->get_all()['rows'] ?? [];

		$data['timestamp_start']= strtotime('-30 days', time());
		$data['timestamp_end']	= time();

		$this->load->view('backend/index', $data);
	}

	public function school_medallion_orders($param1 = 1, $param2 = 'school') {
		$data['page_name'] 		= 'medallion/orders/index';
		$data['page_title'] 	= _l('school_medallion_orders');
		$data['action_add'] 	= base_url('admin/medallion_orders_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_medallion_orders/' . (int)$param1 . '/' . $param2);
		$data['nav']			= (int)$param1;
		$data['medallion_type']	= $param2;
		$data['events']			= $this->event_model->get_all()['rows'] ?? [];

		$data['timestamp_start']= strtotime('-30 days', time());
		$data['timestamp_end']	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_medallion_orders($status = 1, $type = 'user') {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'parent_id'			=> 0,
			'type'				=> $type,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (!empty($status)) {
			$filter_data['status'] = (int)$status;
		}

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = (int)$this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('book_id')) {
			$filter_data['book_id'] = (int)$this->input->get('book_id');
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
		}

		if ($this->input->get('order_code')) {
			$filter_data['order_code'] = $this->input->get('order_code');
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->medallion_order_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->medallion_order_model->getProducts($result['id']);

			$product_names = array_map(function($item) {
				$medallion_info = $this->medallion_model->get($item['medallion_id']);

				$stock_quantity = sprintf('<span class="badge badge-%s">Stock- %s</span>',
					($medallion_info['quantity'] ?? 0) > 0
						? 'success'
						: 'danger',
					$medallion_info['quantity'] ?? 0,
				);

				if (empty($item['book_name'])) {
					return sprintf('%s for %s <br> %s', $item['medallion_name'], $item['school_name'], $stock_quantity);
				} else {
					return sprintf('%s for %s <br> %s', $item['medallion_name'], $item['book_name'], $stock_quantity);
				}
			}, $products);

			$total = array_reduce($products, function($acc = 0, $item = NULL) {
				$acc += $item['total'];
				return $acc;
			});

			$weight = array_reduce($products, function($acc = 0, $item = NULL) {
				$acc += $item['weight'];
				return $acc;
			});
			
			$customer_name = vsprintf('%s %s<br>%s<br>%s', [
				$result['first_name'],
				$result['last_name'],
				$result['mobile'],
				$result['email'],
			]);

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'#'					=> self::_renderCheckBox($result),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> _medallion_order_code($result, $shipping_tracking_info),
				'customer'			=> $customer_name,
				'product'			=> sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $product_names)),
				'weight_amount'		=> vsprintf('%s gm<br>%s%s', [
					$weight,
					$result['currency_symbol'],
					$total,
				]),
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'history'			=> self::_getMedallionOrderHistory($result['id']),
				'actions'			=> _moa_buttons($result, $shipping_tracking_info),
			];
		}

		output_json($json);
	}

	public function medallion_order_details($order_id = 0) {
		$order_info = $this->medallion_order_model->get($order_id);

		if (empty($order_info)) {
			$this->session->set_flashdata('error_message', _l('invalid_medallion_order'));
			redirect('refresh');
		}

		$data['order_info'] 	= $order_info;
		$data['products']  		= $this->medallion_order_model->getProducts($order_info['id']);
		$data['histories']		= $this->medallion_order_history_model->get_all([
			'medallion_order_id'=> $order_info['id']
		])['rows'] ?? [];
		$data['comments']		= $this->medallion_order_comment_model->get_all([
			'medallion_order_id'=> $order_info['id']
		])['rows'] ?? [];

		$data['address']  		= $this->medallion_address_model->get($order_info['address_id']);
		$data['user']	  		= $this->user_model->get($order_info['user_id']);

		$data['page_name'] 		= 'medallion/orders/details';
		$data['page_title'] 	= _l('medallion_order_details');

		$this->load->view('backend/index', $data);
	}

	public function bulk_medallion_order_update() {
		$json = [];

		$order_ids 	= $this->input->post('ids');
		$status 	= $this->input->post('status');

		if (in_array($status, [3, 4, 9, 15, 21])) {
			if ($status == 21) {
				self::_confirmMedallionOrders($order_ids);
				return;
			}

			foreach ($order_ids as $order_id) {
				$order_info = $this->medallion_order_model->get($order_id);

				$this->medallion_order_history_model->add([
					'medallion_order_id' 	=> (int)$order_info['id'],
					'description' 			=> _order_history($status),
					'status' 				=> (int)$status
				]);

				$this->medallion_order_model->edit($order_info['id'], [
					'status'			=> (int)$status,
				]);

				if ($status == 9) {
					$this->alert_model->cron($order_info['id'], 'medallionOrderReadyToShipCron');
				}

				if ($status == 4) {
					$this->medallion_order_model->edit($order_info['id'], [
						'date_completed'	=> date('Y-m-d H:i:s')
					]);

					// $this->alert_model->cron($order_info['id'], 'deliveredMedallionOrderCron');
					CI_Events::trigger('delivered_medallion_order', [
						'order_id'	=> $order_info['id']
					]);

					CI_Events::trigger('after_delivered_medallion_order', [
						'order_id'	=> $order_info['id']
					]);
				}
			}
		}

		output_json($json);
	}

	private function _confirmMedallionOrders($order_ids = []) {
		sort($order_ids);

		$user_orders = [];

		foreach ($order_ids as $order_id) {
			$order_info = $this->medallion_order_model->get($order_id);

			if (!empty($order_info['shipping_status'])) continue;
			if (empty($order_info['address_id'])) continue;

			$user_orders[$order_info['user_id']][] = $order_info;
		}

		if (empty($user_orders)) return;

		foreach ($user_orders as $user_id => $orders) {
			$parent_id = 0;

			$parent_order = array_filter($orders, function($item) {
				return !empty($item['parent_id']);
			});

			$parent_id = $parent_order['id'] ?? $orders[0]['id'] ?? 0;

			foreach ($orders as $order) {
				$this->medallion_order_model->edit($order['id'], [
					'parent_id'		=> $parent_id == $order['id'] ? 0 : $parent_id,
					'shipping_cost'	=> $parent_id == $order['id'] ? $order['shipping_cost'] : 0,
					'total'			=> $parent_id == $order['id'] ? (double)$order['total'] : (double)$order['subtotal'],
					'status'		=> 21,
				]);

				if ($order['status'] == 1) {
					// reduce medallion stock
					$medallion_info = $this->medallion_model->get($order['medallion_id']);

					$this->medallion_model->edit($medallion_info['id'], [
						'quantity'	=> ($medallion_info['quantity'] - 1)
					]);

					$this->medallion_stock_log_model->add([
						'medallion_id'			=> (int)$order['medallion_id'],
						'medallion_order_id'	=> (int)$order['id'],
						'quantity'				=> $medallion_info['quantity'],
						'quantity_order'		=> 1,
					]);
				}
			}
		}
	}

	public function sync_medallion_order() {
		$json = [];

		$order_info = $this->medallion_order_model->get($this->input->post('order_id'));
		$type 		= $this->input->post('type') ?? 'user';

		if ($order_info) {
			if (!empty($order_info['shipping_status'])) {
				$json['error'] = _l('already_shipped');
			}

			if (strtotime($order_info['date_added']) + 600 > time()) {
				$json['error'] = _l('wait_for_10_minutes');
			}

			if (!$json) {
				$this->load->library('couriers/Shiprocket_lib');

				$address_info 	= $this->medallion_address_model->get($order_info['address_id']);
				$user_info 		= $this->user_model->get($order_info['user_id']);
				$products 		= $this->medallion_order_model->getProducts($order_info['id']);

				if (empty($products)) {
					$json['error'] = _l('invalid_medallion_order_products');
				}

				if (empty($address_info)) {
					$json['error'] = _l('invalid_medallion_order_address'); 
				}

				if (empty($user_info)) {
					$json['error'] = _l('invalid_medallion_order_user');
				}

				if (empty($json)) {
					$total = array_reduce($products, function($acc = 0, $item = NULL) {
						$acc += $item['total'];
						return $acc;
					});

					$weight = array_reduce($products, function($acc = 0, $item = NULL) {
						$acc += $item['weight'];
						return $acc;
					});

					$order_info['total'] 	= $total;
					$order_info['subtotal'] = $total - $order_info['shipping_cost'];
					$order_info['weight'] 	= $weight;

					$response = $this->shiprocket_lib->bookMedallionOrder(array_merge($order_info, [
						'products' 	=> $products,
						'address'	=> $address_info,
						'user'		=> $user_info,
					]));

					if (!empty($response->order_id) && !empty($response->shipment_id)) {
						foreach ($products as $product) {
							$this->medallion_order_model->edit($product['id'], [
								'shipping_status' 			=> 1,
								'shipping_tracking_info' 	=> json_encode((array)$response),
							]);
						}

						$json['success'] = json_encode($response);
					} else {
						$json['error'] = json_encode($this->shiprocket_lib->raw ?? '');
					}
				}
			}
		} else {
			$json['error'] = _l('invalid_medallion_order');
		}

		output_json($json);
	}

	public function sync_bulk_medallion_order() {
		$json = [];
		$count = 0;

		if ($this->input->post('order_ids')) {
			foreach ($this->input->post('order_ids') as $order_id) {
				$order_info = $this->medallion_order_model->get($order_id);

				if (($order_info['shipping_status'] == 0) && !in_array($order_info['status'], [0, 4, 9, 15, 91, 92, 93])) {
					// $this->alert_model->invoiceOrderCron($order_info['id'], false);
					$count++;
				}
			}
		}

		$json['success'] 	= _l($count . ' orders_sync_successfully!');
		output_json($json);
	}

	public function medallion_order_ready_to_ship() {
		$json = [];

		$order_info = $this->medallion_order_model->get($this->input->post('order_id'));

		if ($order_info) {
			if (!$json) {
				$this->medallion_order_model->edit($order_info['id'], [
					'status' => 9,
				]);

				$this->medallion_order_history_model->add([
					'medallion_order_id' 	=> $order_info['id'],
					'description' 			=> _order_history(9),
					'status' 				=> 9,
				]);

				$this->medallion_order_packing_log_model->add([
					'medallion_order_id'	=> $order_info['id'],
					'user_id'				=> $this->session->userdata('user_id'),
				]);

				$this->alert_model->cron($order_info['id'], 'medallionOrderReadyToShipCron');

				$json['success'] = _l('medallion_order_moved_to_ready_to_ship');
			}
		} else {
			$json['error'] = _l('invalid_medallion_order');
		}

		output_json($json);
	}

	public function medallion_fetch_awb() {
		$json = [];

		$order_info = $this->medallion_order_model->get($this->input->post('order_id'));

		if ($order_info) {
			$shipping_tracking_info = !empty($order_info['shipping_tracking_info'])
				? json_decode($order_info['shipping_tracking_info'], true)
				: [];

			if (empty($shipping_tracking_info)) {
				$json['error'] = _l('medallion_order_not_synced');
			}

			if (!empty($shipping_tracking_info['awb_code'])) {
				$json['error'] = _l('awb_fetched_already');
			}

			if (!$json) {
				$this->load->library('couriers/Shiprocket_lib', 'shiprocket_lib');
				$awb_info = $this->shiprocket_lib->fetchAWB($shipping_tracking_info['order_id'] ?? '');

				log_kb(['ajax_fetch_awb:: ' => $awb_info]);

				if (!empty($awb_info->data->awb_data->awb)) {
					$shipping_tracking_info['awb_code'] = $awb_info->data->awb_data->awb ?? '';

					$this->medallion_order_model->edit($order_info['id'], [
						'shipping_tracking_info' => json_encode($shipping_tracking_info),
					]);

					$json['success'] = _l('awb_fetched');
				} else {
					$json['error'] = _l('label_not_generated');
				}

				$this->medallion_order_history_model->add([
					'medallion_order_id'	=> $order_info['id'],
					'description' 			=> _l('awb_fetched'),
					'status' 				=> $order_info['status'],
					'_deleted' 				=> 1,
				]);

				$this->medallion_order_packing_log_model->add([
					'medallion_order_id'	=> (int)$order_info['id'],
					'user_id'				=> (int)$this->session->userdata('user_id'),
				]);
			}
		} else {
			$json['error'] = _l('invalid_medallion_order');
		}

		output_json($json);
	}

	public function add_medallion_order_awb() {
		$json = [];

		if ($order_info = $this->medallion_order_model->get($this->input->post('order_id'))) {
			if (!empty($order_info['status'])) {
				$this->medallion_order_model->edit($this->input->post('order_id'), [
					'shipping_tracking_info' => json_encode([
						'awb_code'	=> $this->input->post('awb')
					])
				]);

				$json['success'] 	= _l('awb_assigned_to_the_medallion_order');
			} else {
				$json['error'] 		= _l('unknown_error');
			}
		} else {
			$json['error'] = _l('medallion_order_not_found');
		}

		output_json($json);
	}

	public function add_medallion_order_comment() {
		$json = [];

		if ($order_info = $this->medallion_order_model->get($this->input->post('order_id'))) {
			if (!empty($order_info['status'])) {
				$this->medallion_order_comment_model->add([
					'manager_id' 			=> (int)$this->session->userdata('user_id'),
					'medallion_order_id' 	=> (int)$this->input->post('order_id'),
					'description' 			=> $this->input->post('comment'),
					'status' 				=> $order_info['status'],
				]);

				$json['success'] 	= _l('medallion_order_comment_added');
			} else {
				$json['error'] 		= _l('medallion_order_not_processed_yet');
			}
		} else {
			$json['error'] = _l('medallion_order_not_found');
		}

		output_json($json);
	}

	public function cancel_medallion_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$order_info = $this->medallion_order_model->get($order_id);

			// Add order comment
			$this->medallion_order_comment_model->add([
				'manager_id' 			=> (int)$this->session->userdata('user_id'),
				'medallion_order_id' 	=> (int)$order_id,
				'description' 			=> $this->input->post('comment'),
				'status' 				=> $order_info['status'] ?? 91,
			]);

			$this->medallion_order_model->edit($order_id, [
				'status'		=> 91,
			]);

			$this->medallion_order_history_model->add([
				'medallion_order_id' 	=> (int)$order_id,
				'description' 			=> _l('order_cancelled'),
				'status' 				=> $order_info['status'] ?? 91,
			]);

			$json['success'] 	= _l('order_cancellation_request_added');
		}

		output_json($json);
	}

	public function escalate_medallion_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$order_info = $this->medallion_order_model->get($order_id);

			// Add order comment
			$this->medallion_order_comment_model->add([
				'manager_id' 			=> (int)$this->session->userdata('user_id'),
				'medallion_order_id' 	=> (int)$order_id,
				'description' 			=> $this->input->post('comment'),
				'status' 				=> $order_info['status'] ?? 93,
			]);

			$this->medallion_order_model->edit($order_id, [
				'status'		=> 93,
			]);

			$this->medallion_order_history_model->add([
				'medallion_order_id' 	=> (int)$order_id,
				'description' 			=> _l('order_escalated'),
				'status' 				=> $order_info['status'] ?? 93,
			]);

			$json['success'] 	= _l('medallion_order_escalated_request_added');
		}

		output_json($json);
	}

	public function escalate_restore_medallion_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$order_info = $this->medallion_order_model->get($order_id);

			// Add order comment
			$this->medallion_order_comment_model->add([
				'manager_id' 			=> (int)$this->session->userdata('user_id'),
				'medallion_order_id' 	=> (int)$order_id,
				'description' 			=> $this->input->post('comment'),
				'status' 				=> $order_info['status'] ?? 93,
			]);

			$this->medallion_order_history_model->add([
				'medallion_order_id' 	=> (int)$order_id,
				'description' 			=> _l('order_escalated'),
				'status' 				=> $order_info['status'] ?? 93,
			]);

			$this->medallion_order_model->edit($order_id, [
				'status'		=> 1,
			]);

			$json['success'] 	= _l('escalated_medallion_order_restore_request_added');
		}

		output_json($json);
	}

	public function export_medallion_orders($type = 'user') {
		$json = [];

		$filter_data['parent_id'] = 0;

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = (int)$this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('book_id')) {
			$filter_data['book_id'] = (int)$this->input->get('book_id');
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
		}

		if ($this->input->get('order_code')) {
			$filter_data['order_code'] = $this->input->get('order_code');
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->medallion_order_model->get_all($filter_data)['rows'] ?? [];

		$orders = [];

		$sn = 1;

		foreach ($results as $order) {
			$order_comments = $this->medallion_order_comment_model->get_all([
				'medallion_order_id' => (int)$order['id']
			])['rows'] ?? [];

			$comments = '';

			if (!empty($order_comments)) {
				foreach ($order_comments as $order_comment) {
					$comments .= $order_comment['description'] . "\n";
				}

				$comments = substr($comments, 0, -2);
			}

			$products 		= $this->medallion_order_model->getProducts($order['id']);

			if ($type == 'school') {
				$product_key	= 'school';
			} else {
				$product_key	= 'book';
			}

			$address_info 	= $this->medallion_address_model->get($order['address_id']);

			$address 		= !empty($address_info) ? vsprintf('%s, %s, %s, %s, %s, %s, %s, - %s - %s', [
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

			$total 			= round($order['total'], 2);
			$shipping_info 	= json_decode($order['shipping_info'], true);
			$shipping_tracking_info = json_decode($order['shipping_tracking_info'], true);

			foreach ($products as $key => $product) {
				$orders[] = [
					'sn'			=> $sn,
					'region'		=> strtolower($order['currency_code']) === 'inr'
						? _l('domestic')
						: _l('global'),
					'order_id'		=> $order['id'],
					'order_code'	=> $order['order_code'],
					'medallion'		=> $product['medallion_name'],
					$product_key	=> $type == 'school' ? $product['school_name'] : $product['book_name'],
					'status'		=> _os($order['status']),
					'address'		=> $address,
					'c_mobile'		=> ($order['mobile'] ?? ''),
					'c_email'		=> ($order['email'] ?? ''),
					'currency_code'	=> $order['currency_code'],
					'total'			=> $key == 0 ? $total : 0,
					'weight'		=> $product['weight'] . 'gm',
					'awb_code'		=> $shipping_tracking_info['awb_code'] ?? '',
					'shipping_info'	=> $shipping_tracking_info['courier_name'] ?? ($shipping_info['courier_name'] ?? ''),
					'date_added'	=> $order['date_added'],
					'comments'		=> $comments
				];

				$sn++;
			}
		}

		self::_downloadCsv($orders, 'medallion_orders_');

		output_json($json);
	}

	private function _getMedallionOrderHistory($order_id = 0) {
		$comments = $histories = [];

		foreach ($this->medallion_order_history_model->get_all([
			'medallion_order_id'	=> $order_id,
		])['rows'] ?? [] as $item) {
			$histories[] = vsprintf('%s - %s', [
				$item['description'],
				formatDate($item['date_added']),
			]);
		}

		foreach ($this->medallion_order_comment_model->get_all([
			'medallion_order_id'	=> $order_id,
		])['rows'] ?? [] as $item) {
			$agent_info = !empty($item['manager_id']) ? $this->user_model->get($item['manager_id']) : [];

			$comments[] = vsprintf('%s %s : %s - %s', [
				$agent_info['first_name'] ?? '',
				$agent_info['last_name'] ?? '',
				$item['description'],
				formatDate($item['date_added']),
			]);
		}

		$packing_info 	= $this->medallion_order_packing_log_model->get_all([
			'medallion_order_id'	=> $order_id
		])['rows'];

		$packing_info 	= !empty($packing_info[0]) ? $packing_info[0] : [];
		$agent_info 	= !empty($packing_info['user_id']) ? $this->user_model->get($packing_info['user_id']) : [];

		return vsprintf('<a class="text-primary" data-toggle="tooltip" title="%s"><i class="fa fa-info-circle"></i> %s %s</a><i class="text-danger fa fa-exclamation-triangle" data-toggle="tooltip" title="%s"></i><br>%s', [
			implode("\n", $histories),
			$agent_info['first_name'] ?? '',
			$agent_info['last_name'] ?? '',
			implode("\n", array_slice($comments, 1)),
			$comments[0] ?? '',
		]);
	}

	private function validateMedallionOrderForm($id = 0) {
		$info = $this->medallion_order_model->get($id);

		if (empty($this->event_book_model->get_all([
			'book_id'	=> (int)$this->input->post('book_id'),
			'event_id'	=> (int)$this->input->post('event_id'),
		])['rows'][0] ?? [])) {
			$this->session->set_flashdata('error_message', _l('book_not_in_the_event'));
			redirect(base_url('admin/medallion_orders'), 'refresh');
		}

		$_POST['type'] 		= $info['type'];

		$book_info 			= $this->book_model->get($this->input->post('book_id'));
		$_POST['user_id'] 	= $book_info['user_id'];

		$address_info 		= $this->medallion_address_model->get_all(['user_id' => $book_info['user_id']])['rows'][0] ?? [];
		$_POST['address_id']= $address_info['id'] ?? 0;

		if (empty($address_info)) {
			$this->session->set_flashdata('error_message', _l('medallion_address_not_found'));
			redirect(base_url('admin/medallion_orders'), 'refresh');
		}

		$currency_code		= get_author_currency_code($book_info['user_id']);

		$medallion_info 	= $this->medallion_model->get($this->input->post('medallion_id'));
		$_POST['subtotal']	= apply_currency_exchange($medallion_info['price'], $currency_code);
		$_POST['weight']	= $medallion_info['weight'];
		$_POST['shipping_cost'] = apply_currency_exchange($medallion_info['shipping_cost'], $currency_code);
		$_POST['total']		= apply_currency_exchange($medallion_info['price'] + $medallion_info['shipping_cost'], $currency_code);
		$_POST['status']	= $info['status'] ?? 1;

		$currency_info 		= $this->currency_model->getByCode($currency_code);
		$_POST['currency_id'] 		= $currency_info['id'];
		$_POST['currency_code'] 	= $currency_info['code'];
		$_POST['currency_symbol'] 	= $currency_info['symbol'];

		$_POST['order_code'] 		= vsprintf('BBM-%s%s%s%s', [
			time(),
			(int)$this->input->post('event_id'),
			$medallion_info['id'],
			$book_info['id'],
		]);
	}

	public function ajax_update_medallion_order() {
		$json = [];
	
		$request = $this->input->post();
	
		if (!empty($request['order_id']) && 
			!empty($request['weight']) && 
			!empty($order_info = $this->medallion_order_model->get($request['order_id']))
		) {
			$this->medallion_order_model->edit($order_info['id'], [
				'weight'   	=> $request['weight'] ?? $order_info['weight']
			]);
	
			$json['success'] 	= _l('order_updated_successfully');
		} else {
			$json['error'] 		= _l('something_went_wrong!');
		}
	
		output_json($json);
	}
}
