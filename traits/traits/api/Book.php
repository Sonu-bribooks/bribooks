<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Book {
	public function getBook() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$book_info = $this->book_model->get($this->input->post('book_id'));

			if ($book_info['status'] == 3) {
				$this->json['error'] = _l('book_not_found');
				return;
			}

			$this->json['book'] 	= $book_info;
			$this->json['success'] 	= _l('book_fetched');
		}
	}

	public function getFeaturedBooks() {
		$filter_data = [];

		$location = 'india';

		$user_country_code = ''; // strtolower($this->input->cookie('user_country_code'));

		if (empty($user_country_code)) {
			$user_country_code = strtolower($this->config->item('site_country_code'));
		}

		if (!empty($user_country_code) && $user_country_code !== 'in') {
			$location = 'united states';
		}

		$filter_data = [
			'start'			=> 0,
			'limit'			=> 8,
			'sort'			=> 'bookstore.sold',
			'order'			=> 'DESC',
			'location'		=> $location,
		];

		$cache_key = vsprintf('%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			'featured_books',
			implode('_', array_keys($filter_data)),
			str_replace(' ', '', implode('_', array_values($filter_data))),
		]);

		$books = json_decode($this->cache->get($cache_key), true);

		log_kb(['FeaturedBooks::cache_data::' => $books]);

		if (empty($books)) {
			$result = $this->bookstore_model->get_all($filter_data);

			if ($location == 'india') {
				$books = array_map(function($item) {
					return self::_addRatingAndSold($item);
				}, $result['rows'] ?? []);
			} else {
				$books = array_map(function($item) {
					return $item;
				}, $result['rows'] ?? []);
			}

			$this->cache->save($cache_key, json_encode($books), 3600);
		}

		$this->json['books'] = $books;
		$this->json['competition'] = 0;
	}

	public function getBookPrice() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$this->book_model->updateViews($this->input->post('book_id'));
			$this->bookstore_model->updateViews($this->input->post('book_id'));

			self::_formatBookPrice($this->input->post('book_id'));

			$this->json['price']['preview_block'] = false;

			if (($this->json['price']['status'] ?? 0) == 3 && empty($this->order_product_model->getPurchasedBooks( [
				'user_id'			=> $this->session->userdata('user_id'),
				'product_id'		=> $this->input->post('book_id'),
				'option_type'		=> [1],
			])['rows'][0] ?? [])) {
				$this->json['price']['preview_block'] = true;
			}
		}
	}

	public function getBookBySlug() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required|min_length[3]|max_length[255]');
		self::_runFormValidation();

		if (!$this->json) {
			if ($book_info = $this->book_model->getBySlug($this->input->post('slug'))) {
				if (empty($this->book_version_model->getByVersion($book_info['id'], $book_info['version']))) {
					$this->load->library('Version_lib', 'version_lib');
					$this->version_lib->applyFallback($book_info['id'], $book_info['version']);
				}

				$book_version_info = $this->book_version_model->getByVersion($book_info['id'], $book_info['version']);

				$this->json['book'] = array_merge($book_version_info, [
					'version_id'	=> $book_version_info['id'],
					'id'			=> $book_version_info['book_id'],
					'category'		=> _li($book_version_info['category']),
					'genre'			=> _li($book_version_info['genre']),
				]);
				$this->json['book']['is_free_author'] = $this->session->userdata('user_id') != $book_info['user_id'] &&
					$this->order_model->getAuthorProducts([
						'product_id'	=> $book_info['id'],
					]) == 0
						? true
						: false; // $this->book_model->isFreeAuthor($book_info['id']);
				$this->json['watermark'] = true;

				self::_addAppreciation($this->json['book']);

				if (empty($this->json['book']['isbn'])) {
					$this->json['book']['isbn'] = $this->config->item('default_isbn');
				}

				$this->json['page'] = $this->page_version_model->get_all([
					'book_id'	=> $book_info['id'],
					'version'	=> $book_info['version'],
					'start'		=> 0,
					'limit'		=> 1,
					'sort'		=> 'page_version.sort_order',
					'order'		=> 'ASC',
				])['rows'][0] ?? [];

				$texts = json_decode($this->json['page']['texts']);
				$this->json['page']['texts'] = [strip_tags(html_entity_decode($texts[0] ?? ''))];

				$this->json['reviews'] = $this->review_model->get_all([
					'book_id'	=> $book_info['id'],
					'status'	=> 1,
				])['rows'] ?? [];

				self::_formatBookPrice($book_info['id']);
			} else {
				$this->json['error'] = _l('book_not_found');
			}
		}
	}

	public function getBookShareBySlug() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required|min_length[3]|max_length[255]');
		self::_runFormValidation();

		if (!$this->json) {
			if ($book_info = $this->book_model->getBySlug($this->input->post('slug'))) {
				if (empty($this->book_version_model->getByVersion($book_info['id'], $book_info['version']))) {
					$this->load->library('Version_lib', 'version_lib');
					$this->version_lib->applyFallback($book_info['id'], $book_info['version']);
				}

				$book_version_info 	= $this->book_version_model->getByVersion($book_info['id'], $book_info['version']);
				$auhtor_info 		= $this->user_model->get($book_info['user_id']);
				$state_info 		= $this->state_model->get($auhtor_info['state_id']);
				$city_info 			= $this->city_model->get($auhtor_info['city_id']);

				$this->json['book'] = array_merge($book_version_info, [
					'version_id'	=> $book_version_info['id'],
					'id'			=> $book_version_info['book_id'],
				]);
				$this->json['book']['is_free_author'] = $this->session->userdata('user_id') != $book_info['user_id'] &&
					$this->order_model->getAuthorProducts([
						'product_id'	=> $book_info['id'],
					]) == 0
						? true
						: false; // $this->book_model->isFreeAuthor($book_info['id']);
				$this->json['watermark'] = true;

				self::_addAppreciation($this->json['book']);

				if (empty($this->json['book']['isbn'])) {
					$this->json['book']['isbn'] = $this->config->item('default_isbn');
				}

				$this->json['page'] = $this->page_version_model->get_all([
					'book_id'	=> $book_info['id'],
					'version'	=> $book_info['version'],
					'start'		=> 0,
					'limit'		=> 1,
					'sort'		=> 'page_version.sort_order',
					'order'		=> 'ASC',
				])['rows'][0] ?? [];

				$texts = json_decode($this->json['page']['texts']);
				$this->json['page']['texts'] = [strip_tags(html_entity_decode($texts[0]))];

				$this->json['reviews'] = $this->review_model->get_all([
					'book_id'	=> $book_info['id'],
					'status'	=> 1,
				])['rows'] ?? [];

				$this->json['book']['state'] = $state_info['name'] ?? '';
				$this->json['book']['city'] = $city_info['name'] ?? '';

				self::_formatBookPrice($book_info['id']);
			} else {
				$this->json['error'] = _l('book_not_found');
			}
		}
	}

	public function getBookByToken() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required|min_length[3]|max_length[255]');
		$this->form_validation->set_rules('token', _l('token'), 'trim|required|min_length[3]|max_length[255]');
		self::_runFormValidation();

		if (!$this->json) {
			if ($book_info = $this->book_model->getBookByToken(
				$this->input->post('slug'),
				$this->input->post('token')
			)) {
				$this->json['page'] = $this->page_model->get_all([
					'book_id'	=> $book_info['id'],
					'start'		=> 0,
					'limit'		=> 1,
					'sort'		=> 'page.sort_order',
					'order'		=> 'ASC',
				])['rows'][0] ?? [];

				$this->json['book'] = array_merge(
					$book_info,
					[
						'texts' => json_decode($this->json['page']['texts'])
					]
				);
			} else {
				$this->json['error'] = _l('book_not_found');
			}
		}
	}

	private function _formatBookPrice($book_id = 0) {
		$book_info = $this->book_model->get($this->input->post('book_id'));
		$user_info = $this->session->userdata('user_id')
			? $this->user_model->get($this->session->userdata('user_id'))
			: []
		;

		$this->json['site_id']	= $this->config->item('site_id');

		$book_price = $this->book_model->getPrice($book_id);

		if (!empty($book_info['user_id']) && $book_info['user_id'] == $this->session->userdata('user_id')) {
			$book_price = $this->discount_lib->applyAuthorDiscount(
				$book_id,
				$book_price,
				1,
			);

			if (!empty($book_price['black_white_price'])) {
				$book_price = $this->discount_lib->applyAuthorDiscount(
					$book_id,
					$book_price,
					1,
					'black_white'
				);
			}
		}

		$is_free_author = false;

		if (($book_info['user_id'] ?? 0) == $this->session->userdata('user_id')) {
			$book_price['ebook_price'] = 0.0;
		} else {
			self::_checkDeliveryCountry($book_price);
		}

		$this->json['price'] = array_merge(
			$book_price,
			[
				'currency' 			=> $this->config->item('site_currency_symbol'),
				'currency_code' 	=> $this->config->item('site_currency_code'),
				'price_per_page' 	=> $this->config->item('site_price_per_page'),
				'free_page_limit' 	=> $this->config->item('site_free_page_limit'),
				'free_books'	 	=> ($book_info['user_id'] ?? -1) == ($user_info['id'] ?? 0) ? ($user_info['hard_copy'] ?? 0) : 0,
				'paperback'	 		=> 0,
				'hard_cover'	 	=> $this->config->item('site_hard_cover_price'),
				'is_free_author'	=> $is_free_author,
				'amazon_price'		=> currency($book_info['amazon_price'] ?? 0),
				'can_read'			=> self::_validateSubscription(),
				'status'			=> $book_info['status'] ?? 0
			]
		);
	}

	public function getBooks() {
		$this->form_validation->set_rules('page', _l('page'), 'trim|numeric');
		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [];

			$location = '';

			$user_country_code = ''; // strtolower($this->input->cookie('user_country_code'));

			if (empty($user_country_code)) {
				$user_country_code = strtolower($this->config->item('site_country_code'));
			}

			$sort 	= 'bookstore.sold';
			$order 	= 'DESC';

			if (!empty($this->input->post('sort'))) {
				$sort = 'bookstore.' . (string)$this->input->post('sort');
			}

			if (!empty($this->input->post('order'))) {
				$order = (string)$this->input->post('order');
			}

			$filter_data = [
				'status'	=> 1,
				'start'		=> $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 16
					: 1,
				'limit'		=> 16,
				'sort'		=> $sort,
				'order'		=> $order
			];

			if (!empty($user_country_code) && $user_country_code !== 'in') {
				$filter_data['ne_location'] = 'india';
			} else {
				$filter_data['location'] 	= 'india';
				$location 					= 'india';
			}

			if ($this->input->post('page') && $this->input->post('search')) {
				$filter_data['ne_location'] = '';
				$filter_data['location'] = '';
				$filter_data['search'] = (string)$this->input->post('search');
			}

			if (empty($this->input->post('search'))) {
				$filter_data['quantity_ge'] = '1';
			}

			if ($this->input->post('preapproved')) {
				unset($filter_data['status']);
				$filter_data['ne_status'] = 0;
			}

			if ($this->input->post('genre_id')) {
				$filter_data['genre_id'] = $this->input->post('genre_id');
			}

			$cache_key = vsprintf('%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'get_books',
				implode('_', array_keys($filter_data)),
				str_replace(' ', '', implode('_', array_values($filter_data))),
			]);

			$result = json_decode($this->cache->get($cache_key), true);

			$books = $result['books'] ?? [];
			$total = $result['total'] ?? 0;

			if (empty($books) || empty($total) || !empty($this->input->post('search'))) {
				$result = $this->bookstore_model->get_all($filter_data);

				$sort_order = [];

				if ($location == 'india') {
					$books = array_map(function($item) use(&$sort_order) {
						// Fallback support for old books
						if (empty($this->book_version_model->getByVersion($item['id'], $item['version']))) {
							$this->load->library('Version_lib', 'version_lib');
							$this->version_lib->applyFallback($item['id'], $item['version']);
						}

						$book_version_info = $this->book_version_model->getByVersion($item['id'], $item['version']);

						$item = array_merge($book_version_info, [
							'version_id'	=> $book_version_info['id'],
							'id'			=> $book_version_info['book_id'],
							'views'			=> $item['views'],
							'sold'			=> $item['sold'],
						]);

						$item = self::_addRatingAndSold($item);

						// $sort_order[] = $item['rating'];
						$sort_order[] = $item['site_id'];

						$item['genre_id'] = $book_version_info['genre_id'] ?? 0;

						return format_bookstore_info($item);
					}, $result['rows'] ?? []);
				} else {
					$books = array_map(function($item) use(&$sort_order) {
						// Fallback support for old books
						if (empty($this->book_version_model->getByVersion($item['id'], $item['version']))) {
							$this->load->library('Version_lib', 'version_lib');
							$this->version_lib->applyFallback($item['id'], $item['version']);
						}

						$book_version_info 	= $this->book_version_model->getByVersion($item['id'], $item['version']);

						$item = array_merge($book_version_info, [
							'version_id'	=> $book_version_info['id'],
							'id'			=> $book_version_info['book_id'],
							'views'			=> $item['views'],
						]);

						$sort_order[] = $item['site_id'];

						$item['genre_id'] = $book_version_info['genre_id'] ?? 0;

						return format_bookstore_info($item);
					}, $result['rows'] ?? []);
				}

				// array_multisort($sort_order, SORT_DESC, $books, SORT_ASC);
				$total = $result['total'] ?? 0;

				$this->cache->save($cache_key, json_encode(['books' => $books, 'total' => $total]), 3600);
			}

			$this->json['books'] = $books;
			$this->json['total'] = $total;
			$this->json['filter_data'] = $filter_data;

			if (!empty($this->input->post('search'))) {
				$this->search_log_model->add([
					'search'	=> (string)$this->input->post('search'),
					'user_id'	=> (int)$this->session->userdata('user_id'),
					'ip'		=> $this->input->ip_address(),
				]);
			}
		}
	}

	public function getSitemapBooks() {
		$this->form_validation->set_rules('page', _l('page'), 'trim|numeric');
		self::_runFormValidation();

		if (!$this->json) {
			$filter_data 	= [];
			$location 		= '';

			$sort 	= 'bookstore.sold';
			$order 	= 'DESC';

			$filter_data = [
				'status'	=> 1,
				'start'		=> $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 16
					: 1,
				'limit'		=> 16,
				'sort'		=> $sort,
				'order'		=> $order
			];

			$cache_key = vsprintf('%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'get_sitemap_books',
				implode('_', array_keys($filter_data)),
				str_replace(' ', '', implode('_', array_values($filter_data))),
			]);

			$result = json_decode($this->cache->get($cache_key), true);

			$books = $result['books'] ?? [];
			$total = $result['total'] ?? 0;

			if (empty($books) || empty($total) || !empty($this->input->post('search'))) {
				$result = $this->bookstore_model->get_all($filter_data);

				$sort_order = [];

				if ($location == 'india') {
					$books = array_map(function($item) use(&$sort_order) {
						// Fallback support for old books
						if (empty($this->book_version_model->getByVersion($item['id'], $item['version']))) {
							$this->load->library('Version_lib', 'version_lib');
							$this->version_lib->applyFallback($item['id'], $item['version']);
						}

						$book_version_info = $this->book_version_model->getByVersion($item['id'], $item['version']);

						$item = array_merge($book_version_info, [
							'version_id'	=> $book_version_info['id'],
							'id'			=> $book_version_info['book_id'],
							'views'			=> $item['views'],
							'sold'			=> $item['sold'],
						]);

						$item = self::_addRatingAndSold($item);

						// $sort_order[] = $item['rating'];
						$sort_order[] = $item['site_id'];

						$item['genre_id'] = $book_version_info['genre_id'] ?? 0;

						return $item;
					}, $result['rows'] ?? []);
				} else {
					$books = array_map(function($item) use(&$sort_order) {
						// Fallback support for old books
						if (empty($this->book_version_model->getByVersion($item['id'], $item['version']))) {
							$this->load->library('Version_lib', 'version_lib');
							$this->version_lib->applyFallback($item['id'], $item['version']);
						}

						$book_version_info 	= $this->book_version_model->getByVersion($item['id'], $item['version']);

						$item = array_merge($book_version_info, [
							'version_id'	=> $book_version_info['id'],
							'id'			=> $book_version_info['book_id'],
							'views'			=> $item['views'],
						]);

						$sort_order[] = $item['site_id'];

						$item['genre_id'] = $book_version_info['genre_id'] ?? 0;

						return $item;
					}, $result['rows'] ?? []);
				}

				// array_multisort($sort_order, SORT_DESC, $books, SORT_ASC);
				$total = $result['total'] ?? 0;

				$this->cache->save($cache_key, json_encode(['books' => $books, 'total' => $total]), 3600);
			}

			$this->json['books'] = $books;
			$this->json['total'] = $total;
			$this->json['filter_data'] = $filter_data;
		}
	}

	public function getReviewBooks() {
		$this->form_validation->set_rules('page', _l('page'), 'trim|numeric');
		self::_runFormValidation();

		if (!$this->json) {
			$this->json['books'] = $result['rows'] ?? [];
			$this->json['total'] = $result['total'] ?? 0;
		}
	}

	public function getTotalBooks() {
		if (!$this->json) {
			$result = $this->book_model->get_all([
				'status'	=> 1,
			]);

			$this->json['total'] = $result['total'] ?? 0;
		}
	}

	public function getUserBooks() {
		if (!$this->json) {
			$user_info = $this->user_model->get($this->session->userdata('user_id'));

			$result = array_map(function($item) use($user_info) {
				$item = self::_addOriginalRatingAndSold($item);

				CI_Events::trigger('access_log', [
					'module'	=> 'user_dashboard'
				]);

				$event_book_info	= $this->event_book_model->get_all(['book_id' => $item['id']])['rows'][0] ?? '';
				$event_info			= !empty($event_book_info) ? $this->event_model->get($event_book_info['event_id']) : '';

				$country_code 	= $this->input->cookie('user_country_code', true) ?? '';
				$options 		= bookBuyOptions($item['id'], $country_code);
				$can_listen		= !empty($user_info['subscription_plan_id'] ?? 0) ? true : false;

				if (!empty($options)) {
					if (in_array('audio_book', $options)) {
						$can_listen		= true;
					}
				}

				return array_merge($item, [
					'name'			=> $item['name'] ? $item['name'] : ' ',
					'hard_copy'		=> $user_info['hard_copy'] ?? 0,
					'event_label'	=> !empty($event_info) ? $event_info['label'] : NULL,
					'can_listen'	=> $can_listen,
				]);
			}, $this->book_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'archived'	=> 0,
				'ne_status'	=> 3,
			])['rows'] ?? []);

			$this->json['books'] = $result;
		}
	}

	public function getActiveUserBooks() {
		if (!$this->json) {
			$user_info = $this->user_model->get($this->session->userdata('user_id'));

			$user_event_info = $this->event_user_model->get_all([
				'user_id'					=> $user_info['id'],
				'is_active_book_writing'	=> 1
			])['rows'][0] ?? [];

			if (!empty($user_event_info)) {
				$books = $this->book_model->get_all([
					'user_id'	=> (int)$this->session->userdata('user_id'),
					'archived'	=> 0,
					'event_id'	=> $user_event_info['event_id'] ?? 0,
					'status'	=> 1,
				])['rows'] ?? [];
			} else {
				$books = $this->book_model->get_all([
					'user_id'	=> (int)$this->session->userdata('user_id'),
					'archived'	=> 0,
					'status'	=> 1,
					'event_ne'	=> 1,
				])['rows'] ?? [];
			}

			$this->json['books'] = $books;
		}
	}

	public function getAuthorBooksBySlug() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required|min_length[4]|max_length[255]');
		self::_runFormValidation();

		if (!$this->json) {
			if (($user_info = $this->student_model->getBySlug($this->input->post('slug')))) {
				$result = $this->book_model->get_all([
					'user_id'	=> (int)$user_info['id'],
					'status'	=> 1,
					'archived'	=> 0,
				]);

				$this->json['books'] = $result['rows'] ?? [];
			} else {
				$this->json['error'] = _l('author_not_found');
			}
		}
	}

	public function getInReviewBook() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$book_info = $this->book_model->get($this->input->post('book_id'));

			self::_formatBookPrice($book_info['id']);

			$this->json['book'] = array_merge($book_info, ['options' => [
				[
					'key'	=> 'paperback',
					'name'	=> _l('paperback'),
					'price'	=> 0
				],
				[
					'key'	=> 'hard_cover',
					'name'	=> _l('hard_cover'),
					'price'	=> $this->config->item('site_hard_cover_price'),
				],
			]]);
		}
	}

	public function canRead() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$book_info = $this->book_model->get($this->input->post('book_id'));

			$total_order_count = $this->order_model->getAuthorProducts([
				'product_id'	=> $this->input->post('book_id'),
			]);

			$order_count = $this->order_model->getAuthorProducts([
				'product_id'	=> $this->input->post('book_id'),
				'user_id'		=> $this->session->userdata('user_id'),
			]);

			if (
				$order_count ||
				($book_info['user_id'] == $this->session->userdata('user_id')) ||
				in_array($this->session->userdata('user_email'), REVIEWER_EMAILS) ||
				in_array($book_info['id'], FREE_BOOKS) ||
				self::_validateSubscription() ||
				self::_validateSchoolTeacherAccess()
			) {
				$this->json['limit'] = 0;
				$this->json['qrcode'] = $total_order_count < ISBN_LIMIT && empty($book_info['isbn'])
					? USER_URL . 'bookstore/' . $book_info['slug']
					: null;
				$this->json['success'] = _li('can_read_book');
			} else {
				$this->json['limit'] = 3;
				$this->json['qrcode'] = $total_order_count < ISBN_LIMIT && empty($book_info['isbn'])
					? USER_URL . 'bookstore/' . $book_info['slug']
					: null;
				$this->json['success'] = _li('can\'t_read_book');
			}

			$this->json['can_listen'] = in_array($this->session->userdata('user_email'), REVIEWER_EMAILS) || self::_checkAudioBookAccess($book_info['id']);
		}
	}

	private function _checkSDG($book_id = 0, $event_id = 14) {
		$this->json['is_sdg'] =  $this->event_book_model->get_all([
			'event_id' 	=> 20,
			'book_id' 	=> $book_id
		])['total'] > 0;
	}

	private function _checkAudioBookAccess($book_id = 0) {
		if (empty($book_id) || empty($this->session->userdata('user_id'))) return false;

		$subscribed = self::_validateSubscription();

		$country_code 	= $this->input->cookie('user_country_code', true) ?? '';
		$options 		= bookBuyOptions($book_id, $country_code);

		if (empty($subscribed) && !empty($options)) {
			if (in_array('audio_book', $options)) {
				$subscribed = true;
			}
		}

		if (
			($book_info = $this->book_model->get($book_id)) &&
			$book_info['user_id'] == $this->session->userdata('user_id') && $subscribed
		) {
			CI_Events::trigger('access_log', [
				'module'	=> 'audio_book_listen_' . (int)$book_id
			]);

			return true;
		}

		if (!empty($this->order_model->getAuthorProducts([
			'product_id'	=> $book_id,
			'user_id'		=> $this->session->userdata('user_id'),
			'option_type'	=> 3,
		]))) {
			CI_Events::trigger('access_log', [
				'module'	=> 'audio_book_listen_' . (int)$book_id
			]);

			return true;
		}

		return false;
	}

	private function _addRatingAndSold($item = []) {
		$reviews = $this->review_model->get_all([
			'book_id'	=> $item['id'],
			'status'	=> 1,
		])['rows'] ?? [];

		$item['rating'] = $reviews ? round(array_reduce($reviews, function($item, $acc = 0) {
			$acc += $item['rating'];
			return $acc;
		}) / count($reviews), 1) : 0;

		if (in_array($item['user_id'], BB_UID)) {
			$item['sold'] = get_bb_score($item);
		}

		$item['views'] = !empty($item['views'])
			? readable_format($item['views'])
			: readable_format($item['views'] + (int)(date('n') . date('d')))
		;

		self::_addAppreciation($item);

		return $item;
	}

	private function _addAppreciation(&$item = []) {
		// Add appreciations to the book
		$like = $clap = $love = 0;

		foreach ($this->book_appreciation_model->get_all([
			'book_id'	=> $item['id'],
		])['rows'] ?? [] as $appreciation) {
			if ($appreciation['type'] == 1) {
				$like++;
			} elseif ($appreciation['type'] == 2) {
				$clap++;
			} elseif ($appreciation['type'] == 3) {
				$love++;
			}
		}

		$item['appreciation'] = [
			'like'	=> $like,
			'clap'	=> $clap,
			'love'	=> $love,
		];
	}

	private function _addOriginalRatingAndSold($item = []) {
		$reviews = $this->review_model->get_all([
			'book_id'	=> $item['id'],
			'status'	=> 1,
		])['rows'] ?? [];

		$order_total = $this->order_model->getTotalProductsByProductId($item['id']);

		$item['sold'] = readable_format($order_total ? $order_total : 0);
		$item['rating'] = $reviews ? round(array_reduce($reviews, function($item, $acc = 0 ) {
			$acc += $item['rating'];
			return $acc;
		}) / count($reviews), 1) : 0;

		$item['views'] = !empty($item['views'])
			? readable_format($item['views'])
			: 0
		;

		self::_addAppreciation($item);

		return $item;
	}

	public function archiveBook() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('archive', _l('archive'), 'trim|required|numeric|in_list[0,1]');

		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {
			$book_info 	= $this->book_model->get($this->input->post('book_id'));

			if ($book_info['user_id'] != $this->session->userdata('user_id')) {
				return $this->json['error'] = _l('unauthorized');
			}

			$archived 	= $this->input->post('archive') == 1 ? 1 : 0;

			$this->book_model->edit($this->input->post('book_id'), [
				'archived'	=> $archived
			]);

			$this->load->model('book/BookArchiveLog_model','book_archive_log_model');

			$this->book_archive_log_model->add([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'book_id'	=> (int)$this->input->post('book_id'),
				'ip_address'=> $this->input->ip_address(),
				'status'	=> $archived
			]);

			$this->json['success'] = _li('Your book has been archived successfully');
		}
	}

	public function eventPublishMessage() {
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required|numeric');
		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {
		}
	}

	private function _checkDeliveryCountry(&$book_price = []) {
		$country_info = self::getCountry(true);

		$book_price['ban_country'] = FALSE;

		if (!empty($country_info) &&
			($delivery_info = $this->delivery_country_model->get_all([
				'country_code'	=> $country_info['country_code'],
				'status'		=> 0,
			])['rows'][0] ?? []) &&
			!empty($delivery_info['buying_options'])
		) {
			if (!empty($buying_options = json_decode($delivery_info['buying_options'], true))) {
				$site_info = $this->site_model->getByCountryCode($delivery_info['country_code'], 7);

				if (empty($site_info)) {
					$site_info = $this->site_model->get($this->config->item('default_site_id'));
				}

				if ($buying_options['ebook'] == 2) {
					$book_price['ebook_price'] = $site_info['ebook_price'] ?? 0;
				}
			}

			$book_price['ban_country'] = TRUE;
		}
	}

	public function deactivateBook() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {
			$user_id 	= (int)$this->session->userdata('user_id');
			$book_info 	= $this->book_model->get($this->input->post('book_id'));

			if (
				empty($book_info) ||
				$book_info['status'] != 1 ||
				$book_info['user_id'] != $user_id
			) {
				return $this->json['error'] = _li('Unauthorized Book');
			}

			$this->book_model->edit($this->input->post('book_id'), [
				'status'	=> 3
			]);

			$this->bookstore_model->editByBookId($this->input->post('book_id'), [
				'status'	=> 3
			]);

			$this->book_version_model->editByBookId($this->input->post('book_id'), [
				'status'	=> 3
			]);

			// remove from cart
			$this->db->delete('cart', [
				'product_id'	=> (int)$this->input->post('book_id'),
			]);

			self::_managePublishCount($this->input->post('book_id'));

			$this->load->model('book/BookArchiveLog_model','book_archive_log_model');

			$this->book_archive_log_model->add([
				'user_id'	=> (int)$user_id,
				'book_id'	=> (int)$this->input->post('book_id'),
				'ip_address'=> $this->input->ip_address(),
				'status'	=> 3
			]);

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('deactivate_book_%s', $this->input->post('book_id'))
			]);

			$this->json['user_id'] = $user_id;
			$this->json['success'] = _li('Your book has been deactivated successfully');
		}
	}

	private function _managePublishCount($book_id = 0) {
		$book_info = $this->book_model->get($book_id);

		if (empty($book_info)) return;

		$user_book_info = $this->event_book_model->get_all([
			'book_id'					=> $book_id,
			'is_active_book_writing'	=> 1
		])['rows'][0] ?? [];

		if (!empty($user_book_info)) {
			if (!empty($user_limit_info = $this->user_limit_model->get_all([
				'user_id' 	=> $book_info['user_id'],
				'event_id' 	=> $user_book_info['event_id'],
			])['rows'][0] ?? [])) {
				$this->user_limit_model->updateCanPublish($user_limit_info['id'], false);
			}
		} else {
			if (!empty($user_limit_info = $this->user_limit_model->get_all([
				'user_id' 	=> $book_info['user_id'],
				'event_id' 	=> 0,
			])['rows'][0] ?? [])) {
				$this->user_limit_model->updateCanPublish($user_limit_info['id'], false);
			}
		}

		CI_Events::trigger('access_log', [
			'module'	=> sprintf('deactivate_book_manage_publish_count_%s', $book_info['user_id'])
		]);
	}
}
