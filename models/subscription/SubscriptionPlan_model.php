<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SubscriptionPlan_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($subscription_plan_id = 0) {
		$this->db->select('subscription_plan.*, currency.code, currency.symbol');

		$this->db->where('subscription_plan.id', (int)$subscription_plan_id);
		$this->db->where('subscription_plan._deleted', 0);

		$this->db->join('currency', 'currency.id = subscription_plan.currency_id', 'left');

		return $this->db->get('subscription_plan')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('subscription_plan.*, currency.code, currency.symbol AS currency');

		if (isset($data['site_id'])) {
			$this->db->where('subscription_plan.site_id', (int)$data['site_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('subscription_plan.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['currency_code'])) {
			$this->db->where('subscription_plan.currency_code', $data['currency_code']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('subscription_plan.country_code', $data['country_code']);
		}

		if (isset($data['special'])) {
			$this->db->where('subscription_plan.special', (int)$data['special']);
		}

		if (isset($data['status'])) {
			$this->db->where('subscription_plan.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('subscription_plan.name', $data['search'], 'after');
			$this->db->or_like('subscription_plan.currency_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('subscription_plan._deleted', 0);

		$this->db->join('currency', 'currency.id = subscription_plan.currency_id', 'left');
		$this->db->from('subscription_plan');

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
			'subscription_plan.id',
			'subscription_plan.name',
			'subscription_plan.status',
			'subscription_plan.sort_order',
			'subscription_plan.date_added',
			'subscription_plan.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'subscription_plan.id';
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
		$this->db->insert('subscription_plan', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$data['site_id'] ?? (int)$this->config->item('site_id'),
		]);

		$subscription_plan_id = $this->db->insert_id();

		return $subscription_plan_id;
	}

	public function edit($subscription_plan_id = 0, $data = []) {
		$this->db->where('id', $subscription_plan_id);
		$this->db->update('subscription_plan', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($subscription_plan_id = 0) {
		$this->db->where('id', $subscription_plan_id);
		$this->db->update('subscription_plan',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
