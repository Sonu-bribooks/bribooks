<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_option_type')) {
	function get_option_type($type = 'printed') {
		$options = [
			'ebook'			=> 0,
			'printed'		=> 1,
			'black_white'	=> 2,
			'audio_book'	=> 3
		];

		return $options[strtolower($type)] ?? 1;
	}
}

if (!function_exists('_oa_buttons')) {
	function _oa_buttons($order_info = [], $products = [], $shipping_tracking_info = [], $assignment_code = '') {
		if ($order_info['order_type'] != 3) {
			$CI	=&	get_instance();

			$buttons = [];

			$CI->load->model('address/Address_model', 'address_model');

			$address_info = $CI->address_model->getByID($order_info['address_id']);

			$type = (strtolower($address_info['country'] ?? '') === 'india') ? '' : 'international';

			if (
				empty($order_info['parent_order_id']) &&
				!in_array($order_info['status'], [0, 2, 3, 8, 91, 92])
			) {
				$buttons[] = vsprintf('<a href="%s" class="btn btn-secondary btn-sm" target="_blank">%s</a>', [
					base_url('admin/order_clone/' . $order_info['id']),
					_l('clone'),
				]);
			}

			if ($order_info['status'] == 21) {
				if (get_settings('bb_shipping') && empty($order_info['shipping_status'])) {
					$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm shipnowbtn" data-id="%s">%s</button>', [
						$order_info['id'],
						_l('ship'),
					]);
				} else {
					if (empty($shipping_tracking_info['awb_code'])) {
						$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm btn-fetchawb" data-id="%s">%s</button>', [
							$order_info['id'],
							_l('fetch_awb'),
						]);
					} else {
						$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm btn-readyship" data-id="%s">%s</button>', [
							$order_info['id'],
							_l('ready_to_ship'),
						]);
					}
				}
			}

			if (get_settings('bb_shipping') && $order_info['shipping_status'] == 1 && in_array($order_info['status'], [4, 9, 15])) {
				$shipping_info = json_decode($order_info['shipping_info'], true);

				if (!empty($shipping_info['bb_shipment_id'])) {
					$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm generate-singlelabel" data-id="%s">%s</button>', [
						$order_info['id'],
						_l('label'),
					]);
					$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm generate-invoice" data-id="%s">%s</button>', [
						$order_info['id'],
						_l('invoice'),
					]);
					$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm generate-manifest" data-id="%s">%s</button>', [
						$order_info['id'],
						_l('manifest'),
					]);
				}
			}

			if (!in_array($order_info['status'], [0, 91, 92])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm btn-hold" data-toggle="modal" data-target="#holdModal" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('comment'),
				]);
			}

			if (!get_settings('bb_shipping') && !in_array($order_info['status'], [0, 91, 92, 93, 94]) && empty($order_info['shipping_status'])) {
				$buttons[] = vsprintf('<button type="button" name="sync_order" class="btn btn-danger btn-sm sync-order" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('sync'),
				]);
			}

			if (!in_array($order_info['status'], [0, 91, 92, 93, 94])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm btn-reprint" data-toggle="modal" data-target="#reprintModal" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('reprint'),
				]);
			}

			if (empty($order_info['printing_status']) && $assignment_code) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm btn-rollback" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('rollback'),
				]);
			}

			if ($order_info['date_added'] > date('Y-m-d 23:59:59', strtotime('-120 days')) && !in_array($order_info['status'], [0,91,92,93,94])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm btn-cancel" data-toggle="modal" data-target="#cancelModal" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('cancel'),
				]);
			} elseif (in_array($order_info['status'], [91])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm btn-refund" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('refund'),
				]);
			}

			if (!in_array($order_info['status'], [0, 91, 92, 94])) {
				if (!in_array($order_info['status'], [93])) {
					$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm btn-escalate" data-toggle="modal" data-target="#escalateModal" data-id="%s">%s</button>', [
						$order_info['id'],
						_l('escalate'),
					]);
				} else if (in_array($order_info['status'], [93])) {
					$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm btn-escalate-restore" data-toggle="modal" data-target="#escalateRestoreModal" data-id="%s">%s</button>', [
						$order_info['id'],
						_l('restore'),
					]);
				}
			}

			if ($order_info['status'] < 3 && empty($order_info['printing_status'])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-dark btn-sm change-order-version" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('change_version'),
				]);
			}

			if ($order_info['currency_code'] !== 'INR' && in_array($order_info['status'], [2,8,21])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm btn-awb-assign" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('assign_awb'),
				]);
			}

			if ($order_info['currency_code'] !== 'INR' && $order_info['status'] == 9) {
				$buttons[] = vsprintf('<a href="%s">%s</a>', [
					base_url('admin/download_manifest/' . $order_info['id']),
					_l('download_label'),
				]);
			}

			return implode('<p style="margin-bottom: 0.4rem;"></p>', $buttons);
		}
	}
}

