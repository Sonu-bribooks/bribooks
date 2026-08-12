<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CrosswordStore_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('crossword_stores.*,  city.name as city');
		$this->db->where('crossword_stores.id', (int)$id);
		$this->db->join('city', 'city.id = crossword_stores.city_id', 'left');
		return $this->db->get('crossword_stores')->row_array();
	}

    public function get_all($data = []) {
		$this->db->select('crossword_stores.*, city.name as city');

		if (isset($data['store_id'])) {
			$this->db->where('crossword_stores.id', (int)$data['store_id']);
		}

        if (isset($data['state_id'])) {
			$this->db->where('crossword_stores.state_id', (int)$data['state_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('crossword_stores.city_id', $data['city_id']);
		}

        if (!empty($data['store_name'])) {
			$this->db->where('crossword_stores.store_name', $data['store_name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->or_like('city.name', $data['search'], 'both');
			$this->db->or_like('crossword_stores.store_name', $data['search'], 'both');
			$this->db->group_end();
		}

		if (isset($data['status'])) {
			$this->db->where('crossword_stores.status', (int)$data['status']);
		}

		$this->db->where('crossword_stores._deleted', 0);

		$this->db->join('city', 'city.id = crossword_stores.city_id', 'left');

		$this->db->from('crossword_stores');

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
			'crossword_stores.date_added',
			'crossword_stores.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'crossword_stores.id';
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
		$this->db->insert('crossword_stores', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$token_id = $this->db->insert_id();

		return $token_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('crossword_stores', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id) {
		$this->db->where('id', (int)$id);
		$this->db->update('crossword_stores', [
			'_deleted'	=> 1,
		]);
	}
}
