<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Version_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('book/BookVersion_model');
		$this->load->model('book/Page_model');
		$this->load->model('book/PageVersion_model');
		$this->load->model('user/Student_model');
		$this->load->model('order/Order_model');

		$this->book_model 			= $this->CI->Book_model;
		$this->book_version_model 	= $this->CI->BookVersion_model;
		$this->student_model 		= $this->CI->Student_model;
		$this->order_model 			= $this->CI->Order_model;
		$this->page_model 			= $this->CI->Page_model;
		$this->page_version_model 	= $this->CI->PageVersion_model;
	}

	public function apply($book_id = 0) {
		// Once book published
		// Step 1. check has version
		// Step 2. check has order
		// Step 3. create pages version
		// Step 4. create book version
		$book_info = $this->book_model->get($book_id);

		if (empty($book_info)) return;

		if ($book_info['version']) {
			$total_order_count = $this->order_model->getAuthorProducts([
				'product_id'	=> $book_id,
				'version'		=> $book_info['version'],
			]);

			if ($total_order_count) {
				$new_version = $book_info['version'] + 1;

				$this->book_model->edit($book_id, [
					'version'	=> (int)$new_version
				]);

				self::_savePageVersion($book_id, $new_version);
			} else {
				self::_savePageVersion($book_id, $book_info['version']);
			}
		} else {
			$this->book_model->edit($book_id, [
				'version'	=> 1
			]);

			self::_savePageVersion($book_id, 1);
		}
	}

	// Remove it safely
	public function applyFallback($book_id = 0, $version = 1) {
		self::_savePageVersion($book_id, $version);
	}

	private function _savePageVersion($book_id = 0, $version = 1) {
		if ($book_version_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$book_info = $this->book_model->get($book_id);

			unset(
				$book_info['id'],
				$book_info['date_added'],
				$book_info['date_modified'],
				$book_info['category'],
				$book_info['genre'],
			);

			$this->book_version_model->edit($book_version_info['id'], array_merge($book_info, [
				'version'		=> (int)$version,
				'book_id'		=> (int)$book_id,
			]));
		} else {
			$book_info = $this->book_model->get($book_id);

			unset(
				$book_info['id'],
				$book_info['date_added'],
				$book_info['date_modified'],
				$book_info['category'],
				$book_info['genre'],
			);

			$this->book_version_model->add(array_merge($book_info, [
				'version'		=> (int)$version,
				'book_id'		=> (int)$book_id,
			]));
		}

		$pages = $this->db->get_where('page', [
			'book_id'	=> $book_id,
		])->result_array();

		foreach ($pages as $page) {
			$filter_data = [
				'page_id'	=> $page['id'],
				'version'	=> $version,
				'sort'		=> 'page_version.version',
				'order'		=> 'DESC',
			];

			// no versioning for deleted pages only marked deleted
			if (!empty($page['_deleted'])) {
				unset($filter_data['version']);
			}

			if ($existing = $this->page_version_model->get_all($filter_data)['rows'][0] ?? []) {
				// exclude deleted pages from versioning
				if (!empty($page['_deleted']) && $existing['version'] != $version) continue;

				$this->page_version_model->edit($existing['id'], [
					'version'			=> $page['_deleted'] ? $existing['version'] : (int)$version,
					'page_id'			=> (int)$page['id'],
					'book_id'			=> (int)$page['book_id'],
					'theme_id'			=> (int)$page['theme_id'],
					'custom_theme_id'	=> (int)$page['custom_theme_id'],
					'texts'				=> $page['texts'],
					'sort_order'		=> (int)$page['sort_order'],
					'status'			=> (int)$page['status'],
					'_deleted'			=> (int)$page['_deleted'],
					'date_deleted'		=> empty($page['date_deleted'])
						? null
						: date('Y-m-d H:i:s', strtotime($page['date_deleted'])),
				]);
			} else {
				if (!empty($page['_deleted'])) continue;

				$this->page_version_model->add([
					'version'			=> (int)$version,
					'page_id'			=> (int)$page['id'],
					'book_id'			=> (int)$page['book_id'],
					'theme_id'			=> (int)$page['theme_id'],
					'custom_theme_id'	=> (int)$page['custom_theme_id'],
					'texts'				=> $page['texts'],
					'sort_order'		=> (int)$page['sort_order'],
					'status'			=> (int)$page['status'],
					'_deleted'			=> (int)$page['_deleted'],
					'date_deleted'		=> empty($page['date_deleted'])
						? null
						: date('Y-m-d H:i:s', strtotime($page['date_deleted'])),
				]);
			}
		}
	}

	public function get($book_id = 0) {

	}
}
