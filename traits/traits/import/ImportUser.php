<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportUser {
	private function _importStudentLead($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('common/ImportLeads_model', 'import_leads_model');

		foreach ($rows as $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id']) || empty($data['site_id']) || empty($data['first_name']) || empty($data['type'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($site_info = $this->site_model->get($data['site_id']))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$lead_id = $student_id = 0;

			$email = !empty($data['email']) ? trim($data['email']) : '';

			if (empty($email)) {
				$site_name_email_domain = strtolower(preg_replace(['/[^\w\s]/', '/\s+/'], '', $site_info['name']));

				$owner_email_arr 	= explode('@', $site_info['owner_email']);
				$owner_email_domain = $owner_email_arr[1] ?? $site_name_email_domain;

				$email = strtolower(preg_replace(['/[^\w\s]/', '/\s+/'], '', trim($data['first_name'].$data['last_name']))) . '@' . $owner_email_domain;
			}

			$mobile = !empty($data['mobile']) ? preg_replace('/[^0-9]/', '', trim($data['mobile'])) : '';

			$search_data = [
				'event_id'			=> (int)$data['event_id'],
				'site_id'			=> (int)$data['site_id'],
			];

			if (($data['type'] ?? 'mobile') == 'mobile') {
				$search_data['mobile'] = $mobile;
			} else {
				$search_data['email'] = $email;
			}

			if (!empty($this->db->get_where('import_leads', $search_data)->row_array())) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$search_data = [
				'role_id'			=> 2,
				'status'			=> 1,
				'_deleted'			=> 0,
			];

			if (($data['type'] ?? 'mobile') == 'mobile') {
				$search_data['mobile'] = $mobile;
				$search_data['mobile_verified'] = 1;
			} else {
				$search_data['email'] = $email;
				$search_data['email_verified'] = 1;
			}

			$user_info 	= $this->db->get_where('users', $search_data)->row_array();

			$student_id = $user_info['id'] ?? '';
			$username 	= $user_info['username'] ?? '';

			$password 	= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

			if (empty($student_id)) {
				$name = trim($data['first_name'] . ' ' . $data['last_name']);

				if (!empty($mobile) && ($data['type'] == 'mobile')) {
					$username = strtolower(trim(
						substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $name), 0, 4) .
						substr($mobile, -4)
					));
				} else {
					$this->db->select_max('id');
					$last_user_id = $this->db->get('users')->row_array()['id'];
					$last_user_id++;

					$last_user_id = sprintf('%06d', $last_user_id);

					$username = strtolower(trim(
						substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $name), 0, 2) .
						substr($last_user_id, -6)
					));
				}

				$student_id = $this->student_model->add([
					'site_id'		=> (int)$data['site_id'],
					'role_id'		=> 2,
					'username'		=> $username,
					'first_name'	=> trim($data['first_name']),
					'last_name'		=> trim($data['last_name']),
					'email'			=> $email,
					'mobile'		=> $mobile,
					'password'		=> $encoded_password,
					'slug'			=> get_user_slug($username),
					'status'		=> 1,
					'referral_code'	=> mb_strtoupper($password),
					'state_id'		=> $site_info['state_id'] ?? 0,
					'city_id'		=> $site_info['city_id'] ?? 0,
					'source'		=> $site_info['site_code'] ?? '',
					'ip'			=> $this->input->ip_address(),
					'verification_code'	=> $verification_code,
					'mobile_verified'	=> 0, //($data['type'] ?? 'mobile') == 'mobile',
					'email_verified'	=> 0, //($data['type'] ?? 'mobile') == 'email'
				]);
			} else {
				$this->student_model->edit($student_id, [
					'password'			=> $encoded_password,
					'verification_code'	=> $verification_code
				]);
			}

			$search_data = [];

			if (($data['type'] ?? 'mobile') == 'mobile') {
				$search_data['mobile'] = $mobile;
				$search_data['mobile_verified'] = '1';
			} elseif (($data['type'] ?? 'mobile') == 'email') {
				$search_data['email'] = $email;
				$search_data['email_verified'] = '1';
			}

			$lead_info = $this->db->get_where('lead', $search_data)->row_array();

			$lead_id = $lead_info['id'] ?? '';

			if (empty($lead_id)) {
				$lead_id = $this->lead_model->add([
					'event_id'			=> (int)$data['event_id'],
					'site_id'			=> (int)$data['site_id'],
					'site_type'			=> 1,
					'student_id'		=> (int)$student_id,
					'name'				=> $name,
					'source'			=> $site_info['site_code'] ?? '',
					'mobile'			=> $mobile,
					'email'				=> $email,
					'state_id'			=> $site_info['state_id'] ?? 0,
					'city_id'			=> $site_info['city_id'] ?? 0,
					'ip'				=> $this->input->ip_address(),
					'mobile_verified'	=> ($data['type'] ?? 'mobile') == 'mobile',
					'email_verified'	=> ($data['type'] ?? 'mobile') == 'email'
				]);
			}

			if (empty($this->event_user_model->getEventUserByUserId($data['event_id'], $student_id))) {
				$this->event_user_model->add([
					'event_id'	=> (int)$data['event_id'],
					'user_id'	=> (int)$student_id
				]);
			}

			$this->import_leads_model->add([
				'event_id'			=> (int)$data['event_id'],
				'site_id'			=> (int)$data['site_id'],
				'lead_id'			=> (int)$lead_id,
				'student_id'		=> (int)$student_id,
				'first_name'		=> trim($data['first_name']),
				'last_name'			=> trim($data['last_name']),
				'email'				=> $email,
				'mobile'			=> $mobile,
				'type'				=> $data['type'] ?? 'mobile',
				'username'			=> $username,
				'password'			=> $password,
			]);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importAuthor($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			$country_name = $country_info['country'];

			if (empty($data['first_name']) || empty($data['site_id']) || empty($site_info = $this->site_model->get($data['site_id']))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['email']) && empty($data['mobile'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$country_name = $this->country_model->getByCode($site_info['country_code'])['name'];

			if (!empty($data['email']) && $student = $this->db->get_where('users', [
				'email'		=> $data['email'],
			])->row_array()) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (!empty($data['mobile']) && $student = $this->db->get_where('users', [
				'mobile'		=> $data['mobile'],
			])->row_array()) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (!empty($data['mobile'])) {
				$username = strtolower(trim(
					substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['first_name']), 0, 4) .
					substr($data['mobile'], -4)
				));
			} else {
				$this->db->select_max('id');
				$last_user_id = $this->db->get('users')->row_array()['id'];
				$last_user_id++;

				$last_user_id = sprintf('%06d', $last_user_id);

				$username = strtolower(trim(
					substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['first_name']), 0, 2) .
					substr($last_user_id, -6)
				));
			}

			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

			$this->db->insert('users', [
				'first_name'		=> $data['first_name'],
				'last_name'			=> $data['last_name'],
				'slug'				=> get_user_slug($username),
				'username'			=> $username,
				'password'			=> $encoded_password,
				'mobile'			=> $data['mobile'],
				'email'				=> $data['email'],
				'city_id'			=> $data['city_id'] ?? 0,
				'state_id'			=> $data['state_id'] ?? 0,
				'grade'				=> $data['grade'] ?? 1,
				'section'			=> $data['section'] ?? '',
				'grade_id'			=> $data['grade'] ?? 1,
				'section_id'		=> $data['section'] ?? '',
				'role_id'			=> 2,
				'status'			=> 1,
				'site_id'			=> (int)$data['site_id'],
				'email_verified'	=> 0,
				'mobile_verified'	=> 0,
				'verified'			=> 0,
				'verification_code'	=> $verification_code,
				'source'			=> 'author-import-' . $site_info['id'],
				'location'			=> $country_name,
				'date_added'		=> date('Y-m-d H:i:s'),
			]);

			$user_id = $this->db->insert_id();

			$uploaded++;

			if (
				!empty($user_id) &&
				!empty($data['event_id']) &&
				!empty($event_info = $this->event_model->get($data['event_id'])) &&
				strtotime($event_info['start_date']) < time() &&
				strtotime($event_info['end_date']) > time()
			) {
				$this->event_user_model->add([
					'event_id'	=> $event_info['id'],
					'user_id'	=> $user_id,
				]);
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importTeachers($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['full_name'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['email'])) {
				$data['email'] = $data['mobile'] . '@leaplearner.co';
			}

			// 1. Add teacher
			$explode = explode(' ', trim($data['full_name']), 2);

			if ($teacher = $this->db->get_where('users', [
				// 'email'			=> $data['email'],
				'mobile'		=> $data['mobile'],
				// 'role_id'		=> 3,
				// 'site_id'		=> (int)$data['site_id'],
			])->row_array()) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;

				$teacher_id = $teacher['id'];

				$this->db->update('users', [
					'first_name'		=> array_shift($explode),
					'last_name'			=> array_shift($explode),
					'mobile'			=> $data['mobile'],
					'email'				=> $data['email'],
					'password'			=> sha1($data['password']),
					'lms_password'		=> $data['password'],
					'role_id'			=> 3,
					'site_id'			=> (int)$data['site_id'],
				], [
					'id'				=> (int)$teacher_id
				]);
			} else {
				$uploaded++;

				$this->db->insert('users', [
					'first_name'		=> array_shift($explode),
					'last_name'			=> array_shift($explode),
					'password'			=> sha1($data['password']),
					'lms_password'		=> $data['password'],
					'role_id'			=> 3,
					'parent_name'		=> $data['parent_name'],
					'mobile'			=> $data['mobile'],
					'email'				=> $data['email'],
					'status'			=> 1,
					'site_id'			=> (int)$data['site_id'],
					'date_added'		=> strtotime(date('Y-m-d H:i:s')),
				]);

				$teacher_id = $this->db->insert_id();

				$this->activity_model->add([
					'table'		=> 'users',
					'table_id'	=> $teacher_id,
					'action'	=> 'teacher_add'
				]);
			}

			// Sync Student
			// $this->sync_model->updateTeacher($teacher_id);

			// 2. Get course_id by course name

			// $this->db->select('course_id');
			// $this->db->where('site_id', (int)($this->config->item('site_parent_id') > 0
			// 	? $this->config->item('site_parent_id')
			// 	: $this->config->item('site_id')));
			// $this->db->from('course_to_site');
			//
			// $where_clause = $this->db->get_compiled_select();

			$this->db->like('title', $data['course']);
			$this->db->where('status', 'active');
			// $this->db->where('`course`.`id` IN ($where_clause)', NULL, FALSE);

			if ($course_info = $this->db->get('course')->row_array()) {
				$course_id = $course_info['id'];
				$this->db->delete('teachers', [
					'user_id'		=> (int)$teacher_id,
				]);

				$this->db->insert('teachers', [
					'user_id'		=> (int)$teacher_id,
					'course_id'		=> (int)$course_id,
				]);
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importUserAwardAddress($rows = [], $map = [], $job_id = 0) {
		$this->load->model('user/UserAwardAddress_model', 'user_award_address_model');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id']) || empty($data['user_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if ($student_info = $this->student_model->get($data['user_id'])) {
				if (empty($info = $this->user_award_address_model->get_all([
					'event_id' 	=> $data['event_id'],
					'user_id' 	=> $data['user_id'],
				])['rows'] ?? [])) {
					$this->user_award_address_model->add([
						'event_id'	=> $data['event_id'],
						'user_id'	=> $data['user_id'],
						'status' 	=> 0,

					]);
				} else {
					self::_updateCounter($job_id, true);
					$skipped++;
					continue;
				}
			} else {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}
}
