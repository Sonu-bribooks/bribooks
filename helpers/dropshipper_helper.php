<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('_get_dropshipper_order_stats')) {
	function _get_dropshipper_order_stats($option_type = '', $option = '') {
		$CI	=&	get_instance();

		$CI->load->model('dropshipper/DropshipperOrder_model', 'dropshipper_order_model');

		$total_orders 			= $CI->dropshipper_order_model->quantityCount([
			'option_type'		=> $option_type,
			'option'			=> $option,
			'order_status_ne'	=> 0
		]) ?? 0;

		$balance 				= $CI->dropshipper_order_model->quantityCount([
			'option_type'		=> $option_type,
			'option'			=> $option,
			'order_status'		=> 1
		]) ?? 0;

		$total_orders_printed 	= $CI->dropshipper_order_model->quantityCount([
			'option_type'		=> $option_type,
			'option'			=> $option,
			'order_status_ge'	=> 2
		]) ?? 0;

		$total_under_printing 	= $CI->dropshipper_order_model->printerStats([
			'option'			=> $option,
			'status' 			=> 1,
		]) ?? 0;
		$total_under_printing 	+= $CI->dropshipper_order_model->printerStats([
			'option'			=> $option,
			'status' 			=> 2,
		]) ?? 0;
		$total_under_printing 	+= $CI->dropshipper_order_model->printerStats([
			'option'			=> $option,
			'status' 			=> 4,
		]) ?? 0;

		$order_stats = [
			'total_orders' 			=> $total_orders,
			'total_under_printing' 	=> $total_under_printing,
			'total_orders_printed' 	=> $total_orders_printed,
			'balance' 				=> $balance,
		];

		return $order_stats;
	}
}

if (!function_exists('_get_dropshipper_count_copies')) {
	function _get_dropshipper_count_copies($option = '') {
		$CI	=&	get_instance();

		$CI->load->model('order/ReprintOrder_model', 'reprint_order_model');

		$total_orders 			= $CI->reprint_order_model->countCopies([
			'option'			=> $option,
			'status_ge'			=> 0,
		]) ?? 0;
		$total_orders_printed 	= $CI->reprint_order_model->countCopies([
			'option'			=> $option,
			'status'			=> 3
		]) ?? 0;
		$total_under_printing 	= $CI->reprint_order_model->countCopies([
			'option'			=> $option,
			'status' 			=> 1,
		]) ?? 0;
		$total_under_printing 	+= $CI->reprint_order_model->countCopies([
			'option'			=> $option,
			'status' 			=> 2,
		]) ?? 0;
		$total_under_printing 	+= $CI->reprint_order_model->countCopies([
			'option'			=> $option,
			'status' 			=> 4,
		]) ?? 0;

		$reprint_stats 	= [
			'total_orders' 			=> $total_orders,
			'total_under_printing' 	=> $total_under_printing,
			'total_orders_printed' 	=> $total_orders_printed,
			'balance' 				=> 0,
		];

		return $reprint_stats;
	}
}

