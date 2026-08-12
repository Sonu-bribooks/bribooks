<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Lead {
	public function lead($archived = 0) {
		$data['page_name']		= 'lead';
		$data['page_title']		= $archived ? _l('archived_lead') : _l('lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('lead');

		self::filterSite($data, 'lead/' . (int)$archived . '/');

		$data['action_lead']	= site_url('portal/lead/' . (1 ^ $archived));
		$data['ajax_url']		= site_url('portal/ajax_lead/' . $archived . '/?site_id=' . (int)$data['site_id']);

		$data['demo_dates'] 	= $this->demo_dates;
		$data['demo_times'] 	= $this->demo_times;
		$data['ages'] 			= DEMO_AGES;

		$data['leads']			= [];
		$data['programs'] 		= [];

		$results = $this->course_model->get_all([
			'site_id'	=> $this->config->item('site_parent_id') > 0 ? $this->config->item('site_parent_id') : $this->config->item('site_id'),
			'status'	=> 'active'
		]);

		foreach ($results->result_array() as $result) {
			$data['programs'][] = [
				'program_id'	=> $result['id'],
				'name'			=> $result['title'],
			];
		}

		$data['cities'] = [];

		$data['archived'] = $archived;

		$this->load->view('backend/index', $data);
	}

	public function ajax_lead($archived = 0) {
		$data = $json['data'] = [];

		self::filterSite($data, 'lead');

		$columns = $this->input->get('columns');

		$filter_data = [
			'archived'			=> $archived,
			'site_id'			=> $data['site_id'],
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$result = $this->lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= count($result['rows'] ?? []);

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$statuses = $this->lead_model->get_status($lead['id']);
			$lead['telecaller_id'] && ($telecaller_info = $this->telecaller_model->get($lead['telecaller_id'])->row_array());

			$json['data'][] = [
				'sn'					=> $key + 1,
				'id'					=> $lead['id'],
				'telecaller'			=> $lead['telecaller_id'] ? ($telecaller_info['first_name'] ?? '') . ' ' . ($telecaller_info['last_name'] ?? '') : '',
				'date_added'			=> $lead['date_added'],
				'name'					=> $lead['name'],
				'parent_name'			=> $lead['parent_name'],
				'mobile'				=> $lead['mobile'],
				'email'					=> $lead['email'],
				'grade'					=> $lead['grade'],
				'program_choice'		=> $lead['course'],
				'feedback'				=> implode("\n", array_map(function($status) {
					return $status['status'];
				}, $statuses)),
				'status'				=> _ls($lead['status']) . ' ' . _mv($lead['mobile_verified']),
				'actions'				=> ['id' => $lead['id'], 'course' => $lead['course'], 'email' => $lead['course']],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function assign_telecaller() {
		$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('telecaller_id', _l('telecaller_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('comment', _l('comment'), 'trim|required|min_length[10]|max_length[4000]');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json && ($lead_info = $this->lead_model->get($this->input->post('lead_id'))) && $this->telecaller_model->get($this->input->post('telecaller_id'))) {
			$this->lead_model->reassignTelecaller([
				'original_telecaller_id'		=> $lead_info['telecaller_id'] ?? 0,
				'telecaller_id'					=> $this->input->post('telecaller_id'),
				'lead_id'						=> $this->input->post('lead_id'),
				'comment'						=> $this->input->post('comment'),
			]);

			$json['success'] = _l('successfully_assigned');
		} else {
			$json['error'] = $json['error'] ?? _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function edit_lead() {
		$json = [];

		//$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');
		$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('name', _l('student_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');
		$this->form_validation->set_rules('learning_mode', _l('learning_mode'), 'trim|required|in_list[online,offline]');

		$this->form_validation->set_rules('student_age', _l('student_age'), [
			'trim',
			'required',
			['student_age', [$this->validate_model, 'studentAge']]
		]);

		$this->form_validation->set_rules('programs', _l('programs'), [
			'trim',
			'required',
			'numeric',
			['programs', [$this->validate_model, 'program']]
		]);

		$this->input->post('learning_mode') == 'offline' && $this->form_validation->set_rules('center', _l('center'), [
			'trim',
			'required',
			'numeric',
			['center', [$this->validate_model, 'center']]
		]);

		$this->form_validation->set_rules('demo_date', _l('demo_date'), [
			'trim',
			'required',
			'in_list[' . implode(',', $this->demo_dates) . ']',
			// ['demo_date', [$this->validate_model, 'demoDate']]
		]);

		$this->form_validation->set_rules('demo_time', _l('demo_time'), [
			'trim',
			'required',
			'in_list[' . implode(',', $this->demo_times) . ']',
			// 'numeric',
			// ['demo_time', [$this->validate_model, 'demoTime']]
		]);

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$this->form_validation->set_rules('center', _l('center'), [
			'trim',
			'numeric',
			['center', [$this->validate_model, 'center']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$this->lead_model->edit($this->input->post('lead_id'), [
				'mobile_verified'	=> 1,
				'name'				=> $this->input->post('name'),
				'parent_name'		=> $this->input->post('parent_name'),
				'age'				=> $this->input->post('student_age'),
				'mode'				=> $this->input->post('learning_mode'),
				'mobile'			=> $this->input->post('mobile'),
				'email'				=> $this->input->post('email'),
				'schedule'			=> $this->input->post('demo_date') . ' ' . $this->input->post('demo_time'),
				'class_id'			=> 0, //(int)$this->input->post('demo_time'),
				'course_id'			=> (int)$this->input->post('programs'),
				'center_id'			=> (int)$this->input->post('center'),
				'telecaller_id'		=> (int)$this->session->userdata('user_id'),
			]);

			//$this->alert_model->demoRequest($this->input->post('lead_id'));

			$json['error'] 		= $this->session->flashdata('error_message');

			$json['success'] 	= sprintf(_li('Your Demo Request for %s has been recieved for %s. Our enrolment team will call you soon to confirm the slot availablity.'),
				$lead_info['course'],
				$this->input->post('demo_date') . ' ' . $this->input->post('demo_time')
			);

			$json['redirect']	= site_url('portal/lead');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function status($param1 = null) {
		$json = [];

		if ($param1 == 'add') {
			if ($this->input->method() == 'post') {
				$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('status', _l('status'), 'trim|required|in_list[' . implode(',', array_keys(LEAD_STATUSES)). ']');

				$valid = $this->form_validation->run();

				$json = !$valid ? validation_errors() : [];

				if (!$json) {
					$this->lead_model->add_status();

					$json['error'] 		= $this->session->flashdata('error_message');
					$json['success'] 	= $this->session->flashdata('flash_message');
				}
			} else {
				$json['error'] = _l('error_unknown');
			}
		} else {

		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function email_update() {
		$json = [];

		if ($this->input->method() == 'post') {
			$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');

			$valid = $this->form_validation->run();

			$json = !$valid ? validation_errors() : [];

			if (!$json) {
				$this->lead_model->update_email([
					'lead_id'		=> $this->input->post('lead_id'),
					'email'			=> $this->input->post('email'),
				]);

				$json['error'] 		= $this->session->flashdata('error_message');
				$json['success'] 	= $this->session->flashdata('flash_message');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function studentEmailUpdate() {
		$json = [];

		if ($this->input->method() == 'post') {
			$this->form_validation->set_rules('student_id', _l('student_id'), 'trim|required|numeric');
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');

			$valid = $this->form_validation->run();

			$json = !$valid ? validation_errors() : [];

			if (!$json) {
				$this->student_model->update_email([
					'student_id'	=> $this->input->post('student_id'),
					'email'			=> $this->input->post('email'),
				]);

				$json['error'] 		= $this->session->flashdata('error_message');
				$json['success'] 	= $this->session->flashdata('flash_message');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function lead_detail() {
		$json = [];

		if ($this->input->post('lead_id')) {
			$json['lead'] 				= $this->lead_model->get($this->input->post('lead_id'));
			$json['lead']['status'] 	= _ls($json['lead']['status']);
			$json['lead']['teacher'] 	= $this->schedule_model->get($json['lead']['schedule_id'])['name'] ?? '';
		} else {
			$json['error'] = _l('error_lead_id');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function lead_status() {
		$json = [];

		if ($this->input->post('lead_id')) {
			$json['statuses'] 	= $this->lead_model->get_status($this->input->post('lead_id'));
		} else {
			$json['error'] 		= _l('error_lead_id');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function send_payment_link() {
		$json = [];

		if ($this->input->post('lead_id') && $this->input->post('amount') && $this->input->post('emi_type') && ($lead_info = $this->lead_model->get($this->input->post('lead_id')))) {
			$this->alert_model->enrol($lead_info['id'], (double)$this->input->post('amount'), $this->input->post('emi_type'));

			$json['success'] 	= _l('email_and_sms_has_been_sent');
		} else if ($this->input->post('enrol_id') && ($enrol_info = $this->enrol_model->get($this->input->post('enrol_id')))) {
			$this->alert_model->renew($enrol_info['id'], (double)$this->input->post('amount'));

			$json['success'] 	= _l('email_and_sms_has_been_sent');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function getEmis() {
		$json = [];

		if ($this->input->post('lead_id') && ($lead_info = $this->lead_model->get($this->input->post('lead_id')))) {
			if ($course_info = $this->course_model->get($lead_info['course_id'])->row_array()) {
				$json['emis'] = [];

				if (!empty($lead_info['mode'])) {
					foreach (json_decode($course_info['emi'], 1) as $key => $amount) {
						if (strpos($key, $lead_info['mode']) !== false) {
							$json['emis'][] = [
								'key'		=> $key,
								'amount'	=> $amount,
							];
						}
					}
				} else {
					foreach (json_decode($course_info['emi'], 1) as $key => $amount) {
						$json['emis'][] = [
							'key'		=> $key,
							'amount'	=> $amount,
						];
					}
				}
			} else {
				$json['error'] 		= _l('error_course');
			}
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function archived() {
		$json = [];

		if ($this->input->post('lead_id') && ($lead_info = $this->lead_model->get($this->input->post('lead_id')))) {
			$this->lead_model->archived([
				'lead_id'		=> $lead_info['id'],
				'archived'		=> 1,
			]);

			$json['redirect'] 	= site_url('portal/lead');
			$json['success'] 	= _l('lead_archived');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
