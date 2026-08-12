<?php defined('BASEPATH') or exit('No direct script access allowed');

trait PrinterAlert {
	public function assignedAlert($id = 0, $data = []) {
		$this->cron_model->add([
			'code'			=> 'assignedAlertCron_' . $id,
			'action'		=> 'alert_model->assignedAlertCron',
			'data'			=> [$id, $data],
			'alert_date'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function assignedAlertCron($id = 0, $stats = []) {
		$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');

		if ($info = $this->user_model->get($id)) {
			$assignment_info = $this->printer_assignment_model->get($stats['assignment_id']);

			$data['title']			= vsprintf(_li('%s, has assined new orders for the printing'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= $this->load->view('common/mail/part/assigned_alert', [
				'printer'			=> $info['first_name'] . ' ' . $info['last_name'],
				'stats'				=> $stats,
				'assignment_code'	=> $assignment_info['code'],
			], true);
			$data['link']			= base_url();
			$data['link_text']		= _l('login');

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			self::email(
				$info['email'],
				$data['title'],
				$message,
				(ENVIRONMENT == 'production') ? [$info['alternate_email'],'adarsh@bribooks.com'] : [],
				[]
			);
		}
	}

	public function zipReadyToDownloadAlert($id, $csv_file) {
		$this->cron_model->add([
			'code'			=> 'zipReadyToDownloadAlertCron_' . $id,
			'action'		=> 'alert_model->zipReadyToDownloadAlertCron',
			'data'			=> [$id, ['csv_file' => $csv_file]],
			'alert_date'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function zipReadyToDownloadAlertCron($id = 0, $cron_data = []) {
		log_kb(['zipReadyToDownloadAlertCron' => [$id, $cron_data]]);

		if ($info = $this->user_model->get($id)) {
			$data['title']			= vsprintf(_li('%s, your_files_are_ready_for_the_downloading'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= $this->load->view('common/mail/part/zip_ready_to_download_alert', [
				'printer'		=> $info['first_name'] . ' ' . $info['last_name'],
			], true);
			$data['link']			= base_url();
			$data['link_text']		= _l('login');

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			$attachment				= [
				FCPATH . $cron_data['csv_file'],
			];

			$cc = (ENVIRONMENT === 'production')
				? ['adarsh@bribooks.com']
				: [];

			if (ENVIRONMENT === 'production' && !empty($info['alternate_email'])) {
				$cc[] = $info['alternate_email'];
			}

			self::email(
				$info['email'],
				$data['title'],
				$message,
				$cc,
				[],
				$attachment
			);
		}
	}

	public function qaqcCompleteCron($id = 0, $data = []) {
		$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');
		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
		$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');

		if (empty($assignment_info = $this->printer_assignment_model->get($id)))
			return;

		$assignment_code = $assignment_info['code'];

		$filter_data = [];
		$filter_data['assignment_id'] = $assignment_info['id'];
		$filter_data['assign_printer_id'] = $assignment_info['printer_id'];

		$results = $this->printer_stats_model->printerAssignDataSortByBalanced($filter_data);

		$printer_info = $this->user_model->get($assignment_info['printer_id']);
		if (empty($printer_info))
			return;

		$qa_qc_logs = [];

		$filename = 'qa_qc_printer_' . date('Y_m_d_H_i_s') . '.csv';

		foreach ($results['rows'] ?? [] as $key => $result) {
			$type = strtolower(json_decode($result['option'], 1)['name']);

			$filter_data = [];
			$filter_data['assignment_id'] = $assignment_info['id'];
			$filter_data['book_id'] = $result['product_id'];
			$filter_data['version'] = $result['version'];
			$filter_data['option'] = $type;
			$filter_data['assign_printer_id'] = $assignment_info['printer_id'];

			$printer_assign_results = $this->printer_stats_model->printerAssignData($filter_data);
			$printer_assign_results = $printer_assign_results['rows'][0] ?? [];

			$book_info = $this->book_version_model->getByVersion($result['product_id'], $result['version']);

			$filter_data = [];
			$filter_data['sort'] = 'qa_qc_lots.id';
			$filter_data['order'] = 'ASC';
			$filter_data['assignment_id'] = $assignment_info['id'];
			$filter_data['book_id'] = $result['product_id'];
			$filter_data['version'] = $result['version'];
			$filter_data['option'] = $type;

			$qa_qc_lots_results = $this->qa_qc_lots_model->get_all($filter_data);

			$qa_qc_lots_info = !empty($qa_qc_lots_results['rows']) ? end($qa_qc_lots_results['rows']) : [];

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $result['product_id'],
				'version'	=> $result['version'],
			])['total'] ?? 0;

			$sku = _o_b_code($result['product_id'], $result['version'], $type);

			$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;

			$accepted_short_count = $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

			$rejected_count = $qa_qc_lots_info['rejected_quantity'] ?? 0;

			$balance_count = (int)$result['quantity']-(int)$accepted_count-(int)$accepted_short_count;

			$qa_qc_logs[] = [
				'assignment_code'	=> $assignment_code,
				'book_id'			=> $result['product_id'],
				'book_sku'			=> $sku,
				'book_title'		=> $book_info['name'] ?? '',
				'author_name'		=> $book_info['author_name'] ?? '',
				'pages'				=> ($total_pages * 2 + 1) ?? '0',
				'version'			=> $result['version'],
				'option'			=> $type,
				'book_quantity'		=> $printer_assign_results['quantity'] ?? '0',
				'accepted_quantity'	=> $accepted_count ?? '0',
				'accepted_short_quantity'	=> $accepted_short_count ?? '0',
				'rejected_quantity'	=> $rejected_count ?? '0',
				'balance_quantity'	=> $balance_count ?? '0',
				'qa_qc_date_added'	=> formatDate($qa_qc_lots_info['date_added'])
			];
		}

		$email = $printer_info['email'];

		$attachment = self::_downloadCsv(array_values($qa_qc_logs), 'qa_qc_' . $assignment_code . '_');

		$title = 'BriBooks QA QC Status - Assignment Code [ ' . $assignment_code . ' ]';

		$content = 'Hi,<br />Please check the attached file as QA QC Status.<br />Regards,<br />Team BriBooks';

		self::email(
			$email,
			$title,
			$content,
			(ENVIRONMENT == 'production') ? [$printer_info['alternate_email'],'adarsh@bribooks.com'] : [],
			[],
			FCPATH . $attachment
		);

		unlink(FCPATH . $attachment);
	}
}
