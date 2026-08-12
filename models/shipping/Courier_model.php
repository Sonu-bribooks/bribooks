<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Courier_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($courier_id = 0) {
		$this->db->select('courier.*');

		$this->db->where('courier.id', (int)$courier_id);
		$this->db->where('courier._deleted', 0);

		return $this->db->get('courier')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('courier.*');

		if (!empty($data['courier_id'])) {
			$this->db->where('courier.id', $data['courier_id']);
		}

		if (!empty($data['courier_ids'])) {
			$this->db->where_in('courier.id', $data['courier_ids']);
		}

		if (!empty($data['carrier_id'])) {
			$this->db->where('courier.carrier_id', $data['carrier_id']);
		}

		if (!empty($data['vendor_name'])) {
			$this->db->where('courier.vendor_name', $data['vendor_name']);
		}

		if (!empty($data['carrier_name'])) {
			$this->db->where('courier.carrier_name', $data['carrier_name']);
		}

		if (isset($data['status'])) {
			$this->db->where('courier.status', (int)$data['status']);
		}

		if (!empty($data['weight'])) {
			$this->db->where('courier.weight', (int)$data['weight']);
		}

		if (!empty($data['is_domestic'])) {
			$this->db->where('courier.is_domestic', $data['is_domestic']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('courier.name', $data['search'], 'both');
			$this->db->like('courier.code', $data['search'], 'both');
			$this->db->like('courier.display_name', $data['search'], 'both');
			$this->db->like('courier.courier_type', $data['search'], 'both');
			$this->db->like('courier.vendor_name', $data['search'], 'both');
			$this->db->like('courier.carrier_name', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('courier._deleted', 0);

		$this->db->from('courier');

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
			'courier.name',
			'courier.code',
			'courier.display_name',
			'courier.courier_order',
			'courier.date_added',
			'courier.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'courier.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('courier', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified' => date('Y-m-d H:i:s'),
		]);

		$courier_id = $this->db->insert_id();

		return $courier_id;
	}

	public function edit($courier_id = 0, $data = []) {
		$this->db->where('id', (int)$courier_id);
		$this->db->update('courier', $data + [
			'date_modified' => date('Y-m-d H:i:s'),
		]);
	}

	public function delete($courier_id = 0) {
		$this->db->where('id', (int)$courier_id);
		$this->db->update('courier',  [
			'_deleted'	  => 1,
			'date_deleted'  => date('Y-m-d H:i:s'),
		]);
	}
}
