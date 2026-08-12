<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EmailTemplates {
	public $user_template_types = [
		'email_otp',
		'email_user_signup',
		'email_user_signup_mobile',
		'email_reviewer_signup',
		'email_user_signup_nursery',
		'email_user_signup_university',
		'email_user_signup_community',
		'email_user_tnc',
		'email_user_tnc_nursery',
		'email_user_tnc_university',
		'email_forgot_password',
		'email_reset_password',
		'email_competition_signup',
		'email_send_coupon',
		'email_referral_user_signup',
		'book_approved_nyaf',
		'singup_yaf',
		'yaf_acknowledgement',
		'yaf_request_autoapproval_24hr',
		'yaf_invite_yaf_published',
		'yaf_invite_yaf_unpublished',
		'yaf_invite_summercamp_unpublished',
		'yaf_sch ool_acknowledge_on_student_signup',
	];

	public $book_template_types = [
		'email_book_selling_communication',
		'email_unfinished_book',
		'email_user_publish_book_with_order',
		'book_approved',
		'email_book_reject',
		'email_book_invoice',
		'email_book_incomplete',
		'email_user_publish_book_without_order',
		'email_user_completes_book',
		'email_franchise_author_no_order',
		'book_published_amazon',
	];

	public $school_template_types = [
		'other_school_register',
		'other_schools_auto_approval_email',
		'school_competition_signup',
		'school_competition_signup_nursery',
		'school_competition_signup_university',
		'school_signup_community',
		'school_lead_share',
		'lead_data',
		'school_register',
		'school_not_register',
		'email_teacher_invite',
		'email_student_invite',
		'email_school_order',
	];

	public function add_template($params = '', $param2 = '') {
		if ($params == 'add') {
			if ($this->addtemplate_model->add([
				'name' 			=> $this->input->post('name'),
				'subject' 		=> $this->input->post('subject'),
				'template_id' 	=> $this->input->post('template_id'),
				'body' 			=> $this->input->post('body'),
				'shedule' 		=> $this->input->post('shedule'),
				'type' 			=> $this->input->post('type'),
				'user_id' 		=> $this->session->userdata('user_id')
			])) {
				$this->session->set_flashdata('flash_message', 'Template Added Updated successfully.');
				redirect('/admin/add_template');
			}
		} elseif ($params == 'update') {
			if ($this->addtemplate_model->edit($param2, [
				'name' 			=> $this->input->post('name'),
				'subject' 		=> $this->input->post('subject'),
				'template_id' 	=> $this->input->post('template_id'),
				'body' 			=> $this->input->post('body'),
				'shedule' 		=> $this->input->post('shedule'),
				'type' 			=> $this->input->post('type'),
				'user_id' 		=> $this->session->userdata('user_id')
			])) {
				$this->session->set_flashdata('flash_message', 'Template Added Updated successfully.');
				redirect('/admin/add_template');
			} else {
				$this->session->set_flashdata('error_message', 'Template NOt Updated successfully.');
				redirect('/admin/add_template');
			}
		}

		$data['page_name'] 		= 'add_template';
		$data['page_title'] 	= _l('add_template');
		$data['templates'] 		= $this->addtemplate_model->get_all();

		$this->load->view('backend/index', $data);
	}

	public function email_template($param1 = '') {
		$data['types'] = [
			'email_otp',
			'email_user_signup',
			'email_forgot_password',
			'email_reset_password',
			'email_competition_signup'
		];

		$data['lables'] = [
			'email_otp' 				=> ['author_name', 'otp'],
			'email_user_signup' 		=> ['author_name', 'username', 'password', 'url'],
			'email_user_signup_mobile' 	=> ['author_name', 'username', 'password', 'url'],
			'email_forgot_password' 	=> ['author_name', 'username', 'url'],
			'email_reset_password' 		=> ['author_name', 'username', 'url'],
			'email_competition_signup' 	=> ['author_name', 'username', 'password', 'url'],
		];

		$data['variables'] = [
			'otp',
			'author_name',
			'username',
			'password',
			// 'book_name',
			// 'book_thumb',
			// 'pages',
			// 'url',
			// 'date',
			// 'published_user_name',
			// 'name',
			// 'email',
			// 'mobile',
			// 'country',
		];

		$data['values'] = [
			'otp'					=> '123456',
			'author_name'			=> 'Author Name',
			'username'				=> 'User Name',
			'password'				=> 'Password',
			'book_name'				=> 'Book Name',
			'book_thumb'			=> 'Thumb Image',
			'pages'					=> 'Pages',
			'url'					=> 'URL',
			'date'					=> 'Date',
			'published_user_name'	=> 'Published User Name',
			'name'					=> 'Name',
			'email'					=> 'Email',
			'mobile'				=> 'Mobile',
			'country'				=> 'Country',
		];

		if ($param1 == 'update') {
			foreach ($this->input->post('email_template', FALSE) as $key => $value) {
				$this->crud_model->update_setting_template($key, html_entity_decode($value, ENT_QUOTES));
			}

			$this->session->set_flashdata('flash_message', _l('email_template_updated_successfully'));
			redirect(base_url('admin/email_template'), 'refresh');
		}

		$data['page_name'] 		= 'email_template';
		$data['page_title'] 	= _l('email_template');

		$this->load->view('backend/index', $data);
	}

	public function school_email_template($param1 = '') {
		$data['types'] = [
			// 'email_otp',
			// 'email_user_signup',
			// 'email_unfinished_book',
			// 'email_user_completes_book',
			// 'email_free_user',
			// 'email_free_user_reminder',
			// 'email_book_reject',
			// 'email_book_verified',
			// 'email_book_invoice',
			// 'subject_for_24hrs',
			// 'email_signup_user_24hrs',
			// 'subject_for_48hrs',
			// 'email_signup_user_48hrs',
			// 'subject_for_72hrs',
			// 'email_signup_user_72hrs',
			// 'email_book_incomplete',
			// 'email_competition_signup',
			'school_competition_signup',
			'school_lead_share',
			'email_franchise_author_no_order',
			'other_school_register',
			'reject_lead',
			'Other_schools_auto_approval_email',
			'lead_data',
			'school_register',
			'school_not_register',
		];

		$data['lables'] = [
			'school_competition_signup' 		=> ['author_name', 'name'],
			'school_lead_share' 				=> ['name', 'author_name', 'country', 'email'],
			'email_franchise_author_no_order' 	=> ['author_name', 'book_name', 'url', 'url_2'],
			'other_school_register' 			=> [],
			'reject_lead' 						=> [],
			'lead_data' 						=> []
		];

		$data['variables'] = [
			// 'otp',
			'author_name',
			// 'username',
			// 'password',
			// 'book_name',
			// 'book_thumb',
			// 'pages',
			// 'url',
			// 'date',
			// 'published_user_name',
			'name',
			'email',
			// 'mobile',
			'country',
			'grades'
		];

		$data['values'] = [
			'otp'					=> '123456',
			'author_name'			=> 'Author Name',
			'username'				=> 'User Name',
			'password'				=> 'Password',
			'book_name'				=> 'Book Name',
			'book_thumb'			=> 'Thumb Image',
			'pages'					=> 'Pages',
			'url'					=> 'URL',
			'date'					=> 'Date',
			'published_user_name'	=> 'Published User Name',
			'name'					=> 'Name',
			'email'					=> 'Email',
			'mobile'				=> 'Mobile',
			'country'				=> 'Country',
			'grades'				=> 'grades',
		];

		if ($param1 == 'update') {
			foreach ($this->input->post('email_template', FALSE) as $key => $value) {
				log_kb($value);
				$this->crud_model->update_setting_template($key, html_entity_decode($value, ENT_QUOTES));
			}

			$this->session->set_flashdata('flash_message', _l('email_template_updated_successfully'));
			redirect(base_url('admin/school_email_template'), 'refresh');
		}

		$data['page_name'] 		= 'school_email_template';
		$data['page_title'] 	= _l('school_email_template');

		$this->load->view('backend/index', $data);
	}

	public function nyaf_email_template($param1 = '') {
		$data['types'] = [
			'other_school_register',
			'reject_lead',
			'Other_schools_auto_approval_email',
			'book_approved_nyaf',
			'singup_yaf',
			'yaf_acknowledgement',
			'yaf_request_autoapproval_24hr',
		];

		$data['lables'] = [
			'school_competition_signup' 		=> ['author_name', 'name'],
			'school_lead_share' 				=> ['name', 'author_name', 'country', 'email'],
			'email_franchise_author_no_order' 	=> ['author_name', 'book_name', 'url', 'url_2'],
			'other_school_register' 			=> [],
			'reject_lead' 						=> [],
			'lead_data' 						=> []
		];

		$data['variables'] = [
			// 'otp',
			'author_name',
			// 'username',
			// 'password',
			// 'book_name',
			// 'book_thumb',
			// 'pages',
			// 'url',
			// 'date',
			// 'published_user_name',
			'name',
			'email',
			// 'mobile',
			'country',
			'grades'
		];

		$data['values'] = [
			'otp'					=> '123456',
			'author_name'			=> 'Author Name',
			'username'				=> 'User Name',
			'password'				=> 'Password',
			'book_name'				=> 'Book Name',
			'book_thumb'			=> 'Thumb Image',
			'pages'					=> 'Pages',
			'url'					=> 'URL',
			'date'					=> 'Date',
			'published_user_name'	=> 'Published User Name',
			'name'					=> 'Name',
			'email'					=> 'Email',
			'mobile'				=> 'Mobile',
			'country'				=> 'Country',
			'grades'				=> 'grades',
		];

		if ($param1 == 'update') {
			foreach ($this->input->post('email_template', FALSE) as $key => $value) {
				$this->crud_model->update_setting_template($key, html_entity_decode($value, ENT_QUOTES));
			}

			$this->session->set_flashdata('flash_message', _l('email_template_updated_successfully'));
			redirect(base_url('admin/nyaf_email_template'), 'refresh');
		}

		$data['page_name'] 		= 'school_email_template';
		$data['page_title'] 	= _l('nyaf_email_template');

		$this->load->view('backend/index', $data);
	}

	public function book_email_template($param1 = '') {
		$data['types'] = [
			'email_unfinished_book',
			'email_user_publish_book_with_order',
			'book_approved',
			'email_book_reject',
			'email_book_invoice',
			'email_book_incomplete',
			'Email_User_Publish_Book_without_order',
			'email_user_completes_book'
			// 'book_selling_communication',
		];

		$data['lables'] = [
			'email_unfinished_book' 				=> [],
			'email_user_publish_book_with_order' 	=> [],
			'book_approved' 						=> ['author_name', 'book_name'],
			'email_book_reject' 					=> ['author_name', 'book_name'],
			'email_book_invoice' 					=> [],
			'email_book_incomplete' 				=> ['author_name', 'pages', 'url'],
			'email_user_publish_book_without_order' => ['author_name'],
		];

		$data['variables'] = [
			'author_name',
			'book_name',
			'pages',
			'url',
		];

		$data['values'] = [
			'otp'					=> '123456',
			'author_name'			=> 'Author Name',
			'username'				=> 'User Name',
			'password'				=> 'Password',
			'book_name'				=> 'Book Name',
			'book_thumb'			=> 'Thumb Image',
			'pages'					=> 'Pages',
			'url'					=> 'URL',
			'date'					=> 'Date',
			'published_user_name'	=> 'Published User Name',
			'name'					=> 'Name',
			'email'					=> 'Email',
			'mobile'				=> 'Mobile',
			'country'				=> 'Country',
		];

		if ($param1 == 'update') {
			foreach ($this->input->post('email_template', FALSE) as $key => $value) {
				log_kb($value);
				$this->crud_model->update_setting_template($key, html_entity_decode($value, ENT_QUOTES));
			}

			$this->session->set_flashdata('flash_message', _l('email_template_updated_successfully'));

			redirect(base_url('admin/book_email_template'), 'refresh');
		}

		$data['page_name'] 		= 'book_email_template';
		$data['page_title'] 	= _l('book_email_template');

		$this->load->view('backend/index', $data);
	}

	public function schedule_email_template($param1 = '') {
		$data['types'] = [
			'email_free_user',
			'email_free_user_reminder',
			'subject_for_24hrs',
			'email_signup_user_24hrs',
			'subject_for_48hrs',
			'email_signup_user_48hrs',
			'subject_for_72hrs',
			'email_signup_user_72hrs',
			'email_book_selling_communication',
			'email_send_coupon',
		];

		$data['lables'] = [
			'email_free_user' => [],
			'email_free_user_reminder' => [],
			'subject_for_24hrs' => [],
			'email_signup_user_24hrs' => [],
			'subject_for_48hrs' => [],
			'email_signup_user_48hrs' => [],
			'subject_for_72hrs' => [],
			'email_signup_user_72hrs' => [],
			'email_book_selling_communication' => ['url', 'username'],
			'email_send_coupon' => ['url', 'author_name', 'name', 'book_name', 'password']
		];

		$data['variables'] = [
			// 'otp',
			// 'author_name',
			// 'username',
			// 'password',
			// 'book_name',
			// 'book_thumb',
			// 'pages',
			// 'url',
			// 'date',
			// 'published_user_name',
			// 'name',
			// 'email',
			// 'mobile',
			// 'country',
		];

		$data['values'] = [
			'otp'					=> '123456',
			'author_name'			=> 'Author Name',
			'username'				=> 'User Name',
			'password'				=> 'Password',
			'book_name'				=> 'Book Name',
			'book_thumb'			=> 'Thumb Image',
			'pages'					=> 'Pages',
			'url'					=> 'URL',
			'date'					=> 'Date',
			'published_user_name'	=> 'Published User Name',
			'name'					=> 'Name',
			'email'					=> 'Email',
			'mobile'				=> 'Mobile',
			'country'				=> 'Country',
		];

		if ($param1 == 'update') {
			foreach ($this->input->post('email_template', FALSE) as $key => $value) {
				$this->crud_model->update_setting_template($key, html_entity_decode($value, ENT_QUOTES));
			}

			$this->session->set_flashdata('flash_message', _l('email_template_updated_successfully'));
			redirect(base_url('admin/schedule_email_template'), 'refresh');
		}

		$data['page_name'] 		= 'schedule_email_template';
		$data['page_title'] 	= _l('schedule_email_template');

		$this->load->view('backend/index', $data);
	}

	public function email_test() {
		$json = [];

		if ($this->input->post('email_data') && $this->input->post('message') && $this->input->post('email')) {
			$email_data = $this->input->post('email_data');

			$find = [
				'{otp}',
				'{author_name}',
				'{username}',
				'{password}',
				'{book_name}',
				'{book_thumb}',
				'{pages}',
				'{url}',
				'{url_2}',
				'{date}',
				'{published_user_name}',
				'{name}',
				'{email}',
				'{mobile}',
				'{country}',
				'{state}',
				'{city}',
				'{designation}',
				'{grades}',
				'{sections}',
				'{authorized_person}',
				'{school_head}',
				'{parent_name}',
			];

			$replace = [
				'otp'					=> $email_data['otp'],
				'author_name'			=> $email_data['author_name'],
				'username'				=> $email_data['username'],
				'password'				=> $email_data['password'],
				'book_name'				=> $email_data['book_name'],
				'book_thumb'			=> $email_data['book_thumb'],
				'pages'					=> $email_data['pages'],
				'url'					=> $email_data['url'],
				'url_2'					=> $email_data['url_2'],
				'date'					=> $email_data['date'],
				'published_user_name'	=> $email_data['published_user_name'],
				'name'	  				=> $email_data['name'],
				'email' 				=> $email_data['email'],
				'mobile' 				=> $email_data['mobile'],
				'country'   			=> $email_data['country'],
				'state'   				=> $email_data['name'],
				'city' 					=> $email_data['name'],
				'designation'   		=> $email_data['designation'],
				'grades'   				=> $email_data['grades'],
				'sections'   			=> $email_data['sections'],
				'authorized_person'		=> $email_data['authorized_person'],
				'school_head'   		=> $email_data['school_head'],
				'parent_name'   		=> $email_data['parent_name'],
			];

			$message = str_replace($find, $replace, $this->input->post('message'));
			$status = $this->alert_model->email(
				$this->input->post('email'),
				'Test Email:: ' . get_settings('system_name'),
				$message,
				NULL
			);
			$json['status'] = $status ? 'success' : 'failure';
		}

		output_json($json);
	}

	public function send_signup_hour_mail($hour = 24) {
		$template_data = get_settings('email_signup_user_' . $hour . 'hrs');

		if (empty($template_data)) return false;

		$subject_data = get_settings('subject_for_' . $hour . 'hrs');

		if (empty($subject_data)) return false;

		$filter['signup_interval'] = $hour / 24;

		$user = $this->user_model->get_all($filter);

		if (empty($user['rows'])) return false;

		$find = [
			'{otp}',
			'{user_name}',
			'{book_name}',
		];

		foreach ($user['rows'] as $user) {
			$replace = [
				'user_name'		=> $user['first_name'] . ' ' . $user['last_name']
			];

			$message = str_replace($find, $replace, $template_data);
			$status = $this->alert_model->email(
				$user['email'],
				$subject_data,
				$message,
				NULL
			);
		}

		echo 'Mail Sent Successfully';

		return true;
	}

	public function templates($param1 = '') {
		$type = '';

		switch ($param1) {
			case 'book':
				$type = 'book';
				break;
			case 'school':
				$type = 'school';
				break;
			default:
				$type = 'user';
				break;
		}

		$data['page_name'] 		= 'templates';
		$data['page_title'] 	= _l('email templates');
		$data['type'] 			= $type;

		$this->load->view('backend/index', $data);
	}

	public function email_template_form($param1 = '', $param2 = '') {
		if (empty($param1) || !is_numeric($param1)) {
			$this->session->set_flashdata('error_message', _l('invalid_input'));
			redirect(base_url('admin/templates'), 'refresh');
		}

		$site_info = $this->site_model->get($param1);

		if (empty($site_info)) {
			$this->session->set_flashdata('error_message', _l('invalid_input'));
			redirect(base_url('admin/templates'), 'refresh');
		}

		$types = '';

		switch ($param2) {
			case 'book':
				$types = 'book';
				break;
			case 'school':
				$types = 'school';
				break;
			default:
				$types = 'user';
				break;
		}

		$data['types'] = $this->{$types . '_template_types'};

		$data['lables'] = [
			'email_otp' => ['author_name', 'otp'],
			'email_user_signup' => ['author_name', 'username', 'password', 'url'],
			'email_user_signup_mobile' => ['author_name', 'username', 'password', 'url'],
			'email_user_signup_nursery' => ['parent_name', 'author_name', 'username', 'password', 'url'],
			'email_user_signup_university' => ['author_name', 'username', 'password', 'url'],
			'email_user_signup_community' => ['author_name', 'username', 'password', 'url'],
			'email_user_tnc' => ['author_name'],
			'email_user_tnc_nursery' => ['author_name'],
			'email_user_tnc_university' => ['author_name'],
			'email_forgot_password' => ['author_name', 'url'],
			'email_reset_password' => ['author_name'],
			'email_competition_signup' => ['author_name', 'username', 'password', 'url'],
			'email_book_selling_communication' => ['url', 'username'],
			'email_send_coupon' => ['url', 'author_name', 'name', 'book_name', 'password'],
			'email_unfinished_book' => [],
			'email_user_publish_book_with_order' => ['author_name', 'book_name'],
			'book_approved' => ['author_name', 'book_name'],
			'email_book_reject' => ['author_name', 'book_name'],
			'email_book_invoice' => [],
			'email_book_incomplete' => ['author_name', 'pages', 'url'],
			'email_user_publish_book_without_order' => ['author_name'],
			'email_user_completes_book' => ['author_name', 'book_name'],
			'other_school_register' => ['author_name'],
			'other_schools_auto_approval_email' => ['author_name', 'name'],
			'school_competition_signup' => ['author_name', 'name'],
			'school_competition_signup_nursery' => ['author_name', 'name'],
			'school_competition_signup_university' => ['author_name', 'name'],
			'school_signup_community' => ['owner_name', 'school_name'],
			'school_lead_share' => ['name', 'authorized_person', 'mobile', 'country', 'state', 'city', 'designation', 'grades', 'sections'],
			'email_franchise_author_no_order' => ['author_name', 'book_name', 'url', 'url_2'],
			'lead_data' => ['author_name', 'name', 'school_head', 'state'],
			'school_register' => ['author_name', 'name'],
			'school_not_register' => ['author_name', 'name'],
			'book_approved_nyaf' => ['author_name', 'book_name', 'url'],
			'singup_yaf' => ['author_name', 'username', 'password', 'url'],
			'yaf_acknowledgement' => ['author_name'],
			'yaf_invite_yaf_published' => ['author_name', 'book_name', 'username', 'password'],
			'yaf_invite_yaf_unpublished' => ['author_name', 'book_name', 'username', 'password'],
			'yaf_invite_summercamp_unpublished' => ['author_name', 'book_name', 'username', 'password'],
			'yaf_school_acknowledge_on_student_signup' => ['author_name', 'owner_name', 'mobile', 'city', 'state', 'url'],
			'book_published_amazon' => ['author_name', 'book_name', 'book_url'],
			'email_referral_user_signup' => ['author_name', 'username', 'password', 'url'],
			'email_teacher_invite' => ['name', 'username', 'password', 'url', 'school_name'],
			'email_student_invite' => ['author_name', 'username', 'password', 'url', 'school_name'],
			'email_school_order' => ['school_name', 'date_completed'],
		];

		$data['variables'] = [
			'otp',
			'author_name',
			'username',
			'password',
			'book_name',
			'book_thumb',
			'pages',
			'url',
			'url_2',
			'date',
			'published_user_name',
			'name',
			'email',
			'mobile',
			'country',
			'state',
			'city',
			'designation',
			'grades',
			'sections',
			'authorized_person',
			'school_head',
			'referral_name',
			'parent_name'
		];

		$data['values'] = [
			'otp'					=> '123456',
			'author_name'			=> 'Author Name',
			'username'				=> 'User Name',
			'password'				=> 'Password',
			'book_name'				=> 'Book Name',
			'book_thumb'			=> 'Thumb Image',
			'pages'					=> 'Pages',
			'url'					=> 'URL',
			'url_2'					=> 'URL',
			'date'					=> 'Date',
			'published_user_name'	=> 'Published User Name',
			'name'					=> 'Name',
			'email'					=> 'Email',
			'mobile'				=> 'Mobile',
			'country'				=> 'Country',
			'state'					=> 'State',
			'city'					=> 'City',
			'designation'			=> 'Designation',
			'grades'				=> 'Grades',
			'sections'				=> 'Sections',
			'authorized_person'		=> 'Authorized Person',
			'school_head'			=> 'School Head',
			'parent_name'			=> 'Parent Name',
			'referral_name'			=> 'Referral Name'
		];

		$templates_info = $this->addtemplate_model->get_all(array('site_id' => $param1))['rows'] ?? [];

		$email_template_info = [];

		foreach ($templates_info as $key => $value) {
			$email_template_info[$value['template_id']] = $value;
		}

		$data['site_info'] 		= $site_info;
		$data['templates_info'] = $email_template_info;
		$data['page_name'] 		= 'email_template_form';
		$data['page_title'] 	= _l('email_template');
		$data['header_image'] 	= $this->addtemplate_model->get_email_templates_image_url('header_image_' . $param1);
		$data['footer_image'] 	= $this->addtemplate_model->get_email_templates_image_url('footer_image_' . $param1);

		$this->load->view('backend/index', $data);
	}

	public function save_email_template($param1 = '') {
		if (empty($param1) || !is_numeric($param1)) {
			$this->session->set_flashdata('error_message', _l('invalid_input'));
			redirect(base_url('admin/templates'), 'refresh');
		}

		$site_info = $this->site_model->get($param1);

		$event_info = $this->event_model->get_all([
			'site_id' => $param1
		])['rows'][0] ?? '';

		if (empty($site_info)) {
			$this->session->set_flashdata('error_message', _l('invalid_input'));
			redirect(base_url('admin/templates'), 'refresh');
		}

		if (!empty($_FILES['header_image']['name'])) {
			$header_filename = 'header_image_' . $param1;
			$this->addtemplate_model->upload_image($header_filename, 'header_image');
		}

		if (!empty($_FILES['footer_image']['name'])) {
			$footer_filename = 'footer_image_' . $param1;
			$this->addtemplate_model->upload_image($footer_filename, 'footer_image');
		}

		$email_template = $this->input->post('email_template', false);

		foreach ($email_template as $key => $value) {
			$template_info = $this->addtemplate_model->getByTemplateId($param1, $value['template_id']);

			$save = [
				'event_id' 		=> $event_info['id'] ?? 0,
				'site_id' 		=> $param1,
				'name' 			=> $value['name'],
				'subject' 		=> $value['subject'],
				'template_id' 	=> $value['template_id'],
				'body' 			=> $value['body'],
				'status' 		=> !empty($value['status']) ? $value['status'] : '0',
				'user_id' 		=> $this->session->userdata('user_id')
			];

			if (empty($template_info)) {
				$this->addtemplate_model->add($save);
			} else {
				$this->addtemplate_model->edit($template_info['id'], $save);
			}
		}

		$this->session->set_flashdata('flash_message', _l('email_template_updated_successfully'));

		redirect(base_url('admin/templates'), 'refresh');
	}
}
