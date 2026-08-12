<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GSchool_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->gdb = $this->db;
	}

	public function get($id = 0) {
		$this->gdb->where('adt_school.id', (int)$id);

		return $this->gdb->get('adt_school')->row_array();
	}

	public function get_all($data = []) {
		if (!empty($data['school_id'])) {
			$this->gdb->where('adt_school.id', (int)$data['school_id']);
		}

		if (!empty($data['school_code'])) {
			$this->gdb->where('adt_school.school_code', $data['school_code']);
		}

		if (!empty($data['nation_id'])) {
			$this->gdb->where('adt_school.nation_id', (int)$data['nation_id']);
		}

		if (!empty($data['name'])) {
			$this->gdb->where('adt_school.school_en_name', $data['name']);
		}

		return $this->gdb->get('adt_school')->result_array();
	}

	public function add($data = []) {
		$validity = $this->check_duplication('on_create', $data);

		if ($validity == false) {
			$this->session->set_flashdata('error_message', _l('school_duplication'));
		} else {
			$this->gdb->insert('adt_school', [
				'school_cn_name'	=> $data['name'],
				'school_en_name'	=> $data['name'],
				'school_code'		=> $data['school_code'],
				'nation_id'			=> (int)$data['nation_id'],
				'ext_id'			=> (int)$data['ext_id'],
				'create_time'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$id = $this->gdb->insert_id();

			$this->session->set_flashdata('flash_message', _l('school_added_successfully'));

			return $id;
		}
	}

	public function edit($id = 0, $data = []) {
		$validity = $this->check_duplication('on_update', $data, $id);

		if ($validity) {
			$this->gdb->where('id', (int)$id);

			$this->gdb->update('adt_school', [
				'school_cn_name'	=> $data['name'],
				'school_en_name'	=> $data['name'],
				'school_code'		=> $data['school_code'],
				'nation_id'			=> (int)$data['nation_id'],
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('flash_message', _l('school_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('school_duplication'));
		}
	}

	public function check_duplication($action = null, $data= [], $id = 0) {
		$duplicate_check = $this->gdb->get_where('adt_school', [
			'school_code'	=> $data['school_code'],
		]);

		if ($action == 'on_create') {
			if ($duplicate_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'on_update') {
			if ($duplicate_check->num_rows() > 0) {
				if ($duplicate_check->row()->id == $id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

	public function delete($id = 0) {
		return;
		$this->gdb->where('id', (int)$id);
		$this->gdb->delete('adt_school');
		$this->session->set_flashdata('flash_message', _l('deleted_successfully'));
	}

	public function getBySchoolCode($school_code = '') {
		return $this->gdb->get('adt_school', [
			'school_code'	=> $school_code
		])->row_array();
	}

	public function getByExtId($ext_id = 0) {
		return $this->gdb->get_where('adt_school', [
			'ext_id'	=> (int)$ext_id
		])->row_array();
	}
}
