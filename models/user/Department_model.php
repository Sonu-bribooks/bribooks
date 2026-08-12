<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Department_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->where('department.id', (int)$id);
		$this->db->where('department._deleted', 0);

		$row = $this->db->get('department')->row_array();

		return $row;
	}

	public function get_all($data = []) {
		$this->db->select('department.*,');

		if (isset($data['name'])) {
			$this->db->where('department.name', $data['name']);
		}

		if (isset($data['status'])) {
			$this->db->where('department.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('department.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('department._deleted', 0);

		$this->db->from('department');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'department.name',
			'department.status',
			'department.date_added',
			'department.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'department.id';
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
		$user_ids = $data['user_ids'] ?? [];
		unset($data['user_ids']);

		$this->db->insert('department', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$department_id = $this->db->insert_id();

		self::_updateUsers(compact('user_ids', 'department_id'));

		return $department_id;
	}

	public function edit($department_id = 0, $data = []) {
		$user_ids = $data['user_ids'] ?? [];
		unset($data['user_ids']);

		$this->db->where('id', (int)$department_id);
		$this->db->update('department', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		self::_updateUsers(compact('user_ids', 'department_id'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('department', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', _l('deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('department', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}

	public function getUsers($department_id = 0) {
		return $this->db
			->select('department_user.*, CONCAT(users.first_name, " ", users.last_name) AS name')
			->from('department_user')
			->join('users', 'users.id=department_user.user_id')
			->where('department_user.department_id', (int)$department_id)
			->where('department_user._deleted', 0)
			->where('users._deleted', 0)
			->get()
			->result_array();
	}

	private function _updateUsers($data = []) {
		$department_id 	= $data['department_id'] ?? 0;
		$user_ids 		= $data['user_ids'] ?? [];

		if (empty($department_id) || empty($user_ids)) return;

		$this->db->update('department_user', [
			'_deleted'		=> 0,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		], [
			'department_id'	=> (int)$department_id
		]);

		foreach ($user_ids as $key => $user_id) {
			$existing = $this->db->get_where('department_user', [
				'department_id'	=> (int)$department_id,
				'user_id'		=> (int)$user_id,
			])->row_array();

			if ($existing) {
				$this->db->update('department_user', [
					'department_id'	=> (int)$department_id,
					'user_id'		=> (int)$user_id,
					'_deleted'		=> 0,
					'date_deleted'	=> null,
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> (int)$existing['id'],
				]);
			} else {
				$this->db->insert('department_user', [
					'department_id'	=> (int)$department_id,
					'user_id'		=> (int)$user_id,
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);
			}
		}
	}
}
