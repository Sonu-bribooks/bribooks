<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Signup {
	public function sendSignupOtp() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile,whatsapp]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid mobile number'),
			'max_length'	=> _li('Please enter a valid mobile number'),
			'numeric'		=> _li('Please enter a valid mobile number'),
		]);
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]', [
			'valid_email'	=> _li('Please enter a valid email address'),
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if ($user_info = $this->db->get_where('users', [
				'email'			=> $this->input->post('email'),
			])->row_array()) {
				$this->json['error'] = _li('This_email_is_already_registered_with_BriBooks');
				return;
			}

			if ($user_info = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('mobile'),
			])->row_array()) {
				$this->json['error'] = _li('This_mobile_is_already_registered_with_BriBooks');
				return;
			}
		}

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate()) {
				return;
			}

			self::_executeOtp(
				$this->input->post('type') == 'mobile',
				false,
				$this->input->post('type') == 'whatsapp',
			);

			$this->json['lead_id'] = self::_addLead();
		}
	}

	public function verifySignupOtp() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[email,mobile,whatsapp]');

		if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
				'min_length'	=> _li('Please enter a valid mobile number'),
				'max_length'	=> _li('Please enter a valid mobile number'),
				'numeric'		=> _li('Please enter a valid mobile number')
			]);
		} elseif ($this->input->post('type') == 'email') {
			$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]', [
				'valid_email'	=> _li('Please enter a valid email address'),
			]);
		}

		$this->form_validation->set_rules('otp', _l('otp'), 'trim|required|numeric|exact_length[6]');

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (self::_verifyOtp(in_array($this->input->post('type'), ['mobile', 'whatsapp']))) {
				$lead_info = $this->lead_model->get($this->input->post('lead_id'));

				$user_id = self::_doLogin($lead_info + [
					'type' => $this->input->post('type')
				]);

				if (in_array($this->input->post('type'), ['mobile', 'whatsapp'])) {
					$this->lead_model->edit($lead_info['id'], [
						'student_id'		=> (int)$user_id,
						'mobile_verified'	=> 1,
					]);
				} else if ($this->input->post('type') == 'email') {
					$this->lead_model->edit($lead_info['id'], [
						'student_id'		=> (int)$user_id,
						'email_verified'	=> 1,
					]);
				}

				CI_Events::trigger('access_log', [
					'module'	=> 'user_signup'
				]);

				// add to event
				if (
					!empty($lead_info['event_id']) &&
					$user_id &&
					empty($this->event_user_model->getEventUserByUserId($lead_info['event_id'], $user_id))
				) {
					$this->event_user_model->add([
						'event_id'	=> (int)$lead_info['event_id'],
						'user_id'	=> (int)$user_id,
					]);
				}

				$this->json['success'] 	= _l('otp_successfully_verified');
			} else {
				$this->json['error'] 	= _l('please_enter_the_correct_verification_code');
			}
		}
	}

	// Deprecated
	public function signup() {}

	public function signupBanner() {
		if (!$this->json) {
			// $this->load->library('Royalty_lib', 'royalty_lib');

			$user_country_code = strtolower($this->input->cookie('user_country_code'));
			if (empty($user_country_code)) {
				$user_country_code = strtolower($this->config->item('site_country_code'));
			}

			$filter_data = [
				'status'	=> 1,
				'start'		=> 0,
				'limit'		=> 10,
				'sort'		=> 'sold',
				'order'		=> 'DESC',
				'location'	=> 'india',
				'quantity_ge'=> '1'
			];

			if(!empty($user_country_code) && $user_country_code !== 'in') {
				$filter_data = [
					'status'	=> 1,
					'start'		=> 0,
					'limit'		=> 10,
					'sort'		=> 'sold',
					'order'		=> 'DESC',
					'location'	=> 'united states',
					'ne_location'=> 'india',
					'quantity_ge'=> '1'
				];
			}

			$key = 'signup_banner' . (ENVIRONMENT === 'production' ? '_live_' : '_test_') . implode('_', array_keys($filter_data)) . '_' . implode('_', array_values($filter_data));

			$cache_data = json_decode($this->cache->get($key), true);

			if (!empty($cache_data)) {
				$books = $cache_data;
			} else {
				$books = $this->bookstore_model->get_all($filter_data)['rows'] ?? [];

				$this->cache->save($key, json_encode($books), 7200);
			}

			$book_info = $books[rand(0, count($books) - 1)];

			// $order_total = $this->order_model->getTotalProductsByProductId($book_info['id']);
			// $order_total = readable_format($order_total ? $order_total : 0);

			// $royalty_amount = $this->royalty_lib->getBookTotalRoyality($book_info['id']);
			// $royalty_amount = readable_format(round($royalty_amount ? $royalty_amount : 0, 2));

			$this->json['banner']['image'] = $book_info['author_image'];
			$this->json['banner']['author_name'] = $book_info['author_name'];
			$this->json['banner']['sold'] = $book_info['sold'];
			$this->json['banner']['currency'] = $this->config->item('site_currency_symbol');
			$this->json['banner']['amount'] = $royalty_amount ?? 0;
		}
	}

	public function getGrades() {
		if (!$this->json) {
			$grades = [];

			for ($i=1; $i <= 12; $i++) {
				$grades[] = [
					'id'	=> $i,
					'name'	=> (!empty($this->input->post('country_code')) && !empty(STUDENT_GRADES[strtoupper($this->input->post('country_code'))][$i])) ? STUDENT_GRADES[strtoupper($this->input->post('country_code'))][$i] : $i
				];
			}

			$this->json['grades'] = $grades;

			return $this->json['grades'];

			/*$this->load->model('common/Grade_model', 'grade_model');

			$filter_data = [
				'status'	=> 1,
				'sort'		=> 'site_grade.name',
				'order'		=> 'ASC'
			];

			if ($this->input->post('code')) {
				$filter_data['site_code'] = $this->input->post('code');
			}

			if ($this->input->post('site_id')) {
				$filter_data['site_id'] = $this->input->post('site_id');
			}

			$this->json['grades'] = array_map(function($item) {
				return [
					'id'	=> $item['id'],
					'name'	=> (!empty($this->input->post('country_code')) && !empty(STUDENT_GRADES[strtoupper($this->input->post('country_code'))][$item['name']])) ? STUDENT_GRADES[strtoupper($this->input->post('country_code'))][$item['name']] : $item['name'],
				];
			}, $this->grade_model->get_all($filter_data)['rows'] ?? []);*/
		}
	}

	public function getSections() {
		/*$this->form_validation->set_rules('grade_id', _l('grade_id'), [
			'trim',
			'required',
			'numeric',
			['grade', [$this->validate_model, 'grade']]
		]);

		self::_runFormValidation();*/

		if (!$this->json) {
			$sections = [];

			foreach (range('A', 'Z') as $section) {
			    $sections[] = [
					'id'	=> $section,
					'name'	=> $section
				];
			}

			$this->json['sections'] = $sections;

			return $this->json['sections'];

			/*$this->load->model('common/Section_model', 'section_model');

			$this->json['sections'] = array_map(function($item) {
				return [
					'id'	=> $item['id'],
					'name'	=> $item['name'],
				];
			}, $this->section_model->get_all([
				'grade_id'	=> (!empty($this->input->post('country_code')) && !array_key_exists(STUDENT_GRADES[strtoupper($this->input->post('country_code'))][$this->input->post('grade_id')])) ? STUDENT_GRADES[strtoupper($this->input->post('country_code'))][$this->input->post('grade_id')] : $this->input->post('grade_id'),
				'status'	=> 1,
				'sort'		=> 'site_section.name',
				'order'		=> 'ASC'
			])['rows'] ?? []);*/
		}
	}
}
