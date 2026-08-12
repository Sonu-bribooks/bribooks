<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Classes {
	public function classes($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$this->load->model('common/Class_model', 'class_model');

		if ($param1 == "add") {
			$this->class_model->add();
			redirect(site_url('admin/classes'), 'refresh');
		} elseif ($param1 == "edit") {
			$this->class_model->edit($param2);
			redirect(site_url('admin/classes'), 'refresh');
		} elseif ($param1 == "status") {
			$this->class_model->enableDisable($param2);
			redirect(site_url('admin/classes'), 'refresh');
		} elseif ($param1 == "delete") {
			$this->class_model->delete($param2);
			redirect(site_url('admin/classes'), 'refresh');
		}

		$data['page_name'] = 'class/index';
		$data['page_title'] = _l('classe');
		$data['classes'] = $this->class_model->get_all();
		$this->load->view('backend/index', $data);
	}

	public function class_form($param1 = "", $param2 = "") {
		$data['slots'] 		= $this->slot_model->get_all()->result_array();
		$data['courses'] 	= $this->course_model->get_all()->result_array();
		$data['teachers'] 	= $this->teacher_model->get_all();
		// $data['students'] 	= $this->enrol_model->getAll(['emi_type' => 'premium']);
		$data['cities'] 	= $this->city_model->get_all()['rows'];

		if ($param1 == 'add') {
			$data['page_name'] 		= 'class/form';
			$data['page_title'] 	= _l('class_add');
			$data['action'] 		= site_url('admin/classes/add');

			$this->load->view('backend/index', $data);
		} elseif ($param1 == 'edit') {
			$this->load->model('common/Class_model', 'class_model');

			$data['page_name'] 						= 'class/form';
			$data['class_id'] 						= $param2;
			$data['details'] 						= $this->class_model->get($param2)->row_array();
			$data['details']['student_id'] 			= $this->class_model->get_all_enrolled_students($param2);
			$data['details']['backup_teacher_id'] 	= json_decode($data['details']['backup_teacher_id'], 1);
			$data['page_title'] 					= _l('class_edit');
			$data['action'] 						= site_url('admin/classes/edit/' . (int)$param2);

			$this->load->view('backend/index', $data);
		}
	}

	public function slots($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$this->load->model('common/Slot_model', 'slot_model');

		if ($param1 == 'add') {
			$this->slot_model->add();
			redirect(site_url('admin/slots'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->slot_model->edit($param2);
			redirect(site_url('admin/slots'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->slot_model->delete($param2);
			redirect(site_url('admin/slots'), 'refresh');
		}

		$data['page_name'] = 'slot/index';
		$data['page_title'] = _l('slot');
		$data['slots'] = $this->slot_model->get($param2);
		$this->load->view('backend/index', $data);
	}

	public function slot_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add_slot_form') {
			$data['page_name'] = 'slot/add';
			$data['page_title'] = _l('add');

			$this->load->view('backend/index', $data);
		} elseif ($param1 == 'edit_slot_form') {
			$this->load->model('common/Slot_model', 'slot_model');

			$data['page_name'] = 'slot/edit';
			$data['slot_id'] = $param2;
			$data['slot_details'] = $this->slot_model->get($param2)->row_array();
			$data['page_title'] = _l('slot_edit');

			$this->load->view('backend/index', $data);
		}
	}

	public function ajax_enrol_students() {
		$json = [];
		$filter_data = [
			'emi_type'	=> 'premium',
			'status'	=> 1,
			'course_id'	=> $this->input->post('course_id')
		];

		if ($this->input->post('site_id')) {
			$filter_data['site_id'] = $this->input->post('site_id');
		}

		$json = array_map(function($item) {
			return [
				'id'	=> $item['id'],
				'text'	=> $item['user'] . ' (' . $item['course'] . ')',
			];
		}, $this->enrol_model->getAll($filter_data));

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
