<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('_mourl')) {
	function _mourl($key, $value) {
		$CI	=&	get_instance();
		$role_url = $CI->session->userdata('role_id') == 4 ? 'telecaller' : 'admin';
		return vsprintf('<a href="%s%s/medallion_order_details/%s" target="_blank">%s</a>', [
			base_url(),
			$role_url,
			$key,
			$value,
		]);
	}
}

if (!function_exists('_medallion_order_code')) {
	function _medallion_order_code($result = [], $shipping_tracking_info = []) {
		$CI	=&	get_instance();
		$CI->load->model('address/Address_model', 'address_model');
		$CI->load->library('BriBooksShipping_lib', 'bribooksshipping_lib');

		$shipping_info = json_decode($result['shipping_info'], true);

		$implode = [];

		if (!empty($shipping_info['courier_name']) &&  $shipping_info['courier_name'] == 'BriBooks Speed Shipping') {
			$implode[] = _ad(3, 'magenta');
		}

		$current_order_status = $result['status'];

		$implode[] = _sd($result['shipping_status']);

		if ($result['currency_code'] !== 'INR') {
			$implode[] = _ad($result['currency_code'] !== 'INR' ? 2 : 0);
		}

		$implode[] = '<br>' . _mourl($result['id'], $result['order_code']);
		$implode[] = sprintf('<i class="fa fa-copy text-info" data-copy="%s" data-toggle="tooltip" data-placement="top" title="" data-original-title="copy"></i><br>', $result['order_code']);

		$implode[] = _osb($current_order_status);

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

		if (
			!empty($shipping_info['courier_name']) &&
			in_array(strtolower($shipping_info['courier_name']), ['youbooks flat shipping', 'youbooks free shipping'])
		) {
			$implode[] = vsprintf(
				'<br><a target="_blank" href="https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx">%s</a>', [
					_l('go_to_indiapost')
			]);
		}

		$address_info = $CI->address_model->getByID($result['address_id']);

		$order_type = (strtolower($address_info['country']) === 'india') ? '' : 'international';

		if (empty(allow_bb_shipping_module($order_type)) && !empty($shipping_tracking_info['order_id'])) {
			$implode[] = vsprintf(
				'<br><a target="_blank" href="https://app.shiprocket.in/seller/orders/details/%s">%s</a>', [
				$shipping_tracking_info['order_id'],
				_l('go_to_shiprocket')
			]);
		}

		return implode('', $implode);
	}
}

if (!function_exists('_moa_buttons')) {
	function _moa_buttons($order_info = [], $shipping_tracking_info = []) {
		$CI	=&	get_instance();

		$buttons = [];

		$CI->load->model('address/Address_model', 'address_model');

		$address_info = $CI->address_model->getByID($order_info['address_id']);

		$type = (strtolower($address_info['country']) === 'india') ? '' : 'international';

		if ($order_info['status'] == 21) {
			if (get_settings('bb_shipping') && empty($order_info['shipping_status'])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm shipnowbtn" data-type="%s" data-id="%s">%s</button>', [
					'medallion',
					$order_info['id'],
					_l('ship'),
				]);
			} else {
				if (!empty($order_info['shipping_status'])) {
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
		}

		if ($order_info['shipping_status'] == 1 && in_array($order_info['status'], [4, 9, 15])) {
			$shipping_info = json_decode($order_info['shipping_info'], true);

			if (!empty($shipping_info['bb_shipment_id'])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm generate-singlelabel" data-type="medallion" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('label'),
				]);
				$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm generate-invoice" data-type="medallion" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('invoice'),
				]);
				$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm generate-manifest" data-type="medallion" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('manifest'),
				]);
			}
		}

		if (!in_array($order_info['status'], [0, 91, 92])) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm btn-comment" data-toggle="modal" data-target="#commentModal" data-id="%s">%s</button>', [
				$order_info['id'],
				_l('comment'),
			]);
		}

		if (in_array($order_info['status'], [1])) {
			$buttons[] = vsprintf('<a href="%s" class="btn btn-info btn-sm">%s</a>', [
				base_url('admin/medallion_orders_form/edit/' . $order_info['id']),
				_l('edit'),
			]);
		}

		// if (empty(allow_bb_shipping_module($type)) && !in_array($order_info['status'], [0, 1, 91, 92, 93, 94]) && empty($order_info['shipping_status'])) {
		// 	$buttons[] = vsprintf('<button type="button" name="sync_order" class="btn btn-danger btn-sm sync-order" data-id="%s" data-type="%s">%s</button>', [
		// 		$order_info['id'],
		// 		$order_info['type'],
		// 		_l('sync'),
		// 	]);
		// }

		if ($order_info['date_added'] > date('Y-m-d 23:59:59', strtotime('-120 days')) && !in_array($order_info['status'], [0,91,92,93,94])) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm btn-cancel" data-toggle="modal" data-target="#cancelModal" data-id="%s">%s</button>', [
				$order_info['id'],
				_l('cancel'),
			]);
		}

		if (
			!in_array($order_info['status'], [0, 91, 92, 94])
		) {
			if (!in_array($order_info['status'], [93])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm btn-escalate" data-toggle="modal" data-target="#escalateModal" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('escalate'),
				]);
			} elseif (in_array($order_info['status'], [93])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm btn-escalate-restore" data-toggle="modal" data-target="#escalateRestoreModal" data-id="%s">%s</button>', [
					$order_info['id'],
					_l('restore'),
				]);
			}
		}

		if ($order_info['currency_code'] !== 'INR' && in_array($order_info['status'], [2, 8, 21])) {
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
