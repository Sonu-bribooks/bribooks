<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderProduct_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_product_id = 0) {
		$this->db->select('order_product.*,
			state.name AS state
		');

		$this->db->where('order_product.id', (int)$order_product_id);
		$this->db->where('order_product._deleted', 0);

		$this->db->join('state', 'state.id = order_product.state_id', 'left');

		return $this->db->get('order_product')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('order_product.*');

		if (isset($data['order_id'])) {
			$this->db->where('order_product.order_id', (int)$data['order_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('order_product.product_id', (int)$data['book_id']);
		}

		if (isset($data['product_id'])) {
			$this->db->where('order_product.product_id', (int)$data['product_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('order_product.version', (int)$data['version']);
		}

		if (isset($data['order_status'])) {
			$this->db->where(sprintf("order_product.order_id IN (select `order`.id from `order` WHERE `order`.status=%s)", $data['order_status']));
		}

		if (isset($data['total'])) {
			$this->db->where('order_product.total', (double)$data['total']);
		}

		$this->db->from('order_product');

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
			'order_product.price',
			'order_product.quantity',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_product.quantity';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('order_product', $data);

		$order_product_id = $this->db->insert_id();

		return $order_product_id;
	}

	public function edit($order_id = 0, $product_id = 0, $data = []) {
		$this->db->where('order_id', (int)$order_id);
		$this->db->where('product_id', (int)$product_id);
		return $this->db->update('order_product', $data);
	}

	public function getOrderProductQuantity($user_id = '') {
		$this->db->select('users.id AS user_id, users.site_id, site.site_code, order_product.product_id AS book_id , SUM(order_product.quantity) AS quantity');

		if($user_id) {
			$this->db->where('users.id', $user_id);
		}

		$this->db->where('users.source!=', '');
		$this->db->join('book', 'book.id = order_product.product_id');
		$this->db->join('users', 'users.id = book.user_id');
		$this->db->join('site', 'site.id = users.site_id');
		$this->db->group_by('order_product.product_id');
		return $this->db->get('order_product')->result_array();
	}

	public function getOrderProductQuantityBySiteCode($site_code = '', $user_id = '', $book_id = '') {
		$this->db->select('users.id AS user_id, users.site_id, site.site_code, order_product.product_id AS book_id, book.name AS book_name, book.isbn AS book_isbn, SUM(order_product.quantity) AS quantity');

		$this->db->where('users.source!=', '');

		if($user_id) {
			$this->db->where('users.id', $user_id);
		}

		if($book_id) {
			$this->db->where('order_product.product_id', $book_id);
		}

		if($site_code) {
			$this->db->like('site.site_code', $site_code, 'after');
		}

		$this->db->join('book', 'book.id = order_product.product_id');
		$this->db->join('users', 'users.id = book.user_id');
		$this->db->join('site', 'site.id = users.site_id');
		$this->db->group_by('order_product.product_id');
		return $this->db->get('order_product')->result_array();
	}

	public function getOrderProductByOrderId($order_id = 0) {
		$this->db->select('order_product.*');

		$this->db->where('order_product.order_id', (int)$order_id);
		$this->db->where('order_product._deleted', 0);

		return $this->db->get('order_product')->result_array();
	}

	public function getOrderProductQuantityByEventId($event_id = '', $user_id = '', $book_id = '')
	{
		if(empty($event_id) || empty($user_id))
			return;

		$this->load->model('event/Event_model', 'event_model');
		$event_info = $this->event_model->get($event_id);

		if(empty($event_info))
			return;

		$this->db->select('users.id AS user_id, users.site_id, site.site_code, order_product.order_id, order_product.product_id AS book_id, book.name AS book_name, book.isbn AS book_isbn, SUM(order_product.quantity) AS quantity');
		$this->db->where('event.id', $event_id);
		$this->db->where('event_user.user_id', $user_id);
		$this->db->where('event_user._deleted', 0);
		$this->db->where('order._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where('order_product._deleted', 0);
		if($book_id) { $this->db->where('book.id', $book_id); }
		$this->db->where('book._deleted', 0);
		$this->db->where('book.archived', 0);
		$this->db->where_not_in('order.status', [0,91,92]);
		// $this->db->where('book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($event_info['start_date'])). '" and "'. date('Y-m-d H:i:s', strtotime($event_info['end_date'])).'"');
		$this->db->where('book.date_added <= ', date('Y-m-d H:i:s', strtotime($event_info['end_date'])));
		$this->db->from('order_product');
		$this->db->join('order', 'order.id=order_product.order_id', 'left');
		$this->db->join('book', 'book.id=order_product.product_id', 'left');
		$this->db->join('users', 'users.id=book.user_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->join('event_user', 'event_user.user_id=users.id');
		$this->db->join('event', 'event.id=event_user.event_id');
		$this->db->join('event_book', 'event_book.book_id=book.id AND event_book.event_id=event_user.event_id');
		$this->db->group_by('order_product.product_id');
		$result = $this->db->get()->result_array();

		return $result;
	}

	public function getOrderQuantityByEventId($event_id = '')
	{
		if(empty($event_id))
			return;

		$this->load->model('event/Event_model', 'event_model');
		$event_info = $this->event_model->get($event_id);

		if(empty($event_info))
			return;

		$this->db->select('users.id AS user_id, users.site_id, site.site_code, order_product.order_id, order_product.product_id AS book_id, book.name AS book_name, book.isbn AS book_isbn, SUM(order_product.quantity) AS quantity');
		$this->db->where('event.id', $event_id);
		$this->db->where_not_in('order.status', [0,91,92]);
		// $this->db->where('book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($event_info['start_date'])). '" and "'. date('Y-m-d H:i:s', strtotime($event_info['end_date'])).'"');
		$this->db->where('book.date_added <= ', date('Y-m-d H:i:s', strtotime($event_info['end_date'])));
		$this->db->where('event_user._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where('order._deleted', 0);
		$this->db->where('order_product._deleted', 0);
		$this->db->where('book._deleted', 0);
		$this->db->where('book.archived', 0);
		$this->db->from('order_product');
		$this->db->join('order', 'order.id=order_product.order_id', 'left');
		$this->db->join('book', 'book.id=order_product.product_id', 'left');
		$this->db->join('users', 'users.id=book.user_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->join('event_user', 'event_user.user_id=users.id');
		$this->db->join('event', 'event.id=event_user.event_id');
		$this->db->join('event_book', 'event_book.book_id=book.id AND event_book.event_id=event_user.event_id');
		$this->db->group_by('order_product.product_id');
		$this->db->order_by('quantity', 'DESC');
		$result = $this->db->get()->result_array();

		return $result;
	}

	public function getBookIdsByEventIdNotInCertificates($event_id = '') {
		if (empty($event_id)) return;

		$this->load->model('event/Event_model', 'event_model');
		$event_info = $this->event_model->get($event_id);

		if(empty($event_info))
			return;

		$this->db->simple_query('SET SESSION group_concat_max_len=1000000');

		$this->db->select('GROUP_CONCAT(DISTINCT(book_id)) AS book_ids');
		$this->db->from('certificates');
		$this->db->where('event_id', $event_id);
		$this->db->order_by('book_id');
		$book_ids = $this->db->get()->row_array();

		$this->db->select('users.id AS user_id, users.site_id, site.site_code, order_product.order_id, order_product.product_id AS book_id, book.name AS book_name, book.isbn AS book_isbn, SUM(order_product.quantity) AS quantity, book.date_published');
		$this->db->where('event.id', $event_id);
		$this->db->where_not_in('order.status', [0,91,92]);
		// $this->db->where('book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($event_info['start_date'])). '" and "'. date('Y-m-d H:i:s', strtotime($event_info['end_date'])).'"');
		$this->db->where('book.date_added <= ', date('Y-m-d H:i:s', strtotime($event_info['end_date'])));

		if(!empty($book_ids) && !empty($book_ids = $book_ids['book_ids'])) {
			$this->db->where_not_in('order_product.product_id', explode(',', $book_ids));
		}

		$this->db->where('event_user._deleted', 0);
		$this->db->where('order._deleted', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where('order_product._deleted', 0);
		$this->db->where('book._deleted', 0);
		$this->db->where('book.archived', 0);
		$this->db->from('order_product');
		$this->db->join('order', 'order.id=order_product.order_id', 'left');
		$this->db->join('book', 'book.id=order_product.product_id', 'left');
		$this->db->join('users', 'users.id=book.user_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->join('event_user', 'event_user.user_id=book.user_id');
		$this->db->join('event', 'event.id=event_user.event_id');
		$this->db->join('event_book', 'event_book.book_id=book.id AND event_book.event_id=event_user.event_id');
		$this->db->group_by('order_product.product_id');
		$this->db->order_by('quantity', 'DESC');

		$results = $this->db->get()->result_array();

		return $results;
	}

	public function getPurchasedBooks($data = []) {
		$this->db->select('order_product.*');

		if (isset($data['order_id'])) {
			$this->db->where('order_product.order_id', (int)$data['order_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('order.user_id', (int)$data['user_id']);
		}

		if (isset($data['product_id'])) {
			$this->db->where('order_product.product_id', (int)$data['product_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('order_product.version', (int)$data['version']);
		}

		if (isset($data['option_type'])) {
			$this->db->where_in('order_product.option_type', $data['option_type']);
		}

		if (isset($data['order_ne_status'])) {
			$this->db->where_not_in('order.status', $data['order_ne_status']);
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('order_product._deleted', 0);
		$this->db->from('order_product');
		$this->db->join('order', 'order.id=order_product.order_id', 'left');

		$this->db->group_by('order_product.product_id');

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
			'order_product.price',
			'order_product.quantity',
			'order.date_added',
			'order.id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_product.quantity';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);


		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
