<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_book_id = 0) {
		$this->db->select('event_book.*');

		$this->db->where('event_book.id', (int)$event_book_id);
		$this->db->where('event_book._deleted', 0);

		return $this->db->get('event_book')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_book.*, book.user_id, book.name as book_name, event.name as event_name');

		if (isset($data['book_id'])) {
			$this->db->where('event_book.book_id', (int)$data['book_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_book.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('users.id', (int)$data['user_id']);
		}

		if (isset($data['active_status'])) {
			$this->db->where('book.status', 1);
		}

		if (!empty($data['is_active_book_writing'])) {
			$this->db->where(
				vsprintf('event_book.event_id IN (SELECT id FROM event where book_writing_start_date <= \'%s\' AND book_writing_end_date >= \'%s\')', [
					date('Y-m-d H:i:s'),
					date('Y-m-d H:i:s'),
				])
			);
		}

		$this->db->where('event_book._deleted', 0);

		$this->db->join('event', 'event.id = event_book.event_id', 'left');
		$this->db->join('book', 'book.id = event_book.book_id', 'left');
		
		if (isset($data['site_id']) || isset($data['user_id'])) {
			$this->db->join('users', 'users.id = book.user_id', 'left');
		}

		$this->db->from('event_book');

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
			'event_book.date_added',
			'event_book.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_book.id';
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
		$this->db->insert('event_book', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_book_id = $this->db->insert_id();

		return $event_book_id;
	}

	public function edit($event_book_id = 0, $data = []) {
		$this->db->where('id', (int)$event_book_id);
		$this->db->update('event_book', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_book_id = 0) {
		$this->db->where('id', (int)$event_book_id);
		$this->db->update('event_book',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getEventBookByBookId($event_id = 0, $book_id = 0) {
		$this->db->select('event_book.*');

		if (!empty($event_id)) { 
			$this->db->where('event_book.event_id', (int)$event_id); 
		}
		
		$this->db->where('event_book.book_id', (int)$book_id);
		$this->db->where('event_book._deleted', 0);

		return $this->db->get('event_book')->row_array();
	}
}
