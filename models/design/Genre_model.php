<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Genre_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($genre_id = 0) {
		$this->db->select('genre.*');

		$this->db->where('genre.id', (int)$genre_id);
		$this->db->where('genre._deleted', 0);

		return $this->db->get('genre')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('genre.*');

		if (isset($data['status'])) {
			$this->db->where('genre.status', (int)$data['status']);
		}

		if (isset($data['parent_id'])) {
			$this->db->where('genre.parent_id', (int)$data['parent_id']);
		}

		if (!empty($data['genre_ids'])) {
			$this->db->where_in('genre.id', $data['genre_ids']);
		}

		if (isset($data['parent_id_ne'])) {
			$this->db->where('genre.parent_id != ', (int)$data['parent_id_ne']);
		}

		if (isset($data['is_default'])) {
			$this->db->where('genre.is_default', (int)$data['is_default']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('genre.name', $data['search'], 'after');
			$this->db->or_like('genre.image', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('genre._deleted', 0);
		$this->db->from('genre');

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
			'genre.id',
			'genre.name',
			'genre.status',
			'genre.sort_order',
			'genre.date_added',
			'genre.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'genre.id';
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
		$this->db->insert('genre', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$genre_id = $this->db->insert_id();

		return $genre_id;
	}

	public function edit($genre_id = 0, $data = []) {
		$this->db->where('id', $genre_id);
		$this->db->update('genre', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($genre_id = 0) {
		$this->db->where('id', $genre_id);
		$this->db->update('genre',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getCategories($genre_id = 0) {
		return $this->db->get_where('genre_categories', [
			'genre_id'	=> (int)$genre_id,
		])->result_array();
	}

	public function addCategories($data = []) {
		$genre_id 		= $data['genre_id'] ?? [];
		$category_ids 	= $data['category_ids'] ?? [];

		if (empty($genre_id) || empty($category_ids)) return;

		$this->db->delete('genre_categories', [
			'genre_id'	=> (int)$genre_id,
		]);

		foreach ($category_ids as $category_id) {
			$this->db->insert('genre_categories', [
				'genre_id'		=> (int)$genre_id,
				'category_id'	=> (int)$category_id,
				'date_added'	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
