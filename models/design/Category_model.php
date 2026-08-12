<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($category_id = 0) {
		$this->db->select('category.*');

		$this->db->where('category.id', (int)$category_id);
		$this->db->where('category._deleted', 0);

		return $this->db->get('category')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('category.*');

		if (isset($data['status'])) {
			$this->db->where('category.status', (int)$data['status']);
		}

		if (isset($data['parent_id'])) {
			$this->db->where('category.parent_id', (int)$data['parent_id']);
		}

		if (!empty($data['category_ids'])) {
			$this->db->where_in('category.id', $data['category_ids']);
		}

		if (isset($data['parent_id_ne'])) {
			$this->db->where('category.parent_id != ', (int)$data['parent_id_ne']);
		}

		if (isset($data['is_default'])) {
			$this->db->where('category.is_default', (int)$data['is_default']);
		}

		if (!empty($data['genre_id'])) {
			$this->db->where(
				sprintf("category.id IN (select genre_categories.category_id from genre_categories where genre_id=%d)", (int)$data['genre_id'])
			);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('category.name', $data['search'], 'after');
			$this->db->or_like('category.image', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('category._deleted', 0);
		$this->db->from('category');

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
			'category.name',
			'category.status',
			'category.sort_order',
			'category.date_added',
			'category.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'category.id';
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
		$this->db->insert('category', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$category_id = $this->db->insert_id();

		return $category_id;
	}

	public function edit($category_id = 0, $data = []) {
		$this->db->where('id', $category_id);
		$this->db->update('category', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($category_id = 0) {
		$this->db->where('id', $category_id);
		$this->db->update('category',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
