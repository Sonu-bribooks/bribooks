<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GroupRegion_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($group_region_id = 0) {
		$this->db->select('group_region.*,
			country.name AS country,
			GROUP_CONCAT(state.name ORDER BY state.name SEPARATOR ", ") as state_name
		');

		$this->db->where('group_region.id', (int)$group_region_id);
		$this->db->where('group_region._deleted', 0);

		$this->db->join('country', 'country.id = group_region.country_id', 'left');
		$this->db->join('state', "FIND_IN_SET(state.id, group_region.state)", 'left');

		return $this->db->get('group_region')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('group_region.*,
			country.name AS country,
			GROUP_CONCAT(state.name ORDER BY state.name SEPARATOR ", ") as state_name
		');

		if (isset($data['region_id'])) {
			$this->db->where('group_region.id', $data['region_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('group_region.name', $data['name']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('group_region.country_id', (int)$data['country_id']);
		}

		if (isset($data['country_name'])) {
			$this->db->where('country.name', $data['country_name']);
		}

		if (isset($data['state_id'])) {
			$this->db->where("FIND_IN_SET('" . $data['state_id'] . "', group_region.state) >", 0, FALSE);
		}

		if (isset($data['status'])) {
			$this->db->where('group_region.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('group_region.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('group_region._deleted', 0);

		$this->db->join('country', 'country.id = group_region.country_id', 'left');
		$this->db->join('state', "FIND_IN_SET(state.id, group_region.state) > 0", 'left');
		$this->db->from('group_region');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'group_region.name',
			'group_region.status',
			'group_region.country_id',
			'group_region.date_added',
			'group_region.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'group_region.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->group_by('group_region.id');
		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('group_region', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$group_region_id = $this->db->insert_id();

		return $group_region_id;
	}

	public function edit($group_region_id = 0, $data = []) {
		$this->db->where('id', (int)$group_region_id);
		$this->db->update('group_region', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($group_region_id = 0) {
		$this->db->where('id', (int)$group_region_id);
		$this->db->update('group_region',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('group_region', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
