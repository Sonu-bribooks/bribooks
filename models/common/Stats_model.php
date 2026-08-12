<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stats_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function registered_school($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['verified'])) {
			$this->rdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('site._deleted', 0);

		$this->rdb->from('site');

		return $this->rdb->get()->row()->total;
	}

	public function verified_school($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('site.verified', 1);
		$this->rdb->where('site._deleted', 0);

		$this->rdb->from('site');

		return $this->rdb->get()->row()->total;
	}

	public function school_with_authors($data = []) {
		$this->rdb->select('count(distinct users.site_id) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(users.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('users.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('users.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('users._deleted', 0);
		$this->rdb->where('users.role_id', 2);

		$this->rdb->from('users');

		return $this->rdb->get()->row()->total;
	}

	public function old_school_enrolled($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['verified'])) {
			$this->rdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('site._deleted', 0);

		$this->rdb->from('site');

		return $this->rdb->get()->row()->total;
	}

	public function registered_authors($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(users.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('users.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('users.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('users.role_id', 2);
		$this->rdb->where('users._deleted', 0);

		$this->rdb->from('users');

		return $this->rdb->get()->row()->total;
	}

	public function written_books($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(book.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('book.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('book._deleted', 0);

		$this->rdb->from('book');

		return $this->rdb->get()->row()->total;
	}

	public function published_books($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(book.date_published)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('book.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('book._deleted', 0);
		$this->rdb->where('book.status', 1);

		$this->rdb->from('book');

		return $this->rdb->get()->row()->total;
	}

	public function ordered_copies($data = []) {
		$this->rdb->select('sum(order_product.quantity) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('order._deleted', 0);
		$this->rdb->where('order.parent_order_id', 0);
		$this->rdb->where_not_in('order.status', [0, 91, 92]);

		$this->rdb->from('order');
		$this->rdb->join('order_product', 'order_product.order_id=order.id', 'inner');

		// $this->rdb->group_by('order_product.order_id');

		return $this->rdb->get()->row()->total;
	}

	public function order_total($data = []) {
		$this->rdb->select('count(order.id) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('order._deleted', 0);
		$this->rdb->where('order.parent_order_id', 0);
		$this->rdb->where_not_in('order.status', [0, 91, 92]);

		$this->rdb->from('order');

		return $this->rdb->get()->row()->total;
	}

	public function order_revenue($data = []) {
		$this->rdb->select('sum((order.total * currency.exchange_rate)) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('order._deleted', 0);
		$this->rdb->where('order.parent_order_id', 0);
		$this->rdb->where_not_in('order.status', [0, 91, 92]);

		$this->rdb->from('order');
		$this->rdb->join('currency', 'currency.id=order.currency_id');

		return $this->rdb->get()->row()->total;
	}

	public function subscribed_authors($data = []) {
		$this->rdb->select('count(1) as total');

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(user_subscription_plan.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('user_subscription_plan.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('user_subscription_plan.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('user_subscription_plan._deleted', 0);

		$this->rdb->from('user_subscription_plan');

		return $this->rdb->get()->row()->total;
	}
}
