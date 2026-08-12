<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookClone_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_clone_id = 0) {
		$this->db->select('book_clone.*');

		$this->db->where('book_clone.id', (int)$book_clone_id);
		$this->db->where('book_clone._deleted', 0);

		return $this->db->get('book_clone')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_clone.*');

		if (isset($data['version'])) {
			$this->db->where('book_clone.version', (int)$data['version']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_clone.new_book_id', (int)$data['book_id']);
			$this->db->or_where('book_clone.old_book_id', (int)$data['book_id']);
		}

        if (isset($data['new_book_id'])) {
			$this->db->where('book_clone.new_book_id', (int)$data['new_book_id']);
		}

        if (isset($data['old_book_id'])) {
			$this->db->where('book_clone.old_book_id', (int)$data['old_book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book_clone.user_id', (int)$data['user_id']);
		}

		if (isset($data['temp_user_id'])) {
			$this->db->where('book_clone.temp_user_id', (int)$data['temp_user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('book_clone.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('book_clone.status != ', (int)$data['ne_status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_clone.new_book_id', $data['search']);
			$this->db->or_like('book.name', $data['search']);
			$this->db->group_end();
		}

		
		$this->db->where('book_clone._deleted', 0);
		$this->db->join('book', 'book.id = book_clone.new_book_id', 'left');
		$this->db->from('book_clone');

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
			'book_clone.new_book_id',
			'book_clone.old_book_id',
			'book_clone.status',
			'book_clone.user_id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_clone.date_added';
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
		$this->db->insert('book_clone', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$book_clone_id = $this->db->insert_id();

		return $book_clone_id;
	}

	public function edit($book_clone_id = 0, $data = []) {
		$this->db->where('id', (int)$book_clone_id);
		$this->db->update('book_clone', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($book_clone_id = 0) {
		$this->db->where('id', (int)$book_clone_id);
		$this->db->update('book_clone',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
