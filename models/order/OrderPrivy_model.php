<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderPrivy_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');

		$this->db->where('order_privy.id', (int)$id);

		return $this->db->get('order_privy')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('order_privy.*, order.order_code, order.user_id, users.first_name, users.last_name, users.email, users.mobile, order.currency_symbol, order.total, order.weight, concat(agent.first_name, " ", agent.last_name) as agent_name');

		if (isset($data['order_id'])) {
			$this->db->where('order_privy.order_id', (int)$data['order_id']);
		}

		if (isset($data['agent_id'])) {
			$this->db->where('order_privy.agent_id', (int)$data['agent_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('order_privy.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->or_like('order_privy.order_id', $data['search'], 'both');
			$this->db->or_like('users.email', $data['search'], 'both');
			$this->db->or_like('users.mobile', $data['search'], 'both');
			$this->db->or_like('order.order_code', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->join('order', 'order.id = order_privy.order_id', 'left');
		$this->db->join('users', 'users.id = order.user_id', 'left');
		$this->db->join('users as agent', 'agent.id = order_privy.agent_id', 'left');

		$this->db->from('order_privy');

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
			'order_privy.date_added',
			'order_privy.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_privy.date_added';
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
		$this->db->insert('order_privy', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$rejected_book_id = $this->db->insert_id();

		return $rejected_book_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('order_privy', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
