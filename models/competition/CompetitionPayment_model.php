<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CompetitionPayment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($competition_payment_id = 0) {
		$this->db->select('competition_payment.*, competition.name AS competition');

		$this->db->where('competition_payment.id', (int)$competition_payment_id);
		$this->db->where('competition_payment._deleted', 0);

		$this->db->join('competition', 'competition.id = competition_payment.competition_id', 'left');
		$this->db->join('currency', 'currency.id = competition_payment.currency_id', 'left');

		return $this->db->get('competition_payment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('competition_payment.*, competition.name AS competition, currency.symbol as currency_symbol, users.first_name, users.last_name, users.email, users.mobile');

		if (isset($data['site_id'])) {
			$this->db->where('competition_payment.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('competition_payment.user_id', (int)$data['user_id']);
		}

		if (isset($data['competition_id'])) {
			$this->db->where('competition_payment.competition_id', (int)$data['competition_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('competition_payment.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('competition_payment.provider', $data['provider']);
		}

		if (isset($data['competition_order_id'])) {
			$this->db->where('competition_payment.competition_order_id', (int)$data['competition_order_id']);
		}

		if (isset($data['amount'])) {
			$this->db->where('competition_payment.amount', (double)$data['amount']);
		}

		if (isset($data['status'])) {
			$this->db->where('competition_payment.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('competition_payment.amount', $data['search'], 'after');
			$this->db->or_like('competition.name', $data['search'], 'after');
			$this->db->or_like('users.first_name', $data['search'], 'after');
			$this->db->or_like('users.last_name', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
		}

		$this->db->where('competition_payment._deleted', 0);

		$this->db->join('competition', 'competition.id = competition_payment.competition_id', 'left');
		$this->db->join('currency', 'currency.id = competition_payment.currency_id', 'left');
		$this->db->join('users', 'users.id = competition_payment.user_id', 'left');
		$this->db->from('competition_payment');

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
			'competition_payment.amount',
			'competition_payment.status',
			'competition_payment.date_added',
			'competition_payment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'competition_payment.date_added';
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
		$this->db->insert('competition_payment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$competition_payment_id = $this->db->insert_id();

		return $competition_payment_id;
	}

	public function edit($competition_payment_id = 0, $data = []) {
		$this->db->where('id', (int)$competition_payment_id);
		$this->db->update('competition_payment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($competition_payment_id = 0) {
		$this->db->where('id', (int)$competition_payment_id);
		$this->db->update('competition_payment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getList($data = []) {

		$this->db->select('
			competition_payment.*,
			users.first_name,
			users.last_name,
			users.email,
			users.mobile,
			competition.name as competition_name,
			competition.price as competition_price
		');

		if (isset($data['user_id'])) {
			$this->db->where('competition_user.user_id', (int)$data['user_id']);
		}

		if (isset($data['competition_id'])) {
			$this->db->where('competition_user.competition_id', (int)$data['competition_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('competition_user.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('competition_user.start_date', $data['search'], 'after');
			$this->db->like('competition_user.end_date', $data['search'], 'after');
		}

		$this->db->join('users', 'users.id = competition_payment.user_id', 'left');
		$this->db->join('competition_user', 'competition_user.id = competition_payment.competition_order_id', 'left');
		$this->db->join('competition', 'competition.id = competition_payment.competition_id', 'left');
		$this->db->join('currency', 'currency.id = competition_payment.currency_id', 'left');

		$this->db->from('competition_payment');

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
			'competition_user.status',
			'competition_user.date_added',
			'competition_user.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'competition_user.date_added';
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
