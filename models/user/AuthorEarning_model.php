<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuthorEarning_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($author_earning_id = 0) {
		$this->db->select('author_earning.*');

		$this->db->where('author_earning.id', (int)$author_earning_id);
		$this->db->where('author_earning._deleted', 0);

		return $this->db->get('author_earning')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('author_earning.*');

		if (isset($data['site_id'])) {
			$this->db->where('author_earning.site_id', (int)$data['site_id']);
		}

		if (isset($data['currency_code'])) {
			$this->db->where('author_earning.currency_code', $data['currency_code']);
		}

		if (isset($data['user_site_id'])) {
			$this->db->where('users.site_id', (int)$data['user_site_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('author_earning.order_id', (int)$data['order_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('author_earning.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('author_earning.user_id', (int)$data['user_id']);
		}

		if (isset($data['author_id'])) {
			$this->db->where('author_earning.author_id', (int)$data['author_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('author_earning.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('author_earning.status != ', (int)$data['ne_status']);
		}

		if (isset($data['status_in'])) {
			$this->db->where_in('author_earning.status', $data['status_in']);
		}

		if (isset($data['date_added_le'])) {
			$this->db->where('DATE(author_earning.date_added) < ', date('Y-m-d', strtotime($data['date_added_le'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('author_earning.amount', $data['search'], 'after');
			$this->db->or_like('author_earning.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('author_earning._deleted', 0);

		$this->db->from('author_earning');

		if (isset($data['user_site_id'])) {
			$this->db->join('users', 'users.id = author_earning.author_id', 'left');
		}

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
			'author_earning.amount',
			'author_earning.status',
			'author_earning.date_added',
			'author_earning.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'author_earning.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}


	public function export_excel($data = []) {
		$this->db->select_sum('author_earning.amount');
		$this->db->select('
			author_earning.book_id,
			MAX(author_earning.author_id) AS author_id
		');

		if (isset($data['site_id'])) {
			$this->db->where('author_earning.site_id', (int)$data['site_id']);
		}

		if (isset($data['author_site_id'])) {
			$this->db->where('users.site_id', (int)$data['author_site_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('author_earning.order_id', (int)$data['order_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('author_earning.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('author_earning.user_id', (int)$data['user_id']);
		}

		if (isset($data['author_id'])) {
			$this->db->where('author_earning.author_id', (int)$data['author_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('author_earning.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('author_earning.amount', $data['search'], 'after');
			$this->db->or_like('author_earning.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->from('author_earning');

		if (isset($data['user_site_id'])) {
			$this->db->join('users', 'users.id = author_earning.author_id', 'left');
		}

		$this->db->where('author_earning._deleted', 0);

		$this->db->group_by('author_earning.book_id');

		return ['rows' => $this->db->get()->result_array(), 'total' => 0];
	}

	public function add($data = []) {
		$this->db->insert('author_earning', $data + [
			'currency_id'	=> (int)($data['currency_id'] ?? $this->config->item('site_currency_id')),
			'currency_code'	=> $data['currency_code'] ?? $this->config->item('site_currency_code'),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$author_earning_id = $this->db->insert_id();

		return $author_earning_id;
	}

	public function edit($author_earning_id = 0, $data = []) {
		$this->db->where('id', (int)$author_earning_id);
		$this->db->update('author_earning', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($author_earning_id = 0) {
		$this->db->where('id', (int)$author_earning_id);
		$this->db->update('author_earning',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getTotalEarning($data = []) {
		$this->db->select_sum('author_earning.amount');

		if (isset($data['site_id'])) {
			$this->db->where('author_earning.site_id', (int)$data['site_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('author_earning.order_id', (int)$data['order_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('author_earning.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('author_earning.user_id', (int)$data['user_id']);
		}

		if (isset($data['author_id'])) {
			$this->db->where('author_earning.author_id', (int)$data['author_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('author_earning.status', (int)$data['status']);
		}

		return $this->db->get('author_earning')->row()->amount;
	}

	public function cancelByOrderId($order_id = 0) {
		$this->db->where('order_id', (int)$order_id);
		$this->db->update('author_earning',  [
			'status'		=> 3,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
