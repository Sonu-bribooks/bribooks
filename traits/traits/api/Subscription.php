<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Subscription {
	public function getPlans() {
		if (!$this->json) {
			$plans 			= [];

			$country_code = $this->config->item('site_country_code');

			$filter = [
				'special'       => 0,
				'status'        => 1,
				'sort'          => 'subscription_plan.sort_order',
				'order'         => 'ASC',
			];

			// first check user country wise plan
			$filter['country_code'] = $country_code;

			$plans = $this->subscription_plan_model->get_all($filter)['rows'] ?? [];

			// if empty plan and not already US (default)
			if (empty($plans) && $country_code !== 'US') {
				$filter['country_code'] = 'US';

				$plans = $this->subscription_plan_model->get_all($filter)['rows'] ?? [];
			}

			foreach ($plans as $key => $plan) {
				$plans[$key]['benefits'] 	= json_decode($plan['benefits'] ?? [], true);
			}

			$this->json['plans'] = $plans;
		}
	}

	private function _checkHigherSubription($plan_id = 0) {
		$user_info = $this->student_model->get($this->session->userdata('user_id'));

		if (!empty($user_info['subscription_plan_id'])) {
			$plan_info = $this->subscription_plan_model->get($plan_id);
			$user_plan_info = $this->subscription_plan_model->get($user_info['subscription_plan_id']);

			return $user_plan_info['price'] > $plan_info['price'];
		}

		return false;
	}

	public function applySubscriptionCoupon() {
		$this->form_validation->set_rules('order_id', _l('order_id'), [
			'trim',
			'required',
			'numeric',
			['subscription_order', [$this->validate_model, 'subscription_order']]
		]);

		$this->form_validation->set_rules('coupon', _l('coupon'), 'trim|required|min_length[6]|max_length[20]');

		self::_runFormValidation();

		if (!$this->json) {
			if (($student_info = $this->student_model->get($this->session->userdata('user_id')))) {
				if (
					($site_info = $this->site_model->get($student_info['site_id'])) &&
					($order_info = $this->subscription_order_model->get($this->input->post('order_id'))) &&
					$order_info['amount'] > 0
				) {
					if (
						$coupon_info = $this->coupon_model->getByCouponCode([
							'code'			=> $this->input->post('coupon'),
							'coupon_type'	=> 'subscription',
							'item_id'		=> $order_info['subscription_plan_id']
						])
					) {
						$plan_info = $this->subscription_plan_model->get($order_info['subscription_plan_id']);

						if ($coupon_info['discount_type'] == 2) {
							// percentage discount
							$discounted_amount = $plan_info['price'] * (1 - $coupon_info['discount'] / 100);
						} else {
							// flat discount
							$discounted_amount = $plan_info['price'] - $coupon_info['discount'];
						}

						$this->subscription_order_model->edit(
							$order_info['id'],
							[
								'amount'	=> $discounted_amount,
								'coupon'	=> json_encode([
									'coupon_type'	=> $coupon_info['coupon_type'],
									'code'			=> $coupon_info['code'],
									'discount_type'	=> $coupon_info['discount_type'],
									'discount'		=> $coupon_info['discount'],
								]),
							],
						);

						// update coupon used count
						$this->coupon_model->updateUsedCount($coupon_info['id']);

						self::_createSubscriptionOrderData(
							$order_info['id'],
							$discounted_amount,
							$plan_info
						);
					} else {
						$this->json['error'] = _l('invalid_coupon');
					}
				} else {
					$this->json['error'] = _l('unknown_error');
				}
			} else {
				$this->json['login'] = true;
			}
		}
	}

	public function createSubscriptionOrder() {
		$this->form_validation->set_rules('plan_id', _l('plan_id'), [
			'trim',
			'required',
			'numeric',
			['subscription_plan', [$this->validate_model, 'subscription_plan']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			// Check if user is logged in
			if (!$this->session->userdata('user_id')) {
				$this->json['success'] = _l('login_required_to_continue');
				$this->json['login'] = true;
			} elseif (self::_checkHigherSubription($this->input->post('plan_id'))) {
				// Check if user subscribed to better plan
				$this->json['error'] = _li('You_have_a_better_plan');
			} else {
				$plan_info = $this->subscription_plan_model->get($this->input->post('plan_id'));

				$order_id = $this->subscription_order_model->add([
					'user_id'				=> (int)$this->session->userdata('user_id'),
					'subscription_plan_id'	=> (int)$plan_info['id'],
					'amount'				=> (double)$plan_info['price'],
					'currency_id'			=> (int)$plan_info['currency_id'],
					'provider'				=> $this->config->item('site_payment_gateway'),
					'status'				=> 0,
				]);

				self::_createSubscriptionOrderData(
					$order_id,
					$plan_info['price'],
					$plan_info
				);

				CI_Events::trigger('access_log', [
					'module'	=> 'subscription_order_created_' . (int)$order_id . ($this->input->post('source') ?? '')
				]);
			}
		}
	}

	private function _createSubscriptionOrderData($order_id = 0, $amount = 0, $plan_info = []) {
		$user_info = $this->user_model->get($this->session->userdata('user_id'));

		$ext_order_id = $amount > 0 ? $this->order_model->generateOrderId(
			'S-' . $order_id,
			$amount,
			$plan_info['code']
		) : 'free';

		$this->subscription_order_model->edit($order_id, [
			'ext_order_id'	=> $ext_order_id,
		]);

		$this->json['order'] = [
			'provider'		=> $this->config->item('site_payment_gateway'),
			'key'			=> $this->config->item('site_payment_gateway') === 'razorpay'
				? RAZORPAY_KEY
				: STRIPE_KEY,
			'id'			=> $order_id,
			'amount'		=> $amount * 100,
			'currency'		=> $plan_info['code'],
			'name'			=> $plan_info['name'],
			'description'	=> $plan_info['name'],
			'order_id'		=> $ext_order_id,
			'user'			=> [
				'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'email'		=> $user_info['email'],
				'mobile'	=> $user_info['mobile'],
				'id'		=> $user_info['id'],
			],
			'address'		=> '',
		];
	}

	private function _createSubscriptionPaymentData() {
		$order_info = $this->subscription_order_model->get($this->input->post('order_id'));
		$plan_info = $this->subscription_plan_model->get($order_info['subscription_plan_id']);

		$data = $this->input->post('payment');

		$transaction_key = 'razorpay_payment_id';

		if ($order_info['provider'] == 'phonepe') {
			$transaction_key = 'transactionId';
		}

		if (strpos($order_info['provider'], 'stripe') !== false) {
			$transaction_key = 'id';
		}

		// Update subscription order status
		$this->subscription_order_model->edit($order_info['id'], [
			'status'				=> 1,
			'ext_transaction_id'	=> $data['data'][$transaction_key] ?? 'free',
			'ext_raw_data'			=> json_encode($data['data'] ?? []),
		]);

		// Update user
		$this->student_model->updateSubscriptionPlan(
			$order_info['user_id'],
			$order_info['subscription_plan_id'],
			$plan_info['hard_copy']
		);

		// Create payment
		$this->subscription_payment_model->add([
			'user_id'				=> (int)$order_info['user_id'],
			'subscription_plan_id'	=> (int)$order_info['subscription_plan_id'],
			'subscription_order_id'	=> (int)$order_info['id'],
			'amount'				=> (double)$order_info['amount'],
			'currency_id'			=> (int)$order_info['currency_id'],
			'provider'				=> $order_info['provider'] ?? '',
			'description'			=> $data['data'][$transaction_key] ?? 'free',
			'status'				=> 1,
		]);

		// Update User Subscription
		$user_subscription_info = $this->user_subscription_model->get_all([
			'user_id'				=> $order_info['user_id'],
			'subscription_plan_id'	=> $order_info['subscription_plan_id'],
			'status'				=> 1,
		])['rows'][0] ?? [];

		$duration = (int)($plan_info['duration_month'] ?? 0);

		$user_subscription_id = $this->user_subscription_model->add([
			'user_id'				=> (int)$order_info['user_id'],
			'order_id'				=> (int)$order_info['id'],
			'subscription_plan_id'	=> (int)$order_info['subscription_plan_id'],
			'status'				=> 1,
			'start_date'			=> !empty($user_subscription_info['end_date'] ?? '') ? date('Y-m-d H:i:s', strtotime($user_subscription_info['end_date'])) : date('Y-m-d H:i:s'),
			'end_date' 				=> !empty($user_subscription_info['end_date'] ?? '')
				? date(
					'Y-m-d H:i:s',
					strtotime($user_subscription_info['end_date'] . " +{$duration} months")
				)
				: date(
					'Y-m-d H:i:s',
					strtotime("+{$duration} months")
				),
		]);

		// update lead status
		// if (($lead_info = $this->lead_model->getByStudentId($this->session->userdata('user_id')))) {
		// 	$this->lead_model->edit($lead_info['id'], [
		// 		'is_converted'	=> 1,
		// 		'status'		=> 4,
		// 	]);
		// }

		$this->db->delete('cart', [
			'user_id'	=> (int)$order_info['user_id'],
			'option' 	=> 'ebook'
		]);

		CI_Events::trigger('access_log', [
			'module'	=> 'subscription_payment_created_' . (int)$order_info['id']
		]);

		CI_Events::trigger('subscription_payment_created', [
			'order_id'	=> (int)$order_info['id']
		]);

		if ($order_info['amount'] > 0) {
			// $this->alert_model->invoiceSubscription($user_subscription_id);
			CI_Events::trigger('subscription_purchase', [
				'id'	=> $user_subscription_id
			]);
		}
	}

	public function createUserSubscription() {
		$this->form_validation->set_rules('order_id', _l('order_id'), [
			'trim',
			'required',
			'numeric',
			['subscription_order', [$this->validate_model, 'subscription_order']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$order_info = $this->subscription_order_model->get($this->input->post('order_id'));
			$plan_info = $this->subscription_plan_model->get($order_info['subscription_plan_id']);

			if (((int)$order_info['amount']) === 0) {
				// update order
				self::_createSubscriptionPaymentData();
				$this->json['success'] = _l('subscribed_successfully');
			} else {
				$data = $this->input->post('payment');

				if ($this->order_model->verifyOrder([
					'order_id'		=> $order_info['ext_order_id'],
					'payment_id'	=> $data['data']['razorpay_payment_id'] ?? '',
					'signature'		=> $data['data']['razorpay_signature'] ?? '',
					'order_info'	=> $order_info,
					'data'			=> $data['data'],
				])) {
					self::_createSubscriptionPaymentData();
					$this->json['success'] = _l('subscribed_successfully');
				} else {
					$this->json['error'] = _l('payment_not_verified');
				}
			}

			CI_Events::trigger('access_log', [
				'module'	=> 'create_user_subscription_' . (int)$order_info['id'] . ($this->input->post('source') ?? '')
			]);
		}
	}

	private function _validateSubscription() {
		if ($this->session->userdata('user_id') &&
			($user_info = $this->user_model->get($this->session->userdata('user_id'))) &&
			($user_subscription_info = $this->user_subscription_model->get_all([
				'user_id'				=> $user_info['id'],
				'subscription_plan_id'	=> $user_info['subscription_plan_id'],
				'status'				=> 1,
			])['rows'][0] ?? []) &&
			strtotime($user_subscription_info['end_date']) > time()
		) {
			return true;
		}

		return false;
	}
}
