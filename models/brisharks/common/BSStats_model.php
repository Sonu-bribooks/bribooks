<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSStats_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function registered_school($data = []) {
		$this->bsdb->select('count(1) as total');

		if (isset($data['verified'])) {
			$this->bsdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->bsdb->where('site._deleted', 0);

		$this->bsdb->from('site');

		return $this->bsdb->get()->row()->total;
	}

    public function registered_user_from_school($data = []) {
		$this->bsdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['paid_user'])) {
			$this->bsdb->join('subscription_payment', ' subscription_payment.user_id = user.id');
			$this->bsdb->where('subscription_payment.status', 1);
        }

		$this->bsdb->where('user.role_id', 2);
		$this->bsdb->where('user.source', 'Invite_school');
		$this->bsdb->where('user._deleted', 0);
	
		$this->bsdb->from('user');
		return $this->bsdb->get()->row()->total;

	}

	public function old_user_enrollemt($data = []) {
		$this->bsdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['paid_user'])) {
			$this->bsdb->join('subscription_payment', ' subscription_payment.user_id = user.id');
			$this->bsdb->where('subscription_payment.status', 1);
		}

        $this->bsdb->where_in('user.role_id', [2,99]);
		$this->bsdb->where('user._deleted', 0);
	
		$this->bsdb->from('user');
		return $this->bsdb->get()->row()->total;
	}

	public function new_user_enrollemt($data = []) {
		$this->bsdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['paid_user'])) {
			$this->bsdb->join('subscription_payment', ' subscription_payment.user_id = user.id');
			$this->bsdb->where('subscription_payment.status', 1);
		}

        $this->bsdb->where('user.role_id', 2);
		$this->bsdb->where('user._deleted', 0);

	
		$this->bsdb->from('user');

		return $this->bsdb->get()->row()->total;
	}

	public function subscription_revenue($data = []) {
		$this->bsdb->select('sum((subscription_order.amount * currency.exchange_rate)) as total');

		if (isset($data['date_added'])) {
			$this->bsdb->where('DATE(subscription_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->bsdb->where('subscription_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->bsdb->where('subscription_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->bsdb->where('subscription_order._deleted', 0);
		$this->bsdb->where_not_in('subscription_order.status', [0, 91, 92]);

		$this->bsdb->from('subscription_order');
		$this->bsdb->join('currency', 'currency.code=subscription_order.currency_code');

		return $this->bsdb->get()->row()->total;
	}
}
