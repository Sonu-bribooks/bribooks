<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Users {
	public function getTeachers() {
		$json = [];

		if ($this->input->post('schedule_id') && ($schedule_info = $this->schedule_model->get($this->input->post('schedule_id')))) {
			$json['teachers'] = $this->teacher_model->get_all([
				'site_id'	=> $this->config->item('site_id')
			]);
		} else {
			$json['error'] = _l('error_schedule_id');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function teachers($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			self::validateSite($this->input->post('site_id'), 'teachers');

			$this->teacher_model->add();
			redirect(site_url('portal/teachers'), 'refresh');
		} elseif ($param1 == 'edit') {
			self::validateSite($this->input->post('site_id'), 'teachers');

			$this->teacher_model->edit($param2);
			redirect(site_url('portal/teachers'), 'refresh');
		} elseif ($param1 == 'status') {
			$teacher_info = $this->teacher_model->get($param2);
			self::validateSite($teacher_info['site_id'], 'teachers');

			$this->teacher_model->enableDisable($param2);
			redirect(site_url('portal/teachers'), 'refresh');
		}

		$data['page_name'] = 'teacher/index';
		$data['page_title'] = _l('teacher');

		self::filterSite($data, 'teachers');

		$data['results'] 		= $this->teacher_model->get_all([
			'site_id' => $data['site_id']
		]);

		$this->load->view('backend/index', $data);
	}

	public function teacher_form($param1 = '', $param2 = 0) {
		$data['teacher_id'] 		= $param2;

		$data['courses'] 			= $this->course_model->get_all([
			'site_id' 				=> $this->config->item('site_parent_id') > 0 ? $this->config->item('site_parent_id') : $this->config->item('site_id')
		])['rows'];

		$data['backup_teachers'] 	= $this->teacher_model->get_all([
			'site_id' 				=> $this->config->item('site_id')
		])['rows'];

		$data['page_name'] 			= 'teacher/form';
		$data['teacher_id'] 		= (int)$param2;

		$data['sites'] 		= $this->site_model->get_all([
			'parent_id' 	=> $this->config->item('site_id')
		])['rows'] ?? [];

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> $this->config->item('site_id'),
		]);

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('teacher_add');
			$data['action'] 		= site_url('portal/teachers/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 		= $this->teacher_model->get($param2);
			$data['page_title'] 	= _l('teacher_edit');
			$data['action'] 		= site_url('portal/teachers/edit/' . (int)$param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function telecallers($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			self::validateSite($this->input->post('site_id'), 'telecallers');

			$this->telecaller_model->add();
			redirect(site_url('portal/telecallers'), 'refresh');
		} elseif ($param1 == 'edit') {
			self::validateSite($this->input->post('site_id'), 'telecallers');

			$this->telecaller_model->edit($param2);
			redirect(site_url('portal/telecallers'), 'refresh');
		} elseif ($param1 == 'status') {
			$telecaller_info = $this->telecaller_model->get($param2);
			self::validateSite($telecaller_info['site_id'], 'telecallers');

			$this->telecaller_model->enableDisable($param2);
			redirect(site_url('portal/telecallers'), 'refresh');
		}

		$data['page_name'] = 'telecaller/index';
		$data['page_title'] = _l('telecaller');

		self::filterSite($data, 'telecallers');

		$data['results'] 		= $this->telecaller_model->get_all([
			'site_id' => $data['site_id']
		])['rows'];

		$this->load->view('backend/index', $data);
	}

	public function telecaller_form($param1 = '', $param2 = 0) {
		$data['sites'] 		= $this->site_model->get_all([
			'parent_id' 	=> $this->config->item('site_id')
		])['rows'] ?? [];

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> $this->config->item('site_id'),
		]);

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('telecaller_add');
			$data['action'] 		= site_url('portal/telecallers/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 		= $this->telecaller_model->get($param2);
			$data['page_title'] 	= _l('telecaller_edit');
			$data['action'] 		= site_url('portal/telecallers/edit/' . (int)$param2);
		}

		$data['telecaller_id'] 	= (int)$param2;
		$data['page_name'] 		= 'telecaller/form';

		$this->load->view('backend/index', $data);
	}

	public function students($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			self::validateSite($this->input->post('site_id'), 'students');
			$this->student_model->add($this->input->post());
			redirect(site_url('portal/students'), 'refresh');
		} elseif ($param1 == 'edit') {
			self::validateSite($this->input->post('site_id'), 'students');
			$this->student_model->edit($param2, $this->input->post());
			redirect(site_url('portal/students'), 'refresh');
		} elseif ($param1 == 'status') {
			$student_info = $this->student_model->get($param2);
			self::validateSite($student_info['site_id'], 'students');

			$this->student_model->enableDisable($param2);
			redirect(site_url('portal/students'), 'refresh');
		}

		$data['page_name'] 		= 'student/index';
		$data['page_title'] 	= _l('students');

		self::filterSite($data, 'students');

		$data['results']		= array_map(function($item) {
			$book_written = $this->book_model->get_all([
				'user_id' 	=> (int)$item['id']
			])['total'] ?? 0;
			$book_published = $this->book_model->get_all([
				'user_id' 	=> (int)$item['id'],
				'status'	=> 1,
			])['total'] ?? 0;

			$grade_info = $this->grade_model->get($item['grade_id']);
			$section_info = $this->section_model->get($item['section_id']);

			return array_merge($item, [
				'grade'				=> $grade_info['name'] ?? '',
				'section'			=> $section_info['name'] ?? '',
				'book_written'		=> $book_written,
				'book_published'	=> $book_published,
			]);
		}, $this->student_model->get_all([
			'archived' 	=> 0,
			'site_id' 	=> $data['site_id']
		])['rows'] ?? []);

		$this->load->view('backend/index', $data);
	}

	public function student_form($param1 = '', $param2 = 0) {
		$data['sites'] 		= $this->site_model->get_all([
			'parent_id' 	=> $this->config->item('site_id')
		])['rows'] ?? [];

		$data['grades'] 	= $this->grade_model->get_all([
			'site_id' 		=> $this->config->item('site_id')
		])['rows'] ?? [];

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> $this->config->item('site_id'),
		]);

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('student_add');
			$data['action'] 		= site_url('portal/students/add');
		} elseif ($param1 == 'edit') {
			$data['details'] 		= $this->student_model->get($param2);
			$data['page_title'] 	= _l('student_edit');
			$data['action'] 		= site_url('portal/students/edit/' . (int)$param2);
		}

		$data['student_id'] 	= (int)$param2;
		$data['page_name'] 		= 'student/form';

		$this->load->view('backend/index', $data);
	}

	public function studentDetail() {
		$json = [];

		if ($this->input->post('enrol_id')) {
			$json['student'] 					= $this->enrol_model->get($this->input->post('enrol_id'));
			$json['student']['status'] 			= _es($json['student']['status']);
			$json['student']['amount'] 			= currency($json['student']['amount']);
			$json['student']['renewal_date'] 	= strftime('%b %e, %G', strtotime($json['student']['renewal_date']));
			$json['student']['date_renewed'] 	= strtotime($json['student']['date_renewed']) > 0 ? strftime('%b %e, %G', strtotime($json['student']['date_renewed'])) : '-';
		} else {
			$json['error'] = _l('error_enrol_id');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function getStudents() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->student_model->get_all([
				'mobile'	=> $this->input->get('search'),
				'site_id'	=> $this->config->item('site_id'),
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['first_name'] . ' ' . $result['last_name'] . ' ' . $result['mobile'],
				];
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
