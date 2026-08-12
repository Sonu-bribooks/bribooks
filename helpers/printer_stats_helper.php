<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('_get_printer_order_stats')) {
	function _get_printer_order_stats($option_type = '', $option = '') {
		$CI	=&	get_instance();

		$CI->load->model('printer/PrinterStats_model', 'printer_stats_model');

		$total_orders 			= $CI->printer_stats_model->quantityCount([
			'option_type'		=> $option_type,
			'option'			=> $option,
			'order_status_ne'	=> 0
		]) ?? 0;
		$balance 				= $CI->printer_stats_model->quantityCount([
			'option_type'		=> $option_type,
			'option'			=> $option,
			'order_status'		=> 1
		]) ?? 0;
		$total_orders_printed 	= $CI->printer_stats_model->quantityCount([
			'option_type'		=> $option_type,
			'option'			=> $option,
			'order_status_ge'	=> 2
		]) ?? 0;

		$total_under_printing 	= $CI->printer_stats_model->printerStats([
			'option'			=> $option,
			'status' 			=> 1,
		]) ?? 0;
		$total_under_printing 	+= $CI->printer_stats_model->printerStats([
			'option'			=> $option,
			'status' 			=> 2,
		]) ?? 0;
		$total_under_printing 	+= $CI->printer_stats_model->printerStats([
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

if (!function_exists('_get_printer_count_copies')) {
	function _get_printer_count_copies($option = '') {
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

if (!function_exists('_get_printer_wise_stats')) {
	function _get_printer_wise_stats($value = []) {
		if (empty($value['id']))
			return;

		$CI	=&	get_instance();

		$CI->load->model('printer/PrinterStats_model', 'printer_stats_model');
		$CI->load->model('order/ReprintOrder_model', 'reprint_order_model');

		$total_paperback = $CI->printer_stats_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback'
		]) ?? 0;
		$total_hardcover = $CI->printer_stats_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover'
		]) ?? 0;
		$total_blackwhite = $CI->printer_stats_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White'
		]) ?? 0;

		$today_paperback = $CI->printer_stats_model->todayData([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback'
		]) ?? 0;
		$today_hardcover = $CI->printer_stats_model->todayData([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover'
		]) ?? 0;
		$today_blackwhite = $CI->printer_stats_model->todayData([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White'
		]) ?? 0;

		$printed_paperback = $CI->printer_stats_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Paperback',
			'status' 			=> 3
		]) ?? 0;
		$printed_hardcover = $CI->printer_stats_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Hard Cover',
			'status' 			=> 3
		]) ?? 0;
		$printed_blackwhite = $CI->printer_stats_model->printerStats([
			'assign_printer_id' => $value['id'],
			'type' 				=> 'Black White',
			'status' 			=> 3
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
		];

		return $printers;
	}
}
