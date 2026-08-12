<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function getTotalUsers($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('role_id', 2);
		$this->rdb->where('_deleted', 0);

		return $this->rdb->from('users')
			->count_all_results();
	}

	public function getTotalNewUsers($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('role_id', 2);
		$this->rdb->where('_deleted', 0);
		$this->rdb->where('DATE(date_added)', date('Y-m-d'));

		return $this->rdb->from('users')
			->count_all_results();
	}

	public function getTotalOnlineUsers($data = []) {
		$this->rdb->select('id');

		return $this->rdb->from('online')
			->count_all_results();
	}

	public function getTotalSubscribers($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('_deleted', 0);

		return $this->rdb->from('user_subscription_plan')
			->count_all_results();
	}

	public function getTotalNewSubscribers($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('_deleted', 0);
		$this->rdb->where('DATE(date_added)', date('Y-m-d'));

		return $this->rdb->from('user_subscription_plan')
			->count_all_results();
	}

	public function getTotalBooks($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('_deleted', 0);
		return $this->rdb->from('book')
			->count_all_results();
	}

	public function getTotalPublishedBooks($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('status', 1);
		$this->rdb->where('_deleted', 0);
		return $this->rdb->from('book')
			->count_all_results();
	}

	public function getTotalNewPublishedBooks($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('status', 1);
		$this->rdb->where('_deleted', 0);
		$this->rdb->where('DATE(date_published)', date('Y-m-d'));
		return $this->rdb->from('book')
			->count_all_results();
	}

	public function getTotalOrders($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('_deleted', 0);
		$this->rdb->where('status != ', 0);
		return $this->rdb->from('order')
			->count_all_results();
	}

	public function getTotalNewOrders($data = []) {
		$this->rdb->select('id');
		$this->rdb->where('_deleted', 0);
		$this->rdb->where('status != ', 0);
		$this->rdb->where('DATE(date_added)', date('Y-m-d'));

		return $this->rdb->from('order')
			->count_all_results();
	}

	public function book_isbn_amazon_books($data = []) {
		$this->db->select("
			bookstore.book_id,
			bookstore.user_id,
			bookstore.version,
			bookstore.name AS book_name,
			bookstore.author_name,
			bookstore.location,
			bookstore.sold,
			users.site_id,
			event_book.event_id,
			CASE site.currency_code
				WHEN 'INR' THEN 'Domestic'
				ELSE 'Global'
			END AS book_region,
			, COALESCE(
			ebil.amazon_limit,
			CASE
			WHEN site.currency_code = 'INR' THEN default_domestic.amazon_limit
			ELSE default_global.amazon_limit
			END ) AS amazon_limit
			,COALESCE(
				ebil.isbn_limit,
				CASE
					WHEN site.currency_code = 'INR' THEN default_domestic.isbn_limit
					ELSE default_global.isbn_limit
				END
			) AS isbn_limit",
			false
		);

		if (isset($data['empty_isbn'])) {
			$this->db->where('bookstore.book_id in (select id from book where isbn = "")');
			$this->db->where('
				bookstore.sold >= COALESCE(
					ebil.isbn_limit,
					CASE
						WHEN site.currency_code = "INR" THEN default_domestic.isbn_limit
						ELSE default_global.isbn_limit
					END
				)
			', null, false);
		}

		if (isset($data['empty_amazon'])) {
			$this->db->where('bookstore.book_id in (select id from book where amazon_url = "")');
			$this->db->where('
				bookstore.sold >= COALESCE(
					ebil.amazon_limit,
					CASE
						WHEN site.currency_code = "INR" THEN default_domestic.amazon_limit
						ELSE default_global.amazon_limit
					END
				)
			', null, false);
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('bookstore.book_id in (select book_id from event_book where _deleted = 0 and event_id = %s)', (int)$data['event_id']));
		}

		$this->db->where('bookstore._deleted', 0);
		$this->db->where('bookstore.status', 1);
		$this->db->where('users._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.id', $data['search'], 'both');
			$this->db->or_like('book.name', $data['search'], 'both');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->join('book', 'book.id = bookstore.book_id');
		$this->db->join('users', 'users.id = bookstore.user_id');
		$this->db->join('site', 'site.id = users.site_id');
		$this->db->join('event_book', 'event_book.book_id = bookstore.book_id AND event_book._deleted = 0', 'left');
		$this->db->join('event_book_isbn_limit ebil', 'ebil.event_id = event_book.event_id AND ebil._deleted = 0 AND ebil.status = 1', 'left');
		$this->db->join('event_book_isbn_limit default_domestic', 'default_domestic.event_id = 0 AND default_domestic._deleted = 0 AND default_domestic.status = 1', 'left');
		$this->db->join('event_book_isbn_limit default_global', 'default_global.event_id = 1 AND default_global._deleted = 0 AND default_global.status = 1', 'left');

		$this->db->from('bookstore');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'bookstore.book_id',
			'bookstore.date_added',
			'bookstore.sold',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'bookstore.sold';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
