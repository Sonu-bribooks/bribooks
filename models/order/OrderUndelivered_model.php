<?php defined('BASEPATH') or exit('No direct script access allowed');

class OrderUndelivered_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('order_undelivered.*');
		$this->db->where('order_undelivered.id', (int)$id);
		$this->db->where('order_undelivered._deleted', 0);

		return $this->db->get('order_undelivered')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			order_undelivered.*,
			users.first_name,
			users.last_name,
			users.email
		');

		if (isset($data['order_id'])) {
			$this->db->where('order_undelivered.order_id', (int)$data['order_id']);
		}

		if (!empty($data['email'])) {
			$this->db->where('order_undelivered.email', $data['email']);
		}

		if (!empty($data['mobile'])) {
			$this->db->where('order_undelivered.mobile', $data['mobile']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('users.id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('order_undelivered.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('order_undelivered.id', $data['search'], 'after');
			$this->db->or_like('users.id', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('order_undelivered.email', $data['search'], 'after');
			$this->db->or_like('order_undelivered.mobile', $data['search'], 'after');
			$this->db->or_like('order_undelivered.order_undelivered_code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('order_undelivered._deleted', 0);

		$this->db->join('order', 'order.id = order_undelivered.order_id', 'left');
		$this->db->join('users', 'users.id = order.user_id', 'left');
		$this->db->from('order_undelivered');

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
			'order_undelivered.total',
			'order_undelivered.status',
			'order_undelivered.date_added',
			'order_undelivered.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_undelivered.date_modified';
		}

		if (isset($data['order_undelivered']) && ($data['order_undelivered'] == 'ASC')) {
			$order_undelivered = 'ASC';
		} else {
			$order_undelivered = 'DESC';
		}

		$this->db->order_by($sort, $order_undelivered);

		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}

	public function add($data) {
		$this->db->insert('order_undelivered', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('order_undelivered', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);

		return;
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('order_undelivered',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
