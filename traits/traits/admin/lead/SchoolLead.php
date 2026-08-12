<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolLead {
	public function school_lead($archived = 0) {
		$data['page_name']		= 'lead/school';
		$data['page_title']		= _l('school_lead');

		$data['page_title']		= $archived ? _l('archived_lead') : _l('school_lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('school_lead');

		// $data['action_lead']	= site_url('admin/school_lead/' . (1 ^ $archived));

		$data['leads'] 			= [];

		$data['archived'] 		= $archived;

		$data['telecallers'] = array_map(function($item) {
			return [
				'id'	=> $item['id'],
				'name'	=> $item['first_name'] . ' ' . $item['last_name'],
			];
		}, $this->telecaller_model->get_all()['rows'] ?? []);

		array_unshift($data['telecallers'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$data['countries'] = [];
		$result = $this->country_model->get_all([
			'sort'		=> 'country.id',
			'order'		=> 'ASC'
		])['rows'] ?? [];

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

		$data['events'] = [];
		$result = $this->event_model->get_all();

		foreach ($result['rows'] ?? [] as $result) {
			$data['events'][] = [
				'id'			=> $result['id'],
				'name'			=> $result['name'],
			];
		}

		array_unshift($data['events'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$data['sites'] = [];

		array_unshift($data['sites'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$this->load->view('backend/index', $data);
	}

	public function fresh_lead($archived = 0) {
		$data['page_name']		= 'lead/fresh_lead';
		$data['page_title']		= _l('fresh_lead');

		$data['page_title']		= $archived ? _l('archived_lead') : _l('school_lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('school_lead');

		// $data['action_lead']	= site_url('admin/school_lead/' . (1 ^ $archived));

		$data['leads'] 			= [];

		$data['archived'] 		= $archived;

		$data['telecallers'] = array_map(function($item) {
			return [
				'id'	=> $item['id'],
				'name'	=> $item['first_name'] . ' ' . $item['last_name'],
			];
		}, $this->telecaller_model->get_all()['rows'] ?? []);

		array_unshift($data['telecallers'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$data['countries'] = [];
		$result = $this->country_model->get_all([
			'sort'		=> 'country.id',
			'order'		=> 'ASC'
		])['rows'] ?? [];

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

		$data['events'] = [];
		$result = $this->event_model->get_all();

		foreach ($result['rows'] ?? [] as $result) {
			$data['events'][] = [
				'id'			=> $result['id'],
				'name'			=> $result['name'],
			];
		}

		array_unshift($data['events'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$data['sites'] = [];

		array_unshift($data['sites'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$this->load->view('backend/index', $data);
	}

	public function assign_leads($archived = 0) {
		$data['page_name']		= 'lead/assign_leads';
		$data['page_title']		= _l('assign_leads');

		$data['page_title']		= $archived ? _l('archived_lead') : _l('school_lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('school_lead');

		// $data['action_lead']	= site_url('admin/school_lead/' . (1 ^ $archived));

		$data['leads'] 			= [];

		$data['archived'] 		= $archived;

		$data['telecallers'] = array_map(function($item) {
			return [
				'id'	=> $item['id'],
				'name'	=> $item['first_name'] . ' ' . $item['last_name'],
			];
		}, $this->telecaller_model->get_all()['rows'] ?? []);

		array_unshift($data['telecallers'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$data['countries'] = [];
		$result = $this->country_model->get_all([
			'sort'		=> 'country.id',
			'order'		=> 'ASC'
		])['rows'] ?? [];

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

		$data['events'] = [];
		$result = $this->event_model->get_all();

		foreach ($result['rows'] ?? [] as $result) {
			$data['events'][] = [
				'id'			=> $result['id'],
				'name'			=> $result['name'],
			];
		}

		array_unshift($data['events'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$data['sites'] = [];

		array_unshift($data['sites'], [
			'id'			=> 0,
			'name'			=> _l('all'),
		]);

		$this->load->view('backend/index', $data);
	}

	public function ajax_assign_leads($archived = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'archived'			=> $archived,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'telecaller_id'		=> $this->session->userdata('user_id')
		];

		if (in_array($this->session->userdata('role_id'), [1,5,6])) {
			unset($filter_data['telecaller_id']);
		}

		if ($this->input->get('event_id') && $this->input->get('event_id') != 'all') {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		if (!empty($this->input->get('site_id'))) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		if ($this->input->get('telecaller_id') && $this->input->get('telecaller_id') != 'all') {
			$filter_data['telecaller_id'] = (int)$this->input->get('telecaller_id');
		}

		if ($this->input->get('location') && strtolower($this->input->get('location')) != 'all') {
			$filter_data['location'] = $this->input->get('location');
		}

		$result = $this->school_lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= $result['total'];

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$lead['telecaller_id'] && ($telecaller_info = $this->telecaller_model->get($lead['telecaller_id']));

			$statuses = $this->school_lead_model->get_status($lead['id']);
			$city = $this->city_model->get($lead['city_id']);
			$state = $this->state_model->get($lead['state_id']);
			$site_info = $this->site_model->get($lead['site_id']);

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
				'country'				=> $lead['country'],
				'city'					=> (!empty($city['name']) ? $city['name'] : "") . '-' . (!empty($state['name']) ? $state['name'] : ""),
				'type'					=> $lead['type'],
				'site'					=> $site_info ? $site_info['name'] : "",
				'mobile'				=> $lead['mobile'],
				'authorized_person'		=> $lead['authorized_person'],
				'email'					=> $lead['email'],
				'total_register'		=> $student,
				'total_books'			=> $total_books,
				'total_published'		=> $total_published,
				'total_sold'			=> $total_sold,
				'feedback'				=> implode("\n", array_map(function ($status) {
					return $status['status'];
				}, $statuses)),
				'status'				=>  _ls($lead['status']) . ' '. _mv($lead['mobile_verified']).' '.(($lead['school_id'] == '0')?'<span class="badge badge-warning">Others</span>':""),
				'actions'				=> ['id' => $lead['id'], 'email' => $lead['email']],
			];
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_fresh_school_lead($archived = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'telecaller_id'			=> $archived,
			'telecaller_id'		=> '0',
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('event_id') && $this->input->get('event_id') != 'all') {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		if (!empty($this->input->get('site_id'))) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		if ($this->input->get('telecaller_id') && $this->input->get('telecaller_id') != 'all') {
			$filter_data['telecaller_id'] = (int)$this->input->get('telecaller_id');
		}

		if ($this->input->get('location') && strtolower($this->input->get('location')) != 'all') {
			$filter_data['location'] = $this->input->get('location');
		}

		$result = $this->school_lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= $result['total'];

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$lead['telecaller_id'] && ($telecaller_info = $this->telecaller_model->get($lead['telecaller_id']));

			$statuses = $this->school_lead_model->get_status($lead['id']);
			$city = $this->city_model->get($lead['city_id']);
			$state = $this->state_model->get($lead['state_id']);
			$site_info = $this->site_model->get($lead['site_id']);

			$json['data'][] = [
				'id'					=> $lead['id'],
				'checkbox'				=> $lead['id'],
				'sn'					=> $key + 1,
				'date_added'			=> $lead['date_added'],
				'telecaller'			=> $lead['telecaller_id'] ? ($telecaller_info['first_name'] ?? '') . ' ' . ($telecaller_info['last_name'] ?? '') : '',
				'name'					=> $lead['name'],
				'country'				=> $lead['country'],
				'city'					=> (!empty($city['name']) ? $city['name'] : "") . '-' . (!empty($state['name']) ? $state['name'] : ""),
				'type'					=> $lead['type'],
				'site'					=> $site_info ? $site_info['name'] : "",
				'mobile'				=> $lead['mobile'],
				'authorized_person'		=> $lead['authorized_person'],
				'email'					=> $lead['email'],
				'feedback'				=> implode("\n", array_map(function ($status) {
					return $status['status'];
				}, $statuses)),
				'status'				=> _ls($lead['status']) . ' ' . _mv($lead['email_verified']),
				'actions'				=> ['id' => $lead['id'], 'email' => $lead['email']],
			];
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($json));
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
			['lead', [$this->validate_model, 'schoolLead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {

			if (empty($message = self::validateSchoolLead($this->input->post()))) {
				$this->school_lead_model->edit($this->input->post('school_lead_id'), [
					'name'				=> $this->input->post('name'),
					'authorized_person'	=> $this->input->post('authorized_person'),
					'no_of_students'	=> $this->input->post('no_of_students'),
					'email'				=> $this->input->post('email'),
					'mobile'			=> $this->input->post('mobile'),
				]);

				$json['error'] 		= $this->session->flashdata('error_message');

				$json['success'] 	= _li('Edited Successfully');

				$json['redirect']	= site_url('admin/school_lead');

			} else {
				$json['error'] 		= $message;
			}
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
		// $json['error'] = _l('error_school_lead_id');


		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function school_status($param1 = null) {
		$json = [];

		if ($param1 == 'add') {
			if ($this->input->method() == 'post') {
				$this->form_validation->set_rules('school_lead_id', _l('school_lead_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('status', _l('status'), 'trim|required|in_list[' . implode(',', array_keys(LEAD_STATUSES)) . ']');

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
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('event_id') && $this->input->get('event_id') != 'all') {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		if (!empty($this->input->get('site_id'))) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		if ($this->input->get('telecaller_id') && $this->input->get('telecaller_id') != 'all') {
			$filter_data['telecaller_id'] = (int)$this->input->get('telecaller_id');
		}

		if ($this->input->get('location') && strtolower($this->input->get('location')) != 'all') {
			$filter_data['location'] = $this->input->get('location');
		}

		$result = $this->school_lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= $result['total'];

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$lead['telecaller_id'] && ($telecaller_info = $this->telecaller_model->get($lead['telecaller_id']));
			$this->load->model('user/Student_model', 'student_model');
			$statuses = $this->school_lead_model->get_status($lead['id']);
			$city = $this->city_model->get($lead['city_id']);
			$state = $this->state_model->get($lead['state_id']);
			$site_info = $this->site_model->get($lead['site_id']);
			$student = $this->student_model->get_all([
				'site_id' => $lead['site_id']
			])['total'];
			$totalBook = $this->student_model->totalBooksBySite([
				'site_id' => $lead['site_id']
			]);

			$is_mobile_verified_btn_text = 'Mobile';
			$is_email_verified_btn_text = 'Email';
			if(strtolower($lead['country']) !== 'india') {
				$is_mobile_verified_btn_text = 'Email';
				$is_email_verified_btn_text = 'Mobile';
			}

			$json['data'][] = [
				'id'					=> $lead['id'],
				'checkbox'				=> $lead['id'],
				'sn'					=> $key + 1,
				'date_added'			=> $lead['date_added'],
				'telecaller'			=> $lead['telecaller_id'] ? ($telecaller_info['first_name'] ?? '') . ' ' . ($telecaller_info['last_name'] ?? '') : '',
				'name'					=> $lead['name'],
				'country'				=> $lead['country'],
				'city'					=> $city['name'] . '-' . $state['name'] ,
				'leaflets'				=> $lead['leaflets'],
				'type'					=> $lead['type'],
				'site'					=> $site_info ? $site_info['name'] : "",
				'mobile'				=> $lead['mobile'],
				'total_register'		=> $student,
				'total_books'			=> $totalBook,
				'authorized_person'		=> $lead['authorized_person'],
				'email'					=> $lead['email'],
				'feedback'				=> implode("\n", array_map(function ($status) {
					return $status['status'];
				}, $statuses)),
				'status'				=> _lv($lead['verified']) . ' '. _mv($lead['mobile_verified'], $is_mobile_verified_btn_text) . ' '. _mv($lead['email_verified'], $is_email_verified_btn_text).' '.(($lead['school_id'] == '0')?'<span class="badge badge-warning">Others</span>':""),
				'actions'				=> ['id' => $lead['id'], 'email' => $lead['email']],
			];

		}

		$json['approved_lead'] 	= $this->school_lead_model->get_all([
			'archived'			=> $archived,
			'mobile_verified'	=> 1,
		])['total'];

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function approved_lead($param1 = '') {
		// $this->load->model('school/SchoolInput_model', 'schoolinput_model');
		// $statuses = $this->school_lead_model->get($param1);
		// if ($statuses) {
		// 	if ($statuses['site_id'] == '0') {
		// 		$this->schoolinput_model->add([
		// 			'name' => $statuses['name'],
		// 			'state_id' => $statuses['state_id'],
		// 			'city_id' => $statuses['city_id'],
		// 			'date_added' => $statuses['date_added'],
		// 			'date_modified' => $statuses['date_modified'],
		// 		]);
		// 		self::_addSite($statuses);
		// 		$this->alert_model->schoolLeadRegistration($statuses['id']);
		// 		$this->alert_model->schoolLeadShare($statuses['id']);
		// 	}
		// }

		if ($lead_info = $this->school_lead_model->get($param1)) {

			if ($lead_info['verified'] == 1) {
				$this->session->flashdata('error_message',_l('lead_is_already_veridfied'));
				redirect('admin/school_lead');
			}

			if (!empty($message = self::validateSchoolLead($this->input->post()))) {
				$this->session->flashdata('error_message',_l('duplicacy_in_lead_please_check'));
				redirect('admin/school_lead');
			}

			$site_id 	= 0;
			$school_id 	= 0;

			if (!empty($lead_info['site_id']) && !empty($lead_info['school_id'])) {
				$site_id 	= self::_updateSite($lead_info);
				$school_id 	= self::_updateSchool($lead_info);
			} elseif (empty($lead_info['site_id']) && !empty($lead_info['school_id'])) {
				$site_id = self::_addSite($lead_info);

				if (!empty($site_id)) {
					$lead_info['site_id'] = $site_id;
					$school_id = self::_updateSchool($lead_info);
				}
			} elseif (!empty($lead_info['site_id']) && empty($lead_info['school_id'])) {
				$site_id 	= self::_updateSite($lead_info);

				if (!empty($site_id)) {
					$lead_info['site_id'] = $site_id;
					$school_id 	= self::_addSchool($lead_info);
				}
			} elseif (empty($lead_info['site_id']) && empty($lead_info['school_id'])) {
				$site_id = self::_addSite($lead_info);

				if (!empty($site_id)) {
					$lead_info['site_id'] = $site_id;
					$school_id = self::_addSchool($lead_info);
				}
			}

			$this->school_lead_model->edit($param1, [
				'site_id' 	=> $site_id,
				'school_id' => $school_id,
				'verified' 	=> 1
			]);

			$this->cron_model->add([
				'code'			=> 'sendSchoolLeadVerifyMail_' . $lead_info['id'],
				'action'		=> 'alert_model->sendSchoolLeadVerifyMail',
				'data'			=> [$lead_info['school_id']],
				'alert_date'	=> date('Y-m-d H:i:s'),
			]);

			$this->session->flashdata('flash_message',_l('lead_approved_successfully'));
		} else {
			$this->session->flashdata('error_message',_l('lead_not_found'));
		}

		redirect('admin/school_lead');
	}

	public function reject_lead($param1 = '') {
		$this->load->model('school/SchoolInput_model', 'schoolinput_model');
		$statuses = $this->school_lead_model->get($param1);
		if ($statuses) {
			$this->school_lead_model->edit($param1,['status'=>'5']);
			$this->alert_model->rejectLead($statuses['id']);
		}
		$this->session->flashdata('flash_message',_l('lead_rejected_successfully'));
		redirect('admin/school_lead');

	}

	private function _addSite($lead_info = []) {
		$site_info = [];

		if (empty($country_site_info = $this->site_model->getSiteByName($lead_info['country']))) {
			$country_site_info = $this->site_model->getSiteByName('India');
		}

		if (!empty($country_site_info)) {
			$site_info = $this->site_model->get($country_site_info['id']);
		}

		$site_id = $this->site_model->addSite([
			'license_total'			=> 500,
			'name'					=> $lead_info['name'],
			'site_type'				=> $lead_info['site_type'],
			'image' 				=> '',
			'parent_id'				=> $site_info['id'] ?? 0,
			'payment_gateway'		=> $site_info['payment_gateway'] ?? 'razorpay',
			'sms_gateway'			=> $site_info['sms_gateway'] ?? 'textlocal',
			'email_alert'			=> $site_info['email_alert'] ?? '',
			'address'				=> '',
			'landmark'				=> '',
			'pincode'				=> '',
			'mobile_length' 	 	=> $site_info['mobile_length'],
			'country_code' 		 	=> $site_info['country_code'],
			'state_id'				=> $lead_info['state_id'],
			'city_id'				=> $lead_info['city_id'],
			'site_code' 			=> $site_info['site_code'] . '-lead-' . uniqid(),
			'discount_code' 	  	=> $site_info['discount_code'] ?? '',
			'discount_percentage' 	=> $site_info['discount_percentage'],
			'timezone' 			  	=> $site_info['timezone'],
			'currency_code'			=> $site_info['currency_code'] ?? '',
			'base_price' 		  	=> $site_info['base_price'] ?? '',
			'ebook_price' 		  	=> $site_info['ebook_price'] ?? '',
			'price_per_page' 	  	=> $site_info['price_per_page'] ?? '',
			'free_page_limit' 	  	=> $site_info['free_page_limit'] ?? '',
			'hard_cover_price' 	  	=> $site_info['hard_cover_price'] ?? '',
			'paperback_price' 	  	=> $site_info['paperback_price'] ?? '',
			'tax' 				  	=> $site_info['tax'] ?? 0,
			'tax_text' 			  	=> $site_info['tax_text'] ?? '',
			'owner_email'			=> $lead_info['email'],
			'owner_mobile'			=> $lead_info['mobile'],
			'authorized_person'		=> $lead_info['authorized_person'],
			'owner_name'			=> $lead_info['school_head'] ?? '',
			'can_add_site'			=> 0,
			'status'				=> 1,
			'verified'				=> !empty($lead_info['mobile_verified']) ? trim($lead_info['mobile_verified']) : trim($lead_info['email_verified']),
		]);

		if (!empty($site_id)) {

			$this->site_model->editById($site_id, [
				'site_code' => get_site_code_slug(trim($lead_info['name'])) . "-" . $site_id
			]);
		}

		return $site_id;
	}

	private function _addSchool($lead_info = []) {
		if (!empty($lead_info)) {

			$site_info = [];

			if (empty($country_site_info = $this->site_model->getSiteByName($lead_info['country']))) {
				$country_site_info = $this->site_model->getSiteByName('India');
			}

			if (!empty($country_site_info)) {
				$site_info = $this->site_model->get($country_site_info['id']);
			}

			$insert_school_data = [
				'parent_id' 		  			=> $lead_info['parent_id'] ?? 0,
				'site_id' 		  				=> $lead_info['site_id'] ?? 0,
				'name' 				  			=> trim($lead_info['name']),
				'site_code' 		  			=> $site_info['site_code'] . "-lead-" . uniqid(),
				'site_type' 		  			=> $lead_info['site_type'] ?? 1,
				'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
				'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
				'timezone' 			  			=> $site_info['timezone'] ?? '',
				'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
				'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
				'email_alert' 		  			=> $site_info['email_alert'] ?? '',
				'address' 			  			=> $lead_info['address'] ?? '',
				'landmark' 			  			=> $lead_info['landmark'] ?? '',
				'pincode' 			  			=> $lead_info['zipcode'] ?? '',
				'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
				'country_code' 		  			=> $site_info['country_code'] ?? '',
				'currency_code' 	  			=> $site_info['currency_code'] ?? '',
				'state_id' 			  			=> $lead_info['state_id'] ?? 0,
				'city_id' 			  			=> $lead_info['city_id'] ?? 0,
				'base_price' 		  			=> $site_info['base_price'] ?? 0,
				'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
				'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
				'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
				'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
				'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
				'tax' 				  			=> $site_info['tax'] ?? '',
				'tax_text' 			  			=> $site_info['tax_text'] ?? '',
				'owner_name' 	      			=> !empty($lead_info['school_head']) ? trim($lead_info['school_head']) : '',
				'authorized_person'   			=> !empty($lead_info['authorized_person']) ? trim($lead_info['authorized_person']) : '',
				'owner_email' 		  			=> $lead_info['email'] ?? '',
				'owner_mobile' 	      			=> $lead_info['mobile'] ?? '',
				'alternate_authorized_person'   => $lead_info['alternate_authorized_person'] ?? '',
				'alternate_owner_email' 		=> $lead_info['alternate_email'] ?? '',
				'alternate_owner_mobile' 	    => $lead_info['alternate_mobile'] ?? '',
				'status' 			  			=> 1,
				'verified' 			  			=> !empty($lead_info['mobile_verified']) ? trim($lead_info['mobile_verified']) : trim($lead_info['email_verified']),
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
			];

			$school_id = $this->school_model->add($insert_school_data);

			if (!empty($school_id)) {
				$this->school_model->edit($school_id, [
					'site_code' => get_site_code_slug(trim($lead_info['name'])) . "-" . $school_id
				]);
			}

			return $school_id;
		}
	}

	private function _updateSite($lead_info) {
		$update_site_data = [
			'name' 				  			=> trim($lead_info['name']),
			'state_id' 			  			=> $lead_info['state_id'] ?? 0,
			'city_id' 			  			=> $lead_info['city_id'] ?? 0,
			'owner_name' 	      			=> !empty($lead_info['school_head']) ? trim($lead_info['school_head']) : '',
			'authorized_person'   			=> !empty($lead_info['authorized_person']) ? trim($lead_info['authorized_person']) : '',
			'owner_email' 		  			=> $lead_info['email'] ?? '',
			'owner_mobile' 	      			=> $lead_info['mobile'] ?? '',
			'verified' 			  			=> !empty($lead_info['mobile_verified']) ? trim($lead_info['mobile_verified']) : trim($lead_info['email_verified']),
		];

		$this->site_model->editById($lead_info['site_id'], $update_site_data);

		return $lead_info['site_id'];
	}

	private function _updateSchool($lead_info) {
		$update_school_data = [
			'site_id' 				  		=> $lead_info['site_id'] ?? 0,
			'name' 				  			=> trim($lead_info['name']),
			'state_id' 			  			=> $lead_info['state_id'] ?? 0,
			'city_id' 			  			=> $lead_info['city_id'] ?? 0,
			'owner_name' 	      			=> !empty($lead_info['school_head']) ? trim($lead_info['school_head']) : '',
			'authorized_person'   			=> !empty($lead_info['authorized_person']) ? trim($lead_info['authorized_person']) : '',
			'owner_email' 		  			=> $lead_info['email'] ?? '',
			'owner_mobile' 	      			=> $lead_info['mobile'] ?? '',
			'verified' 			  			=> !empty($lead_info['mobile_verified']) ? trim($lead_info['mobile_verified']) : trim($lead_info['email_verified']),
		];

		$this->school_model->edit($lead_info['school_id'], $update_school_data);

		return $lead_info['school_id'];
	}

	public function assign_school_lead_telecaller() {
		$json = [];

		if ($this->input->post('telecaller_id') && $this->input->post('selected')) {
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
				'school_lead_id' => $this->input->post('school_lead_id'),
				'archived'		=> 1,
			]);

			$json['success'] = _l('archived_successfully');
			$json['redirect'] = site_url('admin/school_lead');
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
					'school_lead_id' => $school_lead_id,
					'archived'		=> 1,
				]);

				$json['success'] = _l('archived_successfully');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	private function validateSchoolLead($data = []) {
		$school_lead_info = $this->school_lead_model->get($data['school_lead_id']);

		$message = '';

		if (!empty($school_lead_info) && !empty($school_lead_info['school_id']) && !empty($school_info = $this->school_model->get_all([
			'school_id'             => $school_lead_info['school_id'],
			'verified' 				=> 1,
		])['rows'][0] ?? '')) {
			return 'This school is already verified';
		}

		if (!empty($school_lead_info) && !empty($school_lead_info['site_id']) && !empty($school_info = $this->site_model->get_all([
			'site_id'             => $school_lead_info['site_id'],
			'verified' 				=> 1,
		])['rows'][0] ?? '')) {
			return 'This school is already verified';
		}

		if (!empty($data['email']) && !empty($user_email_info = $this->user_model->get_all([
			'email'                 => $data['email'],
		])['rows'][0] ?? '')) {
			$message .=  _li('This_Email_is_already_registered_with_BriBooks_For_User_:_') . $user_email_info['id'] . '<br><br>';
		}

		if (!empty($data['mobile']) && !empty($user_mobile_info = $this->user_model->get_all([
			'mobile'                => $data['mobile'],
		])['rows'][0] ?? '')) {
			$message .= _li('This_Mobile_is_already_registered_with_BriBooks_For_User_:_') . $user_mobile_info['id'] . '<br><br>';
		}

		if (!empty($data['email']) && !empty($email_info = $this->school_model->get_all([
		    'owner_email'   => $data['email'],
		    'not_school_id' => $school_lead_info['school_id'] ?? ''
		])['rows'][0] ?? '')) {
		    $message .= _li('This_Email_is_already_registered_with_BriBooks_For_School_:_') . $email_info['id'] . '<br><br>';
		}

		if (!empty($data['email']) && !empty($alternate_email_info = $this->school_model->get_all([
		    'alternate_owner_email'     => $data['email'],
		    'not_school_id'             => $school_lead_info['school_id'] ?? ''
		])['rows'][0] ?? '')) {
		    $message .= _li('This_Email_is_already_registered_with_BriBooks_For_School_:_') . $alternate_email_info['id'] . '<br><br>';
		}

		if (!empty($data['mobile']) && !empty($mobile_info = $this->school_model->get_all([
		    'owner_mobile'      => $data['mobile'],
		    'not_school_id'     => $school_lead_info['school_id'] ?? ''
		])['rows'][0] ?? '')) {
		    $message .= _li('This_Mobile_Number_is_already_registered_with_BriBooks_For_School_:_') . $mobile_info['id'] . '<br><br>';
		}

		if (!empty($data['mobile']) && !empty($alternate_mobile_info = $this->school_model->get_all([
		    'alternate_owner_mobile'    => $data['mobile'],
		    'not_school_id'             => $school_lead_info['school_id'] ?? ''
		])['rows'][0] ?? '')) {
		    $message .= _li('This_Mobile_Number_is_already_registered_with_BriBooks_For_School_:_') . $alternate_mobile_info['id'] . '<br><br>';
		}

		$site_email_filter = [
			'owner_email'      => $data['email']
		];

		$site_mobile_filter = [
			'owner_mobile'     => $data['mobile'],
		];

		if (!empty($school_lead_info['site_id'])) {
			$site_email_filter['site_id_ne'] 	= $school_lead_info['site_id'];
			$site_mobile_filter['site_id_ne'] 	= $school_lead_info['site_id'];
		}

		if (!empty($data['email']) && !empty($site_info = $this->site_model->get_all($site_email_filter)['rows'][0] ?? '')) {
			$message .= _li('This_Email_is_already_registered_with_BriBooks_For_Site_:_') . $site_info['id'] . '<br><br>';
		}

		if (!empty($data['mobile']) && !empty($site_mobile_info = $this->site_model->get_all($site_mobile_filter)['rows'][0] ?? '')) {
			$message .= _li('This_Mobile_is_already_registered_with_BriBooks_For_Site_:_') . $site_info['id'] . '<br><br>';
		}

		return $message;
	}
}
