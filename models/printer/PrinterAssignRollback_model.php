<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PrinterAssignRollback_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function add($data = []) {
		$this->db->insert('printer_assign_rollback', $data + [
			'date_added'	=> date('Y-m-d H:i:s')
		]);

		return $this->db->insert_id();
	}

	public function getByOrderId($order_id = '') {
		$this->db->where('order_id', $order_id);
		return $this->db->get('printer_assign_rollback')->row_array();
	}
}
