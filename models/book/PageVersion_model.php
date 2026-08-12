<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PageVersion_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($page_version_id = 0) {
		$this->db->select('page_version.*,
			theme.id AS theme_id,
			theme.category_id AS theme_category_id,
			theme.name AS theme_name,
			theme.image,
			theme.text_boxes,
			theme.font_family,
			theme.font_size,
			theme.font_color,
			theme.font_weight
		');

		$this->db->where('page_version.id', (int)$page_version_id);
		$this->db->where('page_version._deleted', 0);

		$this->db->join('theme', 'theme.id = page_version.theme_id', 'left');

		return $this->db->get('page_version')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('page_version.*,
			theme.id AS theme_id,
			theme.category_id AS theme_category_id,
			theme.name AS theme_name,
			theme.image,
			theme.text_boxes,
			theme.font_family,
			theme.font_size,
			theme.font_color,
			theme.font_weight
		');

		if (isset($data['version'])) {
			$this->db->where('page_version.version', (int)$data['version']);
		}

		if (isset($data['page_id'])) {
			$this->db->where('page_version.page_id', (int)$data['page_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('page_version.book_id', (int)$data['book_id']);
		}

		if (isset($data['theme_id'])) {
			$this->db->where('page_version.theme_id', (int)$data['theme_id']);
		}

		if (!empty($data['is_custom_id'])) {
			$this->db->where('custom_theme_id !=', 0);
		}

		if (isset($data['sort_order'])) {
			$this->db->where('page_version.sort_order', (int)$data['sort_order']);
		}

		if (isset($data['status'])) {
			$this->db->where('page_version.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('page_version.texts', $data['search'], 'after');
			$this->db->or_like('theme.name', $data['search'], 'after');
		}

		$this->db->where('page_version._deleted', 0);

		$this->db->join('theme', 'theme.id = page_version.theme_id', 'left');
		$this->db->from('page_version');

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
			'page_version.name',
			'page_version.version',
			'page_version.sort_order',
			'page_version.status',
			'page_version.date_added',
			'page_version.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'page_version.date_added';
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
		$this->db->insert('page_version', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$page_version_id = $this->db->insert_id();

		return $page_version_id;
	}

	public function edit($page_version_id = 0, $data = []) {
		$this->db->where('id', (int)$page_version_id);
		$this->db->update('page_version', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($page_version_id = 0) {
		$this->db->where('id', (int)$page_version_id);
		$this->db->update('page_version',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
