<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SubscriptionOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($subscription_order_id = 0) {
		$this->db->select('subscription_order.*, subscription_plan.name AS subscription_plan');

		$this->db->where('subscription_order.id', (int)$subscription_order_id);
		$this->db->where('subscription_order._deleted', 0);

		$this->db->join('subscription_plan', 'subscription_plan.id = subscription_order.subscription_plan_id', 'left');
		$this->db->join('currency', 'currency.id = subscription_order.currency_id', 'left');

		return $this->db->get('subscription_order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('subscription_order.*, subscription_plan.name AS subscription_plan');

		if (isset($data['site_id'])) {
			$this->db->where('subscription_order.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('subscription_order.user_id', (int)$data['user_id']);
		}

		if (isset($data['subscription_plan_id'])) {
			$this->db->where('subscription_order.subscription_plan_id', (int)$data['subscription_plan_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('subscription_order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('subscription_order.provider', $data['provider']);
		}

		if (isset($data['amount'])) {
			$this->db->where('subscription_order.amount', (double)$data['amount']);
		}

		if (isset($data['status'])) {
			$this->db->where('subscription_order.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('subscription_order.amount', $data['search'], 'after');
			$this->db->or_like('subscription_plan.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('subscription_order._deleted', 0);

		$this->db->join('subscription_plan', 'subscription_plan.id = subscription_order.subscription_plan_id', 'left');
		$this->db->join('currency', 'currency.id = subscription_order.currency_id', 'left');
		$this->db->from('subscription_order');

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
			'subscription_order.id',
			'subscription_order.amount',
			'subscription_order.status',
			'subscription_order.date_added',
			'subscription_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'subscription_order.id';
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
		$this->db->insert('subscription_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$subscription_order_id = $this->db->insert_id();

		return $subscription_order_id;
	}

	public function edit($subscription_order_id = 0, $data = []) {
		$this->db->where('id', (int)$subscription_order_id);
		$this->db->update('subscription_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($subscription_order_id = 0) {
		$this->db->where('id', (int)$subscription_order_id);
		$this->db->update('subscription_order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
