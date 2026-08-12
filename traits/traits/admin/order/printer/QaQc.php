<?php defined('BASEPATH') or exit('No direct script access allowed');

trait QaQc {
	public function book_titles($assignment_code = '') {
		if (empty($assignment_code) || empty($assignment_info = $this->printer_assignment_model->getByCode($assignment_code))) {
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		$data['page_name'] 		= 'printer_assignment/book_titles';
		$data['heading'] 		= _l('book_titles');
		$data['page_title'] 	= _l('book_titles ('.$assignment_code.')');
		$data['assignment_id']	= $assignment_info['id'];
		$data['action_ajax'] 	= base_url('admin/ajax_book_titles/'.$assignment_code);

		$filter_data = [];
		$filter_data['assignment_id'] 		= $assignment_info['id'];
		$filter_data['assign_printer_id'] 	= $assignment_info['printer_id'];

		$printer_assign_results = $this->printer_stats_model->printerAssignData($filter_data);

		$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');

		$filter_data = [];
		$filter_data['assignment_id'] = $assignment_info['id'];

		$qa_qc_lots_info = $this->printer_stats_model->getQaQcCount($filter_data);

		$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;

		$accepted_count += $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

		$data['action_visible']		= false;
		$data['qa_qc_complete_btn']	= false;

		if (!empty($qa_qc_lots_info)) {
			$data['action_visible']	= true;

			if ($printer_assign_results['total'] !== $accepted_count) {
				$data['qa_qc_complete_btn']	= true;
			}

			$data['action_csv'] 		= base_url('admin/download_qaqc_csv/' . $assignment_code);
			$data['action_csv_logs'] 	= base_url('admin/download_qaqc_logs_csv/' . $assignment_code);
			$data['action_complete'] 	= base_url('admin/qaqc_complete/' . $assignment_code);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_book_titles($assignment_code = '') {
		$json['data'] = [];

		if ($assignment_code) {
			$columns = $this->input->get('columns');

			$filter_data = [
				'start'				=> (int)$this->input->get('start'),
				'limit'				=> (int)$this->input->get('length'),
				'search'			=> $this->input->get('search[value]'),
				'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
				'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			];

			$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');

			$assignment_info 	= $this->printer_assignment_model->getByCode($assignment_code);
			$printer_id 		= $assignment_info['printer_id'];

			$filter_data['assignment_id'] 		= $assignment_info['id'];
			$filter_data['assign_printer_id'] 	= $printer_id;

			$results = $this->printer_stats_model->printerAssignDataSortByBalanced($filter_data);

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

				$type = strtolower(json_decode($result['option'], 1)['name']);
				$printer_info = $this->user_model->get($printer_id);

				$filter_arr = [];
				$filter_arr['book_id'] 			= $result['id'];
				$filter_arr['assignment_id'] 	= $assignment_info['id'];
				$filter_arr['version'] 			= $result['version'];
				$filter_arr['option'] 			= $type;

				$qa_qc_managers = $this->qa_qc_logs_model->get_all_managers($filter_arr);
				$qa_qc_managers = !empty($qa_qc_managers) ? implode('<br/>', explode(",", $qa_qc_managers)) : '';

				$qa_qc_lots_info	= $this->printer_stats_model->getQaQcCount($filter_arr);
				$accepted_count 	= $qa_qc_lots_info['accepted_quantity'] ?? 0;
				$accepted_count		+= $qa_qc_lots_info['accepted_short_quantity'] ?? 0;
				$rejected_count 	= $qa_qc_lots_info['rejected_quantity'] ?? 0;
				$balance_count 		= (int)$result['quantity']-(int)$accepted_count-(int)$rejected_count;

				$json['data'][] = [
					'#'					=> vsprintf('<input type="checkbox" class="select-me" value="%s">', [
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
					'quantity'			=> vsprintf('Total:: %s<br>Accepted:: %s<br>Rejected:: %s<br>Balance:: %s', [
						$result['quantity'],
						$accepted_count,
						$rejected_count,
						$balance_count
					]),
					'author_name'		=> $book_info['author_name'],
					'assignment_code'	=> $result['assignment_code'],
					'assign_date'		=> date('M j, Y', strtotime($result['date_added'])),
					'manager_name'		=> $qa_qc_managers,
					'printed'			=> (!empty($result['assign_printer_id']) && $result['printing_status'] !== '0') ? 'true' : 'false',
					'assign'		  	=> !empty($result['assign_printer_id']) ? $printer_info['first_name'] . ' ' . $printer_info['last_name'] : 'NA',
					'actions'			=> _qa_qc_rb_buttons($result, $qa_qc_lots_info)
				];
			}
		}

		output_json($json);
	}

	public function ajax_books_details() {
		$json['products'] = [];

		$assignment_info = $this->printer_assignment_model->get($this->input->post('assignment_id'));

		$filter_data = [];
		$filter_data['book_id'] 		= $this->input->post('book_id');
		$filter_data['assignment_id'] 	= $this->input->post('assignment_id');
		$filter_data['version'] 		= $this->input->post('version');
		$filter_data['option'] 			= $this->input->post('option');
		$filter_data['assign_printer_id']= $assignment_info['printer_id'];

		if (!empty($result = $this->printer_stats_model->printerAssignData($filter_data)['rows'][0])) {
			$qa_qc_lots_info 	= $this->printer_stats_model->getQaQcCount($filter_data);

			$accepted_count 	= $qa_qc_lots_info['accepted_quantity'] ?? 0;
			$accepted_count 	+= $qa_qc_lots_info['accepted_short_quantity'] ?? 0;
			$rejected_count 	= $qa_qc_lots_info['rejected_quantity'] ?? 0;
			$balance_count 		= (int)$result['quantity']-(int)$accepted_count-(int)$rejected_count;

			$json['products'][0]['product_id'] = $result['product_id'];
			$json['products'][0]['quantity'] = $balance_count;
			$json['products'][0]['total_quantity'] = $result['quantity'];
			$json['products'][0]['accepted_quantity'] = $accepted_count;
			$json['products'][0]['name'] = $result['name'];
		} else {
			$json['error'] = _l('invalid_details');
		}

		output_json($json);
	}

	public function ajax_books_details_reset() {
		$json = [];

		$assignment_info = $this->printer_assignment_model->get($this->input->post('assignment_id'));

		$filter_data = [];
		$filter_data['book_id'] 		= $this->input->post('book_id');
		$filter_data['assignment_id'] 	= $this->input->post('assignment_id');
		$filter_data['version'] 		= $this->input->post('version');
		$filter_data['option'] 			= $this->input->post('option');

		if (!empty($qa_qc_lots_info = $this->printer_stats_model->getQaQcCount($filter_data))) {
			$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');
			$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');
			$this->load->model('book/BookStock_model', 'book_stock_model');
			// $this->load->model('book/BookStockLog_model', 'book_stock_log_model');

			$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;
			$accepted_count += $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

			if ($stock_info = $this->book_stock_model->get_all([
				'book_id'	=> $this->input->post('book_id'),
				'version'	=> $this->input->post('version'),
				'option'	=> $this->input->post('option'),
			])['rows'][0] ?? []) {
				$qty = (int)$stock_info['quantity'] - (int)$accepted_count;

				$this->book_stock_model->edit($stock_info['id'], [
					'quantity'	=> $qty ?? 0,
				]);
			}

			$this->qa_qc_lots_model->delete($qa_qc_lots_info['id']);

			$this->qa_qc_logs_model->delete($filter_data);

			$json['success'] = _l('reset_successfully');
		} else {
			$json['error'] = _l('invalid_details');
		}

		output_json($json);
	}

	public function ajax_books_details_reset_rejected_count() {
		$json = [];

		$assignment_info = $this->printer_assignment_model->get($this->input->post('assignment_id'));

		$filter_data = [];
		$filter_data['book_id'] 		= $this->input->post('book_id');
		$filter_data['assignment_id'] 	= $this->input->post('assignment_id');
		$filter_data['version'] 		= $this->input->post('version');
		$filter_data['option'] 			= $this->input->post('option');

		if (!empty($qa_qc_lots_info = $this->printer_stats_model->getQaQcCount($filter_data))) {
			$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');
			$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');

			$this->qa_qc_lots_model->edit($qa_qc_lots_info['id'], [
				'rejected_quantity'	=> 0
			]);

			$this->qa_qc_logs_model->add([
				'assignment_id' 	=> (int)$this->input->post('assignment_id'),
				'book_id' 			=> (int)$this->input->post('book_id'),
				'version' 			=> (int)$this->input->post('version'),
				'option' 			=> $this->input->post('option'),
				'quantity' 			=> $qa_qc_lots_info['rejected_quantity'],
				'reason' 			=> 'rejected_quantity_reset',
				'comment' 			=> json_encode('Rejected Quantity Reset'),
				'action' 			=> 2,
				'status' 			=> 1,
				'manager_id'		=> (int)$this->session->userdata('user_id'),
			]);

			$json['success'] = _l('reset_rejected_count_successfully');
		} else {
			$json['error'] = _l('invalid_details');
		}

		output_json($json);
	}

	public function qaqc_action() {
		if (empty($this->input->post())) {
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		$assignment_info = $this->printer_assignment_model->get($this->input->post('assignment_id'));

		$json = [];

		$filter_data = [];
		$filter_data['assignment_id'] 		= $this->input->post('assignment_id');
		$filter_data['book_id'] 			= $this->input->post('book_id');
		$filter_data['assign_printer_id'] 	= $assignment_info['printer_id'];
		$filter_data['version'] 			= $this->input->post('version');

		if (!empty($result = $this->printer_stats_model->printerAssignData($filter_data)['rows'][0])) {
			$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');
			$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');

			$quantity 	= (int)$this->input->post('quantity');
			$action 	= (int)$this->input->post('action');

			if ($quantity > $result['quantity']) {
				$quantity = $result['quantity'];
			} elseif ($quantity < 0) {
				$quantity = $result['quantity'];
			}

			$filter_data = [];
			$filter_data['assignment_id'] 	= $this->input->post('assignment_id');
			$filter_data['book_id'] 		= $this->input->post('book_id');
			$filter_data['version'] 		= $this->input->post('version');
			$filter_data['option'] 			= $this->input->post('option');

			$accepted_quantity = $rejected_quantity = $accepted_short_quantity = 0;

			switch ((int)$action) {
				case 1:
					$accepted_quantity = $quantity;
					break;

				case 2:
					$rejected_quantity = $quantity;
					break;

				case 3:
					$accepted_short_quantity = $quantity;
					break;

				default:
					break;
			}

			$qa_qc_lots_info = $this->qa_qc_lots_model->get_all($filter_data);
			$qa_qc_lots_info = $qa_qc_lots_info['rows'][0] ?? [];

			if (!empty($qa_qc_lots_info)) {
				$this->qa_qc_lots_model->edit($qa_qc_lots_info['id'], [
					'book_quantity' 			=> $result['quantity'],
					'accepted_quantity' 		=> $qa_qc_lots_info['accepted_quantity'] + $accepted_quantity,
					'rejected_quantity' 		=> $qa_qc_lots_info['rejected_quantity'] + $rejected_quantity,
					'accepted_short_quantity' 	=> $qa_qc_lots_info['accepted_short_quantity'] + $accepted_short_quantity,
				]);
			} else {
				$this->qa_qc_lots_model->add([
					'assignment_id' 			=> (int)$this->input->post('assignment_id'),
					'book_id' 					=> (int)$this->input->post('book_id'),
					'version' 					=> (int)$this->input->post('version'),
					'option' 					=> $this->input->post('option'),
					'book_quantity' 			=> $result['quantity'],
					'accepted_quantity'			=> $accepted_quantity,
					'rejected_quantity' 		=> $rejected_quantity,
					'accepted_short_quantity' 	=> $accepted_short_quantity,
				]);
			}

			$this->qa_qc_logs_model->add([
				'assignment_id' 	=> (int)$this->input->post('assignment_id'),
				'book_id' 			=> (int)$this->input->post('book_id'),
				'version' 			=> (int)$this->input->post('version'),
				'option' 			=> $this->input->post('option'),
				'quantity' 			=> $quantity,
				'reason' 			=> ($action != 1) ? $this->input->post('reason') : '',
				'comment' 			=> (($action != 1) && !empty($this->input->post('comment'))) ? json_encode($this->input->post('comment')) : '',
				'action' 			=> $action,
				'status' 			=> 1,
				'manager_id'		=> (int)$this->session->userdata('user_id'),
			]);

			$order_assign_log_ids = explode(',', $result['ids']);

			foreach ($order_assign_log_ids as $order_assign_log_id) {
				$this->printer_assign_log_model->editById($order_assign_log_id, [
					'status' 			=> 3
				]);
			}

			if (($action != 2) && $quantity > 0) {
				$order_ids = explode(',', $result['order_ids']);

				foreach ($order_ids as $order_id) {
					$printer_assign_results = $this->printer_assign_log_model->get_all([
						'order_id'	=> $order_id
					])['rows'];

					foreach ($printer_assign_results as $printer_assign_info) {
						if (!in_array($printer_assign_info['status'], [3])) continue;
					}

					$order_info = $this->order_model->get($order_id);
					$this->order_model->edit($order_id, [
						'status' 			=> in_array($order_info['status'], [1,2]) ? 8 : $order_info['status'],
						'printing_status' 	=> 1
					]);
				}

				$this->load->model('book/BookStock_model', 'book_stock_model');
				$this->load->model('book/BookStockLog_model', 'book_stock_log_model');

				if ($stock_info = $this->book_stock_model->get_all([
					'book_id'	=> $this->input->post('book_id'),
					'version'	=> $this->input->post('version'),
					'option'	=> $this->input->post('option'),
				])['rows'][0] ?? []) {
					$this->book_stock_model->edit($stock_info['id'], [
						'quantity'	=> (int)($stock_info['quantity'] + (int)$quantity),
					]);

					$this->book_stock_log_model->add([
						'manager_id'=> (int)$this->session->userdata('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$quantity,
						'status'	=> 0,
					]);

					$this->json['success'] = _l('book_stock_updated');
				} else {
					$this->book_stock_model->add([
						'manager_id'=> (int)$this->session->userdata('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$quantity,
					]);

					$this->book_stock_log_model->add([
						'manager_id'=> (int)$this->session->userdata('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$quantity,
						'status'	=> 1,
					]);

					$this->json['success'] = _l('book_stock_added');
				}

				$this->load->library('Stock_lib', 'stock_lib');
				$this->stock_lib->stockFulfill(
					$quantity,
					$this->input->post('book_id'),
					$this->input->post('version'),
					$this->input->post('option'),
					$this->input->post('assignment_id')
				);
			}

			$json['success'] = _l('details_saved');
		} else {
			$json['error'] = _l('invalid_details');
		}

		output_json($json);
	}

	public function qaqc_complete($assignment_code = '') {
		if (empty($assignment_code) || empty($this->session->userdata('user_id'))) {
			$this->session->set_flashdata('error_message', _l('invalid_request'));
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		if (empty($assignment_info = $this->printer_assignment_model->getByCode($assignment_code))) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		if ((int)$assignment_info['id']) {
			if (empty($this->cron_model->getByCode('qaqcCompleteCron_' . $assignment_info['id']))) {
				$this->cron_model->add([
				'code'		  	=> 'qaqcCompleteCron_' . $assignment_info['id'],
				'action'		=> 'alert_model->qaqcCompleteCron',
				'data'		  	=> [$assignment_info['id']],
				'site_id'	   	=> 1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);
			} else {
				$this->cron_model->editByCode('qaqcCompleteCron_' . $assignment_info['id'], [
					'alert_date' => date('Y-m-d H:i:00', strtotime('+1 minutes')),
					'status' => 0
				]);
			}

			$this->session->set_flashdata('success_message', _l('mail_in_processing'));

			redirect(base_url('admin/book_titles/'.$assignment_code), 'refresh');
		}
	}

	public function download_qaqc_csv($assignment_code = '') {
		if (empty($assignment_code) || empty($this->session->userdata('user_id'))) {
			$this->session->set_flashdata('error_message', _l('invalid_request'));
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		if (empty($assignment_info = $this->printer_assignment_model->getByCode($assignment_code))) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		$filter_data = [];
		$filter_data['assignment_id'] 		= $assignment_info['id'];
		$filter_data['assign_printer_id'] 	= $assignment_info['printer_id'];

		$results = $this->printer_stats_model->printerAssignDataSortByBalanced($filter_data);

		$printer_info 	= $this->user_model->get($assignment_info['printer_id']);
		$qa_qc_logs 	= [];
		$filename 		= 'qa_qc_printer_' . date('Y_m_d_H_i_s') . '.csv';

		$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');

		foreach ($results['rows'] ?? [] as $key => $result) {
			$type = strtolower(json_decode($result['option'], 1)['name']);

			$filter_data = [];
			$filter_data['assignment_id'] 		= $assignment_info['id'];
			$filter_data['book_id'] 			= $result['product_id'];
			$filter_data['version'] 			= $result['version'];
			$filter_data['option'] 				= $type;
			$filter_data['assign_printer_id'] 	= $assignment_info['printer_id'];

			$printer_assign_results = $this->printer_stats_model->printerAssignData($filter_data);
			$printer_assign_results = $printer_assign_results['rows'][0] ?? [];

			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$filter_data = [];
			$filter_data['sort'] 			= 'qa_qc_lots.id';
			$filter_data['order'] 			= 'ASC';
			$filter_data['assignment_id'] 	= $assignment_info['id'];
			$filter_data['book_id'] 		= $result['product_id'];
			$filter_data['version'] 		= $result['version'];
			$filter_data['option'] 			= $type;

			$qa_qc_lots_results = $this->qa_qc_lots_model->get_all($filter_data);

			$qa_qc_lots_info = !empty($qa_qc_lots_results['rows']) ? end($qa_qc_lots_results['rows']) : [];

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$sku = _o_b_code($result['product_id'], $result['version'], $type);

			$accepted_count 		= $qa_qc_lots_info['accepted_quantity'] ?? 0;
			$accepted_short_count 	= $qa_qc_lots_info['accepted_short_quantity'] ?? 0;
			$rejected_count 		= $qa_qc_lots_info['rejected_quantity'] ?? 0;
			$balance_count 			= (int)$result['quantity']-(int)$accepted_count-(int)$accepted_short_count;

			$qa_qc_logs[] = [
				'assignment_code'	=> $assignment_code,
				'book_id'			=> $result['product_id'],
				'book_sku'			=> $sku,
				'book_title'		=> $book_info['name'] ?? '',
				'author_name'		=> $book_info['author_name'] ?? '',
				'pages'				=> ($total_pages * 2 + 1) ?? '0',
				'version'			=> $result['version'],
				'option'			=> $type,
				'book_quantity'		=> $printer_assign_results['quantity'] ?? 0,
				'accepted_quantity'	=> $accepted_count,
				'accepted_short_quantity'=> $accepted_short_count,
				'rejected_quantity'	=> $rejected_count,
				'balance_quantity'	=> $balance_count,
				'printer'		  	=> !empty($printer_info) ? trim($printer_info['first_name'] . ' ' . $printer_info['last_name']) : '',
				'qa_qc_date_added'	=> formatDate($qa_qc_lots_info['date_added'])
			];
		}

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

		$headers = isset($qa_qc_logs[0]) ? array_keys($qa_qc_logs[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($qa_qc_logs, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function download_qaqc_logs_csv($assignment_code = '') {
		if (empty($assignment_code) || empty($this->session->userdata('user_id'))) {
			$this->session->set_flashdata('error_message', _l('invalid_request'));
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		if (empty($assignment_info = $this->printer_assignment_model->getByCode($assignment_code))) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');

		$filter_data = [];
		$filter_data['sort'] 			= 'qa_qc_logs.id';
		$filter_data['order'] 			= 'ASC';
		$filter_data['assignment_id'] 	= $assignment_info['id'];

		$results 	= $this->qa_qc_logs_model->get_all($filter_data);

		$qa_qc_logs = [];
		$filename 	= 'qa_qc_logs_' . date('Y_m_d_H_i_s') . '.csv';

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_version_model->getByVersion($result['book_id'], $result['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['book_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$user_info = $this->user_model->get($result['manager_id']);

			$delete_user_info = !empty($result['_deleted_manager_id']) ? $this->user_model->get($result['_deleted_manager_id']) : '';

			$printer_info = $this->user_model->get($assignment_info['printer_id']);

			$action = 'Pending';
			switch ($result['action']) {
				case '1':
					$action = 'Accepted';
					break;

				case '2':
					$action = 'Rejected';
					break;

				case '3':
					$action = 'Accepted with Short Quantity';
					break;

				default:
					$action = 'Pending';
					break;
			}

			$qa_qc_logs[] = [
				'assignment_code'	=> $assignment_code,
				'book_id'			=> $result['book_id'],
				'book_sku'			=> _o_b_code($result['book_id'], $result['version'], $result['option']),
				'book_title'		=> $book_info['name'] ?? '',
				'author_name'		=> $book_info['author_name'] ?? '',
				'pages'				=> ($total_pages * 2 + 1) ?? '0',
				'version'			=> $result['version'],
				'option'			=> $result['option'],
				'quantity'			=> $result['quantity'] ?? '0',
				'reason'			=> $result['reason'],
				'comment'			=> !empty($result['comment']) ? json_decode($result['comment']) : '',
				'action'			=> $action,
				'qa_manager'		=> !empty($user_info) ? trim($user_info['first_name'] . ' ' . $user_info['last_name']) : '',
				'printer'		  	=> !empty($printer_info) ? trim($printer_info['first_name'] . ' ' . $printer_info['last_name']) : '',
				'qa_qc_date_added'	=> formatDate($result['date_added']),
				'delete_by_qa_manager'		=> !empty($delete_user_info) ? trim($delete_user_info['first_name'] . ' ' . $delete_user_info['last_name']) : '',
				'qa_qc_delete_date_added'	=> formatDate($result['date_deleted'])
			];
		}

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

		$headers = isset($qa_qc_logs[0]) ? array_keys($qa_qc_logs[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($qa_qc_logs, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function qaqc_reject() {
		if (empty($this->input->post())) {
			redirect(base_url('admin/printer_assignment'), 'refresh');
		}

		$json = [];

		$this->load->library('QaQc_lib', 'qaqc_lib');

		if (empty($response = $this->qaqc_lib->qaqcRejectOrderByBook($this->input->post()))) {
			$json['error'] = $this->qaqc_lib->error ?? _l('invalid_details');
		} else {
			$json['success'] = 'QaQc Rejected';
		}

		output_json($json);
	}
}
