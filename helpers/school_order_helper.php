<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('__schoolmourl')) {
	function __schoolmourl($key, $value) {
		$CI	=&	get_instance();
		$role_url = $CI->session->userdata('role_id') == 4 ? 'telecaller' : 'admin';
		return vsprintf('<a href="%s%s/school_order_details/%s" target="_blank">%s</a>', [
			base_url(),
			$role_url,
			$key,
			$value,
		]);
	}
}

if (!function_exists('_school_order_code')) {
	function _school_order_code($result = [], $shipping_tracking_info = []) {
		$CI	=&	get_instance();
		$CI->load->model('common/Site_model', 'site_model');
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

		$implode[] = '<br>' . __schoolmourl($result['id'], $result['order_code']);
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

		$site_info = $CI->site_model->get($result['site_id']);

		$order_type = isset($site_info['country_code']) ? (strtolower($site_info['country_code']) === 'in') ? '' : 'international' : '';

		if (empty(allow_bb_shipping_module($order_type)) && !empty($shipping_tracking_info['order_id'])) {
			if (isset($shipping_info['courier_name']) && (in_array($shipping_info['courier_name'], DTDC_COURIERS))) {
				$implode[] = vsprintf(
					'<br><a target="_blank" href="https://www.dtdc.in/tracking.asp">%s</a>', [
					_l('go_to_dtdc')
				]);
			} else {
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

if (!function_exists('_so_buttons')) {
	function _so_buttons($order_info = [], $shipping_tracking_info = []) {
		$CI	=&	get_instance();

		$buttons = [];

		$CI->load->model('common/Site_model', 'site_model');

		$site_info = $CI->site_model->get($order_info['site_id']);

		$type = isset($site_info['country_code']) ? (strtolower($site_info['country_code']) === 'in') ? 'india' : 'international' : '';

		if ($order_info['status'] == 21) {
			if(get_settings('bb_shipping') && empty($order_info['shipping_status'])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-danger btn-sm shipnowbtn" data-type="%s" data-id="%s">%s</button>', [
					'school',
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
				$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm generate-singlelabel" data-id="%s" data-type="school">%s</button>', [
					$order_info['id'],
					_l('label'),
				]);
				$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm generate-invoice" data-id="%s" data-type="school">%s</button>', [
					$order_info['id'],
					_l('invoice'),
				]);
				// $buttons[] = vsprintf('<button type="button" class="btn btn-primary btn-sm generate-manifest" data-id="%s">%s</button>', [
				// 	$order_info['id'],
				// 	_l('manifest'),
				// ]);
			}
		}

		$buttons[] = vsprintf('<a href="/admin/school_address_download/%s" class="btn btn-primary btn-sm">%s</a>', [
			$order_info['id'],
			_l('school_address'),
		]);

		if (!in_array($order_info['status'], [0, 91, 92])) {
			$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm btn-comment" data-toggle="modal" data-target="#commentModal" data-id="%s">%s</button>', [
				$order_info['id'],
				_l('comment'),
			]);
		}

		if (in_array($order_info['status'], [1])) {
			$buttons[] = vsprintf('<a href="%s" class="btn btn-info btn-sm">%s</a>', [
				base_url('admin/school_orders_form/edit/' . $order_info['id']),
				_l('edit'),
			]);
		}

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

		return implode('<p style="margin-bottom: 0.4rem;"></p>', $buttons);
	}
}
