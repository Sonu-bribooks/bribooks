<?php defined('BASEPATH') or exit('No direct script access allowed');

trait RejectedBook {
	public function rejected_book($param1 = '', $param2 = '') {
		$data['page_name'] 		= 'rejected_book/index';
		$data['page_title'] 	= _l('rejected_book');
		$data['navigation'] 		= 'nav';
		$data['status'] 			= 0;
		$data['action_ajax'] 	= site_url('admin/ajax_rejected_book');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_rejected_book() {
		$json['data'] = [];

		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->rejected_book_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$printer_info = $this->user_model->get($result['printer_id']);

			$json['data'][] = [
				'sn'			=> $filter_data['start'] + 1 + $key,
				'id'			=> $result['id'],
				'book'			=> $result['book'],
				'version'		=> $result['version'],
				'option'		=> $result['option'],
				'quantity'		=> $result['quantity'],
				'printer'		=> $printer_info['first_name'] ?? '',
				'comment'		=> self::_getRejectedComment($result),
				'status'		=> _sd($result['quantity'] ? 1 : 0),
				'date_added'	=> formatDate($result['date_added']),
				'date_modified'	=> formatDate($result['date_modified']),
				'actions'		=> ''/*[
					'id' 		=> $result['id'],
					'status' 	=> $result['status'] ?? 0
				]*/,
			];
		}

		output_json($json);
	}

	public function export_rejected_book($currency_id = 47) {
		$json = [];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('filter_printer_id'))) {
			$filter_data['assign_printer_id'] = (int)$this->input->get('filter_printer_id');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		$filter_data['currency_id'] = (int)$currency_id;

		$results = $this->rejected_book_model->get_all($filter_data)['rows'] ?? [];

		$rejected_books = [];

		$sn = 1;

		foreach ($results as $result) {
			$printer_info = $this->user_model->get($result['printer_id']);

			$rejected_books[] = [
				'sn'			=> $sn,
				'id'			=> $result['id'],
				'book'			=> $result['book'],
				'version'		=> $result['version'],
				'option'		=> $result['option'],
				'quantity'		=> $result['quantity'],
				'printer'		=> $printer_info['first_name'] ?? '',
				'status'		=> _os($result['status']),
				'date_added'	=> $result['date_added'],
			];

			$sn++;
		}

		self::_downloadCsv($rejected_books, 'rejected_books');

		output_json($json);
	}

	private function _getRejectedComment($item = []) {
		$comments = [];

		foreach ($this->rejected_book_log_model->get_all([
			'printer_id'	=> $item['printer_id'],
			'book_id'		=> $item['book_id'],
			'version'		=> $item['version'],
			'option'		=> $item['option'],
		])['rows'] ?? [] as $item) {
			$agent_info = !empty($item['manager_id'])
				? $this->user_model->get($item['manager_id'])
				: [];

			$comments[] = vsprintf('%s %s : %s - %s', [
				$agent_info['first_name'] ?? '',
				$agent_info['last_name'] ?? '',
				$item['comment'],
				formatDate($item['date_added']),
			]);
		}

		return implode("\n", $comments);
	}
}
