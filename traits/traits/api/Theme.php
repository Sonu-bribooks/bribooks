<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Theme {
	public $cache_ttl = 14400;

	public function getBookStoreGenres() {
		if (!$this->json) {
			$user_country_code = strtolower($this->input->cookie('user_country_code'));

			if (empty($user_country_code)) {
				$user_country_code = strtolower($this->config->item('site_country_code'));
			}

			if (empty($user_country_code)) {
				$user_country_code = 'in';
			}

			$cache_key 	= gen_cache_key(sprintf('get_book_store_genres_%s', (string)$user_country_code));
			$genres 	= json_decode($this->cache->get($cache_key), true);

			if (empty($genres)) {
				$filter = [
					'parent_id'	=> 0,
					'status'	=> 1,
					'sort'		=> 'genre.sort_order',
					'order'		=> 'DESC'
				];

				$results = $this->genre_model->get_all($filter)['rows'] ?? [];

				foreach ($results as $item) {
					$filter_data		= [
						'genre_id'		=> $item['id'],
						'quantity_ge'	=> 1
					];

					if (!empty($user_country_code) && $user_country_code !== 'in') {
						$filter_data['ne_location'] = 'india';
					} else {
						$filter_data['location'] 	= 'india';
					}

					$book_count = $this->bookstore_model->get_all($filter_data)['total'] ?? 0;

					if ($book_count < 16) continue;

					$genres[] 	= [
						'id'			=> $item['id'],
						'parent_id'		=> $item['parent_id'],
						'name'			=> _li($item['name']),
						'image'			=> $item['image'],
						'status'		=> $item['status'],
						'custom_theme'	=> (int)$item['custom_theme'],
						'sort_order'	=> (int)$item['sort_order'],
					];
				}

				$this->cache->save($cache_key, json_encode($genres), $this->cache_ttl);
			}

			$this->json['genres'] = $genres;
		}
	}

	public function getGenres() {
		if (!$this->json) {
			$filter = [
				'parent_id'	=> 0,
				'status'	=> 1,
				'sort'		=> 'genre.sort_order',
				'order'		=> 'DESC'
			];

			if (!empty($this->session->userdata('user_id')) && !empty($user_event_info = $this->event_user_model->get_all([
				'user_id'					=> $this->session->userdata('user_id'),
				'is_active_book_writing'	=> 1
			])['rows'] ?? [])) {
				$genre_ids = array_filter(
					explode(',', implode(',', array_column(array_filter($user_event_info, fn($item) => !empty($item['genre_ids'])), 'genre_ids')))
				);

				if (!empty($genre_ids)) {
					$filter['genre_ids'] = array_unique($genre_ids);
				}
			} else {
				$filter['is_default'] = 1;
			}

			$result 	= $this->genre_model->get_all($filter);

			$genres 	= [];
			$sort_order = $sort_status = [];

			foreach ($result['rows'] ?? [] as $item) {
				if (empty($this->genre_model->getCategories($item['id']))) continue;

				$genres[] 	= [
					'id'			=> $item['id'],
					'parent_id'		=> $item['parent_id'],
					'name'			=> _li($item['name']),
					'image'			=> $item['image'],
					'status'		=> $item['status'],
					'custom_theme'	=> (int)$item['custom_theme'],
					'sort_order'	=> (int)$item['sort_order'],
				];
			}

			$this->json['genres'] = $genres;
		}
	}

	public function getGenreCategories() {
		$this->form_validation->set_rules('genre_id', _l('genre_id'), [
			'trim',
			'required',
			'numeric',
			['genre', [$this->validate_model, 'genre']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$cache_key 	= gen_cache_key(sprintf('genre_categories_%s', (int)$this->input->post('genre_id')));
			$categories = json_decode($this->cache->get($cache_key), true);

			log_kb(['GetGenreCategories::cache_data::' => $categories]);

			if (empty($categories)) {
				$results = $this->genre_model->getCategories($this->input->post('genre_id'));

				$categories = $duplicate_names = $sort_order = $sort_status = [];

				foreach ($results as $genre) {
					$category_info 	= $this->category_model->get($genre['category_id']);

					if (empty($category_info)) continue;

					$status = $category_info['status'];
					$status = $this->theme_model->get_all([
						'category_id'	=> $category_info['id'],
					])['total'] > 0;

					if (in_array($category_info['id'], [129])) continue;
					if (in_array($category_info['name'], $duplicate_names)) continue;

					$duplicate_names[] 	= $category_info['name'];
					$sort_status[] 		= $status;
					$sort_order[] 		= $category_info['sort_order'];

					$categories[] 		= [
						'id'			=> $category_info['id'],
						'parent_id'		=> $category_info['parent_id'],
						'name'			=> _li($category_info['name']),
						'image'			=> $category_info['image'],
						'status'		=> (int)$status,
						'custom_theme'	=> (int)$category_info['custom_theme'],
						'sort_order'	=> (int)$category_info['sort_order'],
					];
				}

				array_multisort(
					$sort_order, SORT_DESC,
					$sort_status, SORT_DESC,
					$categories
				);

				$this->cache->save($cache_key, json_encode($categories), $this->cache_ttl);
			}

			$this->json['categories'] = $categories;
		}
	}

	public function getTopCategories() {
		if (!$this->json) {
			$filter = [
				'parent_id'	=> 0,
				'status'	=> 1,
				'sort'		=> 'category.sort_order',
				'order'		=> 'DESC'
			];

			if (!empty($this->session->userdata('user_id')) && !empty($user_event_info = $this->event_user_model->get_all([
				'user_id'					=> $this->session->userdata('user_id'),
				'is_active_book_writing'	=> 1
			])['rows'] ?? [])) {
				$genre_ids = array_filter(
					explode(',', implode(',', array_column(array_filter($user_event_info, fn($item) => !empty($item['genre_ids'])), 'genre_ids')))
				);

				if (!empty($genre_ids)) {
					$filter['category_ids'] = array_unique($genre_ids);
				}
			} else {
				$filter['is_default'] = 1;
			}

			$result 	= $this->category_model->get_all($filter);

			$categories = [];
			$sort_order = $sort_status = [];

			foreach ($result['rows'] ?? [] as $item) {
				if ($this->category_model->get_all([
					'parent_id'	=> $item['id'],
				])['total'] == 0) continue;

				$categories[] 	= [
					'id'			=> $item['id'],
					'parent_id'		=> $item['parent_id'],
					'name'			=> _li($item['name']),
					'image'			=> $item['image'],
					'status'		=> $item['status'],
					'custom_theme'	=> (int)$item['custom_theme'],
					'sort_order'	=> (int)$item['sort_order'],
				];
			}

			$this->json['categories'] = $categories;
		}
	}

	public function getAllCategories() {
		if (!$this->json) {
			$cache_key 	= gen_cache_key('all_categories');
			$categories = json_decode($this->cache->get($cache_key), true);

			if (empty($categories)) {
				$results 	= $this->genre_model->get_all([
					'status'	=> 1,
					'sort'		=> 'genre.sort_order',
					'order'		=> 'DESC',
				])['rows'] ?? [];

				$categories = [];

				foreach ($results as $item) {
					$categories[] 	= [
						'id'			=> $item['id'],
						'parent_id'		=> $item['parent_id'],
						'name'			=> _li($item['name']),
						'image'			=> $item['image'],
						'status'		=> $item['status'],
						'custom_theme'	=> (int)$item['custom_theme'],
						'sort_order'	=> (int)$item['sort_order'],
					];

					$genre_categories = $this->genre_model->getCategories($item['id']);

					foreach ($genre_categories as $genre_category) {
						$category_info = $this->category_model->get($genre_category['category_id']);
						if (empty($category_info)) continue;

						$categories[] 	= [
							'id'			=> $category_info['id'],
							'parent_id'		=> $item['id'],
							'name'			=> _li($category_info['name']),
							'image'			=> $category_info['image'],
							'status'		=> $category_info['status'],
							'custom_theme'	=> (int)$category_info['custom_theme'],
							'sort_order'	=> (int)$category_info['sort_order'],
						];
					}
				}

				$this->cache->save($cache_key, json_encode($categories), $this->cache_ttl);
			}

			$this->json['categories'] = $categories;
		}
	}

	public function getAllCategoriesByParent() {
		if (!$this->json) {
			$cache_key 	= gen_cache_key('all_categories');
			$categories = json_decode($this->cache->get($cache_key), true);

			if (empty($categories)) {
				$result 	= $this->category_model->get_all([
					'status'	=> 1,
					'sort'		=> 'category.sort_order',
					'order'		=> 'DESC',
				]);

				$categories = [];

				foreach ($result['rows'] ?? [] as $item) {
					$categories[] 	= [
						'id'			=> $item['id'],
						'parent_id'		=> $item['parent_id'],
						'name'			=> _li($item['name']),
						'image'			=> $item['image'],
						'status'		=> $item['status'],
						'custom_theme'	=> (int)$item['custom_theme'],
						'sort_order'	=> (int)$item['sort_order'],
					];
				}

				$this->cache->save($cache_key, json_encode($categories), $this->cache_ttl);
			}

			$this->json['categories'] = $categories;
		}
	}

	public function getCategories() {
		if ($this->input->post('category_id')) {
			$this->form_validation->set_rules('category_id', _l('category_id'), [
				'trim',
				'required',
				'numeric',
				['category', [$this->validate_model, 'category']]
			]);

			self::_runFormValidation();
		}

		if (!$this->json) {
			$cache_key 	= gen_cache_key('get_categories_' . (int)$this->input->post('category_id'));
			$categories = json_decode($this->cache->get($cache_key), true);

			log_kb(['GetCategories::cache_data::' => $categories]);

			if (empty($categories)) {
				$filter_data = [
					'parent_id_ne'	=> 0,
					'status'		=> 1,
					'sort'			=> 'category.sort_order',
					'order'			=> 'DESC',
				];

				if ($this->input->post('category_id')) {
					$filter_data['parent_id'] = (int)$this->input->post('category_id');
					unset($filter_data['parent_id_ne']);
				}

				$result 	= $this->category_model->get_all($filter_data);

				$categories = [];
				$duplicate_names = $sort_order = $sort_status = [];

				foreach ($result['rows'] ?? [] as $item) {
					$status = $item['status'];
					$status = $this->theme_model->get_all([
						'category_id'	=> $item['id'],
					])['total'] > 0;

					if (in_array($item['id'], [129])) continue;
					if (in_array($item['name'], $duplicate_names)) continue;

					$duplicate_names[] 	= $item['name'];
					$sort_status[] 		= $status;
					$sort_order[] 		= $item['sort_order'];

					$categories[] 		= [
						'id'			=> $item['id'],
						'parent_id'		=> $item['parent_id'],
						'name'			=> _li($item['name']),
						'image'			=> $item['image'],
						'status'		=> (int)$status,
						'custom_theme'	=> (int)$item['custom_theme'],
						'sort_order'	=> (int)$item['sort_order'],
					];
				}

				array_multisort(
					$sort_order, SORT_DESC,
					$sort_status, SORT_DESC,
					$categories
				);

				$this->cache->save($cache_key, json_encode($categories), $this->cache_ttl);
			}

			$this->json['categories'] = $categories;
		}
	}

	public function getCover() {
		$this->form_validation->set_rules('cover_id', _l('cover_id'), [
			'trim',
			'required',
			'numeric',
			['cover', [$this->validate_model, 'cover']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$cover_info 					= $this->cover_model->get($this->input->post('cover_id'));
			$cover_info['heading_style'] 	= parse_cover_style(json_decode($cover_info['heading_style'], true));
			$cover_info['footer_style']		= parse_cover_style(json_decode($cover_info['footer_style'], true));
			$this->json['cover'] 			= $cover_info;
		}
	}

	public function getCovers() {
		$this->form_validation->set_rules('category_id', _l('category_id'), [
			'trim',
			'required',
			'numeric',
			['category', [$this->validate_model, 'category']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->input->post('search')) {
				$results 	= $this->cover_model->get_all([
					'search' 	=> preg_replace(['/[^\w\s]/', '/\s+/'], ['', ' '],  trim($this->input->post('search'))),
					'start' 	=> 0,
					'limit' 	=> 100,
				])['rows'] ?? [];
			} else {
				$cache_key 	= gen_cache_key('get_covers_' . (int)$this->input->post('category_id'));
				$results 	= json_decode($this->cache->get($cache_key), true);

				if (empty($results)) {
					$results = array_merge(
						$this->cover_model->get_all([
							'category_id'	=> $this->input->post('category_id'),
						])['rows'] ?? [],
						$this->cover_model->get_all([
							'category_id_ne'	=> $this->input->post('category_id'),
						])['rows'] ?? []
					);

					$this->cache->save($cache_key, json_encode($results), $this->cache_ttl);
				}
			}

			$country_info = self::getCountry(true);
			$country_name = $country_info['country'];

			$covers = [];

			foreach ($results as $key => $item) {
				if (
					(strtolower($country_name) == 'united states' || strtolower($country_name) == 'usa') &&
					in_array($item['id'], USA_HIDE_COVERS)
				) {
					continue;
				}

				$covers[] = [
					'id' 			=> $item['id'],
					'category_id' 	=> $item['category_id'],
					'tags' 			=> $item['tags'],
					'image' 		=> $item['image'],
					'heading_style' => parse_cover_style(json_decode($item['heading_style'], true)),
					'footer_style' 	=> parse_cover_style(json_decode($item['footer_style'], true)),
					'type' 			=> $item['type'],
					'status' 		=> $item['status'],
					'category' 		=> $item['category'],
				];
			}

			// hide/show custom cover button on front end
			// if ($this->config->item('site_country_code') !== 'IN') {
			// 	$covers = array_filter($covers, function($item) {
			// 		return $item['type'] != 2;
			// 	});
			// }

			if (
				$this->input->post('app_os') &&
				version_compare($this->input->post('app_version'), '4.0.0.', '<')
			) {
				$covers = array_filter($covers, function($item) {
					return $item['type'] == 0;
				});
			}

			$this->json['covers'] = array_values($covers);
		}
	}

	public function getThemes() {
		$this->form_validation->set_rules('category_id', _l('category_id'), [
			'trim',
			'required',
			'numeric',
			['category', [$this->validate_model, 'category']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->input->post('search')) {
				$filter_data = [
					'search'	=> preg_replace(['/[^\w\s]/', '/\s+/'], ['', ' '],  trim($this->input->post('search'))),
					'sort'		=> 'theme.sort_order',
					'status'	=> 1,
					'start'		=> 0,
					'limit'		=> 100,
				];

				$results 	= $this->theme_model->get_all($filter_data)['rows'] ?? [];
			} else {
				$cache_key 	= gen_cache_key('get_themes_' . (int)$this->input->post('category_id'));
				$results 	= json_decode($this->cache->get($cache_key), true);

				if (empty($results)) {
					$filter_data = [
						'category_id'	=> $this->input->post('category_id'),
						'sort'			=> 'theme.sort_order',
						'status'		=> 1
					];

					$results 	= $this->theme_model->get_all($filter_data)['rows'] ?? [];

					$this->cache->save($cache_key, json_encode($results), $this->cache_ttl);
				}
			}

			$country_info = self::getCountry(true);
			$country_name = $country_info['country'];

			$themes = [];

			foreach ($results as $key => $item) {
				if ((strtolower($country_name) == 'united states' || strtolower($country_name) == 'usa') &&
				in_array($item['id'], USA_HIDE_THEMES)
				) {
					continue;
				}

				$text_boxes = json_decode($item['text_boxes'], true);

				parse_textboxes($text_boxes);

				$themes[] = [
					'id' 			=> $item['id'],
					'category_id' 	=> $item['category_id'],
					'name' 			=> $item['name'],
					'image' 		=> $item['image'],
					'text_boxes' 	=> $text_boxes,
					'font_size' 	=> (int)$item['font_size'],
					'font_family' 	=> $item['font_family'],
					'font_color' 	=> $item['font_color'],
					'font_weight' 	=> $item['font_weight'],
					'status' 		=> $item['status'],
					'sort_order' 	=> $item['sort_order'],
					'category' 		=> $item['category'],
					'custom_theme' 	=> $item['custom_theme']
				];
			}
			$this->json['themes'] = $themes;
		}
	}
}
