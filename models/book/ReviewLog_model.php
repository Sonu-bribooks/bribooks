<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ReviewLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_bank_id = 0) {
		$this->db->select('user_bank.*');

		$this->db->where('user_bank.id', (int)$user_bank_id);
		$this->db->where('user_bank._deleted', 0);

		return $this->db->get('reviewer_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('reviewer_logs.*');

		if (isset($data['book_id'])) {
			$this->db->where('reviewer_logs.book_id', (int)$data['book_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('reviewer_logs.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('reviewer_logs.comment', $data['search'], 'after');
		}

		// $this->db->where('user_bank._deleted', 0);

		$this->db->from('reviewer_logs');

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
			'reviewer_logs.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'reviewer_logs.date_added';
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
		$this->db->insert('reviewer_logs', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
		]);

		$user_bank_id = $this->db->insert_id();

		return $user_bank_id;
	}

	public function edit($user_bank_id = 0, $data = []) {
		$this->db->where('id', (int)$user_bank_id);
		$this->db->update('reviewer_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_bank_id = 0) {
		$this->db->where('id', (int)$user_bank_id);
		$this->db->update('reviewer_logs',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
