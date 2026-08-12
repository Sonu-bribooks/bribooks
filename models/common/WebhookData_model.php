<?php defined('BASEPATH') OR exit('No direct script access allowed');

class WebhookData_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_code = '', $vendor = 'shiprocket') {
		$this->db->where('webhook_data.order_code', $order_code);
		$this->db->where('webhook_data.vendor', $vendor);

		return $this->db->get('webhook_data')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('webhook_data', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($order_code = '', $vendor = 'shiprocket', $data = []) {
		$this->db->where('order_code', $order_code);
		$this->db->where('vendor', $vendor);

		$this->db->update('webhook_data', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
