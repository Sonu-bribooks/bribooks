<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($shipment_id = 0) {
		$this->db->select('shipment.*');

		$this->db->where('shipment.id', (int)$shipment_id);
		$this->db->where('shipment._deleted', 0);

		return $this->db->get('shipment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('shipment.*');

		if (!empty($data['shipment_id'])) {
			$this->db->where('shipment.id', (int)$data['shipment_id']);
		}

		if (!empty($data['shipment_ids'])) {
			$this->db->where_in('shipment.id', $data['shipment_ids']);
		}

		if (!empty($data['awb_number'])) {
			$this->db->where('shipment.awb_number', $data['awb_number']);
		}

		if (!empty($data['order_id'])) {
			$this->db->where('shipment.order_id', (int)$data['order_id']);
		}

		if (!empty($data['order_ids'])) {
			$this->db->where_in('shipment.order_id', $data['order_ids']);
		}

		if (!empty($data['user_id'])) {
			$this->db->where('shipment.user_id', (int)$data['user_id']);
		}

		if (!empty($data['shipped_by'])) {
			$this->db->where('shipment.shipped_by', (int)$data['shipped_by']);
		}

		if (!empty($data['courier_id'])) {
			$this->db->where('shipment.courier_id', (int)$data['courier_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('shipment.status', (int)$data['status']);
		}

		if (isset($data['order_status'])) {
			if (is_array($data['order_status'])) {
				$this->db->where_in('shipment.order_status', $data['order_status']);
			} else {
				$this->db->where('shipment.order_status', (int)$data['order_status']);
			}
		}

		if (isset($data['startdate']) && isset($data['enddate'])) {
			$this->db->where('shipment.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		$this->db->where('shipment._deleted', 0);

		$this->db->from('shipment');

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
			'shipment.id',
			'shipment.order_id',
			'shipment.courier_id',
			'shipment.awb_number',
			'shipment.date_added',
			'shipment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'shipment.date_added';
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
		$this->db->insert('shipment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified' => date('Y-m-d H:i:s'),
		]);

		$shipment_id = $this->db->insert_id();

		return $shipment_id;
	}

	public function edit($shipment_id = 0, $data = []) {
		$this->db->where('id', (int)$shipment_id);
		$this->db->update('shipment', $data + [
			'date_modified' => date('Y-m-d H:i:s'),
		]);
	}

	public function delete($shipment_id = 0) {
		$this->db->where('id', (int)$shipment_id);
		$this->db->update('shipment',  [
			'_deleted'	  => 1,
			'date_deleted'  => date('Y-m-d H:i:s'),
		]);
	}
}
