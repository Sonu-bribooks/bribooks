<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookWritingLogs_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($log_id = 0) {
		$this->db->select('book_writing_log.*');

		$this->db->where('book_writing_log.id', (int)$log_id);
		$this->db->where('book_writing_log._deleted', 0);

		return $this->db->get('book_writing_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_writing_log.*');

		if (isset($data['browser'])) {
			$this->db->where('book_writing_log.browser', (int)$data['browser']);
		}

		if (isset($data['platform'])) {
			$this->db->where('book_writing_log.platform', (int)$data['platform']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_writing_log.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book_writing_log.user_id', (int)$data['user_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('book_writing_log.ip', $data['ip']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(book_writing_log.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		$this->db->where('book_writing_log._deleted', 0);
		$this->db->from('book_writing_log');

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
			'book_writing_log.date_added',
			'book_writing_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_writing_log.date_added';
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
		$this->db->insert('book_writing_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$log_id = $this->db->insert_id();

		return $log_id;
	}

	public function edit($log_id = 0, $data = []) {
		$this->db->where('id', $log_id);
		$this->db->update('book_writing_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($log_id = 0) {
		$this->db->where('id', $log_id);
		$this->db->update('book_writing_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
