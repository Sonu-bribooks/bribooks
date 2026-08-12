<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportBkp {
	public function get_school_ranks_dump() {
		// return;
		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [
			'parent_id'	=> 581,
		];

		$schools = $this->site_model->get_all($filter_data)['rows'] ?? [];

		$unsorted_rankings = $rankings = $sort_order = [];

		foreach ($schools as $key => $item) {
			$city_info = $this->city_model->get($item['city_id']);
			$state_info = $this->state_model->get($item['state_id']);

			$book_written = $this->ranking_model->get_books([
				'site_id'	=> $item['id'],
				'end_date'	=> '2023-03-15 21:00:00',
			])['total'];
			$book_published = $this->ranking_model->get_books([
				'site_id'	=> $item['id'],
				'end_date'	=> '2023-03-15 21:00:00',
				'status'	=> 1,
			])['total'];
			$total_sold = $this->ranking_model->getTotalSolds([
				'site_id'	=> $item['id'],
				'end_date'	=> '2023-03-15 21:00:00',
			]);

			if ($item['id'] == 265) continue;

			$sort_order[] = $book_written * 0.2 + $book_published * 0.35 + $total_sold * 0.45;

			$unsorted_rankings[] = [
				'id'			=> $item['id'],
				'rank'			=> $rank,
				'name'			=> ucfirst($item['name']),
				'email'			=> $item['owner_email'],
				'mobile'		=> $item['owner_mobile'],
				'city'			=> $city_info['name'],
				'city_id'		=> $city_info['id'],
				'state'			=> $state_info['name'],
				'state_id'		=> $state_info['id'],
				'book_written'	=> $book_written,
				'book_published'=> $book_published,
				'sold'			=> readable_format(!empty($total_sold) ? $total_sold : 0),
			];
		}

		array_multisort($sort_order, SORT_DESC, $unsorted_rankings);

		foreach ($unsorted_rankings as $rank => $item) {
			$rankings[] = array_merge($item, ['rank' => ($rank + 1)]);
		}

		$state_ranks = $city_ranks = [];

		foreach ($rankings as $item) {
			$state_ranks[$item['state_id']]['schools'][] = $item;
			$city_ranks[$item['city_id']]['schools'][] = $item;
		}

		$state_ranks = array_filter($state_ranks, function($item) {
			return count($item['schools']) >= 10;
		});
		$city_ranks = array_filter($city_ranks, function($item) {
			return count($item['schools']) >= 5;
		});

		$city_ranks_toppers = [];

		foreach ($city_ranks as $item) {
			$city_ranks_toppers[] = array_shift($item['schools']);
		}

		$state_ranks_toppers = [];

		foreach ($state_ranks as $item) {
			$state_ranks_toppers[] = array_shift($item['schools']);
		}

		// pr(array_values($state_ranks_toppers));

		// self::_downloadCsv($state_ranks_toppers, 'state_ranks_toppers_');
		// self::_downloadCsv($city_ranks_toppers, 'city_ranks_toppers_');
		self::_downloadCsv($rankings, 'school_rankings_');
	}

	public function gen_book_pdf_from_csv() {
		return;
		$page = 5;
		$limit = 50;

		$this->load->library('zip');
		$this->load->library('parsecsv');
		$this->parsecsv->delimiter = ';';

		$this->parsecsv->auto('assets/csv/missing_pdf.csv');
		$results = array_column($this->parsecsv->data, 'folder_name');

		// pr($results);

		// echo '<pre>';

		$products = [];

		foreach ($results as $filename) {
			preg_match('/^(.+?)\-by\-(.+?)\-v(\d)\-(.+?)$/ims', $filename, $output);

			$book_info = $this->book_model->getBySlug($output[1]);

			if (empty($book_info)) {
				// print_r([
				// 	'slug'			=> $filename,
				// 	'output'		=> $output,
				// ]);
				continue;
			}

			$products[] = [
				'slug'			=> $filename,
				'output'		=> $output,
				'product_id'	=> $book_info['id'],
				'version'		=> $output[3],
				'option'		=> json_encode(['name' => $output[4]]),
			];
		}

		// $products = array_slice($products, ($page - 1) * $limit, $limit);
		$products = array_slice($products, ($page - 1) * $limit);

		// pr($products);

		self::_genMissingBookZipPdfs($products);
	}

	public function export_printer_csv($printer_id = 0, $date = '') {
		// return;
		if (empty($printer_id) || empty($date)) return;

		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');

		$products = $this->printer_stats_model->printerAssignData([
			'date_added'		=> $date,
			'assign_printer_id'	=> $printer_id,
			// 'status'			=> 4,
		])['rows'] ?? [];

		self::_genBookZipPdfsCsv($products, 'bookcsv');
	}

	public function export_reprinter_csv($printer_id = 0, $date = '') {
		// return;
		if (empty($printer_id) || empty($date)) return;

		$this->load->model('order/ReprintOrder_model', 'reprint_order_model');

		$products = $this->reprint_order_model->reprintOrders([
			'date_added'		=> $date,
			'assign_printer_id'	=> $printer_id,
			// 'status'			=> 4,
		])['rows'] ?? [];

		self::_genBookZipPdfsCsv($products, 'bookcsv');
	}

	private function _genBookZipPdfsCsv($products = [], $local_filename = '') {
		$orders = [];

		$sort_order = [];

		foreach ($products as $product) {
			$book_info = $this->book_version_model->getByVersion($product['product_id'], $product['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $product['product_id'],
				'version'	=> $product['version'],
			])['total'] ?? 0;

			if (empty($book_info)) continue;

			$option = json_decode($product['option'], true);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				$option['name'],
			]);

			if (isset($orders[$filename])) {
				$orders[$filename]['quantity'] += $product['quantity'];
			} else {
				$book_info['unique_id'] = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_info['book_id']);

				$latest_book_info = $this->book_model->get($book_info['book_id']);

				if ($latest_book_info['version'] != $book_info['version']) {
					$book_info['isbn'] = '';
				}

				if(!empty($book_info['isbn'])) {
					$book_info['isbn'] = str_replace('-', '', preg_replace('/\s+/', '', $book_info['isbn']));
				}

				$orders[$filename] = [
					'assignment_code'	=> $product['assignment_code'],
					'folder'			=> $filename,
					'sku'				=> _o_b_code($book_info['book_id'], $book_info['version'], $option['name']),
					'ISBN/SN'			=> !empty($book_info['isbn']) ? $book_info['isbn'] : $book_info['unique_id'],
					'book_name'			=> $book_info['name'],
					'author_name'		=> $book_info['author_name'],
					'version'			=> $book_info['version'],
					'option'			=> $option['name'],
					'quantity'			=> $product['quantity'],
					'total_pages'		=> $total_pages * 2 + 1,
					'assigned_date'		=> date('M j, Y', strtotime($product['date_added'])),
					'order_ids'			=> $product['order_ids'],
					'printer_comment'	=> '',
					'BriBooks_comment'	=> '',
				];

				$sort_order[] = $filename;
			}
		}

		array_multisort($sort_order, $orders);

		self::_downloadCsv(array_values($orders), $local_filename);
	}

	public function export_book_zip_pdfs($page = 1) {
		return;
		$this->load->library('zip');
		$limit = 500;
		$max_id = 4325;

		$results = $this->order_model->get_all([
			'startdate'	=> date('Y-m-d', strtotime('2022-12-13 20:52:46')),
			'enddate'	=> date('Y-m-d', strtotime('2022-12-26 17:58:36')),
			'status'	=> 1,
			// 'start'		=> $page > 0
			// 	? ($page - 1) * $limit
			// 	: 0,
			// 'limit'		=> $limit,
			'sort'		=> 'order.date_added',
			'order'		=> 'ASC',
		])['rows'] ?? [];

		// pr($results);

		// self::_genExportBookZipPdfs($results, $max_id, 'Paperback');
		self::_genExportBookZipPdfsCsv($results, $max_id, 'Hard Cover');
	}

	private function _genExportBookZipPdfsCsv($results = [], $max_id = 0, $cover = 'Paperback') {
		$orders = [];

		foreach ($results as $key => $item) {
			if ($item['id'] > $max_id) break;

			$products = $this->order_model->getProducts($item['id']);

			foreach ($products as $product) {
				$book_info = $this->book_model->get($product['product_id']);

				if (empty($book_info)) continue;

				$option = json_decode($product['option'], true);

				if ($option['name'] != $cover) continue;

				$filename = vsprintf('%s-v%s-%s', [
					$book_info['slug'],
					$book_info['version'],
					$option['name'],
				]);

				if (isset($orders[$filename])) {
					$orders[$filename]['quantity'] += $product['quantity'];
				} else {
					$orders[$filename] = [
						'folder'		=> $filename,
						'book_name'		=> $book_info['name'],
						'author_name'	=> $book_info['author_name'],
						'version'		=> $book_info['version'],
						'option'		=> $option['name'],
						'quantity'		=> $product['quantity'],
					];
				}
			}
		}

		self::_downloadCsv(array_values($orders), 'orders');
	}

	private function _genMissingBookZipPdfs($products = []) {
		$this->load->library('zip');

		$exclude = [];

		foreach ($products as $product) {
			$book_info = $this->book_version_model->getByVersion(
				$product['product_id'],
				$product['version']
			);

			if (empty($book_info)) continue;

			$option = json_decode($product['option'], true);

			// Add Cover
			$pdf_data = self::printCover(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				$option['name'],
			]);

			if (in_array($filename, $exclude)) continue;

			$exclude[] = $filename;

			$dirname = vsprintf('%s/%s/%s', [
				'bookpdfs_' . (int)$info['printer_id'],
				$option['name'],
				$filename,
			]);

			$this->zip->add_data(vsprintf('%s/cover-%s.pdf', [
				$dirname,
				$filename,
			]), $pdf_data);

			// Add pages
			$pdf_data = self::printBookPages(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			$this->zip->add_data(vsprintf('%s/pages-%s.pdf', [
				$dirname,
				$filename
			]), $pdf_data);
		}

		$this->zip->download('books.zip');
	}

	private function _genExportBookZipPdfs($results = [], $max_id = 0, $cover = 'Paperback') {
		$exclude = [];

		foreach ($results as $key => $item) {
			if ($item['id'] > $max_id) break;

			$products = $this->order_model->getProducts($item['id']);

			foreach ($products as $product) {
				$book_info = $this->book_model->get($product['product_id']);

				if (empty($book_info)) continue;

				$option = json_decode($product['option'], true);

				if ($option['name'] != $cover) continue;

				// Add Cover
				$pdf_data = self::printCover(
					$book_info['id'],
					$book_info['version'],
					false,
					false
				);

				$filename = vsprintf('%s-v%s-%s', [
					$book_info['slug'],
					$book_info['version'],
					$option['name'],
				]);

				if (in_array($filename, $exclude)) continue;

				$exclude[] = $filename;

				$dir_name = vsprintf('books/%s/%s', [
					$option['name'],
					$filename,
				]);

				$this->zip->add_data(vsprintf('%s/cover-%s.pdf', [
					$dir_name,
					$filename,
				]), $pdf_data);

				// Add pages
				$pdf_data = self::printBookPages(
					$book_info['id'],
					$book_info['version'],
					false,
					false
				);

				$this->zip->add_data(vsprintf('%s/pages-%s.pdf', [
					$dir_name,
					$filename
				]), $pdf_data);
			}
		}

		$this->zip->download('books.zip');
	}

	public function export_site_students($site_id = 0) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/Section_model', 'section_model');
		$data['site_info'] = $this->site_model->get($site_id);

		$students = $this->student_model->get_all([
			'site_id' => $site_id
		])['rows'] ?? [];

		$data['total_registered'] = count($students);

		$data['students'] = [];

		$grade_sort_order = [];
		$section_sort_order = [];
		$name_sort_order = [];

		foreach ($students as $item) {
			$grade_info = $this->grade_model->get($item['grade_id']);
			$section_info = $this->section_model->get($item['section_id']);

			$book_written = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'grade_id'	  	=> $grade_info['id'],
				'section_id'	=> $section_info['id'],
			])['total'];
			$book_published = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'grade_id'	  	=> $grade_info['id'],
				'section_id'	=> $section_info['id'],
				'ne_status'	 	=> 0,
			])['total'];

			$data['students'][] = [
				'name'			=> $item['first_name'] . ' ' . $item['last_name'],
				'email'			=> $item['email'],
				'mobile'		=> $item['mobile'],
				'grade'			=> $grade_info['name'],
				'section'		=> $section_info['name'],
				'book_written'	=> $book_written,
				'book_published'=> $book_published,
			];

			$grade_sort_order[] = $grade_info['name'];
			$section_sort_order[] = $section_info['name'];
			$name_sort_order[] = $item['first_name'] . ' ' . $item['last_name'];
		}

		array_multisort($grade_sort_order, $section_sort_order, $name_sort_order, $data['students']);

		self::_downloadCsv($data['students'], $data['site_info']['name']);
	}

	public function import_bb_data() {
		return;
		self::_importData();
	}

	private function _importData() {
		$this->load->library('parsecsv');
		$this->parsecsv->delimiter = ';';

		self::_importThemesAndMap();
	}

	private function _importThemesAndMap() {
		$this->parsecsv->auto('assets/csv/bbbkp/bbthemes.csv');
		$rows = $this->parsecsv->data;

		$this->load->model('design/Theme_model', 'theme_model');

		$exclude_categories = [21];

		foreach ($rows as $key => $row) {
			$background = basename($row['image']);

			if ($row['_deleted']) continue;
			if (in_array($row['category_id'], $exclude_categories)) continue;

			$data = [
				'category_id'	=> trim($row['category_id']),
				'name'			=> trim($row['name']),
				'image'			=> trim($row['image']),
				'text_boxes'	=> trim($row['text_boxes']),
				'font_family'	=> trim($row['font_family']),
				'font_size'		=> trim($row['font_size']),
				'font_color'	=> trim($row['font_color']),
				'font_weight'	=> trim($row['font_weight']),
			];

			echo '<pre>';

			if ($theme_info = $this->db
				->like('image', $background, 'before')
				->get('theme')
				->row_array()
			) {
				echo $key . ' Old Data:: ' . $theme_info['id'] . ' -- ' . $row['id'] . ' -- ' . print_r($data, 1) . '<br>';
				// $this->theme_model->edit($theme_info['id'], $data);
			} else {
				echo $key . ' New Data:: ' . ($theme_info['id'] ?? '') . ' -- ' . $row['id'] . ' -- ' . print_r($data, 1) . '<br>';
				// $this->theme_model->add($data);
			}
		}
	}

	private function _importCategories() {
		return;
		$this->parsecsv->auto('assets/csv/bbbkp/categories.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $index => $data) {
			if ($category = $this->db->get_where('category', [
				'name'			=> $data['name'],
			])->row_array()) {
				$category_id = $category['id'];
			} else {
				$this->db->insert('category', [
					'name'						=> $data['name'],
					'image'						=> $data['image'],
					'status'					=> (int)$data['status'],
					'date_added'				=> date('Y-m-d H:i:s', strtotime($data['createdAt'])),
					'date_modified'				=> date('Y-m-d H:i:s', strtotime($data['updatedAt'])),
				]);

				$category_id = $this->db->insert_id();
			}

			$slug = preg_replace('/\s+/', '-', trim($data['name']));

			// Import Covers
			self::_importCovers($category_id, $slug);

			// Import Themes
			self::_importThemes($category_id, $slug);
		}
	}

	private function _importCovers($category_id = 0, $slug = '') {
		return;
		$rows = self::_getCovers($slug);

		echo 'Importing Covers' . PHP_EOL;
		hc($rows);

		foreach ($rows as $index => $data) {
			$this->db->insert('cover', [
				'category_id'				=> (int)$category_id,
				'image'						=> $data['image'],
				'heading_style'				=> $data['heading_style'],
				'date_added'				=> date('Y-m-d H:i:s', strtotime($data['createdAt'])),
				'date_modified'				=> date('Y-m-d H:i:s', strtotime($data['updatedAt'])),
			]);
		}
	}

	private function _importThemes($category_id = 0, $ext_category_id = 0) {
		return;
		$rows = self::_getThemes($ext_category_id);

		echo 'Importing Themes' . PHP_EOL;
		hc($rows);

		foreach ($rows as $index => $data) {
			$this->db->insert('theme', [
				'category_id'				=> (int)$category_id,
				'name'						=> $data['name'],
				'image'						=> $data['image'],
				'text_boxes'				=> $data['text_boxes'],
				'font_family'				=> $data['font_family'],
				'font_size'					=> $data['font_size'],
				'font_color'				=> $data['font_color'],
				'font_weight'				=> $data['font_weight'],
				'status'					=> 1,
				'date_added'				=> date('Y-m-d H:i:s', strtotime($data['createdAt'])),
				'date_modified'				=> date('Y-m-d H:i:s', strtotime($data['updatedAt'])),
			]);
		}
	}

	private function _importUsers($rows = []) {
		$this->parsecsv->auto('assets/csv/bbbkp/users.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $index => $data) {
			// if (empty($data['name'])) continue;

			echo 'Importing User' . PHP_EOL;
			hc($data);

			// 1. Add student
			$explode = explode(' ', trim($data['name']), 2);

			$first_name = array_shift($explode);
			$last_name = array_shift($explode);

			if ($student = $this->db->get_where('users', [
				'email'			=> $data['email'],
				'site_id'		=> 1,
				'role_id'		=> 2,
			])->row_array()) {
				$student_id = $student['id'];
			} else {
				$this->db->insert('users', [
					'first_name'				=> $first_name ?? '',
					'last_name'					=> $last_name ?? '',
					'password'					=> md5($data['email']),
					'role_id'					=> 2,
					'mobile'					=> $data['mobile'],
					'email'						=> $data['email'],
					'relation'					=> $data['relation'],
					'subscription_plan_id'		=> 5,
					'slug'						=> $data['slug'],
					'biography'					=> $data['bio'],
					'image'						=> $data['image'],
					'age'						=> (int)$data['age'],
					'status'					=> 1,
					'site_id'					=> 1,
					'date_added'				=> date('Y-m-d H:i:s', strtotime($data['createdAt'])),
				]);

				$student_id = $this->db->insert_id();
			}

			// Import Books
			self::_importBooks($student_id, $data['id']);
		}
	}

	private function _importBooks($user_id = 0, $ext_user_id = 0) {
		$rows = self::_getBooks($ext_user_id);

		echo 'Importing Books' . PHP_EOL;
		hc($rows);

		foreach ($rows as $index => $data) {
			$this->db->insert('book', [
				'user_id'					=> (int)$user_id,
				'site_id'					=> 1,
				'name'						=> $data['name'],
				'author_name'				=> $data['author_name'],
				'author_bio'				=> $data['author_bio'],
				'author_image'				=> $data['author_image'],
				'cover_image'				=> $data['cover_image'],
				'back_color'				=> $data['back_color'],
				'slug'						=> $data['slug'],
				'cover_id'					=> (int)self::_getCoverId($data['coverID']),
				'category_id'				=> (int)self::_getCategoryId($data['categoryID']),
				'featured'					=> (int)$data['featured'],
				'status'					=> (int)$data['status'],
				'date_approved'				=> date('Y-m-d H:i:s', strtotime($data['date_approved'])),
				'date_published'			=> date('Y-m-d H:i:s', strtotime($data['date_published'])),
				'date_added'				=> date('Y-m-d H:i:s', strtotime($data['createdAt'])),
				'date_modified'				=> date('Y-m-d H:i:s', strtotime($data['updatedAt'])),
			]);

			$book_id = $this->db->insert_id();
			// Import Pages

			self::_importPages($book_id, $data['id']);
		}
	}

	private function _importPages($book_id = 0, $ext_book_id = 0) {
		$rows = self::_getPages($ext_book_id);

		echo 'Importing Pages' . PHP_EOL;
		hc($rows);

		foreach ($rows as $index => $data) {
			$this->db->insert('page', [
				'book_id'					=> (int)$book_id,
				'theme_id'					=> (int)self::_getThemeId($data['themeID']),
				'sort_order'				=> (int)$data['sort_order'],
				'texts'						=> $data['texts'],
				'date_added'				=> date('Y-m-d H:i:s', strtotime($data['createdAt'])),
				'date_modified'				=> date('Y-m-d H:i:s', strtotime($data['updatedAt'])),
			]);
		}
	}

	private function _getCovers($slug = '') {
		$this->parsecsv->auto('assets/csv/bbbkp/covers.csv');
		$rows = $this->parsecsv->data;

		return array_values(array_filter($rows, function ($item) use($slug) {
			return strpos($item['image'], $slug) !== false;
		}));
	}

	private function _getThemes($slug = '') {
		$this->parsecsv->auto('assets/csv/bbbkp/themes.csv');
		$rows = $this->parsecsv->data;

		return array_values(array_filter($rows, function ($item) use($slug) {
			return strpos($item['image'], $slug) !== false;
		}));
	}

	private function _getBooks($ext_user_id = 0) {
		$this->parsecsv->auto('assets/csv/bbbkp/books.csv');
		$rows = $this->parsecsv->data;

		return array_values(array_filter($rows, function ($item) use($ext_user_id) {
			return $item['userID'] === $ext_user_id;
		}));
	}

	private function _getPages($ext_book_id = 0) {
		$this->parsecsv->auto('assets/csv/bbbkp/pages.csv');
		$rows = $this->parsecsv->data;

		return array_values(array_filter($rows, function ($item) use($ext_book_id) {
			return $item['bookID'] === $ext_book_id;
		}));
	}

	private function _getThemeId($ext_theme_id = 0) {
		$this->parsecsv->auto('assets/csv/bbbkp/themes.csv');
		$rows = $this->parsecsv->data;

		$results = array_values(array_filter($rows, function ($item) use($ext_theme_id) {
			return $item['id'] === $ext_theme_id;
		}));

		return $this->db->get_where('theme', [
			'image'	=> $results[0]['image'],
		])->row()->id;
	}

	private function _getCoverId($ext_cover_id = 0) {
		$this->parsecsv->auto('assets/csv/bbbkp/covers.csv');
		$rows = $this->parsecsv->data;

		$results = array_values(array_filter($rows, function ($item) use($ext_cover_id) {
			return $item['id'] === $ext_cover_id;
		}));

		return $this->db->get_where('cover', [
			'image'	=> $results[0]['image'],
		])->row()->id;
	}

	private function _getCategoryId($ext_category_id = 0) {
		$this->parsecsv->auto('assets/csv/bbbkp/categories.csv');
		$rows = $this->parsecsv->data;

		$results = array_values(array_filter($rows, function ($item) use($ext_category_id) {
			return $item['id'] === $ext_category_id;
		}));

		return $this->db->get_where('category', [
			'image'	=> $results[0]['image'],
		])->row()->id;
	}
}
