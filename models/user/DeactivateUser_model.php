<?php defined('BASEPATH') or exit('No direct script access allowed');

class DeactivateUser_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('deactivate_user')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('deactivate_user.*');

		if (!empty($data['user_id'])) {
			$this->db->where('deactivate_user.user_id', (int)$data['user_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('deactivate_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('deactivate_user.status', $data['status']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('deactivate_user.site_id', (int)$data['site_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('deactivate_user.event_id', $data['search'], 'after');
			$this->db->or_like('deactivate_user.user_id', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.first_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('deactivate_user._deleted', 0);

		$this->db->join('users', 'users.id = deactivate_user.user_id', 'left');

		$this->db->from('deactivate_user');

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
			'deactivate_user.id',
			'deactivate_user.status',
			'deactivate_user.date_added',
			'deactivate_user.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'deactivate_user.id';
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
		$this->db->insert('deactivate_user', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('deactivate_user', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
