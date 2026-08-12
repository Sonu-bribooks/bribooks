<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Bookstore_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('bookstore.*');

		$this->db->where('bookstore.id', (int)$id);
		$this->db->where('bookstore._deleted', 0);

		return $this->db->get('bookstore')->row_array();
	}

	public function getByBookId($book_id = 0) {
		$this->db->select('bookstore.*');

		$this->db->where('bookstore.book_id', (int)$book_id);
		$this->db->where('bookstore._deleted', 0);

		return $this->db->get('bookstore')->row_array();
	}

	public function get_all($data = []) {
		$joined_users = false;

		$this->db->select('bookstore.id');

		if (!empty($data['search'])) {
			$data['search'] = '+' . preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], trim($data['search']));

			$data['search'] = rtrim($data['search']);

			$this->db->select(sprintf('MATCH (bookstore.name, bookstore.author_name) AGAINST ("%s") AS score', $data['search']), NULL, FALSE);

			$this->db->where(sprintf('MATCH (bookstore.name, bookstore.author_name) AGAINST ("%s")', $data['search']), NULL, FALSE);
		}

		if (isset($data['book_id'])) {
			$this->db->where('bookstore.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('bookstore.version', (int)$data['version']);
		}

		if (!empty($data['book_name'])) {
			$this->db->where('bookstore.name', $data['book_name']);
		}

		if (!empty($data['name'])) {
			$this->db->where('bookstore.name', $data['name']);
		}

		if (!empty($data['author_name'])) {
			$this->db->where('bookstore.author_name', $data['author_name']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('bookstore.user_id', (int)$data['user_id']);
		}

		if (!empty($data['user_ids'])) {
			$this->db->where_in('bookstore.user_id', $data['user_ids']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('bookstore.category_id', (int)$data['category_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('bookstore.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			$this->db->where('bookstore.status != ', (int)$data['ne_status']);
		}

		if (isset($data['admin_status'])) {
			$this->db->where('bookstore.status', (int)$data['admin_status']);
		}

		if (isset($data['quantity'])) {
			$this->db->where('bookstore.sold', (int)$data['quantity']);
		}

		if (!empty($data['quantity_le'])) {
			$this->db->where('bookstore.sold <= ', (int)$data['quantity_le']);
		}

		if (!empty($data['quantity_ge'])) {
			$this->db->where('bookstore.sold >= ', (int)$data['quantity_ge']);
		}

		if (!empty($data['location'])) {
			$this->db->where('bookstore.location', $data['location']);
		}

		if (!empty($data['ne_location'])) {
			$this->db->where('bookstore.location!=', $data['ne_location']);
		}

		if (!empty($data['enddate'])) {
			$this->db->where('bookstore.date_added <= ', date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')));
		}

		if (!empty($data['startdate'])) {
			$this->db->where('bookstore.date_added >= ', date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')));
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('bookstore.book_id in (select book_id from event_book where _deleted = 0 and event_id = %s)', (int)$data['event_id']));
		}

		if (isset($data['event_ne'])) {
			$this->db->where('bookstore.book_id not in (select book_id from event_book where _deleted = 0)');
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
			$this->db->join('users', 'users.id = bookstore.user_id', 'left');

			$joined_users = true;
		}

		if (!empty($data['email'])) {
			$this->db->where('users.email', $data['email']);
			$this->db->join('users', 'users.id = bookstore.user_id');

			$joined_users = true;
		}

		if (isset($data['grade'])) {
			$this->db->where('users.grade', (int)$data['grade']);
			!$joined_users && $this->db->join('users', 'users.id = bookstore.user_id', 'left');

			$joined_users = true;
		}

		if (isset($data['section'])) {
			$this->db->where('users.section', $data['section']);
			!$joined_users && $this->db->join('users', 'users.id = bookstore.user_id', 'left');

			$joined_users = true;
		}

		if (isset($data['isbn_country_code'])) {
			$this->db->where(sprintf('bookstore.book_id in (select id from book where isbn_country_code = %s)', $data['isbn_country_code']));
		}

		if (isset($data['has_isbn'])) {
			if ($data['has_isbn'] == 1) {
				$this->db->where('bookstore.book_id in (select id from book where isbn != "")');
			} else {
				$this->db->where('bookstore.book_id in (select id from book where isbn = "")');
			}
		}

		if (isset($data['has_kdp_upload'])) {
			if ($data['has_kdp_upload'] == 1) {
				$this->db->where("bookstore.book_id IN (select amazon_book.book_id from amazon_book)");
			} else {
				$this->db->where("bookstore.book_id NOT IN (select amazon_book.book_id from amazon_book)");
			}
		}

		if (isset($data['has_amazon_url'])) {
			if ($data['has_amazon_url'] == 1) {
				$this->db->where('bookstore.book_id in (select id from book where amazon_url != "")');
			} else {
				$this->db->where('bookstore.book_id in (select id from book where amazon_url = "")');
			}
		}

		if (isset($data['download_title_verso'])) {
			if ($data['download_title_verso'] == 1) {
				$this->db->where("bookstore.book_id IN (select book_title_verso.book_id from book_title_verso)");
			} else {
				$this->db->where("bookstore.book_id NOT IN (select book_title_verso.book_id from book_title_verso)");
			}
		}

		if (isset($data['has_hall_of_fame'])) {
			if ($data['has_hall_of_fame'] == 1) {
				$this->db->where("bookstore.book_id IN (select hall_of_fame_books.book_id from hall_of_fame_books WHERE hall_of_fame_books._deleted=0)");
			} else {
				$this->db->where("bookstore.book_id NOT IN (select hall_of_fame_books.book_id from hall_of_fame_books WHERE hall_of_fame_books._deleted=0)");
			}
		}

		if (isset($data['genre_id'])) {
			$this->db->where('bookstore.genre_id', (int)$data['genre_id']);
		}

		$this->db->where('bookstore._deleted', 0);

		$this->db->from('bookstore');

		if (!empty($data['search'])) {
			$this->db->having('score > ', 0);
		}

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'bookstore.id',
			'bookstore.name',
			'bookstore.author_name',
			'bookstore.status',
			'bookstore.date_added',
			'bookstore.date_modified',
			'bookstore.sold',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'bookstore.id';
		}

		if (!empty($data['search'])) {
			$sort = 'score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		if (!empty($data['search'])) {
			$this->db->order_by('sold', 'DESC');
			$this->db->order_by('views', 'DESC');
			$this->db->order_by('id', 'ASC');
		}

		$results = $this->db->get()->result_array();

		$rows = [];

		foreach ($results as $item) {
			$item 		= self::get($item['id']);
			$item['id'] = $item['book_id'];
			$rows[]		= $item;
		}

		return ['rows' => $rows, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('bookstore', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$book_id = $this->db->insert_id();

		return $book_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('bookstore', $data + [
			'date_modified'	=> date('Y-m-d H:i:s')
		]);
	}

	public function editByBookId($book_id = 0, $data = []) {
		$this->db->where('book_id', (int)$book_id);
		$this->db->update('bookstore', $data + [
			'date_modified'	=> date('Y-m-d H:i:s')
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('bookstore',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function updateViews($book_id = 0) {
		$this->db->set('views', 'views+1', FALSE);
		$this->db->where('book_id', (int)$book_id);
		$this->db->update('bookstore');
	}
}
