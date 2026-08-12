<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMState_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($state_id = 0) {
		$this->bmdb->select('state.*,
			country.name AS country
		');

		$this->bmdb->where('state.id', (int)$state_id);
		$this->bmdb->where('state._deleted', 0);

		$this->bmdb->join('country', 'country.id = state.country_id', 'left');

		return $this->bmdb->get('state')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('state.*,
			country.name AS country
		');

		if (isset($data['name'])) {
			$this->bmdb->where('state.name', $data['name']);
		}

		if (isset($data['country_id'])) {
			$this->bmdb->where('state.country_id', (int)$data['country_id']);
		}

		if (isset($data['country_name'])) {
			$this->bmdb->where('country.name', $data['country_name']);
		}

		if (isset($data['country_code'])) {
			$this->bmdb->where('country.code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('state.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('state.name', $data['search'], 'after');
			$this->bmdb->or_like('state.code', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('state._deleted', 0);

		$this->bmdb->join('country', 'country.id = state.country_id', 'left');
		$this->bmdb->from('state');

		$total = $this->bmdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bmdb->limit($data['limit'], $data['start']);
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

		$this->bmdb->order_by($sort, $order);

		return ['rows' => $this->bmdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->bmdb->insert('state', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$state_id = $this->bmdb->insert_id();

		return $state_id;
	}

	public function edit($state_id = 0, $data = []) {
		$this->bmdb->where('id', (int)$state_id);
		$this->bmdb->update('state', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($state_id = 0) {
		$this->bmdb->where('id', (int)$state_id);
		$this->bmdb->update('state',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gbmdb->where('id', (int)$id);
			$this->gbmdb->update('state', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
