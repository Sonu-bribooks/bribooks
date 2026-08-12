<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserTransaction_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_transaction_id = 0) {
		$this->db->select('user_transaction.*');

		$this->db->where('user_transaction.id', (int)$user_transaction_id);
		$this->db->where('user_transaction._deleted', 0);

		return $this->db->get('user_transaction')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_transaction.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_transaction.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_transaction.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('user_transaction.name', $data['search'], 'after');
			$this->db->or_like('user_transaction.bank_name', $data['search'], 'after');
			$this->db->or_like('user_transaction.branch_name', $data['search'], 'after');
			$this->db->or_like('user_transaction.account_number', $data['search'], 'after');
			$this->db->or_like('user_transaction.ifsc_code', $data['search'], 'after');
		}

		$this->db->where('user_transaction._deleted', 0);

		$this->db->from('user_transaction');

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
			'user_transaction.amount',
			'user_transaction.status',
			'user_transaction.date_added',
			'user_transaction.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_transaction.date_added';
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
		$this->db->insert('user_transaction', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_transaction_id = $this->db->insert_id();

		return $user_transaction_id;
	}

	public function edit($user_transaction_id = 0, $data = []) {
		$this->db->where('id', (int)$user_transaction_id);
		$this->db->update('user_transaction', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_transaction_id = 0) {
		$this->db->where('id', (int)$user_transaction_id);
		$this->db->update('user_transaction',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
