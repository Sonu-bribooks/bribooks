<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSCity_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($city_id = 0) {
		$this->bsdb->select('city.*,
			state.name AS state,
			state.country_id AS country_id
		');

		$this->bsdb->where('city.id', (int)$city_id);
		$this->bsdb->where('city._deleted', 0);

		$this->bsdb->join('state', 'state.id = city.state_id', 'left');

		return $this->bsdb->get('city')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('city.*,
			state.name AS state,
			country.name AS country
		');

		if (isset($data['name'])) {
			$this->bsdb->where('city.name', $data['name']);
		}

		if (isset($data['state_id'])) {
			$this->bsdb->where('city.state_id', (int)$data['state_id']);
		}

		if (isset($data['city_ids'])) {
			$this->bsdb->where_in('city.id', $data['city_ids']);
		}

		if (isset($data['country_id'])) {
			$this->bsdb->where('country.id', (int)$data['country_id']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('city.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('city.name', $data['search'], 'after');
			$this->bsdb->or_like('city.code', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('city._deleted', 0);

		$this->bsdb->join('state', 'state.id = city.state_id', 'left');
		$this->bsdb->join('country', 'country.id = state.country_id', 'left');
		$this->bsdb->from('city');

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
			'city.name',
			'city.status',
			'city.date_added',
			'city.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'city.date_added';
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
		$this->bsdb->insert('city', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$city_id = $this->bsdb->insert_id();

		return $city_id;
	}

	public function edit($city_id = 0, $data = []) {
		$this->bsdb->where('id', (int)$city_id);
		$this->bsdb->update('city', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($city_id = 0) {
		$this->bsdb->where('id', (int)$city_id);
		$this->bsdb->update('city',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('city', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
