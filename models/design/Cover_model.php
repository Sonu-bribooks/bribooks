<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cover_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($cover_id = 0) {
		$this->db->select('cover.*, category.name AS category');

		$this->db->where('cover.id', (int)$cover_id);
		$this->db->where('cover._deleted', 0);

		$this->db->join('category', 'category.id = cover.category_id', 'left');

		return $this->db->get('cover')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('cover.*, category.name AS category');

		if (isset($data['category_id'])) {
			$this->db->where('cover.category_id', (int)$data['category_id']);
		}

		if (isset($data['category_id_ne'])) {
			$this->db->where('cover.category_id !=', (int)$data['category_id_ne']);
		}

		if (isset($data['status'])) {
			$this->db->where('cover.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('cover.tags', $data['search'], 'both');
			$this->db->or_like('category.name', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('cover._deleted', 0);

		$this->db->join('category', 'category.id = cover.category_id', 'left');
		$this->db->from('cover');

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
			'cover.name',
			'cover.status',
			'cover.date_added',
			'cover.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'cover.id';
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
		$this->db->insert('cover', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$cover_id = $this->db->insert_id();

		return $cover_id;
	}

	public function edit($cover_id = 0, $data = []) {
		$this->db->where('id', (int)$cover_id);
		$this->db->update('cover', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($cover_id = 0) {
		$this->db->where('id', (int)$cover_id);
		$this->db->update('cover',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
