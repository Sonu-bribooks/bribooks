<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Schedule {
	public function schedules($param1 = '', $type = 'lead') {
		$data['page_name']		= 'schedules';
		$data['page_title']		= _l('schedule');

		$data['lead_id']		= 0;
		$data['lead_info']		= [];
		$data['class_id']		= 0;

		if ($type === 'class') {
			$data['class_id']		= (int)$param1;

			$data['action_event']	= site_url('portal/events?class_id=' . (int)$param1);
			$data['action_schedule']= site_url('portal/update_schedule_class');
		} else {
			$data['lead_id'] 		= (int)$param1;
			$data['lead_info']		= $this->lead_model->get($param1);

			$data['action_event']	= site_url('portal/events?lead_id=' . (int)$param1);
			$data['action_schedule']= site_url('portal/update_schedule');
		}

		$this->load->view('backend/index', $data);
	}

	public function events() {
		$filter_data = [
			'status'		=> 1,
			'date_start'	=> $this->input->get('start'),
			'date_end'		=> $this->input->get('end'),
			'site_id' 		=> $this->config->item('site_id')
		];

		if ($this->input->get('lead_id')) {
			$lead_info = $this->lead_model->get($this->input->get('lead_id'));

			$filter_data['is_demo'] 	= 1;
			$filter_data['course_id'] 	= $lead_info['course_id'] ?? 0;
			$filter_data['mode']		= $lead_info['mode'] ?? '';
			$filter_data['center_id']	= $lead_info['center_id'] ?? '';
		}

		if ($this->input->get('class_id')) {
			$filter_data['class_id']	= $this->input->get('class_id');
		}

		$schedules = $this->schedule_model->get_all($filter_data)->result_array();

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

	public function update_schedule_class($param1 = '', $param2 = '', $param3 = '') {
		$json = [];

		if ($action = $this->input->post('action')) {
			if ($action == 'add') {
				$this->load->library('form_validation');

				$this->input->post('class_ids[]') && $this->form_validation->set_rules('class_ids[]', _l('Class'), 'trim|required|numeric');
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

						$json['refresh'] = true;
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
