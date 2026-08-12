<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SubscriptionPayment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($subscription_payment_id = 0) {
		$this->db->select('subscription_payment.*, subscription_plan.name AS subscription_plan, currency.code as currency_code');

		$this->db->where('subscription_payment.id', (int)$subscription_payment_id);
		$this->db->where('subscription_payment._deleted', 0);

		$this->db->join('subscription_plan', 'subscription_plan.id = subscription_payment.subscription_plan_id', 'left');
		$this->db->join('currency', 'currency.id = subscription_payment.currency_id', 'left');

		return $this->db->get('subscription_payment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('subscription_payment.*, subscription_plan.name AS subscription_plan, currency.code as currency_code');

		if (isset($data['site_id'])) {
			$this->db->where('subscription_payment.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('subscription_payment.user_id', (int)$data['user_id']);
		}

		if (isset($data['subscription_plan_id'])) {
			$this->db->where('subscription_payment.subscription_plan_id', (int)$data['subscription_plan_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('subscription_payment.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('subscription_payment.provider', $data['provider']);
		}

		if (isset($data['subscription_order_id'])) {
			$this->db->where('subscription_payment.subscription_order_id', (int)$data['subscription_order_id']);
		}

		if (isset($data['amount'])) {
			$this->db->where('subscription_payment.amount', (double)$data['amount']);
		}

		if (isset($data['status'])) {
			$this->db->where('subscription_payment.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('subscription_payment.amount', $data['search'], 'after');
			$this->db->or_like('subscription_plan.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('subscription_payment._deleted', 0);

		$this->db->join('subscription_plan', 'subscription_plan.id = subscription_payment.subscription_plan_id', 'left');
		$this->db->join('currency', 'currency.id = subscription_payment.currency_id', 'left');
		$this->db->from('subscription_payment');

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
			'subscription_payment.id',
			'subscription_payment.amount',
			'subscription_payment.status',
			'subscription_payment.date_added',
			'subscription_payment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'subscription_payment.id';
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
		$this->db->insert('subscription_payment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$subscription_payment_id = $this->db->insert_id();

		return $subscription_payment_id;
	}

	public function edit($subscription_payment_id = 0, $data = []) {
		$this->db->where('id', (int)$subscription_payment_id);
		$this->db->update('subscription_payment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($subscription_payment_id = 0) {
		$this->db->where('id', (int)$subscription_payment_id);
		$this->db->update('subscription_payment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getList($data = []) {
		$this->db->select('
			subscription_payment.*,
			user_subscription_plan.start_date,
			user_subscription_plan.end_date,
			users.first_name,
			users.last_name,
			users.email,
			users.mobile,
			subscription_plan.name as plan_name,
			subscription_plan.price as plan_price,
			currency.code as currency_code
		');

		if (isset($data['user_id'])) {
			$this->db->where('user_subscription_plan.user_id', (int)$data['user_id']);
		}

		if (isset($data['subscription_plan_id'])) {
			$this->db->where('user_subscription_plan.subscription_plan_id', (int)$data['subscription_plan_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_subscription_plan.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('user_subscription_plan.start_date', $data['search'], 'after');
			$this->db->like('user_subscription_plan.end_date', $data['search'], 'after');
		}

		$this->db->where('subscription_payment._deleted', 0);

		$this->db->join('users', 'users.id = subscription_payment.user_id', 'left');
		$this->db->join('user_subscription_plan', 'user_subscription_plan.id = subscription_payment.subscription_order_id', 'left');
		$this->db->join('subscription_plan', 'subscription_plan.id = subscription_payment.subscription_plan_id', 'left');
		$this->db->join('currency', 'currency.id = subscription_payment.currency_id', 'left');

		$this->db->from('subscription_payment');

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
			'subscription_payment.status',
			'subscription_payment.date_added',
			'subscription_payment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'subscription_payment.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
