<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ReviewEditLogs_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($page_id = 0) {
		$this->db->select('page.*,
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

		$this->db->where('page.id', (int)$page_id);
		$this->db->where('page._deleted', 0);

		$this->db->join('theme', 'theme.id = page.theme_id', 'left');

		return $this->db->get('page')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('page.*,
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

		if (isset($data['book_id'])) {
			$this->db->where('page.book_id', (int)$data['book_id']);
		}

		if (isset($data['theme_id'])) {
			$this->db->where('page.theme_id', (int)$data['theme_id']);
		}

		if (isset($data['sort_order'])) {
			$this->db->where('page.sort_order', (int)$data['sort_order']);
		}

		if (isset($data['status'])) {
			$this->db->where('page.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('page.texts', $data['search'], 'after');
			$this->db->or_like('theme.name', $data['search'], 'after');
		}

		$this->db->where('page._deleted', 0);

		$this->db->join('theme', 'theme.id = page.theme_id', 'left');
		$this->db->from('page');

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
			'page.name',
			'page.sort_order',
			'page.status',
			'page.date_added',
			'page.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'page.date_added';
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
		$this->db->insert('review_edit_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$page_id = $this->db->insert_id();

		return $page_id;
	}

	public function edit($page_id = 0, $data = []) {
		$this->db->where('id', (int)$page_id);
		$this->db->update('page', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($page_id = 0) {
		$this->db->where('id', (int)$page_id);
		$this->db->update('page',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function updateSortOrderBeforePageAdd($book_id = 0, $sort_order = 0) {
		$this->db->set('sort_order', 'sort_order+1', FALSE);
		$this->db->where('sort_order > ', (int)$sort_order);
		$this->db->where('book_id', (int)$book_id);
		$this->db->update('page');
	}

	public function checkSpamWords($words = '') {
		return $this->db->select('word')
			->where('_deleted', 0)
			->where('"' . $this->db->escape($words) . '" LIKE CONCAT("% ", word, " %")')
			->get('spam_words')->result_array();
	}
}
