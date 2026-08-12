<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Nyaf2022 {
	public function nyaf_authors() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'students/nyaf_authors';
		$data['page_title'] 	= _l('nyaf_authors');
		$data['action_zip'] 	= site_url('admin/download_nyaf_author_images_zip');
		$data['action_ajax'] 	= site_url('admin/ajax_nyaf_authors');

		$this->load->view('backend/index', $data);
	}

	public function ajax_nyaf_authors() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$this->load->model('user/UserDetails_model', 'user_details_model');

		$results = $this->user_details_model->getNyafUserImage($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$books = $this->book_model->get_all([
				'user_id'	=> $result['id'],
			]);

			$published_books = count(array_filter($books['rows'] ?? [], function($item) {
				return $item['status'] == 1;
			}));

			$json['data'][] = [
				'result' => $result,
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'name'				=> vsprintf(_l('%s <br> %s <br> %s'), [
					$result['first_name'] . ' ' . $result['last_name'],
					$result['email'],
					$result['mobile'],
				]),
				'books'				=> vsprintf(_l('Total :: %s <br> Published:: %s'), [
					$books['total'],
					$published_books,
				]),
				'location'			=> $result['location'],
				'source'			=> $result['source'],
				'date_added'		=> formatDate($result['photo_date_added']),
				'actions'			=> '<a target="_blank" href="'.$this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test/') . 'user_' . (int)$result['id'] . '.png?v='.time().'" title="Image"><i class="fa fa-download"></i></a>',
			];
		}

		output_json($json);
	}

	public function download_nyaf_author_images_zip() {
		$this->load->library('zip');

		$this->load->model('user/UserDetails_model', 'user_details_model');

		$results = $this->user_details_model->get_user_details();

		if(empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/nyaf_authors'), 'refresh');
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_authors_images.zip';

		foreach ($results as $result) {
			$user_image = 'user_' . (int)$result['user_id'] . '.png';

			$this->zip->add_data($user_image, @file_get_contents($this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_image));
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function nyaf_authors_invite() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['events'] 		= $this->event_model->get_all()['rows'] ?? [];

		$data['page_name'] 		= 'students/nyaf_authors_invite';
		$data['page_title'] 	= _l('nyaf_authors_invite');
		$data['action_csv'] 	= site_url('admin/download_nyaf_author_csv');
		$data['action_zip'] 	= site_url('admin/download_nyaf_author_pdfs_zip');
		$data['action_ajax'] 	= site_url('admin/ajax_nyaf_authors_invite');

		$this->load->view('backend/index', $data);
	}

	public function ajax_nyaf_authors_invite() {
		$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'status'			=> $this->input->get('status') < 0 || is_null($this->input->get('status')) ? null : (int)$this->input->get('status'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'user_details_nyaf_invites.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (($this->input->get('user_type') ?? 'user') == 'teacher') {
			$filter_data['empty_book'] = 1; 
		} else {
			$filter_data['not_empty_book'] = 1; 
		}

		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$results = $this->user_details_invite_model->getNyafAuthorInvite($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$entry_status = $this->user_details_guest_model->get_all([
				'user_id'	=> $result['user_id'],
				'event_id'	=> $result['event_id'],
			])['rows'][0] ?? [];

			$invite_status = '';
			switch ($result['invite_status']) {
				case '1':
					$invite_status = '<span class="text-success">Accepted</span>';
					break;

				case '2':
					$invite_status = '<span class="text-danger">Rejected</span>';
					break;

				default:
					$invite_status = '<span class="text-warning">Pending</span>';
					break;
			}

			$aadhar_images = '';

			$author_images_url = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images/' : 'aadhar_images/test/') . $result['author_image'];
			$author_aadhar_images_url = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images/' : 'aadhar_images/test/') . $result['author_aadhar_image'];

			$author_aadhar_images = ' <a target="_blank" title="Author Image" href="'. $author_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_author_image.png' .'"><i class="fa fa-download"></i></a> <a target="_blank" title="Author Aadhar Image" href="'. $author_aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_author_aadhar_image.png' .'"><i class="fa fa-download"></i></a>';


			if(!empty($result['no_of_guest'])) {
				$aadhar_images_url = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images/' : 'aadhar_images/test/');

				switch ($result['no_of_guest']) {
					case '1':
						$aadhar_images = ' <a target="_blank" title="Aadhar Image Guest 1" href="'. $aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_1.png' .'"><i class="fa fa-download"></i></a>';
						break;

					case '2':
						$aadhar_images = ' <a target="_blank" title="Aadhar Image Guest 2" href="'. $aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_1.png' .'"><i class="fa fa-download"></i></a> <a target="_blank" title="Aadhar Image 2" href="'. $aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_2.png' .'"><i class="fa fa-download"></i></a>';
						break;

					default:
						break;
				}
			}

			$user_info = $this->user_model->get($result['user_id']);
			$book_info = $this->book_model->get($result['book_id']);
			$event_info = $this->event_model->get($result['event_id']);

			$url = vsprintf(USER_YAF_URL . 'registration/submitdetail?uid=%s&code=%s&bid=%s&eid=%s', [
				$user_info['id'],
				$user_info['verification_code'],
				$result['book_id'],
				$result['event_id'] ?? 4,
			]);

			$json['data'][] = [
				'result' => $result,
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'event'				=> $event_info['name'] ?? '',
				'name'				=> vsprintf(_l('%s <br>%s <br> %s <br> %s<br>') . '<span class="badge badge-%s">%s</span>', [
					$book_info['name'],
					$result['first_name'] . ' ' . $result['last_name'],
					$result['email'],
					$result['mobile'],
					$result['is_jury'] ? 'warning' : 'success',
					$result['is_jury'] ? _l('jury') : _l('best_seller'),
				]),
				'book_rank'			=> $result['book_rank'],
				'invite_status'		=> $invite_status,
				'guest_count'		=> $result['no_of_guest'],
				'location'			=> $result['location'],
				'source'			=> $result['source'],
				'verified'			=> sprintf('%s <br> %d %s', _sd($entry_status ['verified']), $entry_status ['guest_count'], _l('guests')),
				'date_added'		=> formatDate($result['invite_date_added']),
				'actions'			=> !empty($result['no_of_guest'])
					? '<a target="_blank" title="Form Link" href="'. $url .'"><i class="fa fa-link"></i></a> <a target="_blank" title="Entry Pass" href="'. base_url().'uploads/eventpass/pdfs/entry_pass_'.sha1(md5($result['user_id'] . '_' . $result['book_id'] . $this->config->item('password_salt'))).'.pdf' .'"><i class="fa fa-download"></i></a>' . $aadhar_images . $author_aadhar_images
					: '<a target="_blank" title="Form Link" href="'. $url .'"><i class="fa fa-link"></i></a>',
			];
		}

		output_json($json);
	}

	public function export_author_invites() {
		$json = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'status'			=> $this->input->get('status') < 0 || is_null($this->input->get('status')) ? null : (int)$this->input->get('status'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'user_details_nyaf_invites.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (($this->input->get('user_type') ?? 'user') == 'teacher') {
			$filter_data['empty_book'] = 1; 
		} else {
			$filter_data['not_empty_book'] = 1; 
		}

		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$results = $this->user_details_invite_model->getNyafAuthorInvite($filter_data);

		foreach ($results['rows'] ?? [] as $key => $result) {
			$invite_status = '';

			switch ($result['invite_status']) {
				case '1':
					$invite_status = _l('accepted');
					break;

				case '2':
					$invite_status = _l('rejected');
					break;

				default:
					$invite_status = _l('pending');
					break;
			}

			$aadhar_images = '';

			if (!empty($result['no_of_guest'])) {
				$aadhar_images_url = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images/' : 'aadhar_images/test/');

				switch ($result['no_of_guest']) {
					case '1':
						$aadhar_images = $aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_1.png';
						break;

					case '2':
						$aadhar_images = $aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_1.png' . "\r\n" . $aadhar_images_url . 'user_' . $result['user_id'] . '_' . $result['book_id'] . '_2.png';
						break;

					default:
						break;
				}
			}

			$user_info = $this->user_model->get($result['user_id']);
			$book_info = $this->book_model->get($result['book_id']);
			$event_info = $this->event_model->get($result['event_id']);

			$url = vsprintf(USER_URL . 'registration/submitdetail?uid=%s&code=%s&bid=%s&eid=%s', [
				$user_info['id'],
				$user_info['verification_code'],
				$result['book_id'],
				$result['event_id'] ?? 4,
			]);

			$invites[] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'event'				=> $event_info['name'] ?? '',
				'book_name'			=> $book_info['name'],
				'author_name'		=> $book_info['name'],
				'email'				=> $result['email'],
				'mobile'			=> $result['mobile'],
				'book_rank'			=> $result['book_rank'],
				'invite_status'		=> $invite_status,
				'guest_count'		=> $result['no_of_guest'],
				// 'location'			=> $result['location'],
				// 'source'			=> $result['source'],
				// 'date_added'		=> formatDate($result['invite_date_added']),
				// 'invite_urls'		=> !empty($result['no_of_guest'])
				// 	? $url . "\r\n" . base_url() . 'uploads/eventpass/pdfs/entry_pass_' . sha1(md5($result['user_id'] . '_' . $result['book_id'] . $this->config->item('password_salt'))) . '.pdf' . "\r\n" . $aadhar_images
				// 	: $url,
			];
		}

		self::_downloadCsv($invites, 'invites_');

		output_json($json);
	}

	public function download_nyaf_author_pdfs_zip() {
		$this->load->library('zip');

		$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

		$results = $this->user_details_guest_model->get_user_details_guest();

		if(empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/nyaf_authors_invite'), 'refresh');
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_authors_guest_pdfs.zip';

		foreach ($results as $result) {
			$entry_pass = 'entry_pass_' . $result['code'] . '.pdf';

			$this->zip->add_data($entry_pass, @file_get_contents(base_url('uploads/eventpass/pdfs/') . $entry_pass));
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function download_nyaf_author_csv() {
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$results = $this->user_details_invite_model->getNyafAuthorInvite([
			'sort'		=> 'user_details_nyaf_invites.book_sold',
			'order'		=> 'DESC',
		]);

		if(empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/nyaf_authors_invite'), 'refresh');
		}

		$authorInvites = [];

		$filename = 'author_invites_' . date('Y_m_d_H_i_s') . '.csv';

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$i = 0;

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_model->get($result['book_id']);

			$page_info = $this->page_version_model->get_all([
				'book_id'	=> $result['book_id'],
				'start'		=> 0,
				'limit'		=> 1,
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'][0] ?? [];

			$texts = json_decode($page_info['texts']);
			$book_desc = substr(strip_tags(html_entity_decode($texts[0])), 0, 250);

			$invite_status = '';
			switch ($result['invite_status']) {
				case '1':
					$invite_status = 'Accepted';
					break;

				case '2':
					$invite_status = 'Rejected';
					break;

				default:
					$invite_status = 'Pending';
					break;
			}

			$state_info = $this->state_model->get($result['state_id']);
			$city_info = $this->city_model->get($result['city_id']);

			$authorInvites[] = [
				'id'				=> $i,
				'book_id'			=> $result['book_id'],
				'user_id'			=> $result['user_id'],
				'book_name'			=> $book_info['name'],
				'book_desc'			=> $book_desc,
				'book_rank'			=> $result['book_rank'],
				'book_sold'			=> $result['book_sold'],
				'book_isbn'			=> $book_info['isbn'],
				'author_name'		=> trim($result['first_name'] . ' ' . $result['last_name']),
				'author_email'		=> $result['email'],
				'author_mobile'		=> $result['mobile'],
				'author_bio'		=> $book_info['author_bio'],
				'invite_status'		=> $invite_status,
				'no_of_guest'		=> $result['no_of_guest'],
				'state'				=> $state_info['name'] ?? '',
				'city'				=> $city_info['name'] ?? '',
				'location'			=> $result['location'],
				'verified'			=> $result['verified'],
				'site_code'			=> $result['source'],
				'date_added'		=> formatDate($result['invite_date_added'])
			];

			$i++;
		}

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($authorInvites[0]) ? array_keys($authorInvites[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($authorInvites, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function nyaf_schools_invite() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['events'] 		= $this->event_model->get_all()['rows'] ?? [];

		$data['page_name'] 		= 'students/nyaf_schools_invite';
		$data['page_title'] 	= _l('nyaf_schools_invite');
		$data['action_csv'] 	= site_url('admin/download_nyaf_school_csv');
		$data['action_zip'] 	= site_url('admin/download_nyaf_school_pdfs_zip');
		$data['action_ajax'] 	= site_url('admin/ajax_nyaf_schools_invite');

		$this->load->view('backend/index', $data);
	}

	public function ajax_nyaf_schools_invite() {
		$json['data'] = [];

		// $columns = $this->input->get('columns');

		// $filter_data = [
		// 	'event_id'			=> 21,
		// 	'start'				=> (int)$this->input->get('start'),
		// 	'limit'				=> (int)$this->input->get('length'),
		// 	'search'			=> $this->input->get('search[value]'),
		// 	'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
		// 	'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		// ];

		$columns = $this->input->get('columns');

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'status'			=> $this->input->get('status') < 0 || is_null($this->input->get('status')) ? null : (int)$this->input->get('status'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			// 'start'				=> (int)$this->input->get('start'),
			// 'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'school_details_nyaf_invites.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');
		$this->load->model('school/SchoolDetailsGuest_model', 'school_details_guest_model');

		$results = $this->school_details_invite_model->getNyafSchoolInvite($filter_data);

		log_kb([
			'status' => $this->input->get('status'),
			'ajax_nyaf_schools_invite' => $results
		]);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$site_info = $this->db->get_where('site', ['id' => $result['site_id']])->row_array();

			$entry_status = $this->school_details_guest_model->getByDetails([
				'site_id'	=> $result['site_id'],
				'event_id'	=> $result['event_id'],
			]) ?? [];

			$invite_status = '';
			switch ($result['invite_status']) {
				case '1':
					$invite_status = '<span class="text-success">Accepted</span>';
					break;

				case '2':
					$invite_status = '<span class="text-danger">Rejected</span>';
					break;

				default:
					$invite_status = '<span class="text-warning">Pending</span>';
					break;
			}

			$aadhar_images = '';

			if(!empty($result['no_of_guest'])) {
				$aadhar_images_url = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images_school/' : 'aadhar_images_school/test/');

				switch ($result['no_of_guest']) {
					case '1':
						$aadhar_images = ' <a target="_blank" title="Aadhar Image 1" href="'. $aadhar_images_url . 'user_' . $result['site_id'] . '_1.png' .'"><i class="fa fa-download"></i></a>';
						break;

					default:
						break;
				}
			}

			$url = vsprintf(USER_URL . 'schoolregistration?site_id=%s&code=%s', [
				$site_info['id'],
				$site_info['site_code']
			]);

			$json['data'][] = [
				'result' => $result,
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'name'				=> vsprintf(_l('%s <br> %s <br> %s'), [
					$result['name'],
					$result['owner_email'],
					$result['owner_mobile'],
				]),
				'invite_status'		=> $invite_status,
				'guest_count'		=> $result['no_of_guest'],
				'location'			=> $result['country_code'],
				'verified'			=> sprintf('%s <br> %d %s', _sd($entry_status ['verified']), $entry_status ['guest_count'], _l('guests')),
				'source'			=> $result['site_code'],
				'date_added'		=> formatDate($result['invite_date_added']),
				'actions'			=> !empty($result['no_of_guest']) ? '<a target="_blank" title="Form Link" href="'. $url .'"><i class="fa fa-link"></i></a> <a target="_blank" title="Entry Pass" href="'. base_url().'uploads/eventpass/pdfs/school_entry_pass_'.sha1(md5($result['site_id'] . $this->config->item('password_salt'))).'.pdf' .'"><i class="fa fa-download"></i></a>' . $aadhar_images : '<a target="_blank" title="Form Link" href="'. $url .'"><i class="fa fa-link"></i></a>',
			];
		}

		output_json($json);
	}

	public function export_school_invites() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'status'			=> $this->input->get('status') < 0 || is_null($this->input->get('status')) ? null : (int)$this->input->get('status'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'school_details_nyaf_invites.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		$results = $this->school_details_invite_model->getNyafSchoolInvite($filter_data);

		log_kb([
			'status' => $this->input->get('status'),
			'ajax_nyaf_schools_invite' => $results
		]);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$site_info = $this->db->get_where('site', ['id' => $result['site_id']])->row_array();

			$invite_status = '';
			switch ($result['invite_status']) {
				case '1':
					$invite_status = 'Accepted';
					break;

				case '2':
					$invite_status = 'Rejected';
					break;

				default:
					$invite_status = 'Pending';
					break;
			}

			$invites[] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'site_id'			=> $result['site_id'],
				'school_name'		=> $result['name'],
				'invite_status'		=> $invite_status,
				'guest_count'		=> $result['no_of_guest'],
			];
		}

		self::_downloadCsv($invites, 'school_invites_');

		output_json($json);
	}

	public function download_nyaf_school_pdfs_zip() {
		$this->load->library('zip');

		$this->load->model('school/SchoolDetailsGuest_model', 'school_details_guest_model');

		$results = $this->school_details_guest_model->get_school_details_guest();

		if(empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/nyaf_schools_invite'), 'refresh');
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_schools_guest_pdfs.zip';

		foreach ($results as $result) {
			$entry_pass = 'entry_pass_' . $result['code'] . '.pdf';

			$this->zip->add_data($entry_pass, @file_get_contents(base_url('uploads/eventpass/pdfs/') . $entry_pass));
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function download_nyaf_school_csv() {
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		$results = $this->school_details_invite_model->getNyafSchoolInvite();

		if(empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/nyaf_schools_invite'), 'refresh');
		}

		$schoolInvites = [];

		$filename = 'school_invites_' . date('Y_m_d_H_i_s') . '.csv';

		foreach ($results['rows'] ?? [] as $key => $result) {
			$school_details_guest_info = $this->db->get_where('school_details_nyaf_guest', ['site_id' => $result['site_id']])->row_array();

			$invite_status = '';
			switch ($result['invite_status']) {
				case '1':
					$invite_status = 'Accepted';
					break;

				case '2':
					$invite_status = 'Rejected';
					break;

				default:
					$invite_status = 'Pending';
					break;
			}

			$schoolInvites[] = [
				'id'				=> $result['id'],
				'school_name'		=> $result['name'],
				'school_attendee'	=> $school_details_guest_info['guest_name_1'] ?? '',
				'school_email'		=> $result['owner_email'],
				'school_mobile'		=> $result['owner_mobile'],
				'invite_status'		=> $invite_status,
				'verified'			=> $school_details_guest_info['verified'] ?? '',
				'site_code'			=> $result['site_code'],
				'date_added'		=> formatDate($result['invite_date_added'])
			];
		}

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($schoolInvites[0]) ? array_keys($schoolInvites[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($schoolInvites, $fp, $headers);

		fclose($fp);

		exit();
	}
}
