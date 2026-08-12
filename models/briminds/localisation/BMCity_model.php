<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMCity_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($city_id = 0) {
		$this->bmdb->select('city.*,
			state.name AS state,
			state.country_id AS country_id
		');

		$this->bmdb->where('city.id', (int)$city_id);
		$this->bmdb->where('city._deleted', 0);

		$this->bmdb->join('state', 'state.id = city.state_id', 'left');

		return $this->bmdb->get('city')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('city.*,
			state.name AS state,
			country.name AS country
		');

		if (isset($data['name'])) {
			$this->bmdb->where('city.name', $data['name']);
		}

		if (isset($data['state_id'])) {
			$this->bmdb->where('city.state_id', (int)$data['state_id']);
		}

		if (isset($data['city_ids'])) {
			$this->bmdb->where_in('city.id', $data['city_ids']);
		}

		if (isset($data['country_id'])) {
			$this->bmdb->where('country.id', (int)$data['country_id']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('city.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('city.name', $data['search'], 'after');
			$this->bmdb->or_like('city.code', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('city._deleted', 0);

		$this->bmdb->join('state', 'state.id = city.state_id', 'left');
		$this->bmdb->join('country', 'country.id = state.country_id', 'left');
		$this->bmdb->from('city');

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

		$this->bmdb->order_by($sort, $order);

		return ['rows' => $this->bmdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->bmdb->insert('city', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$city_id = $this->bmdb->insert_id();

		return $city_id;
	}

	public function edit($city_id = 0, $data = []) {
		$this->bmdb->where('id', (int)$city_id);
		$this->bmdb->update('city', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($city_id = 0) {
		$this->bmdb->where('id', (int)$city_id);
		$this->bmdb->update('city',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gbmdb->where('id', (int)$id);
			$this->gbmdb->update('city', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
