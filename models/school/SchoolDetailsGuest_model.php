<?php defined('BASEPATH') or exit('No direct script access allowed');

class SchoolDetailsGuest_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('school_details_nyaf_guest')->row_array();
	}

	public function getByDetails($data = false) {
		if (!$data)
			return false;

		$this->db->select('*');
		$this->db->where('site_id', (int)$data['site_id']);

		if (!empty($data['event_id'])) {
			$this->db->where('event_id', (int)$data['event_id']);
		}

		return $this->db->get('school_details_nyaf_guest')->row_array();
	}

	public function getByUid($site_id = false) {
		if (!$site_id)
			return false;

		$this->db->select('*');
		$this->db->where('site_id', (int)$site_id);

		return $this->db->get('school_details_nyaf_guest')->result_array();
	}

	public function add($data = []) {
		$this->db->insert('school_details_nyaf_guest', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_details_nyaf_guest', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function get_school_details_guest() {
		$this->db->select('*');
		$this->db->from('school_details_nyaf_guest');
		return $this->db->get()->result_array();
	}

	public function getByCode($code = '') {
		$this->db->select('*');
		$this->db->where('code', $code);
		$this->db->from('school_details_nyaf_guest');
		return $this->db->get()->row_array();
	}
}
