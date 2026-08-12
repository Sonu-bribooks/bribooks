<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait School {
	public function getSchool() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$site_info = $this->site_model->getSchoolBySiteId($this->input->post('site_id'));

			if (!empty($site_info) && $site_info['verified'] == 1) {
				$this->json['error'] = _li('Congratulations! Your school is already registered.');
			} elseif (!empty($site_info)) {
				$site_slug = preg_replace(['/[^\w\s]/', '/\s+/'], ['', ''], mb_strtolower($site_info['name']));

				if (strtolower($site_info['email']) == strtolower($site_slug.'@bribooks.com')) {
					$site_info['email'] = '';
				}

				if (strtolower($site_info['city']) == 'other') {
					$site_info['city'] = '';
				}

				$site_info['image'] = !empty($site_info['image']) ? $this->config->config["s3_base_url"] . "public/SiteImages/" . $site_info['image'] : '';

				$this->json['school'] = $site_info;
			} else {
				$this->json['error'] = _li('school_not_found');
			}
		}
	}

	public function getSchoolBySiteId() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$site_type = 0;
			if (!empty($this->input->post('site_type'))) {
				$site_type = $this->input->post('site_type');
			}

			if (!empty($this->input->post('event_id'))) {
				$site_info = [];

				if (!empty($event_site_info = $this->event_site_model->getDataByEventId($this->input->post('event_id'), ['site_id' => $this->input->post('site_id'), 'site_type' => $site_type]))) {
					$site_info = $event_site_info[0];
					$site_info['id'] = $event_site_info[0]['site_id'];
				}
			} else {
				$site_info = $this->site_model->getSchoolBySiteId($this->input->post('site_id'), $site_type);
			}

			if (!empty($site_info)) {
				$site_info['site_id'] = $site_info['id'];

				$site_slug = preg_replace(['/[^\w\s]/', '/\s+/'], ['', ''], mb_strtolower($site_info['name']));

				if (strtolower($site_info['email']) == strtolower($site_slug.'@bribooks.com')) {
					$site_info['email'] = '';
				}

				if (strtolower($site_info['city']) == 'other') {
					$site_info['city'] = '';
				}

				$site_info['image'] = !empty($site_info['image']) ? $this->config->config["s3_base_url"] . "public/SiteImages/" . $site_info['image'] : '';

				$this->json['school'] = $site_info;
			} else {
				$this->json['error'] = _li('The_Site_Id_Is_Not_Valid');
			}
		}
	}

	public function getSchoolBySiteForEarlyAccess() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$site_info = $this->school_lead_model->getSchoolLeadBySiteId($this->input->post('site_id'));

			if ($site_info) {
				$this->json['school'] = $site_info;
			} else {
				$this->json['error'] = _li('school_not_found');
			}
		}
	}

	public function getSchools() {
		$this->form_validation->set_rules('institute_type', _l('institute_type'), 'trim|in_list[1,2,3]');
		$this->form_validation->set_rules('code', _l('code'), 'trim');
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim');
		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'numeric'
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$site_type = $this->input->post('site_type') ?? 1;

			if (!empty($this->input->post('code')) && ($site_info = $this->site_model->getByCode($this->input->post('code'), $site_type))) {
				$this->json['schools'] = array_map(function($item) {
					return [
						'id'		=> $item['id'],
						'site_code'	=> $item['site_code'],
						'name'		=> ucwords($item['name']),
						'verified'	=> $item['verified'],
					];
				}, $this->site_model->get_all([
					'parent_id'	=> (int)$site_info['id'],
					'sort'		=> 'site.name',
					'order'		=> 'ASC',
				])['rows'] ?? []);
			} elseif (!empty($this->input->post('city_id')) && ($city_info = $this->city_model->get($this->input->post('city_id')))) {
				$this->json['schools'] = array_map(function($item) {
					return [
						'id'		=> $item['id'],
						'site_code'	=> $item['site_code'],
						'name'		=> ucwords($item['name']),
						'verified'	=> $item['verified'],
					];
				}, $this->site_model->get_all([
					'city_id'	=> (int)$city_info['id'],
					'site_type'	=> $site_type,
					'sort'		=> 'site.name',
					'order'		=> 'ASC',
				])['rows'] ?? []);

				if (0 && !empty($this->input->post('site_code')) && (strtolower($this->input->post('site_code')) == strtolower(NYAF_US_SITE_CODE))) {
					array_unshift($this->json['schools'], [
						'id'		=> 2270,
						'site_code'	=> 'ge-NYAFUS-de',
						'name'		=> _li('Direct Online Registration'),
					]);
				} elseif (!empty($this->input->post('site_code')) && (strtolower($this->input->post('site_code')) == strtolower(SUMMER_CAMP_SITE_CODE))) {
					array_unshift($this->json['schools'], [
						'id'		=> 727,
						'site_code'	=> 'in-sc-de',
						'name'		=> _li('Direct Online Registration'),
					]);
				} elseif (!empty($this->input->post('site_code')) && (strtolower($this->input->post('site_code')) == strtolower(UAE_SITE_CODE))) {
					if($site_type == 1) {
						array_unshift($this->json['schools'], [
							'id'		=> 2098,
							'site_code'	=> 'ge-UAE-de',
							'name'		=> _li('Direct Online Registration'),
						]);
					} elseif ($site_type == 2) {
						array_unshift($this->json['schools'], [
							'id'		=> 2099,
							'site_code'	=> 'ge-UAE-dn',
							'name'		=> _li('Direct Online Registration'),
						]);
					}
				} elseif (!empty($this->input->post('site_code')) && (strtolower($this->input->post('site_code')) == strtolower(NYAF_SITE_CODE))) {
					array_unshift($this->json['schools'], [
						'id'		=> 265,
						'site_code'	=> 'NYAFIND2022BB',
						'name'		=> _li('Direct Online Registration'),
					]);
				}
			} elseif (!empty($this->input->post('site_id')) && ($site_info = $this->site_model->get($this->input->post('site_id')))) {
				$this->json['schools'] = array_map(function($item) {
					return [
						'id'		=> $item['id'],
						'site_code'	=> $item['site_code'],
						'name'		=> ucwords($item['name']),
						'verified'	=> $item['verified'],
					];
				}, $this->site_model->get_all([
					'parent_id'		=> (int)$site_info['id'],
					'site_type'		=> $site_type,
					'state_id_ne'	=> 0,
					'sort'			=> 'site.name',
					'order'			=> 'ASC',
				])['rows'] ?? []);
			}
		}
	}

	public function searchSchool() {
		$this->form_validation->set_rules('search', _l('search'), 'trim|required|min_length[3]|max_length[155]');

		self::_runFormValidation();

		if (!$this->json) {
			$this->json['schools'] = array_map(function($item) {
				return [
					'id'		=> $item['id'],
					'site_code'	=> $item['site_code'],
					'name'		=> ucwords($item['name']),
				];
			}, $this->site_model->get_all([
				'parent_id'		=> 2269,
				'search_name'	=> $this->input->post('search'),
				'verified'		=> 0,
				'sort'			=> 'site.name',
				'order'			=> 'ASC',
				'start'			=> 0,
				'limit'			=> 50,
			])['rows'] ?? []);
		}
	}

	public function getSchoolBooks() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('sort', _l('sort'), 'trim|in_list[name,sold]');
		$this->form_validation->set_rules('order', _l('order'), 'trim|in_list[asc,desc]');
		$this->input->post('event_id') && $this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			// if (
			// 	empty($this->session->userdata('user_id')) ||
			// 	(!in_array($this->session->userdata('user_role_id'), [9, 3]))
			// ) {
			// 	return $this->json['error'] = _l('unauthorized');
			// }

			self::_getSchoolEventBooks($this->input->post());
		}
	}

	public function getSchoolEventBooks() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('sort', _l('sort'), 'trim|in_list[name,sold]');
		$this->form_validation->set_rules('order', _l('order'), 'trim|in_list[asc,desc]');
		$this->input->post('event_id') && $this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			self::_getSchoolEventBooks($this->input->post());
		}
	}

	private function _getSchoolEventBooks($data = []) {
		$filter_data = [
			'status'	=> 1,
			'site_id' 	=> (int)$data['site_id'] ?? 0,
			'event_id' 	=> $data['event_id'] ?? 0,
			'start'		=> ($data['page'] ?? 0) > 0 ? ($data['page'] - 1) * 10 : 1,
			'limit'		=> $data['limit'] ?? 10,
			'sort'		=> !empty($data['sort'] ?? '') ? ('bookstore.' . $data['sort']) : 'bookstore.name',
			'order'		=> mb_strtoupper($data['order'] ?? 'ASC'),
		];

		if (!empty($data['year'])) {
			$filter_data['startdate'] 	= date('Y-m-d', strtotime(sprintf('%d-03-01', $data['year'])));
			$filter_data['enddate'] 	= date('Y-m-d', strtotime(sprintf('%d-03-31', $data['year'] + 1)));
		}

		if (!empty($data['grade'])) {
			$filter_data['grade'] = $data['grade'];
		}

		if (!empty($data['section'])) {
			$filter_data['section'] = $data['section'];
		}

		if (!empty($data['search'])) {
			$filter_data['search'] = $data['search'];
		}

		$result = $this->bookstore_model->get_all($filter_data);

		$books = $result['rows'] ?? [];

		foreach ($books as &$book) {
			$author_info 	= $this->student_model->get($book['user_id']);

			$book['price'] 	= $this->book_model->getPrice($book['id']);
			$book['grade']	= $author_info['grade'] ?? '';
			$book['section']= $author_info['section'] ?? '';
		}

		$this->json['books'] = $books;
		$this->json['total'] = $result['total'] ?? 0;
	}

	private function _validateSchoolTeacherAccess() {
		if (
			!empty($user_info = $this->user_model->get($this->session->userdata('user_id'))) &&
			in_array($user_info['role_id'], [3, 9])
		) {
			$filter_data = [
				'book_id'	=> (int)$this->input->post('book_id'),
				'site_id'	=> (int)$user_info['site_id'],
			];

			if ($user_info['role_id'] == 3) {
				$filter_data['grade'] 	= $user_info['grade'];
				$filter_data['section'] = $user_info['section'];
			}

			$result = $this->bookstore_model->get_all($filter_data);

			return $result['total'] > 0;
		}

		return false;
	}
}