if (!function_exists('_get_dropshipper_wise_stats')) {
	function _get_dropshipper_wise_stats($value = []) {
		if (empty($value['id'])) return;

		$CI	=&	get_instance();

		$CI->load->model('dropshipper/DropshipperOrder_model', 'dropshipper_order_model');
		$CI->load->model('order/ReprintOrder_model', 'reprint_order_model');

		$total_paperback = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback'
		]) ?? 0;
		$total_hardcover = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover'
		]) ?? 0;
		$total_blackwhite = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White'
		]) ?? 0;

		$today_paperback = $CI->dropshipper_order_model->todayData([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback'
		]) ?? 0;
		$today_hardcover = $CI->dropshipper_order_model->todayData([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover'
		]) ?? 0;
		$today_blackwhite = $CI->dropshipper_order_model->todayData([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White'
		]) ?? 0;

		$printed_paperback = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback',
			'status' 			=> 3
		]) ?? 0;
		$delivered_paperback = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback',
			'status' 			=> 4
		]) ?? 0;
		$printed_hardcover = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover',
			'status' 			=> 3
		]) ?? 0;
		$printed_blackwhite = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White',
			'status' 			=> 3
		]) ?? 0;
		$delivered_blackwhite = $CI->dropshipper_order_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White',
			'status' 			=> 4
		]) ?? 0;

		$reprint_today_paperback = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback',
			'date_added'		=> date('Y-m-d'),
		]) ?? 0;
		$reprint_today_hardcover = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover',
			'date_added'		=> date('Y-m-d'),
		]) ?? 0;
		$reprint_today_blackwhite = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White',
			'date_added'		=> date('Y-m-d'),
		]) ?? 0;

		$reprint_total_paperback = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback',
		]) ?? 0;
		$reprint_total_hardcover = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover',
		]) ?? 0;
		$reprint_total_blackwhite = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White',
		]) ?? 0;

		$reprint_printed_paperback = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback',
			'status' 			=> 3
		]) ?? 0;
		$reprint_printed_hardcover = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover',
			'status' 			=> 3
		]) ?? 0;
		$reprint_printed_blackwhite = $CI->reprint_order_model->countCopies([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White',
			'status' 			=> 3
		]) ?? 0;

		$printers = [
			'name' 		=> $value['first_name'],
			'id' 		=> $value['id'],
			'backlog' 	=> [
				'paperback' => $total_paperback - $today_paperback - $printed_paperback,
				'hardcover' => $total_hardcover - $today_hardcover - $printed_hardcover,
				'blackwhite' => $total_blackwhite - $today_blackwhite - $printed_blackwhite
			],
			'today' 	=> [
				'paperback' => $today_paperback,
				'hardcover' => $today_hardcover,
				'blackwhite' => $today_blackwhite,
				'reprint'	=> [
					'paperback' => $reprint_today_paperback,
					'hardcover' => $reprint_today_hardcover,
					'blackwhite' => $reprint_today_blackwhite
				]
			],
			'total' 	=> [
				'paperback' => $total_paperback,
				'hardcover' => $total_hardcover,
				'blackwhite' => $total_blackwhite,
				'reprint'	=> [
					'paperback' => $reprint_total_paperback,
					'hardcover' => $reprint_total_hardcover,
					'blackwhite' => $reprint_total_blackwhite
				]
			],
			'printed' 	=> [
				'paperback' => $printed_paperback,
				'hardcover' => $printed_hardcover,
				'blackwhite' => $printed_blackwhite,
				'reprint'	=> [
					'paperback' => $reprint_printed_paperback,
					'hardcover' => $reprint_printed_hardcover,
					'blackwhite' => $reprint_printed_blackwhite
				]
			],
			'delivered' 	=> [
				'paperback' => $delivered_paperback,
				'blackwhite' => $delivered_blackwhite,
			]
		];

		return $printers;
	}
}

