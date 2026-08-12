<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserCreditRedeemJob_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_credit_redeem_job_id = 0) {
		$this->db->select('user_credit_redeem_job.*');

		$this->db->where('user_credit_redeem_job.id', (int)$user_credit_redeem_job_id);
		$this->db->where('user_credit_redeem_job._deleted', 0);

		return $this->db->get('user_credit_redeem_job')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_credit_redeem_job.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_credit_redeem_job.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_credit_redeem_job.status', (int)$data['status']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(user_credit_redeem_job.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_credit_redeem_job.status', $data['search'], 'after');
			$this->db->or_like('user_credit_redeem_job.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_credit_redeem_job._deleted', 0);

		$this->db->from('user_credit_redeem_job');

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
			'user_credit_redeem_job.status',
			'user_credit_redeem_job.date_added',
			'user_credit_redeem_job.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_credit_redeem_job.id';
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
		$this->db->select_sum('user_credit_redeem_job.credit');

		if (isset($data['user_id'])) {
			$this->db->where('user_credit_redeem_job.user_id', (int)$data['user_id']);
		}

		if (isset($data['bank_account_number'])) {
			$this->db->where('user_credit_redeem_job.bank_account_number', $data['bank_account_number']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_credit_redeem_job.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_credit_redeem_job.credit', $data['search'], 'after');
			$this->db->or_like('user_credit_redeem_job.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_credit_redeem_job._deleted', 0);

		$this->db->from('user_credit_redeem_job');

		return $this->db->get()->row()->amount ?? 0;
	}

	public function add($data = []) {
		$this->db->insert('user_credit_redeem_job', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_credit_redeem_job_id = $this->db->insert_id();

		return $user_credit_redeem_job_id;
	}

	public function edit($user_credit_redeem_job_id = 0, $data = []) {
		$this->db->where('id', (int)$user_credit_redeem_job_id);
		$this->db->update('user_credit_redeem_job', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_credit_redeem_job_id = 0) {
		$this->db->where('id', (int)$user_credit_redeem_job_id);
		$this->db->update('user_credit_redeem_job',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
