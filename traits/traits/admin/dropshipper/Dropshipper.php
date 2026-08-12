<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Dropshipper {
	public function book_wise_new_orders_dropshippers() {
		$data['page_name'] 		= 'order/book_wise';
		$data['heading'] 		= _l('book_wise_new_orders');
		$data['page_title'] 	= _l('book_wise_new_orders');
		$data['action_ajax'] 	= base_url('admin/ajax_book_wise_new_orders');

		$this->load->view('backend/index', $data);
	}

	public function ajax_book_wise_new_orders_dropshippers() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'status'			=> 1,
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

		$results = $this->dropshipper_order_model->bookWiseOrders($filter_data);

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

			$type = json_decode($result['option'], 1)['name'];

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
				'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
					$result['ids'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'book_id'			=> _o_b_code($result['id'], $result['version'], $type),
				'name'				=> vsprintf('%s v%s <br>Pages:: %s', [
					$result['name'],
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
				'printed'			=> ($result['printing_status'] !== '0') ? 'true' : 'false',
				'assign'		  	=> ($result['assign_printer_id']) ? $printer_info['first_name'] . ' ' . $printer_info['last_name'] : 'NA',
				'actions'			=> '',
			];
		}

		output_json($json);
	}

	public function dropshipper_stats() {
		$this->load->helper('dropshipper');

		$data['page_name']		= 'dropshipper/stats';
		$data['page_title']		= _l('dropshipper');

		$data['order_stats'] 				= _get_dropshipper_order_stats();
		$data['order_stats_paperback'] 		= _get_dropshipper_order_stats('1', 'Paperback');
		$data['order_stats_hardcover'] 		= _get_dropshipper_order_stats('1', 'Hard Cover');
		$data['order_stats_blackwhite'] 	= _get_dropshipper_order_stats('2', 'Black White');

		// $data['reprint_stats'] 				= _get_dropshipper_count_copies();
		// $data['reprint_stats_paperback']	= _get_dropshipper_count_copies('Paperback');
		// $data['reprint_stats_hardcover']	= _get_dropshipper_count_copies('Hard Cover');
		// $data['reprint_stats_blackwhite']	= _get_dropshipper_count_copies('Black White');


		$printers = $this->student_model->get_by_role_id_in([_dropshipper_role()]);

		$data['printers'] = [];

		foreach ($printers as $key => $value) {
			$data['printers'][] = _get_dropshipper_wise_stats($value);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_assign_order_to_dropshipper($option_type = '1', $new_products = [], $self_call = false) {
		if ($this->input->post('ids')) {
			$order_ids = $this->input->post('ids');

			$assignment_id = $this->dropshipper_assignment_model->get_all([
				'printer_id'	=> (int)$this->input->post('printer_id'),
				'date_added'	=> date('Y-m-d'),
				'option_type'	=> (int)$option_type,
			])['rows'][0]['id'] ?? 0;

			if (empty($assignment_id)) {
				$assignment_id = $this->dropshipper_assignment_model->add([
					'printer_id'	=> (int)$this->input->post('printer_id'),
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'option_type'	=> (int)$option_type,
				]);
			}

			$printer_extra_detail_info = $this->printer_extra_details_model->getByPrinterId($this->input->post('printer_id'));
			$pickup_location_id = $printer_extra_detail_info['pickup_location_id'] ?? 1;

			$hardcover = $paperback = $black_white = 0;

			$filter_data = [];
			$filter_data['option_type'] = [$option_type];

			foreach ($order_ids as $order_id) {
				$order_info = $this->order_model->get($order_id);

				if (in_array($order_info['status'], [21, 91, 92, 94])) continue;

				if (empty($self_call) && in_array($order_info['status'], [93])) {
					$this->order_model->editById($order_id, [
						'assign_printer_id' => (int)$this->input->post('printer_id'),
						'pickup_location_id'=> $pickup_location_id,
						'printing_status' 	=> 0,
					]);
				} else {
					$this->order_model->edit($order_id, [
						'assign_printer_id' => (int)$this->input->post('printer_id'),
						'pickup_location_id'=> (int)$pickup_location_id,
						'status' 			=> 2,
					]);
				}

				$products = !empty($new_products) ? $new_products : $this->order_model->getProducts($order_id, $filter_data);

				foreach ($products as $product) {
					$option = json_decode($product['option'], true);

					if (mb_strtolower($option['name']) === 'ebook') continue;

					/*$stock_info = $this->book_stock_model->get_all([
						'book_id'	=> $product['product_id'],
						'version'	=> $product['version'],
						'option'	=> !empty($option['name']) ? strtolower($option['name']) : 'paperback',
					])['rows'][0] ?? [];

					if (!empty($stock_info['quantity'])) {
						$quantity_hold = $product['quantity'] > $stock_info['quantity']
							? $stock_info['quantity']
							: $product['quantity'];

						$this->book_stock_model->edit($stock_info['id'], [
							'quantity'		=> ($stock_info['quantity'] - $quantity_hold),
							'quantity_hold'	=> ($stock_info['quantity_hold'] + $quantity_hold),
						]);

						$product['quantity'] -= $quantity_hold;
					}

					if (empty($product['quantity'])) continue;*/

					// remove hold quantity from the printing
					$required_qauntity = $product['quantity'];

					if (
						empty($new_products) &&
						($stock_history_info = $this->book_stock_history_model->get_all([
							'order_id'			=> (int)$order_id,
							'book_id'			=> (int)$product['product_id'],
							'version'			=> (int)$product['version'],
							'option'			=> $option['name'],
						])['rows'][0] ?? [])
					) {
						$required_qauntity -= $stock_history_info['quantity_hold'];
					}

					if ($required_qauntity > 0) {
						$this->dropshipper_assignlog_model->add([
							'assignment_id' => (int)$assignment_id,
							'order_id' 		=> $order_id,
							'version' 		=> $product['version'],
							'product_id' 	=> $product['product_id'],
							'option' 		=> $product['option'],
							'quantity' 		=> (int)$required_qauntity,
							'printer_id'	=> (int)$this->input->post('printer_id'),
							'manager_id' 	=> (int)$this->session->userdata('user_id')
						]);

						if (mb_strtolower($option['name']) == 'paperback') {
							$paperback += $product['quantity'];
						} else if (mb_strtolower($option['name']) == 'black white') {
							$black_white += $product['quantity'];
						} else {
							$hardcover += $product['quantity'];
						}
					}
				}

				// Removed as per request
				// $this->order_history_model->add([
				// 	'order_id' 		=> $order_id,
				// 	'description' 	=> _order_history(2),
				// 	'status' 		=> 2
				// ]);

				// check if any product of this order exists in printer assignment
				if ($this->dropshipper_assignlog_model->get_all([
					'printer_id'	=> (int)$this->input->post('printer_id'),
					'order_id'		=> (int)$order_id,
				])['total'] == 0) {
					// mark printed since all products are fullfilled from the stock
					$this->order_model->edit($order_id, [
						'assign_printer_id' => 0,
						'pickup_location_id'=> $pickup_location_id,
						'status' 			=> 8,
					]);
				}
			}

			$this->alert_model->assignedAlert($this->input->post('printer_id'), [
				'hardcover'		=> $hardcover,
				'paperback'		=> $paperback,
				'black_white'	=> $black_white,
				'assignment_id'	=> $assignment_id,
			]);

			$this->dropshipper_assignment_model->edit($assignment_id, [
				'description' => json_encode([
					'hardcover'		=> $hardcover,
					'paperback'		=> $paperback,
					'black_white'	=> $black_white,
				]),
			]);

			if ($option_type == 2) {
				self::ajax_assign_order_to_printer(1, $new_products, true);
			}
		}

		output_json([
			'success'	=> _l('assigned_printer_successfully')
		]);
	}
}
