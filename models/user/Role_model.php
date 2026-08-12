<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->where('role.id', (int)$id);
		$this->db->where('role._deleted', 0);

		$row = $this->db->get('role')->row_array();

		$row['permissions'] = json_decode($row['permissions'], true);

		return $row;
	}

	public function get_all($data = []) {
		$this->db->select('role.*,');

		if (isset($data['name'])) {
			$this->db->where('role.name', $data['name']);
		}

		if (isset($data['status'])) {
			$this->db->where('role.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('role.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('role._deleted', 0);

		$this->db->from('role');

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
			'role.name',
			'role.status',
			'role.date_added',
			'role.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'role.id';
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
		$data['permissions'] = is_array($data['permissions'])
			? json_encode($data['permissions'])
			: $data['permissions']
		;

		$this->db->insert('role', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$role_id = $this->db->insert_id();

		return $role_id;
	}

	public function edit($role_id = 0, $data = []) {
		$data['permissions'] = is_array($data['permissions'])
			? json_encode($data['permissions'])
			: $data['permissions']
		;

		$this->db->where('id', (int)$role_id);
		$this->db->update('role', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('role', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('role', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}
}
