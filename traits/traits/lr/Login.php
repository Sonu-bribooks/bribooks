<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Login {
	public function login() {
		redirect(site_url('home'), 'refresh');

		$data['page_name'] 		= 'login';
		$data['page_title'] 	= _l('login');

		$this->load->view('lr/index', $data);
	}

	public function validateCode() {
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|exact_length[6]|callback_code_check');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$code_info = $this->lr_assessment_code_model->get_all([
				'code' 		=> $this->input->post('code'),
				'status' 	=> 1,
			])['rows'][0] ?? [];

			$this->lr_assessment_code_model->edit($code_info['id'], [
				'last_used' => date('Y-m-d H:i:s')
			]);

			if ($row = $this->db->get_where('users', [
				'id'		=> (int)$code_info['user_id'],
				'status' 	=> 1,
			])->row()) {
				$this->session->set_userdata('quiz_code_id', $code_info['id']);
				$this->session->set_userdata('quiz_level', $code_info['level']);
				$this->session->set_userdata('quiz_category_id', $code_info['category_id']);
				$this->session->set_userdata('quiz_attempt', $code_info['attempt']);
				$this->session->set_userdata('quiz_uid', $row->id);
				$this->session->set_userdata('user_id', $row->id);
				$this->session->set_userdata('role_id', $row->role_id);
				$this->session->set_userdata('grade', $row->grade);
				$this->session->set_userdata('role', get_user_role('user_role', $row->id));
				$this->session->set_userdata('additional_role_id', $row->additional_role_id);
				$this->session->set_userdata('user_email', $row->email);
				$this->session->set_userdata('name', $row->first_name . ' ' . $row->last_name);
				$this->session->set_userdata('user_site', $row->site_id ?? 0);

				$this->json['success'] = _li('code_validated');
				$this->json['redirect'] = base_url('assessment');
			} else {
				$this->json['error'] = _li('invalid_code');
			}
		}

		self::setOutput();
	}

	public function code_check($str) {
		if (!$this->lr_assessment_code_model->get_all([
			'code' 		=> $str,
			'status' 	=> 1,
		])['rows']) {
			$this->form_validation->set_message('code_check', _l('The {field} not found'));
			return false;
		}

		return true;
	}

	public function logout() {
		$this->session->unset_userdata([
			'quiz_code_id',
			'quiz_level',
			'quiz_category_id',
			'quiz_attempt',
			'quiz_uid',
			'user_id',
			'role_id',
			'role',
			'grade',
			'additional_role_id',
			'user_email',
			'name',
			'nauser_siteme',
		]);

		redirect('assessment/login', 'refresh');
	}

	public function changeGrade() {
		if (
			$this->input->get('grade') &&
			in_array($this->input->get('grade'), LEAD_GRADES) &&
			$this->session->userdata('role_id') == 3
		) {
			$this->session->set_userdata('quiz_grade', (int)$this->input->get('grade'));
			redirect(base_url('assessment'), 'refresh');
		}
	}

	private function loginWithoutToken() {
		$data = [];

		$user_id 	= $this->input->cookie('LEAP_SESSION_USER_ID', TRUE);
		$user_role 	= $this->input->cookie('LEAP_SESSION_USER_ROLR', TRUE);

		if ($user_id && $user_role) {
			// Teacher
			if ($user_role == 1) {
				$teacher_info = $this->teacher_model->get_all([
					'exported'	=> $user_id,
				])->row_array();

				$this->session->set_userdata([
					'quiz_uid' 	=> $teacher_info['id'],
					'quiz_grade'=> 3,
					'role_id' 	=> 3,
				]);
			} else {
				$student_info = $this->student_model->get_all([
					'exported'	=> $user_id,
				])->row_array();

				$this->session->set_userdata([
					'quiz_uid' 	=> $student_info['id'],
					'quiz_grade'=> $student_info['grade'],
					'role_id' 	=> 2
				]);
			}
		}

		return $data;
	}

	private function loginWithToken() {
		if ($this->input->cookie('leap_token', TRUE)) {
			$result = json_decode(file_get_contents(
				'https://api.leaplearner.co.in/onlineplatform/user/loginWithToken?type=8&token=' . $this->input->cookie('leap_token', TRUE)
			), TRUE);

			$user_info = $result['data'] ?? [];

			if (!empty($user_info)) {
				unset($user_info['password']);
				$data['user_info'] = $user_info;

				// Teacher
				if ($user_info['urole'] == 1) {
					$teacher_info = $this->teacher_model->get_all([
						'exported'	=> $user_info['id'] ?? 0,
					])->row_array();

					$this->session->set_userdata([
						'quiz_uid' 	=> $teacher_info['id'],
						'quiz_grade'=> 3,
						'role_id' 	=> 3
					]);
				} else {
					$student_info = $this->student_model->get_all([
						'exported'	=> $user_info['id'] ?? 0,
					])->row_array();

					$this->session->set_userdata([
						'quiz_uid' 	=> $student_info['id'],
						'quiz_grade'=> $student_info['grade'],
						'role_id' 	=> 2
					]);
				}
			} else {
				$data['user_info'] = false;
			}
		}
	}
}
