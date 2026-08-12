<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PickupData_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($pickup_data_id = 0) {
		$this->db->select('pickup_data.*');

		$this->db->where('pickup_data.id', (int)$pickup_data_id);
		$this->db->where('pickup_data._deleted', 0);

		return $this->db->get('pickup_data')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('pickup_data.*');

		if (!empty($data['scheduled_date'])) {
			$this->db->where('DATE(pickup_data.scheduled_date)', date('Y-m-d', strtotime($data['scheduled_date'])));
		}
		$this->db->where('pickup_data._deleted', 0);


		$this->db->from('pickup_data');

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
			'pickup_data.id',
			'pickup_data.date_added',
			'pickup_data.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'pickup_data.date_added';
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
		$this->db->insert('pickup_data', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified' => date('Y-m-d H:i:s'),
		]);

		$pickup_data_id = $this->db->insert_id();

		return $pickup_data_id;
	}

	public function edit($pickup_data_id = 0, $data = []) {
		$this->db->where('id', (int)$pickup_data_id);
		$this->db->update('pickup_data', $data + [
			'date_modified' => date('Y-m-d H:i:s'),
		]);
	}
}
