<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait Books {
	public function books($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'edit') {
			$book_info = $this->book_model->get($param2);

			if ($this->input->post('isbn')) {
				$book_isbn_info = $this->book_model->get_all([
					'isbn'	=> $this->input->post('isbn'),
				])['rows'][0] ?? [];

				if ($book_isbn_info && $book_isbn_info['id'] != $book_info['id']) {
					$this->session->set_flashdata('error_message', _li('already_used_isbn_number'));
					redirect(base_url('admin/books'), 'refresh');
				}

				if (empty($book_info['isbn'])) {
					$this->alert_model->isbnAssignAlert($book_info['id']);
				}
			}

			$this->book_model->edit($param2, $this->input->post());

			$this->book_version_model->editByBookId(
				$book_info['id'],
				$book_info['version'],
				$this->input->post()
			);

			if (empty($book_info['amazon_url']) && !empty($this->input->post('amazon_url'))) {
				$this->alert_model->amazonBookPublishAlert($book_info['id']);
			}

			redirect(base_url('admin/approved_books'), 'refresh');
		} else if ($param1 == 'publish') {
			$this->book_model->edit($param2, [
				'status' => 1
			]);

			$this->bookstore_model->editByBookId($param2, [
				'status' => 1
			]);
			redirect(base_url('admin/approved_books'), 'refresh');
		} else if ($param1 == 'unpublish') {
			$this->book_model->edit($param2, [
				'status' => 2
			]);

			$this->bookstore_model->editByBookId($param2, [
				'status' => 2
			]);

			redirect(base_url('admin/approved_books'), 'refresh');
		}else if ($param1 == 'delete') {

			$bookstore_info 	= $this->bookstore_model->getByBookId($param2);
			$bookversion_info 	= $this->book_version_model->get_all([
				'book_id' => $param2
				])['rows'] ?? [];

			$this->book_model->delete($param2);
			$this->bookstore_model->delete($bookstore_info['id'] ?? 0);

			if (!empty($bookversion_info)) {
				$this->db->where_in('id', array_column($bookversion_info, 'id'));
				$this->db->update('book_version',  [
					'_deleted'		=> 1,
					'date_deleted'	=> date('Y-m-d H:i:s'),
				]);
			}
			redirect(base_url('admin/approved_books'), 'refresh');
		} elseif ($param1 == 'uploaded_on_amazon') {
			$this->amazon_book_model->add(['book_id' => $param2]);
			redirect(base_url('admin/approved_books'), 'refresh');
		}

		$data['timestamp_start']= strtotime('-30days', time());
		$data['timestamp_end']	= time();
		$data['status'] 		= 0;
		$data['page_name'] 		= 'books/index';
		$data['page_title'] 	= _l('all books');
		$data['action_ajax'] 	= base_url('admin/ajax_books');

		$this->load->view('backend/index', $data);
	}

	public function book_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'edit') {
			$data['page_name'] 		= 'books/form';
			$data['page_title'] 	= _l('book_edit');
			$data['action'] 		= base_url('admin/books/edit/' . (int)$param2);

			$data['id'] 			= (int)$param2;
			$data['details'] 		= $this->book_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function approved_books($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'theme',
			'user',
			'country',
			'name',
			'author_name',
			'date_added',
			'date_published',
			'date_approved',
			'date_title_verso',
			'status',
			'sold_book',
			'page_count',
			'actions',
		];
		$data['actions']		= [
			[
				'key' 		=> 'title_verso',
				'type' 		=> 'title_verso',
				'url' 		=> 'admin/title_verso/',
			],
			[
				'key' 		=> 'edit',
				'type' 		=> 'edit',
				'url' 		=> 'admin/book_form/edit/',
			],
			[
				'key' 		=> 'uploaded_on_amazon',
				'type' 		=> 'uploaded_on_amazon',
				'url' 		=> 'admin/books/uploaded_on_amazon/',
			],
			[
				'key' 		=> 'unpublish',
				'type' 		=> 'unpublish',
				'url' 		=> 'admin/books/unpublish/',
			],
			[
				'key' 		=> 'review',
				'type' 		=> 'review',
				'url' 		=> 'admin/reviewbook/',
			],
			[
				'key' 		=> 'delete',
				'type' 		=> 'confirm',
				'url' 		=> 'admin/books/delete/',
			],
			[
				'key' 		=> 'book_ai_review',
				'type' 		=> 'callback',
				'callback'	=> 'book_ai_review',
			],
			[
				'key' 		=> 'front_cover_patch',
				'type' 		=> 'confirm',
				'url' 		=> 'admin/book_front_cover_patch/',
			],
			[
				'key' 		=> 'change_genre',
				'type' 		=> 'callback',
				'callback'	=> 'change_genre',
			],
		];
		$data['timestamp_start']= strtotime('-30days', time());
		$data['timestamp_end']	= time();
		$data['status'] 		= 1;
		$data['page_name'] 		= 'books/index';
		$data['page_title'] 	= _l('approved_books');
		$data['action_ajax'] 	= base_url('admin/ajax_books/1');

		$this->load->view('backend/index', $data);
	}

	public function book_front_cover_patch($book_id = 0) {
		if ($book_info = $this->book_model->get($book_id)) {
			$this->bookstore_model->editByBookId($book_id, [
				'cover_image'	=> $book_info['cover_image']
			]);
			$this->book_version_model->editByBookId($book_id, $book_info['version'], [
				'cover_image'	=> $book_info['cover_image']
			]);

			success_message(_l('book_cover_patched_successfully'));
		} else {
			error_message(_l('book_not_found'));
		}

		redirect(base_url('admin/approved_books'), 'refresh');
	}

	public function book_change_genre() {
		$json = [];

		$book_id 		= (int)$this->input->post('book_id');
		$genre_id 		= (int)$this->input->post('genre_id');
		$category_id 	= (int)$this->input->post('category_id');

		if ($book_info = $this->book_model->get($book_id)) {
			$genre_info 	= $this->genre_model->get($genre_id);
			$category_info 	= $this->genre_model->get($category_id);
			$this->bookstore_model->editByBookId($book_id, [
				'genre_id'		=> $genre_id,
				'genre'			=> $genre_info['name'] ?? '',
				'category_id'	=> $category_id,
				'category'		=> $category_info['name'] ?? '',
			]);
			$this->book_version_model->editByBookId($book_id, $book_info['version'], [
				'genre_id'		=> $genre_id,
				'category_id'	=> $category_id,
			]);
			$this->book_model->edit($book_id, [
				'genre_id'		=> $genre_id,
				'category_id'	=> $category_id,
			]);

			$json['success'] 	= _l('genre_change_successfully');
		} else {
			$json['error'] 		= _l('book_not_found');
		}

		output_json($json);
	}

	public function archived_books($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'theme',
			'user',
			'country',
			'name',
			'author_name',
			'archived',
			'page_count',
			'date_added',
			'date_archived',
			'ip',
			'actions',
		];

		if ($param1 == 'unarchived') {
			$this->book_model->edit($param2, [
				'archived'	=> 0,
			]);
			redirect(base_url('admin/archived_books'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('archived_book');
		$data['action_ajax'] 	= base_url('admin/ajax_archived_book');

		$data['actions'] 		= [
			[
				'key'	=> 'unarchived',
				'type' 	=> 'confirm',
				'url'	=> 'admin/archived_books/unarchived/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_archived_book() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'archived'			=> 1,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->book_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$page_count		= $this->page_model->get_all([
				'book_id' 	=> $result['id'],
			])['total'] ?? 0;
			$archived_info 	= $this->book_archive_log_model->get_all([
				'book_id'	=> $result['id'],
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0] ?? [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'theme'					=> $result['category'] ?? '',
				'user'					=> $result['first_name'] ?? '',
				'country'				=> $result['location'] ?? '',
				'name'					=> $result['name'],
				'author_name'			=> $result['author_name'],
				'archived'				=> _sd($result['archived']),
				'page_count'			=> $page_count,
				'date_added'			=> formatDate($result['date_added']),
				'date_archived'			=> formatDate($archived_info['date_added']),
				'ip'					=> $archived_info['ip_address'],
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_books($status = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		if (!empty($this->input->get('search_value'))) {
			$filter_data['search'] = $this->input->get('search_value');
		}

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		if ($this->input->get('sku')) {
			$filter_data['book_id'] = (int)$this->input->get('sku');
		}

		if ($this->input->get('email')) {
			$filter_data['email'] = $this->input->get('email');
		}

		if ($this->input->get('isbn_country_code')) {
			$filter_data['isbn_country_code'] = $this->input->get('isbn_country_code');
		}

		if ($this->input->get('has_isbn')) {
			$filter_data['has_isbn'] = $this->input->get('has_isbn') == 2 ? 0 : 1;
		}

		if ($this->input->get('has_kdp_upload')) {
			$filter_data['has_kdp_upload'] = $this->input->get('has_kdp_upload') == 2 ? 0 : 1;
		}

		if ($this->input->get('has_amazon_url')) {
			$filter_data['has_amazon_url'] = $this->input->get('has_amazon_url') == 2 ? 0 : 1;
		}

		if ($this->input->get('download_title_verso')) {
			$filter_data['download_title_verso'] = $this->input->get('download_title_verso') == 2 ? 0 : 1;
		}

		/*if ($this->input->get('has_amazon_url')) {
			$filter_data['has_amazon_url'] = $this->input->get('has_amazon_url');
		}*/

		if ($this->input->get('quantity') != '') {
			$filter_data['quantity'] = (int)$this->input->get('quantity');
		}

		if ($this->input->get('quantity_le')) {
			$filter_data['quantity_le'] = (int)$this->input->get('quantity_le');
		}

		if ($this->input->get('quantity_ge')) {
			$filter_data['quantity_ge'] = (int)$this->input->get('quantity_ge');
		}

		if ($this->input->get('has_hall_of_fame')) {
			$filter_data['has_hall_of_fame'] = $this->input->get('has_hall_of_fame') == 2 ? 0 : 1;
		}

		if ($status) {
			$filter_data['admin_status'] = (int)$status;
		}

		$results = $this->bookstore_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$this->load->model('book/BookTitleVerso_model', 'book_title_verso_model');

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_book_info = $this->event_book_model->get_all(['book_id' => $result['id']]);
			$book_info = $this->book_model->get($result['id']);

			$event_id = (!empty($event_book_info['rows'][0])) ? $event_book_info['rows'][0]['event_id'] : '';

			$amazon_book_info = $this->amazon_book_model->get_all(['book_id' => $result['id']]);

			$amazon_book_id = (!empty($amazon_book_info['rows'][0])) ? $amazon_book_info['rows'][0]['id'] : '';

			$hall_of_fame_info = $this->hall_of_fame_model->get_all(['book_id'	=> $result['id']]);

			$hall_of_fame_id = (!empty($hall_of_fame_info['rows'][0])) ? $hall_of_fame_info['rows'][0]['id'] : '';

			$category_info = $this->category_model->get($result['category_id']);
			$user_info = $this->student_model->get($result['user_id']);
			$pages = $this->page_model->get_all([
				'book_id' => $result['id'],
			])['total'];
			$review_logs = $this->reviewlog_model->get_all([
				'book_id' => $result['id'],
				'order'	=> 'DESC'
			]);
			$reviewr_name = $this->user_model->get($result['reviewer_id']);

			$book_title_verso_info = $this->book_title_verso_model->get_all([
				'book_id'	=> $result['id']
			])['rows'][0] ?? [];

			$custom_theme_book  = $this->page_version_model->get_all([
				'book_id'		=> $result['id'],
				'version'		=> $result['version'],
				'is_custom_id'	=> 1,
			])['rows'][0] ?? [];

			$name = '<a href="' . USER_URL . 'bookstore/' . $book_info['slug'] . '" target="_blank">' . $book_info['name'] . '</a>';

			$name = sprintf('%s ISBN: %s', $name, $book_info['isbn']) . (
				$result['status'] == 1
					? (
						vsprintf('<br><a href="%s" class="btn btn-sm btn-danger">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-info">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-dark">%s</a>', [
							base_url('admin/printBook/' . $result['id'] . '/' . $result['version']),
							_li('PDF'),
							base_url('admin/printKdpBook/' . $result['id'] . '/' . $result['version']),
							_li('KDP'),
							base_url('admin/printGreyBook/' . $result['id'] . '/' . $result['version']),
							_li('BW'),
						])
					)
					: ''
			);

			$json['data'][] = [
				'sn'				=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">', //$filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'event_id'			=> $event_id,
				'amazon_book_id'	=> $amazon_book_id,
				'theme'				=> $category_info['name'],
				'custom_theme'		=> !empty($custom_theme_book) ? 'YES' : 'NO',
				'user'				=> ($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? ''),
				'country'			=> $user_info['location'] ?? '',
				'name'				=> $name,
				'isbn'				=> $book_info['isbn'],
				'author_name'		=> $result['author_name'],
				'reviewer'  		=> (isset($reviewr_name['first_name'])) ? $reviewr_name['first_name'] . ' ' . $reviewr_name['last_name'] : 'N/A',
				'commented'  		=> $review_logs,
				'status'			=> (($result['status'] == '2') ? (($review_logs['total'] > 0) ? '<i class="mdi mdi-circle" style="color: #4287f5; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="' . strip_tags($review_logs['rows'][0]['comment']) . '" data-original-title="%s"></i>' : _sd($result['status'])) : _sd($result['status'])) . (!empty($amazon_book_id) ? '<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="Amazon Book" data-original-title="Amazon Book"></i>' : '') . (!empty($hall_of_fame_id) ? '<i class="mdi mdi-circle" style="color: #39AFD1; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="Hall Of Fame Book" data-original-title="Hall Of Fame Book"></i>' : ''),
				'date_added'		=> formatDate($book_info['date_added']),
				'date_published'	=> formatDate($result['date_published']),
				'date_approved'		=> formatDate($book_info['date_approved']),
				'date_title_verso'	=> formatDate($book_title_verso_info['date_added'] ?? ''),
				'featured'			=> $result['featured'] == 0 ? _l('no') : _l('yes'),
				'page_count' 		=> $pages,
				'sold_book' 		=> $result['sold'] ?? 0,
				'actions'			=> [
					'id' 				=> $result['id'],
					'status' 			=> $result['status'] ?? 0
				],
			];
		}

		output_json($json);
	}

	public function in_review_books($param1 = NULL, $param2 = NULL) {
		$data['fields']			= [
			'sn',
			'id',
			'theme',
			'user',
			'country',
			'name',
			'author_name',
			'date_added',
			'date_published',
			'date_approved',
			'date_title_verso',
			'status',
			'sold_book',
			'page_count',
			'actions',
		];

		$data['page_name'] 		= 'books/index';
		$data['page_title'] 	= _l('books_in_review');
		$data['action_ajax'] 	= base_url('admin/ajax_in_review_books');
		$data['actions']		= [
			[
				'key' 		=> 'review',
				'type' 		=> 'review',
				'url' 		=> 'admin/reviewbook/',
			],
			[
				'key' 		=> 'publish',
				'type' 		=> 'publish',
				'url' 		=> 'admin/books/publish/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_in_review_books() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'status'			=> '2'
		];

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		if ($this->input->get('site_code')) {
			$filter_data['site_code'] = $this->input->get('site_code');
		}

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		$results = $this->book_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$this->load->model('book/BookTitleVerso_model', 'book_title_verso_model');

		foreach ($results['rows'] ?? [] as $key => $result) {
			$category_info = $this->category_model->get($result['category_id']);
			$user_info = $this->student_model->get($result['user_id']);
			$pages = $this->page_model->get_all([
				'book_id' => $result['id']
			])['total'];
			$review_logs = $this->reviewlog_model->get_all([
				'book_id' => $result['id'],
				'order'	=> 'DESC'
			]);
			$reviewr_name = $this->user_model->get($result['reviewer_id']);

			$book_title_verso_info = $this->book_title_verso_model->get_all([
				'book_id'	=> $result['id']
			])['rows'][0] ?? [];

			$json['data'][] = [
				'sn'				=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">', //$filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'theme'				=> $category_info['name'],
				'user'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'country'			=> $user_info['location'] ?? '',
				'name'				=> $result['name'],
				'author_name'		=> $result['author_name'],
				'date_added'		=> formatDate($result['date_added']),
				'date_published'	=> formatDate($result['date_published']),
				'date_approved'		=> formatDate($result['date_approved']),
				'date_title_verso'	=> formatDate($book_title_verso_info['date_added'] ?? ''),
				'status'			=> ($result['status'] == '2') ? (($review_logs['total'] > 0) ? '<i class="mdi mdi-circle" style="color: #4287f5; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="' . strip_tags($review_logs['rows'][0]['comment']) . '" data-original-title="%s"></i>' : _sd($result['status'])) : _sd($result['status']),
				'sold_book'			=> 0,
				'page_count' 		=> $pages,
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function reviewbook($id) {
		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('admin/books'), 'refresh');
		}

		$book = [];

		if ($id) {
			$book = $this->book_model->get($id);
			$cover_info = !empty($book['cover_id'])
				? $this->cover_model->get($book['cover_id'])
				: [];
			$heading_style = !empty($cover_info['heading_style'])
				? json_decode($cover_info['heading_style'], true)
				: [];
			$data['cover_info'] = $cover_info;
			$data['heading_style'] = !empty($heading_style['style'])
				? $heading_style['style']
				: [];

			if (empty($book)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(base_url('admin/books'), 'refresh');
			}
		}

		$search['book_id'] = $book['id'];
		$search['sort'] = 'page.sort_order';
		$search['order'] = 'ASC';
		$pages = $this->page_model->get_all($search);

		$data['page_name'] 		= 'books/review';
		$data['page_title'] 	= _l('book review');
		$data['action_ajax'] 	= base_url('');

		$data['book'] 		= $book;
		// $data['theme'] 		= $this->db->get();
		$data['pages'] 		= $pages;
		$data['page_index']		= $this->input->get('page_index') ?? '';
		$this->load->view('backend/index', $data);
	}

	public function book_review_comment($id = false) {
		if ($id) {
			$book = $this->book_model->get($id);

			if (empty($book)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(base_url('admin/books'), 'refresh');
			}

			if ($book['status'] != '2') {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(base_url('admin/books'), 'refresh');
			}
		}

		$status = trim($this->input->post('status'));
		$save =  array(
			'status' => $status,
			'reviewer_id' => $this->session->userdata['user_id']
		);

		if ($status) {
			$save['date_approved'] = date('Y-m-d H:i:s');
		}

		$book 	 = $this->book_model->edit($id, $save);
		$comment = trim($this->input->post('comment'));
		$subject = (!empty($this->input->post('mail_subject'))) ? trim($this->input->post('mail_subject')) : false;

		self::_approvedRejectMail($id, $subject, $comment);


		$this->session->set_flashdata('flash_message', 'Information sent to writer.');
		redirect(base_url('admin/books'), 'refresh');
	}

	private function _approvedRejectMail($id = false, $mySubject = false, $comment = false) {
		$this->load->model('Alert_model', 'alert_model');

		if (!$id) return false;

		$book = $this->book_model->get($id);

		if (empty($book['user_id']))
			return false;

		$author_info = $this->student_model->get($book['user_id']);

		if (empty($author_info))
			return false;

		if (empty($author_info['email']))
			return false;

		if ($book['status'] == '0') {
			// $data = get_settings('email_book_reject');
			$subject = $book['author_name'] . ' your book ' . $book['name'] . ' is rejectd.';
			$this->alert_model->bookApproved($id, $subject);
		} else {
			// $data = get_settings('email_book_verified');
			$subject = (strpos($author_info['source'], 'NYAFIND') !== false ? _li($book['author_name'] . ' your book ' . $book['name'] . ' is approved & published') : $book['author_name'] . ' your book ' . $book['name'] . ' is approved.');
			$this->alert_model->bookApproved($id, $subject);
		}

		if (empty($data))
			return false;

		$find = [
			'{author_name}',
			'{book_name}',
			'{comment}',
		];

		$replace = [
			'author_name'	=> $book['author_name'],
			'book_name'		=> $book['name'],
			'comment'		=> $comment,
		];

		$message = str_replace($find, $replace, $data);

		// $this->alert_model->email($author_info['email'], $subject, $message, NULL);

		if (!empty($mySubject))
			// $this->alert_model->email($author_info['email'], $mySubject, $message, NULL);
			//$this->alert_model->email('crm.dev.1@youbooks.co',$subject,$message,NULL);

			return true;
	}

	public function title_update() {
		$book_id = $this->input->post('bookid');
		$text = $this->input->post('mce_4');

		$page_detail = $this->book_model->get($book_id);

		if ($page_detail) {
			$this->revieweditlogs_model->add([
				'page_id' => $book_id,
				'reviewer_id' => $this->session->userdata('user_id'),
				'old_content' => $page_detail['name'],
				'new_content' => $text,
				'type' => '2'
			]);
			$this->book_model->edit($book_id, [
				'name' => $text
			]);
			$this->book_version_model->editByBookId($page_detail['id'], $page_detail['version'], [
				'name' => $text
			]);
			$this->session->set_flashdata('flash_message', 'Book Title Updated successfully.');
			redirect(base_url('admin/reviewbook/') . $book_id);
		}
		$this->session->set_flashdata('error_message', 'Mail subject or message body is missing.');
		redirect(base_url('admin/reviewbook/') . $book_id);
	}

	public function page_update() {
		$page_detail = $this->page_model->get($this->input->post('pageid'));

		if ($page_detail) {
			$book_info = $this->book_model->get($page_detail['book_id']);

			if ($book_info) {
				$this->revieweditlogs_model->add([
					'page_id' => $this->input->post('pageid'),
					'reviewer_id' => $this->session->userdata('user_id'),
					'old_content' => $page_detail['texts'],
					'new_content' => json_encode([$this->input->post('content')], false)
				]);

				$this->page_model->edit($this->input->post('pageid'), [
					'texts' => json_encode([$this->input->post('content')], false)
				]);

				$page_version_detail = $this->page_version_model->get_all([
					'page_id'	=> $page_detail['id'],
					'book_id'	=> $page_detail['book_id'],
					'version'	=> $book_info['version'],
					'theme_id'	=> $page_detail['theme_id'],
					'start'		=> 0,
					'limit'		=> 1,
					'sort'		=> 'page_version.sort_order',
					'order'		=> 'ASC',
				])['rows'][0] ?? [];

				if(!empty($page_version_detail['id'])) {
					$this->page_version_model->edit($page_version_detail['id'], [
						'texts' => json_encode([$this->input->post('content')], false)
					]);
				}

				$this->session->set_flashdata('flash_message', 'Page Updated successfully.');
				redirect(base_url('admin/reviewbook/') . $page_detail['book_id'] . '?page_index=' . $this->input->post('page_index'));
			}
		}

		$this->session->set_flashdata('error_message', 'Mail subject or message body is missing.');
		redirect(base_url('admin/reviewbook/') . $page_detail['book_id'] . '?page_index=' . $this->input->post('page_index'));
	}

	public function delete_book() {
		$ids = $this->input->post('ids');
		if (empty($ids)) {
			echo json_encode(array('status' => false, 'message' => 'Please select at least 1 record'));
			exit();
		}
		for ($i = 0; $i < count($ids); $i++) {
			$this->book_model->delete($ids[$i]);
		}
		$this->session->set_flashdata('flash_message', 'Data deleted successfully.');
		echo json_encode(array('status' => true));
		exit();
	}

	public function title_verso($id) {
		if ($book_info = $this->book_model->get($id)) {
			if(!empty($book_info['isbn'])) {
				$this->load->model('book/BookTitleVerso_model', 'book_title_verso_model');

				$book_title_verso_info = $this->book_title_verso_model->get_all([
					'book_id'	=> $id
				])['rows'][0] ?? [];

				if(empty($book_title_verso_info)) {
					$this->book_title_verso_model->add([
						'book_id'		=> $id,
						'manager_id'	=> $this->session->userdata('user_id'),
					]);
				}
			}

			$dompdf = new Dompdf();

			$data['data'] = $book_info;
			// pr($data);die;

			$html = $this->load->view('backend/admin/books/title_verso', $data, true);
			$dompdf->loadHtml($html);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream('title_verso_' . str_replace('-', '_', $book_info['slug']) . date('Y_m_d_H_i_s') . '.pdf');
		}
	}

	public function ajax_search_books() {
		$json = [];

		$filter_data = [
			'status'			=> 1,
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->book_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}

	public function ajax_book_versions() {
		$data = [];

		$filter_data = [
			'book_id'			=> $this->input->get('book_id'),
			'order'				=> 'ASC'
		];

		$results = $this->book_version_model->get_all($filter_data)['rows'] ?? [];


		foreach ($results as $key => $result) {
			$data[] = [
				'id'				=> $result['id'],
				'version'			=> $result['version'],
			];
		}

		$json['success'] = $data;
		output_json($json);
	}
}
