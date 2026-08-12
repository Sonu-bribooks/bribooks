<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SystemUser_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('users.*, CONCAT(users.first_name, " ", users.last_name) AS name');
		
		$this->db->where('users.id', (int)$id);
		$this->db->where('users._deleted', 0);
		$this->db->where_not_in('users.role_id', [1, 2, 3, 4, 9, 12, 15]);

		$row = $this->db->get('users')->row_array();

		return $row;
	}

	public function get_all($data = []) {
		$this->db->select('users.*, CONCAT(users.first_name, " ", users.last_name) AS name');

		if (isset($data['name'])) {
			$this->db->where('CONCAT(users.first_name, " ", users.last_name)', $data['name']);
		}

		if (isset($data['status'])) {
			$this->db->where('users.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where_not_in('users.role_id', [1, 2, 3, 4, 9, 12, 15]);
		$this->db->where('users._deleted', 0);

		$this->db->from('users');

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
			'users.first_name',
			'users.status',
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.id';
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
		$data['social_links'] = is_array($data['social_links']) ? json_encode($data['social_links']) : '';

		$this->db->insert('users', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$users_id = $this->db->insert_id();

		return $users_id;
	}

	public function edit($users_id = 0, $data = []) {
		$data['social_links'] = is_array($data['social_links']) ? json_encode($data['social_links']) : '';

		$this->db->where('id', (int)$users_id);
		$this->db->update('users', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('users', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('users', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}
}
