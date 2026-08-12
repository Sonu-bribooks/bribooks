<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Book_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_id = 0) {
		$this->db->select('book.*, category.name AS category, genre.name AS genre');

		$this->db->where('book.id', (int)$book_id);
		$this->db->where('book._deleted', 0);

		$this->db->join('category', 'category.id = book.category_id', 'left');
		$this->db->join('genre', 'genre.id = book.genre_id', 'left');

		return $this->db->get('book')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			book.*,
			category.name AS category,
			genre.name AS genre,
			users.first_name,
			users.last_name,
			users.location,
			if(book.status > 0,
			(SELECT COALESCE(SUM(quantity), 0) FROM order_product
			JOIN `order` on `order`.id=order_product.order_id
			WHERE `order`.parent_order_id = 0 AND `order`._deleted = 0
			AND order_product.product_id = book.id
			AND order_product._deleted = 0), 0
			) AS sold
		');

		if (isset($data['book_id'])) {
			$this->db->where('book.id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('book.version', (int)$data['version']);
		}

		if (!empty($data['name'])) {
			$this->db->where('book.name', $data['name']);
		}

		if (!empty($data['author_name'])) {
			$this->db->where('book.author_name', $data['author_name']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['site_code'])) {
			$this->db->like('site.site_code', $data['site_code'], 'after');
		}

		if (isset($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (isset($data['section'])) {
			$this->db->where('users.section', $data['section']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', $data['grade_id']);
		}

		if (isset($data['grade'])) {
			$this->db->where('users.grade', (int)$data['grade']);
		}

		if (!empty($data['startdate']) && !empty($data['enddate'])) {
			$this->db->where('book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		} else if (empty($data['startdate']) && !empty($data['enddate'])) {
			$this->db->where('book.date_added <= ', date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')));
		} else if (!empty($data['startdate']) && empty($data['enddate'])) {
			$this->db->where('book.date_added >= ', date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')));
		}

		if (isset($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (!empty($data['user_ids'])) {
			$this->db->where_in('book.user_id', $data['user_ids']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('book.category_id', (int)$data['category_id']);
		}

		if (isset($data['cover_id'])) {
			$this->db->where('book.cover_id', (int)$data['cover_id']);
		}

		if (isset($data['reviewer_id'])) {
			$this->db->where('book.reviewer_id', (int)$data['reviewer_id']);
		}

		if (isset($data['genre_id'])) {
			$this->db->where('book.genre_id', (int)$data['genre_id']);
		}

		if (isset($data['featured'])) {
			$this->db->where('book.featured', (int)$data['featured']);
		}

		if (isset($data['status'])) {
			$this->db->where('book.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('book.status != ', (int)$data['ne_status']);

		}

		if (isset($data['isbn'])) {
			$isbn = $data['isbn'];
			$this->db->group_start();
			$this->db->where('book.isbn', $isbn);
			$this->db->or_where("REPLACE(book.isbn, '-', '') = '$isbn'");
			$this->db->group_end();
		}

		if (isset($data['isbn_country_code'])) {
			$this->db->where('book.isbn_country_code', $data['isbn_country_code']);
		}

		if (isset($data['has_isbn'])) {
			if ($data['has_isbn'] == '1') {
				$this->db->where('book.isbn !=', "");
			} else {
				$this->db->where('book.isbn =', "");
			}
		}

		if (isset($data['has_kdp_upload'])) {
			if ($data['has_kdp_upload'] == '1') {
				$this->db->where("book.id IN (select amazon_book.book_id from amazon_book)");
			} else {
				$this->db->where("book.id NOT IN (select amazon_book.book_id from amazon_book)");
			}
		}

		if (isset($data['has_amazon_url'])) {
			if ($data['has_amazon_url'] == '1') {
				$this->db->where('book.amazon_url !=', "");
			} else {
				$this->db->where('book.amazon_url =', "");
			}
		}

		if (isset($data['download_title_verso'])) {
			if ($data['download_title_verso'] == '1') {
				$this->db->where("book.id IN (select book_title_verso.book_id from book_title_verso)");
			} else {
				$this->db->where("book.id NOT IN (select book_title_verso.book_id from book_title_verso)");
			}
		}

		if (isset($data['has_hall_of_fame'])) {
			if ($data['has_hall_of_fame'] == '1') {
				$this->db->where("book.id IN (select hall_of_fame_books.book_id from hall_of_fame_books WHERE hall_of_fame_books._deleted=0)");
			} else {
				$this->db->where("book.id NOT IN (select hall_of_fame_books.book_id from hall_of_fame_books WHERE hall_of_fame_books._deleted=0)");
			}
		}

		if (isset($data['custom_theme'])) {
			$this->db->where("book.id IN (select page_version.book_id from page_version WHERE page_version._deleted=0 AND page_version.custom_theme_id != 0)");
			// $this->db->where("book.id IN (select page_version.book_id from page_version WHERE page_version.version = book.version AND page_version._deleted=0 AND page_version.custom_theme_id != 0)");
		}

		if (isset($data['custom_review_status'])) {
			$this->db->where(sprintf("book.id IN (select custom_theme_book_review.book_id from custom_theme_book_review WHERE custom_theme_book_review._deleted=0 AND custom_theme_book_review.book_id = book.id AND custom_theme_book_review.status = %s)", $data['custom_review_status']));
		}

		if (isset($data['quantity'])) {
			$this->db->having('sold', (int)$data['quantity']);
		}

		if (!empty($data['quantity_le'])) {
			$this->db->having('sold <= ', (int)$data['quantity_le']);
		}

		if (!empty($data['quantity_ge'])) {
			$this->db->having('sold >= ', (int)$data['quantity_ge']);
		}

		if (!empty($data['location'])) {
			$this->db->where('users.location', $data['location']);
		}

		if (!empty($data['ne_location'])) {
			$this->db->where('users.location!=', $data['ne_location']);
		}

		if (!empty($data['book_name'])) {
			$this->db->where('book.name', $data['book_name']);
		}

		if (!empty($data['author_name'])) {
			$this->db->where('book.author_name', $data['author_name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.id', $data['search'], 'after');
			$this->db->or_like('book.name', $data['search'], 'both');
			$this->db->or_like('book.author_name', $data['search'], 'both');
			$this->db->or_like('book.isbn', $data['search'], 'after');
			$this->db->or_like('book.slug', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'both');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.location', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'both');
			$this->db->group_end();
		}

		if (isset($data['archived'])) {
			$this->db->where('book.archived', (int)$data['archived']);
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('book.id in (select book_id from event_book where event_id = %s)', (int)$data['event_id']));
		}

		if (isset($data['event_ne'])) {
			$this->db->where('book.id not in (select book_id from event_book where _deleted = 0)');
		}

		if (isset($data['ne_event'])) {
			$this->db->where('book.id NOT IN (select book_id from event_book)');
		}

		$this->db->where('book._deleted', 0);

		$this->db->join('category', 'category.id = book.category_id', 'left');
		$this->db->join('genre', 'genre.id = book.genre_id', 'left');
		$this->db->join('users', 'users.id = book.user_id', 'left');

		if (!empty($data['site_code'])) {
			$this->db->join('site', 'site.id = users.site_id', 'left');
		}

		$this->db->from('book');

		$this->db->group_by('book.id');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'book.name',
			'book.author_name',
			'book.featured',
			'book.status',
			'book.site_id',
			'book.date_published',
			'book.date_approved',
			'book.date_added',
			'book.date_modified',
			'sold',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function get_all_with_sold($data = []) {
		$this->db->select('
			book.*,
			category.name AS category,
			users.first_name,
			users.last_name,
			users.grade,
			users.section,
			(
			SELECT COALESCE(SUM(quantity), 0) FROM order_product
			JOIN `order` on `order`.id=order_product.order_id
			WHERE `order`.parent_order_id = 0 AND `order`._deleted = 0
			AND order_product.product_id = book.id AND order_product._deleted = 0
			) AS sold
		');

		if (isset($data['book_id'])) {
			$this->db->where('book.id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('book.version', (int)$data['version']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['site_code'])) {
			$this->db->like('site.site_code', $data['site_code'], 'after');
		}

		if (isset($data['section_id'])) {
			$this->db->where('users.section_id', $data['section_id']);
		}

		if (isset($data['section'])) {
			$this->db->where('users.section', $data['section']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (isset($data['grade'])) {
			$this->db->where('users.grade', (int)$data['grade']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		if (isset($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (!empty($data['user_ids'])) {
			$this->db->where_in('book.user_id', $data['user_ids']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('book.category_id', (int)$data['category_id']);
		}

		if (isset($data['cover_id'])) {
			$this->db->where('book.cover_id', (int)$data['cover_id']);
		}

		if (isset($data['reviewer_id'])) {
			$this->db->where('book.reviewer_id', (int)$data['reviewer_id']);
		}

		if (isset($data['genre_id'])) {
			$this->db->where('book.genre_id', (int)$data['genre_id']);
		}

		if (isset($data['featured'])) {
			$this->db->where('book.featured', (int)$data['featured']);
		}

		if (isset($data['status'])) {
			$this->db->where('book.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('book.status != ', (int)$data['ne_status']);
		}

		if (isset($data['isbn'])) {
			$isbn = $data['isbn'];
			$this->db->group_start();
			$this->db->where('book.isbn', $isbn);
			$this->db->or_where("REPLACE(book.isbn, '-', '') = '$isbn'");
			$this->db->group_end();
		}

		if (isset($data['isbn_country_code'])) {
			$this->db->where('book.isbn_country_code', $data['isbn_country_code']);
		}

		if (isset($data['has_isbn'])) {
			if ($data['has_isbn'] == '1') {
				$this->db->where('book.isbn !=', "");
			} else {
				$this->db->where('book.isbn =', "");
			}
		}

		if (isset($data['has_kdp_upload'])) {
			if ($data['has_kdp_upload'] == '1') {
				$this->db->where("book.id IN (select amazon_book.book_id from amazon_book)");
			} else {
				$this->db->where("book.id NOT IN (select amazon_book.book_id from amazon_book)");
			}
		}

		if (isset($data['has_amazon_url'])) {
			if ($data['has_amazon_url'] == '1') {
				$this->db->where('book.amazon_url !=', "");
			} else {
				$this->db->where('book.amazon_url =', "");
			}
		}

		if (isset($data['download_title_verso'])) {
			if ($data['download_title_verso'] == '1') {
				$this->db->where("book.id IN (select book_title_verso.book_id from book_title_verso)");
			} else {
				$this->db->where("book.id NOT IN (select book_title_verso.book_id from book_title_verso)");
			}
		}

		if (isset($data['quantity'])) {
			$this->db->having('sold', (int)$data['quantity']);
		}

		if (!empty($data['quantity_le'])) {
			$this->db->having('sold <= ', (int)$data['quantity_le']);
		}

		if (!empty($data['quantity_ge'])) {
			$this->db->having('sold >= ', (int)$data['quantity_ge']);
		}

		if (!empty($data['location'])) {
			$this->db->where('users.location', $data['location']);
		}

		if (!empty($data['ne_location'])) {
			$this->db->where('users.location!=', $data['ne_location']);
		}

		if (!empty($data['book_name'])) {
			$this->db->where('book.name', $data['book_name']);
		}

		if (!empty($data['author_name'])) {
			$this->db->where('book.author_name', $data['author_name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'both');
			$this->db->or_like('book.author_name', $data['search'], 'both');
			$this->db->or_like('book.isbn', $data['search'], 'after');
			$this->db->or_like('book.slug', $data['search'], 'both');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->group_end();
		}

		if (isset($data['archived'])) {
			$this->db->where('book.archived', (int)$data['archived']);
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('book.id in (select book_id from event_book where event_id = %s)', (int)$data['event_id']));
		}

		$this->db->where('book._deleted', 0);

		$this->db->join('category', 'category.id = book.category_id', 'left');
		$this->db->join('users', 'users.id = book.user_id', 'left');

		if (!empty($data['site_code'])) {
			$this->db->join('site', 'site.id = users.site_id', 'left');
		}

		$this->db->from('book');

		$this->db->group_by('book.id');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'book.name',
			'book.author_name',
			'book.featured',
			'book.status',
			'book.site_id',
			'book.date_published',
			'book.date_approved',
			'book.date_added',
			'book.date_modified',
			'sold',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function getBestSeller($data = []) {
		$this->db->select('book.*, SUM(order_product.quantity) AS total');

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'both');
			$this->db->or_like('book.author_name', $data['search'], 'both');
			$this->db->or_like('book.isbn', $data['search'], 'after');
			$this->db->group_end();
		}

		if (isset($data['featured'])) {
			$this->db->where('book.featured', (int)$data['featured']);
		}

		$this->db->where('book._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where('order._deleted', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);
		$this->db->where('book.status', 1);
		$this->db->where('book.archived', 0);

		$this->db->join('order_product', 'order_product.product_id = book.id');
		$this->db->join('order', 'order.id = order_product.order_id');

		if (!empty($data['location'])) {
			$location = $data['location'];
			$this->db->join('users', "users.id = book.user_id AND users.location = '$location'");
		}

		if (!empty($data['ne_location'])) {
			$ne_location = $data['ne_location'];
			$this->db->join('users', "users.id = book.user_id AND users.location != '$ne_location'");
		}

		if (!empty($data['site_code'])) {
			$this->db->join('site', 'site.id = users.site_id');
		}

		if (!empty($data['quantity_ge'])) {
			$this->db->having('SUM(IF(order_product.quantity, order_product.quantity, 0)) >= ', (int)$data['quantity_ge']);
		}

		$this->db->from('book');

		$this->db->group_by('book.id');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'total',
			'book.name',
			'book.author_name',
			'book.featured',
			'book.status',
			'book.site_id',
			'book.date_published',
			'book.date_approved',
			'book.date_added',
			'book.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'total';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('book', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)($data['site_id'] ?? $this->config->item('site_id')),
		]);

		$book_id = $this->db->insert_id();

		$unique_id = '1' . sprintf('%03d', ($data['version'] ?? 0)) . sprintf('%09d', $book_id);

		self::edit($book_id, [
			'unique_id' => $unique_id
		]);

		return $book_id;
	}

	public function edit($book_id = 0, $data = []) {
		$book_info = self::get($book_id);

		$unique_id = '1' . sprintf('%03d', ($data['version'] ?? $book_info['version'])) . sprintf('%09d', $book_id);

		if(isset($data['isbn'])) {
			$data['isbn'] = preg_replace('/\s+/', '', $data['isbn']);
		}

		$this->db->where('id', (int)$book_id);
		$this->db->update('book', $data + [
			'unique_id' => $unique_id,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($book_id = 0) {
		$this->db->where('id', (int)$book_id);
		$this->db->update('book',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getTotalPages($book_id = 0) {
		$book_info = self::get($book_id);
		$this->db->select('page_version.id');
		return $this->db->get_where('page_version',  [
			'book_id'		=> (int)$book_id,
			'version'		=> (int)$book_info['version'],
			'_deleted'		=> 0,
		])->num_rows();
	}

	public function getPrice($book_id = 0) {
		$ebook_enabled 			= false;
		$black_white_enabled 	= false;
		$black_white_total 		= 0;
		$ppp_total				= 0;
		$black_white_ppp_total	= 0;
		$total_pages 			= self::getTotalPages($book_id) * 2 + 5;
		$is_banned_country 		= _is_banned_country($this->input->cookie('user_country_code', true));
		$is_subscribed_author	= self::_checkAuthorSubscription($book_id);

		if ($is_subscribed_author) {
			$ebook_enabled = true;
		} else {
			if (
				$total_pages >= EBOOK_PAGE_LIMIT ||
				$is_banned_country
			) {
				$ebook_enabled = true;
			}
		}
		
		if ($total_pages >= BLACK_WHITE_PAGE_LIMIT) {
			$black_white_enabled = true;
		}
		// $remainder = $total_pages % 8;

		// rounding in 8 pages
		// if ($remainder > 0) {
		// 	$total_pages += (8 - $remainder);
		// }

		// hard cover or paper back pages
		// $total_pages += 4;

		$base_price 		= $total = $this->config->item('site_base_price');
		$ebook_price 		= $this->config->item('site_ebook_price');
		$audio_book_price 	= $this->config->item('site_audio_book_price');
		$black_white_price 	= $this->config->item('site_black_white_price');

		if ($total_pages > $this->config->item('site_free_page_limit')) {
			$ppp_total = (
				$total_pages - $this->config->item('site_free_page_limit')
			) * $this->config->item('site_price_per_page');

			$black_white_ppp_total = $total_pages > BLACK_WHITE_FREE_LIMIT ? (
				$total_pages - BLACK_WHITE_FREE_LIMIT
			) * $this->config->item('site_black_white_price_per_page') : 0;

			$total = $base_price + $ppp_total;
			$black_white_total += $black_white_price + $black_white_ppp_total;
		}

		$price_data = [
			'price' 				=> round($base_price, 2),
			'total' 				=> round($total, 2),
			'ppp_total' 			=> round($ppp_total, 2),
			'total_pages' 			=> $total_pages,
			'ebook_price' 			=> $ebook_enabled ? round($ebook_price, 2) : 0,
			'audio_book_price' 		=> $is_subscribed_author ? round($audio_book_price, 2) : 0,
			'black_white_price' 	=> $black_white_enabled ? round($black_white_price, 2) : 0,
			'black_white_total' 	=> $black_white_enabled ? round($black_white_total, 2) : 0,
			'black_white_ppp_total' => $black_white_enabled ? round($black_white_ppp_total, 2) : 0,
			'is_banned_country'		=> $is_banned_country,
		];

		self::_formatEventPrice($book_id, $price_data, [
			'ebook_price' 			=> round($ebook_price, 2),
			'audio_book_price' 		=> round($audio_book_price, 2),
			'black_white_price' 	=> round($black_white_price, 2),
			'black_white_total' 	=> round($black_white_total, 2),
		]);

		return $price_data;
	}

	private function _formatEventPrice($book_id = 0, &$data = [], $extra_data = []) {
		$country_code 	= $this->input->cookie('user_country_code', true) ?? '';
		$options 		= bookBuyOptions($book_id, $country_code);

		if (!empty($options)) {
			if (!in_array('printed', $options)) {
				$data['price'] 				= 0.00;
				$data['total'] 				= 0.00;
				$data['black_white_price']	= 0.00;
				$data['black_white_total']	= 0.00;
			}

			if (in_array('printed', $options)) {
				if (in_array('black_white', $options)) {
					$data['black_white_price'] = $extra_data['black_white_price'] ?? 0.00;
					$data['black_white_total'] = $extra_data['black_white_total'] ?? 0.00;
				} else {
					$data['black_white_price'] = $data['black_white_price'] ?? 0.00;
					$data['black_white_total'] = $data['black_white_total'] ?? 0.00;
				}
			}

			if (in_array('ebook', $options)) {
				$data['ebook_price'] = $extra_data['ebook_price'] ?? 0.00;
			}

			if (in_array('audio_book', $options)) {
				$data['audio_book_price'] = $extra_data['audio_book_price'] ?? 0.00;
			}
		}
	}

	private function _checkAuthorSubscription($book_id = 0) {
		if (!empty($book_id) &&
			($book_info = $this->book_model->get($book_id)) &&
			($user_info = $this->user_model->get($book_info['user_id'])) &&
			($user_subscription_info = $this->user_subscription_model->get_all([
				'user_id'				=> $user_info['id'],
				'subscription_plan_id'	=> $user_info['subscription_plan_id'],
			])['rows'][0] ?? []) &&
			strtotime($user_subscription_info['end_date']) > time()
		) {
			return true;
		}

		return false;
	}

	public function updateTempUserId($temp_user_id = 0, $user_id = 0) {
		if (empty($user_id)) return;

		$this->db->update('book', [
			'user_id'		=> (int)$user_id,
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'temp_user_id'	=> $temp_user_id,
			'user_id'		=> 0
		]);
	}

	public function getBySlug($slug = '') {
		$this->db->select('book.*, category.name AS category');

		$this->db->where('book.slug', $slug);
		$this->db->where('book.status != ', 0);
		$this->db->where('book._deleted', 0);

		$this->db->join('category', 'category.id = book.category_id', 'left');

		return $this->db->get('book')->row_array();
	}

	public function getBookByToken($slug = '', $token = '') {
		$this->db->select('book.*');

		$this->db->where('book.slug', $slug);
		$this->db->where('book.preview_token', $token);
		$this->db->where('book._deleted', 0);

		return $this->db->get('book')->row_array();
	}

	public function isFreeAuthor($book_id = 0) {
		$this->db->select('subscription_plan.price');

		$this->db->where('book.id', (int)$book_id);

		$this->db->join('users', 'users.id = book.user_id');
		$this->db->join('subscription_plan', 'subscription_plan.id = users.subscription_plan_id');

		return $this->db->get('book')->row()->price < 1;
	}

	public function updateViews($book_id = 0) {
		$this->db->set('views', 'views+1', FALSE);
		$this->db->where('id', (int)$book_id);
		$this->db->update('book');
	}

	public function getRankingBySections($data = []) {
		$this->db->select('COUNT(book.id) AS books_quantity, COUNT(users.id) AS users_quantity, users.grade_id, users.section_id, users.grade, users.section');

		if (!empty($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		$this->db->where('book.status != ', 0);
		$this->db->where('book._deleted', 0);

		$this->db->join('users', 'users.id = book.user_id', 'left');

		$this->db->group_by('users.section_id');
		$this->db->order_by('books_quantity DESC, users_quantity DESC');

		return $this->db->get('book')->result_array();
	}

	public function getByBookId($book_id = 0) {
		$this->db->select('book.id, book.user_id, book.isbn, book.name AS book_name, book.author_name, category.name AS program');

		$this->db->where('book.id', (int)$book_id);
		$this->db->where('book.status', 1);
		$this->db->where('book._deleted', 0);

		$this->db->join('category', 'category.id = book.category_id', 'left');

		return $this->db->get('book')->row_array();
	}

	public function addUniqueId($book_id = '') {
		$book_info = self::get($book_id);

		$unique_id = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_id);

		self::edit($book_id, [
			'unique_id' => $unique_id
		]);

		$this->db->where('book_id', (int)$book_id);
		$this->db->update('book_version', [
			'unique_id' => $unique_id,
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		return $unique_id;
	}

	public function get_all_domestic_books() {
		$this->db->select('book.*, category.name AS category, users.first_name, users.last_name, users.mobile, users.email, users.site_id, site.site_code');
		$this->db->join('category', 'category.id = book.category_id', 'left');
		$this->db->join('users', 'users.id = book.user_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->where('book.status!=', 0);
		$this->db->where('book._deleted', 0);
		$this->db->where('site.country_code', 'IN');
		$this->db->from('book');
		$this->db->order_by('book.date_published ASC');
		$results = $this->db->get()->result_array();

		return $results;
	}
}
