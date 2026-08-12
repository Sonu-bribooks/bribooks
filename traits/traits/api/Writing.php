<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Writing {
	public function checkGrammar() {
		$this->form_validation->set_rules('text', _l('text'), 'required|min_length[3]|max_length[1000]');

		self::_runFormValidation();

		if (!$this->json) {
			return;
			$lang 		= $this->input->cookie('user_language') ?? 'en';
			$payload	= http_build_query([
				'text' 				=> $this->input->post('text'),
				'language' 			=> $lang === 'en' ? 'en-GB' : $lang,
				'level' 			=> 'picky',
				'enableHiddenRules' => 'true',
				'disabledRules' 			=> 'WHITESPACE_RULE,EN_QUOTES',
				'allowIncompleteResults'	=> 'true'
			]);

			$ch = curl_init('http://BBGrammar-v3-env.eba-dimxs3fn.us-east-1.elasticbeanstalk.com/v2/check');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/x-www-form-urlencoded'
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);

			$response = curl_exec($ch);
			curl_close($ch);

			$json = json_decode($response, true);

			$errors = [];

			if (!empty($json['matches'])) {
				$timestamp = time() * 1000; // milliseconds

				foreach ($json['matches'] as $index => $item) {
					$errors[] = [
						'id' 			=> $timestamp + $index,
						'message' 		=> $item['message'] ?? '',
						'heading' 		=> $item['shortMessage'] ?? ($item['rule']['description'] ?? ''),
						'length' 		=> $item['length'] ?? 0,
						'offset' 		=> $item['offset'] ?? 0,
						'replacements' 	=> array_map(fn($r) => $r['value'], $item['replacements'] ?? []),
						'context' 		=> $item['context'] ?? [],
						'sentence' 		=> $item['sentence'] ?? ''
					];
				}
			}

			$this->json['errors'] = $errors;
		}
	}

	public function getFonts() {
		if (!$this->json) {
			$cache_key = vsprintf('%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'book_front_cover_fonts',
			]);

			$fonts = json_decode($this->cache->get($cache_key), true);

			if (empty($fonts)) {
				$fonts = array_map(fn($item) => [
					'id'	=> $item['id'],
					'name'	=> $item['name'],
					'value'	=> $item['name'],
					'url'	=> sprintf('%s%s%s', $this->config->item('cloudfront_url'), $this->config->item('s3_user_gallery'), $item['url']),
					'image'	=> sprintf('%s%s%s', $this->config->item('cloudfront_url'), $this->config->item('s3_user_gallery'), $item['image']),
					'tags'	=> explode(',', $item['tags']),
				], $this->font_model->get_all([
					'status' 	=> 1,
					'sort'		=> 'font.name',
					'order'		=> 'ASC',
				])['rows'] ?? []);

				$this->cache->save($cache_key, json_encode($fonts), ENVIRONMENT === 'production' ? 3600 * 24 * 10 : 1);
			}

			$this->json['fonts'] = $fonts;
		}
	}

	public function getSpamWords() {
		if (!$this->json) {
			$cache_key = vsprintf('%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'spam_words',
			]);

			$spam_words = json_decode($this->cache->get($cache_key), true);

			if (empty($spam_words)) {
				$spam_words = array_map(function ($item) {
					return $item['word'];
				}, $this->page_model->getSpamWords());

				$this->cache->save($cache_key, json_encode($spam_words), 3600);
			}

			$this->json['spam_words'] = $spam_words;
		}
	}

	public function createBook() {
		$this->form_validation->set_rules('back_color', _l('back_color'), 'trim|required|min_length[4]|max_length[20]');
		$this->form_validation->set_rules('category_id', _l('category_id'), [
			'trim',
			'required',
			'numeric',
			['category', [$this->validate_model, 'category']]
		]);
		if (
			empty($this->input->post('app_os')) ||
			version_compare($this->input->post('app_version'), '4.4.2', '>')
		) {
			$this->form_validation->set_rules('genre_id', _l('genre_id'), [
				'trim',
				'required',
				'numeric',
				['genre', [$this->validate_model, 'genre']]
			]);
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (
				$this->input->post('app_os') &&
				version_compare($this->input->post('app_version'), '4.2.0.', '<')
			) {
				$this->json['error'] = _li('Upgrade your app to explore book writing by genre');
				return;
			}

			if (
				!empty($this->input->post('app_os')) &&
				version_compare($this->input->post('app_version'), '4.4.3', '<')
			) {
				$category_info = $this->category_model->get($this->input->post('category_id'));
				$_POST['genre_id'] = $category_info['parent_id'] ?? 0;
			}

			if (self::_validateWriting()) {
				$this->book_model->edit($this->input->post('book_id'), [
					'genre_id'		=> (int)$this->input->post('genre_id'),
					'category_id'	=> (int)$this->input->post('category_id'),
					'user_id'		=> (int)$this->session->userdata('user_id'),
					'back_color'	=> $this->input->post('back_color'),
					'temp_user_id'	=> $this->session->userdata('user_id') ? '' : get_bb_user_id(),
				]);

				$book_id = (int)$this->input->post('book_id');
			} else {
				$book_id = $this->book_model->add([
					'genre_id'		=> (int)$this->input->post('genre_id'),
					'category_id'	=> (int)$this->input->post('category_id'),
					'user_id'		=> (int)$this->session->userdata('user_id'),
					'back_color'	=> $this->input->post('back_color'),
					'temp_user_id'	=> $this->session->userdata('user_id') ? '' : get_bb_user_id(),
				]);
			}

			$book_info = $this->book_model->get($book_id);

			$this->json['book'] = $book_info;

			CI_Events::trigger('book_created', [
				'book_id'		=> $book_info['id'],
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'book_created_' . $book_info['id']
			]);

			CI_Events::trigger('book_writing_log', $book_info);

			$this->json['success'] = _l('book_created');
		}
	}

	public function updateBookCategory() {
		$this->form_validation->set_rules('category_id', _l('category_id'), [
			'trim',
			'required',
			'numeric',
			['category', [$this->validate_model, 'category']]
		]);

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

			$this->book_model->edit($this->input->post('book_id'), [
				'category_id'	=> (int)$this->input->post('category_id'),
			]);

			$book_info = $this->book_model->get($book_id);

			CI_Events::trigger('book_updated', [
				'book_id'		=> $book_info['id'],
				'category_id'	=> $book_info['category_id'],
			]);

			$this->json['book'] = $book_info;

			$this->json['success'] = _l('book_theme_updated');
		}
	}

	public function updateBookAuthor() {
		$this->form_validation->set_rules(
			'author_name',
			_l('author_name'),
			'trim|required|min_length[3]|max_length[40]'
		);
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		if (
			$this->input->post('author_name') &&
			_has_not_allowed_chars_single($this->input->post('author_name'))
		) {
			$this->json['error'] = _li('You are trying to use Non-English characters. Please use only valid English language characters.');
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$this->book_model->edit($this->input->post('book_id'), [
				'author_name' => _clean_text_single($this->input->post('author_name'))
			]);

			$book_info = $this->book_model->get($this->input->post('book_id'));

			$this->json['book'] 	= $book_info;
			$this->json['success'] 	= _l('author_name_saved');
		}
	}

	public function finishWriting() {
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

			if (!self::_validateActiveSession()) {
				return;
			}

			// validate total no of pages
			$result 		= $this->page_model->get_all([
				'book_id'  => $this->input->post('book_id')
			]);
			$total_pages 	= $result['total'] ?? 0;

			if ($total_pages < WRITING_PAGE_LIMIT) {
				return $this->json['error'] = sprintf(_l('your_book_pages_are_less_than %s'), WRITING_PAGE_LIMIT);
			}

			if ($empty_page_nos = self::_checkEmptyPages($result['rows'] ?? [])) {
				return $this->json['error'] = sprintf(_li('Pages_%s_in_the_book_must_not_be_empty'), $empty_page_nos);
			}

			$book_info 			= $this->book_model->get($this->input->post('book_id'));
			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];
			$custom_cover_info 	= !empty($user_cover_info['custom_cover_id']) ? $this->custom_cover_model->get($user_cover_info['custom_cover_id']) : [];

			$this->json['book'] 				= $book_info;
			$this->json['book']['design'] 		= $user_cover_info['design'] ?? '';
			$this->json['book']['custom_cover'] = $custom_cover_info;
			$this->json['success'] 				= _li('book_writing_done');
		}
	}

	private function _checkEmptyPages($pages = []) {
		$empty_pages = array_filter($pages, function($page) {
			$page['texts'] = preg_replace('/[^\w]/', '', $page['texts']);

			return empty(trim($page['texts'])) === true;
		});

		return !empty($empty_pages)
			? implode(', ', array_column(array_map(function($item) {
				$item['sort_order'] += 1;
				return $item;
			}, $empty_pages), 'sort_order'))
			: false;
	}

	public function updateBookFrontCover() {
		$this->form_validation->set_rules(
			'name',
			_l('book_name'),
			'trim|required|min_length[3]|max_length[30]'
		);
		$this->form_validation->set_rules(
			'author_name',
			_l('author_name'),
			'trim|required|min_length[3]|max_length[40]'
		);
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('cover_id', _l('cover_id'), [
			'trim',
			'required',
			'numeric',
			['cover', [$this->validate_model, 'cover']]
		]);

		if (
			$this->input->post('author_name') &&
			_has_not_allowed_chars_single($this->input->post('author_name'))
		) {
			$this->json['error'] = _li('You are trying to use Non-English characters. Please use only valid English language characters.');
		}

		if (
			$this->input->post('name') &&
			_has_not_allowed_chars_single($this->input->post('name'))
		) {
			$this->json['error'] = _li('You are trying to use Non-English characters. Please use only valid English language characters.');
		}

		if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('name')))) {
			$spam_words = implode(', ', array_column($spam_word_data, 'word'));
			$this->json['error'] = _li('Spam Word : ') . ' ' . $spam_words;
		}

		if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('author_name')))) {
			$spam_words = implode(', ', array_column($spam_word_data, 'word'));
			$this->json['error'] = _li('Spam Word : ')  . ' ' . $spam_words;
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$existing_book = $this->bookstore_model->get_all([
				'status'		=> 1,
				'name'			=> $this->input->post('name'),
				'author_name'	=> $this->input->post('author_name'),
			])['rows'][0] ?? [];

			log_kb(['existing_book' => $existing_book]);

			$author_info = $this->user_model->get($this->session->userdata('user_id'));

			if (($author_info['role_id'] ?? 2) != 11) {
				if ($existing_book && $existing_book['id'] != $this->input->post('book_id')) {
					$this->json['error'] = _li('The Book & Author Name already exists on BriBooks');
					return;
				}
			}

			$cover_info = $this->cover_model->get($this->input->post('cover_id'));
			$book_info 	= $this->book_model->get($this->input->post('book_id'));

			$user_cover_image = $book_info['cover_image'];

			log_kb(['BookCover' => [
				isset($_FILES['image']),
				$_FILES['image']['size'],
			]]);

			if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
				if (self::_validateFileUpload('image', false)) {
					$filename = sprintf('user_cover_%s_b%s_v%s.png', uniqid(), $book_info['version'], $book_info['id']);

					log_kb(['BookCover' => $this->s3->amazonS3Upload(
						$filename,
						$_FILES['image']['tmp_name'],
						rtrim($this->config->item('s3_user_cover_img'), '/')
					)]);

					// delete old cover image if not exists in the last book version
					if (strpos($user_cover_image, 'user_cover_') !== false && (
							empty($book_info['version']) || (
								($book_version_info = $this->book_version_model->getByVersion($book_info['id'], $book_info['version'])) &&
								$book_version_info['cover_image'] !== $user_cover_image
							)
						)
					) {
						log_kb(['DeleteCoverImage' => $this->s3->amazonS3Delete(
							str_replace('AuthorCoverImages/', '', $user_cover_image),
							rtrim($this->config->item('s3_user_cover_img'), '/')
						)]);
					}

					$user_cover_image = 'AuthorCoverImages/' . $filename;
				}
			}

			if (!empty($book_info['user_cover_id'])) {
				$design = json_decode($this->input->post('design'), true);

				!empty($user_cover_image) && $this->user_cover_model->edit($book_info['user_cover_id'], [
					'image'		=> $user_cover_image,
					'design'	=> !empty($design['objects']) ? json_encode($design) : '',
				]);

				$user_cover_id = $book_info['user_cover_id'];
			} else {
				$design = json_decode($this->input->post('design'), true);

				$user_cover_id = $user_cover_image ? $this->user_cover_model->add([
					'user_id'	=> (int)$this->session->userdata('user_id'),
					'image'		=> $user_cover_image,
					'design'	=> !empty($design['objects']) ? json_encode($design) : '',
				]) : 0;
			}

			if (
				$this->input->post('app_os') &&
				version_compare($this->input->post('app_version'), '4.0.0.', '<')
			) {
				$user_cover_id = 0;
			}

			$this->book_model->edit($this->input->post('book_id'), [
				'name'			=> strip_tags(_clean_text_single($this->input->post('name'))),
				'author_name'	=> strip_tags(_clean_text_single($this->input->post('author_name'))),
				'cover_id'		=> (int)$this->input->post('cover_id'),
				'user_cover_id'	=> (int)$user_cover_id,
				'cover_image'	=> $user_cover_image ? $user_cover_image : ($cover_info['image'] ?? ''),
			]);

			$book_info 			= $this->book_model->get($this->input->post('book_id'));
			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];
			$custom_cover_info 	= !empty($user_cover_info['custom_cover_id']) ? $this->custom_cover_model->get($user_cover_info['custom_cover_id']) : [];

			CI_Events::trigger('book_updated', [
				'book_id'		=> $book_info['id'],
				'name'			=> $book_info['name'],
				'author_name'	=> $book_info['author_name'],
				'cover_id'		=> $book_info['cover_id'],
				'cover_image'	=> $book_info['cover_image'],
			]);

			$this->json['book'] 				= $book_info;
			$this->json['book']['design'] 		= $user_cover_info['design'] ?? '';
			$this->json['book']['custom_cover'] = $custom_cover_info;
			$this->json['success'] 				= _l('book_front_cover_updated');
		}
	}

	public function updateBookBackCover() {
		$this->form_validation->set_rules(
			'author_bio',
			_l('author_bio'),
			'trim|required|min_length[3]|max_length[255]',
			[
				'required' 		=> _li('The profile/biography must have at least 3 characters'),
				'min_length' 	=> _li('The profile/biography must have at least 3 characters'),
				'max_length' 	=> _li('The profile/biography must have maximum 255 characters'),
			]
		);
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		if (
			$this->input->post('author_bio') &&
			_has_not_allowed_chars_single($this->input->post('author_bio'))
		) {
			$this->json['error'] = _li('You are trying to use Non-English characters. Please use only valid English language characters.');
		}

		if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('author_bio')))) {
			$spam_words = implode(', ', array_column($spam_word_data, 'word'));
			$this->json['error'] = _li('Spam Word : ')  . ' ' . $spam_words;
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$book_info = $this->book_model->get($this->input->post('book_id'));

			$author_image = $book_info['author_image'];

			if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
				if (self::_validateFileUpload()) {
					$filename = sprintf('author_%s_%s_%s.png', uniqid(), $book_info['version'], (int)$this->input->post('book_id'));

					log_kb($this->s3->amazonS3Upload(
						$filename,
						$_FILES['image']['tmp_name'],
						rtrim($this->config->item('s3_author_img'), '/')
					));

					// delete old author image if not exists in the last book version
					if (strpos($author_image, 'author_') !== false && (
							empty($book_info['version']) || (
								($book_version_info = $this->book_version_model->getByVersion($book_info['id'], $book_info['version'])) &&
								$book_version_info['author_image'] !== $author_image
							)
						)
					) {
						log_kb(['DeleteAuthorImage' => $this->s3->amazonS3Delete(
							str_replace('AuthorImages/', '', $author_image),
							rtrim($this->config->item('s3_author_img'), '/')
						)]);
					}

					$author_image = 'AuthorImages/' . $filename;
				}
			}

			$this->book_model->edit($this->input->post('book_id'), [
				'author_bio'	=> strip_tags(_clean_text_single($this->input->post('author_bio'))),
				'author_image'	=> $author_image,
			]);

			$this->user_model->edit($this->session->userdata('user_id'), [
				'biography'		=> $this->input->post('author_bio'),
				'image'			=> $author_image,
			]);

			$book_info 			= $this->book_model->get($this->input->post('book_id'));
			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];
			$custom_cover_info 	= !empty($user_cover_info['custom_cover_id']) ? $this->custom_cover_model->get($user_cover_info['custom_cover_id']) : [];

			CI_Events::trigger('book_updated', [
				'book_id'		=> $book_info['id'],
				'author_bio'	=> strip_tags(_clean_text_single($this->input->post('author_bio'))),
				'author_image'	=> $author_image,
			]);

			$this->json['book'] 				= $book_info;
			$this->json['book']['design'] 		= $user_cover_info['design'] ?? '';
			$this->json['book']['custom_cover'] = $custom_cover_info;
			$this->json['success'] 				= _l('book_back_cover_updated');
		}
	}

	public function publishBook() {
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
				return $this->json['error'] = _l('book_not_found');
			}

			$user_info = $this->session->userdata('user_id')
				? $this->user_model->get($this->session->userdata('user_id'))
				: [];

			if (!$this->session->userdata('user_id')) {
				$this->json['login'] 	= true;
				$this->json['success'] 	= _l('login_to_publish');
			} elseif (
				!empty($user_info) &&
				empty($user_info['mobile_verified']) &&
				empty($this->input->post('app_os')) &&
				(strtolower($user_info['location']) == 'india')
			) {
				$this->json['mobile'] 	= true;
				$this->json['success'] 	= _l('update_mobile_no');
			} elseif (
				!empty($user_info) &&
				empty($user_info['email_verified'])
			) {
				$this->json['email'] 	= true;
				$this->json['success'] 	= _l('update_email_address');
			} else {
				$total_pages = $this->page_model->get_all([
					'book_id' => $this->input->post('book_id')
				])['total'] ?? 0;

				if ($total_pages < WRITING_PAGE_LIMIT) {
					return $this->json['error'] = _l('your_book_pages_are_less_then 10');
				}

				$new_publish = empty($book_info['version']) ? true : false;

				self::_updatePublishingLimit($book_info);

				if ($this->json) return $this->json;

				$this->book_model->edit($this->input->post('book_id'), [
					'user_id'				=> !empty($book_info['user_id'])
						? $book_info['user_id']
						: $user_info['id'],
					'status'				=> 2,
					'editing'				=> 0,
					'archived'				=> 0,
					'preview_token'			=> sha1(md5(time())),
					'slug'					=> !empty($book_info['slug'])
						? $book_info['slug']
						: get_book_slug(sprintf('%s by %s', $book_info['name'], $book_info['author_name']), $book_info['id']),
					'date_published'		=> date('Y-m-d H:i:s'),
				]);

				// delete old cover image if same version not exists in the book version
				$old_book_info = $book_info;
				$old_book_version_info = $this->book_version_model->getByVersion($book_info['id'], $book_info['version']);

				CI_Events::trigger('book_published', [
					'book_id'	=> $book_info['id']
				]);

				CI_Events::trigger('access_log', [
					'module'	=> 'book_published_' . $book_info['id']
				]);

				$book_info = $this->book_model->get($this->input->post('book_id'));

				// delete old cover image if no version change
				if ($old_book_info['version'] === $book_info['version']) {
					if (
						$book_info['cover_image'] !== $old_book_version_info['cover_image'] &&
						!empty($old_book_version_info['cover_image'])
					) {
						log_kb(['PublishDeleteCoverImage' => $this->s3->amazonS3Delete(
							str_replace('AuthorCoverImages/', '', $old_book_version_info['cover_image']),
							rtrim($this->config->item('s3_user_cover_img'), '/')
						)]);
					}

					if (
						$book_info['author_image'] !== $old_book_version_info['author_image'] &&
						!empty($old_book_version_info['author_image'])
					) {
						log_kb(['PublishDeleteAuthorImage' => $this->s3->amazonS3Delete(
							str_replace('AuthorImages/', '', $old_book_version_info['author_image']),
							rtrim($this->config->item('s3_author_img'), '/')
						)]);
					}
				}

				self::_updatePublishingLimit($book_info, $new_publish);
				self::_removeActiveSession($this->input->post('book_id'));

				$this->json['book'] 	= $book_info;
				$this->json['success'] 	= _l('book_published');
			}
		}
	}

	public function unPublishBook() {
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

			if ($book_info['user_id'] !== $this->session->userdata('user_id')) {
				$this->json['error'] = _li('you_can\'t_edit_the_book');
				return;
			}

			// open for editing
			$this->book_model->edit($this->input->post('book_id'), [
				'editing'	=> 1,
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'book_reedit_' . (int)$this->input->post('book_id')
			]);

			$book_info 			= $this->book_model->get($this->input->post('book_id'));
			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];
			$custom_cover_info 	= !empty($user_cover_info['custom_cover_id']) ? $this->custom_cover_model->get($user_cover_info['custom_cover_id']) : [];

			$this->json['book'] 				= $book_info;
			$this->json['book']['design'] 		= $user_cover_info['design'] ?? '';
			$this->json['book']['custom_cover'] = $custom_cover_info;
			$this->json['success'] 				= _li('Book_opened_for_the_editing');
		}
	}

	private function _updatePublishingLimit($book_info = [], $update_limit = false) {
		// check if user has subscription or free publishing limit

		$this->load->library('Subscription_lib');

		if ($update_limit) {
			// set user free publishing limit is occupied or not

			$this->subscription_lib->updatePublishingLimit($book_info);
		} else {
			if (!$this->subscription_lib->checkCanPublish($book_info)) {
				$this->json['can_publish'] 		= false;

				if (!empty($this->input->post('app_os'))) {
					$this->json['error'] 			=  _li('Free Publishing Limit Reached. Buy BriBooks+ to unlock unlimited publishing.');
				} else {
					$this->json['success'] 			=  _li('Free Publishing Limit Reached. Buy BriBooks+ to unlock unlimited publishing.');
				}

				CI_Events::trigger('access_log', [
					'module'	=> 'reached_publishing_limit_' . $book_info['id']
				]);

				return;
			}
		}
	}

	private function _updateTempUserId() {
		if ($this->session->userdata('user_id')) {
			log_kb([
				'update_book_temp_user' => [
					get_bb_user_id(),
					$this->session->userdata('user_id')
				]
			]);

			$this->book_model->updateTempUserId(
				get_bb_user_id(),
				$this->session->userdata('user_id')
			);
		}
	}

	private function _validateWriting($book_id = 0) {
		return ($book_info = $this->book_model->get($book_id ? $book_id : $this->input->post('book_id'))) &&
		(
			$book_info['user_id'] == $this->session->userdata('user_id') ||
			$book_info['temp_user_id'] == get_bb_user_id()
		);
	}

	private function _updateActiveSession($book_id = 0) {
		$this->load->library('user_agent');

		$cache_ttl 	= ENVIRONMENT === 'production' ? 24*3600 : 600;
		$browser	= !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser();
		$platform	= !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform();
		$cookie_id	= get_bb_user_id();

		$cache_key 	= vsprintf('%s_active_writing_%s', [
			ENVIRONMENT === 'production' ? 'live' : 'test',
			(int)$book_id,
		]);

		$data 		= vsprintf('%s_active_writing_%s_%s_%s_%s', [
			ENVIRONMENT === 'production' ? 'live' : 'test',
			$browser,
			$platform,
			$cookie_id,
			(int)$book_id,
		]);

		log_kb(['_updateActiveSession' => [$cache_key, $data]]);

		$this->cache->save($cache_key, $data, $cache_ttl);
	}

	private function _removeActiveSession($book_id = 0) {
		$cache_key 	= vsprintf('%s_active_writing_%s', [
			ENVIRONMENT === 'production' ? 'live' : 'test',
			(int)$book_id,
		]);

		$this->cache->delete($cache_key);
	}

	private function _validateActiveSession($book_id = 0) {
		$book_id 	= $book_id ? $book_id : $this->input->post('book_id');
		$book_info 	= $this->book_model->get($book_id);

		if (empty($book_info['editing']) && !empty($book_info['status'])) {
			$this->json['redirect'] = 'account/mybooks';
			$this->json['error'] 	= _li('This book has already been published on another platform and cannot be republished here.');

			return false;
		}

		$this->load->library('user_agent');

		$browser	= !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser();
		$platform	= !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform();
		$cookie_id	= get_bb_user_id();

		$cache_key 	= vsprintf('%s_active_writing_%s', [
			ENVIRONMENT === 'production' ? 'live' : 'test',
			(int)$book_id,
		]);

		$data 		= vsprintf('%s_active_writing_%s_%s_%s_%s', [
			ENVIRONMENT === 'production' ? 'live' : 'test',
			$browser,
			$platform,
			$cookie_id,
			(int)$book_id,
		]);

		$cache_info = $this->cache->get($cache_key);

		if ($cache_info == $data || empty($cache_info)) {
			return true;
		}

		log_kb(['active_session' => $cache_info]);

		$this->json['error'] 	= _li('We noticed your account might be active in another session. Please review to ensure data remains consistent.');
		$this->json['redirect'] = 'account/mybooks';

		return false;
	}
}
