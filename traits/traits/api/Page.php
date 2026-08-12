<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Page {
	public function getPages() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$result = array_map(function($item) {
				return self::_formatPageTheme($item);
			}, $this->page_model->get_all([
				'book_id'	=> $this->input->post('book_id'),
				'sort'		=> 'page.sort_order',
				'order'		=> 'ASC',
			])['rows'] ?? []);

			$this->json['pages'] = $result;

			self::_updateActiveSession($this->input->post('book_id'));
		}
	}

	public function getPublishedPages() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$book_info = $this->book_model->get($this->input->post('book_id'));

			// Fallback support for old books
			if (empty($this->page_version_model->get_all([
				'book_id'	=> $this->input->post('book_id'),
				'version'	=> $book_info['version'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['total'])) {
				$this->load->library('Version_lib', 'version_lib');
				$this->version_lib->applyFallback($book_info['id'], $book_info['version']);
			}

			$result = array_map(function($item) {
				return self::_formatPageTheme($item);
			}, $this->page_version_model->get_all([
				'book_id'	=> $this->input->post('book_id'),
				'version'	=> $book_info['version'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'] ?? []);

			$this->json['pages'] = $result;
		}
	}

	public function createPage() {
		$this->form_validation->set_rules('sort_order', _l('sort_order'), 'trim|required|numeric|less_than[500]');
		$this->form_validation->set_rules('theme_id', _l('theme_id'), [
			'trim',
			'required',
			'numeric',
			['theme', [$this->validate_model, 'theme']]
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

			if (!self::_validateActiveSession()) {
				return;
			}

			// freeing slot for current sort index
			$this->page_model->updateSortOrderBeforePageAdd(
				$this->input->post('book_id'),
				($this->input->post('sort_order') - 1)
			);

			$page_id = $this->page_model->add([
				'book_id'		=> (int)$this->input->post('book_id'),
				'theme_id'		=> (int)$this->input->post('theme_id'),
				'sort_order'	=> (int)$this->input->post('sort_order'),
			]);

			$page_info = $this->page_model->get($page_id);

			$this->json['page'] = self::_formatPageTheme($page_info);
			$this->json['page']['sort_order'] = (int)$page_info['sort_order'];

			$this->json['success'] = _l('page_modified');
		}
	}

	public function updatePageTheme() {
		$this->form_validation->set_rules('page_id', _l('page_id'), [
			'trim',
			'required',
			'numeric',
			['page', [$this->validate_model, 'page']]
		]);
		$this->form_validation->set_rules('theme_id', _l('theme_id'), [
			'trim',
			'required',
			'numeric',
			['theme', [$this->validate_model, 'theme']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$page_info = $this->page_model->get($this->input->post('page_id'));

			if (empty($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateWriting($page_info['book_id'])) {
				$this->json['error'] = _l('page_not_found');
				return;
			}

			if (!self::_validateActiveSession($page_info['book_id'])) {
				return;
			}

			$theme_info = $this->theme_model->get($this->input->post('theme_id'));

			$update_data = [
				'theme_id'			=> (int)$this->input->post('theme_id'),
			];

			if (empty($theme_info['custom_theme'])) {
				$update_data['custom_theme_id'] = 0;
			}

			$this->page_model->edit($this->input->post('page_id'), $update_data);

			$page_id = $page_info['id'];

			$page_info = $this->page_model->get($page_id);

			$this->json['page'] = self::_formatPageTheme($page_info);
			$this->json['page']['sort_order'] = (int)$page_info['sort_order'];
			$this->json['success'] = _l('page_modified');
		}
	}

	public function updatePageCustomTheme() {
		$this->form_validation->set_rules('page_id', _l('page_id'), [
			'trim',
			'required',
			'numeric',
			['page', [$this->validate_model, 'page']]
		]);
		$this->form_validation->set_rules('theme_id', _l('theme_id'), [
			'trim',
			'required',
			'numeric',
			['theme', [$this->validate_model, 'theme']]
		]);
		$this->form_validation->set_rules('custom_theme_id', _l('custom_theme_id'), [
			'trim',
			'required',
			'numeric',
			['custom_theme', [$this->validate_model, 'custom_theme']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$page_info = $this->page_model->get($this->input->post('page_id'));

			if (empty($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateWriting($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateActiveSession($page_info['book_id'])) {
				return;
			}

			$theme_info = $this->theme_model->get($this->input->post('theme_id'));

			$update_data = [
				'custom_theme_id'	=> (int)$this->input->post('custom_theme_id'),
			];

			log_kb([$theme_info]);

			if (empty($theme_info['custom_theme'])) {
				$update_data['custom_theme_id'] = 0;
			}

			$this->page_model->edit($this->input->post('page_id'), $update_data);

			$page_id = $page_info['id'];

			$page_info = $this->page_model->get($page_id);

			$this->json['page'] = self::_formatPageTheme($page_info);
			$this->json['page']['sort_order'] = (int)$page_info['sort_order'];
			$this->json['success'] = _l('page_modified');
		}
	}

	public function updatePageTexts() {
		$this->form_validation->set_rules('page_id', _l('page_id'), [
			'trim',
			'required',
			'numeric',
			['page', [$this->validate_model, 'page']]
		]);
		$this->form_validation->set_rules('texts[]', _l('texts'), [
			'trim',
			'max_length[700]'
		]);

		if (
			$this->input->post('texts') &&
			_has_not_allowed_chars($this->input->post('texts'))
		) {
			$this->json['error'] = _li('You are trying to use Non-English characters. Please use only valid English language characters.');
		}

		self::_runFormValidation();

		if (!$this->json) {
			$old_page_info = $page_info = $this->page_model->get($this->input->post('page_id'));

			if (empty($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateWriting($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateActiveSession($page_info['book_id'])) {
				return;
			}

			$clean_text = [];

			if (is_array($this->input->post('texts'))) {
				foreach ($this->input->post('texts') as $text) {
					$clean_text[] = maskSpamWord($text);
				}
			}

			$this->page_model->edit($this->input->post('page_id'), [
				'texts'		=> json_encode(_clean_text($clean_text))
			]);

			$page_id = $page_info['id'];

			$page_info = $this->page_model->get($page_id);

			self::_logMissingTexts($old_page_info, $page_info);

			$this->json['page'] = self::_formatPageTheme($page_info);
			$this->json['page']['sort_order'] = (int)$page_info['sort_order'];
			$this->json['success'] = _l('page_modified');
		}
	}

	public function sortPages() {
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

			foreach ($this->input->post('page_ids') as $key => $page_id) {
				$this->page_model->edit($page_id, [
					'sort_order' => (int)$key
				]);
			}

			$this->json['success'] = _l('page_reordered');
		}
	}

	public function deletePage() {
		$this->form_validation->set_rules('page_id', _l('page_id'), [
			'trim',
			'required',
			'numeric',
			['page', [$this->validate_model, 'page']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$page_info = $this->page_model->get($this->input->post('page_id'));

			if (empty($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateWriting($page_info['book_id'])) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (!self::_validateActiveSession($page_info['book_id'])) {
				return;
			}

			$this->page_model->delete($this->input->post('page_id'));
			$this->page_model->resetSortOrder($page_info['book_id']);

			$this->json['success'] = _l('page_modified');
		}
	}

	private function _logMissingTexts($old_page_info = [], $new_page_info = []) {
		$this->load->library('user_agent');

		$old_text = $old_page_info['texts'] ?? '';
		$new_text = $new_page_info['texts'] ?? '';

		if (strlen($new_text) < 20 && strlen($old_text) > 20) {
			log_kb_imp([
				'logEmptyTexts' => [$old_text, $new_text, $this->input->post()],
				'agent' 		=> [
					'user_id'	=> (int)$this->session->userdata('user_id'),
					'browser'	=> !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser(),
					'platform'	=> !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform(),
					'ip'		=> $this->input->ip_address(),
				]
			]);
		}
	}

	public function logMissingPage() {
		if (!$this->json) {
			log_kb_imp(['logMissingPage' => $this->input->post()]);
		}
	}

	private function _formatPageTheme($item = []) {
		$text_boxes = json_decode($item['text_boxes'], true);

		parse_textboxes($text_boxes);

		$custom_theme_info = $this->custom_theme_model->get($item['custom_theme_id']);
		$category_info = $this->category_model->get($item['theme_category_id']);

		return array_merge($item, [
			'sort_order'	=> (int)$item['sort_order'],
			'font_size'		=> (int)$item['font_size'],
			'texts'			=> json_decode($item['texts'], true),
			'text_boxes' 	=> $text_boxes,
			'theme'			=> [
				'id'					=> $item['theme_id'],
				'category_id'			=> $item['theme_category_id'],
				'name'					=> $item['theme_name'],
				'image'					=> $item['image'],
				's3Image'				=> $this->config->item('cloudfront_url') . 'public/Themes/' . $item['image'],
				'text_boxes'			=> $text_boxes,
				'font_family'			=> $item['font_family'],
				'font_size'				=> (int)$item['font_size'],
				'font_color'			=> $item['font_color'],
				'font_weight'			=> $item['font_weight'],
				'custom_theme_open'		=> $category_info['custom_theme'] ?? 0,
				'custom_theme'			=> [
					'id'			=> $item['custom_theme_id'],
					'image'			=> $custom_theme_info['image'] ?? '',
					's3Image'		=> !empty($custom_theme_info['image'])
						? $this->config->item('cloudfront_url') . 'public/CustomThemes/' . $custom_theme_info['image']
						: '',
				],
			]
		]);
	}
}
