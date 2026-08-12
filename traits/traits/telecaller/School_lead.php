<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait School_lead {
	public function schoolLead($archived = 0) {
		$data['page_name']		= 'school';
		$data['page_title']		= _l('school_lead');

		$data['page_title']		= $archived ? _l('archived_lead') : _l('school_lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('school_lead');

		$data['action_lead']	= site_url('telecaller/schoolLead/' . (1 ^ $archived));

		$data['leads'] 			= [];

		$data['archived'] 		= $archived;

		$this->load->view('backend/index', $data);
	}

	public function edit_school_lead() {
		$json = [];

		$this->form_validation->set_rules('name', _l('school_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('authorized_person', _l('authorized_person'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('no_of_students', _l('no_of_students'), 'trim|required|numeric|greater_than[0]|less_than[20000]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');

		$this->form_validation->set_rules('school_lead_id', _l('school_lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		$json['error'] = _l('error_unknown');

		if ($lead_info = $this->school_lead_model->get($this->input->post('school_lead_id'))) {
			if ($lead_info['telecaller_id'] != $this->session->userdata('user_id')) {
				$json['error'] = _l('error_unauthorized');
			}
		} else {
			$json['error'] = _l('error_school_lead_id');
		}

		if (!$json) {
			$this->school_lead_model->edit($this->input->post('school_lead_id'), [
				'name'				=> $this->input->post('name'),
				'authorized_person'	=> $this->input->post('authorized_person'),
				'no_of_students'	=> $this->input->post('no_of_students'),
				'email'				=> $this->input->post('email'),
				'mobile'			=> $this->input->post('mobile'),
			]);

			$json['error'] 		= $this->session->flashdata('error_message');

			$json['success'] 	= _li('Edited Successfully');

			$json['redirect']	= site_url('telecaller/schoolLead');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function school_lead_detail() {
		$json = [];

		if ($this->input->post('school_lead_id')) {
			$json['lead'] 			= $this->school_lead_model->get($this->input->post('school_lead_id'));
			$json['lead']['status'] = _ls($json['lead']['status']);
		} else {
			$json['error'] = _l('error_school_lead_id');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function school_status($param1 = null) {
		$json = [];

		if ($param1 == 'add') {
			if ($this->input->method() == 'post') {
				$this->form_validation->set_rules('school_lead_id', _l('school_lead_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('status', _l('status'), 'trim|required|in_list[' . implode(',', array_keys(LEAD_STATUSES)). ']');

				$valid = $this->form_validation->run();

				$json = !$valid ? validation_errors() : [];

				if (!$json) {
					$this->school_lead_model->add_status();

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

	public function school_lead_status() {
		$json = [];

		if ($this->input->post('school_lead_id')) {
			$json['statuses'] = $this->school_lead_model->get_status($this->input->post('school_lead_id'));
		} else {
			$json['error'] = _l('error_school_lead_id');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_school_lead($archived = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'archived'			=> $archived,
			'telecaller_id'		=> $this->session->userdata('user_id'),
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (in_array($this->session->userdata('user_email'), MASTER_TELECALLERS)) {
			unset($filter_data['telecaller_id']);
		}

		$result = $this->school_lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= count($result['rows'] ?? []);

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$state = $this->state_model->get($lead['state_id']);
			$city = $this->city_model->get($lead['city_id']);

			$lead['telecaller_id'] && ($telecaller_info = $this->telecaller_model->get($lead['telecaller_id']));

			$statuses = $this->school_lead_model->get_status($lead['id']);

			$student = $this->student_model->get_all([
				'site_id' 	=> $lead['site_id']
			])['total'];

			$total_books = $this->book_model->get_all([
				'site_id' 	=> $lead['site_id']
			])['total'];

			$total_published = $this->book_model->get_all([
				'site_id'	=> $lead['site_id'],
				'status'	=> 1,
			])['total'] ?? 0;

			$total_sold = count($this->order_model->getTopSoldBooks([
				'site_id'	=> $lead['site_id'],
			]));

			$json['data'][] = [
				'id'					=> $lead['id'],
				'checkbox'				=> $lead['id'],
				'sn'					=> $key + 1,
				'date_added'			=> $lead['date_added'],
				'telecaller'			=> $lead['telecaller_id'] ? ($telecaller_info['first_name'] ?? '') . ' ' . ($telecaller_info['last_name'] ?? '') : '',
				'name'					=> $lead['name'],
				'authorized_person'		=> $lead['authorized_person'],
				'site'					=> $lead['site'],
				'state'					=> $lead['state_id'],
				'mobile'				=> $lead['mobile'],
				'email'					=> $lead['email'],
				'state_name'			=> $state['name'] ?? '',
				'city_name'				=> $city['name'] ?? '',
				'total_register'		=> $student,
				'total_books'			=> $total_books,
				'total_published'		=> $total_published,
				'total_sold'			=> $total_sold,
				'no_of_students'		=> $lead['no_of_students'],
				'feedback'				=> implode("\n", array_map(function($status) {
					return $status['status'];
				}, $statuses)),
				'status'				=> _ls($lead['status']) . ' ' . _mv($lead['mobile_verified']),
				'actions'				=> ['id' => $lead['id'], 'email' => $lead['email']],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/*public function assign_telecaller() {
		$this->form_validation->set_rules('school_lead_id', _l('school_lead_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('telecaller_id', _l('telecaller_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('comment', _l('comment'), 'trim|required|min_length[10]|max_length[4000]');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json && $this->school_lead_model->get($this->input->post('school_lead_id')) && $this->telecaller_model->get($this->input->post('telecaller_id'))) {
			$this->school_lead_model->reassignTelecaller([
				'original_telecaller_id'		=> $this->session->user_id,
				'telecaller_id'					=> $this->input->post('telecaller_id'),
				'school_lead_id'				=> $this->input->post('school_lead_id'),
				'comment'						=> $this->input->post('comment'),
			]);

			$json['success'] = _l('successfully_assigned');
		} else {
			$json['error'] = $json['error'] ?? _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}*/

	/*public function archived() {
		$json = [];

		if ($this->input->post('school_lead_id') && ($lead_info = $this->school_lead_model->get($this->input->post('school_lead_id')))) {
			$this->school_lead_model->archived([
				'school_lead_id'=> $lead_info['id'],
				'archived'		=> 1,
			]);

			$json['redirect'] 	= site_url('telecaller/schoolLead/1');
			$json['success'] 	= _l('lead_archived');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}*/

	public function assign_school_lead_telecaller() {
		$json = [];

		$json['error'] = _l('error_unknown');

		if (!$json &&
			$this->input->post('telecaller_id') &&
			$this->input->post('selected')
		) {
			$explode = is_array($this->input->post('selected')) ? $this->input->post('selected') : explode(',', $this->input->post('selected'));

			foreach ($explode as $school_lead_id) {
				$this->school_lead_model->edit($school_lead_id, [
					'telecaller_id'	=> (int)$this->input->post('telecaller_id')
				]);

				$json['success'] = _l('reassigned_successfully');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function school_lead_archived() {
		if ($this->school_lead_model->get($this->input->post('school_lead_id'))) {
			$this->school_lead_model->archived([
				'school_lead_id'=> $this->input->post('school_lead_id'),
				'archived'		=> 1,
			]);

			$json['success'] = _l('archived_successfully');
			$json['redirect'] = site_url('telecaller/schoolLead');
		} else {
			$json['error'] = _l('lead_not_found');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function school_lead_bulk_archive() {
		$json = [];

		if ($this->input->post('selected')) {
			$explode = is_array($this->input->post('selected')) ?
				$this->input->post('selected') :
				explode(',', $this->input->post('selected'));

			foreach ($explode as $school_lead_id) {
				$this->school_lead_model->archived([
					'school_lead_id'=> $school_lead_id,
					'archived'		=> 1,
				]);

				$json['success'] = _l('archived_successfully');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
