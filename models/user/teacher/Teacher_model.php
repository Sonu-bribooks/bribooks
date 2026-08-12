<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Teacher_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($teacher_id = 0) {
		$this->db->select('users.*');

		$this->db->where('users.id', (int)$teacher_id);

		$this->db->where('users.role_id', 3);

		return $this->db->get('users')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('users.id');

		if (isset($data['site_id'])) {
			$this->db->where('site_id', (int)$data['site_id']);
		}

		if (!empty($data['teacher_id_ne'])) {
			$this->db->where('id !=' , (int)$data['teacher_id_ne']);
		}

		if (isset($data['name'])) {
			$this->db->where('first_name', $data['name']);
		}

		if (isset($data['grade'])) {
			$this->db->where('grade', $data['grade']);
		}

		if (!empty($data['section'])) {
			$this->db->where('section', $data['section']);
		}

		if (isset($data['mobile'])) {
			$this->db->where('mobile', $data['mobile']);
		}

		if (isset($data['email'])) {
			$this->db->where('email', $data['email']);
		}

		if (isset($data['email_verified'])) {
			$this->db->where('email_verified', (int)$data['email_verified']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('mobile_verified', (int)$data['mobile_verified']);
		}

		if (isset($data['verification_code'])) {
			$this->db->where('verification_code', $data['verification_code']);
		}

		$this->db->where('users.role_id', 3);
		$this->db->where('users._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->group_end();
		}

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
			'users.id',
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

		$rows = array_map(function($item) {
			return self::get($item['id']);
		}, $this->db->get()->result_array());

		return ['rows' => $rows, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('users', $data + [
			'role_id'		=> 3,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$teacher_id = $this->db->insert_id();

		$this->updateTeacher($teacher_id, $data);

		return $teacher_id;
	}

	public function edit($teacher_id = 0, $data = []) {
		$this->db->where('id', (int)$teacher_id);
		$this->db->update('users', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($teacher_id = 0) {
		$this->db->where('id', (int)$teacher_id);
		$this->db->update('users',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($teacher_id) {
		if ($row = self::get($teacher_id)->row_array()) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $teacher_id);
			$this->db->where('role_id', 3);
			$this->db->update('users', [
				'status'	=> (int)$status
			]);
		}
	}

	public function get_image_url($teacher_id = 0) {
	}

	public function my_courses() {
	}

	public function updateTeacher($teacher_id = 0, $data = []) {
	}

	public function getLmsLink($data = []) {}
}
