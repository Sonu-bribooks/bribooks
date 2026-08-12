<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DropshipperAssignment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($dropshipper_assignment_id = 0) {
		$this->db->select('dropshipper_assignment.*');

		$this->db->where('dropshipper_assignment.id', (int)$dropshipper_assignment_id);
		$this->db->where('dropshipper_assignment._deleted', 0);

		return $this->db->get('dropshipper_assignment')->row_array();
	}

	public function getByCode($dropshipper_assignment_code = '') {
		$this->db->select('dropshipper_assignment.*');

		$this->db->where('dropshipper_assignment.code', $dropshipper_assignment_code);
		$this->db->where('dropshipper_assignment._deleted', 0);

		return $this->db->get('dropshipper_assignment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('dropshipper_assignment.*');

		if (isset($data['manager_id'])) {
			$this->db->where('dropshipper_assignment.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['printer_id'])) {
			$this->db->where('dropshipper_assignment.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(dropshipper_assignment.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (isset($data['status'])) {
			$this->db->where('dropshipper_assignment.status', (int)$data['status']);
		}

		if (isset($data['option_type'])) {
			$this->db->where('dropshipper_assignment.option_type', (int)$data['option_type']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('dropshipper_assignment.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		if (!empty($data['search'])) {
			$this->db->like('dropshipper_assignment.code', $data['search'], 'after');
		}

		$this->db->where('dropshipper_assignment._deleted', 0);

		$this->db->from('dropshipper_assignment');

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
			'dropshipper_assignment.id',
			'dropshipper_assignment.code',
			'dropshipper_assignment.date_added',
			'dropshipper_assignment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'dropshipper_assignment.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('dropshipper_assignment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'code'			=> date('dmy') . (int)$data['printer_id'] . _p_a_code($data['option_type']),
		]);

		$dropshipper_assignment_id = $this->db->insert_id();

		return $dropshipper_assignment_id;
	}

	public function edit($dropshipper_assignment_id = 0, $data = []) {
		$this->db->where('id', (int)$dropshipper_assignment_id);
		$this->db->update('dropshipper_assignment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($dropshipper_assignment_id = 0) {
		$this->db->where('id', (int)$dropshipper_assignment_id);
		$this->db->update('dropshipper_assignment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
