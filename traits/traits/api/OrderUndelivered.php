<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait OrderUndelivered {
	public function getUndeliveredOrder() {
        $this->form_validation->set_rules('order_code', _l('order_code'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
            $order_info  = $this->order_model->getOrderByCode($this->input->post('order_code'));

            if (empty($order_info) || ($order_info['status'] ?? 0) != 20) {
                $this->json['error'] = _li('order_not_found');
			    return;
            }

            if (empty($user_info  = $this->user_model->get($order_info['user_id']))) {
                $this->json['error'] = _li('user_not_found');
			    return;
            }

            $this->json['order'] = [
                'id'            =>  $order_info['id'],
                'order_code'    =>  $order_info['order_code'],
                'name'          => sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
                'email'         =>  $user_info['email'],
                'mobile'        =>  $user_info['mobile'],
            ];

		}
	}

	public function scheduledUndeliveredOrder() {
        $this->form_validation->set_rules('order_id', _l('order_id'), [
			'trim',
			'numeric',
			'required'
		]);

        $this->form_validation->set_rules('email', _l('email'), [
			'trim',
		]);

        $this->form_validation->set_rules('mobile', _l('mobile'), [
			'trim',
		]);

        $this->form_validation->set_rules('slot', _l('slot'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
		    $this->load->model('order/OrderUndelivered_model', 'order_undelivered_model');

            $order_info  = $this->order_model->get($this->input->post('order_id'));

            if (empty($order_info) || ($order_info['status'] ?? 0) != 20) {
                $this->json['error'] = _li('order_not_found');
			    return;
            }

            if (!empty($info = $this->order_undelivered_model->get_all([
                'order_id' => $this->input->post('order_id')
            ])['rows'][0] ?? [])) {
                $this->json['error'] = _l('you_have_already_submitted');
				return;
            }

            $this->order_undelivered_model->add([
                'order_id'  => $this->input->post('order_id'),
                'email'     => $this->input->post('email') ?? '',
                'mobile'    => $this->input->post('mobile') ?? '',
                'slot'      => $this->input->post('slot') ?? '',
                'status'    => 20,
            ]);

            $this->json['success'] = _l('you_have_submitted_successfully');
		}
	}
}
