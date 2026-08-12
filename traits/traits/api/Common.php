<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Common {
	public function getCleverUser() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[5]|max_length[500]');
		$this->form_validation->set_rules('redirect_uri', _l('redirect_uri'), 'trim|required|min_length[5]|max_length[1000]');

		self::_runFormValidation();

		if (!$this->json) {
			$payload = [
				'code'			=> $this->input->post('code'),
				'grant_type'	=> 'authorization_code',
				'redirect_uri'	=> $this->input->post('redirect_uri'),
			];
			$token 	= base64_encode('8e2a2afacf22242f85f7' . ':' . '1c9372d7e6620f0978c0d342a4b189ce14aaeb6e');
			$result = _curl('https://clever.com/oauth/tokens', $payload, 'POST', ['Authorization: Basic ' . $token]);

			log_kb(['clever::' => $result]);

			// $this->json['init'] = $result;

			if (!empty($result['access_token'])) {
				$token = $result['access_token'];
				$result = _curl('https://api.clever.com/v3.0/me', [], 'GET', ['Authorization: Bearer ' . $token]);

				// $this->json['init_2'] = $result;

				if (!empty($result['data']['id'])) {
					$result = _curl('https://api.clever.com/v3.0/users/' . $result['data']['id'], [], 'GET', ['Authorization: Bearer ' . $token]);

					// $this->json['init_3'] = $result;

					$type = $result['type'] ?? '';

					self::_addCleverUser([
						'email' 		=> $result['data']['email'] ?? '',
						'first_name' 	=> $result['data']['name']['first'] ?? '',
						'last_name' 	=> ($result['data']['name']['middle'] ?? '') . ' ' . ($result['data']['name']['last'] ?? ''),
						'type' 			=> $type
					]);

					if (!$this->json) {
						$this->json['user'] = [
							'email' 		=> $result['data']['email'] ?? '',
							'first_name' 	=> $result['data']['name']['first'] ?? '',
							'last_name' 	=> ($result['data']['name']['middle'] ?? '') . ' ' . ($result['data']['name']['last'] ?? ''),
						];
					}
				}
			}
		}
	}

	private function _addCleverUser($data = []) {
		if (empty($data['email'])) return;
		if (empty($data['first_name'])) return;
		if (empty($data['type'])) return;

		$roles = [
			'user'    => 2,
			'teacher' => 3,
			'school'  => 9,
		];

		$role_id = $roles[strtolower($data['type'])] ?? 0;

		if ($user_info = $this->db->get_where('users', [
			'email' => $data['email']
		])->row_array()) {
			if ($user_info['role_id'] != $role_id) {
				$this->json['error'] = _l('email_is_already_register');
				return;
			}

			// add to event
			if (
				!empty($data['event_id']) &&
				$user_id &&
				empty($this->event_user_model->getEventUserByUserId($data['event_id'], $user_info['id']))
			) {
				$this->event_user_model->add([
					'event_id'	=> (int)$data['event_id'],
					'user_id'	=> (int)$user_info['id'],
				]);
			}

		} else {
			$this->db->select_max('id');
			$last_user_id = $this->db->get('users')->row_array()['id'];
			$last_user_id++;

			$last_user_id = sprintf('%06d', $last_user_id);

			$username = strtolower(trim(
				substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['first_name']), 0, 2) .
				substr($last_user_id, -6)
			));

			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

			$user_id = $this->student_model->add([
				'first_name'	=> $data['first_name'] ?? '',
				'last_name'		=> $data['last_name'] ?? '',
				'slug'			=> get_user_slug($username),
				'username'		=> $username,
				'password'		=> $encoded_password,
				'mobile'		=> $data['mobile'] ?? '',
				'email'			=> $data['email'] ?? '',
				'source'		=> $data['source'] ?? 'clever',
				'dob'			=> $data['dob'] ?? '',
				'country_id'	=> (int)($data['country_id'] ?? 0),
				'state_id'		=> (int)($data['state_id'] ?? 0),
				'city_id'		=> (int)($data['city_id'] ?? 0),
				'grade_id'		=> $data['grade'],
				'section_id'	=> $data['section'],
				'grade'			=> $data['grade'],
				'section'		=> $data['section'],
				'role_id'		=> 2,
				'site_id'		=> 1,
				'status'		=> 1,
				'location'		=> $data['location'] ?? '',
				'referral_code'	=> mb_strtoupper(uniqid()),
				'verification_code'	=> $verification_code,
				'ip'			=> $this->input->ip_address() ?? '',
				'timezone'		=> $data['timezone'] ?? '',
				'email_verified'	=> 1,
				'parent_referral_id'=> (int)($data['parent_referral_id'] ?? 0)
			]);

			// add to event
			if (
				!empty($data['event_id']) &&
				$user_id &&
				empty($this->event_user_model->getEventUserByUserId($data['event_id'], $user_id))
			) {
				$this->event_user_model->add([
					'event_id'	=> (int)$data['event_id'],
					'user_id'	=> (int)$user_id,
				]);
			}
		}
	}

	private function _updateOnline() {
		$this->load->library('user_agent');
		$this->load->library('Online_lib');

		if ($this->input->method() !== 'options') {
			if (empty($this->session->userdata('user_id'))) return;

			$this->online_lib->save((int)$this->session->userdata('user_id'), [
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'temp_id' 	=> get_bb_user_id(),
				'ip' 		=> $this->input->ip_address(),
				'url'		=> $this->input->server('REQUEST_URI'),
				'referer'	=> $this->input->server('HTTP_REFERER'),
				'browser'	=> !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser(),
				'platform'	=> !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform(),
			]);
		}
	}

	public function logClientError() {
		if (!$this->json) {
			log_kb(['Client Error:: ' => $this->input->post()]);

			$data = $this->input->post();

			if (!empty($data['error'])) {
				$data['error'] = is_array($data['error']) ? json_encode($data['error']) : $data['error'];

				if (strpos(strtolower($data['error']), 'invariant') !== false) return;

				log_kb_imp([
					'Client Error:: ' 	=> $data,
					'IP'				=> $this->input->ip_address(),
				]);
			}
		}
	}

	public function fakeApi() {
		if (!$this->json) {
			$this->json['success'] = 'OK';
		}
	}

	public function testApiLog() {
		if (!$this->json) {
			_jhkjhjksdf('ok');
		}
	}

	public function getAppConfig() {
		if (!$this->json) {
			if (version_compare($this->input->post('app_version'), '4.4.11', '<')) {
				$this->json['version'] 				= 1;
				$this->json['app_version'] 			= $this->input->post('app_version');
				$this->json['HOMEVIDEO'] 			= 'tJaGYl1m9b4';
				$this->json['WRITINGVIDEO'] 		= 'kYFoiaA8bBE';
				$this->json['FRONTCOVERVIDEO_IN'] 	= 'SD2SZIcQtAc';
				$this->json['FRONTCOVERVIDEO_GE'] 	= 'a2DrHShN-2E';
			} else {
				$this->json['version'] 				= 2;
				$this->json['app_version'] 			= $this->input->post('app_version');
				$this->json['HOMEVIDEO'] 			= 'public/EventGallery/APP-Videos/Android/Masterclass_by_Ami_Dror_How_to_Write_a_Great_Book-.mp4
';
				$this->json['WRITINGVIDEO'] 		= 'public/EventGallery/APP-Videos/Android/Exciting-New-Feature-How-to-Write-a-Book-on-BriBooks-in-Different-Genres.mp4
';
				$this->json['FRONTCOVERVIDEO_IN'] 	= 'public/EventGallery/APP-Videos/Android/Enjoy-The-New-BriBooks-Plus-Feature-On-Your-BriBooks-App.mp4
';
				$this->json['FRONTCOVERVIDEO_GE'] 	= 'public/EventGallery/APP-Videos/Android/Enjoy-The-New-BriBooks-Plus-Feature-On-Your-BriBooks-App.mp4
';
				$this->json['HOMEYVIDEO'] 			= 'tJaGYl1m9b4';
				$this->json['WRITINGYVIDEO'] 		= 'kYFoiaA8bBE';
				$this->json['FRONTCOVERYVIDEO_IN'] 	= 'SD2SZIcQtAc';
				$this->json['FRONTCOVERYVIDEO_GE'] 	= 'a2DrHShN-2E';
				$this->json['GENRE_LAYOUT'] 		= 2;
			}
		}
	}

	public function getAppUpdate() {
		$version_info = $this->db->get_where('app_version', [
			'device_type'	=> mb_strtolower($this->input->post('app_os')) === 'ios' ? 2 : 1,
			'_deleted'		=> 0,
		])->row_array();

		if (!empty($version_info['version']) && version_compare($this->input->post('app_version'), $version_info['version'], '<')) {
			$this->json['status'] = 1;
		}
	}

	public function getAppBanner() {
		if (!$this->json) {
			if (strtolower($this->config->item('site_country_code')) === 'in') {
				$banner_info = $this->db->get_where('app_banner', [
					'status'	=> 1,
					'type'		=> 0,
					'_deleted'	=> 0,
				])->row_array();

				$this->json['banner']['image'] = $this->input->post('is_tablet')
					? ($banner_info['tab_image'] ?? '')
					: ($banner_info['image'] ?? '');
				$this->json['banner']['id'] = $banner_info['id'] ?? '';
				$this->json['banner']['action'] = $banner_info['action'] ?? '';
			}

			$version_info = $this->db->get_where('app_version', [
				'device_type'	=> mb_strtolower($this->input->post('app_os')) === 'ios' ? 2 : 1,
				'_deleted'		=> 0,
			])->row_array();

			if (!empty($version_info['version']) && version_compare($this->input->post('app_version'), $version_info['version'], '<')) {
				$banner_info = $this->db->get_where('app_banner', [
					'status'	=> 1,
					'type'		=> 1,
					'_deleted'	=> 0,
				])->row_array();

				$this->json['banner']['image'] = $this->input->post('is_tablet')
					? ($banner_info['tab_image'] ?? '')
					: ($banner_info['image'] ?? '');
				$this->json['banner']['id'] = date('Ymd') . ($banner_info['id'] ?? '');

				unset($this->json['banner']['action']);

				$this->json['banner']['link'] = strtolower($this->input->post('app_os')) != 'ios'
					? 'https://play.google.com/store/apps/details?id=com.bribooks&pli=1'
					: 'https://apps.apple.com/us/app/bribooks/id6448090977'
				;
			}
		}
	}

	public function updateDeviceToken() {
		$this->form_validation->set_rules('device_type', _l('device_type'), 'trim|required|min_length[1]|max_length[1]');
		$this->form_validation->set_rules('device_token', _l('device_token'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($this->session->userdata('user_id'))) {
				$this->load->model('user/UserDeviceToken_model', 'user_device_token_model');

				if (!empty($this->user_device_token_model->getByUser($this->session->userdata('user_id')))) {
					$this->user_device_token_model->editByUser($this->session->userdata('user_id'), [
						'device_type' 	=> $this->input->post('device_type'),
						'device_token' 	=> $this->input->post('device_token'),
						'app_version' 	=> $this->input->post('app_version'),
					]);
				} else {
					$this->user_device_token_model->add([
						'user_id' 		=> $this->session->userdata('user_id'),
						'device_type' 	=> $this->input->post('device_type'),
						'device_token' 	=> $this->input->post('device_token'),
						'app_version' 	=> $this->input->post('app_version'),
					]);
				}
				$this->json['success'] = _li('Device token has been updated!');
			} else {
				$this->json['error'] = _li('Invalid user!');
			}
		}
	}

	public function saveContact() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[4]|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		// $this->form_validation->set_rules('message', _l('message'), 'trim|required|min_length[3]|max_length[600]');

		self::_runFormValidation();

		if (!$this->json) {
			$this->db->insert('contact_us', [
				'name'				=> $this->input->post('name'),
				'mobile'			=> $this->input->post('mobile'),
				'email'				=> $this->input->post('email'),
				'message'			=> $this->input->post('message'),
				'usertype'			=> $this->input->post('usertype_id'),
				'book_title'		=> $this->input->post('book_title'),
				'published_book'	=> $this->input->post('published_book'),
				'aspiring'			=> $this->input->post('aspiring_author'),
				'city'				=> $this->input->post('city_id'),
				'state'				=> $this->input->post('state_id'),
				'country'			=> $this->input->post('country_id'),
				'school_name'		=> $this->input->post('other_school') ? $this->input->post('other_school') : $this->input->post('school_id'),
				'youngauthor'		=> $this->input->post('youngauthor'),
				'opportunities'		=> $this->input->post('opportunities'),
				'resume'			=> $this->input->post('resume'),
				'date_added' 		=> date('Y-m-d H:i:s'),
			]);

			$request_city 	= $this->input->post('city_id');
			$request_school = $this->input->post('school_id');

			if (isset($request_city)) {
				$city = $this->city_model->get($request_city);
			}

			$upload_folder = $this->config->item('s3_resume');

			if (!empty($_FILES['resume']['name'])) {
				if (self::_validateFileUpload('image')) {
					$file_temp_name = $_FILES['resume']['tmp_name'];
					$image_name 	= $_FILES['resume']['name'];

					$file_name = $this->s3->amazonS3Upload($image_name, $file_temp_name, $upload_folder);
				}
			}

			if (isset($request_school)) {
				$school = $this->schoolinput_model->get($request_school);
			}

			$this->alert_model->contactUsAlert([
				'name'				=> $this->input->post('name'),
				'mobile'			=> $this->input->post('mobile'),
				'email'				=> $this->input->post('email'),
				'message'			=> $this->input->post('message'),
				'usertype'			=> $this->input->post('usertype_id'),
				'book_title'		=> $this->input->post('book_title'),
				'published_book'	=> $this->input->post('published_book'),
				'aspiring'			=> $this->input->post('aspiring_author'),
				'city_id'			=> $city['name'],
				'state_id'			=> $city['state'],
				'country_id'		=> $this->input->post('country_id'),
				'school_id'			=> $this->input->post('other_school')?$this->input->post('other_school'):$school['name'],
				'youngauthor'		=> $this->input->post('youngauthor'),
				'opportunities'		=> $this->input->post('opportunities'),
				'resume'			=> $this->input->post('resume'),
				'alerts'			=> true,
			]);

			$this->json['success'] = _li('Your query has been submitted!');
		}
	}

	public function getAuthors() {
		if (!$this->json) {
			$filter_data = [
				'start'			=> $this->input->post('page') > 0 ? ($this->input->post('page') - 1) * 16 : 0,
				'limit'			=> 16,
				'sort'			=> 'sold',
				'order'			=> 'DESC',
				'location'		=> 'india',
				'quantity_ge'	=> '1',
			];

			$user_country_code = strtolower($this->input->cookie('user_country_code'));

			if (empty($user_country_code)) {
				$user_country_code = strtolower($this->config->item('site_country_code'));
			}

			if (!empty($user_country_code) && $user_country_code !== 'in') {
				$filter_data['ne_location'] = $filter_data['location'];
				unset($filter_data['location']);
			}

			$cache_key = vsprintf('%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'get_authors',
				implode('_', array_keys($filter_data)),
				str_replace(' ', '', implode('_', array_values($filter_data))),
			]);

			$authors = json_decode($this->cache->get($cache_key), true);

			log_kb(['getAuthors::cache_data::' => $authors]);

			if (empty($authors)) {
				$result = $this->bookstore_model->get_all($filter_data);

				$authors = [];

				foreach ($result['rows'] ?? [] as $item) {
					$student_info = $this->student_model->get($item['user_id']);

					if ($student_info['slug']) {
						$authors[] = [
							'id'		=> $student_info['id'],
							'name'		=> $student_info['first_name'] . ' ' . $student_info['last_name'],
							'image'		=> $student_info['image'],
							'slug'		=> $student_info['slug'],
						];
					}
				}

				$this->cache->save($cache_key, json_encode($authors), 4 * 3600);
			}

			$this->json['authors'] = $authors;
		}
	}

	public function getSites() {
		if (!$this->json) {
			$filter_data = [
				'status'	=> 1,
			];

			$filter_data['limit'] = 10;
			$filter_data['offset'] = $this->input->post('page') > 0
				? ($this->input->post('page') - 1) * 10
				: 0;

			$result = $this->site_model->get_all($filter_data)['rows'] ?? [];

			$sites = [];

			foreach ($result as $item) {
				$sites[] = [
					'id'		=> $item['id'],
					'name'		=> $item['name'],
					'slug'		=> $item['site_code'],
				];
			}

			$this->json['sites'] = $sites;
		}
	}

	public function botSuggestions() {
		$payload = [
			'text'		=> $this->input->post('text'),
			'category'	=> $this->input->post('category'),
			'type'		=> $this->input->post('type'),
		];

		$this->json = self::_curl(
			'https://s3uy2bp00c.execute-api.us-east-1.amazonaws.com/default/Dgpt2Function',
			$payload
		);
	}

	public function grammmer() {
	}

	private function _getLocality($data = []) {
		$token = self::_getShippingToken();

		$payload = http_build_query($data);

		$locality = self::_curl(
			'https://apiv2.shiprocket.in/v1/external/open/postcode/details?' . $payload,
			null,
			'GET',
			['Authorization: Bearer ' . $token]
		);

		log_kb([
			'locality'	=> $locality
		]);

		return $locality;
	}

	private function _getShippingToken() {
		$token_file = FCPATH . 'uploads/ship_token_file_briboo_kb_tok_file.php';

		if (!is_file($token_file) || (filemtime($token_file) + (9 * 24 * 3600)) < time()) {
			$result = self::_curl(
				'https://apiv2.shiprocket.in/v1/external/auth/login',
				[
					'email'			=> SHIPROCKET['email'],
					'password'		=> SHIPROCKET['password'],
				],
				'POST'
			);

			log_kb(['Shiprocket Token' => $result]);

			$token = $result['token'] ?? '';

			file_put_contents($token_file, $token);

			return $token;
		} else {
			return file_get_contents($token_file);
		}
	}

	private function _verifyCaptcha() {
		if ($this->input->post('app_os')) {
			return true;
		}

		if ($this->input->post('captcha_token')) {
			$result = self::_curl(
				'https://www.google.com/recaptcha/api/siteverify',
				[
					'secret'	=> RECAPTCHA_SECRET,
					'response'	=> $this->input->post('captcha_token'),
					'remoteip'	=> $this->input->ip_address(),
				],
				'POST',
				[],
				'form'
			);

			log_kb(['captcha_token::validation' => $result]);

			return ($result['success'] ?? 0) == 1;
		} else {
			return false;
		}
	}

	public function saveBotLogs() {
		$this->form_validation->set_rules('user_id', _l('user_id'), 'trim|required|numeric');
		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('user_agent');

			$data = $this->input->post();

			$this->bot_logs_model->add([
				'user_id' 	=> $data['user_id'],
				'browser'	=> !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser(),
				'platform'	=> !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform(),
				'ip'		=> $this->input->ip_address(),
				'payload' 	=> json_encode([
					'user_id' 	=> $data['user_id'] ?? '',
					'grade' 	=> $data['grade'] ?? '',
					'location' 	=> $data['location'] ?? '',
					'category' 	=> $data['category'] ?? '',
					'theme' 	=> $data['theme'] ?? '',
					'text' 		=> $data['text'] ?? '',
				]),
				'response' 	=> json_encode($data['response'] ?? []),
			]);

			$this->json['success'] = _li('bot_logs_saved_successfully!');
		}
	}

	public function homePageCount() {
		if (!$this->json) {

			$cache_key = vsprintf('%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'homePageCount',
			]);

			$result 	= json_decode($this->cache->get($cache_key), true);
			$count_data = $result['count_data'] ?? [];

			if (empty($count_data)) {
				$author_count = $this->db
					->select('count(id) as author_count')
					->from('users')
					->where('users.role_id', 2)
					->where('users._deleted', 0)
					->get()
					->row_array()['author_count'] ?? 0;

				$written_book_count = $this->db
					->select('count(id) as written_book_count')
					->from('book')
					->where('book._deleted', 0)
					->get()
					->row_array()['written_book_count'] ?? 0;

				$published_book_count = $this->db
					->select('count(id) as published_book_count')
					->from('book')
					->where('book.status', 1)
					->where('book._deleted', 0)
					->where('book.archived', 0)
					->get()->row_array()['published_book_count'] ?? 0;

				$count_data = [
					'author_count' 			=> $author_count,
					'written_book_count' 	=> $written_book_count,
					'published_book_count' 	=> 500000 + $published_book_count
				];

				$this->cache->save($cache_key, json_encode(['count_data' => $count_data]), 3 * 3600);
			}

			$this->json['count_data'] = $count_data;
		}
	}

	public function updateMobile() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[school,student]');
		$this->form_validation->set_rules('is_txt_msg_enable', _l('is_txt_msg_enable'), 'trim|in_list[0,1]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);
		$this->form_validation->set_rules('lead_id', _l('lead_id'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if ($user_info = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('mobile'),
				// 'role_id'		=> ($this->input->post('type') == 'school') ? 9 : 2,
			])->row_array()) {
				$this->json['error'] = _li('already_registered_mobile_no');
			}
		}

		if (!$this->json) {
			return $this->json;

			if (($this->input->post('type') == 'school') && !empty($lead_info = $this->school_lead_model->get($this->input->post('lead_id')))) {
				$this->school_lead_model->edit($this->input->post('lead_id'), [
					'mobile'		=> $this->input->post('mobile')
				]);

				if (!empty($lead_info['site_id']) && !empty($site_info = $this->site_model->get($lead_info['site_id']))) {
					$update = [];
					$update['owner_mobile'] 	= $this->input->post('mobile');
					$update['date_modified'] 	= date('Y-m-d H:i:s');

					$this->db->where('id', $site_info['id']);
					$this->db->update('site', $update);

					$update = [];
					$update['mobile'] 			= $this->input->post('mobile');
					$update['date_modified'] 	= date('Y-m-d H:i:s');

					$this->db->where('site_id', $site_info['id']);
					$this->db->where('role_id', 9);
					$this->db->update('users', $update);
				}
			} elseif ($this->input->post('type') == 'student' && !empty($lead_info = $this->lead_model->get($this->input->post('lead_id')))) {
				$this->lead_model->edit($this->input->post('lead_id'), [
					'mobile'		=> $this->input->post('mobile')
				]);

				$this->student_model->edit($lead_info['student_id'], [
					'mobile'		=> $this->input->post('mobile')
				]);
			}

			if (empty($this->input->post('is_txt_msg_enable')) && empty($this->db->get_where('unsubscribed', [
				'email'	=> $this->input->post('mobile')
			])->row_array())) {
				$this->load->model('user/Unsubscribed_model', 'unsubscribed_model');

				$this->unsubscribed_model->add([
					'email'		=> $this->input->post('mobile')
				]);
			}

			$this->json['success'] = _li('Your details has been updated!');
		}
	}
	private function _curl($url, $payload, $method = 'POST', $headers = [], $type = 'json') {
		return _curl($url, $payload, $method, $headers, $type);
	}
}
