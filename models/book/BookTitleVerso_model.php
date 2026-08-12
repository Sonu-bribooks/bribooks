<?php defined('BASEPATH') or exit('No direct script access allowed');

class BookTitleVerso_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_title_verso_id = 0) {
		$this->db->select('book_title_verso.*');

		$this->db->where('book_title_verso.id', (int)$book_title_verso_id);
		$this->db->where('book_title_verso._deleted', 0);

		return $this->db->get('book_title_verso')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_title_verso.*');

		if (isset($data['book_id'])) {
			$this->db->where('book_title_verso.book_id', (int) $data['book_id']);
		}

		$this->db->where('book_title_verso._deleted', 0);

		$this->db->from('book_title_verso');

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
			'book_title_verso.book_id',
			'book_title_verso.status',
			'book_title_verso.date_added',
			'book_title_verso.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_title_verso.date_added';
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
		$this->db->insert('book_title_verso', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$book_title_verso_id = $this->db->insert_id();

		return $book_title_verso_id;
	}

	public function edit($book_title_verso_id = 0, $data = []) {
		$this->db->where('id', (int)$book_title_verso_id);
		$this->db->update('book_title_verso', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($book_title_verso_id = 0) {
		$this->db->where('id', (int)$book_title_verso_id);
		$this->db->update('book_title_verso',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
