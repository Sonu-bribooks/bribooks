<?php defined('BASEPATH') or exit('No direct script access allowed');

class UserDetails_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('user_details')->row_array();
	}

	public function getByUid($user_id = false) {
		if (!$user_id)
			return false;

		$this->db->select('*');
		$this->db->where('user_id', (int)$user_id);
		return $this->db->get('user_details')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('user_details', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($user_id = 0, $data = []) {
		$this->db->where('user_id', (int)$user_id);
		$this->db->update('user_details', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_id = 0) {
		$this->db->where('user_id', (int)$user_id);
		$this->db->update('user_details',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function get_user_details() {
		$this->db->select('*');
		$this->db->from('user_details');
		return $this->db->get()->result_array();
	}

	public function getNyafUserImage($data = []) {
		$this->db->select('*, user_details.date_modified AS photo_date_added');

		if (!empty($data['search'])) {
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.source', $data['search'], 'after');
		}

		$this->db->join('users', 'users.id = user_details.user_id', 'left');
		$this->db->from('user_details');

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
			'users.amount',
			'users.status',
			'users.first_name',
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
