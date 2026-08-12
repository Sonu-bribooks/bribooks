<?php defined('BASEPATH') or exit('No direct script access allowed');

class Ranking_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_rank_id = 0) {
		$this->db->select('user_rank.*');

		$this->db->where('user_rank.id', (int)$user_rank_id);
		$this->db->where('user_rank._deleted', 0);

		return $this->db->get('user_rank')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_rank.*');

		if (isset($data['event_challenge_id'])) {
			$this->db->where('user_rank.event_challenge_id', (int)$data['event_challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_rank.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('user_rank.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('user_rank.book_id', (int)$data['book_id']);
		}

		if (isset($data['score'])) {
			$this->db->where('user_rank.score', (int)$data['score']);
		}

		$this->db->where('user_rank._deleted', 0);

		$this->db->from('user_rank');

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
			'user_rank.date_added',
			'user_rank.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_rank.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, user_rank.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('user_rank', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_rank_id = $this->db->insert_id();

		return $user_rank_id;
	}

	public function edit($user_rank_id = 0, $data = []) {
		$this->db->where('id', (int)$user_rank_id);
		$this->db->update('user_rank', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_rank_id = 0) {
		$this->db->where('id', (int)$user_rank_id);
		$this->db->update('user_rank',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getRanks($data = []) {
		if (!empty($data['end_date'])) {
			$where_clause = $this->db
				->select('id')
				->where('date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])))
				->from('order')
				->get_compiled_select();
		} else {
			$where_clause = $this->db
				->select('id')
				->where('status != ', 0)
				->where('_deleted', 0)
				->from('order')
				->get_compiled_select();
		}

		$this->db->select('book.*');
		$this->db->select_sum('order_product.quantity');

		if (!empty($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (!empty($data['product_id'])) {
			$this->db->where('order_product.product_id', (int)$data['product_id']);
		}

		if (!empty($data['book_ids'])) {
			$this->db->where_in('book.id', $data['book_ids']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('users.state_id', (int)$data['state_id']);
		}

		if (!empty($data['city_id'])) {
			$this->db->where('users.city_id', (int)$data['city_id']);
		}

		if (!empty($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (!empty($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (!empty($data['grade'])) {
			$this->db->where('site_grade.name', (int)$data['grade']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('site.id IN (select site_id from event_site where event_id=' . (int)$data['event_id'] . ')');
		}

		if (!empty($data['start_date'])) {
			$this->db->where('book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['site_code'])) {
			$this->db->group_start();
			$this->db->like('site.site_code', $data['site_code'], 'after');
			// $this->db->or_like('site.site_code', 'in-sc', 'after');
			$this->db->group_end();
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search']);
			// $this->db->or_like('site.site_code', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search']);
			$this->db->group_end();
		}

		// if (!empty($data['end_date'])) {
		// 	$this->db->where("(`order_product`.`order_id` IN ($where_clause) || order_product.quantity IS NULL)", NULL, FALSE);
		// }

		$this->db->where('book.status', 1);
		$this->db->where('book._deleted', 0);

		$this->db->join('users', 'users.id = book.user_id', 'left');
		$this->db->join('event_user', 'event_user.user_id = book.user_id', 'left');

		if (!empty($data['grade'])) {
			$this->db->join('site_grade', 'site_grade.id = users.grade_id', 'left');
		}

		$this->db->join('site', 'site.id = users.site_id', 'left');

		if (!empty($data['end_date'])) {
			$this->db->join('order_product', "order_product.product_id = book.id AND (`order_product`.`order_id` IN ($where_clause) || order_product.quantity IS NULL)", 'left');
		} else {
			$this->db->join('order_product', "order_product.product_id = book.id AND (`order_product`.`order_id` IN ($where_clause) || order_product.quantity IS NULL)", 'left');
		}

		$this->db->from('book');

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

		$this->db->group_by('book.id');

		if (!empty($data['quantity_ge'])) {
			$this->db->having('SUM(IF(order_product.quantity, order_product.quantity, 0)) >= ', (int)$data['quantity_ge']);
		}

		if (!empty($data['quantity_le'])) {
			$this->db->having('SUM(IF(order_product.quantity, order_product.quantity, 0)) <= ', (int)$data['quantity_le']);
		}

		$this->db->order_by('quantity DESC, book.views DESC');

		$result = ['rows' => $this->db->get()->result_array(), 'total' => $total];

		// log_kb([$this->db->last_query()]);

		return $result;
	}

	public function get_books($data = []) {
		$this->db->select('count(book.id) as total');

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['start_date']) || isset($data['end_date'])) {
			$this->db->where('book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['start_date'])). '" and "'. date('Y-m-d H:i:s', strtotime($data['end_date'])) . '"');
		}

		if (isset($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('book.category_id', (int)$data['category_id']);
		}

		if (isset($data['cover_id'])) {
			$this->db->where('book.cover_id', (int)$data['cover_id']);
		}

		if (isset($data['reviewer_id'])) {
			$this->db->where('book.reviewer_id', (int)$data['reviewer_id']);
		}

		if (isset($data['genre_id'])) {
			$this->db->where('book.genre_id', (int)$data['genre_id']);
		}

		if (isset($data['featured'])) {
			$this->db->where('book.featured', (int)$data['featured']);
		}

		if (!empty($data['end_date']) && !empty($data['status'])) {
			$this->db->where('book.date_published < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (isset($data['status'])) {
			$this->db->where('book.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('book.status != ', (int)$data['ne_status']);
		}

		if (isset($data['isbn'])) {
			$this->db->where('book.isbn', $data['isbn']);
		}

		if (isset($data['isbn_country_code'])) {
			$this->db->where('book.isbn_country_code', $data['isbn_country_code']);
		}

		if (isset($data['has_isbn'])) {
			if ($data['has_isbn'] == '1') {
				$this->db->where('book.isbn !=', "");
			} else {
				$this->db->where('book.isbn =', "");
			}
		}

		if (isset($data['has_amazon_url'])) {
			if ($data['has_amazon_url'] == '1') {
				$this->db->where('book.amazon_url !=', "");
			} else {
				$this->db->where('book.amazon_url =', "");
			}
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('book.isbn', $data['search'], 'after');
			$this->db->or_like('book.slug', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book._deleted', 0);

		$this->db->join('category', 'category.id = book.category_id', 'left');
		$this->db->join('users', 'users.id = book.user_id', 'left');
		$this->db->join('event_user', 'event_user.user_id = book.user_id', 'left');
		$this->db->from('book');

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
			'book.name',
			'book.author_name',
			'book.featured',
			'book.status',
			'book.site_id',
			'book.date_published',
			'book.date_approved',
			'book.date_added',
			'book.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$return = ['total' => $this->db->get()->row()->total];

		// log_kb([$this->db->last_query()]);

		return $return;
	}

	public function get_students($data = []) {
		$this->db->select('count(distinct users.id) as total');

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['start_date']) || isset($data['end_date'])) {
			$this->db->where('event_user.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['start_date'])). '" and "'. date('Y-m-d H:i:s', strtotime($data['end_date'])) . '"');
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('users._deleted', 0);

		$this->db->join('event_user', 'event_user.user_id = users.id', 'left');
		$this->db->from('users');

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
			'users.id',
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$return = ['total' => $this->db->get()->row()->total];

		// log_kb([$this->db->last_query()]);

		return $return;
	}

	public function getTotalSolds($data = []) {
		$this->db->select_sum('order_product.quantity');

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
			$this->db->where('event_book.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (isset($data['grade'])) {
			$this->db->where('users.grade', (int)$data['grade']);
		}

		if (isset($data['section'])) {
			$this->db->where('users.section', $data['section']);
		}

		if (!empty($data['start_date'])) {
			$this->db->where('order.date_added >= ', date('Y-m-d H:i:s', strtotime($data['start_date'])));
		}

		if (!empty($data['end_date'])) {
			$this->db->where('order.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('book._deleted', 0);
		$this->db->where('users._deleted', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);

		$this->db->join('order', 'order.id = order_product.order_id', 'left');
		$this->db->join('book', 'book.id = order_product.product_id', 'left');
		$this->db->join('event_book', 'event_book.book_id = book.id', 'left');
		$this->db->join('users', 'users.id = book.user_id', 'left');
		$this->db->join('event_user', 'event_user.user_id = book.user_id', 'left');

		$return = $this->db->get('order_product')->row()->quantity;

		// log_kb([$this->db->last_query()]);

		return $return;
	}

	public function getSchoolRanks($data = []) {
		$this->db->select('users.site_id');

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('users.state_id', (int)$data['state_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('users.city_id', (int)$data['city_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['start_date']) || isset($data['end_date'])) {
			$this->db->where('event_user.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['start_date'])). '" and "'. date('Y-m-d H:i:s', strtotime($data['end_date'])) . '"');
		}

		if (!empty($data['end_date'])) {
			$this->db->where('event_user.date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('users._deleted', 0);

		$this->db->join('event_user', 'event_user.user_id = users.id', 'left');
		$this->db->from('users');

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

		$this->db->group_by('users.site_id');

		if (!empty($data['quantity_ge'])) {
			$this->db->having('COUNT(users.id) >= ', (int)$data['quantity_ge']);
		}

		if (!empty($data['quantity_le'])) {
			$this->db->having('COUNT(users.id) <= ', (int)$data['quantity_le']);
		}

		$sort_data = [
			'users.id',
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$return = ['rows' => $this->db->get()->result_array(), 'total' => $total];

		// log_kb([$this->db->last_query()]);

		return $return;
	}
}
