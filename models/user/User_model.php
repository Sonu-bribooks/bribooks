<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		if (empty($id)) return false;

		$this->db->select('*');

		$this->db->where('id', (int)$id);
		$this->db->where('_deleted', 0);

		return $this->db->get('users')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('users.*');

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['email'])) {
			$this->db->where('users.email', $data['email']);
		}

		if (isset($data['mobile'])) {
			$this->db->where('users.mobile', $data['mobile']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('users.id', (int)$data['user_id']);
		}

		if (isset($data['code'])) {
			$this->db->where('users.verification_code', $data['code']);
		}

		if (isset($data['role_id'])) {
			$this->db->where('users.role_id', (int)$data['role_id']);
		}

		if (isset($data['role_id_not'])) {
			$this->db->where('users.role_id !=', (int)$data['role_id_not']);
		}

		if (isset($data['signup_interval'])) {
			$this->db->where('users.date_added BETWEEN DATE_SUB(NOW(), INTERVAL '.$data['signup_interval'].' DAY) AND NOW()');
		}

		if (isset($data['status'])) {
			$this->db->where('users.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->db->where('users.verified', (int)$data['verified']);
		}

		if (isset($data['user_verified'])) {
			$this->db->group_start();
			$this->db->where('users.mobile_verified', (int)$data['user_verified']);
			$this->db->or_where('users.email_verified', (int)$data['user_verified']);
			$this->db->group_end();
		}

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
		} else {
			$this->db->limit(10, 0);
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

	public function edit($id = 0, $data = []) {
		$this->db->where('id', $id);
		$this->db->update('users', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', $id);
		$this->db->update('users', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function get_user_image_url($user_id) {
		if (file_exists('uploads/user_image/' . $user_id . '.jpg')) {
			return base_url() . 'uploads/user_image/' . $user_id . '.jpg';
		} else {
			return base_url() . 'uploads/user_image/placeholder.png';
		}
	}

	public function loginWithCode($data = []) {
		if ($row = $this->db->get_where('login_with_code', [
			'code'			=> $data['code'],
			'status'		=> 0,
			// 'expired >'		=> date('Y-m-d H:i:s'),
		])->row_array()) {
			$this->db->update('login_with_code', [
				'status'			=> 1,
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			return $row;
		}
	}

	public function addLoginCode($data = []) {
		$code = md5(uniqid(true));

		$this->db->insert('login_with_code', [
			'user_id'		=> (int)$data['user_id'],
			'code'			=> $code,
			'status'		=> 0,
			'expired'		=> date('Y-m-d H:i:s', strtotime(sprintf('+ %d minutes', LOGIN_CODE_VALIDITY))),
			'date_added'	=> date('Y-m-d H:i:s'),
		]);

		return $code;
	}
}
