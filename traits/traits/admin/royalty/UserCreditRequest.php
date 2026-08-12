<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait UserCreditRequest {
	public function user_credit_request() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['sites'] 			= [];
		$data['country'] 		= $this->country_model->get_all()['rows'] ?? [];
		$data['site_id'] 		= (int)$this->input->get('site_id');
		$data['country_name'] 	= $this->input->get('country');

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> 0,
		]);
		array_unshift($data['country'], [
			'name'	=> _l('default'),
			'id'	=> ''
		]);

		$data['action_ajax'] 	= base_url('admin/ajax_user_credit_request');
		$data['page_name'] 		= 'user_credit_request';
		$data['page_title'] 	= _l('user_credit_request');

		$this->load->view('backend/index', $data);
	}

	public function ajax_user_credit_request() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'user_credit_request.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		$results = $this->user_credit_request_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$author_currency_code = get_author_currency_code($result['user_id']);

			$user_info = $this->student_model->get($result['user_id']);

			$json['data'][] = [
				'sn'				=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">', //$filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'name'				=> _transfer_type($result['donation_type'], $result['type']),
				'user'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'amount'			=> currency(
					convert_to_local_currency($result['credit'], $result['user_id'], $author_currency_code),
					0,
					$author_currency_code
				),
				'status'			=> _request_status($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'date_modified'		=> formatDate($result['date_modified']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function user_credit_request_export($site_id = 0, $export_type = 'text', $type = 1, $currency_code = 'INR') {
		$this->load->library('Encrypt_lib', 'encrypt_lib');

		$filter_data = [
			'ne_status'		=> 1,
			'type'			=> (int)$type,
			'currency_code'	=> $currency_code,
		];

		// if (ENVIRONMENT !== 'production') return;

		if ($site_id) {
			// $filter_data['user_site_id'] = $site_id;
		}

		$job_id = $this->user_credit_redeem_job_model->get_all([
			'user_id'		=> (int)$this->session->userdata('user_id'),
			'date_added'	=> date('Y-m-d'),
		])['rows'][0]['id'] ?? 0;

		if (empty($job_id)) {
			$job_id = $this->user_credit_redeem_job_model->add([
				'user_id'		=> (int)$this->session->userdata('user_id'),
			]);
		}

		$filter_data['date_added_le'] = date('Y-m') . '-20';

		$results = $this->user_credit_request_model->get_all($filter_data)['rows'] ?? [];

		$rows = [];

		foreach ($results as $key => $item) {
			// if ($type == 1 && $currency_code === 'INR') {
			// 	$item['credit'] = convert_to_local_currency($item['credit'], $item['user_id'], 'INR');
			// }

			if ($item['credit'] > 0) {
				$bank_info = $this->bank_model->get_all([
					'user_id' => $item['user_id'],
				])['rows'][0] ?? [];

				if (!empty($bank_info)) {
					$bank_info['account_number'] = $this->encrypt_lib->decrypt($bank_info['account_number']);

					if (isset($rows[$bank_info['account_number']])) {
						$rows[$bank_info['account_number']]['amount'] += round($item['credit'], 2);
						$rows[$bank_info['account_number']]['payment_details_1'][] = (string)$item['id'];

						if (!in_array($item['user_id'], $rows[$bank_info['account_number']]['payment_details_2'])) {
							$rows[$bank_info['account_number']]['payment_details_2'][] = (string)$item['user_id'];
						}
					} else {
						$rows[$bank_info['account_number']] = [
							'user_id'			=> $item['user_id'],
							'bank_name'			=> $bank_info['bank_name'],
							'branch_name'		=> $bank_info['branch_name'],
							'name'				=> $bank_info['name'],
							'ifsc_code'			=> mb_strtoupper($bank_info['ifsc_code']),
							'account_number'	=> $bank_info['account_number'],
							'amount'			=> round($item['credit'], 2),
							'currency_code'		=> $item['currency_code'],
							'date_added'		=> $item['date_added'],
							'payment_details_1'	=> [(string)$item['id']],
							'payment_details_2'	=> [(string)$item['user_id']],
							'payment_details_3'	=> [],
							'payment_details_4'	=> [],
						];
					}

					empty($item['status']) && $this->user_credit_request_model->edit($item['id'], [
						'status'			=> 2,
						'processing_by'		=> (int)$this->session->userdata('user_id'),
						'date_processing'	=> date('Y-m-d H:i:s'),
					]);
				}
			}
		}

		$payments = $sort_order = [];
		$total_amount = 0;

		foreach ($rows as $account_number => $item) {
			// Redeem data
			$redeem_id = $this->user_credit_redeem_model->get_all([
				'bank_account_number' 	=> $this->encrypt_lib->encrypt($item['account_number']),
				'status' 				=> 0,
			])['rows'][0]['id'] ?? 0;

			if (empty($redeem_id)) {
				$redeem_id = $this->user_credit_redeem_model->add([
					'user_credit_redeem_job_id'	=> (int)$job_id,
					'user_ids'					=> json_encode($item['payment_details_2']),
					'amount'					=> (double)$item['amount'],
					'bank_account_name'			=> $item['name'],
					'bank_account_number'		=> $this->encrypt_lib->encrypt($item['account_number']),
					'bank_name'					=> $item['bank_name'],
					'bank_branch_name'			=> $item['branch_name'],
					'bank_ifsc_code'			=> $item['ifsc_code'],
					'user_credit_request_ids'	=> json_encode($item['payment_details_1']),
				]);
			} else {
				$this->user_credit_redeem_model->edit($redeem_id, [
					'user_credit_redeem_job_id'	=> (int)$job_id,
					'user_ids'					=> json_encode($item['payment_details_2']),
					'amount'					=> (double)$item['amount'],
					'bank_account_name'			=> $item['name'],
					'bank_name'					=> $item['bank_name'],
					'bank_branch_name'			=> $item['branch_name'],
					'bank_ifsc_code'			=> $item['ifsc_code'],
					'user_credit_request_ids'	=> json_encode($item['payment_details_1']),
				]);
			}

			if ($type == 2) {
				$author_info = $this->student_model->get($item['user_id']);

				$payments[] = [
					'author_name'	=> $author_info['first_name'] . ' ' . $author_info['last_name'],
					'amount'		=> round($item['amount'], 2),
					'currency'		=> $item['currency_code'],
					'request_date'	=> formatDate($item['date_added']),
				];
			} else {
				if (preg_match('/kotak/i', $item['bank_name'])) {
					$payment_type = 'IFT';
				} else {
					$payment_type = 'NEFT';
				}

				$payments[] = [
					'Client_Code (refer setup mail)' => 'YBOOKS',
					'Product_Code (refer setup mail )' => 'VPAY',
					'Payment_Type ( IFT(kotak),NEFT(other bank any amount),RTGS( more than 2 lakh other bank),IMPS( upto 5 lakh instatnt payment other bank ))' => $payment_type,
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
					'Beneficiary_Acc_No (receiver account number)' => ($export_type == 'text' ? $item['account_number'] : '\'' . $item['account_number'] . '\''),
					'Location' => '',
					'Print_Location' => '',
					'Instrument_Number' => '',
					'Ben_Add1' => '',
					'Ben_Add2' => '',
					'Ben_Add3' => '',
					'Ben_Add4' => '',
					'Beneficiary_Email' => '',
					'Beneficiary_Mobile' => '',
					'Debit_Narration ( to show in own bank statement) maxcimum 40 characters only' => 'Author Stipend from BriBooks',
					'Credit_Narration (to show in Receiver bank statement maxcimum 40 characters only)' => 'Author Stipend from BriBooks',
					'Payment Details 1' => $job_id,
					'Payment Details 2' => $redeem_id,
					'Payment Details 3' => '',
					'Payment Details 4' => '',
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
			}

			$total_amount += $item['amount'];

			$sort_order[] = round($item['amount'], 2);
		}

		array_multisort($sort_order, SORT_DESC, $payments);

		// pr($payments);

		$this->user_credit_redeem_job_model->edit($job_id, [
			'description'	=> json_encode([
				'total'			=> count($payments),
				'total_amount'	=> $total_amount,
			]),
		]);

		if ($export_type == 'text') {
			if (ENVIRONMENT === 'production') {
				self::_generateTextBankData($payments, $job_id);
			} else {
				self::_generateCsvBankData($payments, $job_id);
			}
		} else {
			self::_generateCsvBankData($payments, $job_id);
		}

		return;
	}

	private function _generateTextBankData($results = [], $job_id = 0) {
		if (ENVIRONMENT !== 'production') return;

		$rows = [];

		foreach ($results as $key => $value) {
			$rows[] = implode('~', array_values($value));
		}

		$filename = FCPATH . 'uploads/csv/royalties' . $job_id . '.txt';

		$fp = fopen($filename, 'w');
		fwrite($fp, implode("\n", $rows));
		fclose($fp);

		self::_saveToSftpServer($filename);

		redirect('admin/user_credit_request');
	}

	private function _saveToSftpServer($localfile = '') {
		if (ENVIRONMENT !== 'production') return;

		$this->load->library('Sftp_lib', 'sftp_lib');
		$this->sftp_lib->initialize([
			'hostname'	=> '54.173.224.108',
			'username'	=> 'kotakbbcoluser',
			'password'	=> 'ldgfhjgj$65263@3~1@!hsg):;><?+',
			'debug'		=> TRUE,
		]);

		$this->sftp_lib->upload($localfile, sprintf('YBOOKS%s', basename($localfile)));

		is_file($localfile) && unlink($localfile);
	}

	private function _generateCsvBankData($results = [], $job_id = 0) {
		$filename = 'royalties_' . $job_id . '.csv';

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

	public function import_bank_data() {
		if (!is_dir(FCPATH . 'uploads/csv')) {
			mkdir(FCPATH . 'uploads/csv', 0777, TRUE);
		}

		$this->load->library('Encrypt_lib', 'encrypt_lib');

		$config = [
			'upload_path' 	=> 'uploads/csv/',
			'allowed_types' => 'text/plain|text/csv|csv',
			'remove_spaces' => TRUE,
			'max_size' 		=> 5000,
			'file_name' 	=> 'ar_bb_' . date('d-m-Y_H_i_s')
		];

		$this->load->library('upload', $config);

		if ($this->upload->do_upload('file')) {
			$upload = $this->upload->data();

			$this->load->library('parsecsv');

			$this->parsecsv->auto($config['upload_path'] . '/' . $upload['file_name']);

			$rows = $this->parsecsv->data;

			$failed = $success = $job_id = 0;

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

					$redeems = $this->user_credit_redeem_model->get_all([
						'bank_account_number' 	=> $this->encrypt_lib->encrypt(trim($row['Receiver Account Number'])),
						'status' 				=> 0,
					])['rows'] ?? [];

					log_kb([
						'$redeems'	=> $redeems,
						'$row'		=> $row,
					]);

					if (empty($redeems)) {
						$failed++;
						continue;
					}

					foreach ($redeems as $key => $redeem) {
						if (empty($redeem)) continue;

						if (empty($key)) {
							// update redeem job status
							$this->user_credit_redeem_job_model->edit($redeem['job_id'], [
								'status'	=> 1,
							]);
						}

						// update redeem status
						$this->user_credit_redeem_model->edit($redeem['id'], [
							'status'	=> 1,
						]);

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

						$user_credit_request_ids = json_decode($redeem['user_credit_request_ids'], true);

						foreach ($user_credit_request_ids as $user_credit_request_id) {
							$item = $this->user_credit_request_model->get($user_credit_request_id);

							if (empty($item) || $item['status'] != 2) continue;

							if (isset($royalties[$item['user_id']])) {
								$royalties[$item['user_id']]['user_id'] = $item['user_id'];
								$royalties[$item['user_id']]['amount'] += $item['credit'];
								$royalties[$item['user_id']]['user_credit_request_ids'][] = $item['id'];
							} else {
								$royalties[$item['user_id']]['user_id'] = $item['user_id'];
								$royalties[$item['user_id']]['amount'] = $item['credit'];
								$royalties[$item['user_id']]['user_credit_request_ids'] = [$item['id']];
							}

							log_kb([
								'id'				=> $item['id'],
								'status'			=> 1,
								'processed_by'		=> (int)$this->session->userdata('user_id'),
								'date_processed'	=> date('Y-m-d H:i:s', strtotime($row['Sent By DateTime'])),
							]);

							$this->user_credit_request_model->edit($item['id'], [
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
						'author_earning_ids'	=> implode(',', $item['user_credit_request_ids']),
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
