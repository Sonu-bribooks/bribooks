<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Lr {
	public function lr_category($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->lr_category_model->add($this->input->post());
			redirect(site_url('admin/lr_category'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->lr_category_model->edit($param2, $this->input->post());
			redirect(site_url('admin/lr_category'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->lr_category_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/lr_category'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->lr_category_model->delete($param2);
			redirect(site_url('admin/lr_category'), 'refresh');
		}

		$data['page_name'] 		= 'lr/category/index';
		$data['page_title'] 	= _l('lr_category');
		$data['action_add'] 	= site_url('admin/lr_category_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_lr_category');

		$this->load->view('backend/index', $data);
	}

	public function lr_category_form($param1 = NULL, $param2 = NULL) {
		$data['categories'] = $this->lr_category_model->get_all([
			'sort'	=> 'name',
			'order'	=> 'ASC'
		])['rows'] ?? [];

		if ($param1 == 'add') {
			$data['page_name'] 						= 'lr/category/form';
			$data['page_title'] 					= _l('lr_category_add');
			$data['action'] 						= site_url('admin/lr_category/add');
			$data['units']							= [];
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'lr/category/form';
			$data['page_title'] 					= _l('lr_category_edit');
			$data['action'] 						= site_url('admin/lr_category/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->lr_category_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function lr_questionbank($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->lr_questionbank_model->add($this->input->post());
			redirect(site_url('admin/lr_questionbank'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->lr_questionbank_model->edit($param2, $this->input->post());
			redirect(site_url('admin/lr_questionbank'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->lr_questionbank_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/lr_questionbank'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->lr_questionbank_model->delete($param2);
			redirect(site_url('admin/lr_questionbank'), 'refresh');
		}

		$data['page_name'] 		= 'lr/questionbank/index';
		$data['page_title'] 	= _l('lr_questionbank');
		$data['action_add'] 	= site_url('admin/lr_questionbank_form/add');

		$data['category_id']	= 'all';
		$data['level']			= 'all';

		if ($this->input->get('category_id')) {
			$data['category_id'] = (int)$this->input->get('category_id');
		}

		if ($this->input->get('level')) {
			$data['level'] = (string)$this->input->get('level');
		}

		$data['categories'] = $this->lr_category_model->get_all([
			'sort'	=> 'name',
			'order'	=> 'ASC'
		])['rows'] ?? [];

		$data['action_ajax'] = site_url(sprintf('admin/ajax_lr_questionbank/%d/%s',
			$data['category_id'],
			$data['level'],
		));

		$this->load->view('backend/index', $data);
	}

	public function lr_questionbank_form($param1 = NULL, $param2 = NULL) {
		$data['categories'] = $this->lr_category_model->get_all([
			'sort'	=> 'name',
			'order'	=> 'ASC'
		])['rows'] ?? [];

		if ($param1 == 'add') {
			$data['page_name'] 						= 'lr/questionbank/form';
			$data['page_title'] 					= _l('lr_questionbank_add');
			$data['action'] 						= site_url('admin/lr_questionbank/add');
			$data['units']							= [];
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'lr/questionbank/form';
			$data['page_title'] 					= _l('lr_questionbank_edit');
			$data['action'] 						= site_url('admin/lr_questionbank/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->lr_questionbank_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function lr_assessment($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'status') {
			$this->lr_assessment_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/lr_assessment'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->lr_assessment_model->delete($param2);
			redirect(site_url('admin/lr_assessment'), 'refresh');
		}

		$data['page_name'] 		= 'lr/assessment/index';
		$data['page_title'] 	= _l('lr_assessment');
		$data['action_ajax'] 	= site_url('admin/ajax_lr_assessment');

		$this->load->view('backend/index', $data);
	}

	public function lr_assessment_code($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->lr_assessment_code_model->add($this->input->post());
			redirect(site_url('admin/lr_assessment_code'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->lr_assessment_code_model->edit($param2, $this->input->post());
			redirect(site_url('admin/lr_assessment_code'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->lr_assessment_code_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/lr_assessment_code'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->lr_assessment_code_model->delete($param2);
			redirect(site_url('admin/lr_assessment_code'), 'refresh');
		}

		$data['page_name'] 		= 'lr/assessment_code/index';
		$data['page_title'] 	= _l('lr_assessment_code');
		$data['action_add'] 	= site_url('admin/lr_assessment_code_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_lr_assessment_code');

		$this->load->view('backend/index', $data);
	}

	public function lr_assessment_code_form($param1 = NULL, $param2 = NULL) {
		$data['categories'] = $this->lr_category_model->get_all([
			'sort'	=> 'name',
			'order'	=> 'ASC'
		])['rows'] ?? [];

		$data['students'] = $this->student_model->get_all([
			'sort'	=> 'first_name',
			'order'	=> 'ASC'
		])->result_array();
		$data['teachers'] = $this->teacher_model->get_all([
			'sort'	=> 'first_name',
			'order'	=> 'ASC'
		])->result_array();

		if ($param1 == 'add') {
			$data['page_name'] 						= 'lr/assessment_code/form';
			$data['page_title'] 					= _l('lr_assessment_code_add');
			$data['action'] 						= site_url('admin/lr_assessment_code/add');
			$data['details']['code']				= mb_strtoupper(random_code());
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'lr/assessment_code/form';
			$data['page_title'] 					= _l('lr_assessment_code_edit');
			$data['action'] 						= site_url('admin/lr_assessment_code/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->lr_assessment_code_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}
}
