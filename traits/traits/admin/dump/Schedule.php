<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Schedule {
	public function schedules($lead_id = 0) {
		$data['page_name']		= 'schedules';
		$data['page_title']	= _l('schedule');

		$data['slots'] 		= $this->slot_model->get_all()->result_array();
		$data['courses'] 	= $this->course_model->get_all()->result_array();
		$data['centers'] 	= $this->center_model->get_all()->result_array();
		$data['students'] 	= $this->student_model->get_all()['rows'] ?? [];


		$data['lead_id'] 	= $lead_id;

		$data['action_event'] 	= site_url('admin/events');

		if ($this->input->get('class_id')) {
			$data['action_event'] 	= site_url('admin/events') . '?class_id=' . (int)$this->input->get('class_id');
			$data['classes']		= [];
		} else {
			$data['classes'] 		= $this->class_model->get_all(['sort' => 'name', 'order' => 'ASC'])->result_array();
		}

		$data['center_id'] 		= 0;
		$data['select_mode'] 	= 0;
		$data['course_id'] 		= 0;

		if (!empty($this->input->get('select_mode')) && !empty($this->input->get('center_id')) && !empty($this->input->get('course_id'))) {
			$data['action_event'] = site_url('admin/events/' . (string)$this->input->get('select_mode') . '/' . (int)$this->input->get('center_id') . '/' . (int)$this->input->get('course_id'));
			$data['center_id'] 		= $this->input->get('center_id');
			$data['select_mode'] 	= $this->input->get('select_mode');
			$data['course_id'] 		= $this->input->get('course_id');
		}

		$this->load->view('backend/index', $data);
	}

	// Do here for scheduling
	public function update_schedule($param1 = '', $param2 = '', $param3 = '') {
		$json = [];

		if ($action = $this->input->post('action')) {
			if ($action == 'add') {
				$this->load->library('form_validation');

				$this->input->post('class_ids[]') && $this->form_validation->set_rules('class_ids[]', _l('Class'), 'trim|required|numeric');
				$this->input->post('class_id') && $this->form_validation->set_rules('class_id', _l('Class'), 'trim|required|numeric');
				$this->form_validation->set_rules('schedule', _l('Schedule'), 'trim|required');

				$valid = $this->form_validation->run();

				$json = !$valid ? validation_errors() : [];

				if (!$json) {
					$schedule_id = $this->schedule_model->add();

					$json['error'] = $this->session->flashdata('error_message');
					$json['success'] = $this->session->flashdata('flash_message');

					if (empty($json['error'])) {
						$schedule = $this->schedule_model->get($schedule_id);

						$json['schedule'] = self::formatEvent($schedule);
					}
				}
			} elseif ($action == 'edit') {
				$this->load->library('form_validation');

				$this->form_validation->set_rules('id', _l('schedule_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('class_id', _l('Class'), 'trim|required|numeric');
				$this->form_validation->set_rules('schedule', _l('Schedule'), 'trim|required');

				$valid = $this->form_validation->run();

				$json = !$valid ? validation_errors() : [];

				if (!$json) {
					$schedule_id = (int)$this->input->post('id');

					if ($schedule = $this->schedule_model->get_all([
						'schedule_id'	=> $schedule_id,
					])->row_array()) {
						$this->schedule_model->edit($schedule_id);

						$json['error'] = $this->session->flashdata('error_message');
						$json['success'] = $this->session->flashdata('flash_message');

						if (empty($json['error'])) {
							$schedule = $this->schedule_model->get($schedule_id);

							$json['schedule'] = self::formatEvent($schedule);
						}
					}
				}
			} elseif ($action == 'remove') {
				$this->schedule_model->delete($this->input->post('id'));

				$json['error'] = $this->session->flashdata('error_message');
				$json['success'] = $this->session->flashdata('flash_message');
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
	
	private function formatEvent($schedule) {
		$start = date('Y-m-d H:i:s', strtotime($schedule['schedule']));
		$end = date('Y-m-d H:i:s', strtotime('+60 minutes', strtotime($schedule['schedule'])));

		$attendance_info = $this->class_model->get_attendance(['schedule_id' => $schedule['id']])->row_array();

		return [
			'id'				=> $schedule['id'],
			'class_id'			=> $schedule['class_id'],
			'title'				=> $schedule['class'],
			'start'				=> $start,
			'end'				=> $end,
			'slot'				=> date('H:i:s', strtotime($schedule['schedule'])),
			'className'			=> 'bg-' . $schedule['color'],
			'cellColor' 		=> $attendance_info ? '#c0e2ff' : '',
			'description'		=> "{$schedule['class']}<br>{$start} {$end}",
		];
	}

	public function events($mode = '', $center_id = '', $course_id = '') {
		$mode == 'all' && ($mode = '');
		$center_id == 'all' && ($center_id = 0);
		$course_id == 'all' && ($course_id = 0);

		if ($this->input->get('class_id')) {
			$class_id = (int)$this->input->get('class_id');
		} else {
			$class_id = 0;
		}

		$schedules = $this->schedule_model->get_all([
			'date_start'	=> $this->input->get('start'),
			'date_end'		=> $this->input->get('end'),
			'mode'			=> $mode,
			'center_id'		=> $center_id,
			'course_id'		=> $course_id,
			'class_id'		=> $class_id,
		])->result_array();

		$json = [];

		foreach ($schedules as $schedule) {
			$json[] = self::formatEvent($schedule);
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
