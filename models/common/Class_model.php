<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Class_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		/*
		update `classes` c, classes_to_site c2s
		set c.site_id = c2s.site_id
		where c.id = c2s.class_id
		*/
	}

	public function get($id = 0) {
		$this->db->select('classes.*, slots.slot_start AS slot, course.title AS course, CONCAT(users.first_name, " ", users.last_name) AS teacher, users.email AS email, users.mobile AS mobile, centers.name AS center, centers.city_id');

		if ($id > 0) {
			$this->db->where('classes.id', (int)$id);
		}

		$this->db->join('users', 'users.id = classes.teacher_id', 'left');
		$this->db->join('course', 'course.id = classes.course_id', 'left');
		$this->db->join('slots', 'slots.id = classes.slot_id', 'left');
		$this->db->join('centers', 'centers.id = classes.center_id', 'left');

		return $this->db->get('classes');
	}

	public function get_all($data = []) {
		// if (isset($data['site_id'])) {
		// 	$this->db->select('class_id');
		// 	$this->db->where('site_id', (int)$data['site_id']);
		// 	$this->db->from('classes_to_site');
		//
		// 	$where_clause = $this->db->get_compiled_select();
		// }

		$this->db->select('classes.*, slots.slot_start AS slot, course.title AS course, CONCAT(users.first_name, " ", users.last_name) AS teacher, users.email AS email, users.mobile AS mobile, centers.name AS center, centers.city_id');

		if (isset($data['site_id'])) {
			// $this->db->where("`classes`.`id` IN ($where_clause)", NULL, FALSE);
			$this->db->where('classes.site_id', (int)$data['site_id']);
		}

		if (!empty($data['class_id'])) {
			$this->db->where('classes.id', (int)$data['class_id']);
		}

		if (!empty($data['center_id'])) {
			$this->db->where('classes.center_id', (int)$data['center_id']);
		}

		if (!empty($data['teacher_id'])) {
			$this->db->where('classes.teacher_id', (int)$data['teacher_id']);
		}

		if (!empty($data['course_id'])) {
			$this->db->where('classes.course_id', (int)$data['course_id']);
		}

		if (!empty($data['slot_id'])) {
			$this->db->where('classes.slot_id', (int)$data['slot_id']);
		}

		if (isset($data['is_demo'])) {
			$this->db->where('classes.is_demo', (int)$data['is_demo']);
		}

		if (!empty($data['mode'])) {
			$this->db->where('classes.mode', $data['mode']);
		}

		if (isset($data['status'])) {
			$this->db->where('classes.status', (int)$data['status']);
		}

		if (isset($data['exported'])) {
			$this->db->where('classes.exported', (int)$data['exported']);
		}

		$this->db->join('users', 'users.id = classes.teacher_id');
		$this->db->join('course', 'course.id = classes.course_id');
		$this->db->join('slots', 'slots.id = classes.slot_id');
		$this->db->join('centers', 'centers.id = classes.center_id', 'left');

		if (!empty($data['order']) && !empty($data['sort']) && in_array($data['order'], ['ASC', 'DESC'])) {
			$this->db->order_by($data['sort'], $data['order']);
		} else {
			$this->db->order_by('classes.date_added', 'DESC');
		}

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		return $this->db->get('classes');
	}

	public function add() {
		$validity = $this->check_duplication('on_create');

		if ($validity == false) {
			$this->session->set_flashdata('error_message', _l('class_duplication'));
		} else {
			$this->db->insert('classes', [
				'name'				=> $this->input->post('name'),
				'mode'				=> $this->input->post('mode'),
				'site_id'			=> (int)$this->input->post('site_id'),
				'course_id'			=> (int)$this->input->post('course_id'),
				'slot_id'			=> (int)$this->input->post('slot_id'),
				'teacher_id'		=> (int)$this->input->post('teacher_id'),
				'backup_teacher_id'	=> json_encode($this->input->post('backup_teacher_id')),
				'center_id'			=> (int)$this->input->post('center_id'),
				'is_demo'			=> (int)$this->input->post('is_demo') ?? 0,
				'color'				=> $this->input->post('color') ?? '',
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();

			if ($this->input->post('mode') == 'online') {
				$this->load->model('common/Schedule_model', 'schedule_model');

				$this->schedule_model->addScheduleLink([
					'class_id'		=> (int)$id,
					'teacher_id'	=> (int)$this->input->post('teacher_id'),
				]);
			}

			if (!$this->input->post('is_demo') || $this->input->post('mode') == 'offline') {
				$this->updateStudents($id);
			}

			$this->session->set_flashdata('flash_message', _l('class_added_successfully'));

			return $id;
		}
	}

	public function edit($id = 0) {
		$validity = $this->check_duplication('on_update', $id);

		if ($validity) {
			$class_info = self::get($id)->row_array();

			$this->db->update('classes', [
				'name'				=> $this->input->post('name'),
				'mode'				=> $this->input->post('mode'),
				'site_id'			=> (int)$this->input->post('site_id'),
				'course_id'			=> (int)$this->input->post('course_id'),
				'slot_id'			=> (int)$this->input->post('slot_id'),
				'teacher_id'		=> (int)$this->input->post('teacher_id'),
				'backup_teacher_id'	=> json_encode($this->input->post('backup_teacher_id')),
				'center_id'			=> (int)$this->input->post('center_id'),
				'is_demo'			=> (int)$this->input->post('is_demo') ?? 0,
				'color'				=> $this->input->post('color') ?? '',
				'date_modified'		=> date('Y-m-d H:i:s'),
			], [
				'id'				=> (int)$id
			]);

			if ($this->input->post('mode') == 'online') {
				$this->load->model('common/Schedule_model', 'schedule_model');

				$this->schedule_model->addScheduleLink([
					'class_id'		=> (int)$id,
					'teacher_id'	=> (int)$this->input->post('teacher_id'),
				]);
			}

			$this->db->update('schedules', [
				'mode'			=> $this->input->post('mode'),
				'user_id'		=> (int)$this->input->post('teacher_id'),
				'is_demo'		=> (int)$this->input->post('is_demo') ?? 0,
				'modified'		=> 1,
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'class_id' 		=> (int)$id
			]);

			if ($class_info['slot_id'] != $this->input->post('slot_id')) {
				$this->load->model('common/Slot_model', 'slot_model');
				$slot_info = $this->slot_model->get($this->input->post('slot_id'))->row_array();

				$this->db->set('schedule', "CONCAT(DATE(schedule), ' ', '{$slot_info['slot_start']}')", FALSE);
				$this->db->where('class_id', (int)$id);
				$this->db->update('schedules');
			}

			$this->updateStudents($id);
		
			$this->session->set_flashdata('flash_message', _l('update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('duplication'));
		}
	}

	public function updateStudents($class_id, $data = []) {
		$this->db->where('class_id', (int)$class_id);
		$this->db->delete('classes_to_students');

		$data = $this->input->post('student_id') ? $this->input->post('student_id') : $data;

		foreach ($data as $enrol_id) {
			$enrol_info = $this->db->get_where('enrol', [
				'id'	=> (int)$enrol_id
			])->row_array();

			$this->db->insert('classes_to_students', [
				'class_id'		=> (int)$class_id,
				'student_id'	=> $enrol_info ? (int)$enrol_info['user_id'] : 0,
				'enrol_id'		=> (int)$enrol_id,
			]);
		}
	}

	public function check_duplication($action = null, $id = 0) {
		return true;

		if ($this->input->post('mode') == 'offline') {
			$this->db->where('center_id', (int)$this->input->post('center_id'));
		}

		$duplicate_check = $this->db->get_where('classes', [
			'slot_id'		=> (int)$this->input->post('slot_id'),
			'course_id'		=> (int)$this->input->post('course_id'),
			'mode'			=> $this->input->post('mode'),
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
		if (!in_array($this->session->userdata('role_id'), [1, 5])) return;

		$this->db->where('id', (int)$id);
		$this->db->delete('classes');

		$this->db->delete('schedules', ['class_id' => (int)$id]);
		$this->db->delete('classes_to_students', ['class_id' => (int)$id]);

		$this->session->set_flashdata('flash_message', _l('deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)->row_array()) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('classes', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('class_updated_successfully'));
	}

	public function get_all_students($id) {
		$students = [];

		$this->db->where('class_id', (int)$id);
		$results = $this->db->get('classes_to_students')->result_array();

		foreach ($results as $result) {
			$students[] = (int)$result['student_id'];
		}

		return $students;
	}

	public function get_all_enrolled_students($id) {
		$students = [];

		$this->db->where('class_id', (int)$id);
		$results = $this->db->get('classes_to_students')->result_array();

		foreach ($results as $result) {
			$students[] = (int)$result['enrol_id'];
		}

		return $students;
	}

	public function get_filtered_students($data = []) {
		if (isset($data['exported'])) {
			$this->db->where('exported', (int)$data['exported']);
		}

		$this->db->order_by('rand()');

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		return $this->db->get('classes_to_students')->result_array();
	}

	public function get_attendance($data = []) {
		if (!empty($data['class_id'])) {
			$this->db->where('class_id', (int)$data['class_id']);
		}

		if (!empty($data['schedule_id'])) {
			$this->db->where('schedule_id', (int)$data['schedule_id']);
		}

		if (!empty($data['teacher_id'])) {
			$this->db->where('teacher_id', (int)$data['teacher_id']);
		}

		return $this->db->get('attendance');
	}

	public function mark_attendance() {
		if ($schedule_info = $this->db->get_where('schedules', ['id' => (int)$this->input->post('id')])->row_array()) {
			$results = $this->get_all_students($schedule_info['class_id']);

			$this->load->model('user/Lead_model', 'lead_model');
			$demo_students = $this->lead_model->getDemoStudents($this->input->post('id'));

			if ($schedule_info['is_demo']) {
				if ($schedule_info['mode'] == 'online') {
					$results = $demo_students;
				} else {
					$results = array_unique(array_merge($results, $demo_students));
				}
			}

			$students = [];

			foreach ($this->input->post('attendance') ?? [] as $student_id) {
				if (in_array($student_id, $results)) {
					$students[] = (int)$student_id;
				}
			}

			if ($schedule_info['is_demo']) {
				foreach ($results ?? [] as $student_id) {
					if (in_array($student_id, ($this->input->post('attendance') ?? []))) {
						$this->updateLeadStatus($student_id, $this->input->post('id'), 2);
					} else {
						$this->updateLeadStatus($student_id, $this->input->post('id'), 3);
					}
				}
			}

			$attendance_info = $this->db->get_where('attendance', ['schedule_id' => (int)$this->input->post('id')])->row_array();

			if ($attendance_info) {
				$this->db->update('attendance', [
					'schedule_id'	=> (int)$this->input->post('id'),
					'class_id'		=> (int)$schedule_info['class_id'],
					'teacher_id'	=> (int)$this->session->userdata('user_id'),
					'students'		=> json_encode($students),
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> $attendance_info['id']
				]);

				$this->session->set_flashdata('flash_message', _l('attendance_update_successfully'));
			} else {
				$this->db->insert('attendance', [
					'schedule_id'	=> (int)$this->input->post('id'),
					'class_id'		=> (int)$schedule_info['class_id'],
					'teacher_id'	=> (int)$this->session->userdata('user_id'),
					'students'		=> json_encode($students),
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);

				$this->session->set_flashdata('flash_message', _l('attendance_added_successfully'));
			}
		} else {
			$this->session->set_flashdata('error_message', _l('invalid_class'));
		}
	}

	public function updateLeadStatus($student_id = 0, $schedule_id = 0, $status = 2) {
		$lead_schedule_info = $this->db->get_where('demo_lead_schedule', [
			'student_id' 	=> (int)$student_id,
			'schedule_id' 	=> (int)$schedule_id
		])->row_array();

		$this->db->update('lead', [
			'status'			=> (int)$status,
			'date_demo'			=> date('Y-m-d H:i:s'),
		], [
			'id'				=> (int)$lead_schedule_info['lead_id'],
		]);

		if ($status === 2) {
			$this->load->model('Alert_model', 'alert_model');

			$this->alert_model->demoCompleted($lead_schedule_info['lead_id']);
		}
	}

	public function getEnrolId($class_id, $student_id) {
		return $this->db->get_where('classes_to_students', [
			'class_id'		=> (int)$class_id,
			'student_id'	=> (int)$student_id,
		])->row_array();
	}

	public function getClassByEnrolId($enrol_id) {
		return $this->db->get_where('classes_to_students', [
			'enrol_id'	=> (int)$enrol_id,
		])->row()->class_id;
	}
}
