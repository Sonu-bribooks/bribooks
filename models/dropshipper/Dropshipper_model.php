<?php defined('BASEPATH') or exit('No direct script access allowed');

class Dropshipper_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($dropshipper_id = 0) {
		$role_id = _dropshipper_role();

		$this->db->select('users.*, state_ids, colored_limit, bw_limit, bw_printer, pickup_id, limit');
		$this->db->where('users.id', (int)$dropshipper_id);

		$this->db->where('users.role_id', $role_id);
		$this->db->where('users._deleted', 0);

		$this->db->join('dropshippers', 'dropshippers.user_id = users.id', 'left');

		return $this->db->get('users')->row_array();
	}

	public function get_all($data = []) {
		$role_id = _dropshipper_role();

		$this->db->select('users.*');

		if (isset($data['location'])) {
			$this->db->where('location', $data['location']);
		}

		if (isset($data['exported'])) {
			$this->db->where('exported', (int)$data['exported']);
		}

		if (isset($data['mobile'])) {
			$this->db->like('mobile', $data['mobile'], 'after');
		}

		if (isset($data['name'])) {
			$this->db->like('first_name', $data['name'], 'after');
		}

		if (isset($data['source'])) {
			$this->db->like('source', $data['source'], 'after');
		}

		if (isset($data['email'])) {
			$this->db->like('email', $data['email'], 'after');
		}

		if (isset($data['email_verified'])) {
			$this->db->where('email_verified', (int)$data['email_verified']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('mobile_verified', (int)$data['mobile_verified']);
		}

		$this->db->where('users.role_id', $role_id);
		$this->db->where('users._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
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
			'users.amount',
			'users.status',
			'users.first_name',
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

	public function add($data) {
		$this->db->insert('users', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$dropshipper_id = $this->db->insert_id();

		return $dropshipper_id;
	}

	public function edit($dropshipper_id, $data) {
		$this->db->where('id', $dropshipper_id);
		$this->db->update('users', $data);

		$this->session->set_flashdata('flash_message', _l('dropshipper_update_successfully'));
	}

	public function enableDisable($dropshipper_id) {
		if ($row = self::get($dropshipper_id)) {
			$role_id 	= _dropshipper_role();
			$status 	= (1 ^ $row['status']);

			$this->db->where('id', $dropshipper_id);
			$this->db->where('users.role_id', $role_id);
			$this->db->update('users', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('dropshipper_updated_successfully'));
	}

	public function delete($dropshipper_id = 0)	{
		$this->db->where('id', (int)$dropshipper_id);
		$this->db->update('users',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->db->where('user_id', (int)$dropshipper_id);
		$this->db->update('dropshippers',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', get_phrase('dropshipper_deleted_successfully'));
	}

	public function add_dropshipper_pickup($dropshipper_id, $data) {
		$this->db->insert('dropshippers', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$dropshipper_pickup_id = $this->db->insert_id();

		return $dropshipper_pickup_id;
	}

	public function edit_dropshipper_pickup($dropshipper_id, $data) {
		$this->db->where('user_id', $dropshipper_id);
		$this->db->update('dropshippers', $data);

		$this->session->set_flashdata('flash_message', _l('dropshipper_update_successfully'));
	}

	public function getDropShipperByState($state_id = 0) {
		$role_id = _dropshipper_role();

		$this->db->select('dropshippers.*');

		if (!empty($state_id)) {
			$this->db->where("FIND_IN_SET('" . $state_id . "', dropshippers.state_ids) >", 0);
		}

		$this->db->where('users.role_id', $role_id);
		$this->db->where('users._deleted', 0);
		$this->db->where('users.status', 1);

		$this->db->join('dropshippers', 'dropshippers.user_id = users.id', 'left');

		return $this->db->get('users')->row_array();
	}
}
