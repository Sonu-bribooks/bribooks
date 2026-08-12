<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderHistory_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_history_id = 0) {
		$this->db->select('order_history.*');

		$this->db->where('order_history.id', (int)$order_history_id);
		$this->db->where('order_history._deleted', 0);

		return $this->db->get('order_history')->row_array();
	}

	public function getByOrder($order_code = ''){
		$this->db->select('order_history.*');

		$this->db->where('order_history.order_id', $order_code);
		return $this->db->get('order_history')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('order_history.*');

		if (isset($data['order_id'])) {
			$this->db->where('order_history.order_id', (int)$data['order_id']);
		}

		if (isset($data['description'])) {
			$this->db->where('order_history.description', $data['description']);
		}

		if (!empty($data['search'])) {
			$this->db->like('order_history.description', $data['search'], 'after');
		}

		if (isset($data['status'])) {
			$this->db->where('order_history.status', (int)$data['status']);
		}

		if (isset($data['in_status'])) {
			$data['in_status'] = is_array($data['in_status']) ? $data['in_status'] : [(int)$data['in_status']];
			$this->db->where_in('order_history.status', $data['in_status']);
		}

		$this->db->where('order_history._deleted', 0);

		$this->db->from('order_history');

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
			'order_history.id',
			'order_history.status',
			'order_history.date_added',
			'order_history.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_history.id';
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
		$this->db->insert('order_history', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$order_history_id = $this->db->insert_id();

		return $order_history_id;
	}

	public function edit($order_history_id = 0, $data = []) {
		$this->db->where('id', $order_history_id);
		$this->db->update('order_history', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($order_history_id = 0) {
		$this->db->where('id', $order_history_id);
		$this->db->update('order_history',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
