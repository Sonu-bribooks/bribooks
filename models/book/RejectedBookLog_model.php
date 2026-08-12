<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RejectedBookLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($rejected_book_log_id = 0) {
		$this->db->select('rejected_book_log.*,
			book.name AS book
		');

		$this->db->where('rejected_book_log.id', (int)$rejected_book_log_id);
		$this->db->where('rejected_book_log._deleted', 0);

		$this->db->join('book_version as book', 'book.book_id = rejected_book_log.book_id AND book.version = rejected_book_log.version', 'left');

		return $this->db->get('rejected_book_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('rejected_book_log.*,
			book.name AS book
		');

		if (isset($data['assignment_id'])) {
			$this->db->where('rejected_book_log.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['printer_id'])) {
			$this->db->where('rejected_book_log.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['manager_id'])) {
			$this->db->where('rejected_book_log.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('rejected_book_log.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('rejected_book_log.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->where('rejected_book_log.option', $data['option']);
		}

		if (isset($data['quantity'])) {
			$this->db->where('rejected_book_log.quantity', (int)$data['quantity']);
		}

		if (isset($data['status'])) {
			$this->db->where('rejected_book_log.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('rejected_book_log.book_id', $data['search'], 'after');
			$this->db->or_like('rejected_book_log.option', $data['search'], 'after');
			$this->db->or_like('rejected_book_log.quantity', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('rejected_book_log._deleted', 0);

		$this->db->join('book_version as book', 'book.book_id = rejected_book_log.book_id AND book.version = rejected_book_log.version', 'left');
		$this->db->from('rejected_book_log');

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
			'book.name',
			'rejected_book_log.quantity',
			'rejected_book_log.version',
			'rejected_book_log.status',
			'rejected_book_log.date_added',
			'rejected_book_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'rejected_book_log.date_added';
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
		$this->db->insert('rejected_book_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$rejected_book_log_id = $this->db->insert_id();

		return $rejected_book_log_id;
	}

	public function edit($rejected_book_log_id = 0, $data = []) {
		$this->db->where('id', (int)$rejected_book_log_id);
		$this->db->update('rejected_book_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($rejected_book_log_id = 0) {
		$this->db->where('id', (int)$rejected_book_log_id);
		$this->db->update('rejected_book_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('rejected_book_log', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
