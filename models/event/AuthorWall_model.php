<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuthorWall_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($author_wall_id = 0) {
		$this->db->select('author_wall.*');

		$this->db->where('author_wall.id', (int)$author_wall_id);
		$this->db->where('author_wall._deleted', 0);

		return $this->db->get('author_wall')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('author_wall.*');

		if (isset($data['event_id'])) {
			$this->db->where('author_wall.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('author_wall.site_id', (int)$data['site_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('author_wall.book_id', (int)$data['book_id']);
		}

		if (isset($data['is_jury'])) {
			$this->db->where('author_wall.is_jury', (int)$data['is_jury']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('author_wall.book_name', $data['search'], 'after');
			$this->db->or_like('author_wall.book_id', $data['search'], 'after');
			$this->db->or_like('author_wall.author_name', $data['search'], 'after');
			$this->db->or_like('author_wall.user_id', $data['search'], 'after');
			$this->db->or_like('author_wall.site_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('author_wall._deleted', 0);

		$this->db->from('author_wall');

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
			'author_wall.id',
			'author_wall.book_rank',
			'author_wall.date_added',
			'author_wall.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'author_wall.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('author_wall', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$author_wall_id = $this->db->insert_id();

		return $author_wall_id;
	}

	public function edit($author_wall_id = 0, $data = []) {
		$this->db->where('id', (int)$author_wall_id);
		$this->db->update('author_wall', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($author_wall_id = 0) {
		$this->db->where('id', (int)$author_wall_id);
		$this->db->update('author_wall',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
