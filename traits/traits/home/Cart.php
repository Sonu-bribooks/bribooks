<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Cart {
	public function cart() {
		$json['items']		= $this->cart_lib->getItems();
		$json['total']		= $this->cart_lib->getTotal();

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function addCart() {
		$json = [];

		if ($this->input->post('enrol_ids') && is_array($this->input->post('enrol_ids'))) {
			$this->cart_lib->empty();

			foreach ($this->input->post('enrol_ids') as $enrol_id) {
				$this->cart_lib->add($enrol_id);
			}

			$json['items']		= $this->cart_lib->getItems();
			$json['total']		= $this->cart_lib->getTotal();

			$json['success'] 	= _li('added_to_cart');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function emptyCart() {
		$this->cart_lib->empty();

		$json['success'] = _l('cart_is_empty');

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function confirmPay() {
		if ($items = $this->cart_lib->getItems()) {
			$total = $this->cart_lib->getTotal();

			$order_id = $this->order_model->add([
				'enrol_ids'			=> json_encode(array_map(function($item) {
					return $item['enrol_id'];
				}, $items)),
				'amount'			=> (double)$total['total'],
				'bulk_renewal'		=> 1,
				'payment_type'		=> 'razorpay',
				'user_id'			=> (int)$this->session->user_id,
			]);

			$json['order'] = [
				'name'			=> implode(', ', array_map(function($item) {
					return $item['course'];
				}, $items)),
				'description'	=> '',
				'image'			=> site_url('uploads/system/logo-dark.png'),
				'amount'		=> $total['total'],
				'order_id'		=> $this->order_model->generateOrderId($order_id, $total['total']),
				'key'			=> RAZORPAY_KEY,
				'currency_code'	=> $this->config->item('site_country_code'),
				'id'			=> $order_id,
			];

			$this->order_model->edit($order_id, ['extra' => $json['order']['order_id']]);

			$student_info = $this->student_model->get($this->session->user_id)->row_array();

			$json['order']['user'] = [
				'name'			=>	$this->session->name,
				'email'			=>	$student_info['email'] ?? '',
				'mobile'		=>	$student_info['mobile'] ?? '',
				'user_id'		=>	$this->session->user_id
			];

			$this->session->set_userdata('order', $json['order']);
		} else {
			$json['error']		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function updateBulkTransaction() {
		$json = [];

		if (($order_id 		= $this->input->post('order_id')) &&
			($payment_id	= $this->input->post('payment_id')) &&
			($signature 	= $this->input->post('signature')) &&
			($id 			= $this->input->post('id'))
		) {

			if ($this->order_model->verifyOrder([
				'order_id'		=> $this->input->post('order_id'),
				'payment_id'	=> $this->input->post('payment_id'),
				'signature'		=> $this->input->post('signature'),
			]) && ($order_info = $this->order_model->get($this->input->post('id')))) {
				foreach($order_info['enrol_ids'] as $enrol_id) {
					$this->enrol_model->enrol([
						'enrol_id'		=> (int)$enrol_id,
						'payment_type'	=> 'razorpay',
						'order_id'		=> (int)$id,
						'amount'		=> (double)$this->enrol_model->getRenewalAmount($enrol_id),
					]);
				}

				$result = $this->order_model->edit($this->input->post('id'), [
					'transaction_id' => $this->input->post('payment_id')
				]);

				$total = $this->cart_lib->getTotal();

				$this->session->set_userdata([
					'order_amount'		=> $total['total'],
					'order_payment_id'	=> htmlentities($this->input->post('payment_id'), ENT_QUOTES, 'UTF-8'),
					'order_course'		=> ''
				]);

				$this->cart_lib->empty();

				$json['redirect'] 	= site_url('home/success');
			} else {
				$json['error']		= _('error_invalid_transaction');
			}
		} else {
			$json['error']		= _('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
