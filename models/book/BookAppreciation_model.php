<?php defined('BASEPATH') or exit('No direct script access allowed');

class BookAppreciation_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_appreciation_id = 0) {
		$this->db->select('book_appreciation.*');

		$this->db->where('book_appreciation.id', (int)$book_appreciation_id);
		$this->db->where('book_appreciation._deleted', 0);

		return $this->db->get('book_appreciation')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_appreciation.*');

		if (isset($data['book_id'])) {
			$this->db->where('book_appreciation.book_id', (int) $data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book_appreciation.user_id', (int) $data['user_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('book_appreciation.ip', $data['ip']);
		}

		$this->db->where('book_appreciation._deleted', 0);

		$this->db->from('book_appreciation');

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
			'book_appreciation.name',
			'book_appreciation.status',
			'book_appreciation.date_added',
			'book_appreciation.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_appreciation.date_added';
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
		$this->db->insert('book_appreciation', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$book_appreciation_id = $this->db->insert_id();

		return $book_appreciation_id;
	}

	public function edit($book_appreciation_id = 0, $data = []) {
		$this->db->where('id', (int)$book_appreciation_id);
		$this->db->update('book_appreciation', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('book_appreciation', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}

	public function delete($book_appreciation_id = 0) {
		$this->db->where('id', (int)$book_appreciation_id);
		$this->db->update('book_appreciation',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
