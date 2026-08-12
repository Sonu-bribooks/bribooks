<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Lead {
	public function lead($archived = 0) {
		$data['page_name']		= 'lead';
		$data['page_title']		= $archived ? _l('archived_lead') : _l('lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('lead');

		$data['action_lead']	= site_url('telecaller/lead/' . (1 ^ $archived));

		$telecaller_info = $this->telecaller_model->get($this->session->userdata('user_id'));

		$data['leads'] = [];
		/*$this->lead_model->get_all([
			'archived'			=> $archived,
			//'telecaller_id'	=> $this->session->userdata('user_id'),
			//'mode'			=> $telecaller_info['mode'] ?? ''
		]);*/

		$data['demo_dates'] = $this->demo_dates;
		$data['demo_times'] = $this->demo_times;
		$data['ages'] 		= DEMO_AGES;

		$data['programs'] 	= [];

		$results = $this->course_model->get_all([
			'site_id' 	=> $this->config->item('site_id'),
			'status'	=> 'active'
		]);

		foreach ($results->result_array() as $result) {
			$data['programs'][] = [
				'program_id'	=> $result['id'],
				'name'			=> $result['title'],
			];
		}

		$data['countries'] = [];
		$result = $this->country_model->get_all()['rows'] ?? [];

		foreach ($result ?? [] as $result) {
			$data['countries'][] = [
				'country_id'	=> $result['id'],
				'name'			=> $result['name'],
			];
		}

		array_unshift($data['countries'], [
			'country_id'	=> 0,
			'name'			=> _l('all'),
		]);

		$data['cities'] = [];
		$result = $this->city_model->get_all([
			'country_code'	=> $this->config->item('site_country_code')
		]);

		foreach ($result['rows'] ?? [] as $result) {
			$data['cities'][] = [
				'city_id'		=> $result['id'],
				'name'			=> $result['name'],
			];
		}

		array_unshift($data['cities'], [
			'city_id'		=> 0,
			'name'			=> _l('not_listed'),
		]);

		$data['archived'] = $archived;

		$data['sites'] = [];

		foreach ($this->site_ids as $site_id) {
			if ($site_info = $this->site_model->get($site_id)) {
				$data['sites'][] = $site_info;
			}
		}

		array_unshift($data['sites'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		array_unshift($data['sites'], [
			'name'	=> _l('default'),
			'id'	=> ''
		]);

		$data['book_status_id'] = 0;

		$data['book_statuses'] = [
			[
				'name'	=> _l('all'),
				'id'	=> 'all',
			],
			[
				'name'	=> _l('book_not_started'),
				'id'	=> 1,
			],
			[
				'name'	=> _l('book_in_writing'),
				'id'	=> 2,
			],
		];

		$data['lead_status_id'] = 0;

		$data['lead_statuses'] = [
			[
				'name'	=> _l('all'),
				'id'	=> 'all',
			],
			[
				'name'	=> _l('verified'),
				'id'	=> 1,
			],
			[
				'name'	=> _l('not_verified'),
				'id'	=> 2,
			],
		];

		$data['page_counts'] = [
			[
				'name'	=> _l('all'),
				'id'	=> 'all',
			],
			[
				'name'	=> 5,
				'id'	=> 5,
			],
			[
				'name'	=> 10,
				'id'	=> 10,
			],
			[
				'name'	=> 20,
				'id'	=> 20,
			],
			[
				'name'	=> 30,
				'id'	=> 30,
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_lead($archived = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'archived'			=> $archived,
			// 'telecaller_id'		=> $this->session->userdata('user_id'),
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'site_ids' 			=> $this->site_ids,
			//'mode'			=> $telecaller_info['mode'] ?? ''
		];

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		if ($this->input->get('verified') && $this->input->get('verified') != 'all') {
			$filter_data['mobile_verified'] = $this->input->get('verified') == 1
				? (int)$this->input->get('verified')
				: 0;
		}

		if ($this->input->get('book_status') && $this->input->get('book_status') != 'all') {
			if ($this->input->get('book_status') == 2) {
				$filter_data['book_status_writing'] = 1;
			} else {
				$filter_data['book_status_not_writing'] = 1;
			}
		}

		if ($this->input->get('page_count') && $this->input->get('page_count') != 'all') {
			$filter_data['page_count'] = (int)$this->input->get('page_count');
		}

		if ($this->input->get('location') && strtolower($this->input->get('location')) != 'all') {
			$filter_data['location'] = $this->input->get('location');
		}

		if (in_array($this->session->userdata('user_email'), MASTER_TELECALLERS)) {
			unset($filter_data['telecaller_id']);
		}

		$result = $this->lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= $result['total'];

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$state = $this->state_model->get($lead['state_id']);
			$city = $this->city_model->get($lead['city_id']);
			$statuses = $this->lead_model->get_status($lead['id']);

			$json['data'][] = [
				'sn'					=> $key + 1,
				'id'					=> $lead['id'],
				'date_added'			=> $lead['date_added'],
				'site'					=> $lead['site'],
				'name'					=> $lead['name'],
				'parent_name'			=> $lead['parent_name'],
				'grade'					=> $lead['grade'],
				'email'					=> $lead['email'],
				'mobile'				=> $lead['mobile'],
				'program_choice'		=> $lead['course'],
				'location'				=> $lead['location'],
				'state_name'			=> $state['name'] ?? '',
				'city_name'				=> $city['name'] ?? '',
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

		$json['error'] = _l('not_allowed');

		if (!$json && $this->lead_model->get($this->input->post('lead_id')) && $this->telecaller_model->get($this->input->post('telecaller_id'))) {
			$this->lead_model->reassignTelecaller([
				'original_telecaller_id'		=> $this->session->user_id,
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

		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');
		$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('name', _l('student_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('grade', _l('grade'), 'trim|required|numeric|greater_than[0]|less_than[12]');
		$this->form_validation->set_rules('location', _l('country'), 'trim|required|min_length[3]|max_length[128]');
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');

		$this->form_validation->set_rules('programs', _l('programs'), [
			'trim',
			'required',
			'numeric',
			['programs', [$this->validate_model, 'program']]
		]);

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if ($lead_info = $this->lead_model->get($this->input->post('lead_id'))) {
			if ($lead_info['telecaller_id'] != $this->session->userdata('user_id')) {
				$json['error'] = _l('error_unauthorized');
			}
		} else {
			$json['error'] = _l('error_lead_id');
		}

		$json['error'] = _l('not_allowed');

		if (!$json) {
			$this->lead_model->edit($this->input->post('lead_id'), [
				'mobile_verified'	=> 1,
				'name'				=> $this->input->post('name'),
				'parent_name'		=> $this->input->post('parent_name'),
				'grade'				=> $this->input->post('grade'),
				'location'			=> $this->input->post('location'),
				'email'				=> $this->input->post('email'),
				'course_id'			=> (int)$this->input->post('programs'),
			]);

			$json['error'] 		= $this->session->flashdata('error_message');

			$json['success'] 	= _li('Edited Successfully');

			$json['redirect']	= site_url('telecaller/lead');
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
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function email_update() {
		$json = [];

		$json['error'] = _l('error_unknown');

		if (!$json && $this->input->method() == 'post') {
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

		$json['error'] = _l('error_unknown');

		if (!$json &&
			$this->input->post('lead_id') &&
			$this->input->post('amount') &&
			$this->input->post('emi_type') &&
			($lead_info = $this->lead_model->get($this->input->post('lead_id')))
		) {
			$this->alert_model->enrol(
				$lead_info['id'],
				(double)$this->input->post('amount'),
				$this->input->post('emi_type')
			);

			$names = explode(' ', $lead_info['name'], 2);

			$student_id = $this->lead_model->addStudent([
				'first_name'		=> array_shift($names),
				'last_name'			=> array_shift($names),
				'lead_id'			=> $lead_info['id'],
				'parent_name'		=> $lead_info['parent_name'],
				'schedule_id'		=> 0,
				'email'				=> $lead_info['email'],
				'mobile'			=> $lead_info['mobile'],
			]);

			$json['success'] 	= _l('email_and_sms_has_been_sent');
		} else if (
			$this->input->post('enrol_id') &&
			($enrol_info = $this->enrol_model->get($this->input->post('enrol_id')))
		) {
			$this->alert_model->renew($enrol_info['id'], (double)$this->input->post('amount'));

			$json['success'] 	= _l('email_and_sms_has_been_sent');
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

			$json['redirect'] 	= site_url('telecaller/lead/1');
			$json['success'] 	= _l('lead_archived');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
