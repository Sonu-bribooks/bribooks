<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EscalatedDropshipperOrders_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($escalated_order_id = 0) {
		$this->db->select('escalated_dropshipper_orders.*');

		$this->db->where('escalated_dropshipper_orders.id', (int)$escalated_order_id);
		$this->db->where('escalated_dropshipper_orders._deleted', 0);

		return $this->db->get('escalated_dropshipper_orders')->row_array();
	}

	public function getByOrder($order_id = ''){
		$this->db->select('escalated_dropshipper_orders.*');

		$this->db->where('escalated_dropshipper_orders.order_id', $order_id);
		$this->db->where('escalated_dropshipper_orders._deleted', 0);
		return $this->db->get('escalated_dropshipper_orders')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('escalated_dropshipper_orders.*');

		if (isset($data['order_id'])) {
			$this->db->where('escalated_dropshipper_orders.order_id', (int)$data['order_id']);
		}

		if (isset($data['description'])) {
			$this->db->where('escalated_dropshipper_orders.description', $data['description']);
		}

		if (!empty($data['search'])) {
			$this->db->like('escalated_dropshipper_orders.description', $data['search'], 'after');
		}

		$this->db->where('escalated_dropshipper_orders._deleted', 0);

		$this->db->from('escalated_dropshipper_orders');

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
			'escalated_dropshipper_orders.status',
			'escalated_dropshipper_orders.date_added',
			'escalated_dropshipper_orders.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'escalated_dropshipper_orders.date_added';
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
		$this->db->insert('escalated_dropshipper_orders', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$escalated_order_id = $this->db->insert_id();

		return $escalated_order_id;
	}

	public function edit($escalated_order_id = 0, $data = []) {
		$this->db->where('id', $escalated_order_id);
		$this->db->update('escalated_dropshipper_orders', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($escalated_order_id = 0) {
		$this->db->where('id', $escalated_order_id);
		$this->db->update('escalated_dropshipper_orders',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
