<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SubscriptionAlert {
	public function expireSubscriptionCron() {
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$this->load->model('shipping/ShippingCredit_model', 'shipping_credit_model');
		$this->load->library('Subscription_lib');

		$results = $this->user_subscription_model->get_all([
			'expire_date_lt'=> date('Y-m-d H:i:s'),
			'status'		=> 1,
		])['rows'] ?? [];

		log_kb(['expireSubscriptionCron' => $results]);

		foreach ($results as $item) {
			$this->user_model->edit($item['user_id'], [
				'subscription_plan_id'	=> 0,
			]);

			$this->user_subscription_model->edit($item['id'], [
				'status'				=> 0,
			]);

			$shipping_credit_info = $this->shipping_credit_model->get_all([
				'user_id'				=> $item['user_id'],
			])['rows'][0] ?? [];

			if (!empty($shipping_credit_info)) {
				$this->shipping_credit_model->edit($shipping_credit_info['id'], [
					'credit'			=> 0,
				]);
			}

			self::_activateNewSubscription($item['user_id']);

			// $this->cron_model->add([
			// 	'code'			=> 'expireSubscriptionMessageAlert_' . $item['user_id'],
			// 	'action'		=> 'alert_model->expireSubscriptionMessageAlert',
			// 	'data'			=> [$item['user_id'], $item['subscription_plan_id']],
			// 	'alert_date'	=> date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime(date('Y-m-d H:i:s')))),
			// ]);

			CI_Events::trigger('subscription_expired', [
				'user_id'				=> $item['user_id'],
				'subscription_plan_id' 	=> $item['subscription_plan_id']
			]);
		}
	}

	private function _activateNewSubscription($user_id = 0) {
		$subscription_info = $this->user_subscription_model->get_all([
			'user_id'		=> (int)$user_id,
			'expire_date_gt'=> date('Y-m-d H:i:s'),
			'status'		=> 1,
		])['rows'][0] ?? [];

		$this->user_model->edit($subscription_info['user_id'], [
			'subscription_plan_id'	=> $subscription_info['subscription_plan_id'],
		]);

		log_kb(['ActivateNewSubscription' => $subscription_info]);

		$this->subscription_lib->addShippingCredit($subscription_info['order_id']);
	}

	public function expireSubscriptionMessageAlert($user_id = 0, $subscription_plan_id = 0) {
		if (empty($user_info = $this->user_model->get($user_id))) return;

		if (!empty($user_subscription_info = $this->user_subscription_model->get_all([
			'status' 				=> 1,
			'user_id' 				=> $user_info['id'],
			'subscription_plan_id'	=> $subscription_plan_id,
		])['rows'][0] ?? [])) return;

		CI_Events::trigger('access_log', [
			'module'	=> sprintf('expireSubscriptionMessageAlert_%s_%s_%s', $user_id, $subscription_plan_id, ($user_subscription_info['id'] ?? 0))
		]);

		$template = 'bb_subscription_expired';

		$title = self::formatEmailSubject($template, 1, [
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'parent_name'		=> $user_info['parent_name'] ?? ''
		]) ?? '';

		if (empty($title)) return;

		$data['title']			= $title;
		$data['heading']		= '';
		$data['subheading']		= '';
		$data['content']		= self::formatEmailMessage($template, [
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name']
		], 1);
		$data['site_id']		= 1;
		$data['parent_id']		= '';
		$data['site_code']		= '';
		$data['link']			= '';
		$data['link_text']		= '';
		$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

		$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

		$mobile = $user_info['mobile'];
		$email 	= $user_info['email'];

		self::email(
			$email,
			$data['title'],
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[]
		);

		self::sendOnextelWhatsappMessage(
			$mobile,
			[
				'template_id'		=> '01kev9wksmnbbra97cerzy962k',
				'parameters'	=> [
					$user_info['first_name'] . ' ' . $user_info['last_name']
				]
			],
		);
	}

	public function expireSubscriptionFomo($subscription_plan_id = 0, $day = 0) {
		if (!empty($results = $this->user_subscription_model->get_all([
			'start' 				=> 0,
			'limit' 				=> 500,
			'status' 				=> 1,
			'subscription_plan_id'	=> $subscription_plan_id,
			'end_date'				=> date('Y-m-d', strtotime('+' . ($day - 1) . ' days'))
		])['rows'] ?? [])) {
			foreach ($results as $result) {
				if (empty($user_info = $this->user_model->get($result['user_id']))) continue;

				if (!empty($user_subscription_info = $this->user_subscription_model->get_all([
					'status' 				=> 1,
					'user_id' 				=> $user_info['id'],
					'subscription_plan_id'	=> $subscription_plan_id,
					'expire_date_gt'		=> date('Y-m-d H:i:s', strtotime('+' . ($day + 1) . ' days'))
				])['rows'][0] ?? [])) continue;

				CI_Events::trigger('subscription_expiry_reminder', [
					'user_id'	=> $user_info['id'],
					'day' 		=> $day
				]);

				// $template = 'bb_subscription_expired_info';

				// $title = self::formatEmailSubject($template, 1, [
				// 	'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				// 	'parent_name'		=> $user_info['parent_name'] ?? ''
				// ]) ?? '';

				// if (empty($title)) continue;

				// CI_Events::trigger('access_log', [
				// 	'module'	=> sprintf('expireSubscriptionFomo_%s_%s_%s', $result['user_id'], $subscription_plan_id, $result['id'])
				// ]);

				// $data['title']			= $title;
				// $data['heading']		= '';
				// $data['subheading']		= '';
				// $data['content']		= self::formatEmailMessage($template, [
				// 	'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				// 	'datetime'			=> $result['end_date']

				// ], 1);
				// $data['site_id']		= 1;
				// $data['parent_id']		= '';
				// $data['site_code']		= '';
				// $data['link']			= '';
				// $data['link_text']		= '';
				// $data['unsubscribe_url']= vsprintf(USER_URL . 'unsubscribe?uid=%s&code=%s', [
				// 	$user_info['id'],
				// 	$user_info['verification_code'],
				// ]);

				// $message 				= $this->load->view('common/mail/templates/site/general', $data, true);

				// $mobile = $user_info['mobile'];
				// $email 	= $user_info['email'];

				// self::email(
				// 	$email,
				// 	$data['title'],
				// 	$message,
				// 	[],
				// 	(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				// 	[]
				// );

				// self::sendOnextelWhatsappMessage(
				// 	$mobile,
				// 	[
				// 		'template_id'	=> '01kev9pm7ym7dtr66m2mks8rkv',
				// 		'parameters'	=> [
				// 			$user_info['first_name'] . ' ' . $user_info['last_name'],
				// 			date('M j, Y', strtotime($result['end_date']))
				// 		]
				// 	],
				// );
			}
		}
	}
}
