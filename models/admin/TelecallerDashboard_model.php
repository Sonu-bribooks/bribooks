<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TelecallerDashboard_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		// $this->db = $this->load->database('replica', TRUE);
	}

	public function registered_school($data = []) {
		$this->db->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (!empty($data['user_id']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('schools.id IN (SELECT school_id FROM telecaller_school WHERE user_id = %d AND event_id = %d)', (int)$data['user_id'], (int)$data['event_id']));
		}

		if (isset($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('site._deleted', 0);
		$this->db->where('schools._deleted', 0);
		$this->db->where('event_site._deleted', 0);

		$this->db->from('event_site');
		$this->db->join('site', 'site.id=event_site.site_id');
		$this->db->join('schools', 'schools.site_id=site.id');

		return $this->db->get()->row()->total;
	}

	public function registered_school_data($data = []) {
		$this->db->select('
			event_site.event_id,
			event_site.site_id,
			site.name,
			event_site.date_added
		');

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (!empty($data['user_id']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('schools.id IN (SELECT school_id FROM telecaller_school WHERE user_id = %d AND event_id = %d)', (int)$data['user_id'], (int)$data['event_id']));
		}

		if (isset($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_site.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('site._deleted', 0);
		$this->db->where('schools._deleted', 0);
		$this->db->where('event_site._deleted', 0);

		$this->db->from('event_site');
		$this->db->join('site', 'site.id=event_site.site_id');
		$this->db->join('schools', 'schools.site_id=site.id');

		$this->db->limit(1000, 0);

		return $this->db->get()->result_array();
	}

	public function school_with_authors($data = []) {
		$this->db->select('count(distinct users.site_id) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (!empty($data['user_id']) && !empty($data['event_id'])) {
			$this->db->where(vsprintf('users.site_id IN (
				SELECT schools.site_id
				FROM telecaller_school
				join schools on schools.id = telecaller_school.school_id
				WHERE telecaller_school.user_id = %d AND telecaller_school.`event_id` = %d
			)',
			[
				(int)$data['user_id'],
				(int)$data['event_id']
			]));
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('event_user.date_added > event_site.date_added');

		$this->db->where('users.role_id', 2);
		$this->db->where('event_user._deleted', 0);
		$this->db->where('users._deleted', 0);

		$this->db->from('event_user');
		$this->db->join('users', 'users.id=event_user.user_id', 'inner');
		$this->db->join('event_site', 'event_site.site_id=users.site_id AND event_site.event_id = event_user.event_id');

		return $this->db->get()->row()->total;
	}

	public function schools_with_authors_data($data = []) {
		$this->db->select('
			users.site_id,
			site.name,
			event_site.event_id,
			count(distinct users.id) as total
		');

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (!empty($data['user_id']) && !empty($data['event_id'])) {
			$this->db->where(vsprintf('users.site_id IN (
				SELECT schools.site_id
				FROM telecaller_school
				join schools on schools.id = telecaller_school.school_id
				WHERE telecaller_school.user_id = %d AND telecaller_school.`event_id` = %d
			)',
			[
				(int)$data['user_id'],
				(int)$data['event_id']
			]));
		}
		
		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('event_user.date_added > event_site.date_added');

		$this->db->where('users.role_id', 2);
		$this->db->where('event_user._deleted', 0);
		$this->db->where('users._deleted', 0);

		$this->db->from('event_user');
		$this->db->join('users', 'users.id=event_user.user_id', 'inner');
		$this->db->join('site', 'site.id=users.site_id', 'inner');
		$this->db->join('event_site', 'event_site.site_id=site.id AND event_site.event_id = event_user.event_id');

		$this->db->group_by('users.site_id');
		$this->db->order_by('total', 'DESC');

		$this->db->limit(10000, 0);

		return $this->db->get()->result_array();
	}

	public function verified_school($data = []) {
		$this->db->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->db->where('site.verified', 1);
		$this->db->where('site._deleted', 0);
		$this->db->where('event_site._deleted', 0);

		$this->db->from('event_site');
		$this->db->join('site', 'site.id=event_site.site_id');

		return $this->db->get()->row()->total;
	}

	public function verified_school_data($data = []) {
		$this->db->select('
			event_site.site_id,
			site.name,
			event_site.date_added,
			site.date_verified
		');

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->db->where('site.verified', 1);
		$this->db->where('site._deleted', 0);
		$this->db->where('event_site._deleted', 0);

		$this->db->from('event_site');
		$this->db->join('site', 'site.id=event_site.site_id');

		$this->db->limit(1000, 0);

		return $this->db->get()->result_array();
	}

	public function old_school_enrolled($data = []) {
		$this->db->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			// $this->db->where(sprintf('site.date_verified >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->db->where('site._deleted', 0);
		$this->db->where('event_site._deleted', 0);

		$this->db->from('event_site');
		$this->db->join('site', 'site.id=event_site.site_id');

		return $this->db->get()->row()->total;
	}

	public function old_school_enrolled_data($data = []) {
		$this->db->select('
			event_site.site_id,
			site.name,
			event_site.date_added,
			site.date_verified
		');

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(site.date_verified)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_site.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_site.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('site.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			// $this->db->where(sprintf('site.date_verified >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->db->where('site._deleted', 0);
		$this->db->where('event_site._deleted', 0);

		$this->db->from('event_site');
		$this->db->join('site', 'site.id=event_site.site_id');

		$this->db->limit(1000, 0);

		return $this->db->get()->result_array();
	}

	public function registered_authors($data = []) {
		$this->db->select('count(distinct users.id) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_user.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_user.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['is_new']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('users.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		if (!empty($data['is_old']) && !empty($data['event_id'])) {
			$this->db->where(sprintf('users.date_added < (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->db->where('users._deleted', 0);
		$this->db->where('event_user._deleted', 0);

		$this->db->from('event_user');
		$this->db->join('users', 'users.id=event_user.user_id', 'inner');

		return $this->db->get()->row()->total;
	}

	public function written_books($data = []) {
		$this->db->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(book.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('book.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['event_id'])) {
			$this->db->where(sprintf('book.date_added >= (select start_date from event where id = %s limit 1)', (int)$data['event_id']));
			$this->db->where(sprintf('book.date_added <= (select book_writing_end_date from event where id = %s limit 1)', (int)$data['event_id']));
		}

		$this->db->where('book._deleted', 0);
		$this->db->where('event_user._deleted', 0);

		$this->db->from('book');
		$this->db->join('event_user', 'event_user.user_id=book.user_id', 'inner');

		return $this->db->get()->row()->total;
	}

	public function published_books($data = []) {
		$this->db->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_book.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_book.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_book.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('book._deleted', 0);
		$this->db->where('event_book._deleted', 0);

		$this->db->from('event_book');
		$this->db->join('book', 'book.id=event_book.book_id', 'inner');

		return $this->db->get()->row()->total;
	}

	public function ordered_copies($data = []) {
		$this->db->select('COALESCE(sum(event_order.quantity),0) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('event_order._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);

		$this->db->from('event_order');
		$this->db->join('order', 'order.id=event_order.order_id', 'inner');

		$total = $this->db->get()->row()->total;

		// pr($this->db->last_query(), 1);

		return $total;
	}

	public function order_total($data = []) {
		$this->db->select('count(distinct event_order.order_id) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('event_order._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);

		$this->db->from('event_order');
		$this->db->join('order', 'order.id=event_order.order_id', 'inner');

		$total = $this->db->get()->row()->total;

		// pr($this->db->last_query(), 1);

		return $total;
	}

	public function order_revenue($data = []) {
		$this->db->select('sum((order.total * currency.exchange_rate / (select count(id) from event_order where order_id = order.id))) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('event_order._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);

		$this->db->from('event_order');
		$this->db->join('order', 'order.id=event_order.order_id', 'inner');
		$this->db->join('currency', 'currency.id=order.currency_id');

		// $this->db->group_by('event_order.order_id');

		$total = $this->db->get()->row()->total;

		// log_kb($this->db->last_query());
		return $total;
	}

	public function order_revenue_ebook($data = []) {
		$this->db->select('sum((order.total * currency.exchange_rate / (select count(id) from event_order where order_id = order.id))) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(event_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('event_order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('option', '{"name":"Ebook","price":0}');

		$this->db->from('event_order');
		$this->db->join('order', 'order.id=event_order.order_id', 'inner');
		$this->db->join('order_product', 'order_product.order_id=order.id', 'inner');
		$this->db->join('currency', 'currency.id=order.currency_id');

		// $this->db->group_by('event_order.order_id');

		$total = $this->db->get()->row()->total;

		// log_kb($this->db->last_query());
		return $total;
	}

	public function subscribed_authors($data = []) {
		$this->db->select('count(1) as total');

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(user_subscription_plan.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['start_date'])) {
			$this->db->where('user_subscription_plan.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('user_subscription_plan.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('user_subscription_plan._deleted', 0);
		$this->db->where('event_user._deleted', 0);

		$this->db->from('user_subscription_plan');
		$this->db->join('event_user', 'user_subscription_plan.user_id=event_user.user_id');

		return $this->db->get()->row()->total;
	}
}
