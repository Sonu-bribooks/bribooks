<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PincodeZone_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($pincode_zone_id = 0) {
		$this->db->select('pincode_zone.*');

		$this->db->where('pincode_zone.id', (int)$pincode_zone_id);
		$this->db->where('pincode_zone._deleted', 0);

		return $this->db->get('pincode_zone')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('pincode_zone.*');

		if (isset($data['pincode'])) {
			$this->db->where('pincode_zone.pincode', $data['pincode']);
		}

        if (isset($data['zone'])) {
			$this->db->where('pincode_zone.zonne', $data['zone']);
		}

        if (isset($data['id_ne'])) {
			$this->db->where('pincode_zone.id !=', $data['id_ne']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('pincode_zone.pincode', $data['search'], 'after');
			$this->db->or_like('pincode_zone.zone', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('pincode_zone._deleted', 0);

		$this->db->from('pincode_zone');

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
			'pincode_zone.id',
			'pincode_zone.pincode',
			'pincode_zone.zone',
			'pincode_zone.date_added',
			'pincode_zone.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'pincode_zone.id';
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
		$this->db->insert('pincode_zone', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$pincode_zone_id = $this->db->insert_id();

		return $pincode_zone_id;
	}

	public function edit($pincode_zone_id = 0, $data = []) {
		$this->db->where('id', (int)$pincode_zone_id);
		$this->db->update('pincode_zone', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($pincode_zone_id = 0) {
		$this->db->where('id', (int)$pincode_zone_id);
		$this->db->update('pincode_zone',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
