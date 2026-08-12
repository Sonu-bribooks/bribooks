<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderPackingLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_packing_log_id = 0) {
		$this->db->select('order_packing_log.*');

		$this->db->where('order_packing_log.id', (int)$order_packing_log_id);
		$this->db->where('order_packing_log._deleted', 0);

		return $this->db->get('order_packing_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('order_packing_log.*');

		if (isset($data['user_id'])) {
			$this->db->where('order_packing_log.user_id', (int)$data['user_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('order_packing_log.order_id', (int)$data['order_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('order_packing_log.status', (int)$data['status']);
		}

		if (isset($data['type'])) {
			$this->db->where('order_packing_log.type', (int)$data['type']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(order_packing_log.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (isset($data['month'])) {
			$this->db->where('MONTH(order_packing_log.date_added)', date('m', strtotime($data['month'])));
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('order_packing_log.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('order_packing_log.order_id', $data['search'], 'after');
			$this->db->or_like('order_packing_log.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('order_packing_log._deleted', 0);

		$this->db->from('order_packing_log');

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
			'order_packing_log.id',
			'order_packing_log.date_added',
			'order_packing_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_packing_log.id';
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
		$this->db->insert('order_packing_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$order_packing_log_id = $this->db->insert_id();

		return $order_packing_log_id;
	}

	public function edit($order_packing_log_id = 0, $data = []) {
		$this->db->where('id', (int)$order_packing_log_id);
		$this->db->update('order_packing_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($order_packing_log_id = 0) {
		$this->db->where('id', (int)$order_packing_log_id);
		$this->db->update('order_packing_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
