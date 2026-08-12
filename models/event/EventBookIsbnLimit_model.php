<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventBookIsbnLimit_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_book_isbn_limit_id = 0) {
		$this->db->select('event_book_isbn_limit.*');

		$this->db->where('event_book_isbn_limit.id', (int)$event_book_isbn_limit_id);
		$this->db->where('event_book_isbn_limit._deleted', 0);

		return $this->db->get('event_book_isbn_limit')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_book_isbn_limit.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_book_isbn_limit.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('event_book_isbn_limit.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_book_isbn_limit.event_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_book_isbn_limit._deleted', 0);

		$this->db->from('event_book_isbn_limit');

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
			'event_book_isbn_limit.id',
			'event_book_isbn_limit.event_id',
			'event_book_isbn_limit.date_added',
			'event_book_isbn_limit.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_book_isbn_limit.date_added';
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
		$this->db->insert('event_book_isbn_limit', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_book_isbn_limit_id = $this->db->insert_id();

		return $event_book_isbn_limit_id;
	}

	public function edit($event_book_isbn_limit_id = 0, $data = []) {
		$this->db->where('id', (int)$event_book_isbn_limit_id);
		$this->db->update('event_book_isbn_limit', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_book_isbn_limit_id = 0) {
		$this->db->where('id', (int)$event_book_isbn_limit_id);
		$this->db->update('event_book_isbn_limit',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