if (!function_exists('_qa_qc_rb_buttons')) {
	function _qa_qc_rb_buttons($order_info = [], $qa_qc_lots_info = []) {
		$CI	=&	get_instance();
		$CI->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');

		$order_assign_log_info = $CI->printer_assign_log_model->get($order_info['ids']);

		if (in_array($order_assign_log_info['status'], [1, 2])) return;

		$buttons = [];

		$type = strtolower(json_decode($order_info['option'], 1)['name']);

		$accepted_count 	= $qa_qc_lots_info['accepted_quantity'] ?? 0;
		$accepted_count 	+= $qa_qc_lots_info['accepted_short_quantity'] ?? 0;
		$rejected_count 	= $qa_qc_lots_info['rejected_quantity'] ?? 0;
		$balance_count 		= (int)$order_info['quantity']-(int)$accepted_count;

		if ($balance_count > 0) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm btn-qaqc" data-toggle="modal" data-target="#qaqcModal" data-id="%s" data-version="%s" data-option="%s">%s</button>', [
				$order_info['id'],
				$order_info['version'],
				$type,
				'QA QC',
			]);
		}

		if (!empty($rejected_count)) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm btn-qaqc-reset-rejected-count" data-id="%s" data-version="%s" data-option="%s">%s</button>', [
				$order_info['id'],
				$order_info['version'],
				$type,
				'Reset Rejected Count',
			]);
		}

		/*if ($accepted_count > 0) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm btn-qaqc-reject" data-toggle="modal" data-target="#qaqcRejectModal" data-id="%s" data-version="%s" data-option="%s">%s</button>', [
				$order_info['id'],
				$order_info['version'],
				$type,
				'Reject QA QC',
			]);
		}*/

		/*if (0 && !empty($qa_qc_lots_info)) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm btn-qaqc-reset" data-id="%s" data-version="%s" data-option="%s">%s</button>', [
				$order_info['id'],
				$order_info['version'],
				$type,
				'Reset',
			]);
		}*/

		return implode('<p style="margin-bottom: 0.4rem;"></p>', $buttons);
	}
}

if (!function_exists('_op_name')) {
	function _op_name($products = [], $order_info = [], $order_type = 'printed') {
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

				$stock_quantity = '';

				$stock_quantity .= sprintf('<span class="badge badge-%s">Stock- %s</span>',
					($stock_info['quantity'] ?? 0) > 0
						? 'success'
						: 'danger',
					$stock_info['quantity'] ?? 0,
				);

				if ($order_type === 'printed' && ((strtolower($option['name']) === 'ebook') || (strtolower($option['name']) === 'audio book'))) continue;
				if ($order_type === 'ebook' && (!in_array(strtolower($option['name']), ['ebook', 'audio book']))) continue;

				// if (!empty($stock_info['quantity_hold']) && $order_info['status'] == 8) {
				// 	$stock_quantity .= sprintf('<span class="badge badge-warning">Stock Hold- %s</span>', $stock_info['quantity_hold']);
				// }

				$total_pages 	= $CI->page_version_model->get_all([
					'book_id'	=> $product['product_id'],
					'version'	=> $product['version'],
				])['total'] ?? 0;

				$book_info 	= $CI->book_model->get($product['product_id']);

				$user_info 	= $CI->user_model->get($book_info['user_id'] ?? '');

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
					$stock_quantity
				]);
			}
		}

		return implode('<br><br>', $product_names);
	}
}

