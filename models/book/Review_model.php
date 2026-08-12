<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Review_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($review_id = 0) {
		$this->db->select('review.*, book.name AS book');

		$this->db->where('review.id', (int)$review_id);
		$this->db->where('review._deleted', 0);

		$this->db->join('book', 'book.id = review.book_id', 'left');

		return $this->db->get('review')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('review.*, book.name AS book');

		if (isset($data['site_id'])) {
			$this->db->where('review.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_site_id'])) {
			$this->db->where('users.site_id', (int)$data['user_site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('review.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('review.book_id', (int)$data['book_id']);
		}

		if (isset($data['rating'])) {
			$this->db->where('review.rating', (int)$data['rating']);
		}

		if (isset($data['reviewer_id'])) {
			$this->db->where('review.reviewer_id', (int)$data['reviewer_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('review.status', (int)$data['status']);
		}

		if (!empty($data['review_flag'])) {
			$this->db->where("review.id IN (select review_id from review_flags where _deleted = 0)");
		}

		if (!empty($data['search'])) {
			$this->db->like('review.author', $data['search'], 'after');
			$this->db->like('review.text', $data['search'], 'after');
			$this->db->or_like('book.name', $data['search'], 'after');
		}

		$this->db->where('review._deleted', 0);

		$this->db->join('book', 'book.id = review.book_id', 'left');
		$this->db->join('users', 'users.id = review.user_id', 'left');
		$this->db->from('review');

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
			'review.author',
			'review.status',
			'review.date_added',
			'review.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'review.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('review', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$review_id = $this->db->insert_id();

		return $review_id;
	}

	public function edit($review_id = 0, $data = []) {
		$this->db->where('id', (int)$review_id);
		$this->db->update('review', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($review_id = 0) {
		$this->db->where('id', (int)$review_id);
		$this->db->update('review',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
