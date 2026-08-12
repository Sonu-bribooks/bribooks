<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DropshipperAssignment {

	private $currency_id = 47;

	public function dropshipper_all_orders($param1 = NULL, $param2 = NULL) {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('all_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders');
		$data['status'] 			= 0;

		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_new_orders($param1 = NULL, $param2 = NULL) {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('new_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/1');
		$data['status'] 			= 1;

		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_in_print_orders($param1 = NULL, $param2 = NULL) {
		$data['page_name'] 		= 'dropshipper_assignment/orders';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('in_print_orders');
		$data['action_ajax'] 	= base_url('admin/ajax_dropshipper_order_with_stats/2');
		$data['status'] 		= 2;
		$data['last_download'] 	= [];
		$data['last_request'] 	= [];

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
			'actions'
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_qaqc_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= 'QA/QC orders';
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/8');
		$data['status'] 			= 8;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_afs() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('available_for_shipping');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/21');
		$data['status'] 			= 21;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_ready_to_ship() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('ready_to_ship');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/9');
		$data['status'] 			= 9;

		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_shipped_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('shipped_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/3');
		$data['status'] 			= 3;

		$data['printer_list'] = [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_delivered_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('delivered_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/4');
		$data['status'] 			= 4;

		$data['printer_list'] = [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_return_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('return_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/15');
		$data['status'] 			= 15;

		$data['printer_list'] = [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_escalate_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('escalate_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/93');
		$data['status'] 			= 93;

		$data['printer_list'] = [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_cancelled_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('cancelled_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_orders/91');
		$data['status'] 			= 91;

		$data['printer_list'] = [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function rollback() {
		$this->dropshipper_assignlog_model->editByOrderId($this->input->post('id'), [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->order_model->editById($this->input->post('id'), [
			'pickup_location_id'		=> $this->config->item('default_pickup_location_id'),
			'assign_printer_id'			=> 0,
		]);

		$this->load->library('Stock_lib', 'stock_lib');
		$this->stock_lib->orderFulfill($this->input->post('id'));

		redirect(base_url('/admin/dropshipper_in_print_orders'));
	}

	public function restore() {
		$this->dropshipper_assignlog_model->editByOrderId($this->input->post('id'), [
			'status'		=> 1,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->order_model->editById($this->input->post('id'), [
			'status'		=> 1,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		redirect(base_url('/admin/dropshipper_in_print_orders'));
	}

	public function escalate() {
		$this->order_model->editById($this->input->post('id'), [
			'status'		=> 93,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		redirect(base_url('/admin/dropshipper_in_print_orders'));
	}

	public function export_dropshipper_assignment_by_filter() {
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$json = [];

		if ($this->input->get('printer_id')) {
			$filter_data['printer_id'] = (int)$this->input->get('printer_id');
		}

		$results = $this->dropshipper_assignlog_model->get_all($filter_data)['rows'] ?? [];

		$sn = 1;

		$dropshipper_assignments = $sort_order = [];

		foreach ($results as $result) {
			$printer_info 	= $this->user_model->get($result['printer_id']);
			$book_info 		= $this->book_version_model->getByVersion($result['product_id'], $result['version']);
			$order_info 	= $this->order_model->get($result['order_id']);

			if (empty($order_info)) continue;

			$assignment_info = $this->dropshipper_assignment_model->get($result['assignment_id']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$option = json_decode($result['option'], true);

			$order_product_info = $this->order_product_model->get_all([
				'order_id'		=> $result['order_id'],
				'product_id'	=> $result['product_id'],
			])['rows'][0] ?? [];

			$dropshipper_assignments[] = [
				'sn'				=> $sn,
				'assignment_code'	=> $assignment_info['code'] ?? '',
				'order_code'		=> $order_info['order_code'],
				'book'				=> $book_info['name'],
				'author_name'		=> $book_info['author_name'],
				'version'			=> $result['version'],
				'option'			=> $option['name'],
				'quantity'			=> $result['quantity'],
				'unit_pages'		=> ($total_pages * 2 + 1),
				'total_pages'		=> $result['quantity'] * ($total_pages * 2 + 1),
				'unit_price'		=> sprintf('%s %s', $order_info['currency_code'], ($order_product_info['price'] + $order_product_info['ppp_total'])),
				'total_price'		=> sprintf('%s %s', $order_info['currency_code'], $order_product_info['total']),
				'cost_per_page'		=> !empty($order_product_info['total']) ? sprintf('%s %s', $order_info['currency_code'], round($order_product_info['total'] / ($order_product_info['quantity'] * ($total_pages * 2 + 1)), 2)) : 0,
				'printer'			=> sprintf('%s %s', $printer_info['first_name'] ?? '', $printer_info['last_name'] ?? ''),
				'status'			=> strip_tags(_printer_status($result['status'])),
				'assignment_date'	=> formatDate($result['date_added']),
			];

			$sort_order[] = $book_info['name'];

			$sn++;
		}

		array_multisort($sort_order, $dropshipper_assignments);

		self::_downloadCsv($dropshipper_assignments, 'dropshipper_assignments' . '_' . (int)$assignment_id . '_');

		output_json($json);
	}

	public function export_dropshipper_assignment_csv($assignment_id = 0) {
		if (empty($assignment_id)) return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$assignment_info = $this->dropshipper_assignment_model->get($assignment_id);

		$json = [];

		$filter_data['assignment_id'] = (int)$assignment_id;

		$results = $this->dropshipper_assignlog_model->get_all($filter_data)['rows'] ?? [];

		$sn = 1;

		$dropshipper_assignments = $sort_order = [];

		foreach ($results as $result) {
			$printer_info 	= $this->user_model->get($result['printer_id']);
			$book_info 		= $this->book_version_model->getByVersion($result['product_id'], $result['version']);
			$order_info		= $this->order_model->get($result['order_id']);

			if (empty($order_info)) continue;

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$option = json_decode($result['option'], true);

			$order_product_info = $this->order_product_model->get_all([
				'order_id'		=> $result['order_id'],
				'product_id'	=> $result['product_id'],
			])['rows'][0] ?? [];

			$dropshipper_assignments[] = [
				'sn'				=> $sn,
				'assignment_code'	=> $assignment_info['code'],
				'order_code'		=> $order_info['order_code'],
				'book'				=> $book_info['name'],
				'author_name'		=> $book_info['author_name'],
				'version'			=> $result['version'],
				'option'			=> $option['name'],
				'quantity'			=> $result['quantity'],
				'unit_pages'		=> ($total_pages * 2 + 1),
				'total_pages'		=> $result['quantity'] * ($total_pages * 2 + 1),
				'unit_price'		=> sprintf('%s %s', $order_info['currency_code'], ($order_product_info['price'] + $order_product_info['ppp_total'])),
				'total_price'		=> sprintf('%s %s', $order_info['currency_code'], $order_product_info['total']),
				'cost_per_page'		=> !empty($order_product_info['total']) ? sprintf('%s %s', $order_info['currency_code'], round($order_product_info['total'] / ($order_product_info['quantity'] * ($total_pages * 2 + 1)), 2)) : 0,
				'printer'			=> sprintf('%s %s', $printer_info['first_name'] ?? '', $printer_info['last_name'] ?? ''),
				'status'			=> strip_tags(_printer_status($result['status'])),
				'assignment_date'	=> formatDate($result['date_added']),
			];

			$sort_order[] = $book_info['name'];

			$sn++;
		}

		array_multisort($sort_order, $dropshipper_assignments);

		self::_downloadCsv($dropshipper_assignments, 'dropshipper_assignments' . '_' . (int)$assignment_id . '_');

		output_json($json);
	}

	public function export_dropshipper_csv_by_assignment($assignment_id = 0) {
		if (empty($assignment_id)) return;

		$products = $this->dropshipper_order_model->printerAssignData([
			'assignment_id'		=> $assignment_id,
		])['rows'] ?? [];

		self::_genBookZipPdfsCsv($products, 'dropshipper_assignments' . '_' . (int)$assignment_id . '_');
	}

	public function export_dropshipper_csv_po_by_assignment($assignment_id = 0) {
		if (empty($assignment_id))
			return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');

		$assignment_info = $this->dropshipper_assignment_model->get($assignment_id);

		if (empty($assignment_info)) return;

		$printer_costing_info = $this->printer_costing_model->getByPrinterId($assignment_info['printer_id']);
		/*if (empty($printer_costing_info))
			return;*/

		$json = [];

		$filter_data = [];
		$filter_data['assignment_id'] = $assignment_info['id'];
		$filter_data['assign_printer_id'] = $assignment_info['printer_id'];

		$results = $this->dropshipper_order_model->printerAssignData($filter_data);

		$printer_po = [];

		foreach ($results['rows'] ?? '' as $result) {
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$page 	= 0;
			$pages 	= ($total_pages * 2 + 1);

			if (!empty($printer_costing_info['page'])) {
				switch ($printer_costing_info['page']) {
					case '2':
						$page = $pages%2;
						$page = ($page > 0) ? (2 - $page) : 0;
						break;

					case '4':
						$page = $pages%4;
						$page = ($page > 0) ? (4 - $page) : 0;
						break;

					case '8':
						$page = $pages%8;
						$page = ($page > 0) ? (8 - $page) : 0;
						break;

					default:
						break;
				}
			}

			$pages 			= $pages + $page + $printer_costing_info['add_cover_pages_per_book'];

			$pages_price 	= $pages * ($printer_costing_info['rate_per_page'] ?? 0);
			$binding_price 	= $printer_costing_info['binding_cost_per_book'] ?? 0;

			$unit_price 	= $pages_price + $binding_price;

			$printer_po[] = [
				'currency'			=> $printer_costing_info['currency'] ?? '',
				'book_name'			=> $book_info['name'],
				'quantity'			=> $result['quantity'],
				'pages'				=> $pages,
				'total_pages'		=> $pages * $result['quantity'],
				'pages_price'		=> ($pages_price * $result['quantity']) ?? 0,
				'binding_price'		=> ($binding_price * $result['quantity'])  ?? 0,
				'unit_price'		=> $unit_price ?? 0,
				'total_price'		=> ($unit_price * $result['quantity']) ?? 0
			];
		}

		self::_downloadCsv($printer_po, 'printer_po' . '_' . (int)$assignment_id . '_');

		output_json($json);
	}

	public function export_dropshipper_csv_po_monthly() {
		if (empty($printer_id = (int)$this->input->get('printer_id'))) return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');

		$printer_costing_info = $this->printer_costing_model->getByPrinterId($printer_id);

		$json = [];

		$filter_data = [];
		$filter_data['assign_printer_id'] = $printer_id;

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

		$results = $this->dropshipper_order_model->printerAssignData($filter_data);

		$printer_po = [];

		foreach ($results['rows'] ?? '' as $result) {
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$page 	= 0;
			$pages 	= ($total_pages * 2 + 1);

			if (!empty($printer_costing_info['page'])) {
				switch ($printer_costing_info['page']) {
					case '2':
						$page = $pages%2;
						$page = ($page > 0) ? (2 - $page) : 0;
						break;

					case '4':
						$page = $pages%4;
						$page = ($page > 0) ? (4 - $page) : 0;
						break;

					case '8':
						$page = $pages%8;
						$page = ($page > 0) ? (8 - $page) : 0;
						break;

					default:
						break;
				}
			}

			$pages 			= $pages + $page + $printer_costing_info['add_cover_pages_per_book'];

			$pages_price 	= $pages * ($printer_costing_info['rate_per_page'] ?? 0);
			$binding_price 	= $printer_costing_info['binding_cost_per_book'] ?? 0;

			$unit_price 	= $pages_price + $binding_price;

			$printer_po[] = [
				'assignment_code'	=> $result['assignment_code'] ?? '',
				'currency'			=> $printer_costing_info['currency'] ?? '',
				'book_name'			=> $book_info['name'],
				'quantity'			=> $result['quantity'],
				'pages'				=> $pages,
				'total_pages'		=> $pages * $result['quantity'],
				'pages_price'		=> ($pages_price * $result['quantity']) ?? 0,
				'binding_price'		=> ($binding_price * $result['quantity'])  ?? 0,
				'unit_price'		=> $unit_price ?? 0,
				'total_price'		=> ($unit_price * $result['quantity']) ?? 0,
				'date'				=> $result['date_added']
			];
		}

		self::_downloadCsv($printer_po, 'printer_po' . '_' . (int)$assignment_id . '_');

		output_json($json);
	}

	public function export_dropshipper_csv_po_total_by_dropshipper_id() {
		if (empty($printer_id = (int)$this->input->get('printer_id'))) return;

		$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');

		$printer_costing_info = $this->printer_costing_model->getByPrinterId($printer_id);

		if (empty($printer_costing_info)) return;

		// pr($printer_costing_info);

		$json = [];

		$filter_data = [];
		$filter_data['assign_printer_id'] = $printer_id;

		$results = $this->dropshipper_order_model->printerAssignData($filter_data);

		// pr($results, 1);

		$printer_po = [];

		foreach ($results['rows'] ?? '' as $result) {
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$page 	= 0;
			$pages 	= ($total_pages * 2 + 1);

			if (!empty($printer_costing_info['page'])) {
				switch ($printer_costing_info['page']) {
					case '2':
						$page = $pages%2;
						$page = ($page > 0) ? (2 - $page) : 0;
						break;

					case '4':
						$page = $pages%4;
						$page = ($page > 0) ? (4 - $page) : 0;
						break;

					case '8':
						$page = $pages%8;
						$page = ($page > 0) ? (8 - $page) : 0;
						break;

					default:
						break;
				}
			}

			$pages 			= $pages + $page + ($printer_costing_info['add_cover_pages_per_book'] ?? 0);

			$pages_price 	= $pages * ($printer_costing_info['rate_per_page'] ?? 0);
			$binding_price 	= $printer_costing_info['binding_cost_per_book'] ?? 0;

			$unit_price 	= $pages_price + $binding_price;

			$total_amount 	= ($unit_price * $result['quantity']) ?? 0;

			if (empty($printer_po[$result['assignment_code']])) {
				$printer_po[$result['assignment_code']]['date'] = $result['date_added'];
				$printer_po[$result['assignment_code']]['total_quantity'] = $result['quantity'];
				$printer_po[$result['assignment_code']]['total_pages'] = $pages * $result['quantity'];
				$printer_po[$result['assignment_code']]['total_pages_price'] = $pages_price * $result['quantity'];
				$printer_po[$result['assignment_code']]['total_binding_price'] = $binding_price * $result['quantity'];
				$printer_po[$result['assignment_code']]['total_amount'] = $total_amount;
			} else {
				$printer_po[$result['assignment_code']]['date'] = $result['date_added'];
				$printer_po[$result['assignment_code']]['total_quantity'] += $result['quantity'];
				$printer_po[$result['assignment_code']]['total_pages'] += $pages * $result['quantity'];
				$printer_po[$result['assignment_code']]['total_pages_price'] += $pages_price * $result['quantity'];
				$printer_po[$result['assignment_code']]['total_binding_price'] += $binding_price * $result['quantity'];
				$printer_po[$result['assignment_code']]['total_amount'] += $total_amount;
			}
		}

		$po_totals = [];

		foreach ($printer_po as $key => $value) {
			$po_totals[] = [
				'date'					=> $value['date'],
				'lot_id'				=> 'ID-' . $key,
				'total_quantity'		=> $value['total_quantity'],
				'total_pages'			=> $value['total_pages'],
				'total_pages_price'		=> $value['total_pages_price'],
				'total_binding_price'	=> $value['total_binding_price'],
				'po_total'				=> $value['total_amount']
			];
		}

		// pr($po_totals, 1);

		self::_downloadCsv($po_totals, 'printer_po_total' . '_' . $printer_id . '_');

		output_json($json);
	}

	public function export_dropshipper_csv_by_printer_id() {
		if (empty($printer_id = (int)$this->input->get('printer_id'))) return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');

		$printer_costing_info = $this->printer_costing_model->getByPrinterId($printer_id);

		$json = [];

		$filter_data = [];
		$filter_data['assign_printer_id'] = $printer_id;

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

		$results = $this->dropshipper_order_model->printerAssignData($filter_data);
		// pr($results,1);

		$printer_csv = [];

		foreach ($results['rows'] ?? '' as $result) {
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$page 	= 0;
			$pages 	= ($total_pages * 2 + 1);

			if(!empty($printer_costing_info['page'])) {
				switch ($printer_costing_info['page']) {
					case '2':
						$page = $pages%2;
						$page = ($page > 0) ? (2 - $page) : 0;
						break;

					case '4':
						$page = $pages%4;
						$page = ($page > 0) ? (4 - $page) : 0;
						break;

					case '8':
						$page = $pages%8;
						$page = ($page > 0) ? (8 - $page) : 0;
						break;

					default:
						break;
				}
			}

			$pages 			= $pages + $page + $printer_costing_info['add_cover_pages_per_book'];

			$pages_price 	= $pages * ($printer_costing_info['rate_per_page'] ?? 0);
			$binding_price 	= $printer_costing_info['binding_cost_per_book'] ?? 0;

			$unit_price 	= $pages_price + $binding_price;

			$printer_csv[] = [
				'assignment_code'	=> $result['assignment_code'] ?? '',
				'currency'			=> $printer_costing_info['currency'] ?? '',
				'book_name'			=> $book_info['name'],
				'quantity'			=> $result['quantity'],
				'pages'				=> $pages,
				'total_pages'		=> $pages * $result['quantity'],
				'pages_price'		=> ($pages_price * $result['quantity']) ?? 0,
				'binding_price'		=> ($binding_price * $result['quantity'])  ?? 0,
				'unit_price'		=> $unit_price ?? 0,
				'total_price'		=> ($unit_price * $result['quantity']) ?? 0,
				'assignment_date'	=> formatDate($result['date_added']),
			];
		}

		self::_downloadCsv($printer_csv, 'printer_csv_' . (int)$printer_id . '_');

		output_json($json);
	}

	public function ajax_dropshipper_order_with_stats($status = 1) {
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

			$actions = '';

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
				'actions'			=> $actions,
			];
		}

		output_json($json);
	}

	public function ajax_dropshipper_orders($status = 0, $assignment_code = '', $site_code = '') {
		$json['data'] = [];

		$dropshippper 	= $this->dropshipper_assignment_model->getByCode($assignment_code);
		$columns 		= $this->input->get('columns');

		$filter_data = [
			'start'				 => (int)$this->input->get('start'),
			'limit'				 => (int)$this->input->get('length'),
			'search'			 => trim($this->input->get('search[value]')),
			'sort'				 => 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				 => mb_strtoupper($this->input->get('order[0][dir]')),
			'is_dropshipper' 	 => TRUE,
			'ne_status'	 		 => 0,
		];

		if ($status) {
			$filter_data['status'] = (int)$status;
			$filter_data['dropshipper_assign_log_status'] = (int)$status;

			if ($status == 21) {
				$filter_data['sort'] = 'order.date_added';
				$filter_data['order'] = 'ASC';
			}
		}

		if (!empty($this->input->get('assign_date'))) {
			$filter_data['date_added'] = $this->input->get('assign_date');
		}

		// printed and printed with ebook
		$filter_data['order_type'] = [1];

		if (empty($assignment_code)) {
			$filter_data['option_type'] 	= [1];
			$filter_data['ne_option_type'] 	= [2];
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products 				= $this->order_model->getProducts($result['id'], $filter_data);
			$site_info 				= $this->site_model->get($result['site_id']);
			$printer_assign_info 	= $this->dropshipper_assignlog_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'paperback'
			])['rows'];

			$printer_info 			= !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];
			$customer_info 			= $this->user_model->get($result['user_id']);
			$printer_assign_info 	= !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];
			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
					$result['id'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> self::_formatOrderCode($result),
				'product'			=> _dropshipper_op_name($products, $result),
				'weight'			=> ($result['weight'] ?? 0) . ' gm',
				'status'			=> _sd($result['status']),
				'dropshipper'		=> _o_dropshipper($result, $printer_info, $printer_assign_info, 'paperback'),
				'order_date'		=> formatDate($result['date_added']),
				'actions'			=> self::_formatActions($result),
			];
		}

		output_json($json);
	}

	public function dropshipper_all_bw_orders($param1 = NULL, $param2 = NULL) {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('all_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders');
		$data['status'] 			= 0;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_new_bw_orders($param1 = NULL, $param2 = NULL) {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('new_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/1');
		$data['status'] 			= 0;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_in_print_bw_orders($param1 = NULL, $param2 = NULL) {
		$data['page_name'] 		= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('in_print_orders');
		$data['action_ajax'] 	= base_url('admin/ajax_dropshipper_bw_order_with_stats/2');
		$data['status'] 		= 2;
		$data['last_download'] 	= [];
		$data['last_request'] 	= [];

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
			'actions'
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_qaqc_bw_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= 'QA/QC orders';
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/8');
		$data['status'] 			= 8;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_bw_afs() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('available_for_shipping');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/21');
		$data['status'] 			= 21;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_bw_ready_to_ship() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('ready_to_ship');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/9');
		$data['status'] 			= 9;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_shipped_bw_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('shipped_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/3');
		$data['status'] 			= 3;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_delivered_bw_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('delivered_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/4');
		$data['status'] 			= 4;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_return_bw_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('return_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/15');
		$data['status'] 			= 15;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_escalate_bw_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('escalate_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/93');
		$data['status'] 			= 93;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function dropshipper_cancelled_bw_orders() {
		$data['in_print_attr'] 		= '';
		$data['in_print_action'] 	= '';
		$data['in_print_text'] 		= '';
		$data['page_name'] 			= 'dropshipper_assignment/bw_orders';
		$data['headeing'] 			= _l('orders');
		$data['page_title'] 		= _l('cancelled_orders');
		$data['action_ajax'] 		= base_url('admin/ajax_dropshipper_bw_orders/91');
		$data['status'] 			= 91;
		$data['printer_list'] 		= [];

		$data['fields'] = [
			'#',
			'sn',
			'order_code',
			'product',
			'weight',
			'dropshipper',
			'order_date',
			'actions',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_dropshipper_bw_orders($status = 0, $assignment_code = '', $site_code = '') {
		$json['data'] = [];

		$dropshippper = $this->dropshipper_assignment_model->getByCode($assignment_code);

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				 => (int)$this->input->get('start'),
			'limit'				 => (int)$this->input->get('length'),
			'search'			 => trim($this->input->get('search[value]')),
			'sort'				 => 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				 => mb_strtoupper($this->input->get('order[0][dir]')),
			'is_dropshipper' 	 => TRUE,
			'ne_status'	 		 => 0,
		];

		if ($status) {
			$filter_data['status'] 							= (int)$status;
			$filter_data['dropshipper_assign_log_status'] 	= (int)$status;

			if ($status == 21) {
				$filter_data['sort'] 	= 'order.date_added';
				$filter_data['order'] 	= 'ASC';
			}
		}

		// printed and printed with ebook
		$filter_data['order_type'] = [1];

		if (empty($assignment_code)) {
			$filter_data['option_type'] 	= [2];
			$filter_data['ne_option_type'] 	= [1];
		}

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products 				= $this->order_model->getProducts($result['id'], $filter_data);
			$site_info 				= $this->site_model->get($result['site_id']);
			$printer_assign_info 	= $this->dropshipper_assignlog_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'paperback'
			])['rows'];

			$printer_info 			= !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];
			$customer_info 			= $this->user_model->get($result['user_id']);
			$printer_assign_info 	= !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];
			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
					$result['id'],
				]),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> self::_formatOrderCode($result),
				'product'			=> _dropshipper_op_name($products, $result),
				'weight'			=> $result['weight'] ?? 0 . ' gm',
				'status'			=> _sd($result['status']),
				'dropshipper'		=> _o_dropshipper($result, $printer_info, $printer_assign_info, 'paperback'),
				'order_date'		=> formatDate($result['date_added']),
				'actions'			=> self::_formatActions($result),
			];
		}

		output_json($json);
	}

	public function ajax_dropshipper_rollback() {
		$json = [];

		if (
			($order_info = $this->order_model->get($this->input->post('order_id'))) &&
			$order_info['pickup_location_id'] != $this->config->item('default_pickup_location_id')
		) {
			$results = $this->dropshipper_assignlog_model->get_all([
				'order_id' => $order_info['id']
			])['rows'] ?? [];

			foreach ($results as $item) {
				if ($item['status'] != 1) {
					$json['error'] = _l('order_processed_already');
					output_json($json);
					exit;
				}

				$this->dropshipper_assignlog_model->delete($item['id']);
			}

			$this->order_model->edit($order_info['id'], [
				'assign_printer_id' 	=> 0,
				'pickup_location_id' 	=> (int)$this->config->item('default_pickup_location_id'),
			]);

			$this->dropshipper_assign_rollback_model->add([
				'order_id'	=> $order_info['id'],
				'user_id'	=> $this->session->userdata('user_id'),
				'comment'	=> $this->input->post('comment'),
			]);

			$this->stock_lib->orderFulfill($order_info['id']);

			$json['success'] 	= _l('order_rollback_success');
		} else {
			$json['error'] 		= _l('unable_to_process');
		}

		output_json($json);
	}

	public function ajax_dropshipper_bw_order_with_stats($status = 1) {
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

		$filter_data['option_type'] = [2];

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

			$actions = '';

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
				'actions'			=> $actions,
			];
		}

		output_json($json);
	}

	public function ajax_cancel_dropshipper_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			if (!empty(array_filter(
				$this->dropshipper_assignlog_model->get_all([
					'order_id'	=> $order_id,
				])['rows'] ?? [],
				function ($item) {
					return $item['status'] > 1;
				}
			))) {
				output_json([
					'error' => _l('order_can\'t_be_cancelled')
				]);
				return;
			}

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

			$this->order_model->edit($order_id, [
				'status'		=> 91,
			]);

			$this->order_history_model->add([
				'order_id' 		=> (int)$order_id,
				'description' 	=> 'Order Cancelled',
				'status' 		=> $order_info['status'] ?? 91,
			]);

			if (
				!empty($event_orders = $this->event_order_model->get_all([
					'order_id' => $order_info['id']
				])['rows'] ?? [])
			) {
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

			$this->dropshipper_assignlog_model->editByOrderId($order_id, [
				'_deleted'		=> 1,
				'date_deleted'	=> date('Y-m-d H:i:s'),
			]);

			$json['success'] 	= _l('order_cancel_request_added');
		}

		output_json($json);
	}

	public function escalate_dropshipper_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$this->load->model('order/OrderHistory_model', 'order_history_model');
			$this->load->model('dropshipper/EscalatedDropshipperOrders_model', 'escalated_dropshipper_orders_model');

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
				'description' 	=> 'Order Escalated',
				'status' 		=> $order_info['status'] ?? 93,
			]);

			$this->escalated_dropshipper_orders_model->add([
				'order_id' 		=> (int)$order_id,
				'description' 	=> $this->input->post('comment'),
				'order_status' 	=> $order_info['status'] ?? 93,
			]);

			$json['success'] 	= _l('order_escalated_request_added');
		}

		output_json($json);
	}

	public function restore_dropshipper_escalate_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$this->load->model('order/OrderHistory_model', 'order_history_model');
			$this->load->model('dropshipper/EscalatedDropshipperOrders_model', 'escalated_dropshipper_orders_model');

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
				'description' 	=> 'Order Escalated',
				'status' 		=> $order_info['status'] ?? 93,
			]);

			$escalated_orders_info = $this->escalated_dropshipper_orders_model->getByOrder($order_id);

			if (!empty($escalated_orders_info)) {
				$this->db->update('order', [
					'status'		=> $escalated_orders_info['order_status'],
					'date_modified'	=> date('Y-m-d H:i:s')
				], [
					'id'			=> (int)$order_id
				]);

				$this->escalated_dropshipper_orders_model->delete($escalated_orders_info['id']);
			}

			$json['success'] 	= _l('escalated_order_restore_request_added');
		}

		output_json($json);
	}

	private function _formatOrderCode($result = []) {
		return vsprintf('<a href="%s" target="_blank">%s</a><br>%s', [
			base_url('admin/order_details/' . $result['id']),
			$result['order_code'],
			_dosb($result['status'])
		]);
	}

	private function _formatActions($result = []) {
		// if ($result['status'] > 1) return;

		$buttons = [];
		if (in_array($result['status'], [1])) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm btn-rollback mb-1" data-toggle="modal" data-target="#rollbackModal" data-id="%s">%s</button>', [
				$result['id'],
				_l('rollback'),
			]);
			$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm btn-cancel mb-1" data-toggle="modal" data-target="#cancelModal" data-id="%s">%s</button>', [
				$result['id'],
				_l('cancel'),
			]);
			$buttons[] = vsprintf('<button type="button" class="btn btn-dark btn-sm change-order-version mb-1" data-id="%s">%s</button>', [
				$result['id'],
				_l('change_version'),
			]);
			$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm escalate-dropshipper-btn" data-toggle="modal" data-target="#escalateModal" data-id="%s">%s</button>', [
				$result['id'],
				_l('escalate'),
			]);
		}

		if (in_array($result['status'], [93])) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm restore-escalate-dropshipper-btn" data-toggle="modal" data-target="#escalateRestoreModal" data-id="%s">%s</button>', [
				$result['id'],
				_l('restore'),
			]);
		}

		return implode('<br>', $buttons);
	}

	public function change_dropshipper_order_version() {
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

			$products 			= $this->order_model->getProducts($this->input->post('order_id'));
			$change_products 	= $this->input->post('product');

			foreach ($products as $item) {
				if (!empty($change_products[$item['product_id']])) {
					$printing_status = $this->dropshipper_assignlog_model->get_all([
						'order_id'		=> $this->input->post('order_id'),
						'product_id'	=> $item['product_id'],
					])['rows'][0] ?? [];

					if ($printing_status && $printing_status['status'] > 1) {
						$json['error'] = _l('sent_to_the_printer');
						break;
					}
				}
			}

			foreach ($products as $item) {
				if (!empty($change_products[$item['product_id']])) {
					$printing_status = $this->dropshipper_assignlog_model->get_all([
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

					if ($printing_status) {
						$this->dropshipper_assignlog_model->edit($printing_status['id'], [
							'version'	=> (int)$change_products[$item['product_id']],
						]);
					}
				}
			}

			if (empty($json)) {
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

	public function dropshipper_bulk_order_update() {
		$json = [];

		$order_ids 	= $this->input->post('ids');
		$status 	= $this->input->post('status');

		if (in_array($status, [2, 3, 4, 8, 9, 10, 15, 21])) {
			foreach ($order_ids as $order_id) {
				$order_info = $this->order_model->get($order_id);

				$this->order_history_model->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> _order_history($status),
					'status' 		=> $status
				]);

				$this->order_model->edit($order_info['id'], [
					'status'			=> (int)$status,
					'date_completed'	=> date('Y-m-d H:i:s')
				]);

				if ($status === '8') {
					$this->load->model('dropshipper/DropshipperAssignlog_model', 'dropshipper_assignlog_model');

					$this->dropshipper_assignlog_model->editByOrderId($order_info['id'], [
						'status' 			=> 3,
						'date_printed'		=> date('Y-m-d H:i:s')
					]);

					$this->order_model->edit($order_info['id'], [
						'printing_status'	=> 1
					]);
				} elseif ($status === '4') {
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
			}
			$json['success'] = _li('order_updated_successfully');
		} else {
			$json['success'] = _li('invalid_order_status');
		}

		output_json($json);
	}
}
