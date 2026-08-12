<?php defined('BASEPATH') or exit('No direct script access allowed');

class EventGroupBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		$this->db->where('_deleted', 0);

		return $this->db->get('event_group_book')->row_array();
	}

	public function get_all($data = []) {
		if (empty($data)) return false;

		$this->db->select('event_group_book.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_group_book.event_id', (int)$data['event_id']);
		}

		if (isset($data['group_id'])) {
			$this->db->where('event_group_book.group_id', (int)$data['group_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_group_book.book_id', (int)$data['book_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('event_group_book.status', (int)$data['status']);
		}

		$this->db->where('event_group_book._deleted', 0);

		$this->db->from('event_group_book');

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
			'event_group_book.id',
			'event_group_book.event_id',
			'event_group_book.group_id',
			'event_group_book.book_id',
			'event_group_book.status',
			'event_group_book.date_added',
			'event_group_book.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_group_book.id';
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
		$this->db->insert('event_group_book', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_group_book', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_group_book',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
