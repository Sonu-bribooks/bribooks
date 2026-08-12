<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shiprocket_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_code) {
		$this->db->where('shiprocket_callbacks.order_code', $order_code);
		return $this->db->get('shiprocket_callbacks')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('shiprocket_callbacks', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($order_code = 0, $data = []) {
		$this->db->where('order_code', $order_code);
		$this->db->update('shiprocket_callbacks', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
