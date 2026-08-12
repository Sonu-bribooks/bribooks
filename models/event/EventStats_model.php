<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventStats_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function registered_school($data = []) {
		$this->rdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->rdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_south'])) {
			$this->rdb->where("site.state_id in (select id from state where country_id = 1 and code = 'ss')");
		}

		$this->rdb->where('site._deleted', 0);
		$this->rdb->where('event_site._deleted', 0);

		$this->rdb->from('event_site');
		$this->rdb->join('site', 'site.id=event_site.site_id');

		return $this->rdb->get()->row()->total;
	}

	public function registered_school_data($data = []) {
		$this->rdb->select('
			event_site.event_id,
			event_site.site_id,
			site.name,
			site.owner_email as email,
			site.owner_mobile as mobile,
			(
				SELECT COUNT(event_user.user_id)
				FROM event_user
				JOIN users ON users.id = event_user.user_id
				WHERE event_user.event_id = event_site.event_id
				AND event_user._deleted = 0
				AND users._deleted = 0
				AND users.site_id = event_site.site_id
			) AS registered_student,
			event_site.date_added
		');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->rdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('site._deleted', 0);
		$this->rdb->where('event_site._deleted', 0);

		$this->rdb->from('event_site');
		$this->rdb->join('site', 'site.id=event_site.site_id');

		$this->db->limit(1000, 0);

		return $this->rdb->get()->result_array();
	}

	public function verified_school($data = []) {
		$this->rdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->rdb->where('site.verified', 1);
		$this->rdb->where('site._deleted', 0);
		$this->rdb->where('event_site._deleted', 0);

		$this->rdb->from('event_site');
		$this->rdb->join('site', 'site.id=event_site.site_id');

		return $this->rdb->get()->row()->total;
	}

	public function verified_school_data($data = []) {
		$this->rdb->select('
			event_site.site_id,
			site.name,
			event_site.date_added,
			site.date_verified
		');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->rdb->where('site.verified', 1);
		$this->rdb->where('site._deleted', 0);
		$this->rdb->where('event_site._deleted', 0);

		$this->rdb->from('event_site');
		$this->rdb->join('site', 'site.id=event_site.site_id');

		$this->db->limit(1000, 0);

		return $this->rdb->get()->result_array();
	}

	public function school_with_authors($data = []) {
		$this->rdb->select('count(distinct users.site_id) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(users.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('users.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('users.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['school_in_event']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('users.site_id in (select site_id from event_site where event_id = %d)', (int)$data['event_id']));
		}

		$this->rdb->where('users.role_id', 2);
		$this->rdb->where('event_user._deleted', 0);
		$this->rdb->where('users._deleted', 0);

		$this->rdb->from('event_user');
		$this->rdb->join('users', 'users.id=event_user.user_id', 'inner');

		return $this->rdb->get()->row()->total;
	}

	public function schools_with_authors_data($data = []) {
		$this->rdb->select('
			users.site_id,
			site.name,
			event_site.event_id,
			count(distinct users.id) as total
		');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_user.event_id', (int)$data['event_id']);
		}

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
		$this->rdb->where('event_user._deleted', 0);
		$this->rdb->where('users._deleted', 0);

		$this->rdb->from('event_user');
		$this->rdb->join('users', 'users.id=event_user.user_id', 'inner');
		$this->rdb->join('site', 'site.id=users.site_id', 'inner');
		$this->rdb->join('event_site', 'event_site.site_id=site.id and event_site.event_id = event_user.event_id', 'left');

		$this->rdb->group_by('users.site_id');
		$this->rdb->order_by('total', 'DESC');

		$this->db->limit(1000, 0);

		return $this->rdb->get()->result_array();
	}

	public function old_school_enrolled($data = []) {
		$this->rdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->rdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			// $this->rdb->where(sprintf('site.date_verified >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->rdb->where('site._deleted', 0);
		$this->rdb->where('event_site._deleted', 0);

		$this->rdb->from('event_site');
		$this->rdb->join('site', 'site.id=event_site.site_id');

		return $this->rdb->get()->row()->total;
	}

	public function old_school_enrolled_data($data = []) {
		$this->rdb->select('
			event_site.site_id,
			site.name,
			event_site.date_added,
			site.date_verified
		');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->rdb->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			// $this->rdb->where(sprintf('site.date_verified >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->rdb->where('site._deleted', 0);
		$this->rdb->where('event_site._deleted', 0);

		$this->rdb->from('event_site');
		$this->rdb->join('site', 'site.id=event_site.site_id');

		$this->db->limit(1000, 0);

		return $this->rdb->get()->result_array();
	}

	public function registered_authors($data = []) {
		$this->rdb->select('count(distinct users.id) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('users.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->rdb->where(sprintf('users.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['utm_source']) && !empty($data['event_id'])) {
			$this->rdb->where(
				"event_user.user_id IN (
					SELECT lead.student_id 
					FROM `lead` 
					WHERE lead.event_id = " . (int)$data['event_id'] . "
					AND lead.utm_source = '" . $data['utm_source'] . "'
				)",
				null,
				false
			);
		}

		$this->rdb->where('users._deleted', 0);
		$this->rdb->where('event_user._deleted', 0);

		$this->rdb->from('event_user');
		$this->rdb->join('users', 'users.id=event_user.user_id', 'inner');

		return $this->rdb->get()->row()->total;
	}

	public function written_books($data = []) {
		$this->rdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(book.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('book.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['event_id'])) {
			$this->rdb->where(sprintf('book.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			$this->rdb->where(sprintf('book.date_added <= (select book_writing_end_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->rdb->where('book._deleted', 0);
		$this->rdb->where('event_user._deleted', 0);

		$this->rdb->from('book');
		$this->rdb->join('event_user', 'event_user.user_id=book.user_id', 'inner');

		return $this->rdb->get()->row()->total;
	}

	public function published_books($data = []) {
		$this->rdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_book.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_book.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_book.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('book._deleted', 0);
		$this->rdb->where('event_book._deleted', 0);

		$this->rdb->from('event_book');
		$this->rdb->join('book', 'book.id=event_book.book_id', 'inner');

		return $this->rdb->get()->row()->total;
	}

	public function ordered_copies($data = []) {
		$this->rdb->select('COALESCE(sum(event_order.quantity),0) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('order._deleted', 0);
		$this->rdb->where('event_order._deleted', 0);
		$this->rdb->where('order.parent_order_id', 0);
		$this->rdb->where_not_in('order.status', [0, 91, 92]);

		$this->rdb->from('event_order');
		$this->rdb->join('order', 'order.id=event_order.order_id', 'inner');

		$total = $this->rdb->get()->row()->total;

		// pr($this->rdb->last_query(), 1);

		return $total;
	}

	public function order_total($data = []) {
		$this->rdb->select('count(distinct event_order.order_id) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('order._deleted', 0);
		$this->rdb->where('event_order._deleted', 0);
		$this->rdb->where('order.parent_order_id', 0);
		$this->rdb->where_not_in('order.status', [0, 91, 92]);

		$this->rdb->from('event_order');
		$this->rdb->join('order', 'order.id=event_order.order_id', 'inner');

		$total = $this->rdb->get()->row()->total;

		// pr($this->rdb->last_query(), 1);

		return $total;
	}

	public function order_revenue($data = []) {
		$this->rdb->select('sum((order.total * currency.exchange_rate / (select count(id) from event_order where order_id = order.id))) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('order._deleted', 0);
		$this->rdb->where('event_order._deleted', 0);
		$this->rdb->where('order.parent_order_id', 0);
		$this->rdb->where_not_in('order.status', [0, 91, 92]);

		$this->rdb->from('event_order');
		$this->rdb->join('order', 'order.id=event_order.order_id', 'inner');
		$this->rdb->join('currency', 'currency.id=order.currency_id');

		// $this->rdb->group_by('event_order.order_id');

		$total = $this->rdb->get()->row()->total;

		// log_kb($this->rdb->last_query());
		return $total;
	}

	public function order_revenue_ebook($data = []) {
		$this->rdb->select('sum((order.total * currency.exchange_rate / (select count(id) from event_order where order_id = order.id))) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->rdb->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->rdb->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->rdb->where('option', '{"name":"Ebook","price":0}');

		$this->rdb->from('event_order');
		$this->rdb->join('order', 'order.id=event_order.order_id', 'inner');
		$this->rdb->join('order_product', 'order_product.order_id=order.id', 'inner');
		$this->rdb->join('currency', 'currency.id=order.currency_id');

		// $this->rdb->group_by('event_order.order_id');

		$total = $this->rdb->get()->row()->total;

		// log_kb($this->rdb->last_query());
		return $total;
	}

	public function subscribed_authors($data = []) {
		$this->rdb->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->rdb->where('event_user.event_id', (int)$data['event_id']);
		}

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
		$this->rdb->where('event_user._deleted', 0);

		$this->rdb->from('user_subscription_plan');
		$this->rdb->join('event_user', 'user_subscription_plan.user_id=event_user.user_id');

		return $this->rdb->get()->row()->total;
	}
}
