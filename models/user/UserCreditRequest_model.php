<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserCreditRequest_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_credit_request_id = 0) {
		$this->db->select('user_credit_request.*');

		$this->db->where('user_credit_request.id', (int)$user_credit_request_id);
		$this->db->where('user_credit_request._deleted', 0);

		return $this->db->get('user_credit_request')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_credit_request.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_credit_request.user_id', (int)$data['user_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('user_credit_request.type', (int)$data['type']);
		}

		if (isset($data['donation_type'])) {
			$this->db->where('user_credit_request.donation_type', (int)$data['donation_type']);
		}

		if (isset($data['credit'])) {
			$this->db->where('user_credit_request.credit', (double)$data['credit']);
		}

		if (isset($data['currency_code'])) {
			$this->db->where('user_credit_request.currency_code', $data['currency_code']);
		}

		if (isset($data['date_added_le'])) {
			$this->db->where('DATE(user_credit_request.date_added) <= ', date('Y-m-d', strtotime($data['date_added_le'])));
		}

		if (isset($data['status'])) {
			$this->db->where('user_credit_request.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('user_credit_request.status != ', (int)$data['ne_status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_credit_request.credit', $data['search'], 'after');
			$this->db->or_like('user_credit_request.user_id', $data['search'], 'after');
			$this->db->or_like('user_credit_request.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_credit_request._deleted', 0);

		$this->db->from('user_credit_request');

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
			'user_credit_request.credit',
			'user_credit_request.status',
			'user_credit_request.date_added',
			'user_credit_request.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_credit_request.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function getAmount($data = []) {
		$this->db->select_sum('user_credit_request.credit');

		if (isset($data['user_id'])) {
			$this->db->where('user_credit_request.user_id', (int)$data['user_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('user_credit_request.type', (int)$data['type']);
		}

		if (isset($data['donation_type'])) {
			$this->db->where('user_credit_request.donation_type', (int)$data['donation_type']);
		}

		if (isset($data['credit'])) {
			$this->db->where('user_credit_request.credit', (double)$data['credit']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_credit_request.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('user_credit_request.status != ', (int)$data['ne_status']);
		}

		if (isset($data['date_added_le'])) {
			$this->db->where('DATE(user_credit_request.date_added) < ', date('Y-m-d', strtotime($data['date_added_le'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_credit_request.credit', $data['search'], 'after');
			$this->db->or_like('user_credit_request.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_credit_request._deleted', 0);

		$this->db->from('user_credit_request');

		return $this->db->get()->row()->credit ?? 0;
	}

	public function add($data = []) {
		$this->db->insert('user_credit_request', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_credit_request_id = $this->db->insert_id();

		return $user_credit_request_id;
	}

	public function edit($user_credit_request_id = 0, $data = []) {
		$this->db->where('id', (int)$user_credit_request_id);
		$this->db->update('user_credit_request', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_credit_request_id = 0) {
		$this->db->where('id', (int)$user_credit_request_id);
		$this->db->update('user_credit_request',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
