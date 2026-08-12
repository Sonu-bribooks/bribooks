<?php defined('BASEPATH') or exit('No direct script access allowed');

class SCMedallionAddress_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('sc_user_medallion_address')->row_array();
	}

	public function get_all() {
		$this->db->select('*');
		$this->db->from('sc_user_medallion_address');
		return $this->db->get()->result_array();
	}

	public function getByIds($user_id = false, $book_id = false, $type = false) {
		if (!$user_id)
			return false;

		$this->db->select('*');
		$this->db->where('user_id', (int)$user_id);
		if($book_id) { $this->db->where('book_id', (int)$book_id); }
		if($type) { $this->db->where('type', $type); }
		return $this->db->get('sc_user_medallion_address')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('sc_user_medallion_address', $data + [
			'status'		=> 1,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('sc_user_medallion_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByIds($user_id = false, $book_id = false, $data = [], $type = false) {
		if (!$user_id || !$book_id)
			return false;

		if ($type) {
			$this->db->where('type', $type);
		}

		$this->db->where('ship_status=0');
		$this->db->where('user_id', (int)$user_id);
		$this->db->where('book_id', (int)$book_id);
		$this->db->update('sc_user_medallion_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
