<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CrosswordBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('crossword_books')->row_array();
	}

    public function get_all($data = []) {
		$this->db->select('crossword_books.*, crossword_stores.store_name, book.name as book_name, book.isbn as book_isbn');

		if (isset($data['store_id'])) {
			$this->db->where('crossword_books.store_id', (int)$data['store_id']);
		}

        if (isset($data['book_id'])) {
			$this->db->where('crossword_books.book_id', (int)$data['book_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('crossword_books.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->or_like('crossword_stores.store_name', $data['search'], 'both');
			$this->db->or_like('book.name', $data['search'], 'both');
			$this->db->or_like('book.isbn', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->join('crossword_stores', 'crossword_stores.id = crossword_books.store_id', 'left');
		$this->db->join('book', 'book.id = crossword_books.book_id', 'left');

		$this->db->where('crossword_books._deleted', 0);

		$this->db->from('crossword_books');

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
			'crossword_books.date_added',
			'crossword_books.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'crossword_books.id';
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
		$this->db->insert('crossword_books', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$token_id = $this->db->insert_id();

		return $token_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('crossword_books', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getBookByWhere($where) {
		$this->db->where($where);
		return $this->db->get()->result_array();
	}
}
