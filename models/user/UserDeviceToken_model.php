<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserDeviceToken_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('user_device_token')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('user_device_token', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$token_id = $this->db->insert_id();

		return $token_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_device_token', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByUser($user_id = 0, $data = []) {
		$this->db->where('user_id', (int)$user_id);
		$this->db->update('user_device_token', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByUser($user_id = 0) {
		$this->db->select('*');
		$this->db->where('user_id', (int)$user_id);
		return $this->db->get('user_device_token')->row_array();
	}
}
