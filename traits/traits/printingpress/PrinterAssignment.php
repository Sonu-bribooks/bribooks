<?php defined('BASEPATH') or exit('No direct script access allowed');

trait PrinterAssignment {
	public function printer_assignment($param1 = '', $param2 = '') {
		if ($this->session->userdata('printingPress') != true || !in_array($this->session->userdata('role_id'), [12,15])) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 			= 'printer_assignment/index';
		$data['page_title'] 		= _l('printer_assignment');
		$data['action_ajax'] 		= site_url('printingPress/ajax_printer_assignment');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_printer_assignment() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$filter_data['printer_id'] = (int)$this->session->userdata('user_id');

		$results = $this->printer_assignment_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$printer_info = $this->user_model->get($result['printer_id']);

			$stats = '';
			if (in_array($this->session->userdata('role_id'), [12,15])) {
				$total_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id']
				]) ?? 0;

				$new_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 1,
				]) ?? 0;

				$in_print_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 2,
				]) ?? 0;

				$verify_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 4,
				]) ?? 0;

				$printed_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 3,
				]) ?? 0;

				$qa_qc_lots_info = $this->printer_stats_model->getQaQcAssignCount([
					'assignment_id'		=> $result['id'],
				]);

				$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;

				$accepted_count += $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

				$rejected_count = $qa_qc_lots_info['rejected_quantity'] ?? 0;

				$balance_count = (int)$total_count-(int)$accepted_count-(int)$rejected_count;

				$stats = vsprintf('
					<span class="badge badge-secondary">%s total</span><br>
					<span class="badge badge-info">%s new</span><br>
					<span class="badge badge-warning">%s in print</span><br>
					<span class="badge badge-danger">%s verify print</span><br>
					<span class="badge badge-success">%s printed</span><br>
					<span class="badge badge-success-lighten">%s accepted</span><br>
					<span class="badge badge-danger">%s rejected</span><br>
					<span class="badge badge-danger-lighten">%s balance</span>
				', [
					$total_count,
					$new_orders_count,
					$in_print_orders_count,
					$verify_orders_count,
					$printed_orders_count,
					$accepted_count,
					$rejected_count,
					$balance_count,
				]);
			}

			$json['data'][] = [
				'sn'			=> $filter_data['start'] + 1 + $key,
				'id'			=> $result['id'],
				'printer'		=> sprintf('%s %s', $printer_info['first_name'] ?? '',  $printer_info['last_name'] ?? ''),
				'assignment_code'=> $result['code'] . vsprintf('<br /><a target="_blank" href="%s" title="View Book Titles">View Book Titles</a>', [base_url('printingPress/book_titles/'.$result['code'])]),
				'stats'				=> $stats,
				'assignment_date'=> formatDate($result['date_added']),
				'actions'		=> [
					'id' 		=> $result['id'],
				],
			];
		}

		output_json($json);
	}

	public function export_assignment_by_filter() {
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$json = [];

		if ($this->input->get('printer_id')) {
			$filter_data['printer_id'] = (int)$this->input->get('printer_id');
		}

		$results = $this->printer_assign_log_model->get_all($filter_data)['rows'] ?? [];

		$sn = 1;

		$printer_assignments = $sort_order = [];

		foreach ($results as $result) {
			$printer_info = $this->user_model->get($result['printer_id']);
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);
			$order_info = $this->order_model->get($result['order_id']);

			if (empty($order_info)) continue;

			$assignment_info = $this->printer_assignment_model->get($result['assignment_id']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$option = json_decode($result['option'], true);

			$order_product_info = $this->order_product_model->get_all([
				'order_id'		=> $result['order_id'],
				'product_id'	=> $result['product_id'],
			])['rows'][0] ?? [];

			$printer_assignments[] = [
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
				'cost_per_page'		=> sprintf('%s %s', $order_info['currency_code'], round($order_product_info['total'] / ($order_product_info['quantity'] * ($total_pages * 2 + 1)), 2)),
				'printer'			=> sprintf('%s %s', $printer_info['first_name'] ?? '', $printer_info['last_name'] ?? ''),
				'status'			=> strip_tags(_printer_status($result['status'])),
				'assignment_date'	=> formatDate($result['date_added']),
			];

			$sort_order[] = $book_info['name'];

			$sn++;
		}

		array_multisort($sort_order, $printer_assignments);

		self::_downloadCsv($printer_assignments, 'printer_assignments' . '_' . (int)$assignment_id . '_');

		output_json($json);
	}

	public function export_assignment_csv($assignment_id = 0) {
		if (empty($assignment_id)) return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$assignment_info = $this->printer_assignment_model->get($assignment_id);

		$json = [];

		$filter_data['assignment_id'] = (int)$assignment_id;

		$results = $this->printer_assign_log_model->get_all($filter_data)['rows'] ?? [];

		$sn = 1;

		$printer_assignments = $sort_order = [];

		foreach ($results as $result) {
			$printer_info = $this->user_model->get($result['printer_id']);
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);
			$order_info = $this->order_model->get($result['order_id']);

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

			$printer_assignments[] = [
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
				'cost_per_page'		=> sprintf('%s %s', $order_info['currency_code'], round($order_product_info['total'] / ($order_product_info['quantity'] * ($total_pages * 2 + 1)), 2)),
				'printer'			=> sprintf('%s %s', $printer_info['first_name'] ?? '', $printer_info['last_name'] ?? ''),
				'status'			=> strip_tags(_printer_status($result['status'])),
				'assignment_date'	=> formatDate($result['date_added']),
			];

			$sort_order[] = $book_info['name'];

			$sn++;
		}

		array_multisort($sort_order, $printer_assignments);

		self::_downloadCsv($printer_assignments, 'printer_assignments' . '_' . (int)$assignment_id . '_');

		output_json($json);
	}

	public function export_printer_csv_by_assignment($assignment_id = 0) {
		if (empty($assignment_id)) return;

		$products = $this->printer_stats_model->printerAssignData([
			'assignment_id'		=> $assignment_id,
		])['rows'] ?? [];

		self::_genBookZipPdfsCsv($products, 'printer_assignments' . '_' . (int)$assignment_id . '_');
	}

	public function export_printer_csv_po_by_assignment($assignment_id = 0) {
		if (empty($assignment_id))
			return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('printer/PrinterCosting_model', 'printer_costing_model');

		$assignment_info = $this->printer_assignment_model->get($assignment_id);
		if (empty($assignment_info))
			return;

		$printer_costing_info = $this->printer_costing_model->getByPrinterId($assignment_info['printer_id']);
		/*if (empty($printer_costing_info))
			return;*/

		$json = [];

		$filter_data = [];
		$filter_data['assignment_id'] = $assignment_info['id'];
		$filter_data['assign_printer_id'] = $assignment_info['printer_id'];

		$results = $this->printer_stats_model->printerAssignData($filter_data);

		$printer_po = [];

		foreach ($results['rows'] ?? '' as $result) {
			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$page = 0;
			$pages = ($total_pages * 2 + 1);
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

			$pages = $pages + $page + $printer_costing_info['add_cover_pages_per_book'];

			$pages_price = $pages * ($printer_costing_info['rate_per_page'] ?? 0);
			$binding_price = $printer_costing_info['binding_cost_per_book'] ?? 0;

			$unit_price = $pages_price + $binding_price;

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
}
