<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PrinterAssignment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($printer_assignment_id = 0) {
		$this->db->select('printer_assignment.*');

		$this->db->where('printer_assignment.id', (int)$printer_assignment_id);
		$this->db->where('printer_assignment._deleted', 0);

		return $this->db->get('printer_assignment')->row_array();
	}

	public function getByCode($printer_assignment_code = '') {
		$this->db->select('printer_assignment.*');

		$this->db->where('printer_assignment.code', $printer_assignment_code);
		$this->db->where('printer_assignment._deleted', 0);

		return $this->db->get('printer_assignment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('printer_assignment.*');

		if (isset($data['manager_id'])) {
			$this->db->where('printer_assignment.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['printer_id'])) {
			$this->db->where('printer_assignment.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(printer_assignment.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (isset($data['status'])) {
			$this->db->where('printer_assignment.status', (int)$data['status']);
		}

		if (isset($data['option_type'])) {
			$this->db->where('printer_assignment.option_type', (int)$data['option_type']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('printer_assignment.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		if (!empty($data['search'])) {
			$this->db->like('printer_assignment.code', $data['search'], 'after');
		}

		$this->db->where('printer_assignment._deleted', 0);

		$this->db->from('printer_assignment');

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
			'printer_assignment.id',
			'printer_assignment.code',
			'printer_assignment.date_added',
			'printer_assignment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'printer_assignment.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('printer_assignment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'code'			=> date('dmy') . (int)$data['printer_id'] . _p_a_code($data['option_type']),
		]);

		$printer_assignment_id = $this->db->insert_id();

		return $printer_assignment_id;
	}

	public function edit($printer_assignment_id = 0, $data = []) {
		$this->db->where('id', (int)$printer_assignment_id);
		$this->db->update('printer_assignment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($printer_assignment_id = 0) {
		$this->db->where('id', (int)$printer_assignment_id);
		$this->db->update('printer_assignment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