if (!function_exists('_o_printer')) {
	function _o_printer($order_info = [], $printer_info = [], $printer_assign_info = [], $option = 'paperback') {
		$CI	=&	get_instance();
		$CI->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');

		$printer_assign_results = $CI->printer_assign_log_model->get_all([
			'order_id'	=> $order_info['id'],
			'option'	=> $option
		])['rows'] ?? [];

		foreach ($printer_assign_results as $printer_assign_info) {
			if (in_array($printer_assign_info['status'], [1,2,4])) {
				$order_info['printing_status'] = 0;
				continue;
			}
		}

		return !empty($printer_assign_info) ? vsprintf('<b>%s</b><br><badge class="badge badge-%s">%s</badge><br> %s:: %s <br> %s:: %s', [
			$order_info['assign_printer_id'] ? $printer_info['first_name'] . ' ' . $printer_info['last_name'] : 'NA',
			$order_info['printing_status'] ? 'success' : 'danger',
			$order_info['printing_status'] ? _l('printed') : _l('not_printed'),
			_l('assignment_date'),
			!empty($printer_assign_info['date_added']) ? date('M j, Y', strtotime($printer_assign_info['date_added'])) : '',
			_li('ID'),
			$printer_assign_info['assignment_id'] ?? '',
		]) : 'NA';
	}
}

if (!function_exists('_os')) {
	function _os($index) {
		$types = array_flip(ORDER_STATUS);

		return _li($types[$index] ?? '');
	}
}

if (!function_exists('_osb')) {
	function _osb($index) {
		$types = array_flip(ORDER_STATUS);

		return sprintf('<br><span class="badge badge-%s">%s</span>', (ORDER_STATUS_COLOR[$index] ?? ''), _li($types[$index] ?? ''));
	}
}

if (!function_exists('_order_status')) {
	function _order_status($key) {
		$types = [
			0 => _l('book_is_sent_for_printing'),
			1 => _l('your_order_is_shipped'),
			2 => _l('order_completed'),
		];

		return $types[$key] ?? '';
	}
}

if (!function_exists('_order_history')) {
	function _order_history($key) {
		$types = [
			2 => _li('book_is_sent_for_printing'),
			3 => _li('your_order_is_shipped'),
			4 => _li('order_completed'),
			8 => _li('book_is_printed'),
			9 => _li('your_order_is_ready_to_ship'),
			10 => _li('book_is_reprinted'),
			15 => _li('your_order_is_returned'),
			19 => _li('your_order_is_out_for_delivery'),
			20 => _li('your_order_is_undelivered'),
			21 => _li('your_order_is_available_for_shipping'),
			91 => _li('your_order_is_cancelled'),
			92 => _li('your_order_is_refunded'),
			93 => _li('your_order_is_escalated'),
			94 => _li('your_order_is_cloned'),
		];

		return $types[$key] ?? '';
	}
}

