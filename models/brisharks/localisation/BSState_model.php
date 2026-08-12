<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSState_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($state_id = 0) {
		$this->bsdb->select('state.*,
			country.name AS country
		');

		$this->bsdb->where('state.id', (int)$state_id);
		$this->bsdb->where('state._deleted', 0);

		$this->bsdb->join('country', 'country.id = state.country_id', 'left');

		return $this->bsdb->get('state')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('state.*,
			country.name AS country
		');

		if (isset($data['name'])) {
			$this->bsdb->where('state.name', $data['name']);
		}

		if (isset($data['country_id'])) {
			$this->bsdb->where('state.country_id', (int)$data['country_id']);
		}

		if (isset($data['country_name'])) {
			$this->bsdb->where('country.name', $data['country_name']);
		}

		if (isset($data['country_code'])) {
			$this->bsdb->where('country.code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('state.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('state.name', $data['search'], 'after');
			$this->bsdb->or_like('state.code', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('state._deleted', 0);

		$this->bsdb->join('country', 'country.id = state.country_id', 'left');
		$this->bsdb->from('state');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
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

		$this->bsdb->order_by($sort, $order);

		return ['rows' => $this->bsdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->bsdb->insert('state', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$state_id = $this->bsdb->insert_id();

		return $state_id;
	}

	public function edit($state_id = 0, $data = []) {
		$this->bsdb->where('id', (int)$state_id);
		$this->bsdb->update('state', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($state_id = 0) {
		$this->bsdb->where('id', (int)$state_id);
		$this->bsdb->update('state',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('state', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
