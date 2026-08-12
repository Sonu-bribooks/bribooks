<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolUser_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

    public function get($id = 0) {

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		$this->db->where('role_id', 9);
		return $this->db->get('users')->row_array();
	}

    public function get_all($data = []) {
		$this->db->select('*');

		if (isset($data['id'])) {
			$this->db->where('users.id', (int)$data['id']);
		}

		if (isset($data['verification_code'])) {
			$this->db->where('users.verification_code', (int)$data['verification_code']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('users.first_name', $data['name']);
		}

        if (isset($data['email'])) {
			$this->db->where('users.email', $data['email']);
		}

        if (isset($data['mobile'])) {
			$this->db->where('users.mobile', $data['mobile']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('users.city_id', (int)$data['city_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('users.state_id', (int)$data['state_id']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('users.mobile_verified', (int)$data['mobile_verified']);
		}

		if (isset($data['email_verified'])) {
			$this->db->where('users.email_verified', (int)$data['email_verified']);
		}

		$this->db->where('users._deleted', 0);
		$this->db->where('users.role_id', 9);
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
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

    public function add($data = []) {
		$this->db->insert('users', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$school_user_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('school_lead_added_successfully'));

		return $school_user_id;
	}

	public function edit($school_user_id = 0, $data = []) {
		$this->db->where('id', $school_user_id);
		$this->db->update('users', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('school_lead_edited_successfully'));
	}
}