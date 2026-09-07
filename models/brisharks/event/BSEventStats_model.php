<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSEventStats_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function registered_school($data = []) {
		$this->bsdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->bsdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->bsdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(event_site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->bsdb->where('site._deleted', 0);
		$this->bsdb->where('event_site._deleted', 0);

		$this->bsdb->from('event_site');
		$this->bsdb->join('site', 'site.id=event_site.site_id');

		return $this->bsdb->get()->row()->total;
	}

	public function registered_user_from_school($data = []) {
		$this->bsdb->select('count(distinct user.id) as total');

		if (!empty($data['event_id'])) {
			$this->bsdb->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['paid_user']) && !empty($data['event_id'])) {
			$this->bsdb->join('subscription_payment', ' subscription_payment.user_id = event_user.user_id');
			$this->bsdb->where('subscription_payment.status', 1);
			$this->bsdb->where(sprintf('subscription_payment.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			$this->bsdb->where(sprintf('subscription_payment.date_added <= (select end_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->bsdb->where('user.role_id', 2);
		$this->bsdb->where('user.source', 'Invite_school');
		$this->bsdb->where('user._deleted', 0);
		$this->bsdb->where('event_user._deleted', 0);
	
		$this->bsdb->from('event_user');
		$this->bsdb->join('user', 'user.id=event_user.user_id');

		return $this->bsdb->get()->row()->total;

	}

	public function old_user_enrollemt($data = []) {
		$this->bsdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->bsdb->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->bsdb->where(sprintf('user.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->bsdb->where(sprintf('user.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['paid_user']) && !empty($data['event_id'])) {
			$this->bsdb->join('subscription_payment', ' subscription_payment.user_id = event_user.user_id');
			$this->bsdb->where('subscription_payment.status', 1);
			$this->bsdb->where(sprintf('subscription_payment.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			$this->bsdb->where(sprintf('subscription_payment.date_added <= (select end_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->bsdb->where_in('user.role_id', [2,99]);
		$this->bsdb->where('user._deleted', 0);
		$this->bsdb->where('event_user._deleted', 0);
	
		$this->bsdb->from('event_user');
		$this->bsdb->join('user', 'user.id=event_user.user_id');

		return $this->bsdb->get()->row()->total;
	}

	public function new_user_enrollemt($data = []) {
		$this->bsdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->bsdb->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->bsdb->where(sprintf('user.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['paid_user']) && !empty($data['event_id'])) {
			$this->bsdb->join('subscription_payment', ' subscription_payment.user_id = event_user.user_id');
			$this->bsdb->where('subscription_payment.status', 1);
			$this->bsdb->where(sprintf('subscription_payment.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			$this->bsdb->where(sprintf('subscription_payment.date_added <= (select end_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->bsdb->where('user.role_id', 2);
		$this->bsdb->where('user._deleted', 0);
		$this->bsdb->where('event_user._deleted', 0);
	
		$this->bsdb->from('event_user');
		$this->bsdb->join('user', 'user.id=event_user.user_id');

		return $this->bsdb->get()->row()->total;
	}

	public function subscription_revenue($data = []) {
		$this->bsdb->select('sum((subscription_order.amount * currency.exchange_rate / (select count(id) from event_order where order_id = subscription_order.id))) as total');

		if (!empty($data['event_id'])) {
			$this->bsdb->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->bsdb->where('subscription_order._deleted', 0);
		$this->bsdb->where('event_order._deleted', 0);
		$this->bsdb->where_not_in('subscription_order.status', [0, 91, 92]);

		$this->bsdb->from('event_order');
		$this->bsdb->join('subscription_order', 'subscription_order.id=event_order.order_id', 'inner');
		$this->bsdb->join('currency', 'currency.code=subscription_order.currency_code');

		// $this->bsdb->group_by('event_order.order_id');

		$total = $this->bsdb->get()->row()->total;

		// log_kb($this->bsdb->last_query());
		return $total;
	}

}
