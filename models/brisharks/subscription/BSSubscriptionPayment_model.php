<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSSubscriptionPayment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($subscription_payment_id = 0) {
		$this->bsdb->where('subscription_payment.id', (int)$subscription_payment_id);
		$this->bsdb->where('subscription_payment._deleted', 0);

		return $this->bsdb->get('subscription_payment')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('subscription_payment.*, user.first_name, user.last_name, user.email, user.mobile');

		if (isset($data['email'])) {
			$this->bsdb->where('user.email', $data['email']);
		}

		if (isset($data['mobile'])) {
			$this->bsdb->where('user.mobile', $data['mobile']);
		}

		if (isset($data['subscription_plan_id'])) {
			$this->bsdb->where('subscription_payment.subscription_plan_id', (int)$data['subscription_plan_id']);
		}

		if (isset($data['subscription_order_id'])) {
			$this->bsdb->where('subscription_payment.subscription_order_id', (int)$data['subscription_order_id']);
		}

		if (isset($data['user_id'])) {
			$this->bsdb->where('subscription_payment.user_id', (int)$data['user_id']);
		}

		if (isset($data['currency_code'])) {
			$this->bsdb->where('subscription_payment.currency_code', $data['currency_code']);
		}

		if (isset($data['provider'])) {
			$this->bsdb->where('subscription_payment.provider', $data['provider']);
		}

		if (isset($data['amount'])) {
			$this->bsdb->where('subscription_payment.amount', (double)$data['amount']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('subscription_payment.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('subscription_payment.amount', $data['search'], 'after');
			$this->bsdb->or_like('subscription_payment.provider', $data['search'], 'after');
			$this->bsdb->or_like('user.email', $data['search'], 'after');
			$this->bsdb->or_like('user.mobile', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		if (!empty($data['date_start'])) {
			$this->bsdb->where('subscription_payment.date_added >= ', date('Y-m-d', strtotime($data['date_start'])));
		}

		if (!empty($data['date_end'])) {
			$this->bsdb->where('subscription_payment.date_added < ', date('Y-m-d', strtotime($data['date_end'])));
		}

		$this->bsdb->where('subscription_payment._deleted', 0);

		$this->bsdb->from('subscription_payment');
		$this->bsdb->join('user', 'user.id = subscription_payment.user_id', 'left');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
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

		$this->bsdb->order_by($sort, $order);

		return ['rows' => $this->bsdb->get()->result_array(), 'total' => $total];
	}

	public function stats($data = []) {
		$this->bsdb->select('SUM(subscription_payment.amount * currency.exchange_rate) as total', false);

		if (isset($data['subscription_plan_id'])) {
			$this->bsdb->where('subscription_payment.subscription_plan_id', (int)$data['subscription_plan_id']);
		}

		if (isset($data['subscription_order_id'])) {
			$this->bsdb->where('subscription_payment.subscription_order_id', (int)$data['subscription_order_id']);
		}

		if (isset($data['user_id'])) {
			$this->bsdb->where('subscription_payment.user_id', (int)$data['user_id']);
		}

		if (isset($data['currency_code'])) {
			$this->bsdb->where('subscription_payment.currency_code', $data['currency_code']);
		}

		if (isset($data['provider'])) {
			$this->bsdb->where('subscription_payment.provider', $data['provider']);
		}

		if (isset($data['amount'])) {
			$this->bsdb->where('subscription_payment.amount', (double)$data['amount']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('subscription_payment.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->like('subscription_payment.amount', $data['amount'], 'after');
			$this->bsdb->like('subscription_payment.provider', $data['search'], 'after');
		}

		if (!empty($data['date_start'])) {
			$this->bsdb->where('subscription_payment.date_added >= ', date('Y-m-d', strtotime($data['date_start'])));
		}

		if (!empty($data['date_end'])) {
			$this->bsdb->where('subscription_payment.date_added < ', date('Y-m-d', strtotime($data['date_end'])));
		}

		$this->bsdb->where('subscription_payment._deleted', 0);

		$this->bsdb->from('subscription_payment');
		$this->bsdb->join('currency', 'currency.code = subscription_payment.currency_code');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
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

		$this->bsdb->order_by($sort, $order);

		return $this->bsdb->get()->row()->total;
	}

	public function add($data) {
		if (self::get_all([
			'order_id'	=> (int)$data['order_id'],
		])['total'] !== 0) {
			return;
		}

		$this->bsdb->insert('subscription_payment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('subscription_payment_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bsdb->update('subscription_payment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);

		$this->session->set_flashdata('flash_message', _l('subscription_payment_updated_successfully'));
	}

	public function delete($subscription_payment_id = 0) {
		$this->bsdb->where('id', (int)$subscription_payment_id);
		$this->bsdb->update('subscription_payment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
