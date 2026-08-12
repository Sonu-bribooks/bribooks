<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Course_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		if ($id > 0) {
			$this->db->where('id', (int)$id);
		}

		return $this->db->get('course');
	}

	public function get_all($data = []) {
		if (isset($data['site_id'])) {
			$this->db->select('course_id');
			$this->db->where('site_id', (int)$data['site_id']);
			$this->db->from('course_to_site');

			$where_clause = $this->db->get_compiled_select();
		}

		if (isset($data['exported'])) {
			$this->db->where('exported', (int)$data['exported']);
		}

		if (isset($data['status'])) {
			$this->db->where('status', (int)$data['status']);
		}

		if (isset($data['site_id'])) {
			$this->db->where("`course`.`id` IN ($where_clause)", NULL, FALSE);
		}

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		$this->db->order_by('sort_order', 'DESC');

		return $this->db->get('course');
	}

	public function add() {
		$validity = $this->check_duplication('on_create');

		if ($validity == false) {
			$this->session->set_flashdata('error_message', _l('class_duplication'));
		} else {
			$this->db->insert('course', [
				'name'			=> $this->input->post('name'),
				'mode'			=> $this->input->post('mode'),
				'course_id'		=> (int)$this->input->post('course_id'),
				'slot_id'		=> (int)$this->input->post('slot_id'),
				'teacher_id'	=> (int)$this->input->post('teacher_id'),
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', _l('class_added_successfully'));
		}
	}

	public function edit($id = 0) {
		$validity = $this->check_duplication('on_update', $id);

		if ($validity) {
			$this->db->where('id', (int)$id);

			$this->db->update('course', [
				'name'			=> $this->input->post('name'),
				'mode'			=> $this->input->post('mode'),
				'course_id'		=> (int)$this->input->post('course_id'),
				'slot_id'		=> (int)$this->input->post('slot_id'),
				'teacher_id'	=> (int)$this->input->post('teacher_id'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('flash_message', _l('update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('email_duplication'));
		}

		$this->upload_image($id);
	}

	public function check_duplication($action = null, $id = 0) {
		$duplicate_check = $this->db->get_where('course', [
			'mode'			=> $this->input->post('mode'),
			'slot_id'		=> (int)$this->input->post('slot_id'),
			'teacher_id'	=> (int)$this->input->post('teacher_id'),
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
		$this->db->where('id', (int)$id);
		$this->db->delete('course');
		$this->session->set_flashdata('flash_message', _l('deleted_successfully'));
	}

	public function enrol($course_id, $student_id) {
		$duplicate_check = $this->db->get_where('enrol', [
			'course_id'		=> (int)$course_id,
			'user_id'		=> (int)$student_id,
		]);

		if ($duplicate_check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', _l('enrol_duplication'));
		} else {
			$this->db->insert('enrol', [
				'course_id'		=> (int)$course_id,
				'user_id'		=> (int)$student_id,
				'date_added'	=> strtotime(date('Y-m-d H:i:s')),
			]);

			$id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', _l('enrolled_successfully'));
		}
	}
}
