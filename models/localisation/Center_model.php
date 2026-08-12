<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Center_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('centers.*, cities.name AS city');

		if ($id > 0) {
			$this->db->where('centers.id', (int)$id);
		}

		$this->db->join('cities', 'cities.id = centers.city_id', 'left');

		return $this->db->get('centers');
	}

	public function getEnrolledStudentsByCenterId($center_id) {
		$this->db->where('classes.center_id', (int)$center_id);

		$this->db->join('classes', 'classes.id = classes_to_students.class_id');
		$this->db->join('enrol', 'enrol.id = classes_to_students.enrol_id');

		return $this->db->get('classes_to_students');
	}

	public function get_all($data = []) {
		$this->db->select('centers.*, cities.name AS city');

		if (!empty($data['center_id'])) {
			$this->db->where('centers.id', (int)$data['center_id']);
		}

		if (!empty($data['city_id'])) {
			$this->db->where('centers.city_id', (int)$data['city_id']);
		}

		$this->db->join('cities', 'cities.id = centers.city_id', 'left');

		return $this->db->get('centers');
	}

	public function add() {
		$validity = $this->check_duplication('on_create');

		if ($validity == false) {
			$this->session->set_flashdata('error_message', get_phrase('center_duplication'));
		} else {
			$this->db->insert('centers', [
				'name'			=> $this->input->post('name'),
				'city_id'		=> (int)$this->input->post('city_id'),
				'address'		=> $this->input->post('address'),
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();

			$this->session->set_flashdata('flash_message', get_phrase('center_added_successfully'));

			return $id;
		}
	}

	public function edit($id = 0) {
		$validity = $this->check_duplication('on_update', $id);

		if ($validity) {
			$this->db->where('id', (int)$id);

			$this->db->update('centers', [
				'name'			=> $this->input->post('name'),
				'city_id'		=> (int)$this->input->post('city_id'),
				'address'		=> $this->input->post('address'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('flash_message', get_phrase('center_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', get_phrase('center_duplication'));
		}
	}

	public function check_duplication($action = null, $id = 0) {
		$duplicate_check = $this->db->get_where('centers', [
			'name'		=> 'junkkkk', //$this->input->post('name'),
			'city_id'	=> $this->input->post('city_id'),
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
		$this->db->where('id', (int)$id);
		$this->db->delete('centers');
		$this->session->set_flashdata('flash_message', get_phrase('center_deleted_successfully'));
	}
}
