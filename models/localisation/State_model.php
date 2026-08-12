<?php defined('BASEPATH') OR exit('No direct script access allowed');

class State_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($state_id = 0) {
		$this->db->select('state.*,
			country.name AS country
		');

		$this->db->where('state.id', (int)$state_id);
		$this->db->where('state._deleted', 0);

		$this->db->join('country', 'country.id = state.country_id', 'left');

		return $this->db->get('state')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('state.*,
			country.name AS country
		');

		if (isset($data['name'])) {
			$this->db->where('state.name', $data['name']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('state.country_id', (int)$data['country_id']);
		}

		if (isset($data['country_name'])) {
			$this->db->where('country.name', $data['country_name']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('country.code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->db->where('state.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('state.name', $data['search'], 'after');
			$this->db->or_like('state.code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('state._deleted', 0);

		$this->db->join('country', 'country.id = state.country_id', 'left');
		$this->db->from('state');

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
			'state.name',
			'state.status',
			'state.date_added',
			'state.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'state.date_added';
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
		$this->db->insert('state', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$state_id = $this->db->insert_id();

		return $state_id;
	}

	public function edit($state_id = 0, $data = []) {
		$this->db->where('id', (int)$state_id);
		$this->db->update('state', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($state_id = 0) {
		$this->db->where('id', (int)$state_id);
		$this->db->update('state',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('state', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
