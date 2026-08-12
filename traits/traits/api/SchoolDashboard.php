<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait SchoolDashboard {
	public function getSchoolEvents() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$active = [];

			$this->json['events'] = array_map(function($item) use(&$active) {
				$event_info = $this->event_model->get($item['event_id']);

				$is_active = strtotime($event_info['start_date']) < time() && strtotime($event_info['end_date']) > time();

				$data = [
					'id'						=> $event_info['id'],
					'name'						=> $event_info['name'],
					'start_date'				=> $event_info['start_date'],
					'end_date'					=> $event_info['end_date'],
					'book_writing_end_date'		=> $event_info['book_writing_end_date'],
					'student_reg_end_date'		=> $event_info['student_reg_end_date'],
					'active'					=> $is_active,
				];

				if ($is_active) {
					$active = $data;
				}

				return $data;
			}, $this->event_site_model->get_all([
				'site_id'	=> (int)$this->input->post('site_id')
			])['rows'] ?? []);

			CI_Events::trigger('access_log', [
				'module'	=> 'school_dashboard'
			]);

			$this->json['active'] = $active;
		}
	}

	public function getSchoolData() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$user_id = (int)$this->session->userdata('user_id');

			if (
				$user_id &&
				$user_info = $this->db->get_where('users', [
					'id'		=> $user_id,
					'role_id'	=> 9,
					'status'	=> 1,
					'_deleted'	=> 0,
				])->row_array()
			) {
				$this->load->library('Common_lib', 'common_lib');

				$this->json['school_data'] = $this->common_lib->getGradeWiseData($user_id, $this->input->post('event_id'));
			} else {
				$this->json['error'] = _l('session_expired');
				$this->json['unauthorized'] = true;
			}
		}
	}

	public function getSchoolReport() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$user_id = (int)$this->session->userdata('user_id');

			if (
				$user_id &&
				$user_info = $this->db->get_where('users', [
					'id'		=> $user_id,
					'role_id'	=> 9,
					'status'	=> 1,
					'_deleted'	=> 0,
				])->row_array()
			) {
				$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

				$this->json['school_data'] = $this->schooldashboard_lib->getGradeWiseData($user_id, $this->input->post('event_id'));
			} else {
				$this->json['error'] = _l('session_expired');
				$this->json['unauthorized'] = true;
			}
		}
	}

	public function getSchoolReportByCode() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('code', _l('code'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]',
		]);
		self::_runFormValidation();

		if (!$this->json) {
			if (empty($invite_info = $this->event_school_invite_code_model->get_all([
				'event_id'	  	=> $this->input->post('event_id'),
				'site_id'	 	=> $this->input->post('site_id'),
				'code'		  	=> $this->input->post('code'),
			])['rows'][0] ?? [])) {
				return $this->json['error'] = _li('invalid_code');
			}

			if (
				!empty($user_info = $this->db->get_where('users', [
					'site_id'	=> $invite_info['site_id'],
					'role_id'	=> 9,
					'status'	=> 1,
					'_deleted'	=> 0,
				])->row_array())
			) {
				$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

				$this->json['school_data'] = $this->schooldashboard_lib->getGradeWiseData($user_info['id'], $this->input->post('event_id'));
			} else {
				$this->json['error'] = _l('session_expired');
				$this->json['unauthorized'] = true;
			}
		}
	}

	private function _formatSchool($user_id = 0) {
		if (
			$user_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 9,
				'status'	=> 1,
				'_deleted'	=> 0,
				// 'verified'	=> 1,
			])->row_array()
		) {
			$user = [
				'user_id'		=> $user_info['id'],
				'user_email'	=> $user_info['email'],
				'user_mobile'	=> $user_info['mobile'],
				'user_role_id'	=> $user_info['role_id'],
				'user_role'		=> get_user_role_by_id($user_info['role_id']),
				'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'user_site'		=> $user_info['site_id'] ?? 0,
				'user_site_id'	=> $user_info['site_id'] ?? 0,
			];

			$this->session->set_userdata($user);

			$school_info = $this->site_model->get($user_info['site_id']);

			$country_id 	= 0;
			$country_code 	= false;
			$user_events 	= [];

			if (!empty($school_info['state_id'])) {
				$state_info = $this->state_model->get($school_info['state_id']);
				$country_id = $state_info['country_id'];

				$this->load->model('localisation/Country_model', 'country_model');
				$country_info = $this->country_model->get($country_id);
				$country_code = $country_info['country_code'] ?? '';
			}

			if (empty($country_code) && !empty($school_info['country_code'])) {
				$country_code = $school_info['country_code'];
			}

			if (!empty($country_code)) {
				$filter_data 			= [];
				$filter_data['sort'] 	= 'event_site.id';
				$filter_data['order'] 	= 'DESC';
				$filter_data['site_id'] = $user_info['site_id'];

				$event_site_results 	= $this->event_site_model->get_all($filter_data);

				foreach ($event_site_results['rows'] ?? [] as $event_site_info) {
					$event_info = $this->event_model->get($event_site_info['event_id']);

					if (!empty($event_info)) {
						$user_event = [];
						$user_event['event_id'] 		= $event_info['id'];
						$user_event['event_name'] 		= $event_info['name'];
						$user_event['parent_site_id'] 	= $event_info['parent_site_id'];
						$user_event['country_code'] 	= $event_info['country_code'];
						$user_event['start_date'] 		= $event_info['start_date'];
						$user_event['end_date'] 		= $event_info['end_date'];

						$user_events[] = $user_event;
					}
				}
			}

			if (!empty($school_info['city_id'])) {
				$city_info = $this->city_model->get($school_info['city_id']);
			}

			$this->json['user'] = [
				'id' 					=> $user_info['id'],
				'user_email'			=> $user_info['email'],
				'address_id'			=> $user_info['address_id'],
				'user_mobile'			=> $user_info['mobile'],
				'image'					=> $user_info['image'],
				'name'					=> trim($user_info['first_name'] . ' ' . $user_info['last_name']),
				'user_site'				=> $user_info['site_id'] ?? 0,
				'school'				=> $school_info['name'] ?? 0,
				'country_id'			=> $country_id,
				'country_code'			=> $country_code,
				'state_id'				=> $school_info['state_id'] ?? 0,
				'state'					=> $state_info['name'] ?? '',
				'city_id'				=> $school_info['city_id'] ?? 0,
				'city'					=> $city_info['name'] ?? '',
				'role_id'				=> $user_info['role_id'],
				'role'					=> get_user_role_by_id($user_info['role_id']),
				'site_code'				=> $school_info['site_code'] ?? '',
				'site_type'				=> $school_info['site_type'] ?? '1',
				'contact_person_name'	=> $school_info['authorized_person'] ?? '',
				'verification_code'		=> $user_info['verification_code'] ?? '',
				'events'				=> $user_events,
				'is_school'				=> !empty($school_info) ? 1 : 0,
			];
		}
	}

	public function sendEmailReport() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (
			!$this->json &&
			!empty($user_id = $this->session->userdata('user_id')) &&
			!empty($event_id = $this->input->post('event_id')) &&
			$user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 9,
				'status'	=> 1,
				'_deleted'	=> 0,
			])->row_array()
		) {
			$this->load->model('common/Cron_model', 'cron_model');
			$this->cron_model->add([
				'code'			=> 'sendEmailReportCron_' . $event_id . '_' . $user_id,
				'action'		=> 'alert_model->sendEmailReportCron',
				'data'			=> [$event_id, $user_id],
				'site_id'		=> 1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
			]);
		}
	}

	public function downloadReport($event_id = 0) {
		if ($event_info = $this->event_model->get($event_id)) {
			self::_getReport($event_id, true, $this->session->userdata('user_id'));
		}
	}

	public function downloadSchoolReport($event_id = 0, $user_id = 0, $code = '') {
		$user_filter = [
			'user_id' => $user_id
		];

		if (!empty($code)) {
			$user_filter['code'] = $code;
		}

		$user_info = $this->user_model->get_all($user_filter)['rows'][0] ?? '';

		if (!empty($event_info = $this->event_model->get($event_id)) && !empty($user_info)) {
			self::_getSchoolReport($event_info['id'], true, $user_info['id']);
		}
	}

	public function inviteTeacher() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|min_length[3]|max_length[255]|valid_email');
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[6]|max_length[20]');
		$this->form_validation->set_rules('grade', _l('grade'), [
			'trim',
			'required',
			'numeric',
			['grade', [$this->validate_model, 'grade']]
		]);
		$this->form_validation->set_rules('section', _l('section'), [
			'trim',
			'required',
			['section', [$this->validate_model, 'section']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($info = $this->db->get_where('users', [
				'email'	=> $this->input->post('email')
			])->row_array())) {
				$this->json['error'] = 'This email is already registered with BriBooks';
				return;
			}

			if (!empty($this->teacher_model->get_all([
				'site_id'	=> (int)$this->session->userdata('user_site_id'),
				'grade'		=> (int)$this->input->post('grade'),
				'section'	=> $this->input->post('section'),
			])['rows'][0])) {
				if ($this->config->item('site_country_code') === 'IN') {
					$this->json['error'] = sprintf(_l('The_teacher_is_already_assigned_to_Grade_%s%s'), (int)$this->input->post('grade'), (string)$this->input->post('section'));
					return;
				}

				$this->json['error'] = sprintf(_l('The_teacher_is_already_assigned_to_Grade_%s'), (int)$this->input->post('grade'));
				return;
			}

			$site_info = $this->site_model->get($this->session->userdata('user_site_id'));

			$last_user_id = $this->db->select_max('id')->get('users')->row()->id;
			$last_user_id++;

			$last_user_id 	= sprintf('%06d', $last_user_id);
			$username 		= strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $this->input->post('name')), 0, 2) .
				substr($last_user_id, -6)
			));

			$explode 		= explode(' ', ($this->input->post('name') ?? ''), 2);
			$first_name 	= array_shift($explode);
			$last_name 		= array_shift($explode);

			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

			$teacher_id = $this->teacher_model->add([
				'first_name'	=> $first_name ?? '',
				'last_name'		=> $last_name ?? '',
				'slug'			=> get_user_slug($username),
				'username'		=> $username,
				'password'		=> $encoded_password,
				'mobile'		=> '',
				'email'			=> $this->input->post('email'),
				'source'		=> 'school_dashboard',
				'country_id'	=> (int)($site_info['country_id'] ?? 0),
				'state_id'		=> (int)($site_info['state_id'] ?? 0),
				'city_id'		=> (int)($site_info['city_id'] ?? 0),
				'grade'			=> (int)$this->input->post('grade'),
				'section'		=> $this->input->post('section'),
				'role_id'		=> 3,
				'site_id'		=> (int)$site_info['id'],
				'status'		=> 1,
				'location'		=> '',
				'referral_code'	=> mb_strtoupper(uniqid()),
				'verification_code'	=> $verification_code,
				'ip'				=> $this->input->ip_address(),
				'timezone'			=> '',
				'mobile_verified'	=> 0,
				'email_verified'	=> 0,
			]);

			CI_Events::trigger('teacher_created', [
				'teacher_id'	=> $teacher_id,
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'teacher_created_' . (int)$teacher_id
			]);

			$this->cron_model->add([
				'code'			=> 'inviteTeacherCron_' . $teacher_id,
				'action'		=> 'alert_model->inviteTeacherCron',
				'data'			=> [$teacher_id, ($this->input->post('event_id') ?? 0)],
				'alert_date'	=> date('Y-m-d H:i:s', strtotime('+1 minutes')),
			]);

			$this->json['success'] = _l('Teacher_has_been_added_successfully');
		}
	}

	public function inviteStudent() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|min_length[3]|max_length[255]|valid_email');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[6]|max_length[20]');
		$this->form_validation->set_rules('grade', _l('grade'), [
			'trim',
			'required',
			'numeric',
			['grade', [$this->validate_model, 'grade']]
		]);
		$this->form_validation->set_rules('section', _l('section'), [
			'trim',
			'required',
			['section', [$this->validate_model, 'section']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($info = $this->db->get_where('users', [
				'email'	=> $this->input->post('email')
			])->row_array())) {
				$this->json['error'] = 'This email is already registered with BriBooks';
				return;
			}

			if (!empty($info = $this->db->get_where('users', [
				'mobile'	=> $this->input->post('mobile')
			])->row_array())) {
				$this->json['error'] = 'This mobile is already registered with BriBooks';
				return;
			}

			$site_info = $this->site_model->get($this->session->userdata('user_site_id'));

			$last_user_id = $this->db->select_max('id')->get('users')->row()->id;
			$last_user_id++;

			$last_user_id 	= sprintf('%06d', $last_user_id);
			$username 		= strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $this->input->post('name')), 0, 2) .
				substr($last_user_id, -6)
			));

			$explode 		= explode(' ', ($this->input->post('name') ?? ''), 2);
			$first_name 	= array_shift($explode);
			$last_name 		= array_shift($explode);

			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

			$student_id = $this->student_model->add([
				'first_name'	=> $first_name ?? '',
				'last_name'		=> $last_name ?? '',
				'slug'			=> get_user_slug($username),
				'username'		=> $username,
				'password'		=> $encoded_password,
				'mobile'		=> $this->input->post('mobile'),
				'email'			=> $this->input->post('email'),
				'source'		=> $this->input->post('source') ?? 'teacher_dashboard',
				'country_id'	=> (int)($site_info['country_id'] ?? 0),
				'state_id'		=> (int)($site_info['state_id'] ?? 0),
				'city_id'		=> (int)($site_info['city_id'] ?? 0),
				'grade'			=> (int)$this->input->post('grade'),
				'section'		=> $this->input->post('section'),
				'role_id'		=> 2,
				'site_id'		=> (int)$site_info['id'],
				'status'		=> 1,
				'location'		=> '',
				'referral_code'	=> mb_strtoupper(uniqid()),
				'verification_code'	=> $verification_code,
				'ip'				=> $this->input->ip_address(),
				'timezone'			=> '',
				'mobile_verified'	=> 0,
				'email_verified'	=> 0,
			]);

			CI_Events::trigger('student_created', [
				'teacher_id'	=> $student_id,
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'student_created_' . (int)$student_id
			]);

			$this->cron_model->add([
				'code'			=> 'inviteStudentCron_' . $student_id,
				'action'		=> 'alert_model->inviteStudentCron',
				'data'			=> [$student_id, ($this->input->post('event_id') ?? 0)],
				'alert_date'	=> date('Y-m-d H:i:s', strtotime('+1 minutes')),
			]);

			$this->json['success'] = _l('Student_has_been_added_successfully');
		}
	}

	private function _getReport($event_id = 0, $download = true, $user_id = 0) {
		$user_id = !empty($user_id) ? (int)$user_id : (int)$this->session->userdata('user_id');

		if (
			$user_id && $event_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> $user_id,
				'role_id'	=> 9,
				'status'	=> 1,
				'_deleted'	=> 0,
			])->row_array()
		) {

			$this->load->library('Common_lib', 'common_lib');

			$data = $this->common_lib->getGradeWiseData($user_id, $event_id);
			$data['event_id'] = $event_id;

			$new_html = '';

			if (in_array($event_id, [NYAF_IN_EVENT_ID, YABWF_EVENT_ID, 14, 21])) {
				$html = $this->load->view('common/report/grade_wise_indian_student_pdf', $data, true);
				$new_data = $this->common_lib->getSchoolDashboardReport($user_info['site_id'], $event_id);
				$new_html = $this->load->view('common/report/student_pdf', $new_data, true);
			}else{
				$html = $this->load->view('common/report/grade_wise_student_pdf', $data, true);
			}

			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$file_name = sprintf('uploads/pdfs/%s-%s.pdf', date('Y-m-d'), $event_id);

			if ($download) {
				$dompdf->stream($file_name);
			} else {
				return $dompdf->output();
			}
		}
	}

	private function _getSchoolReport($event_id = 0, $download = true, $user_id = 0) {
		$user_id = !empty($user_id) ? (int)$user_id : (int)$this->session->userdata('user_id');

		if (
			$user_id && $event_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> $user_id,
				'role_id'	=> 9,
				'status'	=> 1,
				'_deleted'	=> 0,
			])->row_array()
		) {
			$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

			$data 				= $this->schooldashboard_lib->getGradeWiseData($user_id, $event_id);
			$new_data 			= $this->schooldashboard_lib->getSchoolDashboardReport($user_info['site_id'], $event_id);
			$data['event_id'] 	= $event_id;
			$event_info 		= $this->event_model->get($event_id);

			$grade_label = 'Grade';
			if (!empty($event_info) && $event_info['country_code'] == 'GB') {
				$grade_label = 'Year';
			}

			$new_html = '';

			if (
				strtolower($user_info['location']) != 'india'
			) {
				$data['grade_label'] 		= $grade_label;
				$new_data['grade_label'] 	= $grade_label;

				$html	= $this->load->view('common/report/school_report_us', $data, true);
				$new_html 	= $this->load->view('common/report/student_pdf_us', $new_data, true);
				// $html	= $this->load->view('common/report/school_report', $data, true);
			} else {
				$html 		= $this->load->view('common/report/school_report', $data, true);
				$new_html 	= $this->load->view('common/report/student_pdf', $new_data, true);
			}


			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$file_name = sprintf('uploads/pdfs/%s_%s_%s.pdf', $user_info['site_id'], time(), $event_id);

			if ($download) {
				$dompdf->stream($file_name);
			} else {
				return $dompdf->output();
			}
		}
	}

	public function getSchoolLeagueUserData() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('type', _l('type'), [
			'trim',
			'required',
			'in_list[school,city,state,country]'
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$site_id 	= (int)$this->input->post('site_id') ?? 0;
			$type 		= strtolower($this->input->post('type') ?? 'school');

			$this->load->model(sprintf('event/EventChallenge%s_model', ucwords($type)), sprintf('event_challenge_%s_model', strtolower($type)));
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($type)), sprintf('ranking_%s_model', strtolower($type)));

			$challenge_model_name 	= sprintf('event_challenge_%s_model', strtolower($type));
			$rank_model_name 		= sprintf('ranking_%s_model', strtolower($type));

			if (empty($challenge_info = $this->{$challenge_model_name}->get_all([
				'type'					=> 'user',
				'event_id'				=> (int)$this->input->post('event_id'),
			])['rows'][0] ?? [])) {
				return $this->json['error'] = _l('invalid_league!');
			}

			$limit 	= $this->input->post('limit') ?? 10;
			$page 	= $this->input->post('page') ?? 1;

			$ranks = $this->{$rank_model_name}->get_all([
				'event_id' 		=> $challenge_info['event_id'],
				'site_id' 		=> $site_id,
				'challenge_id' 	=> $challenge_info['id'],
				'start'			=> $page > 0
					? ($page - 1) * $limit
					: 0,
				'limit'			=> $limit,
				'sort'			=> 'score',
				'order'			=> 'DESC',
			]) ?? [];

			$user_ranks = [];

			foreach ($ranks['rows'] as $key => $rank) {
				$student_info = $this->user_model->get($rank['user_id']);

				$user_ranks[] = [
					'event_id' 		=> $rank['event_id'],
					'site_id' 		=> $site_id,
					'user_id' 		=> $rank['user_id'],
					'book_id' 		=> $rank['book_id'],
					'book_name' 	=> $rank['book_name'],
					'author_name' 	=> $rank['author_name'],
					'book_image' 	=> $rank['book_image'],
					'author_image' 	=> $rank['author_image'],
					'book_slug' 	=> $rank['book_slug'],
					'grade' 		=> $student_info['grade'],
					'section' 		=> $student_info['section'],
					'score' 		=> $rank['score']
				];
			}

			$this->json['ranks'] = $user_ranks;
			$this->json['total'] = $ranks['total'];
		}
	}
}
