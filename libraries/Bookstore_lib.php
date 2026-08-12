<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Bookstore_lib {
	public function __construct() {
		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/Bookstore_model', 'bookstore_model');
		$this->load->model('user/User_model', 'User_model');
		$this->load->model('design/Genre_model', 'genre_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$this->book_model = $this->CI->book_model;
		$this->bookstore_model = $this->CI->bookstore_model;
		$this->user_model = $this->CI->User_model;
		$this->genre_model = $this->CI->genre_model;
		$this->category_model = $this->CI->category_model;
		$this->order_model = $this->CI->order_model;
		$this->order_product_model = $this->CI->order_product_model;
	}

	public function enrolBookstore($book_id = 0) {
		if (empty($book_id)) return;
		if (empty($book_info = $this->book_model->get($book_id))) return;
		if (empty($user_info = $this->user_model->get($book_info['user_id']))) return;
		if (empty($genre_info = $this->genre_model->get($book_info['genre_id']))) return;
		if (empty($category_info = $this->category_model->get($book_info['category_id']))) return;

		$sold = $this->order_model->getTotalProductsByProductId($book_id);

		if (!empty($bookstore_info = $this->bookstore_model->getByBookId($book_id))) {
			$this->bookstore_model->edit($bookstore_info['id'], [
				'name'			=> $book_info['name'],
				'version'		=> $book_info['version'],
				'slug'			=> $book_info['slug'],
				'author_name'	=> $book_info['author_name'],
				'author_image'	=> $book_info['author_image'],
				'cover_image'	=> $book_info['cover_image'],
				'genre_id'		=> $book_info['genre_id'],
				'genre'			=> $genre_info['name'] ?? '',
				'category_id'	=> $book_info['category_id'],
				'category'		=> $category_info['name'] ?? '',
				'sold'			=> $sold ?? 0,
				'views'			=> $book_info['views'] ?? 0,
				'status'		=> $book_info['status']
			]);
		} else {
			$this->bookstore_model->add([
				'book_id'		=> $book_info['id'],
				'user_id'		=> $book_info['user_id'],
				'name'			=> $book_info['name'],
				'version'		=> $book_info['version'],
				'slug'			=> $book_info['slug'],
				'author_name'	=> $book_info['author_name'],
				'author_image'	=> $book_info['author_image'],
				'cover_image'	=> $book_info['cover_image'],
				'genre_id'		=> $book_info['genre_id'],
				'genre'			=> $genre_info['name'] ?? '',
				'category_id'	=> $book_info['category_id'],
				'category'		=> $category_info['name'] ?? '',
				'location'		=> $user_info['location'] ?? '',
				'sold'			=> $sold ?? 0,
				'views'			=> 0,
				'status'		=> $book_info['status'],
				'date_published'=> $book_info['date_published']
			]);
		}
	}

	public function updateBookstoreSold($order_id = 0) {
		log_kb([
			'Bookstore_lib::updateBookstoreSold::order_id:: ' => $order_id
		]);

		if (
			$order_id &&
			!empty($order_info = $this->order_model->get($order_id)) &&
			!empty($order_product_results = $this->order_product_model->getOrderProductByOrderId($order_id))
		) {
			foreach ($order_product_results as $order_product_info) {
				self::enrolBookstore($order_product_info['product_id']);
			}
		}
	}
}
