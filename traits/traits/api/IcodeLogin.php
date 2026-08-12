<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait IcodeLogin {
	private function generateSign($data, $secret) {
		array_multisort(array_keys($data), $data);

		$http_query = '';

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$value = '[' . implode(',', $value) . ']';
			}

			$http_query .= "{$key}={$value}&";
		}

		$http_query = rtrim($http_query, '&');

		return md5($http_query . $secret);
		//return md5(str_replace('+', ' ', http_build_query($data)) . $secret);
	}

	public function loginToIcode() {
		$data = array_filter($this->input->post(), function($k) {
			return $k !== 'sign';
		}, ARRAY_FILTER_USE_KEY);

		if ($this->input->post('sign') !== self::generateSign($data, ICODE_SECRET)) {
			$this->json['error'] = _l('invalid_signature');
		}

		// Step 1: school code not exists in icode then register school in icode
		// Step 2: register user account with school code
		// Step 3: login user and get login code
		if (!$this->json) {
			$this->form_validation->set_rules('first_name', _l('first_name'), 'trim|required|min_length[3]|max_length[40]');
			$this->form_validation->set_rules('last_name', _l('last_name'), 'trim|required|min_length[3]|max_length[40]');
			$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
			$this->form_validation->set_rules('grade', _l('student_grade'), 'trim|required|max_length[4]');
			$this->form_validation->set_rules('course', _l('course'), 'trim|required|max_length[40]');
			$this->form_validation->set_rules('school_code', _l('school_code'), [
				'trim',
				'required',
				['school_code', [$this->validate_model, 'schoolCode']]
			]);

			$valid = $this->form_validation->run();

			!$valid && ($this->json['error'] = strip_tags(validation_errors()));
		}

		if (!$this->json) {
			$site_info = $this->site_model->getByCode($this->input->post('school_code'));

			$this->site_model->initConfig($site_info['id']);

			if ($student_info = $this->db->get_where('users', [
				'mobile' 	=> $data['mobile'],
				'email' 	=> $data['email'],
				'role_id' 	=> 2
			])->row_array()) {
				$this->db->update('users', [
					'first_name'		=> $data['first_name'],
					'last_name'			=> $data['last_name'],
					'parent_name'		=> $data['parent_name'],
					'role_id'			=> 2,
					'site_id'			=> (int)$site_info['id'],
					'grade'				=> $data['grade'] ?? '',
					'emi_type'			=> 'premium',
				], [
					'id'				=> (int)$student_info['id']
				]);

				$student_id = $student_info['id'];

				self::enrolToCourse($student_id, $this->input->post('course'));
			} else {
				$this->db->insert('users', [
					'first_name'		=> $data['first_name'],
					'last_name'			=> $data['last_name'],
					'parent_name'		=> $data['parent_name'],
					'grade'				=> $data['grade'],
					'email'				=> $data['email'],
					'mobile'			=> $data['mobile'],
					'emi_type'			=> 'premium',
					'password'			=> sha1(uniqid()),
					'role_id'			=> 2,
					'site_id'			=> (int)$site_info['id'],
					'date_added'		=> strtotime(date('Y-m-d H:i:s')),
					'status'			=> 1,
				]);

				$student_id = $this->db->insert_id();

				self::enrolToCourse($student_id, $this->input->post('course'));
			}

			$code = $this->user_model->addLoginCode([
				'user_id'	=> $student_id
			]);

			$this->json['redirect'] = site_url('login/code/' . $code);
		}

		self::setOutput();
	}

	private function enrolToCourse($student_id, $course) {
		if ($course_info = $this->db->like('title', $course)->get('course')->row_array()) {
			log_message('KB', print_r($course_info, 1));
			if ($row = $this->db->get_where('enrol', [
				'course_id'			=> (int)$course_info['id'],
				'user_id'			=> (int)$student_id,
			])->row_array()) {

			} else {
				$this->db->insert('enrol', [
					'user_id'		=> (int)$student_id,
					'course_id'		=> (int)$course_info['id'],
					'mode'			=> 'online',
					'emi_type'		=> 'premium',
					'status'		=> 1,
					'renewal_date'	=> date('Y-m-d H:i:s', strtotime('+3 months')),
					'doj'			=> date('Y-m-d H:i:s'),
					'date_added'	=> strtotime(date('Y-m-d H:i:s')),
					'site_id'		=> (int)$this->config->item('site_id'),
				]);
			}
		}
	}
}
