<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserCover_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_cover_id = 0) {
		$this->db->select('user_cover.*');

		$this->db->where('user_cover.id', (int)$user_cover_id);
		$this->db->where('user_cover._deleted', 0);

		return $this->db->get('user_cover')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_cover.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_cover.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_cover.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_cover.image', $data['search'], 'after');
			$this->db->or_like('user_cover.design', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('user_cover._deleted', 0);

		$this->db->from('user_cover');

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
			'user_cover.id',
			'user_cover.status',
			'user_cover.date_added',
			'user_cover.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_cover.id';
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
		$this->db->insert('user_cover', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_cover_id = $this->db->insert_id();

		return $user_cover_id;
	}

	public function edit($user_cover_id = 0, $data = []) {
		$this->db->where('id', (int)$user_cover_id);
		$this->db->update('user_cover', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_cover_id = 0) {
		$this->db->where('id', (int)$user_cover_id);
		$this->db->update('user_cover',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
