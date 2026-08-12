<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Slot_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		if ($id > 0) {
			$this->db->where('id', $id);
		}

		return $this->db->get('slots');
	}

	public function get_all($data = []) {
		if (!empty($data['slot_id'])) {
			$this->db->where('id', $data['slot_id']);
		}

		if (isset($data['exported'])) {
			$this->db->where('exported', (int)$data['exported']);
		}

		$this->db->order_by('slot_start', 'ASC');

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		return $this->db->get('slots');
	}

	public function add() {
		$validity = $this->check_duplication('on_create');

		if ($validity == false) {
			$this->session->set_flashdata('error_message', get_phrase('slot_duplication'));
		} else {
			$this->db->insert('slots', [
				'name'			=> $this->input->post('name'),
				'type'			=> $this->input->post('type'),
				'slot_start'	=> $this->input->post('slot'),
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('slot_added_successfully'));
		}
	}

	public function edit($id = 0) {
		$validity = $this->check_duplication('on_update', $id);

		if ($validity) {
			$this->db->where('id', (int)$id);

			$slots = explode('-', $this->input->post('slot'), 2);

			$this->db->update('slots', [
				'name'			=> $this->input->post('name'),
				'type'			=> $this->input->post('type'),
				'slot_start'	=> $this->input->post('slot'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('flash_message', get_phrase('update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', get_phrase('email_duplication'));
		}
	}

	public function check_duplication($action = null, $id = 0) {
		$slots = explode('-', $this->input->post('slot'), 2);

		$duplicate_check = $this->db->get_where('slots', [
			'type'			=> $this->input->post('type'),
			'slot_start'	=> $this->input->post('slot'),
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

	public function delete($id = "") {
		return;
		$this->db->where('id', $id);
		$this->db->delete('slots');
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}
}
