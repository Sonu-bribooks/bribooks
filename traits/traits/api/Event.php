<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Event {
	public function getEvents() {
		if (!$this->json) {

			$filter_data = [
				'sort'			=> 'event.id',
				'order'			=> 'ASC',
			];

			if(!empty( $this->input->post('event_id'))){
				$filter_data['event_id'] = (int)$this->input->post('event_id');
			}

			if (!empty($this->input->post('selling_start_date_le'))) {
				$filter_data['selling_start_date_le'] = $this->input->post('selling_start_date_le');
			}

			if (!empty($this->input->post('selling_end_date_ge'))) {
				$filter_data['selling_end_date_ge'] = $this->input->post('selling_end_date_ge');
			}

			if (!empty($this->input->post('order'))) {
				$filter_data['order'] = $this->input->post('order');
			}

			$this->json['events'] = array_map(function ($item) {
				return [
					'id'	=> $item['id'],
					'name'	=> $item['name'],
				];
			}, $this->event_model->get_all($filter_data)['rows'] ?? []);
		}
	}

	public function getEventRankers() {
		if (!$this->json) {
			$key = 'event_rankers' . (ENVIRONMENT === 'production' ? '_live' : '_test');

			$cache_data = json_decode($this->cache->get($key), true);

			if (!empty($cache_data)) {
				$this->json['top_rankers'] = $cache_data;
			} else {
				$this->load->model('event/EventTopRanker_model', 'event_top_ranker_model');

				$this->json['top_rankers'] = array_map(function ($item) {
					$book_info = $this->book_model->get($item['book_id']);

					$student_info = $this->student_model->get($book_info['user_id']);

					$site_info = $this->site_model->get($student_info['site_id'] ?? 0);

					$author_image = empty($book_info['author_image'])
						? base_url('uploads/user_image/placeholder.png')
						: $this->config->item('cloudfront_url') . 'public/' . $book_info['author_image'];

					return [
						'id'			=> $item['id'],
						'book_id'		=> $item['book_id'],
						'user_id'		=> $item['user_id'],
						'book_name'		=> $item['book_name'],
						'author_name'	=> $item['author_name'],
						'site_id'		=> $site_info['id'] ?? 0,
						'school_name'	=> $site_info['name'] ?? '',
						'book_image'	=> $this->config->item('cloudfront_url') . 'public/' . $book_info['cover_image'],
						'author_image'	=> $author_image,
						'book_url'		=> !empty($book_info['amazon_url']) ? $book_info['amazon_url'] : (USER_URL . 'bookstore/' . $item['book_slug'])
					];
				}, $this->event_top_ranker_model->get_all([
					'sort'	=> 'event_top_rankers.score',
					'order'	=> 'ASC'
				])['rows'] ?? []);

				$this->cache->save($key, json_encode($this->json['top_rankers']), 7200);
			}
		}
	}

	public function acceptInvite() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('accept', _l('accept'), 'trim|required|numeric|in_list[0,1]');

		self::_runFormValidation();

		if (!$this->json) {
			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (!(
				$event_info['start_date'] <= date('Y-m-d H:i:s')
				&&
				$event_info['end_date'] >= date('Y-m-d H:i:s')
			)) {
				$this->json['error'] = _li('The_event_has_not_started_yet');
				return;
			}

			if (!($user_info = $this->db->get_where('users', [
					'id'				=> (int)$this->input->post('uid'),
					'verification_code' => $this->input->post('code')
				])->row_array())
			) {
				$this->json['error'] = _li('Invalid_invite_link');
				return;
			}

			if ($this->event_user_model->get_all([
				'event_id'	=> (int)$event_info['id'],
				'user_id'	=> (int)$user_info['id'],
			])['total'] > 0) {
				$this->json['error'] = _li('Congratulations! You are successfully registered in NYAF 2023-24');
				return;
			}

			$this->event_user_model->add([
				'event_id'	=> (int)$event_info['id'],
				'user_id'	=> (int)$user_info['id'],
			]);

			// alert for event communication
			$this->alert_model->eventSignup($event_info['id'], $user_info['id']);

			$this->json['user'] 	= [
				'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'user_id'	=> $user_info['id'],
			];
			$this->json['success'] 	= _li('Successfully_added_to_the_event');
		}
	}

	public function addEventSite() {
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

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id')))) {
				$this->event_site_model->add([
					'event_id'	=> (int)$this->input->post('event_id'),
					'site_id'	=> (int)$this->input->post('site_id')
				]);

				$this->json['success'] = _li('Successfully_added_to_the_event');
			} else {
				$this->json['error'] = _li('your_school_is_already_registered');
			}
		}
	}

	public function getUserEvents() {
		if (!$this->json) {
			$this->json['events'] = array_map(function($item) {
				$event_info = $this->event_model->get($item['event_id']);

				return [
					'id'			=> $event_info['id'],
					'name'			=> $event_info['name'],
					'start_date'	=> $event_info['start_date'],
					'end_date'		=> $event_info['end_date'],
					'publish_date'	=> $event_info['book_writing_end_date'],
					'url'			=> $event_info['url'],
					'active'		=> $event_info['start_date'] <= date('Y-m-d H:i:s') && date('Y-m-d H:i:s') <= $event_info['end_date'],
				];
			}, $this->event_user_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'sort'		=> 'event_user.event_id',
			])['rows'] ?? []);
		}
	}

	public function getUserActiveEvent() {
		if (!$this->json) {
			if (
				$this->input->post('app_os') &&
				version_compare($this->input->post('app_version'), '4.1.2', '<')
			) return;

			// app stop live ranking
			if ($this->input->post('app_os')) return;

			// if ($this->config->item('site_country_code') !== 'IN') {
			// 	return;
			// }

			$active_event = $this->event_user_model->get_all([
				'user_id'			=> (int)$this->session->userdata('user_id'),
				'is_active_event'	=> 1,
				'sort'				=> 'event_user.event_id',
				'order'				=> 'DESC',
				'start'				=> 0,
				'limit'				=> 1,
			])['rows'][0] ?? [];

			$event_info = $this->event_model->get($active_event['event_id'] ?? 0);

			$this->json['event'] = !empty($event_info) ? [
				'id'			=> $event_info['id'] ?? 0,
				'name'			=> $event_info['name'] ?? '',
				'start_date'	=> $event_info['start_date'] ?? '',
				'end_date'		=> $event_info['end_date'] ?? '',
				'publish_date'	=> $event_info['book_writing_end_date'] ?? '',
				'url'			=> $event_info['url'] ?? '',
				'active'		=> $event_info['start_date'] <= date('Y-m-d H:i:s') && date('Y-m-d H:i:s') <= $event_info['end_date'] ?? '',
				'can_publish'	=> date('Y-m-d H:i:s') <= $event_info['book_writing_end_date'] ?? '',
			] : [];
		}
	}

	public function getUserBooksByEventId() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|numeric');
		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {
			$user_books = [];

			$filter_data = [
				'user_id'		=> $this->session->userdata('user_id'),
				'status'		=> 1,
				'start'			=> 0,
				'limit'			=> 100,
				'archived'		=> 0,
				'achievement'	=> 0,
			];

			if (
				!empty($this->input->post('event_id')) &&
				!empty($event_info = $this->event_model->get($this->input->post('event_id')))
			) {
				$filter_data['event_id'] = $event_info['id'];
			} else {
				$filter_data['event_id'] = 0;
			}

			if (
				!empty($this->input->post('achievement'))
			) {
				$filter_data['achievement'] = $this->input->post('achievement');
			}

			$results = $this->certificate_model->get_all($filter_data)['rows'] ?? [];

			$this->json['books'] = [];

			foreach ($results as $item) {
				if (in_array($item['book_id'], array_column($this->json['books'], 'book_id'))) continue;
				if (empty($book_info = $this->bookstore_model->getByBookId($item['book_id']))) continue;

				$this->json['books'][] = [
					'book_id'		=> $item['book_id'],
					'book_name'		=> $book_info['name'] ?? '',
					'category_id'	=> $book_info['category_id'],
					'date_added'	=> $book_info['date_published'],
				];
			}
		}
	}

	public function getBarneRanks () {
		if (!$this->json) {
			$this->load->model('api/HomePageStats_model', 'home_page_stats_model');

			$filter_data = [
				'event_id' => 14
			];

			if (!empty($this->input->post('user_id'))) {
				$filter_data['user_id'] = $this->input->post('user_id');
			}

			if (!empty($this->input->post('book_id'))) {
				$filter_data['book_id'] = $this->input->post('book_id');
			}

			$ranks = $this->home_page_stats_model->getBarneRanks($filter_data)['rows'] ?? [];

			$this->json['ranks'] = $ranks;
		}
	}

	public function updateUserDetails() {
		$this->form_validation->set_rules('user_id', _l('user_id'), [
			'trim',
			'required',
			'numeric',
		]);
		$this->form_validation->set_rules('state_id', _l('state'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		$this->form_validation->set_rules('city_id', _l('city'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
		]);
		$this->form_validation->set_rules('grade_id', _l('grade'), [
			'trim',
			'required',
			'numeric'
		]);
		$this->form_validation->set_rules('section_id', _l('section'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$user_info = $this->student_model->get($this->session->userdata('user_id'));

			if ($user_info['id'] != $this->input->post('user_id')) {
				return $this->json['success'] = _li('You are logged in with a different account');
			}

			if (!empty($user_info)) {
				if (
					!empty($user_info['city_id']) &&
					!empty($user_info['state_id']) &&
					!empty($user_info['site_id']) &&
					!empty($user_info['grade']) &&
					!empty($user_info['section'])
				) {
					return $this->json['success'] = _l('profile_successfully_saved');
				}

				if ($this->input->post('is_student') != 1) {
					return $this->json['success'] = _l('thanks_for_your_response');
				}

				$state_info = $this->state_model->get($this->input->post('state_id'));

				$this->student_model->edit($user_info['id'], [
					'country_id'			=> (int)$state_info['country_id'],
					'state_id'				=> (int)$this->input->post('state_id'),
					'city_id'				=> (int)$this->input->post('city_id'),
					'site_id'				=> (int)$this->input->post('site_id'),
					'grade_id'				=> $this->input->post('grade_id'),
					'section_id'			=> $this->input->post('section_id'),
					'grade'					=> $this->input->post('grade_id'),
					'section'				=> $this->input->post('section_id'),
				]);

				$this->json['success'] = _l('profile_successfully_saved');
			} else {
				$this->json['error'] = _li('Invalid user');
			}
		}
	}

	public function userEventInviteStatus() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('user_id', _l('user'), [
			'trim',
			'required',
			'numeric',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');

			$user_invite_info 	= $this->user_event_invitation_model->get_all([
				'event_id' 		=> $this->input->post('event_id'),
				'user_id' 		=> $this->input->post('user_id')
			])['rows'][0] ?? '';

			$this->json['user_data'] = $user_invite_info;
		}
	}

	public function eventCertificateStatus() {
		if (!$this->json) {
			if (
				$this->session->userdata('user_id')
			) {
				$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

				$template_info = $this->certificate_template_model->get_all([
					'event_id' 		=> $this->input->post('event_id') ?? 0,
					'achievement'	=> 2
				])['rows'] ?? [];

				$events = array_column($template_info, 'event_id');

				$cert_info = $this->certificate_model->get_all([
					'event_id'		=> $this->input->post('event_id') ?? 0,
					'user_id'		=> $this->session->userdata('user_id'),
					'achievement'	=> 2
				])['rows'][0] ?? '';

				$is_achievement 	= true;
				$is_league 			= true;
				$is_participation 	= false;

				if (!empty($events) && in_array($this->input->post('event_id'), $events) && !empty($cert_info)) {
					$is_participation = true;
				} else if ($this->input->post('event_id') == 0) {
					$is_participation = false;
					$is_league 		  = false;
				}

				$this->json['user'] = [
					'id' 					=> $this->session->userdata('user_id'),
					'is_achievement'		=> $is_achievement,
					'is_league'				=> $is_league,
					'is_participation'		=> $is_participation,
				];
			}

		}
	}

	public function getEventUserDetailsByCode() {
		$this->form_validation->set_rules('uid', _l('uid'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
			$user_info 	= $this->user_model->get_all(['user_id' => $this->input->post('uid'), 'code' => $this->input->post('code')])['rows'][0] ?? [];
			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (!empty($user_info)	) {
				if (!empty($this->input->post('event_id')) &&
					!empty($this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $user_info['id']))
				) {
					$this->json['error'] = _li('You_are_already_a_part_of_') . ($event_info['label'] ?? '');
				}

				$this->json['info'] = [
					'id'	   		=> $user_info['id'],
					'site_id'	   	=> $user_info['site_id'],
					'name'			=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
					'first_name'	=> ucwords($user_info['first_name']),
					'last_name'	 	=> ucwords($user_info['last_name']),
					'email'	 		=> $user_info['email'],
					'mobile'		=> $user_info['mobile'],
					'grade'   		=> $user_info['grade'],
					'section'   	=> $user_info['section'],
					'city_id'  		=> $user_info['city_id'],
					'state_id'	  	=> $user_info['state_id']
				];
			} else {
				$this->json['error'] = _li('Invalid url');
			}
		}
	}

	private function _sendEmailVerifyLink($email = false, $event_id = 0,  $lead_id = 0, $code = '', $type = '') {
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');
		$this->load->model('Alert_model', 'alert_model');

		if (!empty($email) && !empty($event_info = $this->event_model->get($event_id)) && !empty($lead_id) && !empty($code)) {
			$template_filter = [
				'template_type' => 'email_otp',
			];

			$school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';

			if (!empty($school_template_info)) {

				$variables = [
					'url'	=> sprintf($event_info['url'] . '/%s/signup?lid=%s&code=%s', $type, $lead_id, $code),
				];

				$subject = $this->alert_model->formatCommonEmailSubject($school_template_info['subject'], $variables) ?? '';

				$content = $this->alert_model->formatCommonEmailContent($school_template_info['body'], $variables) ?? '';

				$data['title']		  	= $subject;
				$data['heading']		= '';
				$data['subheading']	 	= '';
				$data['subheading']		= '';
				$data['content']		= $content;
				$data['site_id']		= 1;
				$data['site_code']		= '';
				$data['link']			= '';
				$data['link_text']		= '';
				$data['unsubscribe_url']= '';

				$message				= $this->load->view('common/mail/templates/site/general', $data, true);

				$attachment = [];

				if (!empty($subject) && !empty($content)) {
					$this->alert_model->email(
						$email,
						$subject,
						$message,
						[],
						ENVIRONMENT === 'production'
							? ['communication@bribooks.com']
							: [],
						$attachment
					);
				}

			}
		}
	}
}
