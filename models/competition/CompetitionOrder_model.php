<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CompetitionOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($competition_order_id = 0) {
		$this->db->select('competition_order.*, competition.name AS competition');

		$this->db->where('competition_order.id', (int)$competition_order_id);
		$this->db->where('competition_order._deleted', 0);

		$this->db->join('competition', 'competition.id = competition_order.competition_id', 'left');
		$this->db->join('currency', 'currency.id = competition_order.currency_id', 'left');

		return $this->db->get('competition_order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('competition_order.*, competition.name AS competition, currency.symbol as currency_symbol, users.first_name, users.last_name, users.email, users.mobile');

		if (isset($data['site_id'])) {
			$this->db->where('competition_order.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('competition_order.user_id', (int)$data['user_id']);
		}

		if (isset($data['competition_id'])) {
			$this->db->where('competition_order.competition_id', (int)$data['competition_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('competition_order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('competition_order.provider', $data['provider']);
		}

		if (isset($data['amount'])) {
			$this->db->where('competition_order.amount', (double)$data['amount']);
		}

		if (isset($data['status'])) {
			$this->db->where('competition_order.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('competition_order.amount', $data['search'], 'after');
			$this->db->or_like('competition.name', $data['search'], 'after');
			$this->db->or_like('users.first_name', $data['search'], 'after');
			$this->db->or_like('users.last_name', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
		}

		$this->db->where('competition_order._deleted', 0);

		$this->db->join('competition', 'competition.id = competition_order.competition_id', 'left');
		$this->db->join('currency', 'currency.id = competition_order.currency_id', 'left');
		$this->db->join('users', 'users.id = competition_order.user_id', 'left');
		$this->db->from('competition_order');

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
			'competition_order.amount',
			'competition_order.status',
			'competition_order.date_added',
			'competition_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'competition_order.date_added';
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
		$this->db->insert('competition_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$competition_order_id = $this->db->insert_id();

		return $competition_order_id;
	}

	public function edit($competition_order_id = 0, $data = []) {
		$this->db->where('id', (int)$competition_order_id);
		$this->db->update('competition_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($competition_order_id = 0) {
		$this->db->where('id', (int)$competition_order_id);
		$this->db->update('competition_order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
