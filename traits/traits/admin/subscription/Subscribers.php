<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Subscribers {
	public function subscribers() {

		$data['page_name'] 		= 'subscribers/index';
		$data['page_title'] 	= _l('subscribers');
		$data['action_ajax'] 	= site_url('admin/ajax_subscriber');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 20
		];
		$this->load->view('backend/index', $data);
	}

	public function ajax_subscriber() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start'))) ? (int)$this->input->get('start') : 0,
			'limit'				=> (!empty($this->input->get('length'))) ? (int)$this->input->get('length') : 20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->subscription_payment_model->getList($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['first_name'] . ' ' . $result['last_name'],
				'email'					=> $result['email'],
				'mobile'				=> $result['mobile'],
				'plan_name'				=> $result['plan_name'],
				'plan_price'			=> sprintf('%s %s', $result['currency_code'], $result['plan_price']),
				'paid_amount'			=> sprintf('%s %s', $result['currency_code'], $result['amount']),
				'start_date'			=> formatDate($result['start_date']),
				'end_date'				=> formatDate($result['end_date']),
				'status'				=> $result['status'] == 0 ? _l('pending') : _l('success'),
				'paid_on'				=> formatDate($result['date_added']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
