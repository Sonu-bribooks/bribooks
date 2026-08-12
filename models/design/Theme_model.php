<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Theme_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($theme_id = 0) {
		$this->db->select('theme.*, category.name AS category, category.custom_theme');

		$this->db->where('theme.id', (int)$theme_id);
		$this->db->where('theme._deleted', 0);

		$this->db->join('category', 'category.id = theme.category_id', 'left');

		return $this->db->get('theme')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('theme.*, category.name AS category, category.custom_theme');

		if (isset($data['category_id'])) {
			$this->db->where('theme.category_id', (int)$data['category_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('theme.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('theme.name', $data['search'], 'after');
			$this->db->or_like('category.name', $data['search'], 'after');
			$this->db->or_like('theme.image', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('theme._deleted', 0);

		$this->db->join('category', 'category.id = theme.category_id', 'left');
		$this->db->from('theme');

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
			'theme.id',
			'theme.name',
			'theme.status',
			'theme.sort_order',
			'theme.date_added',
			'theme.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'theme.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		if (isset($data['sort']) && in_array($data['sort'], ['theme.sort_order'])) {
			$this->db->order_by('theme.id', 'DESC');
		}

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('theme', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$theme_id = $this->db->insert_id();

		return $theme_id;
	}

	public function edit($theme_id = 0, $data = []) {
		$this->db->where('id', (int)$theme_id);
		$this->db->update('theme', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($theme_id = 0) {
		$this->db->where('id', (int)$theme_id);
		$this->db->update('theme',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