if (!function_exists('_printer_status')) {
	function _printer_status($index) {
		$statuses = [
			1 => _l('new'),
			2 => _l('in_print'),
			3 => _l('printed'),
			4 => _l('in_verify'),
		];
		$badges = [
			1 => 'info',
			2 => 'warning',
			3 => 'success',
			4 => 'danger',
		];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_order_code')) {
	function _order_code($result = [], $shipping_tracking_info = [], $type = 'printed', $option = 'paperback') {
		$CI	=&	get_instance();
		$CI->load->model('printer/PrinterAssignRollback_model', 'printer_assign_rollback_model');
		$CI->load->model('address/Address_model', 'address_model');
		$CI->load->model('order/OrderClone_model', 'order_clone_model');
		$CI->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');
		$CI->load->library('BriBooksShipping_lib', 'bribooksshipping_lib');

		$shipping_info = json_decode($result['shipping_info'], true);

		$printer_assign_rollback_info = '';

		if ($result['status'] === 1) {
			$printer_assign_rollback_info = $CI->printer_assign_rollback_model->getByOrderId($result['id']);
			$printer_assign_rollback_info = !empty($printer_assign_rollback_info) ? ' <span class="badge badge-secondary">(Rollback)</span>' : '';
		}

		$implode = [];

		if (!empty($shipping_info['courier_name']) &&  $shipping_info['courier_name'] == 'BriBooks Speed Shipping') {
			$implode[] = _ad(3, 'magenta');
		}

		$current_order_status = $result['status'];

		/*$printer_assign_results = $CI->printer_assign_log_model->get_all([
			'order_id'	=> $result['id'],
			'option'	=> $option
		])['rows'] ?? [];

		foreach ($printer_assign_results as $printer_assign_info) {
			if (in_array($printer_assign_info['status'], [1,2,4])) {
				$current_order_status = 2;
			}
		}*/

		$implode[] = _sd($result['shipping_status']);

		if ($result['currency_code'] !== 'INR') {
			$implode[] = _ad($result['currency_code'] !== 'INR' ? 2 : 0);
		}

		if (_is_option_type_exist($result['id'], 2)) {
			$implode[] = _ad(3, 'black');
		}

		$implode[] = '<br>'._ourl($result['id'], $result['order_code']);
		$implode[] = sprintf('<i class="fa fa-copy text-info" data-copy="%s" data-toggle="tooltip" data-placement="top" title="" data-original-title="copy"></i><br>', $result['order_code']);
		$implode[] = _ourl($result['id'], $result['ext_transaction_id']);
		$implode[] = sprintf('<i class="fa fa-copy text-info" data-copy="%s" data-toggle="tooltip" data-placement="top" title="" data-original-title="copy"></i>', $result['ext_transaction_id']);

		if ($type === 'printed') {
			$implode[] = _osb($current_order_status) . $printer_assign_rollback_info;

			// if (!empty($shipping_tracking_info['awb_code'])) {
			// 	$implode[] = sprintf(
			// 		'<br>%s: <a href="https://shiprocket.co/tracking/%s" target="_blank">%s</a>',
			// 		_l('awb'),
			// 		$shipping_tracking_info['awb_code'] ?? '',
			// 		$shipping_tracking_info['awb_code'] ?? ''
			// 	);
			// }

			if (!empty($shipping_info['bb_shipment_id'])) {
				$implode[] = sprintf(
					'<br>%s: <a href="%s" target="_blank">%s</a>',
					_l('awb'),
					$CI->bribooksshipping_lib->trackingUrl($shipping_info['bb_shipment_id'] ?? ''),
					$shipping_tracking_info['awb_code'] ?? ''
				);
			}

			if (!empty($order_clone_result = $CI->order_clone_model->getByIds([
				'parent_order_id' => $result['id']
			]))) {
				foreach ($order_clone_result as $order_clone_info) {
					if (!empty($clone_order_info = $CI->order_model->get($order_clone_info['clone_order_id']))) {
						$current_clone_order_status = $clone_order_info['status'];

						/*$printer_assign_results = $CI->printer_assign_log_model->get_all([
							'order_id'	=> $order_clone_info['clone_order_id'],
							'option'	=> $option
						])['rows'];

						foreach ($printer_assign_results as $printer_assign_info) {
							if (in_array($printer_assign_info['status'], [1,2,4])) {
								$current_clone_order_status = 2;
							}
						}*/

						$implode[] = '<br>'. _ourl($clone_order_info['id'], $clone_order_info['order_code']);
						$implode[] = _osb($current_clone_order_status);
					}
				}
			}

			if (
				!empty($shipping_info['courier_name']) &&
				in_array(strtolower($shipping_info['courier_name']), ['youbooks flat shipping', 'youbooks free shipping'])
			) {
				$implode[] = vsprintf(
					'<br><a target="_blank" href="https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx">%s</a>', [
						_l('go_to_indiapost')
				]);
			}

			$address_info = $CI->address_model->getByID($result['address_id'] ?? '');

			$order_type = (strtolower($address_info['country'] ?? '') === 'india') ? '' : 'international';

			if (empty(allow_bb_shipping_module($order_type)) && !empty($shipping_tracking_info['order_id'])) {
				$implode[] = vsprintf(
					'<br><a target="_blank" href="https://app.shiprocket.in/seller/orders/details/%s">%s</a>', [
					$shipping_tracking_info['order_id'],
					_l('go_to_shiprocket')
				]);
			}
		}

		return implode('', $implode);
	}
}

if (!function_exists('_is_option_type_exist')) {
	function _is_option_type_exist($order_id = 0, $option_type = 1) {
		if (empty($order_id))
			return false;

		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('order_product', [
			'order_id'		=> $order_id,
			'option_type'	=> $option_type
		])->result_array()) {
			return true;
		}

		return false;
	}
}

if (!function_exists('_ourl')) {
	function _ourl($key, $value) {
		return vsprintf('<a href="%s/order_details/%s" target="_blank">%s</a>', [
			base_url('admin'),
			$key,
			$value,
		]);
	}
}

if (!function_exists('_o_b_code')) {
	function _o_b_code($book_id, $version, $option) {
		return vsprintf('%sV%s%s', [
			$book_id,
			$version,
			substr(mb_strtoupper($option), 0, 1)
		]);
	}
}

if (!function_exists('_order_option_type')) {
	function _order_option_type($index) {
		$types = [
			0 => _li('e-book'),
			1 => _li('coloured_printed_copy'),
			2 => _li('b_w_printed_copy'),
			3 => _li('audioBook'),
		];

		return $types[$index] ?? '';
	}
}