if (!function_exists('_dosb')) {
	function _dosb($index) {
		$types = [
			0 => _li('incomplete'),
			1 => _li('processing'),
			2 => _li('in_print'),
			3 => _li('shipped'),
			4 => _li('order_completed'),
			8 =>  'QA/QC',
			9 => _li('ready_to_ship'),
			10 => _li('reprint'),
			15 => _li('returned'),
			21 => _li('available_for_shipping'),
			91 => _li('cancelled'),
			92 => _li('refunded'),
			93 => _li('escalated'),
			94 => _li('clone'),
		];
		$badges = [
			0 => 'dark',
			1 => 'warning',
			2 => 'info',
			3 => 'primary',
			4 => 'success',
			8 => 'dark',
			9 => 'secondary',
			10 => 'danger',
			15 => 'dark',
			21 => 'secondary',
			91 => 'dark',
			92 => 'dark',
			93 => 'dark',
			94 => 'dark',
		];

		return sprintf('<br><span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($types[$index] ?? ''));
	}
}

if (!function_exists('_o_dropshipper')) {
	function _o_dropshipper($order_info = [], $printer_info = [], $printer_assign_info = [], $option = 'paperback') {
		$CI	=&	get_instance();
		$CI->load->model('dropshipper/DropshipperAssignLog_model', 'dropshipper_assignlog_model');

		$printer_assign_results = $CI->dropshipper_assignlog_model->get_all([
			'order_id'	=> $order_info['id'],
			'option'	=> $option
		])['rows'] ?? [];

		foreach ($printer_assign_results as $printer_assign_info) {
			if (in_array($printer_assign_info['status'], [1, 2, 4])) {
				$order_info['printing_status'] = 0;
				continue;
			}
		}

		return $printer_info ? $printer_info['first_name'] . ' ' . $printer_info['last_name'] : 'NA';
	}
}

if (!function_exists('_dropshipper_op_name')) {
	function _dropshipper_op_name($products = [], $order_info = [], $order_type = 'printed') {
		$CI	=&	get_instance();
		$CI->load->model('book/BookStock_model', 'book_stock_model');
		$CI->load->model('book/PageVersion_model', 'page_version_model');
		$CI->load->model('book/Book_model', 'book_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('common/Site_model', 'site_model');

		$product_names = [];

		if (!empty($products)) {
			foreach ($products as $product) {
				$option  = json_decode($product['option'], 1);

				$stock_info = $CI->book_stock_model->get_all([
					'book_id'	=> $product['product_id'],
					'version'	=> $product['version'],
					'option'	=> !empty($option['name']) ? strtolower($option['name']) : 'paperback',
				])['rows'][0] ?? [];

				if ($order_type === 'printed' && strtolower($option['name']) === 'ebook') continue;
				if ($order_type === 'ebook' && strtolower($option['name']) !== 'ebook') continue;

				$total_pages 	= $CI->page_version_model->get_all([
					'book_id'	=> $product['product_id'],
					'version'	=> $product['version'],
				])['total'] ?? 0;

				$book_info 	= $CI->book_model->get($product['product_id']);

				$user_info 	= $CI->user_model->get($book_info['user_id']);

				$site_info 	= !empty($user_info['site_id']) ? $CI->site_model->get($user_info['site_id']) : '';

				if (empty($site_info['site_code']) && !empty($site_info['parent_id'])) {
					$site_info 	= $CI->site_model->get($site_info['parent_id']);
				}

				$is_author_copy = ($order_info['user_id'] == $product['user_id']) ? 'true' : 'false';

				$product_names[] = vsprintf('%s-v%s<br/>by %s<br/>Qty:: <b>%s</b><br/>SKU:: %s<br/>Src:: %s<br/>Pages::%s<br/><span class="%s">%s</span><br/>%s', [
					$product['name'],
					$product['version'],
					$product['author_name'],
					$product['quantity'],
					_o_b_code($product['product_id'], $product['version'], $option['name']),
					$site_info['site_code'] ?? '',
					$total_pages * 2 + 1,
					strtolower($option['name']) == 'paperback' ? 'text-success' : 'text-danger',
					$option['name'],
					''
				]);
			}
		}

		return implode('<br><br>', $product_names);
	}
}

if (!function_exists('_co_action_btn')) {
	function _co_action_btn($result=[], $status = 0, $inprint_orders = 0) {
		$actions = '';

		switch ($status) {
			case 1:
				$actions = !empty($inprint_orders == 0)
					? sprintf('<button type="button" class="btn btn-primary btn-sm send_in_print" data-id="%s">Send to Inprint</button>', $result['id'])
					: '<span class="badge badge-secondary">First Clear Your In Print Tab Of B&W Order</span>';
				break;
			case 8:
				$actions = vsprintf('<div class="custom-control custom-switch custom-switch-lg"><input type="checkbox"  value="0" id="qaqc"%d" class="custom-control-input qaqc-btn" data-id="%d"/><label class="custom-control-label" for="qaqc"%d">QA/QC</label></div>', [
					$result['id'],
					$result['id'],
					$result['id'],
				]);
				break;
			case 21:
				$actions = sprintf('<button type="button" class="btn btn-danger btn-sm shipbtn" data-id="%d">Ship</button>', $result['id']);
				break;
			case 9:
				$actions = vsprintf('
					<a href="%s" class="btn btn-info mt-2" id="download_manifest">%s</a><br>
					<a href="%s" class="btn btn-primary mt-2" id="download_label">%s</a><br>
					<a href="%s" class="btn btn-warning mt-2" id="download_invoice">%s</a>',
					[
						base_url('dropShipper/download_manifest/' . $result['id']),
						_l('download_manifest'),
						base_url('dropShipper/download_label/' . $result['id']),
						_l('download_label'),
						base_url('dropShipper/download_invoice/' . $result['id']),
						_l('download_invoice')
					]
				);
				break;
		}

		return $actions;
	}
}

if (!function_exists('_action_btn')) {
	function _action_btn($result=[], $status = 0) {
		$actions = '';

		switch ($status) {
			case 1:
				$actions = '<button type="button" class="btn btn-primary btn-sm send_in_print" data-id="' . $result['ids'] . '" data-orderid="' . $result['order_ids'] . '">Send to Inprint</button>';
				break;
			case 2:
				$actions = '<button type="button" class="btn btn-primary btn-sm send_verify_print" data-id="' . $result['ids'] . '" data-orderid="' . $result['order_ids'] . '">Send to QA/QC</button>';
				break;
			case 21:
				$actions = '<button type="button" class="btn btn-danger btn-sm shipnowbtn" data-product="' . $result['product_id'] . '" data-id="' . $result['order_ids'] . '">Ship</button>';
				break;
		}

		return $actions;
	}
}

if (!function_exists('_dropshipper_role')) {
	function _dropshipper_role() {
		$CI	=&	get_instance();

		$CI->load->model('user/Role_model', 'role_model');

		$role_id = $CI->role_model->get_all([
			'name' => 'dropshipper'
		])['rows'][0]['id'] ?? 0;

		return  $role_id;
	}
}
