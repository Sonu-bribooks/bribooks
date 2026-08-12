<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserReferralList_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('user_referral_list.*');

		$this->db->where('user_referral_list.id', (int)$id);
		return $this->db->get('user_referral_list')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_referral_list.*, users.first_name, users.last_name, users.source, referral_user.first_name as referral_first_name, referral_user.last_name as referral_last_name');

		if (isset($data['referral_id'])) {
			$this->db->where('user_referral_list.referral_id', (int)$data['referral_id']);
		}

        if (isset($data['user_id'])) {
			$this->db->where('user_referral_list.user_id', (int)$data['user_id']);
		}

        if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'both');
			$this->db->or_like('CONCAT(referral_user.first_name, " ", referral_user.last_name)', $data['search'], 'both');
			$this->db->group_end();
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('user_referral_list.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		$this->db->where('user_referral_list._deleted', 0);

		$this->db->from('user_referral_list');

		$this->db->join('users as referral_user', 'referral_user.id = user_referral_list.referral_id', 'left');
		$this->db->join('users', 'users.id = user_referral_list.user_id', 'left');

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
			'user_referral_list.id',
			'user_referral_list.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_referral_list.id';
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
		$this->db->insert('user_referral_list', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_referral_list', $data + [
			'date_modified'	=> date('Y-m-d H:i:s')
		]);
	}
}
