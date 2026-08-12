<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookStockLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_stock_log_id = 0) {
		$this->db->select('book_stock_log.*,
			book.name AS book
		');

		$this->db->where('book_stock_log.id', (int)$book_stock_log_id);
		$this->db->where('book_stock_log._deleted', 0);

		$this->db->join('book_version as book', 'book.book_id = book_stock_log.book_id AND book.version = book_stock_log.version', 'left');

		return $this->db->get('book_stock_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_stock_log.*,
			book.name AS book
		');

		if (isset($data['pickup_location_id'])) {
			$this->db->where('book_stock_log.pickup_location_id', (int)$data['pickup_location_id']);
		}

		if (isset($data['manager_id'])) {
			$this->db->where('book_stock_log.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_stock_log.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('book_stock_log.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->where('book_stock_log.option', $data['option']);
		}

		if (isset($data['quantity'])) {
			$this->db->where('book_stock_log.quantity', (int)$data['quantity']);
		}

		if (isset($data['status'])) {
			$this->db->where('book_stock_log.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book_stock_log.book_id', $data['search'], 'after');
			$this->db->or_like('book_stock_log.option', $data['search'], 'after');
			$this->db->or_like('book_stock_log.quantity', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_stock_log._deleted', 0);

		$this->db->join('book_version as book', 'book.book_id = book_stock_log.book_id AND book.version = book_stock_log.version', 'left');
		$this->db->from('book_stock_log');

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
			'book_stock_log.quantity',
			'book_stock_log.version',
			'book_stock_log.status',
			'book_stock_log.date_added',
			'book_stock_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_stock_log.date_added';
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
		$this->db->insert('book_stock_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$book_stock_log_id = $this->db->insert_id();

		return $book_stock_log_id;
	}

	public function edit($book_stock_log_id = 0, $data = []) {
		$this->db->where('id', (int)$book_stock_log_id);
		$this->db->update('book_stock_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($book_stock_log_id = 0) {
		$this->db->where('id', (int)$book_stock_log_id);
		$this->db->update('book_stock_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('book_stock_log', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
