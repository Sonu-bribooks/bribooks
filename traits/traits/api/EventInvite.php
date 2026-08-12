<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventInvite {
	public function getUserEventGuest() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('user_id', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
			if (empty($form_invite_info = $this->db->get_where('event_form_invite_code', [
				'event_id'	=> (int)$this->input->post('event_id'),
			])->row_array()) || empty($form_invite_info['end_date'])) {
				return $this->json['error'] = _li('Invalid Form');
			}

			if ($form_invite_info['end_date'] < date('Y-m-d H:i:s')) {
				return $this->json['error'] = _li('Form Expired');
			}

			$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');

			$invite_info = $this->event_user_invite_code_model->get_all([
				'event_id'  => (int)$this->input->post('event_id'),
				'user_id'   => (int)$this->input->post('user_id'),
				'code'	  	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (empty($invite_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$info = $this->event_user_invite_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id') ?? 0,
				'book_id'	=> (int)$this->input->post('book_id'),
				'is_jury'	=> (int)$this->input->post('is_jury') ?? 0,
			])['rows'][0] ?? [];

			if (empty($info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			if ($info['status'] == 1) {
				$this->json['error'] = _li('Details already submitted. <br/>For any changes mail at support@bribooks.com.');
			}

			$book_info  = $this->book_model->get($info['book_id'] ?? 0);
			$user_info  = $this->user_model->get($info['user_id'] ?? 0);
			$site_info  = $this->site_model->get($user_info['site_id'] ?? 0);

			$guest_data = [
				'event_id'	  	=> $info['event_id'],
				'site_id'	   	=> $user_info['site_id'],
				'user_id'	   	=> $info['user_id'],
				'book_id'	   	=> $info['book_id'],
				'book_rank'	 	=> $info['book_rank'],
				'book_sold'	 	=> $info['book_sold'],
				'grade'		 	=> $user_info['grade'],
				'section'	   	=> $user_info['section'],
				'mobile'		=> $user_info['mobile'],
				'email'		 	=> $user_info['email'],
				'site_name'	 	=> $site_info['name'] ?? '',
				'book_name'	 	=> $book_info['name'] ?? '',
				'author_name'   => $book_info['author_name'] ?? '',
				'no_of_guest'   => $info['no_of_guest'] ?? 0,
				'is_jury'	   	=> $info['is_jury'] ?? 0
			];

			$this->json['details'] = $guest_data;
		}
	}

	public function addUserEventGuest() {
		$this->form_validation->set_rules('user_id', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('status', _l('Author Invite'), 'trim|required|in_list[1,2]');

		if ($this->input->post('status') == 1) {
			$this->form_validation->set_rules('guest_1_name', _l('First Guest Name'), 'trim|required|min_length[3]');
			$this->form_validation->set_rules('guest_1_relation', _l('First Guest Relation'), 'trim|required|in_list[father,mother,guardian]');
			$this->form_validation->set_rules('guest_2_name', _l('Second Guest Name'), 'trim|min_length[3]');
			$this->form_validation->set_rules('guest_2_relation', _l('Second Guest Relation'), 'trim|in_list[father,mother,guardian]');

			if (mb_strtolower($this->config->item('site_country_code')) == 'in') {
				$this->form_validation->set_rules('guest_1_aadhaar', _l('First Guest ID Proof'), 'trim|required|min_length[4]|max_length[18]');
				$this->form_validation->set_rules('guest_2_aadhaar', _l('Second Guest ID Proof'), 'trim|min_length[4]|max_length[18]');
			} else {
				$this->form_validation->set_rules('guest_1_aadhaar', _l('First Guest ID Proof'), 'trim|required|min_length[4]|max_length[30]');
				$this->form_validation->set_rules('guest_2_aadhaar', _l('Second Guest ID Proof'), 'trim|min_length[4]|max_length[30]');
			}
		}

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('addUserEventGuest_%s_%s_%s', 
					$this->input->post('event_id'),
					$this->input->post('user_id'),
					$this->input->post('book_id')
				)
			]);

			$invite_info = $this->event_user_invite_code_model->get_all([
				'event_id'  => (int)$this->input->post('event_id'),
				'user_id'   => (int)$this->input->post('user_id'),
				'code'	  	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (empty($invite_info)) {
				$this->json['error'] = _li('Invalid url!');
				return;
			}

			$info = $this->event_user_invite_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id') ?? 0,
				'user_id'	=> (int)$this->input->post('user_id'),
				'book_id'	=> (int)$this->input->post('book_id'),
				'is_jury'	=> (int)$this->input->post('is_jury') ?? 0,
			])['rows'][0] ?? [];

			if (empty($info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			if (($this->input->post('status') ?? 0) == 2) {
				$this->event_user_invite_model->edit($info['id'], [
					'status'		=> 2
				]);

				$this->json['success'] = _li('Details submitted');
				return;
			}

			$full_author_image = '';
			$code = sha1(md5(sprintf('%s_%s_%s_%s', $info['event_id'], $info['user_id'], $info['book_id'], $this->config->item('password_salt'))));

			$guest_count = 1;

			$data = [
				'author_aadhaar'		=> $this->input->post('author_aadhaar') ?? NULL,
				'guest_1_name'			=> $this->input->post('guest_1_name') ?? NULL,
				'guest_1_relation'		=> $this->input->post('guest_1_relation') ?? NULL,
				'guest_1_aadhaar'		=> $this->input->post('guest_1_aadhaar') ?? NULL,
				'code'					=> $code,
				'status'				=> 1,
			];

			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$dir_name = (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test');

			if (isset($_FILES['author_image']) && ($_FILES['author_image']['size'] > 0)) {
				if (self::_validateFileUpload('author_image')) {

					$author_image = $this->s3_lib->putFile(
						$_FILES['author_image']['tmp_name'],
						$dir_name,
					);

					$full_author_image = $this->s3_lib->getUrl($author_image, $dir_name, false, 30);

					$data['author_image'] = $author_image;
				}
			}

			if (isset($_FILES['author_aadhaar_image']) && ($_FILES['author_aadhaar_image']['size'] > 0)) {
				if (self::_validateFileUpload('author_aadhaar_image')) {

					$author_aadhaar_image = $this->s3_lib->putFile(
						$_FILES['author_aadhaar_image']['tmp_name'],
						$dir_name,
					);

					$data['author_aadhaar_image'] = $author_aadhaar_image;
				}
			}

			if (isset($_FILES['guest_1_aadhaar_image']) && ($_FILES['guest_1_aadhaar_image']['size'] > 0)) {
				if (self::_validateFileUpload('guest_1_aadhaar_image')) {

					$guest_1_aadhaar_image = $this->s3_lib->putFile(
						$_FILES['guest_1_aadhaar_image']['tmp_name'],
						$dir_name,
					);

					$data['guest_1_aadhaar_image'] = $guest_1_aadhaar_image;
				}
			}

			if (isset($_FILES['guest_2_aadhaar_image']) &&
				($_FILES['guest_2_aadhaar_image']['size'] > 0) &&
				!empty($this->input->post('guest_2_name')) &&
				!empty($this->input->post('guest_2_relation')) &&
				!empty($this->input->post('guest_2_aadhaar'))
			) {
				if (self::_validateFileUpload('guest_2_aadhaar_image')) {

					$guest_2_aadhaar_image = $this->s3_lib->putFile(
						$_FILES['guest_2_aadhaar_image']['tmp_name'],
						$dir_name,
					);

					$data = $data + [
						'guest_2_name'			=> $this->input->post('guest_2_name'),
						'guest_2_relation'		=> $this->input->post('guest_2_relation'),
						'guest_2_aadhaar'		=> $this->input->post('guest_2_aadhaar'),
						'guest_2_aadhaar_image' => $guest_2_aadhaar_image,
					];

					$guest_count++;
				}
			}

			$data['no_of_guest'] = $guest_count;

			$this->event_user_invite_model->edit($info['id'], $data);

			$qrcode_url = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'author_invite_qr.png');

			$this->json['success'] = _l('details_successfully_saved');

			$this->json['data'] = [
				'author_image'  => $full_author_image,
				'book_rank'	 	=> $info['book_rank'] ?? 0,
				'guest_1_name'  => $this->input->post('guest_1_name') ?? '',
				'guest_2_name'  => $this->input->post('guest_2_name') ?? '',
				'qr_url'		=> $qrcode_url,
				'pdf_url'	   	=> '',
			];
		}
	}

	public function getSchoolEventGuest() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {

			if (empty($form_invite_info = $this->db->get_where('event_form_invite_code', [
				'event_id'	=> (int)$this->input->post('event_id'),
			])->row_array()) || empty($form_invite_info['end_date'])) {
				return $this->json['error'] = _li('Invalid Form');
			}

			if ($form_invite_info['end_date'] < date('Y-m-d H:i:s')) {
				return $this->json['error'] = _li('Form Expired');
			}

			$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');

			$invite_info = $this->event_school_invite_code_model->get_all([
				'event_id'  => (int)$this->input->post('event_id'),
				'site_id'   => (int)$this->input->post('site_id'),
				'code'	  	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (empty($invite_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$info = $this->event_school_invite_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id') ?? 0,
				'site_id'	=> (int)$this->input->post('site_id'),
			])['rows'][0] ?? [];

			if (empty($info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			if ($info['status'] == 1) {
				$this->json['error'] = _li('Details already submitted. <br/>For any changes mail at support@bribooks.com.');
			}

			$site_info	  	= $this->site_model->get($info['site_id'] ?? 0);
			$city_info	  	= $this->city_model->get($site_info['city_id'] ?? 0);
			$state_info	 	= $this->state_model->get($site_info['state_id'] ?? 0);

			$guest_data = [
				'event_id'			  	=> $info['event_id'],
				'site_id'			   	=> $info['site_id'],
				'school_rank'		   	=> $info['school_rank'],
				'owner_name'			=> $site_info['owner_name'] ?? '',
				'authorized_person'	 	=> $site_info['authorized_person'] ?? '',
				'mobile'				=> $site_info['owner_mobile'],
				'email'				 	=> $site_info['owner_email'],
				'site_name'			 	=> $site_info['name'] ?? '',
				'city_id'			   	=> $site_info['city_id'] ?? 0,
				'city'				  	=> $city_info['name'] ?? '',
				'state_id'			  	=> $site_info['state_id'] ?? 0,
				'state'				 	=> $state_info['name'] ?? '',
				'no_of_guest'		   	=> $info['no_of_guest'] ?? 0,
			];

			$this->json['details'] = $guest_data;
		}
	}

	public function addSchoolEventGuest() {
		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('status', _l('Author Invite'), 'trim|required|in_list[1,2]');

		if ($this->input->post('status') == 1) {
			$this->form_validation->set_rules('guest_1_name', _l('First Guest Name'), 'trim|required|min_length[3]');
			$this->form_validation->set_rules('guest_1_gender', _l('First Guest Gender'), 'trim|required|in_list[male,female]');

			if (mb_strtolower($this->config->item('site_country_code')) == 'in') {
				$this->form_validation->set_rules('guest_1_aadhaar', _l('First Guest ID Proof'), 'trim|required|min_length[4]|max_length[18]');
			} else {
				$this->form_validation->set_rules('guest_1_aadhaar', _l('First Guest ID Proof'), 'trim|required|min_length[4]|max_length[30]');
			}
		}

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');

			$invite_info = $this->event_school_invite_code_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id'),
				'site_id'   => (int)$this->input->post('site_id'),
				'code'	  	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (empty($invite_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$info = $this->event_school_invite_model->get_all([
				'event_id'	=> (int)$this->input->post('event_id') ?? 0,
				'site_id'	=> (int)$this->input->post('site_id'),
			])['rows'][0] ?? [];

			if (empty($info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			if (($this->input->post('status') ?? 0) == 2) {
				$this->event_school_invite_model->edit($info['id'], [
					'status'		=> 2
				]);

				$this->json['success'] = _li('Details submitted');
				return;
			}

			$code = sha1(md5(sprintf('%s_%s_%s', $info['event_id'], $info['site_id'], $this->config->item('password_salt'))));

			$data = [
				'guest_1_name'			=> $this->input->post('guest_1_name') ?? NULL,
				'guest_1_gender'		=> $this->input->post('guest_1_gender') ?? NULL,
				'guest_1_aadhaar'		=> $this->input->post('guest_1_aadhaar') ?? NULL,
				'code'					=> $code,
				'no_of_guest'			=> 1,
				'status'				=> 1,
			];

			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$dir_name = (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test');

			if (isset($_FILES['guest_1_aadhaar_image']) && ($_FILES['guest_1_aadhaar_image']['size'] > 0)) {
				if (self::_validateFileUpload('guest_1_aadhaar_image')) {

					$guest_1_aadhaar_image = $this->s3_lib->putFile(
						$_FILES['guest_1_aadhaar_image']['tmp_name'],
						$dir_name,
					);

					$data['guest_1_aadhaar_image'] = $guest_1_aadhaar_image;
				}
			}

			$qrcode_url = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'author_invite_qr.png');

			$this->event_school_invite_model->edit($info['id'], $data);

			$this->json['success'] = _l('details_successfully_saved');

			$this->json['data'] = [
				'school_rank'   => $info['school_rank'] ?? 0,
				'guest_1_name'  => $this->input->post('guest_1_name') ?? '',
				'qr_url'		=> $qrcode_url,
				'pdf_url'	   	=> '',
			];
		}
	}

	public function getEventPass() {
		if (!$this->json) {
			$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');

			$results = $this->event_user_invite_model->get_all([
				'user_id'			=> (int)$this->session->userdata('user_id'),
				'is_active_event'	=> 1,
				'status'			=> 1,
			])['rows'] ?? [];

			$results = array_filter($results, fn($item) => !empty($item['pdf']));

			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$s3_dirname = (ENVIRONMENT === 'production' ? 'event_pass_pdf' : 'event_pass_pdf/test');

			$this->json['event_passes'] = array_map(function($item) use ($s3_dirname) {
				$pdf = $this->s3_lib->getUrl($item['pdf'], $s3_dirname, false, 30);

				return [
					'event_id'			=> $item['event_id'],
					'challenge_id'		=> $item['challenge_id'],
					'challenge_type'	=> $item['challenge_type'],
					'book_id'			=> $item['book_id'],
					'book_rank'			=> $item['book_rank'],
					'pdf'				=> $pdf,
				];
			}, $results);
		}
	}

	public function getBBUser() {
		$this->form_validation->set_rules('user_id', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');

			$invite_info = $this->event_user_invite_code_model->get_all([
				'event_id'  => (int)$this->input->post('event_id') ?? 0,
				'user_id'   => (int)$this->input->post('user_id'),
				'code'	  	=> $this->input->post('code')
			])['rows'][0] ?? [];

			if (empty($invite_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$user_info  	= $this->user_model->get($invite_info['user_id'] ?? 0);
			$site_info  	= $this->site_model->get($user_info['site_id'] ?? 0);
			$state_info  	= $this->state_model->get($user_info['state_id'] ?? 0);
			$city_info  	= $this->city_model->get($user_info['city_id'] ?? 0);
			$country_info  	= $this->country_model->get($user_info['country_id'] ?? 0);

			$guest_data = [
				'event_id'	  	=> $invite_info['event_id'] ?? 0,
				'site_id'	   	=> $user_info['site_id'] ?? 0,
				'user_id'	   	=> $user_info['user_id'] ?? 0,
				'country_id'	=> $user_info['country_id'] ?? 0,
				'state_id'	   	=> $user_info['state_id'] ?? 0,
				'city_id'	   	=> $user_info['city_id'] ?? 0,
				'grade'		 	=> $user_info['grade'] ?? 0,
				'section'	   	=> $user_info['section'] ?? '',
				'first_name'	=> $user_info['first_name'] ?? '',
				'last_name'		=> $user_info['last_name'] ?? '',
				'mobile'		=> $user_info['mobile'] ?? '',
				'email'		 	=> $user_info['email'] ?? '',
				'site_name'	 	=> $site_info['name'] ?? '',
				'country'	 	=> $country_info['name'] ?? '',
				'state'	 		=> $state_info['name'] ?? '',
				'city'	 		=> $city_info['name'] ?? '',
				'age'	 		=> calculate_age($user_info['dob']),
				'status'	 	=> $invite_info['status'] ?? 0,
			];

			$this->json['details'] = $guest_data;
			
		}
	}
}
