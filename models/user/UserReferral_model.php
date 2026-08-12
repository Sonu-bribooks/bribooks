<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserReferral_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('user_referrals.*');

		$this->db->where('user_referrals.id', (int)$id);
		$this->db->where('user_referrals._deleted', 0);

		return $this->db->get('user_referrals')->row_array();
	}

	public function getByUserId($user_id = 0) {
		$this->db->select('user_referrals.*');
		$this->db->where('user_referrals.user_id', (int)$user_id);
		$this->db->where('user_referrals._deleted', 0);
		$this->db->order_by('user_referrals.id', 'DESC');
		return $this->db->get('user_referrals')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_referrals.*');

		if (isset($data['event_id'])) {
			$this->db->where('user_referrals.event_id', (int)$data['event_id']);
		}

		if (isset($data['referrer_id'])) {
			$this->db->where('user_referrals.referrer_id', (int)$data['referrer_id']);
		}

		if (isset($data['referral_id'])) {
			$this->db->where('user_referrals.referral_id', (int)$data['referral_id']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('user_referrals.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		$this->db->where('user_referrals._deleted', 0);

		$this->db->from('user_referrals');

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
			'user_referrals.id',
			'user_referrals.date_added',
			'user_referrals.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_referrals.id';
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
		$this->db->insert('user_referrals', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_referrals', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_referrals',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
