<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TelecallerSchool_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('telecaller_school.*');

		$this->db->where('telecaller_school.id', (int)$id);
		$this->db->where('telecaller_school._deleted', 0);
		return $this->db->get('telecaller_school')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('telecaller_school.*');

		if (!empty($data['event_id'])) {
			$this->db->where('telecaller_school.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('telecaller_school.user_id', (int)$data['user_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('telecaller_school.school_id', (int)$data['school_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('telecaller_school.event_id', $data['search'], 'after');
			$this->db->or_like('telecaller_school.user_id', $data['search'], 'after');
			$this->db->or_like('telecaller_school.school_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('telecaller_school._deleted', 0);

		$this->db->from('telecaller_school');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'telecaller_school.id',
			'telecaller_school.date_added',
			'telecaller_school.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'telecaller_school.id';
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
		$this->db->insert('telecaller_school', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('telecaller_school_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('telecaller_school', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('telecaller_school_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('telecaller_school', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
