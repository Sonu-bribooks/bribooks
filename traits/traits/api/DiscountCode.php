<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait DiscountCode {
	public function applyDiscount() {
		$this->form_validation->set_rules('discount_code', _l('discount_code'), 'trim|required|min_length[3]|max_length[60]');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			// Step 1. Validate Discount code
			// Step 2. Update lead site_id
			// Step 3. Send new billing plan

			if ($site_info = $this->site_model->getSiteByDiscountCode($this->input->post('discount_code'))) {
				$lead_info = $this->lead_model->get($this->input->post('lead_id'));

				$this->lead_model->edit($lead_info['id'], [
					'site_id'	=> (int)$site_info['id'],
				]);

				$lead_info = $this->lead_model->get($this->input->post('lead_id'));

				$this->json['success'] 	= _l('discount_code_applied_successfully');
				$this->json['amount'] 	= self::_getFormattedAmount($lead_info);
				$this->json['lead_id'] 	= (int)$lead_info['id'];
				$this->json['redirect'] = self::_generatePaymentLink(
					$lead_info['id'],
					'premium'
				);
			} else {
				$this->json['error'] = _l('invalid_discount_code');
			}
		}

		$this->setOutput();
	}

	public function verifyDiscountCode() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[' . implode(',', array_keys(EMI_CHARGE)) . ']');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);
		$this->form_validation->set_rules('discount_code', _l('discount_code'), 'trim|required|min_length[8]|max_length[16]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			if($this->input->post('discount_code') && in_array($this->input->post('discount_code'), DEMO_DISCOUNT_CODES)) {
				if($this->input->post('lead_id') && $this->input->post('discount_code')) {
					$this->lead_model->edit($this->input->post('lead_id'), [
						'discount_code'		=> $this->input->post('discount_code'),
						'mobile_verified'	=> 1
					]);
				}

				$this->json['success'] 	= _l('verified');
			} else if ($this->input->post('discount_code')) {
				$this->json['error'] = _l('enter_valid_code');
			} else {
				$this->json['error'] = '';
				$this->json['success'] = '';
			}
		}

		$this->setOutput();
	}
}
