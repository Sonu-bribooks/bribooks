<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookExhibition_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('book_exhibition.*');
		$this->db->where('book_exhibition.id', (int)$id);
		$this->db->where('book_exhibition._deleted', 0);

		return $this->db->get('book_exhibition')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_exhibition.*');

		if (isset($data['event_id'])) {
			$this->db->where('book_exhibition.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book_exhibition.user_id', (int)$data['user_id']);
		}

		if (isset($data['email'])) {
			$this->db->where('book_exhibition.email', $data['email']);
		}

		if (isset($data['mobile'])) {
			$this->db->where('book_exhibition.mobile', $data['mobile']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_exhibition.book_id', (int)$data['book_id']);
		}

		if (isset($data['code'])) {
			$this->db->where('book_exhibition.code', (int)$data['code']);
		}

		if (isset($data['slot_id'])) {
			$this->db->where('book_exhibition.slot_id', (int)$data['slot_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('book_exhibition.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->db->where('book_exhibition.verified', (int)$data['verified']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_exhibition.name', $data['search'], 'after');
			$this->db->or_like('book_exhibition.email', $data['search'], 'after');
			$this->db->or_like('book_exhibition.mobile', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_exhibition._deleted', 0);
		$this->db->from('book_exhibition');

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
			'book_exhibition.date_added',
			'book_exhibition.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_exhibition.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('book_exhibition', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_exhibition', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByUserEvent($user_id = 0, $data = [], $event_id = '') {
		$this->db->where('user_id', (int)$user_id);

		if (!empty($event_id)) {
			$this->db->where('event_id', (int)$event_id);
		}
		$this->db->update('book_exhibition', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCode($code = '', $event_id = 0) {
		$this->db->where('code', $code);

		if (!empty($event_id)) {
			$this->db->where('event_id', (int)$event_id);
		}

		return $this->db->get('book_exhibition')->row_array();
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_exhibition',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
