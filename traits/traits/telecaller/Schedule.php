<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Schedule {
	public function schedule($param1 = '') {
		$data['page_name']		= 'schedules';
		$data['page_title']		= _l('schedule');

		// $data['slots'] 		= $this->slot_model->get_all(['site_id' => $this->config->item('site_id')])->result_array();
		// $data['courses'] 	= $this->course_model->get_all(['site_id' => $this->config->item('site_id')])->result_array();
		// $data['students'] 	= $this->student_model->get_all(['site_id' => $this->config->item('site_id')])->result_array();
		// $data['classes'] 	= $this->class_model->get_all(['site_id' => $this->config->item('site_id')])->result_array();

		$data['lead_id'] 	= (int)$param1;

		$data['lead_info']	= $this->lead_model->get($param1);

		$this->load->view('backend/index', $data);
	}

	public function update_schedule($param1 = '', $param2 = '', $param3 = '') {
		$json = [];

		if (($this->input->method() == 'post')) {
			$this->form_validation->set_rules('id', _l('schedule_id'), 'trim|required|numeric');
			$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');

			$valid = $this->form_validation->run();

			$json = !$valid ? validation_errors() : [];

			$lead_info = $this->lead_model->get($this->input->post('lead_id'));
			$schedule_info = $this->schedule_model->get($this->input->post('id'));

			// if ($schedule_info['lead_id'] != 0 && $schedule_info['lead_id'] != $this->input->post('lead_id') && $lead_info['mode'] == 'online') {
			// 	$json['error'] = _l('already_assigned_to_the_other_lead');
			// }

			if ($lead_info['mode'] === 'online' && count($this->lead_model->get_all(['schedule_id' => $schedule_info['id']])) >= LIMIT_ONLINE_SCHEDULE) {
				$json['error'] = _l('already_assigned_to_maximum_allowed_students');
			}

			if ($lead_info['status'] > 1) {
				//$json['error'] = _l('not_eligible_for_demo');
			}

			if (strtotime($schedule_info['schedule']) < time()) {
				$json['error'] = _l('can\'t select previous date');
			}

			if (!$json && $lead_info && $schedule_info) {
				$this->lead_model->schedule([
					'schedule_id'		=> $this->input->post('id'),
					'old_schedule_id'	=> $lead_info['schedule_id'],
					'lead_id'			=> $this->input->post('lead_id'),
					'schedule'			=> $schedule_info['schedule'],
				]);

				$names = explode(' ', $lead_info['name'], 2);

				$student_id = $this->lead_model->addStudent([
					'first_name'		=> array_shift($names),
					'last_name'			=> array_shift($names),
					'lead_id'			=> $lead_info['id'],
					'parent_name'		=> $lead_info['parent_name'],
					'schedule_id'		=> $this->input->post('id'),
					'email'				=> $lead_info['email'],
					'mobile'			=> $lead_info['mobile'],
				]);

				//$this->class_model->updateStudents($schedule_info['class_id'], [$student_id]);

				$json['error'] = $this->session->flashdata('error_message');
				$json['success'] = $this->session->flashdata('flash_message');

				if (empty($json['error'])) {
					$schedule = $this->schedule_model->get($this->input->post('id'));
					$old_schedule = $this->schedule_model->get($lead_info['schedule_id']);

					$_GET['lead_id'] = (int)$this->input->post('lead_id');

					$json['schedule'] = self::formatEvent($schedule);
					$json['old_schedule'] = self::formatEvent($old_schedule);

					$this->alert_model->demoConfirmed($lead_info['id']);
				}
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function events() {
		if ($this->input->get('lead_id')) {
			$lead_info = $this->lead_model->get($this->input->get('lead_id'));
		}

		$telecaller_info = $this->telecaller_model->get($this->session->userdata('user_id'))->row_array();

		$schedules = $this->schedule_model->get_all([
			'is_demo'		=> 1,
			'status'		=> 1,
			'site_id' 		=> $this->config->item('site_id'),
			'course_id'		=> $lead_info['course_id'] ?? 0,
			'mode'			=> isset($lead_info['mode']) ? $lead_info['mode'] : $telecaller_info['mode'],
			'mode'			=> $lead_info['mode'] ?? '',
			'center_id'		=> $lead_info['center_id'] ?? '',
			'date_start'	=> $this->input->get('start'),
			'date_end'		=> $this->input->get('end'),
		])->result_array();

		$json = [];

		foreach ($schedules as $schedule) {
			$json[] = self::formatEvent($schedule);
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	private function formatEvent($schedule) {
		if (!$schedule) return null;

		$start = date('Y-m-d H:i:s', strtotime($schedule['schedule']));
		$end = date('Y-m-d H:i:s', strtotime('+60 minutes', strtotime($schedule['schedule'])));

		$color = $schedule['lead_id'] ? '#ff8a70' : '';

		if ($schedule['lead_id'] && $this->input->get('lead_id') == $schedule['lead_id']) {
			$color = 'green';
		}

		//$course_info = $this->course_model->get($schedule['course_id'])->row_array();

		return [
			'id'				=> $schedule['id'],
			'class_id'			=> $schedule['class_id'],
			'title'				=> $schedule['class'],
			'start'				=> $start,
			'end'				=> $end,
			'slot'				=> date('H:i:s', strtotime($schedule['schedule'])),
			'className'			=> 'bg-info',
			'backgroundColor'	=> $color ? $color . ' !important' : '',
			'cellColor' 		=> $color ? '#fff2f0' : '',
			'description'		=> "{$schedule['class']}<br>{$start} {$end}",
		];
	}

	public function scheduleDetail() {
		$json = [];

		if ($this->input->method() == 'post') {
			$this->form_validation->set_rules('id', _l('schedule_id'), 'trim|required|numeric');

			$valid = $this->form_validation->run();

			$json = !$valid ? validation_errors() : [];

			if (!$json) {
				$schedule_info = $this->schedule_model->get($this->input->post('id'));
				$class_info = $this->class_model->get($schedule_info['class_id'])->row_array();

				$json['schedule'] = [
					'id'			=> $schedule_info['id'],
					'class_id'		=> $schedule_info['class_id'],
					'class'			=> $schedule_info['class'],
					'teacher'		=> $class_info['teacher'],
					'course'		=> $class_info['course'],
					'schedule'		=> $schedule_info['schedule'],
				];
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
