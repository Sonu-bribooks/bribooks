<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserCredit_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_credit_id = 0) {
		$this->db->select('user_credit.*');

		$this->db->where('user_credit.id', (int)$user_credit_id);
		$this->db->where('user_credit._deleted', 0);

		return $this->db->get('user_credit')->row_array();
	}

	public function getByUserId($user_id = 0) {
		$this->db->select('user_credit.*');

		$this->db->where('user_credit.user_id', (int)$user_id);
		$this->db->where('user_credit._deleted', 0);

		return $this->db->get('user_credit')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_credit.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_credit.user_id', (int)$data['user_id']);
		}

		if (isset($data['currency_code'])) {
			$this->db->where('user_credit.currency_code', $data['currency_code']);
		}

		if (isset($data['credit_le'])) {
			$this->db->where('user_credit.credit <= ', (double)$data['credit_le']);
		}

		if (isset($data['credit_ge'])) {
			$this->db->where('user_credit.credit >= ', (double)$data['credit_ge']);
		}

		if (isset($data['credit'])) {
			$this->db->where('user_credit.credit', (double)$data['credit']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_credit.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_credit.credit', $data['search'], 'after');
			$this->db->or_like('user_credit.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_credit._deleted', 0);

		$this->db->from('user_credit');

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
			'user_credit.credit',
			'user_credit.status',
			'user_credit.date_added',
			'user_credit.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_credit.date_added';
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
		$this->db->insert('user_credit', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_credit_id = $this->db->insert_id();

		return $user_credit_id;
	}

	public function edit($user_credit_id = 0, $data = []) {
		$this->db->where('id', (int)$user_credit_id);
		$this->db->update('user_credit', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_credit_id = 0) {
		$this->db->where('id', (int)$user_credit_id);
		$this->db->update('user_credit',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
