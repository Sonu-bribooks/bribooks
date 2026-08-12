<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Blog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($blog_id = 0) {
		$this->db->select('blog.*,
			category.name AS category,
			CONCAT(users.first_name, " ", users.last_name) AS author
		');

		$this->db->where('blog.id', (int)$blog_id);
		$this->db->where('blog._deleted', 0);

		$this->db->join('category', 'category.id = blog.category_id', 'left');
		$this->db->join('users', 'users.id = blog.user_id', 'left');

		return $this->db->get('blog')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('blog.*,
			category.name AS category,
			CONCAT(users.first_name, " ", users.last_name) AS author
		');

		if (isset($data['user_id'])) {
			$this->db->where('blog.user_id', (int)$data['user_id']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('blog.category_id', (int)$data['category_id']);
		}

		if (isset($data['sort_order'])) {
			$this->db->where('blog.sort_order', (int)$data['sort_order']);
		}

		if (isset($data['status'])) {
			$this->db->where('blog.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('blog.name', $data['search'], 'after');
			$this->db->or_like('blog.description', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('blog._deleted', 0);

		$this->db->join('category', 'category.id = blog.category_id', 'left');
		$this->db->join('users', 'users.id = blog.user_id', 'left');
		$this->db->from('blog');

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
			'blog.name',
			'blog.sort_order',
			'blog.status',
			'blog.date_added',
			'blog.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'blog.date_added';
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
		unset($data['files']);

		if (is_array($data['related'])) {
			$data['related'] = implode(',', $data['related']);
		}

		$this->db->insert('blog', $data + [
			'user_id'		=> (int)$this->session->userdata('user_id'),
			'slug'			=> get_blog_slug($data['name']),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$blog_id = $this->db->insert_id();

		return $blog_id;
	}

	public function edit($blog_id = 0, $data = []) {
		unset($data['files']);

		if (is_array($data['related'])) {
			$data['related'] = implode(',', $data['related']);
		}

		$this->db->where('id', (int)$blog_id);
		$this->db->update('blog', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($blog_id = 0) {
		$this->db->where('id', (int)$blog_id);
		$this->db->update('blog',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function updateViews($blog_id = 0) {
		$this->db->set('views', 'views+1', FALSE);
		$this->db->where('id', (int)$blog_id);
		$this->db->update('blog');
	}

	public function getBySlug($slug = '') {
		$this->db->select('blog.*, category.name AS category');

		$this->db->where('blog.slug', $slug);
		$this->db->where('blog.status != ', 0);
		$this->db->where('blog._deleted', 0);

		$this->db->join('category', 'category.id = blog.category_id', 'left');

		return $this->db->get('blog')->row_array();
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('blog', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
