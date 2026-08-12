<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AmazonVoucher {
	public function amazon_voucher($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'user',
			'email',
			'currency_code',
			'amount',
			'status',
			'date_added',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->user_credit_request_model->add($this->input->post());
			redirect(base_url('admin/amazon_voucher'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->user_credit_request_model->edit($param2, $this->input->post());
			redirect(base_url('admin/amazon_voucher'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->user_credit_request_model->edit($param2, [
				'status'			=> 1,
				'processed_by'	  	=> $this->session->userdata('user_id'),
				'date_processed'	=> date('Y-m-d H:i:s')
			]);
			redirect(base_url('admin/amazon_voucher'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->user_credit_request_model->delete($param2);
			redirect(base_url('admin/amazon_voucher'), 'refresh');
		}

		$data['page_name'] 		= 'user_credit/index';
		$data['page_title'] 	= _l('amazon_voucher');
		$data['action_ajax'] 	= base_url('admin/ajax_amazon_voucher');
		$data['action_export'] 	= base_url('admin/export_amazon_voucher');

		$data['actions'] 		= [
			[
				'key'	=> 'mark_paid',
				'type' 	=> 'confirm',
				'url'	=> 'admin/amazon_voucher/status/',
			]
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_amazon_voucher() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'type'				=> 3,
		];

		$results = $this->user_credit_request_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$author_currency_code = get_author_currency_code($result['user_id']);

			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'name'				=> _transfer_type($result['donation_type'], $result['type']),
				'user'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'email'				=> $user_info['email'],
				'currency_code'		=> $result['currency_code'],
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
	
	public function export_amazon_voucher() {
		$json = [];

		$results = $this->user_credit_request_model->get_all([
			'type'	  	=> 3,
			'status'	=> 0,
		])['rows'] ?? [];

		$vouchers = [];

		foreach ($results as $result) {
			$user_info			  	= $this->user_model->get($result['user_id']);
			$author_currency_code	= get_author_currency_code($result['user_id']);

			$vouchers[] = [
				'request_id'		=> $result['id'],
				'user_id'			=> $result['user_id'],
				'name'				=> _transfer_type($result['donation_type'], $result['type']),
				'user'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'email'				=> $user_info['email'],
				'currency_code'		=> $result['currency_code'],
				'amount'			=> currency(
					convert_to_local_currency($result['credit'], $result['user_id'], $author_currency_code),
					0,
					$author_currency_code
				),
				'status'			=> _request_status($result['status']),
			];

			$this->user_credit_request_model->edit($result['id'], [
				'status'			=> 2,
				'processing_by'	 	=> $this->session->userdata('user_id'),
				'date_processing'	=> date('Y-m-d H:i:s')
			]);
		}

		self::_downloadCsv($vouchers, 'amazon_vouchers');

		output_json($json);
	}
}
