<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class MessageTemplateListener_lib {
	public static function userOtp(...$params) {
		list($data) = $params;

		log_kb([
			'Event::userOtp' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('Alert_model');

		if (!empty($data['mobile']) &&
			strlen($data['mobile']) == 12 &&
			substr($data['mobile'], 0, 2) == 91
		) {
			$data['site_id'] = 1;
		}

		$data['site_id'] 	= $data['site_id'] == 1 ? 1 : $CI->config->item('default_site_id');

		log_kb([
			'Event::userOtp::template' => [$data]
		]);

		$payload = [
			'mobile'	=> $data['mobile'] ?? '',
			'email'		=> $data['email'] ?? '',
			'otp'		=> $data['otp'],
		];

		$CI->Alert_model->genericMessageTemplate([
			'code'				=> 'user_otp',
			'site_id'			=> $data['site_id'] ?? 0,
			'email'		   		=> $data['email'] ?? '',
			'mobile'		  	=> $data['mobile'] ?? '',
			'includes'			=> [$data['type'] ?? 'sms'],
			'data'				=> $payload,
		]);
	}

	public static function deliveredMedallionOrder(...$params) {
		list($data) = $params;

		log_kb([
			'Event::deliveredMedallionOrder' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$CI->load->model('user/User_model', 'user_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->medallion_order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 	= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 	= $CI->user_model->get($order_info['user_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$products 	= $CI->medallion_order_model->getProducts($order_info['id']);

		$product_text = '';

		foreach ($products as $key => $product) {
			$product_text .= sprintf('<li> %s for your amazing book %s </li>', $product['medallion_name'], $product['book_name']);
		}

		$data = [
			'first_name'		=> $user_info['first_name'],
			'date'			  	=> date('M j, Y', strtotime($order_info['date_completed'])),
			'order_products'	=> $product_text
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'delivered_medallion_order',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function afterDeliveredMedallionOrder(...$params) {
		list($data) = $params;

		log_kb([
			'Event::afterDeliveredMedallionOrder' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$CI->load->model('user/User_model', 'user_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->medallion_order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 	= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 	= $CI->user_model->get($order_info['user_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'first_name'		=> $user_info['first_name'],
			'link'			  	=> sprintf('%sugcconfirmation?uid=%d&code=%s&oid=%s',
				USER_URL,
				$user_info['id'],
				$user_info['verification_code'],
				$order_info['order_code']
			),
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'after_delivered_medallion_order',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function subscriptionPurchase(...$params) {
		list($data) = $params;

		log_kb([
			'Event::subscriptionPurchase' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$CI->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');

		$CI->load->model('Alert_model');

		if (empty($info = $CI->user_subscription_model->get($data['id']))) return;

		if (empty($user_info = $CI->user_model->get($info['user_id']))) return;

		if (empty($subscription_info = $CI->subscription_plan_model->get($info['subscription_plan_id']))) return;

		$site_id = strtolower($subscription_info['country_code']) != 'in' ? 2 : 1;

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'name'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'first_name'		=> $user_info['first_name'],
			'start_date'		=> date('M d, Y', strtotime($info['start_date'])),
			'end_date'			=> date('M d, Y', strtotime($info['end_date'])),
			'subscription_plan'	=> $subscription_info['name'],
			'currency'			=> $subscription_info['symbol'],
			'currency_code'		=> $subscription_info['code'],
			'price'				=> $subscription_info['price'],
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $info['id'],
			'code'				=> 'subscription_purchase',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function subscriptionExpiryReminder(...$params) {
		list($data) = $params;

		log_kb([
			'Event::subscriptionExpiryReminder' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$CI->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');

		$CI->load->model('Alert_model');

		if (empty($user_info = $CI->user_model->get($data['user_id'])) || empty($user_info['subscription_plan_id'] ?? 0)) return;

		if (empty($user_subscription_info = $CI->user_subscription_model->get_all([
			'status' 				=> 1,
			'user_id' 				=> $user_info['id'],
			'subscription_plan_id'	=> $user_info['subscription_plan_id'],
		])['rows'][0] ?? [])) return;

		$expire_date		= date('Y-m-d H:i:s', strtotime('+' . ($data['day'] + 1) . ' days'));

		if ($user_subscription_info['end_date'] >= $expire_date) return;

		if (empty($subscription_info = $CI->subscription_plan_model->get($user_subscription_info['subscription_plan_id']))) return;

		$site_id = strtolower($subscription_info['country_code']) != 'in' ? 2 : 1;

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'name'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'first_name'		=> $user_info['first_name'],
			'end_date'			=> date('M d, Y H:i:s', strtotime($user_subscription_info['end_date'])),
			'subscription_plan'	=> $subscription_info['name'],
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $subscription_info['id'],
			'code'				=> 'subscription_expiry_reminder',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function subscriptionExpired(...$params) {
		list($data) = $params;

		log_kb([
			'Event::subscriptionExpired' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$CI->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');

		$CI->load->model('Alert_model');

		if (empty($user_info = $CI->user_model->get($data['user_id']))) return;

		if (!empty($user_subscription_info = $CI->user_subscription_model->get_all([
			'status' 				=> 1,
			'user_id' 				=> $user_info['id'],
			'subscription_plan_id'	=> $data['subscription_plan_id'],
		])['rows'][0] ?? [])) return;

		log_kb([
			'Event::subscriptionExpired::user_subscription_info' => $user_subscription_info
		]);

		if (empty($subscription_info = $CI->subscription_plan_model->get($data['subscription_plan_id']))) return;

		log_kb([
			'Event::subscriptionExpired::subscription_info' => $subscription_info
		]);

		$site_id = strtolower($subscription_info['country_code']) != 'in' ? 2 : 1;

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'name'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'first_name'		=> $user_info['first_name'],
			'subscription_plan'	=> $subscription_info['name'],
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $subscription_info['id'],
			'code'				=> 'subscription_expiry_reminder',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function medallionFeedback(...$params) {
		list($data) = $params;

		log_kb([
			'Event::medallionFeedback' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('medallion/Medallion_model', 'medallion_model');
		$CI->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$CI->load->model('medallion/MedallionFeedback_model', 'medallion_feedback_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('event/Event_model', 'event_model');

		$CI->load->model('Alert_model');

		if (empty($info = $CI->medallion_feedback_model->get($data['id']))) return;

		log_kb([
			'Event::medallionFeedback::info' => $info
		]);

		$order_info = $CI->medallion_order_model->get($info['order_id']);

		$site_id = strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;

		$user_info 		= $CI->user_model->get($info['user_id']);
		$event_info 	= $CI->event_model->get($info['event_id'] ?? 0);
		$medallion_info = $CI->medallion_model->get($info['medallion_id']);

		$data = [
			'user_name'			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'media_type'		=> strtoupper($info['type']),
			'date'			  	=> date('M j, Y h:i A', strtotime($info['date_added'])),
			'event'				=> $event_info['name'] ?? 'General',
			'medallion'			=> $medallion_info['name'] ?? '',
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $info['id'],
			'code'				=> 'medallion_feedback',
			'site_id'			=> $site_id,
			'email'		   		=> 'communication@bribooks.com',
			'mobile'		  	=> '',
			'data'				=> $data,
		]);
	}

	public static function eventInviteVerified(...$params) {
		list($data) = $params;

		log_kb([
			'Event::eventInviteVerified' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('Alert_model');

		if (!empty($data['mobile']) &&
			strlen($data['mobile']) == 12 &&
			substr($data['mobile'], 0, 2) == 91
		) {
			$data['site_id'] = 1;
		}

		$data['site_id'] 	= $data['site_id'] == 1 ? 1 : $CI->config->item('default_site_id');

		log_kb([
			'Event::eventInviteVerified' => [$data]
		]);

		$payload = [
			'mobile'	=> $data['mobile'] ?? '',
			'email'		=> $data['email'] ?? '',
		];

		$CI->Alert_model->genericMessageTemplate([
			'code'				=> 'event_invite_verified',
			'site_id'			=> $data['site_id'] ?? 0,
			'email'		   		=> $data['email'] ?? '',
			'mobile'		  	=> $data['mobile'] ?? '',
			'data'				=> $payload,
		]);
	}
}
