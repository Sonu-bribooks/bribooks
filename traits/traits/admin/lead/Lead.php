<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Lead {
	public function lead($archived = 0) {
		$data['page_name']		= 'lead/index';
		$data['page_title']		= _l('lead');

		$data['page_title']		= $archived ? _l('archived_lead') : _l('lead');
		$data['text_archived']	= !$archived ? _l('archived') : _l('lead');

		$data['action_lead']	= site_url('admin/lead/' . (1 ^ $archived));

		$data['leads'] 			= [];

		$data['archived'] 		= $archived;

		$data['programs'] = [];

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

	public function edit_lead() {
		$json = [];

		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');
		$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('name', _l('student_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('grade', _l('grade'), 'trim|required|numeric|greater_than[0]|less_than[12]');
		$this->form_validation->set_rules('location', _l('country'), 'trim|required|min_length[3]|max_length[128]');
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');

		// $this->form_validation->set_rules('programs', _l('programs'), [
		// 	'trim',
		// 	'required',
		// 	'numeric',
		// 	['programs', [$this->validate_model, 'program']]
		// ]);

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$this->lead_model->edit($this->input->post('lead_id'), [
				'mobile_verified'	=> 1,
				'name'				=> $this->input->post('name'),
				'parent_name'		=> $this->input->post('parent_name'),
				'grade'				=> $this->input->post('grade'),
				'location'			=> $this->input->post('location'),
				'email'				=> $this->input->post('email'),
				// 'course_id'			=> (int)$this->input->post('programs'),
			]);

			$json['error'] 		= $this->session->flashdata('error_message');

			$json['success'] 	= _li('Edited Successfully');

			$json['redirect']	= site_url('admin/lead');
		}

		output_json($json);
	}

	public function lead_detail() {
		$json = [];

		if ($this->input->post('lead_id')) {
			$json['lead'] = $this->lead_model->get($this->input->post('lead_id'));
			$json['lead']['status'] = _ls($json['lead']['status']);
		} else {
			$json['error'] = _l('error_lead_id');
		}

		output_json($json);
	}

	public function status($param1 = null) {
		$json = [];

		if ($param1 == 'add') {
			if ($this->input->method() == 'post') {
				$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('status', _l('status'), 'trim|required|in_list[' . implode(',', array_keys(LEAD_STATUSES)) . ']');

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

		output_json($json);
	}

	public function lead_status() {
		$json = [];

		if ($this->input->post('lead_id')) {
			$json['statuses'] = $this->lead_model->get_status($this->input->post('lead_id'));
		} else {
			$json['error'] = _l('error_lead_id');
		}

		output_json($json);
	}

	public function ajax_lead($archived = 0) {
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

		$result = $this->lead_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= $result['total'];

		foreach ($result['rows'] ?? [] as $key => $lead) {
			$lead['telecaller_id'] && ($telecaller_info = $this->telecaller_model->get($lead['telecaller_id']));

			$feedbacks = $this->lead_model->get_status($lead['id']);

			if ($lead['student_id']) {
				$books = $this->book_model->get_all([
					'user_id'	=> $lead['student_id'],
				]);

				$published_books = count(array_filter($books['rows'] ?? [], function($item) {
					return $item['status'] == 1;
				}));
			}

			$json['data'][] = [
				'id'					=> $lead['id'],
				'checkbox'				=> $lead['id'],
				'sn'					=> $key + 1,
				'date_added'			=> $lead['date_added'],
				'telecaller'			=> $lead['telecaller_id'] ? ($telecaller_info['first_name'] ?? '') . ' ' . ($telecaller_info['last_name'] ?? '') : '',
				'name'					=> $lead['name'],
				'parent_name'			=> $lead['parent_name'],
				'site'					=> $lead['site'],
				'books'				=> vsprintf(_l('Total :: %s <br> Published:: %s'), [
					$books['total'] ?? 0,
					$published_books ?? 0,
				]),
				'mobile'				=> $lead['mobile'],
				'email'					=> $lead['email'],
				'grade'					=> $lead['grade'] ?? '',
				'section'				=> $lead['section'] ?? '',
				'mode'					=> $lead['mode'],
				'center'				=> $lead['center'],
				'location'				=> $lead['location'],
				'requested_schedule'	=> $lead['schedule'],
				'confirmed_schedule'	=> $lead['confirmed_schedule'],
				'feedback'				=> implode("\n", array_map(function ($feedback) {
					return $feedback['status'];
				}, $feedbacks)),
				'status'				=> _ls($lead['status']) . ' ' . _mv($lead['mobile_verified']),
				'actions'				=> ['id' => $lead['id'], 'course' => $lead['course'], 'email' => $lead['course']],
			];
		}

		output_json($json);
	}

	public function download_lead($type = 'school') {
		$filter_data = [
			'archived'			=> $archived
		];

		$leads = $type == 'school'
			? $this->school_lead_model->get_all($filter_data)['rows']
			: $this->lead_model->get_all($filter_data)['rows'];

		$results = [];

		foreach ($leads ?? [] as $lead => $item) {
			$telecaller_info = [];

			// $user_info = $this->student_model->get($item['telecaller_id']);
			if(!empty($item['telecaller_id']))
				$telecaller_info = $this->telecaller_model->get($item['telecaller_id']);

			$state = $this->state_model->get($item['state_id']);
			$city = $this->city_model->get($item['city_id']);
			$feedback_status = $this->school_lead_model->get_status($item['id']);

			$student = $total_book = $total_published = $total_sold = 0;

			if(!empty($item['site_id'])) {
				$student = $this->student_model->get_all([
					'site_id' => $item['site_id']
				])['total'];

				$total_book = $this->student_model->totalBooksBySite([
					'site_id' => $item['site_id']
				]);

				$total_published = $this->book_model->get_all([
					'site_id'	=> $item['site_id'],
					'status'	=> 1
				])['total'];

				$total_sold = $this->order_model->getTopSoldBooks([
					'site_id'	=> $item['site_id']
				])['total'];
			}

			$event_info = [];
			if(!empty($item['event_id'])) {
				$event_info = $this->event_model->get($item['event_id']);
			}

			if (!empty($item['status'])) {
				$status = 'pending';
			} else {
				$status = 'rejected';
			}

			if ($type === 'school') {
				$results[] = [
					'event_name'			=> !empty($event_info['name']) ? $event_info['name'] : '',
					'name'					=> ucfirst($item['name']),
					'mobile'				=> $item['mobile'],
					'email'					=> $item['email'],
					'grade'					=> $item['grades'],
					'state'					=> $state['name'] ?? '',
					'leaflets'				=> $item['leaflets'],
					'school_head'			=> $item['school_head'],
					'authorized_person'		=> $item['authorized_person'],
					'designation'			=> $item['designation'],
					'city'					=> $city['name'] ?? '',
					'mobile_verified'		=> (($item['mobile_verified'] == '1') ? _l('verified') : _l('not_verified')) . "|" . (($item['school_id'] == '0') ? _l('other') : "inlist"),
					'status'				=> (($item['mobile_verified'] == '1') ? _l('verified') : _l('not_verified')),
					'telecaller'			=> !empty($item['telecaller_id']) ? ($telecaller_info['first_name'] ?? '') . ' ' . ($telecaller_info['last_name'] ?? '') : '',
					'students'				=> $student,
					'total_book'			=> $total_book,
					'total_publish_book'	=> $total_published,
					'total_sold'			=> $total_sold,
					'date_added'			=> $item['date_added'],
					'utm_source'			=> $item['utm_source'],
					'utm_medium'			=> $item['utm_medium'],
					'utm_campaign'			=> $item['utm_campaign'],
					'feedback'				=> implode(",", array_map(function ($status) {
						return json_encode(['status' => $status['status'], 'comment' => $status['comment'], 'date' => $status['date_added']]);
					}, $feedback_status))

				];
			} else {
				$site_info = $this->site_model->get($item['site_id']);
				$feedback_status = $this->school_lead_model->get_status($item['site_id']);

				$results[] = [
					'name'					=> ucfirst($item['name']),
					'mobile'				=> $item['mobile'],
					'email'					=> $item['email'],
					'grade'					=> $item['grade'],
					'section'				=> $item['section'],
					'location'				=> $item['location'],
					'mobile_verified'		=> $item['mobile_verified'] ? _li('verified') : _li('not_verified'),
					'telecaller_id'        	=> $user_info['name'],
					'date_added'			=> $item['date_added'],
					'utm_source'			=> $item['utm_source'],
					'utm_medium'			=> $item['utm_medium'],
					'utm_campaign'			=> $item['utm_campaign'],
					'feedback'				=> implode(",", array_map(function ($status) {
						return json_encode(['status' => $status['status'], 'comment' => $status['comment'], 'date' => $status['date_added']]);
					}, $feedback_status))
				];
			}
		}

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="leads' . date('Y-m-d') . '.csv"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	public function assign_telecaller() {
		$json = [];

		if ($this->input->post('telecaller_id') && $this->input->post('selected')) {
			$explode = is_array($this->input->post('selected')) ? $this->input->post('selected') : explode(',', $this->input->post('selected'));

			foreach ($explode as $lead_id) {
				$this->lead_model->edit($lead_id, [
					'telecaller_id'	=> (int)$this->input->post('telecaller_id')
				]);

				$json['success'] = _l('reassigned_successfully');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		output_json($json);
	}

	public function lead_archived() {
		if ($this->lead_model->get($this->input->post('lead_id'))) {
			$this->lead_model->archived([
				'lead_id'		=> $this->input->post('lead_id'),
				'archived'		=> 1,
			]);

			$json['success'] = _l('archived_successfully');
			$json['redirect'] = site_url('admin/lead');
		} else {
			$json['error'] = _l('lead_not_found');
		}

		output_json($json);
	}

	public function bulk_archive() {
		$json = [];

		if ($this->input->post('selected')) {
			$explode = is_array($this->input->post('selected')) ?
				$this->input->post('selected') :
				explode(',', $this->input->post('selected'));

			foreach ($explode as $lead_id) {
				$this->lead_model->archived([
					'lead_id'		=> $lead_id,
					'archived'		=> 1,
				]);

				$json['success'] = _l('archived_successfully');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		output_json($json);
	}

	public function send_payment_link() {
		$json = [];

		if ($this->input->post('lead_id') && $this->input->post('amount') && $this->input->post('emi_type') && ($lead_info = $this->lead_model->get($this->input->post('lead_id')))) {
			$this->alert_model->enrol($lead_info['id'], (float)$this->input->post('amount'), $this->input->post('emi_type'));

			$json['success'] 	= _l('email_and_sms_has_been_sent');
		} else if ($this->input->post('enrol_id') && ($enrol_info = $this->enrol_model->get($this->input->post('enrol_id')))) {
			$this->alert_model->renew($enrol_info['id'], (float)$this->input->post('amount'));

			$json['success'] 	= _l('email_and_sms_has_been_sent');
		} else {
			$json['error'] 		= _l('error_unknown');
		}

		output_json($json);
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

				$json['error'] = $this->session->flashdata('error_message');
				$json['success'] = $this->session->flashdata('flash_message');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		output_json($json);
	}
}
