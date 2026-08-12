<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait WebhookOrderAlert {
	public function processWebhookOrderCron($data = []) {
		log_kb([
			'WebhookOrder::processWebhookOrderCron Data:: ' => $data
		]);

		self::_processSubscriptionOrder($data);

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('order/Coupon_model', 'coupon_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		if (!($order_info = $this->db->get_where('order', [
			'ext_order_id'	=> $data['order_id'],
			'status'		=> 0,
		])->row_array())) return;

		log_kb([
			'WebhookOrder::_createPaymentData Data:: ' => $data
		]);

		// return;

		// Setting default site to order site
		$this->site_model->initConfig($order_info['site_id']);

		// Update subscription order status
		$this->order_model->edit($order_info['id'], [
			'order_code'			=> 'BB-' . time() . '-' . $order_info['id'] . 'I' . $order_info['user_id'],
			'status'				=> 1,
			'ext_transaction_id'	=> $data['id'],
			'ext_raw_data'			=> json_encode($data),
		]);

		$this->session->set_userdata('user_id', $order_info['user_id']);
		$this->session->set_userdata('shipping_info', json_decode($order_info['user_id'], true));
		// done upto this

		// Add order product
		$cart_items = $this->cart_lib->getSessionItems($order_info['id']);

		$used_credit = 0;

		foreach ($cart_items as $item) {
			$this->cart_lib->updateSessionItem($item['id'], [
				'status'	=> 1,
			]);

			$used_credit += $item['total']['used_credit'];

			$book_info = $this->book_model->get($item['book']['id']);

			$filter_data['version'] 	= (int)$book_info['version'];
			$filter_data['order_id'] 	= (int)$order_info['id'];
			$filter_data['product_id'] 	= (int)$item['book']['id'];
			$filter_data['total'] 		= (double)$item['total']['total'];

			if (empty($this->order_product_model->get_all($filter_data)['total'])) {
				$item['total']['option']['name'] = str_replace('_', ' ', $item['total']['option']['name']);

				$this->order_model->addProduct([
					'version'			=> (int)$book_info['version'],
					'order_id'			=> (int)$order_info['id'],
					'product_id'		=> (int)$item['book']['id'],
					'quantity'			=> (int)$item['quantity'],
					'price'				=> (double)$item['total']['price'],
					'credit'			=> (int)$item['total']['credit'],
					'used_credit'		=> (int)$item['total']['used_credit'],
					'credit_discount'	=> (double)$item['total']['credit_discount'],
					'ppp_total'			=> (double)$item['total']['ppp_total'],
					'subtotal'			=> (double)$item['total']['subtotal'],
					'total'				=> (double)$item['total']['total'],
					'weight'			=> (double)$item['total']['weight'],
					'option'			=> json_encode($item['total']['option']),
					'option_type'		=> get_option_type($item['total']['type']),
				]);
			}
		}

		// update coupon used count
		if (!empty($order_info['coupon_id'])) {
			$this->coupon_model->updateUsedCount($order_info['coupon_id']);
		}

		// Create payment
		$this->payment_model->add([
			'order_id'				=> (int)$order_info['id'],
			'user_id'				=> (int)$order_info['user_id'],
			'currency_id'			=> (int)$order_info['currency_id'],
			'currency_code'			=> $order_info['currency_code'],
			'currency_symbol'		=> $order_info['currency_symbol'],
			'provider'				=> $order_info['provider'],
			'amount'				=> (double)$order_info['total'],
			'status'				=> 1,
		]);

		$this->cart_lib->empty();

		$this->student_model->updateHardCopy(
			$order_info['user_id'],
			$used_credit
		);

		$this->session->unset_userdata([
			'user_id',
			'couriers',
			'shipping_address_id',
			'shipping_courier_id',
			'shipping_info',
		]);

		if (!empty($cart_items)) {
			$this->alert_model->invoiceOrder($order_info['id']);

			if (in_array($order_info['order_type'], [1, 2])) {
				CI_Events::trigger('order_created', [
					'order_id'	=> $order_info['id']
				]);

				CI_Events::trigger('printer_assigned', [
					'order_id'	=> $order_info['id']
				]);
			}
		}
	}

	private function _processSubscriptionOrder($data = []) {
		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');
		$this->load->model('subscription/SubscriptionPayment_model', 'subscription_payment_model');
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('Student_model', 'student_model');

		if (!($order_info = $this->db->get_where('subscription_order', [
			'ext_order_id'	=> $data['order_id'],
			'status'		=> 0,
		])->row_array())) return;

		log_kb([
			'WebhookOrder::_processSubscriptionOrder Data:: ' => $data
		]);

		$plan_info = $this->subscription_plan_model->get($order_info['subscription_plan_id']);

		// Update subscription order status
		$this->subscription_order_model->edit($order_info['id'], [
			'status'				=> 1,
			'ext_transaction_id'	=> $data['id'] ?? '',
			'ext_raw_data'			=> json_encode($data),
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
			'description'			=> $data['id'] ?? '',
			'status'				=> 1,
		]);

		// Update User Subscription

		$months = (int) $plan_info['duration_month'] ?? 0;

		$user_subscription_id = $this->user_subscription_model->add([
			'user_id'				=> (int)$order_info['user_id'],
			'order_id'				=> (int)$order_info['id'],
			'subscription_plan_id'	=> (int)$order_info['subscription_plan_id'],
			'status'				=> 1,
			'start_date'			=> date('Y-m-d H:i:s'),
			'end_date'				=> date('Y-m-d H:i:s', strtotime(" +{$months} months")),
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
}
