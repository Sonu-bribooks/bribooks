<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UnionTerritory_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('union_territory.*,
			city.name AS city,
			state.name AS state,
			state.country_id AS country_id
		');

		$this->db->where('union_territory.id', (int)$id);
		$this->db->where('union_territory._deleted', 0);

		$this->db->join('city', 'city.id = union_territory.city_id', 'left');
		$this->db->join('state', 'state.id = union_territory.state_id', 'left');

		return $this->db->get('union_territory')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('union_territory.*,
			city.name AS state,
			state.name AS state,
			country.name AS country
		');

		if (isset($data['name'])) {
			$this->db->where('union_territory.name', $data['name']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('union_territory.state_id', (int)$data['state_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('union_territory.city_id', $data['city_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('country.id', (int)$data['country_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('union_territory.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('union_territory.name', $data['search'], 'after');
			$this->db->or_like('union_territory.code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('union_territory._deleted', 0);

		$this->db->join('city', 'city.id = union_territory.city_id', 'left');
		$this->db->join('state', 'state.id = union_territory.state_id', 'left');
		$this->db->join('country', 'country.id = state.country_id', 'left');
		$this->db->from('union_territory');

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
			'union_territory.name',
			'union_territory.status',
			'union_territory.date_added',
			'union_territory.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'union_territory.date_added';
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
		$this->db->insert('union_territory', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('union_territory', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('union_territory',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('union_territory', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
