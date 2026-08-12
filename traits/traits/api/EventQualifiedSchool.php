<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventQualifiedSchool {
	public function getEventQualifiedSchool() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('city_id', _l('city'), [
			'trim',
			'numeric'
		]);

		$this->form_validation->set_rules('type', _l('type'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [
				'status'	=> 1,
				'start'		=> $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 10
					: 0,
				'limit'		=> 10,
				'sort'		=> 'site.name',
				'order'		=> 'ASC',
			];

			if (!empty($this->input->post('event_id'))) {
				$filter_data['event_id'] = $this->input->post('event_id');
			}

			if (!empty($this->input->post('city_id'))) {
				$filter_data['city_id'] = $this->input->post('city_id');
			}

			if (!empty($this->input->post('state_id'))) {
				$filter_data['state_id'] = $this->input->post('state_id');
			}

			if (!empty($this->input->post('country_id'))) {
				$filter_data['country_id'] = $this->input->post('country_id');
			}

			if (!empty($this->input->post('type'))) {
				$filter_data['type'] = $this->input->post('type');
			}

			if (!empty($this->input->post('search'))) {
				$filter_data['search'] = $this->input->post('search');
			}

			$user_rank = [];

			if (!empty($this->input->post('site_id')) &&
				!empty($site_info = $this->qualified_school_model->get_all([
					'event_id' 	=> $this->input->post('event_id'),
					'city_id' 	=> $this->input->post('city_id') ?? '',
					'state_id' 	=> $this->input->post('state_id') ?? '',
					'country_id'=> $this->input->post('country_id') ?? '',
					'site_id' 	=> $this->input->post('site_id'),
					'type' 		=> $this->input->post('type') ?? '',
				])['rows'][0] ?? [])
			) {
				$registered_author = $this->student_model->get_all([
					'site_id' 	=> $site_info['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id')
				])['total'];

				$publish_school_books = $this->bookstore_model->get_all([
					'status'	=> 1,
					'site_id' 	=> $site_info['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id')
				])['total'];

				$city_info = $this->city_model->get($site_info['city_id']);

				$user_rank = [
					'event_id' 		=> $site_info['event_id'],
					'site_id' 		=> $site_info['site_id'],
					'school_name' 	=> $site_info['school_name'],
					'site_code' 	=> $site_info['site_code'],
					'country_id' 	=> $this->input->post('country_id') ?? 0,
					'city_id' 		=> $site_info['city_id'],
					'state_id' 		=> $site_info['state_id'],
					'city' 			=> $city_info['name'] ?? '',
					'state' 		=> $city_info['state'] ?? '',
					'rank' 			=> 0,
					'registered' 	=> $registered_author,
					'score' 		=> $publish_school_books
				];
			}

			$schools = [];
			$results = $this->qualified_school_model->get_all($filter_data);

			foreach($results['rows'] as $result) {
				$registered_student = $this->student_model->get_all([
					'site_id' 	=> $result['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id')
				])['total'];

				$school_books = $this->bookstore_model->get_all([
					'status'	=> 1,
					'site_id' 	=> $result['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id')
				])['total'];

				$city_info = $this->city_model->get($result['city_id']);

				$schools[] = [
					'event_id' 		=> $result['event_id'],
					'site_id' 		=> $result['site_id'],
					'school_name' 	=> $result['school_name'],
					'site_code' 	=> $result['site_code'],
					'country_id' 	=> $this->input->post('country_id') ?? 0,
					'city_id' 		=> $result['city_id'],
					'state_id' 		=> $result['state_id'],
					'city' 			=> $city_info['name'] ?? '',
					'state' 		=> $city_info['state'] ?? '',
					'rank' 			=> 0,
					'registered' 	=> $registered_student,
					'score' 		=> $school_books
				];
			}

			$this->json['schools'] 		= $schools;
			$this->json['total'] 		= $results['total'];
			$this->json['user_rank'] 	= $user_rank;
			$this->json['filter_data'] 	= $filter_data;
		}
	}

	public function getEventQualifiedTeacher() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('site_id', _l('site'), [
			'trim',
			'numeric'
		]);

		$this->form_validation->set_rules('type', _l('type'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [
				'status'	=> 1,
				'start'		=> $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 10
					: 0,
				'limit'		=> 10,
				'sort'		=> 'users.grade',
				'order'		=> 'ASC',
			];

			if (!empty($this->input->post('event_id'))) {
				$filter_data['event_id'] = $this->input->post('event_id');
			}

			if (!empty($this->input->post('site_id'))) {
				$filter_data['site_id'] = $this->input->post('site_id');
			}

			if (!empty($this->input->post('city_id'))) {
				$filter_data['city_id'] = $this->input->post('city_id');
			}

			if (!empty($this->input->post('state_id'))) {
				$filter_data['state_id'] = $this->input->post('state_id');
			}

			if (!empty($this->input->post('country_id'))) {
				$filter_data['country_id'] = $this->input->post('country_id');
			}

			if (!empty($this->input->post('type'))) {
				$filter_data['type'] = $this->input->post('type');
			}

			if (!empty($this->input->post('search'))) {
				$filter_data['search'] = $this->input->post('search');
			}

			$user_rank = [];

			if (!empty($this->input->post('teacher_id')) &&
				!empty($teacher_info = $this->qualified_school_model->get_qualified_teacher([
					'event_id' 		=> $this->input->post('event_id'),
					'site_id' 		=> $this->input->post('site_id'),
					'teacher_id' 	=> $this->input->post('teacher_id'),
					'city_id' 		=> $this->input->post('city_id') ?? '',
					'state_id' 		=> $this->input->post('state_id') ?? '',
					'country_id' 	=> $this->input->post('country_id') ?? '',
					'type' 			=> $this->input->post('type')
				])['rows'][0])
			) {
				$teacher_student = $this->student_model->get_all([
					'site_id' 	=> $teacher_info['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id'),
					'grade' 	=> $teacher_info['grade'],
					'section' 	=> $teacher_info['section'],
				])['total'];

				$teacher_score = $this->bookstore_model->get_all([
					'status'	=> 1,
					'site_id' 	=> $teacher_info['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id'),
					'grade' 	=> $teacher_info['grade'],
					'section' 	=> $teacher_info['section'],
				])['total'];

				$city_info = $this->city_model->get($teacher_info['city_id']);

				$user_rank = [
					'event_id' 		=> $teacher_info['event_id'],
					'teacher_id' 	=> $teacher_info['teacher_id'],
					'site_id' 		=> $teacher_info['site_id'],
					'name' 			=> $teacher_info['name'],
					'city_id' 		=> $teacher_info['city_id'],
					'state_id' 		=> $teacher_info['state_id'],
					'city' 			=> $city_info['name'] ?? '',
					'state' 		=> $city_info['state'] ?? '',
					'grade' 		=> $teacher_info['grade'],
					'section' 		=> $teacher_info['section'],
					'school_name' 	=> $teacher_info['school_name'] ?? '',
					'registered' 	=> $teacher_student,
					'score' 		=> $teacher_score,
				];
			}

			$teachers 	= [];
			$results 	= $this->qualified_school_model->get_qualified_teacher($filter_data);

			foreach($results['rows'] as $result) {
				$registered_student = $this->student_model->get_all([
					'site_id' 	=> $result['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id'),
					'grade' 	=> $result['grade'],
					'section' 	=> $result['section'],
				])['total'];

				$score = $this->bookstore_model->get_all([
					'status'	=> 1,
					'site_id' 	=> $result['site_id'],
					'event_id' 	=> (int)$this->input->post('event_id'),
					'grade' 	=> $result['grade'],
					'section' 	=> $result['section'],
				])['total'];

				$city_info = $this->city_model->get($result['city_id']);

				$teachers[] = [
					'event_id' 		=> $result['event_id'],
					'teacher_id' 	=> $result['teacher_id'],
					'site_id' 		=> $result['site_id'],
					'name' 			=> $result['name'],
					'city_id' 		=> $result['city_id'],
					'state_id' 		=> $result['state_id'],
					'city' 			=> $city_info['name'] ?? '',
					'state' 		=> $city_info['state'] ?? '',
					'grade' 		=> $result['grade'],
					'section' 		=> $result['section'],
					'school_name' 	=> $result['school_name'] ?? '',
					'registered' 	=> $registered_student,
					'score' 		=> $score,
				];
			}

			$this->json['teachers'] 	= $teachers;
			$this->json['total'] 		= $results['total'];
			$this->json['user_rank'] 	= $user_rank;
			$this->json['filter_data'] 	= $filter_data;
		}
	}

	public function getEventQualifiedBook() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('school_id', _l('school'), [
			'trim',
			'numeric'
		]);

		$this->form_validation->set_rules('city_id', _l('city'), [
			'trim',
			'numeric'
		]);

		$this->form_validation->set_rules('state_id', _l('state'), [
			'trim',
			'numeric'
		]);

		$this->form_validation->set_rules('country_id', _l('country'), [
			'trim',
			'numeric'
		]);

		$this->form_validation->set_rules('type', _l('type'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [
				'event_id'	=> $this->input->post('event_id'),
				'status'	=> 1,
				'start'		=> $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 10
					: 0,
				'limit'		=> 10,
			];

			if (!empty($this->input->post('school_id'))) {
				$filter_data['site_id'] = $this->input->post('school_id');
			}

			if (!empty($this->input->post('city_id'))) {
				$filter_data['city_id'] = $this->input->post('city_id');
			}

			if (!empty($this->input->post('state_id'))) {
				$filter_data['state_id'] = $this->input->post('state_id');
			}

			if (!empty($this->input->post('country_id'))) {
				$filter_data['country_id'] = $this->input->post('country_id');
			}

			if (!empty($this->input->post('type'))) {
				$filter_data['type'] = $this->input->post('type');
			}

			if (!empty($this->input->post('search'))) {
				$filter_data['search'] = $this->input->post('search');
			}

			$user_rank = [];

			if (empty($this->input->post('search')) && !empty($this->input->post('book_id')) &&
				!empty($user_book_info = $this->event_book_qualification_pending_model->get_all([
					'event_id' 	=> $this->input->post('event_id'),
					'city_id' 	=> $this->input->post('city_id') ?? '',
					'state_id' 	=> $this->input->post('state_id') ?? '',
					'country_id'=> $this->input->post('country_id') ?? '',
					'book_id' 	=> $this->input->post('book_id'),
					'user_id' 	=> $this->input->post('user_id'),
					'type' 		=> $this->input->post('type') ?? '',
				])['rows'][0] ?? [])
			) {
				$user_rank = $user_book_info;
			}

			$results = $this->event_book_qualification_pending_model->get_all($filter_data);

			$this->json['ranks'] 		= $results['rows'];
			$this->json['total'] 		= $results['total'];
			$this->json['user_rank'] 	= $user_rank;
			$this->json['filter_data'] 	= $filter_data;
		}
	}
}
