<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Subscription_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('user/Student_model');
		$this->load->model('user/User_model');
		$this->load->model('user/UserLimit_model');
		$this->load->model('order/Order_model');
		$this->load->model('address/Address_model');

		$this->load->model('subscription/SubscriptionPlan_model');
		$this->load->model('subscription/SubscriptionOrder_model');
		$this->load->model('subscription/SubscriptionPayment_model');
		$this->load->model('subscription/UserSubscription_model');

		$this->load->model('shipping/ShippingCredit_model');
		$this->load->model('shipping/ShippingCreditHistory_model');

		$this->load->model('common/Site_model');
		$this->load->model('event/EventUser_model');

		$this->student_model 					= $this->CI->Student_model;
		$this->user_model 						= $this->CI->User_model;
		$this->user_limit_model 				= $this->CI->UserLimit_model;
		$this->order_model 						= $this->CI->Order_model;
		$this->address_model 					= $this->CI->Address_model;

		$this->subscription_plan_model 			= $this->CI->SubscriptionPlan_model;
		$this->subscription_order_model 		= $this->CI->SubscriptionOrder_model;
		$this->subscription_payment_model 		= $this->CI->SubscriptionPayment_model;
		$this->user_subscription_model 			= $this->CI->UserSubscription_model;

		$this->shipping_credit_model 			= $this->CI->ShippingCredit_model;
		$this->shipping_credit_history_model 	= $this->CI->ShippingCreditHistory_model;

		$this->site_model 						= $this->CI->Site_model;
		$this->event_user_model 				= $this->CI->EventUser_model;
	}

	public function addShippingCredit($order_id = 0) {
		$order_info = $this->subscription_order_model->get($order_id);

		if (empty($order_info)) return;

		$subscription_info = $this->user_subscription_model->get_all([
			'order_id' 	=> (int)$order_id,
			'user_id' 	=> (int)$order_info['user_id'],
		])['rows'][0] ?? [];

		if (strtotime($subscription_info['start_date']) > time()) {
			log_kb(['Subscription::addShippingCredit::skipping:: ' => $order_info, $subscription_info]);
			return;
		}

		log_kb(['Subscription::addShippingCredit::' => $order_info, $subscription_info]);

		if (strtotime($subscription_info['end_date']) > time()) {
			$plan_info = $this->subscription_plan_model->get($subscription_info['subscription_plan_id']);
			$site_info = $this->site_model->get($order_info['site_id']);

			if (!empty($this->shipping_credit_history_model->get_all([
				'user_id'		=> (int)$order_info['user_id'],
				'order_id'		=> (int)$order_info['id'],
				'type'			=> 0,
			])['rows'][0] ?? [])) {
				return;
			}

			if (
				!empty($shipping_credit_info = $this->shipping_credit_model->get_all([
					'user_id'		=> (int)$order_info['user_id'],
					'country_code'	=> $site_info['country_code'],
				])['rows'][0] ?? [])
			) {
				$this->shipping_credit_model->edit($shipping_credit_info['id'], [
					'credit'		=> (double)($shipping_credit_info['credit'] + $plan_info['shipping_credit']),
				]);
			} else {
				$this->shipping_credit_model->add([
					'user_id'		=> (int)$order_info['user_id'],
					'country_code'	=> $site_info['country_code'],
					'credit'		=> (double)$plan_info['shipping_credit'],
				]);
			}

			$this->shipping_credit_history_model->add([
				'user_id'		=> (int)$order_info['user_id'],
				'order_id'		=> (int)$order_info['id'],
				'credit'		=> (double)$plan_info['shipping_credit'],
				'type'			=> 0,
			]);
		}
	}

	public function useShippingCredit($order_id = 0) {
		$order_info = $this->order_model->get($order_id);

		if (empty($order_info)) return;

		$shipping_info = json_decode($order_info['shipping_info'], true);
		$address_info = $this->address_model->get($order_info['address_id']);
		$shipping_info['shipping_credit'] = (double)($shipping_info['shipping_credit'] ?? 0);

		if (empty($shipping_info['shipping_credit'])) return;

		if (
			empty($shipping_credit_info = $this->shipping_credit_model->get_all([
				'user_id'		=> (int)$order_info['user_id'],
				'country_code'	=> _get_country_code_by_name($address_info['country']),
			])['rows'][0] ?? [])
		) return;

		if (!empty($this->shipping_credit_history_model->get_all([
			'user_id'		=> (int)$order_info['user_id'],
			'order_id'		=> (int)$order_info['id'],
			'credit'		=> (double)$shipping_info['shipping_credit'],
			'type'			=> 1,
		])['rows'] ?? [])) return;

		$this->shipping_credit_model->edit($shipping_credit_info['id'], [
			'credit'	=> (double)($shipping_credit_info['credit'] - $shipping_info['shipping_credit'])
		]);

		$this->shipping_credit_history_model->add([
			'user_id'		=> (int)$order_info['user_id'],
			'order_id'		=> (int)$order_info['id'],
			'credit'		=> (double)$shipping_info['shipping_credit'],
			'type'			=> 1,
		]);
	}

	public function checkCanPublish($book = []) {
		if (validate_user_subscription()) return true;
		if (!empty($book['version'])) return true;

		$user_event_info = $this->event_user_model->get_all([
			'user_id'					=> $book['user_id'],
			'is_active_book_writing'	=> 1
		])['rows'][0] ?? [];

		if (!empty($user_event_info)) {
			$user_limit_info 	= $this->user_limit_model->get_all([
				'user_id' 	=> $book['user_id'],
				'event_id' 	=> $user_event_info['event_id'],
			])['rows'][0] ?? [];
		} else {
			$user_limit_info 	= $this->user_limit_model->get_all([
				'user_id' 	=> $book['user_id'],
				'event_id' 	=> 0,
			])['rows'][0] ?? [];
		}

		if (!empty($user_event_info)) {
			if (empty($user_event_info['publishing_limit'])) return true;

			if (($user_limit_info['current'] ?? 0) < ($user_event_info['publishing_limit'] ?? 0)) {
				return true;
			} else {
				return $user_limit_info['can_publish'] ?? false;
			}
		}

		if (
			!empty($user_limit_info) &&
			(strtolower($user_limit_info['country_code']) != strtolower($this->config->item('site_country_code')))
		) {
			return false;
		}

		$country_limit = $this->config->item('site_publishing_limit') ?? 0;

		if (empty($country_limit)) return true;

		if (!empty($country_limit)) {
			if (($user_limit_info['current'] ?? 0) < ($country_limit ?? 0)) {
				return true;
			} else {
				return $user_limit_info['can_publish'] ?? false;
			}
		}

		return false;
	}
	
	public function updatePublishingLimit($book = []) {
		if (empty($user_info = $this->user_model->get($book['user_id']))) return;

		$user_event_info = $this->event_user_model->get_all([
			'user_id'					=> $user_info['id'],
			'is_active_book_writing'	=> 1
		])['rows'][0] ?? [];

		if (!empty($user_event_info)) {
			$user_limit_info = $this->user_limit_model->get_all([
				'user_id' 	=>  $user_info['id'],
				'event_id' 	=> $user_event_info['event_id'],
			])['rows'][0] ?? [];

			if (empty($user_limit_info)) {
				$this->user_limit_model->add([
					'user_id' 			=>  $user_info['id'],
					'event_id' 			=> $user_event_info['event_id'],
					'country_code' 		=> $user_event_info['country_code'],
					'publishing_limit' 	=> $user_event_info['publishing_limit'],
					'current'			=> 1,
					'can_publish'		=> 0,
				]);
			} else {
				$this->user_limit_model->updateCanPublish($user_limit_info['id']);
			}
		} else {
			$user_limit_info = $this->user_limit_model->get_all([
				'user_id' 	=>  $user_info['id'],
				'event_id' 	=> 0,
			])['rows'][0] ?? [];

			if (empty($user_limit_info)) {
				$this->user_limit_model->add([
					'user_id' 			=>  $user_info['id'],
					'country_code' 		=> $this->config->item('site_country_code'),
					'publishing_limit' 	=> $this->config->item('site_publishing_limit'),
					'current'			=> 1,
					'can_publish'		=> 0,
				]);
			} else {
				$this->user_limit_model->updateCanPublish($user_limit_info['id']);
			}
		}
	}
}
