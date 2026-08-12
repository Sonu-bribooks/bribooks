<?php defined('BASEPATH') or exit('No direct script access allowed');

class Printer_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($printer_id = 0) {
		$this->db->select('users.*');
		$this->db->where('users.id', (int)$printer_id);

		$this->db->where_in('users.role_id', [12, 15]);
		$this->db->where('users._deleted', 0);

		return $this->db->get('users')->row_array();
	}

	public function get_all($data = []) {
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

		if (isset($data['section_id'])) {
			$this->db->where('section_id', (int)$data['section_id']);
		}

		$this->db->where_in('users.role_id', [12, 15]);
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

	public function edit($printer_id = "") {
		// Admin does this editing
		$data['first_name'] = html_escape($this->input->post('first_name'));
		$data['last_name'] = html_escape($this->input->post('last_name'));

		if (isset($_POST['alternate_email'])) {
			$data['alternate_email'] = html_escape($this->input->post('alternate_email'));
		}

		$data['biography'] = $this->input->post('biography');
		$data['date_modified'] = date('Y-m-d H:i:s');

		$this->db->where('id', $printer_id);
		$this->db->update('users', $data);

		$this->session->set_flashdata('flash_message', _l('printer_update_successfully'));
	}

	public function enableDisable($printer_id) {
		if ($row = self::get($printer_id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $printer_id);
			$this->db->where_in('users.role_id', [12,15]);
			$this->db->update('users', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('printer_updated_successfully'));
	}

	public function delete($printer_id = 0) {
		$this->db->where('id', (int)$printer_id);
		$this->db->update('users',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', get_phrase('printer_deleted_successfully'));
	}
}
