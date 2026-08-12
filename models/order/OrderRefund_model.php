<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderRefund_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = '') {
		$this->db->where('id', $id);
		return $this->db->get('order_refund')->row_array();
	}

	public function getByOrderId($order_id = '') {
		$this->db->where('order_id', $order_id);
		return $this->db->get('order_refund')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('order_refund', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('order_refund', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}
}
