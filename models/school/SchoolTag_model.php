<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolTag_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			school_tag.*
		');

		$this->db->where('school_tag.id', (int)$id);
		$this->db->where('school_tag._deleted', 0);

		return $this->db->get('school_tag')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			school_tag.*
		');

		if (!empty($data['name'])) {
			$this->db->where('school_tag.name', $data['name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('school_tag.name', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('school_tag._deleted', 0);
		$this->db->from('school_tag');

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
			'school_tag.id',
			'school_tag.name',
			'school_tag.date_added',
			'school_tag.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_tag.id';
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
		$this->db->insert('school_tag', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$school_tag_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('school_tag_added_successfully'));

		return $school_tag_id;
	}

	public function edit($school_tag_id = 0, $data = []) {
		$this->db->where('id', $school_tag_id);
		$this->db->update('school_tag', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('school_tag_edited_successfully'));
	}

	public function delete($school_tag_id = 0) {
		$this->db->where('id', $school_tag_id);
		$this->db->update('school_tag', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
