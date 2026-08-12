<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Coupon {
	public function applyCoupon() {
		$this->form_validation->set_rules('coupon', _l('coupon'), 'trim|max_length[20]');

		self::_runFormValidation();

		if (!$this->json) {
			if (($user_info = $this->user_model->get($this->session->userdata('user_id')))) {
				$this->cart_lib->removeCoupon();

				if (
					$this->input->post('coupon') &&
					($coupon_info = $this->coupon_model->getByCouponCode([
						'code'			=> $this->input->post('coupon'),
						'coupon_type'	=> 'product',
					]))
				) {
					if ($coupon_info['used_count'] >= $coupon_info['used_limit']) {
						return $this->json['error'] = _l('Coupon_code_already_used');
					}

					if (!self::_validateCoupon($coupon_info)) {
						$this->json['error'] = _l('invalid_coupon');
						return;
					}

					if ($this->cart_lib->applyCoupon($this->input->post('coupon'))) {
						self::_getCart();

						CI_Events::trigger('access_log', [
							'module'	=> 'cart_applied_coupon_' . (int)$coupon_info['id']
						]);

						$this->json['success'] = _l('Coupon_applied_successfully');
					} else {
						$this->json['error'] = _l('invalid_coupon');
					}
				} elseif (empty($this->input->post('coupon'))) {
					$this->cart_lib->applyCoupon($this->input->post('coupon'));
					self::_getCart();

					$this->json['success'] = _l('Coupon_removed_successfully');
				} else {
					$this->json['error'] = _l('invalid_coupon');
				}
			} else {
				$this->json['login'] = true;
			}
		}
	}

	private function _validateCoupon($coupon_info = []) {
		if (empty($coupon_info)) return false;

		if (strtotime($coupon_info['date_end']) < time()) return false;

		if (!empty($coupon_info['user_id']) && $coupon_info['user_id'] != $this->session->userdata('user_id')) return false;

		if (!empty($coupon_info['quantity']) || !empty($coupon_info['item_id'])) {
			$items = $this->cart_lib->getItems();

			if (empty($items)) return false;

			foreach ($items as $key => $item) {
				if (!empty($coupon_info['quantity']) &&
					$item['quantity'] >= $coupon_info['quantity'] &&
					(empty($coupon_info['item_id']) || $item['book']['id'] == $coupon_info['item_id'])
				) {
					if (!empty($coupon_info['max_quantity']) && $item['quantity'] > $coupon_info['max_quantity']) {
						return false;
					}

					return true;
				} elseif (empty($coupon_info['quantity']) && $coupon_info['item_id'] == $item['book']['id']) {
					if (!empty($coupon_info['max_quantity']) && $item['quantity'] > $coupon_info['max_quantity']) {
						return false;
					}

					return true;
				}
			}

			return false;
		}

		return true;
	}
}
