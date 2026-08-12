<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Payment {
	public function payment($range = '') {

		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'payment/index';
		$data['page_title'] 	= _l('payment');

		if ($range != '') {
			$date_range					= $this->input->get('date_range');
			$date_range					= explode('-', $date_range);
			$data['timestamp_start'] 	= strtotime(trim($date_range[0]));
			$data['timestamp_end']	 	= strtotime('+1 days', strtotime(trim($date_range[1])));
		} else {
			$data['timestamp_start'] 	= strtotime('-1 month', time());
			$data['timestamp_end']	 	= strtotime('+1 days', strtotime(date("m/d/Y")));
		}

		$data['action_ajax'] 	= site_url('admin/ajax_payment?range=' . $this->input->get('date_range'));

		$this->load->view('backend/index', $data);
	}

	public function ajax_payment() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start')))?(int)$this->input->get('start'):0,
			'limit'				=> (!empty($this->input->get('length')))?(int)$this->input->get('length'):20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('range')) {
			$date_range					= $this->input->get('range');
			$date_range					= explode('-', $date_range);
			$filter_data['date_start'] 	= date('Y-m-d', strtotime(trim($date_range[0])));
			$filter_data['date_end']	= date('Y-m-d', strtotime('+1 days', strtotime(trim($date_range[1]))));
		}

		$results = $this->payment_model->getList($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['first_name'].' '.$result['last_name'],
				'email'					=> $result['email'],
				'mobile'				=> $result['mobile'],
				'amount'				=> $result['currency_code'] . $result['amount'],
				'provider'				=> $result['provider'],
				'status'				=> $result['status'] == 0 ? _l('pending') : _l('success'),
				'date_added'			=> formatDate($result['date_added']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
