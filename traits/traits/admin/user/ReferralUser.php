<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ReferralUser {
	public function referral_user($param1 = NULL, $param2 = NULL) {
		

		$data['page_name'] 		= 'referral_user/index';
		$data['page_title'] 	= _l('referral_users');
		$data['action_ajax'] 	= site_url('admin/ajax_referral_user');

		$this->load->view('backend/index', $data);
	}

	public function ajax_referral_user() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->user_referral_list_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'referral_name'		=> $result['referral_first_name'].' '.$result['referral_last_name'],
				'user_name'			=> $result['first_name'].' '.$result['last_name'],
				'date_added'		=> formatDate($result['date_added']),
				'source'		    => $result['source'],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
