<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HallOfFameAsia_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('hall_of_fame_asia_books.*');

		$this->db->where('hall_of_fame_asia_books.id', (int)$id);
		$this->db->where('hall_of_fame_asia_books._deleted', 0);

		return $this->db->get('hall_of_fame_asia_books')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			hall_of_fame_asia_books.event_id,
			hall_of_fame_asia_books.location,
			hall_of_fame_asia_books.country_code,
			bookstore.*,
			COALESCE((
				SELECT hfc.priority
				FROM hall_of_fame_asia_country hfc
				WHERE hfc.country_code = hall_of_fame_asia_books.country_code
				AND hfc.event_id = CASE
					WHEN EXISTS (
						SELECT 1
						FROM hall_of_fame_asia_country ehfc
						WHERE ehfc.country_code = hall_of_fame_asia_books.country_code
							AND ehfc.event_id = hall_of_fame_asia_books.event_id
							AND ehfc.event_id > 0
							AND ehfc._deleted = 0
							AND ehfc.status = 1
					)
					THEN hall_of_fame_asia_books.event_id
					ELSE 0
				END
				AND hfc._deleted = 0
				AND hfc.status = 1
				LIMIT 1
			), 5) AS priority,

			bookstore.sold AS sold');

		if (isset($data['book_id'])) {
			$this->db->where('hall_of_fame_asia_books.book_id', (int) $data['book_id']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('hall_of_fame_asia_books.country_code', $data['country_code']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('hall_of_fame_asia_books.event_id', (int) $data['event_id']);
		}

		$this->db->where('
			bookstore.sold >= COALESCE((
				SELECT hfc.book_sold
				FROM hall_of_fame_asia_country hfc
				WHERE hfc.country_code = hall_of_fame_asia_books.country_code
				AND hfc.event_id = CASE
					WHEN EXISTS (
						SELECT 1
						FROM hall_of_fame_asia_country ehfc
						WHERE ehfc.country_code = hall_of_fame_asia_books.country_code
							AND ehfc.event_id = hall_of_fame_asia_books.event_id
							AND ehfc.event_id > 0
							AND ehfc._deleted = 0
							AND ehfc.status = 1
					)
					THEN hall_of_fame_asia_books.event_id
					ELSE 0
				END
				AND hfc._deleted = 0
				AND hfc.status = 1
				LIMIT 1
			), 1)
		', null, false);

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('bookstore.book_id', $data['search'], 'after');
			$this->db->or_like('bookstore.name', $data['search'], 'both');
			$this->db->or_like('bookstore.author_name', $data['search'], 'both');
			$this->db->or_like('hall_of_fame_asia_books.location', $data['search'], 'after');
			$this->db->or_like('hall_of_fame_asia_books.country_code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('hall_of_fame_asia_books._deleted', 0);
		$this->db->where('bookstore._deleted', 0);

		$this->db->join('bookstore', 'bookstore.book_id = hall_of_fame_asia_books.book_id');

		$this->db->from('hall_of_fame_asia_books');

		$total = $this->db->count_all_results('', false);

		if (isset($data['start']) && isset($data['limit'])) {
			$start = max(0, (int) $data['start']);
			$limit = (int) $data['limit'];

			if ($limit < 1) {
				$limit = 10;
			}

			$this->db->limit($limit, $start);
		}

		$sort_data = [
			'priority',
			'hall_of_fame_asia_books.id',
			'hall_of_fame_asia_books.book_id',
			'hall_of_fame_asia_books.date_added',
			'hall_of_fame_asia_books.date_modified'
		];

		$sort = isset($data['sort']) && in_array($data['sort'], $sort_data, true)
			? $data['sort']
			: 'hall_of_fame_asia_books.id';

		$order = isset($data['order']) && strtoupper($data['order']) === 'ASC'
			? 'ASC'
			: 'DESC';

		$this->db->order_by($sort, $order);
		$this->db->order_by('sold', 'DESC');

		$results = $this->db->get()->result_array();

		return [
			'rows'  => $results,
			'total' => $total
		];
	}

	public function add($data = []) {
		$this->db->insert('hall_of_fame_asia_books', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();
		
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('hall_of_fame_asia_books', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('hall_of_fame_asia_books',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByBookId($book_id = 0) {
		$this->db->select('hall_of_fame_asia_books.*');
		$this->db->where('hall_of_fame_asia_books.book_id', (int)$book_id);
		return $this->db->get('hall_of_fame_asia_books')->row_array();
	}

	public function deleteByBookId($book_id = 0) {
		$this->db->where('book_id', (int)$book_id);
		$this->db->where('_deleted', 0);
		$this->db->update('hall_of_fame_asia_books',  [
			'_deleted'			=> 1,
			'date_deleted'		=> date('Y-m-d H:i:s'),
			'deleted_manager_id'=> $this->session->userdata('user_id') ?? 0
		]);
	}
}
