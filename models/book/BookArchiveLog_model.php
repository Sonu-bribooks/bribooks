<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookArchiveLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('book_archive_log.*');

		$this->db->where('book_archive_log.id', (int)$id);
		$this->db->where('book_archive_log._deleted', 0);
		return $this->db->get('book_archive_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_archive_log.*');

		if (isset($data['book_id'])) {
			$this->db->where('book_archive_log.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book_archive_log.user_id', (int)$data['user_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('book_archive_log.ip', $data['ip']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_archive_log.book_id', $data['search'], 'after');
			$this->db->like('book_archive_log.user_id', $data['search'], 'after');
			$this->db->like('book_archive_log.ip', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_archive_log._deleted', 0);

		$this->db->from('book_archive_log');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'book_archive_log.id',
			'book_archive_log.date_added',
			'book_archive_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_archive_log.id';
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
		$this->db->insert('book_archive_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_archive_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_archive_log', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
