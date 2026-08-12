<?php defined('BASEPATH') or exit('No direct script access allowed');

class UserDetailsGuest_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('user_details_nyaf_guest')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_details_nyaf_guest.*');

		if (!empty($data['user_id'])) {
			$this->db->where('user_details_nyaf_guest.user_id', (int)$data['user_id']);
		}

		if (!empty($data['book_id'])) {
			$this->db->where('user_details_nyaf_guest.book_id', (int)$data['book_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('user_details_nyaf_guest.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('user_details_nyaf_guest.site_id', (int)$data['site_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('user_details_nyaf_guest.site_id', (int)$data['site_id']);
		}

		if (isset($data['verified'])) {
			$this->db->where('user_details_nyaf_guest.verified', (int)$data['verified']);
		}

		if (isset($data['is_jury'])) {
			$this->db->where('user_details_nyaf_guest.is_jury', (int)$data['is_jury']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_details_nyaf_guest.guest_name_1', $data['search'], 'after');
			$this->db->or_like('user_details_nyaf_guest.guest_name_2', $data['search'], 'after');
			$this->db->or_like('user_details_nyaf_guest.code', $data['search'], 'after');
			$this->db->or_like('user_details_nyaf_guest.aadhar_no_1', $data['search'], 'after');
			$this->db->or_like('user_details_nyaf_guest.aadhar_no_2', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_details_nyaf_guest._deleted', 0);

		$this->db->from('user_details_nyaf_guest');

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
			'user_details_nyaf_guest.id',
			'user_details_nyaf_guest.status',
			'user_details_nyaf_guest.date_added',
			'user_details_nyaf_guest.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_details_nyaf_guest.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function getByDetails($data = false) {
		if (!$data)
			return false;

		$this->db->select('*');
		$this->db->where('user_id', (int)$data['user_id']);
		$this->db->where('book_id', (int)$data['book_id']);
		return $this->db->get('user_details_nyaf_guest')->row_array();
	}

	public function getByUid($user_id = false) {
		if (!$user_id)
			return false;

		$this->db->select('*');
		$this->db->where('user_id', (int)$user_id);
		return $this->db->get('user_details_nyaf_guest')->result_array();
	}

	public function add($data = []) {
		$this->db->insert('user_details_nyaf_guest', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_details_nyaf_guest', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function get_user_details_guest() {
		$this->db->select('*');
		$this->db->from('user_details_nyaf_guest');
		return $this->db->get()->result_array();
	}

	public function getByCode($code = '') {
		$this->db->select('*');
		$this->db->where('code', $code);
		$this->db->from('user_details_nyaf_guest');
		return $this->db->get()->row_array();
	}
}
