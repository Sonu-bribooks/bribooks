<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Competition {
	public function getCompetition() {
		if (!$this->json) {
			$result = $this->competition_model->get(COMPETITION[$this->config->item('site_id')]);

			if (empty($result['status'])) {
				$this->json['error'] = _li('Competition_not_active');
			} elseif (strtotime($result['start_date']) > time()) {
				$this->json['error'] = _li('Competition_not_started');
			} elseif (strtotime($result['end_date']) < time()) {
				$this->json['error'] = _li('Competition_ended!');
			} else {
				$submitted = rand(35, 50);
				$this->json['competition'] = [
					'id'			=> $result['id'],
					'submitted'		=> $submitted,
					'remaining'		=> 100 - $submitted,
					'name'			=> $result['name'],
					'start_date'	=> $result['start_date'],
					'end_date'		=> $result['end_date'],
				];
			}
		}
	}

	public function getCompetitionPrice() {
		$this->form_validation->set_rules('competition_id', _l('competition_id'), [
			'trim',
			'required',
			'numeric',
			['competition', [$this->validate_model, 'competition']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($this->session->userdata('user_id'))) {
				$this->json['login'] = true;
			}

			$result = $this->competition_model->get(COMPETITION[$this->config->item('site_id')]);

			if (empty($result['status'])) {
				$this->json['error'] = _li('Competition_not_active');
			} elseif (strtotime($result['start_date']) > time()) {
				$this->json['error'] = _li('Competition_not_started');
			} elseif (strtotime($result['end_date']) < time()) {
				$this->json['error'] = _li('Competition_ended!');
			} else {
				$this->json['competition'] = [
					'id'			=> $result['id'],
					'name'			=> $result['name'],
					'original_price'=> mb_strtolower($this->config->item('site_country_code')) === 'in' ? 7700 : $result['price'],
					'price'			=> $result['price'],
					'start_date'	=> $result['start_date'],
					'end_date'		=> $result['end_date'],
					'currency'		=> $result['symbol'],
				];
			}
		}
	}

	private function _checkInCompetition($competition_id = 0) {
		return $this->competition_model->checkUser([
			'competition_id'	=> $competition_id,
			'user_id'			=> $this->session->userdata('user_id'),
		]);
	}

	public function createCompetitionOrder() {
		$this->form_validation->set_rules('competition_id', _l('competition_id'), [
			'trim',
			'required',
			'numeric',
			['competition', [$this->validate_model, 'competition']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			// Check if user is logged in
			if (!$this->session->userdata('user_id')) {
				$this->json['success'] = _li('login_required_to_continue');
				$this->json['login'] = true;
			} elseif (self::_checkInCompetition($this->input->post('competition_id'))) {
				$this->json['error'] = _li('you_are_already_in_competition');
			} else {
				$competition_info = $this->competition_model->get($this->input->post('competition_id'));
				$user_info = $this->student_model->get($this->session->userdata('user_id'));

				$order_id = $this->competition_order_model->add([
					'user_id'				=> (int)$this->session->userdata('user_id'),
					'competition_id'		=> (int)$competition_info['id'],
					'amount'				=> (double)$competition_info['price'],
					'currency_id'			=> (int)$competition_info['currency_id'],
					'provider'				=> $this->config->item('site_payment_gateway'),
					'status'				=> 0,
				]);

				$ext_order_id = $competition_info['price'] > 0
					? $this->order_model->generateOrderId(
						'competition_' . $order_id,
						$competition_info['price']
					)
					: 'free'
				;

				$this->competition_order_model->edit($order_id, [
					'ext_order_id'	=> $ext_order_id,
				]);

				$this->json['order'] = [
					'provider'		=> $this->config->item('site_payment_gateway'),
					'key'			=> $this->config->item('site_payment_gateway') === 'razorpay'
						? RAZORPAY_KEY
						: STRIPE_KEY,
					'id'			=> $order_id,
					'amount'		=> $competition_info['price'] * 100,
					'currency'		=> $competition_info['code'],
					'name'			=> $competition_info['name'],
					'description'	=> $competition_info['name'],
					'order_id'		=> $ext_order_id,
					'user'			=> [
						'name'		=>  $user_info['first_name'] . ' ' . $user_info['last_name'],
						'email'		=>  $user_info['email'],
						'mobile'	=>  $user_info['mobile']
					],
					'address'		=> '',
				];
			}
		}
	}

	private function _createCompetitionPaymentData() {
		$order_info = $this->competition_order_model->get($this->input->post('order_id'));
		$user_info = $this->student_model->get($order_info['user_id']);
		$competition_info = $this->competition_model->get($order_info['competition_id']);
		$plan_info = $this->subscription_plan_model->get($competition_info['subscription_plan_id']);

		$data = $this->input->post('payment');

		$transaction_key = $this->config->item('site_payment_gateway') === 'razorpay'
			? 'razorpay_payment_id'
			: 'id';

		// Update competition order status
		$this->competition_order_model->edit($order_info['id'], [
			'status'				=> 1,
			'ext_transaction_id'	=> $data['data'][$transaction_key] ?? 'free',
			'provider'				=> $data['provider'] ?? '',
			'ext_raw_data'			=> json_encode($data['data'] ?? []),
		]);

		// Create payment
		$this->competition_payment_model->add([
			'user_id'				=> (int)$order_info['user_id'],
			'competition_id'		=> (int)$order_info['competition_id'],
			'competition_order_id'	=> (int)$order_info['id'],
			'amount'				=> (double)$order_info['amount'],
			'currency_id'			=> (int)$order_info['currency_id'],
			'provider'				=> $data['provider'] ?? '',
			'description'			=> $data['data'][$transaction_key] ?? 'free',
			'status'				=> 1,
		]);

		// Update User Competition
		$competition_user_id = $this->competition_model->addUser([
			'user_id'				=> (int)$order_info['user_id'],
			'competition_id'		=> (int)$order_info['competition_id'],
			'competition_order_id'	=> (int)$order_info['id'],
		]);

		// Update user if no subscription
		if (empty($user_info['subscription_plan_id'])) {
			$this->student_model->updateSubscriptionPlan(
				$order_info['user_id'],
				$competition_info['subscription_plan_id'],
				$plan_info['hard_copy']
			);
		}

		$user_subscription_id = $this->user_subscription_model->add([
			'user_id'				=> (int)$order_info['user_id'],
			'order_id'				=> (int)$order_info['id'],
			'subscription_plan_id'	=> (int)$competition_info['subscription_plan_id'],
			'status'				=> 1,
			'start_date'			=> date('Y-m-d H:i:s'),
			'end_date'				=> date('Y-m-d H:i:s', strtotime('+1 years')),
		]);

		if ($order_info['amount'] > 0) {
			$this->alert_model->invoiceCompetition($user_subscription_id);
		}
	}

	public function subscribeCompetition() {
		$this->form_validation->set_rules('order_id', _l('order_id'), [
			'trim',
			'required',
			'numeric',
			['competition_order', [$this->validate_model, 'competition_order']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$order_info = $this->competition_order_model->get($this->input->post('order_id'));
			$competition_info = $this->competition_model->get($order_info['competition_id']);

			if (((int)$order_info['amount']) === 0) {
				// update order
				self::_createCompetitionPaymentData();
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
					self::_createCompetitionPaymentData();
					$this->json['success'] = _l('subscribed_successfully');
				} else {
					$this->json['error'] = _l('payment_not_verified');
				}
			}
		}
	}
}
