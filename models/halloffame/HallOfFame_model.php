<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HallOfFame_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('hall_of_fame_books.*');

		$this->db->where('hall_of_fame_books.id', (int)$id);
		$this->db->where('hall_of_fame_books._deleted', 0);

		return $this->db->get('hall_of_fame_books')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			hall_of_fame_books.event_id,
			hall_of_fame_books.location,
			hall_of_fame_books.country_code,
			bookstore.*,
			(SELECT COALESCE(SUM(priority), 5) FROM hall_of_fame_country WHERE country_code = hall_of_fame_books.country_code AND _deleted = 0) AS priority,
			bookstore.sold AS sold
		');

		if (isset($data['book_id'])) {
			$this->db->where('hall_of_fame_books.book_id', (int)$data['book_id']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('hall_of_fame_books.country_code', $data['country_code']);
		}

		$this->db->where('sold >= (SELECT COALESCE(SUM(book_sold), 1) FROM hall_of_fame_country WHERE country_code = hall_of_fame_books.country_code AND _deleted = 0)');


		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('bookstore.book_id', $data['search'], 'after');
			$this->db->or_like('bookstore.name', $data['search'], 'both');
			$this->db->or_like('bookstore.author_name', $data['search'], 'both');
			$this->db->or_like('hall_of_fame_books.location', $data['search'], 'after');
			$this->db->or_like('hall_of_fame_books.country_code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('hall_of_fame_books._deleted', 0);
		$this->db->where('bookstore._deleted', 0);

		$this->db->join('bookstore', 'bookstore.book_id = hall_of_fame_books.book_id');

		$this->db->from('hall_of_fame_books');

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
			'priority',
			'hall_of_fame_books.id',
			'hall_of_fame_books.book_id',
			'hall_of_fame_books.date_added',
			'hall_of_fame_books.date_modified'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'hall_of_fame_books.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, sold DESC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('hall_of_fame_books', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();
		
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('hall_of_fame_books', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('hall_of_fame_books',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByBookId($book_id = 0) {
		$this->db->select('hall_of_fame_books.*');
		$this->db->where('hall_of_fame_books.book_id', (int)$book_id);
		return $this->db->get('hall_of_fame_books')->row_array();
	}

	public function deleteByBookId($book_id = 0) {
		$this->db->where('book_id', (int)$book_id);
		$this->db->where('_deleted', 0);
		$this->db->update('hall_of_fame_books',  [
			'_deleted'			=> 1,
			'date_deleted'		=> date('Y-m-d H:i:s'),
			'deleted_manager_id'=> $this->session->userdata('user_id') ?? 0
		]);
	}
}
