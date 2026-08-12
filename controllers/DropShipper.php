<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('dropshipper');
load_trait('common');

class DropShipper extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->model('order/OrderComment_model', 'order_comment_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/ReprintOrder_model', 'reprint_order_model');
		$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');
		
		$this->load->model('event/EventOrder_model', 'event_order_model');
		
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/Student_model', 'student_model');

		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Validate_model', 'validate_model');
		$this->load->model('common/Enrol_model', 'enrol_model');

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');

		$this->load->model('PrinterZipDownload_model', 'printer_zip_download_model');

		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');
		$this->load->model('shipping/Shipment_model', 'shipment_model');

		$this->load->model('dropshipper/DropshipperOrder_model', 'dropshipper_order_model');
		$this->load->model('dropshipper/DropshipperAssignLog_model', 'dropshipper_assignlog_model');
		$this->load->model('dropshipper/DropshipperAssignment_model', 'dropshipper_assignment_model');

		$this->load->library('form_validation');

		if ($this->session->userdata('dropShipper') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$this->printer_path = PRINTER_PDF_DIR . 'bookpdfs_' . (int)$this->session->userdata('user_id') . '.zip';
	}

	use Orders,
		BookPrintCustom,
		BookPrintGrey,
		BulkDownload,
		DropshipperAssignments,
		DropshipperShipOrder,
		BlackWhiteOrders;

	public function index() {
		$this->dashboard();
	}

	public function dashboard() {
		$data['page_name'] 		= 'dashboard';
		$data['page_title'] 	= _l('dashboard');

		if (in_array($this->session->userdata('role_id'), [_dropshipper_role()])) {
			$total_orders = $this->dropshipper_order_model->printerStats([
				'assign_printer_id'	=> $this->session->userdata('user_id'),
			]);

			$total_orders_printed = $this->dropshipper_order_model->printerStats([
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'status'			=> 3,
			]);

			$data['order_stats'] 			= [
				'total_orders' 				=> $total_orders,
				'total_orders_printed' 		=> $total_orders_printed,
				'balance' 					=> $total_orders - $total_orders_printed,
			];

			$total_paperback 				= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
			]);

			$total_blackwhite 				= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
			]);
			$backlog_paperback 				= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 1
			]);

			$backlog_blackwhite 			= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 1
			]);
			$today_paperback 				= $this->dropshipper_order_model->todayData([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 1
			]);

			$today_blackwhite 				= $this->dropshipper_order_model->todayData([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 1,
			]);
			$printed_paperback 				= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 3
			]);
			$printed_hardcover 				= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Hard Cover',
				'status' 					=> 3
			]);
			$printed_blackwhite 			= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 3
			]);

			$delivered_paperback 			= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 4
			]);

			$delivered_blackwhite 			= $this->dropshipper_order_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 4
			]);

			$data['backlogs'] 	= [
				'backlog' 		=> [
					'paperback' 	=> ($backlog_paperback >= $today_paperback) ? ($backlog_paperback - $today_paperback) : 0,
					'blackwhite' 	=> ($backlog_blackwhite >= $today_blackwhite) ? ($backlog_blackwhite - $backlog_blackwhite) : 0
				],
				'today' 		=> [
					'paperback' 	=> (int)$today_paperback ?? 0,
					'blackwhite' 	=> (int)$today_blackwhite ?? 0
				],
				'total' 		=> [
					'paperback' 	=> $total_paperback ?? 0,
					'blackwhite' 	=> $total_blackwhite ?? 0
				],
				'delivered' 		=> [
					'paperback' 	=> $delivered_paperback ?? 0,
					'blackwhite' 	=> $delivered_blackwhite ?? 0
				],
			];
		}

		$data['download_report_action'] = base_url('dropShipper/download_today_report');

		$this->load->view('backend/index', $data);
	}

	public function download_today_report() {
		$filter_data = [
			'status'		=> 1,
			'shipped_by'	=> $this->session->userdata('user_id'),
			'startdate'		=> date('Y-m-d'),
			'enddate'		=> date('Y-m-d'),
		];

		$results = $this->shipment_model->get_all($filter_data)['rows'] ?? [];

		$sn = 1;

		$dropshipper_assignments = $sort_order = [];

		foreach ($results as $result) {
			$products = $this->dropshipper_assignlog_model->get_all([
				'order_id'	=> $result['order_id'],
			])['rows'] ?? [];

			foreach ($products as $product) {
				$assignment_info= $this->dropshipper_assignment_model->get($product['assignment_id']);
				$printer_info 	= $this->user_model->get($product['printer_id']);
				$book_info 		= $this->book_version_model->getByVersion($product['product_id'], $product['version']);
				$order_info 	= $this->order_model->get($product['order_id']);

				if (empty($order_info)) continue;

				$option = json_decode($product['option'], true);

				$dropshipper_assignments[] = [
					'sn'				=> $sn,
					'assignment_code'	=> $assignment_info['code'],
					'order_code'		=> $order_info['order_code'],
					'book'				=> $book_info['name'],
					'author_name'		=> $book_info['author_name'],
					'version'			=> $product['version'],
					'option'			=> $option['name'],
					'quantity'			=> $product['quantity'],
					'printer'			=> sprintf('%s %s', $printer_info['first_name'] ?? '', $printer_info['last_name'] ?? ''),
					'assignment_date'	=> formatDate($product['date_added']),
					'ship_date'			=> formatDate($result['date_added']),
				];

				$sort_order[] = $book_info['name'];

				$sn++;
			}
		}

		array_multisort($sort_order, $dropshipper_assignments);

		self::_downloadCsv($dropshipper_assignments, 'dropshipper_assignments');
	}

	private function _downloadCsv($results = [], $filename = 'download') {
		$filename = $filename . date('Y_m_d_h_i_s') . '.csv';

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function _writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	private function _getAdminBarcode($data = 0) {
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			160,
			40,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();

		file_put_contents(FCPATH . $file, $bobj->getPngData());
		return $file;
	}
}
