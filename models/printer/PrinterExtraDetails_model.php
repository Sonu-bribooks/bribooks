<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PrinterExtraDetails_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('printer_extra_details.*');

		$this->db->where('printer_extra_details.id', (int)$id);
		$this->db->where('printer_extra_details._deleted', 0);

		return $this->db->get('printer_extra_details')->row_array();
	}

	public function getByPrinterId($printer_id = 0) {
		$this->db->select('printer_extra_details.*');
		$this->db->where('printer_extra_details.printer_id', (int)$printer_id);
		return $this->db->get('printer_extra_details')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('printer_extra_details.*');

		$this->db->from('printer_extra_details');

		if (isset($data['pickup_location_id'])) {
			$this->db->where('printer_extra_details.pickup_location_id', (int)$data['pickup_location_id']);
		}

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
			'printer_extra_details.name',
			'printer_extra_details.date_added',
			'printer_extra_details.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'printer_extra_details.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('printer_extra_details', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('printer_extra_details', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('printer_extra_details',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
