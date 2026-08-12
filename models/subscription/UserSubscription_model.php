<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserSubscription_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_subscription_plan_id = 0) {
		$this->db->select('user_subscription_plan.*, subscription_plan.name AS subscription_plan');

		$this->db->where('user_subscription_plan.id', (int)$user_subscription_plan_id);
		$this->db->where('user_subscription_plan._deleted', 0);

		$this->db->join('subscription_plan', 'subscription_plan.id = user_subscription_plan.subscription_plan_id', 'left');

		return $this->db->get('user_subscription_plan')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_subscription_plan.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_subscription_plan.user_id', (int)$data['user_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('user_subscription_plan.order_id', (int)$data['order_id']);
		}

		if (isset($data['subscription_plan_id'])) {
			$this->db->where('user_subscription_plan.subscription_plan_id', (int)$data['subscription_plan_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_subscription_plan.status', (int)$data['status']);
		}

		if (isset($data['expire_date_lt'])) {
			$this->db->where('user_subscription_plan.end_date < ', date('Y-m-d H:i:s', strtotime($data['expire_date_lt'])));
		}

		if (isset($data['expire_date_gt'])) {
			$this->db->where('user_subscription_plan.end_date > ', date('Y-m-d H:i:s', strtotime($data['expire_date_gt'])));
		}

		if (isset($data['start_date'])) {
			$this->db->where('DATE(user_subscription_plan.start_date)', date('Y-m-d', strtotime($data['start_date'])));
		}

		if (isset($data['end_date'])) {
			$this->db->where('DATE(user_subscription_plan.end_date)', date('Y-m-d', strtotime($data['end_date'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_subscription_plan.start_date', $data['search'], 'after');
			$this->db->like('user_subscription_plan.end_date', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_subscription_plan._deleted', 0);

		$this->db->from('user_subscription_plan');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'user_subscription_plan.id',
			'user_subscription_plan.status',
			'user_subscription_plan.date_added',
			'user_subscription_plan.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_subscription_plan.id';
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
		$this->db->insert('user_subscription_plan', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_subscription_plan_id = $this->db->insert_id();

		return $user_subscription_plan_id;
	}

	public function edit($user_subscription_plan_id = 0, $data = []) {
		$this->db->where('id', (int)$user_subscription_plan_id);
		$this->db->update('user_subscription_plan', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_subscription_plan_id = 0) {
		$this->db->where('id', (int)$user_subscription_plan_id);
		$this->db->update('user_subscription_plan',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
