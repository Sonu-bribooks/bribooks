<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookVersion_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_version_id = 0) {
		$this->db->select('book_version.*, category.name AS category');

		$this->db->where('book_version.id', (int)$book_version_id);
		$this->db->where('book_version._deleted', 0);

		$this->db->join('category', 'category.id = book_version.category_id', 'left');

		return $this->db->get('book_version')->row_array();
	}

	public function getByVersion($book_id = 0, $version = 1) {
		$this->db->select('book_version.*, category.name AS category, genre.name as genre');

		$this->db->where('book_version.book_id', (int)$book_id);
		$this->db->where('book_version.version', (int)$version);
		$this->db->where('book_version._deleted', 0);

		$this->db->join('category', 'category.id = book_version.category_id', 'left');
		$this->db->join('genre', 'genre.id = book_version.genre_id', 'left');

		return $this->db->get('book_version')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_version.*, category.name AS category, users.first_name, users.last_name,users.site_id');

		if (isset($data['version'])) {
			$this->db->where('book_version.version', (int)$data['version']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_version.book_id', (int)$data['book_id']);
		}

		if (isset($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('book_version.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		if (isset($data['user_id'])) {
			$this->db->where('book_version.user_id', (int)$data['user_id']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('book_version.category_id', (int)$data['category_id']);
		}

		if (isset($data['cover_id'])) {
			$this->db->where('book_version.cover_id', (int)$data['cover_id']);
		}

		if (isset($data['reviewer_id'])) {
			$this->db->where('book_version.reviewer_id', (int)$data['reviewer_id']);
		}

		if (isset($data['genre_id'])) {
			$this->db->where('book_version.genre_id', (int)$data['genre_id']);
		}

		if (isset($data['slug'])) {
			$this->db->where('book_version.slug', $data['slug']);
		}

		if (isset($data['isbn'])) {
			$this->db->where('book_version.isbn', $data['isbn']);
		}

		if (isset($data['featured'])) {
			$this->db->where('book_version.featured', (int)$data['featured']);
		}

		if (isset($data['status'])) {
			$this->db->where('book_version.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('book_version.status != ', (int)$data['ne_status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_version.name', $data['search']);
			$this->db->or_like('book_version.author_name', $data['search']);
			$this->db->or_like('book_version.isbn', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_version._deleted', 0);

		$this->db->join('category', 'category.id = book_version.category_id', 'left');
		$this->db->join('users', 'users.id = book_version.user_id', 'left');
		$this->db->from('book_version');

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
			'book_version.name',
			'book_version.author_name',
			'book_version.featured',
			'book_version.status',
			'book_version.site_id',
			'book_version.date_published',
			'book_version.date_approved',
			'book_version.date_added',
			'book_version.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_version.date_added';
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
		$this->db->insert('book_version', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$data['site_id'] ?? (int)$this->config->item('site_id'),
		]);

		$book_version_id = $this->db->insert_id();

		return $book_version_id;
	}

	public function edit($book_version_id = 0, $data = []) {
		$this->db->where('id', (int)$book_version_id);
		$this->db->update('book_version', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByBookId($book_id = 0, $version = 1, $data = []) {
		$this->db->where('book_id', (int)$book_id);

		if (!empty($version)) {
			$this->db->where('version', (int)$version);
		}

		$this->db->update('book_version', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($book_version_id = 0) {
		$this->db->where('id', (int)$book_version_id);
		$this->db->update('book_version',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
