<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait Logistic {
	public function logisticdashboardint() {
		$data['page_name'] 	= 'logistic';
		$data['page_title'] = _l('logistic');

		$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

		$results = $this->student_model->get_by_role_id(10);

		$data['users'] = [];

		foreach ($results as $item) {
			$data['users'][] = [
				'name'	=> $item['first_name'] . ' ' . $item['last_name'],
				'total'	=> $this->order_packing_log_model->get_all([
					'user_id'		=> (int)$item['id'],
					'month'			=> date('Y-m-d'),
				])['total'] ?? 0,
				'new'	=> $this->order_packing_log_model->get_all([
					'user_id'		=> (int)$item['id'],
					'date_added'	=> date('Y-m-d'),
				])['total'] ?? 0,
			];
		}

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}
}
