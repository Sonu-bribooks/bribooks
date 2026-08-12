<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AddressLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function add($data = []) {
		$this->db->insert('address_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$address_log_id = $this->db->insert_id();

		return $address_log_id;
	}
}
