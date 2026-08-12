<?php defined('BASEPATH') or exit('No direct script access allowed');

class BMUser_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($id = 0) {
		if (empty($id)) return false;

		$this->bmdb->select('*');

		$this->bmdb->where('id', (int)$id);
		$this->bmdb->where('_deleted', 0);

		return $this->bmdb->get('user')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('user.*');

		if (isset($data['site_id'])) {
			$this->bmdb->where('user.site_id', (int)$data['site_id']);
		}

		if (isset($data['email'])) {
			$this->bmdb->where('user.email', $data['email']);
		}

		if (isset($data['mobile'])) {
			$this->bmdb->where('user.mobile', $data['mobile']);
		}

		if (isset($data['user_id'])) {
			$this->bmdb->where('user.id', (int)$data['user_id']);
		}

		if (isset($data['role_id'])) {
			$this->bmdb->where('user.role_id', (int)$data['role_id']);
		}

		if (isset($data['role_id_not'])) {
			$this->bmdb->where('user.role_id !=', (int)$data['role_id_not']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('user.status', (int)$data['status']);
		}

		if (isset($data['user_verified'])) {
			$this->bmdb->group_start();
			$this->bmdb->where('user.mobile_verified', (int)$data['user_verified']);
			$this->bmdb->or_where('user.email_verified', (int)$data['user_verified']);
			$this->bmdb->group_end();
		}

		$this->bmdb->where('user._deleted', 0);

		$this->bmdb->from('user');

		$total = $this->bmdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bmdb->limit($data['limit'], $data['start']);
		} else {
			$this->bmdb->limit(10, 0);
		}

		$sort_data = [
			'user.first_name',
			'user.status',
			'user.date_added',
			'user.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->bmdb->order_by($sort, $order);

		return ['rows' => $this->bmdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->bmdb->insert('user', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bmdb->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bmdb->where('id', $id);
		$this->bmdb->update('user', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->bmdb->where('id', $id);
		$this->bmdb->update('user', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
