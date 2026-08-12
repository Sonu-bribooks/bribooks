<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PickupLocation_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('pickup_locations.*');

		$this->db->where('pickup_locations.id', (int)$id);
		$this->db->where('pickup_locations._deleted', 0);

		return $this->db->get('pickup_locations')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('pickup_locations.*');

		if (isset($data['status'])) {
			$this->db->where('pickup_locations.status', (int)$data['status']);
		}

		$this->db->where('pickup_locations._deleted', 0);

		$this->db->from('pickup_locations');

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
			'pickup_locations.name',
			'pickup_locations.status',
			'pickup_locations.date_added',
			'pickup_locations.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'pickup_locations.date_added';
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
		$this->db->insert('pickup_locations', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('pickup_locations', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('pickup_locations',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
