<?php defined('BASEPATH') or exit('No direct script access allowed');

class PrinterCosting_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('printer_costing.*');
		$this->db->where('printer_costing.id', (int)$id);
		return $this->db->get('printer_costing')->row_array();
	}

	public function getByPrinterId($printer_id = 0) {
		$this->db->select('printer_costing.*');
		$this->db->where('printer_costing.printer_id', (int)$printer_id);
		return $this->db->get('printer_costing')->row_array();
	}

	public function add($data) {
		$this->db->insert('printer_costing', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();
		$this->session->set_flashdata('flash_message', _l('printer_costing_added_successfully'));
		return $id;
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('printer_costing', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}
}
