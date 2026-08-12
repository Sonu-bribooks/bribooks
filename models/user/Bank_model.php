<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Bank_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_bank_id = 0) {
		$this->db->select('user_bank.*');

		$this->db->where('user_bank.id', (int)$user_bank_id);
		$this->db->where('user_bank._deleted', 0);

		return $this->db->get('user_bank')->row_array();
	}

	public function getByUid($user_id = 0) {
		$this->db->select('user_bank.*');
		$this->db->where('user_bank._deleted', 0);
		$this->db->where('user_bank.user_id', (int)$user_id);
		return $this->db->get('user_bank')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_bank.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_bank.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_bank.status', (int)$data['status']);
		}

		if (isset($data['account_number'])) {
			$this->db->like('user_bank.account_number', $data['account_number'], 'before');
		}

		if (isset($data['ifsc_code'])) {
			$this->db->where('user_bank.ifsc_code', $data['ifsc_code']);
		}

		if (isset($data['branch_name'])) {
			$this->db->where('user_bank.branch_name', $data['branch_name']);
		}

		if (isset($data['bank_name'])) {
			$this->db->where('user_bank.bank_name', $data['bank_name']);
		}

		if (isset($data['name'])) {
			$this->db->where('user_bank.name', $data['name']);
		}

		if (!empty($data['search'])) {
			$this->db->like('user_bank.name', $data['search'], 'after');
			$this->db->or_like('user_bank.bank_name', $data['search'], 'after');
			$this->db->or_like('user_bank.branch_name', $data['search'], 'after');
			$this->db->or_like('user_bank.account_number', $data['search'], 'after');
			$this->db->or_like('user_bank.ifsc_code', $data['search'], 'after');
		}

		$this->db->where('user_bank._deleted', 0);

		$this->db->from('user_bank');

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
			'user_bank.name',
			'user_bank.bank_name',
			'user_bank.branch_name',
			'user_bank.ifsc_code',
			'user_bank.date_added',
			'user_bank.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_bank.id';
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
		$this->db->insert('user_bank', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_bank_id = $this->db->insert_id();

		return $user_bank_id;
	}

	public function edit($user_bank_id = 0, $data = []) {
		$this->db->where('id', (int)$user_bank_id);
		$this->db->update('user_bank', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_bank_id = 0) {
		$this->db->where('id', (int)$user_bank_id);
		$this->db->update('user_bank',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
