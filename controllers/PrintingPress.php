<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('printingpress');
load_trait('common');

class PrintingPress extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/Student_model', 'student_model');

		$this->load->model('common/Validate_model', 'validate_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('printer/PrinterStatusLog_model', 'printer_status_log_model');
		$this->load->model('PrinterZipDownload_model', 'printer_zip_download_model');
		$this->load->model('order/ReprintOrder_model', 'reprint_order_model');
		$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');
		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');
		$this->load->model('printer/PrinterExtraDetails_model', 'printer_extra_details_model');

		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');

		$this->load->library('form_validation');

		if ($this->session->userdata('printingPress') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$this->printer_path = PRINTER_PDF_DIR . 'bookpdfs_' . (int)$this->session->userdata('user_id') . '.zip';
	}

	use Orders,
		BookPrintCustom,
		BookPrintGrey,
		BulkDownload,
		ReprintOrders,
		PrinterAssignment,
		QaQc,
		BlackWhiteOrders;

	public function index() {
		$this->dashboard();
	}

	public function dashboard() {
		if ($this->session->userdata('printingPress') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'dashboard';
		$data['page_title'] 	= _l('dashboard');

		if (in_array($this->session->userdata('role_id'), [12, 15])) {
			$total_orders = $this->printer_stats_model->printerStats([
				'assign_printer_id'	=> $this->session->userdata('user_id'),
			]);

			$total_orders_printed = $this->printer_stats_model->printerStats([
				'assign_printer_id'	=> $this->session->userdata('user_id'),
				'status'			=> 3,
			]);

			$data['order_stats'] 			= [
				'total_orders' 				=> $total_orders,
				'total_orders_printed' 		=> $total_orders_printed,
				'balance' 					=> $total_orders - $total_orders_printed,
			];

			$total_paperback 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
			]);
			$total_hardcover 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Hard Cover',
			]);
			$total_blackwhite 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
			]);
			$backlog_paperback 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 1
			]);
			$backlog_hardcover 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Hard Cover',
				'status' 					=> 1
			]);
			$backlog_blackwhite 			= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 1
			]);
			$today_paperback 				= $this->printer_stats_model->todayData([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 1
			]);
			$today_hardcover 				= $this->printer_stats_model->todayData([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Hard Cover',
				'status' 					=> 1,
			]);
			$today_blackwhite 				= $this->printer_stats_model->todayData([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 1,
			]);
			$printed_paperback 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Paperback',
				'status' 					=> 3
			]);
			$printed_hardcover 				= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Hard Cover',
				'status' 					=> 3
			]);
			$printed_blackwhite 			= $this->printer_stats_model->printerStats([
				'assign_printer_id' 		=> $this->session->userdata('user_id'),
				'type' 						=> 'Black White',
				'status' 					=> 3
			]);

			$data['backlogs'] 	= [
				'backlog' 		=> [
					'paperback' 	=> ($backlog_paperback >= $today_paperback) ? ($backlog_paperback - $today_paperback) : 0,
					'hardcover' 	=> ($backlog_hardcover >= $today_hardcover) ? ($backlog_hardcover - $backlog_hardcover) : 0,
					'blackwhite' 	=> ($backlog_blackwhite >= $today_blackwhite) ? ($backlog_blackwhite - $backlog_blackwhite) : 0
				],
				'today' 		=> [
					'paperback' 	=> (int)$today_paperback ?? 0,
					'hardcover' 	=> (int)$today_hardcover ?? 0,
					'blackwhite' 	=> (int)$today_blackwhite ?? 0
				],
				'total' 		=> [
					'paperback' 	=> $total_paperback ?? 0,
					'hardcover' 	=> $total_hardcover ?? 0,
					'blackwhite' 	=> $total_blackwhite ?? 0
				],
				'printed' 		=> [
					'paperback' 	=> $printed_paperback ?? 0,
					'hardcover' 	=> $printed_hardcover ?? 0,
					'blackwhite' 	=> $printed_blackwhite ?? 0
				],
			];
		} else if ($this->session->userdata('role_id') == 13) {
			$total_orders = $this->printer_stats_model->printerStats([]);

			$total_orders_printed = $this->printer_stats_model->printerStats([
				'status' 		=> 3,
			]);

			$data['order_stats'] = [
				'total_orders' 				=> $total_orders,
				'total_orders_printed' 		=> $total_orders_printed,
				'balance' 					=> $total_orders - $total_orders_printed,
			];

			$data['list'] = [];

			$printer_list = $this->student_model->get_by_role_id_in([12, 15]);

			foreach ($printer_list as $key => $value) {
				$backlog_paperback 		= $this->printer_stats_model->printerStats([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Paperback',
					'status' 			=> 1,
				]);
				$backlog_hardcover 		= $this->printer_stats_model->printerStats([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Hard Cover',
					'status'			=> 1,
				]);
				$backlog_blackwhite 	= $this->printer_stats_model->printerStats([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Black White',
					'status'			=> 1,
				]);
				$printed_paperback 		= $this->printer_stats_model->printerStats([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Paperback',
					'status' 			=> 3,
				]);
				$printed_hardcover 		= $this->printer_stats_model->printerStats([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Hard Cover',
					'status' 			=> 3,
				]);
				$printed_blackwhite 	= $this->printer_stats_model->printerStats([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Black White',
					'status' 			=> 3,
				]);
				$today_paperback 		= $this->printer_stats_model->todayData([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Paperback',
					'status' 			=> 1,
				])['total'];
				$today_hardcover 		= $this->printer_stats_model->todayData([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Hard Cover',
					'status' 			=> 1,
				])['total'];
				$today_blackwhite 		= $this->printer_stats_model->todayData([
					'assign_printer_id' => $value['id'],
					'type' 				=> 'Black White',
					'status' 			=> 1,
				])['total'];

				$data['list'][] = [
					'name' 		=> $value['first_name'],
					'backlog' 	=> [
						'paperback' 	=> $backlog_paperback - $today_paperback,
						'hardcover' 	=> $backlog_hardcover - $today_hardcover,
						'blackwhite' 	=> $backlog_blackwhite - $today_blackwhite
					],
					'today' 	=> [
						'paperback' 	=> $today_paperback ?? 0,
						'hardcover' 	=> $today_hardcover ?? 0,
						'blackwhite' 	=> $today_blackwhite ?? 0
					],
					'total' 	=> [
						'paperback' 	=> ($backlog_paperback - $today_paperback) + $today_paperback,
						'hardcover' 	=> ($backlog_hardcover - $today_hardcover) + $today_hardcover,
						'blackwhite' 	=> ($backlog_blackwhite - $today_blackwhite) + $today_blackwhite
					],
					'printed' 	=> [
						'paperback' 	=> $printed_paperback ?? 0,
						'hardcover' 	=> $printed_hardcover ?? 0,
						'blackwhite' 	=> $printed_blackwhite ?? 0
					],
				];;
			}
		}

		$this->load->view('backend/index', $data);
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
}
