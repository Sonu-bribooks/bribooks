<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AuthorRoyalty {
	public function remove_bank_unpaid_royalty_users_csv() {
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->delimiter = ';';

		$this->parsecsv->auto('assets/csv/unpaid_royalty_fix.csv');

		$results = array_column($this->parsecsv->data, 'account_number');

		$user_dump = [];

		foreach ($results as $account_number) {
			$bank_users = $this->bank_model->get_all([
				'account_number' => $account_number,
			])['rows'];

			foreach ($bank_users as $bank_info) {
				$this->bank_model->delete($bank_info['id']);
			}
		}
	}

	public function export_unpaid_royalty_users_csv() {
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->delimiter = ';';

		$this->parsecsv->auto('assets/csv/unpaid_royalty_fix.csv');

		$results = array_column($this->parsecsv->data, 'account_number');

		$user_dump = [];

		foreach ($results as $account_number) {
			$bank_users = $this->bank_model->get_all([
				'account_number' => $account_number,
			])['rows'];

			foreach ($bank_users as $bank_info) {
				$user_info = $this->student_model->get($bank_info['user_id']);

				$user_dump[] = [
					'id'		=> $user_info['id'],
					'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
					'email'		=> $user_info['email'],
					'mobile'	=> $user_info['mobile'],
					'account'	=> $bank_info['account_number'],
				];
			}

			if (empty($bank_users)) {
				// echo $account_number . '<br>';
			}
		}

		// pr($user_dump);
		self::_downloadCsv($user_dump, 'unpaid_users');
	}

	public function export_paid_royalty_users_csv() {
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->delimiter = ';';

		$this->parsecsv->auto('assets/csv/royalty_fix.csv');

		$results = array_column($this->parsecsv->data, 'account_number');

		$user_dump = [];

		foreach ($results as $account_number) {
			$bank_users = $this->bank_model->get_all([
				'account_number' => $account_number,
			])['rows'];

			foreach ($bank_users as $bank_info) {
				$user_info = $this->student_model->get($bank_info['user_id']);

				$user_dump[] = [
					'id'		=> $user_info['id'],
					'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
					'email'		=> $user_info['email'],
					'mobile'	=> $user_info['mobile'],
					'account'	=> $bank_info['account_number'],
				];
			}
		}

		// pr($user_dump);
		self::_downloadCsv($user_dump, 'paid_users');
	}

	public function fix_author_royalty_transaction_by_csv() {
		return;
		$this->load->library('zip');
		$this->load->library('parsecsv');
		$this->parsecsv->delimiter = ';';

		$this->parsecsv->auto('assets/csv/bbbkp/royalty_fix.csv');

		$results = array_column($this->parsecsv->data, 'account_number');

		// pr($results);

		$royalties = [];

		foreach ($results as $account_number) {
			$bank_users = $this->bank_model->get_all([
				'account_number' => $account_number,
			])['rows'];

			foreach ($bank_users as $bank_info) {
				$author_earnings = $this->author_earning_model->get_all([
					'author_id'	=> $bank_info['user_id'],
					'status'	=> 2,
				])['rows'] ?? [];

				if ($author_earnings) {
					foreach ($author_earnings as $info) {
						if (empty($info) || $info['status'] != 2) continue;

						$order_info = $this->order_model->get($info['order_id']);
						$order_site_info = $this->site_model->get($order_info['site_id']);

						if (strtolower($order_site_info['country_code']) !== 'in') {
							$info['amount'] = $info['amount'] * get_exchange_rate($order_site_info['currency_code']);
						}

						if (isset($royalties[$info['author_id']])) {
							$royalties[$info['author_id']]['user_id'] = $info['author_id'];
							$royalties[$info['author_id']]['amount'] += $info['amount'];
							$royalties[$info['author_id']]['author_earning_ids'][] = $info['id'];
						} else {
							$royalties[$info['author_id']]['user_id'] = $info['author_id'];
							$royalties[$info['author_id']]['amount'] = $info['amount'];
							$royalties[$info['author_id']]['author_earning_ids'] = [$info['id']];
						}

						log_kb([
							'id'				=> $info['id'],
							'status'			=> 1,
							'processed_by'		=> (int)$this->session->userdata('user_id'),
						]);

						$this->author_earning_model->edit($info['id'], [
							'status'			=> 1,
							'processed_by'		=> (int)$this->session->userdata('user_id'),
							'date_processed'	=> '2022-02-22 12:00:00',
							'date_modified'		=> '2022-03-02 12:00:00',
						]);
					}
				}
			}
		}

		// pr($royalties);

		$this->load->model('user/UserTransaction_model', 'user_transaction_model');

		foreach ($royalties as $user_id => $item) {
			$this->user_transaction_model->add([
				'user_id'				=> (int)$user_id,
				'amount'				=> round($item['amount']),
				'author_earning_ids'	=> implode(',', $item['author_earning_ids']),
				'approved_by'			=> (int)$this->session->userdata('user_id'),
				'status'				=> 1,
			]);
		}
	}

	public function fix_author_royalty_transaction() {
		return;
		$author_earnings = $this->db->get_where('author_earning', [
			'DATE(date_processed)'	=> '2023-01-23',
			'status'				=> 1,
		])->result_array();

		foreach ($author_earnings as $info) {
			if (empty($info) || $info['status'] != 1) continue;

			$order_info = $this->order_model->get($info['order_id']);
			$order_site_info = $this->site_model->get($order_info['site_id']);

			if (strtolower($order_site_info['country_code']) !== 'in') {
				$info['amount'] = $info['amount'] * get_exchange_rate($order_site_info['currency_code']);
			}

			if (isset($royalties[$info['author_id']])) {
				$royalties[$info['author_id']]['user_id'] = $info['author_id'];
				$royalties[$info['author_id']]['amount'] += $info['amount'];
				$royalties[$info['author_id']]['author_earning_ids'][] = $info['id'];
			} else {
				$royalties[$info['author_id']]['user_id'] = $info['author_id'];
				$royalties[$info['author_id']]['amount'] = $info['amount'];
				$royalties[$info['author_id']]['author_earning_ids'] = [$info['id']];
			}

			log_kb([
				'id'				=> $info['id'],
				'status'			=> 1,
				'processed_by'		=> (int)$this->session->userdata('user_id'),
				'date_processed'	=> date('Y-m-d H:i:s', strtotime($row['Sent By DateTime'])),
			]);
		}

		// log_kb($royalties);
		// pr($royalties);

		// $this->load->model('user/UserTransaction_model', 'user_transaction_model');
		//
		// foreach ($royalties as $user_id => $item) {
		// 	$this->user_transaction_model->add([
		// 		'user_id'				=> (int)$user_id,
		// 		'amount'				=> round($item['amount']),
		// 		'author_earning_ids'	=> implode(',', $item['author_earning_ids']),
		// 		'approved_by'			=> (int)$this->session->userdata('user_id'),
		// 		'status'				=> 1,
		// 		'date_added'			=> '2023-02-22 16:34:17',
		// 	]);
		// }
	}

	public function author_royalty() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['sites'] 		= [];

		$data['country'] 		= $this->country_model->get_all()['rows'] ?? [];

		$data['site_id'] = (int)$this->input->get('site_id');
		$data['country_name'] = $this->input->get('country');

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> 0,
		]);
		array_unshift($data['country'], [
			'name'	=> _l('default'),
			'id'	=> ''
		]);

		$data['action_ajax'] 	= base_url('admin/ajax_author_earnings');
		$data['page_name'] 		= 'author_royalty';
		$data['page_title'] 	= _l('author royalty');

		$this->load->view('backend/index', $data);
	}

	public function ajax_author_earnings() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'author_earning.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		$results = $this->author_earning_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_model->get($result['book_id']);

			$order_site_info = $this->site_model->get($result['site_id']);

			if (strtolower($order_site_info['country_code']) !== 'in') {
				$result['amount'] = $result['amount'] * get_exchange_rate($order_site_info['currency_code']);
			}

			$json['data'][] = [
				'sn'				=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">', //$filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'book'				=> $book_info['name'],
				'author'			=> $book_info['author_name'],
				'quantity'			=> $result['quantity'],
				'amount'			=> currency($result['amount']),
				'status'			=> _aes($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'date_modified'		=> formatDate($result['date_modified']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function author_royalty_export($site_id = 0, $type = 'text') {
		$filter_data = [
			'ne_status'	=> 1,
		];

		if ($site_id) {
			$filter_data['user_site_id'] = $site_id;
		}

		$filter_data['date_added_le'] = date('Y-m') . '-21';

		$results = $this->author_earning_model->get_all($filter_data)['rows'];

		$rows = [];

		foreach ($results as $key => $value) {
			$order_info = $this->order_model->get($value['order_id']);
			$order_site_info = $this->site_model->get($order_info['site_id']);

			if (strtolower($order_site_info['country_code']) !== 'in') {
				$value['amount'] = $value['amount'] * get_exchange_rate($order_site_info['currency_code']);
			}

			if ($value['amount'] > 0 && $order_info['status'] == 4) {
				$book_info = $this->book_model->get($value['book_id']);
				$bank_info = $this->bank_model->get_all([
					'user_id' => $value['author_id'],
				])['rows'][0] ?? [];

				if (!empty($bank_info)) {
					if (preg_match('/kotak/i', $bank_info['bank_name'])) {
						$bank_name = 'IFT';
					} else {
						$bank_name = $bank_info['bank_name'];
					}

					if (isset($rows[$bank_info['account_number']])) {
						$rows[$bank_info['account_number']]['amount'] += round($value['amount'], 2);
						$rows[$bank_info['account_number']]['payment_details_1'][] = (string)$value['id'];
						$rows[$bank_info['account_number']]['payment_details_2'][] = (string)$value['order_id'];

						if (!in_array($value['book_id'], $rows[$bank_info['account_number']]['payment_details_3'])) {
							$rows[$bank_info['account_number']]['payment_details_3'][] = (string)$value['book_id'];
						}

						if (!in_array($value['author_id'], $rows[$bank_info['account_number']]['payment_details_4'])) {
							$rows[$bank_info['account_number']]['payment_details_4'][] = (string)$value['author_id'];
						}
					} else {
						$rows[$bank_info['account_number']] = [
							'bank_name'			=> $bank_name,
							'name'				=> $bank_info['name'],
							'ifsc_code'			=> $bank_info['ifsc_code'],
							'account_number'	=> '\'' . $bank_info['account_number'] . '\'',
							'amount'			=> round($value['amount'], 2),
							'payment_details_1'	=> [(string)$value['id']],
							'payment_details_2'	=> [(string)$value['order_id']],
							'payment_details_3'	=> [(string)$value['book_id']],
							'payment_details_4'	=> [(string)$value['author_id']],
						];
					}

					empty($value['status']) && $this->author_earning_model->edit($value['id'], [
						'status'			=> 2,
						'processing_by'		=> (int)$this->session->userdata('user_id'),
						'date_processing'	=> date('Y-m-d H:i:s'),
					]);
				}
			}
		}

		$payments = $sort_order = [];

		foreach ($rows as $account_number => $item) {
			$payments[] = [
				'Client_Code (refer setup mail)' => 'YBOOKS',
				'Product_Code (refer setup mail )' => 'VPAY',
				'Payment_Type ( IFT(kotak),NEFT(other bank any amount),RTGS( more than 2 lakh other bank),IMPS( upto 5 lakh instatnt payment other bank ))' => $item['bank_name'],
				'Payment_Ref_No. (BLANK)' => '',
				'Payment_Date (DD/MM/YYYY)' => date('d/m/Y'),
				'Instrument Date ( FILL IN CASE OF imps only)' => '',
				'Dr_Ac_No (refer setup mail)' => '9746235011',
				'Amount' => round($item['amount']),
				'Bank_Code_Indicator(fixed for every txn)' => 'M',
				'Beneficiary_Code (blank)' => '',
				'Beneficiary_Name (receiver full name without special character ) maximum 40 charcter' => substr($item['name'], 0, 35),
				'Beneficiary_Bank (blank)' => $item['bank_name'],
				'Beneficiary_Branch / IFSC Code ( receiver bank ifsc code) 11 digit only' => $item['ifsc_code'],
				'Beneficiary_Acc_No (receiver account number)' => '\'' . $item['account_number'] . '\'',
				'Location' => '',
				'Print_Location' => '',
				'Instrument_Number' => '',
				'Ben_Add1' => '',
				'Ben_Add2' => '',
				'Ben_Add3' => '',
				'Ben_Add4' => '',
				'Beneficiary_Email' => '',
				'Beneficiary_Mobile' => '',
				'Debit_Narration ( to show in own bank statement) maxcimum 40 characters only' => 'Author Royalty from BriBooks',
				'Credit_Narration (to show in Receiver bank statement maxcimum 40 characters only)' => 'Author Royalty from BriBooks',
				'Payment Details 1' => implode(',', $item['payment_details_1']),
				'Payment Details 2' => implode(',', $item['payment_details_2']),
				'Payment Details 3' => implode(',', $item['payment_details_3']),
				'Payment Details 4' => implode(',', $item['payment_details_4']),
				'Enrichment_1' => '',
				'Enrichment_2' => '',
				'Enrichment_3' => '',
				'Enrichment_4' => '',
				'Enrichment_5' => '',
				'Enrichment_6' => '',
				'Enrichment_7' => '',
				'Enrichment_8' => '',
				'Enrichment_9' => '',
				'Enrichment_10' => '',
				'Enrichment_11' => '',
				'Enrichment_12' => '',
				'Enrichment_13' => '',
				'Enrichment_14' => '',
				'Enrichment_15' => '',
				'Enrichment_16' => '',
				'Enrichment_17' => '',
				'Enrichment_18' => '',
				'Enrichment_19' => '',
				'Enrichment_20' => ''
			];

			$sort_order[] = round($item['amount'], 2);
		}

		array_multisort($sort_order, SORT_DESC, $payments);

		// pr($payments);

		if ($type == 'text') {
			self::_generateTextBank($payments);
		} else {
			self::_generateCsvBank($payments);
		}

		return;
	}

	private function _generateTextBank($results = []) {
		$rows = [];

		foreach ($results as $key => $value) {
			$rows[] = implode('~', array_values($value));
		}

		$filename = 'royalties_' . date('Y_m_d_h_i_s') . '.txt';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/octet-stream; ");

		$fp = fopen('php://output', 'w');
		fwrite($fp, implode("\n", $rows));
		fclose($fp);

		exit;
	}

	private function _generateCsvBank($results = []) {
		$filename = 'royalties_' . date('Y_m_d_h_i_s') . '.csv';

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

	public function import_royalty() {
		if (!is_dir(FCPATH . 'uploads/csv')) {
			mkdir(FCPATH . 'uploads/csv', 0777, TRUE);
		}

		$config = [
			'upload_path' 	=> "uploads/csv/",
			'allowed_types' => "text/plain|text/csv|csv",
			'remove_spaces' => TRUE,
			'max_size' 		=> "5000",
			'file_name' 	=> "ar_bb_" . date('d-m-Y_H_i_s')
		];

		$this->load->library('upload', $config);

		if ($this->upload->do_upload('file')) {
			$upload = $this->upload->data();

			$this->load->library('parsecsv');

			$this->parsecsv->auto($config['upload_path'] . '/' . $upload['file_name']);

			$rows = $this->parsecsv->data;

			$failed = $success = 0;

			if ($rows) {
				$royalties = [];

				foreach ($rows as $key => $row) {
					if (empty($row)) {
						$failed++;
						continue;
					}

					if (strtolower($row['Instrument Status']) != 'processed') {
						$failed++;
						continue;
					}

					$bank_users = $this->bank_model->get_all([
						'account_number' => $row['Receiver Account Number'],
					])['rows'];

					foreach ($bank_users as $bank_info) {
						if (empty($bank_info)) continue;

						if (preg_match('/^(?P<date>\d{1,2})\/(?P<month>\d{1,2})\/(?P<year>\d{4})\s(?P<hour>\d{1,2}):(?P<minute>\d{1,2}):(?P<second>\d{1,2})$/', trim($row['Sent By DateTime']), $matches)) {
							$row['Sent By DateTime'] = vsprintf('%s-%s-%s %s:%s:%s', [
								$matches['year'],
								$matches['month'],
								$matches['date'],
								$matches['hour'],
								$matches['minute'],
								$matches['second'],
							]);
						}

						$author_earnings = $this->author_earning_model->get_all([
							'author_id'	=> $bank_info['user_id'],
							'status'	=> 2,
						])['rows'] ?? [];

						foreach ($author_earnings as $info) {
							if (empty($info) || $info['status'] != 2) continue;

							$order_info = $this->order_model->get($info['order_id']);
							$order_site_info = $this->site_model->get($order_info['site_id']);

							if (strtolower($order_site_info['country_code']) !== 'in') {
								$info['amount'] = $info['amount'] * get_exchange_rate($order_site_info['currency_code']);
							}

							if (isset($royalties[$info['author_id']])) {
								$royalties[$info['author_id']]['user_id'] = $info['author_id'];
								$royalties[$info['author_id']]['amount'] += $info['amount'];
								$royalties[$info['author_id']]['author_earning_ids'][] = $info['id'];
							} else {
								$royalties[$info['author_id']]['user_id'] = $info['author_id'];
								$royalties[$info['author_id']]['amount'] = $info['amount'];
								$royalties[$info['author_id']]['author_earning_ids'] = [$info['id']];
							}

							log_kb([
								'id'				=> $info['id'],
								'status'			=> 1,
								'processed_by'		=> (int)$this->session->userdata('user_id'),
								'date_processed'	=> date('Y-m-d H:i:s', strtotime($row['Sent By DateTime'])),
							]);

							$this->author_earning_model->edit($info['id'], [
								'status'			=> 1,
								'processed_by'		=> (int)$this->session->userdata('user_id'),
								'date_processed'	=> date('Y-m-d H:i:s', strtotime($row['Sent By DateTime'])),
							]);
						}
					}

					$success++;
				}

				log_kb($royalties);
				// pr($royalties);

				$this->load->model('user/UserTransaction_model', 'user_transaction_model');

				foreach ($royalties as $user_id => $item) {
					$this->user_transaction_model->add([
						'user_id'				=> (int)$user_id,
						'amount'				=> round($item['amount']),
						'author_earning_ids'	=> implode(',', $item['author_earning_ids']),
						'approved_by'			=> (int)$this->session->userdata('user_id'),
						'status'				=> 1,
					]);
				}

				log_kb([
					'AuthorRoyalty::processed::' => sprintf(_l('success:: %s failed:: %s'), $success, $failed)
				]);

				$data['success'] 		= sprintf(_l('success:: %s failed:: %s'), $success, $failed);

				unlink(FCPATH . 'uploads/csv/' . $upload['file_name']);
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $this->upload->display_errors();
		}

		output_json($data);
	}
}
