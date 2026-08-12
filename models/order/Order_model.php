<?php defined('BASEPATH') or exit('No direct script access allowed');

class Order_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_id = 0) {
		$this->db->select('order.*, IFNULL(payment.date_added, order.date_added) AS date_added');
		$this->db->where('order.id', (int)$order_id);
		$this->db->where('order._deleted', 0);
		$this->db->join('payment', 'payment.order_id = order.id', 'left');

		return $this->db->get('order')->row_array();
	}

	public function getOrderByCode($order_code = '') {
		$this->db->select('order.*, IFNULL(payment.date_added, order.date_added) AS date_added');
		$this->db->where('order.order_code', $order_code);
		$this->db->where('order._deleted', 0);
		$this->db->join('payment', 'payment.order_id = order.id', 'left');

		return $this->db->get('order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			order.*,
			users.first_name,
			users.last_name,
			users.email,
			IFNULL(payment.date_added, order.date_added) AS date_added
		');

		if (!empty($data['order_ids'])) {
			$this->db->where("order.id IN (" . $data['order_ids'] . ")", NULL, false);
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('order.id in (select order_id from event_order where event_id = %s)', (int)$data['event_id']));
		}

		if (isset($data['parent_order_id'])) {
			$this->db->where('order.parent_order_id', (int)$data['parent_order_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('order.site_id', (int)$data['site_id']);
		}

		if (isset($data['order_code'])) {
			$this->db->where('order.order_code', $data['order_code']);
		}

		if (isset($data['coupon_id'])) {
			$this->db->where('order.coupon_id', (int)$data['coupon_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('order.user_id', (int)$data['user_id']);
		}

		if (isset($data['address_id'])) {
			$this->db->where('order.address_id', (int)$data['address_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['ne_currency_id'])) {
			$this->db->where('order.currency_id !=', (int)$data['ne_currency_id']);
		}

		if (isset($data['currency_code'])) {
			$this->db->where('order.currency_code', (int)$data['currency_code']);
		}

		if (isset($data['coupon_id'])) {
			$this->db->where('order.coupon_id', (int)$data['coupon_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('order.provider', $data['provider']);
		}

		if (isset($data['total'])) {
			$this->db->where('order.total', (float)$data['total']);
		}

		if (isset($data['status'])) {
			$this->db->where('order.status', (int)$data['status']);
		}

		if (!empty($data['order_status'])) {
			$this->db->where_in('order.status', $data['order_status']);
		}

		if (isset($data['ne_status'])) {
			if (is_array($data['ne_status'])) {
				$this->db->where_not_in('order.status', $data['ne_status']);
			} elseif ($data['ne_status']) {
				$this->db->where_not_in('order.status', [0, (int)$data['ne_status']]);
			} else {
				$this->db->where('order.status != ', (int)$data['ne_status']);
			}
		}

		if (isset($data['order_type'])) {
			if (is_array($data['order_type'])) {
				$this->db->where_in('order.order_type', $data['order_type']);
			} elseif ($data['order_type']) {
				$this->db->where_in('order.order_type', [(int)$data['order_type']]);
			} else {
				$this->db->where('order.order_type', (int)$data['order_type']);
			}
		}

		if (!empty($data['ne_like_status'])) {
			$this->db->where_not_in('order.status', $data['ne_like_status']);
		}

		if (isset($data['printing_status'])) {
			$this->db->where('order.printing_status', (int)$data['printing_status']);
		}

		if (isset($data['shipping_status'])) {
			$this->db->where('order.shipping_status', (int)$data['shipping_status']);
		}

		if (!empty($data['ne_like_shipping_info'])) {
			$this->db->where("(shipping_info NOT LIKE '%" . $data['ne_like_shipping_info'] . "%')", NULL, FALSE);
		}

		if (!empty($data['ne_like_shipping_tracking_info'])) {
			$this->db->where("(shipping_tracking_info NOT LIKE '%" . $data['ne_like_shipping_tracking_info'] . "%')", NULL, FALSE);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.id', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			// $this->db->like('order.total', $data['search'], 'after');
			// $this->db->like('order.provider', $data['search'], 'after');
			$this->db->or_like('order.order_code', $data['search'], 'after');
			$this->db->group_end();
		}

		if (!empty($data['name'])) {
			$this->db->group_start();
			$this->db->like('users.first_name', $data['name'], 'after');
			$this->db->or_like('users.last_name', $data['name'], 'after');
			$this->db->group_end();
		}

		if (isset($data['email'])) {
			$this->db->where('users.email', $data['email']);
		}

		if (isset($data['mobile'])) {
			$this->db->where('users.mobile', $data['mobile']);
		}

		if (isset($data['ext_transaction_id'])) {
			$this->db->where('order.ext_transaction_id', $data['ext_transaction_id']);
		}

		if (isset($data['awb'])) {
			$this->db->group_start();
			$this->db->like('order.shipping_tracking_info', '"awb_code":"' . $data['awb'] . '"');
			$this->db->or_like('order.shipping_tracking_info', '"awb_code": "' . $data['awb'] . '"');
			$this->db->group_end();
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('order.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		$this->db->where('order._deleted', 0);

		$this->db->join('users', 'users.id = order.user_id', 'left');
		$this->db->join('payment', 'payment.order_id = order.id', 'left');
		$this->db->from('order');

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
			'order.total',
			'order.status',
			'order.date_added',
			'order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order.date_modified';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}

	public function searchProductName($data = []) {
		$joined = false;

		$this->db->select('order.id');

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('order.id in (select order_id from event_order where event_id = %s)', (int)$data['event_id']));
		}

		if (isset($data['parent_order_id'])) {
			$this->db->where('order.parent_order_id', (int)$data['parent_order_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('order.site_id', (int)$data['site_id']);
		}

		if (isset($data['order_code'])) {
			$this->db->where('order.order_code', $data['order_code']);
		}

		if (isset($data['coupon_id'])) {
			$this->db->where('order.coupon_id', (int)$data['coupon_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('order.user_id', (int)$data['user_id']);
		}

		if (isset($data['address_id'])) {
			$this->db->where('order.address_id', (int)$data['address_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['ne_currency_id'])) {
			$this->db->where('order.currency_id !=', (int)$data['ne_currency_id']);
		}

		if (isset($data['currency_code'])) {
			$this->db->where('order.currency_code', (int)$data['currency_code']);
		}

		if (isset($data['coupon_id'])) {
			$this->db->where('order.coupon_id', (int)$data['coupon_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('order.provider', $data['provider']);
		}

		if (isset($data['total'])) {
			$this->db->where('order.total', (float)$data['total']);
		}

		if (isset($data['status'])) {
			$this->db->where('order.status', (int)$data['status']);
		}

		if (isset($data['in_status'])) {
			$data['in_status'] = is_array($data['in_status'])
				? $data['in_status']
				: [(int)$data['in_status']];

			$this->db->where_in('order.status', $data['in_status']);
		}

		if (isset($data['order_type'])) {
			if (is_array($data['order_type'])) {
				$this->db->where_in('order.order_type', $data['order_type']);
			} else {
				$this->db->where('order.order_type', (int)$data['order_type']);
			}
		}

		if (isset($data['pickup_location_id'])) {
			if (is_array($data['pickup_location_id'])) {
				$this->db->where_in('order.pickup_location_id', $data['pickup_location_id']);
			} else {
				$this->db->where('order.pickup_location_id', (int)$data['pickup_location_id']);
			}
		}

		if (isset($data['printing_status'])) {
			$this->db->where('order.printing_status', (int)$data['printing_status']);
		}

		if (isset($data['assign_printer_id'])) {
			$this->db->where('order.assign_printer_id', (int)$data['assign_printer_id']);
		}

		if (isset($data['ne_status'])) {
			if (is_array($data['ne_status'])) {
				$this->db->where_not_in('order.status', $data['ne_status']);
			} elseif ($data['ne_status']) {
				$this->db->where_not_in('order.status', [0, (int)$data['ne_status']]);
			} else {
				$this->db->where('order.status != ', (int)$data['ne_status']);
			}
		}

		if (isset($data['shipping_status'])) {
			$this->db->where('order.shipping_status', (int)$data['shipping_status']);
		}

		if (isset($data['ext_transaction_id'])) {
			$this->db->where('order.ext_transaction_id', $data['ext_transaction_id']);
		}

		if (!empty($data['customer_info'])) {
			$joined = true;
			$this->db->group_start();
			$this->db->join('users', 'users.id = order.user_id', 'left');
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['customer_info'], 'after');
			$this->db->or_like('users.email', $data['customer_info'], 'after');
			$this->db->or_like('users.mobile', $data['customer_info'], 'after');
			$this->db->or_like('users.slug', $data['customer_info'], 'after');
			$this->db->or_like('users.location', $data['customer_info'], 'after');
			$this->db->or_like('users.source', $data['customer_info'], 'after');
			$this->db->group_end();
		}

		if (!empty($data['search'])) {
			$joined = true;
			$this->db->group_start();
			$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
			$this->db->join('book_version as book', 'book.book_id = order_product.product_id AND book.version = order_product.version', 'left');
			$this->db->join('users', 'users.id = book.user_id', 'left');
			$this->db->join('shipment', 'shipment.order_id = order.id', 'left');

			$this->db->like('order.order_code', $data['search'], 'after');
			$this->db->or_like('order.ext_transaction_id', $data['search'], 'after');
			$this->db->or_like('book.book_id', $data['search'], 'after');
			$this->db->or_like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('book.slug', $data['search'], 'after');
			$this->db->or_like('book.isbn', $data['search'], 'after');
			$this->db->or_like('book.unique_id', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.slug', $data['search'], 'after');
			$this->db->or_like('users.location', $data['search'], 'after');
			$this->db->or_like('users.source', $data['search'], 'after');
			$this->db->or_like('order.shipping_info', $data['search'], 'after');
			$this->db->or_like('order.shipping_tracking_info', $data['search'], 'after');
			$this->db->or_like('shipment.awb_number', $data['search'], 'after');
			$this->db->group_end();
		}

		if (!empty($data['book_slug']) || !empty($data['book_isbn']) || !empty($data['book_id']) || isset($data['has_isbn']) || isset($data['has_amazon_url'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
				$this->db->join('book_version as book', 'book.book_id = order_product.product_id AND book.version = order_product.version', 'left');
			}

			$joined = true;

			!empty($data['book_slug']) && $this->db->where('book.slug', $data['book_slug']);
			!empty($data['book_isbn']) && $this->db->where('book.isbn', $data['book_isbn']);
			!empty($data['book_id']) && $this->db->where('book.book_id', (int)$data['book_id']);
			!empty($data['version']) && $this->db->where('order_product.version', (int)$data['version']);
			!empty($data['option']) && $this->db->like('order_product.option', $data['option']);

			if (isset($data['has_isbn'])) {
				if ($data['has_isbn'] == '1') {
					$this->db->where('book.isbn !=', "");
				} else {
					$this->db->where('book.isbn =', "");
				}
			}

			if (isset($data['has_amazon_url'])) {
				if ($data['has_amazon_url'] == '1') {
					$this->db->where('book.amazon_url !=', "");
				} else {
					$this->db->where('book.amazon_url =', "");
				}
			}
		}

		if (!empty($data['option_type'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id');
			}

			$joined = true;

			$this->db->where_in('order_product.option_type', $data['option_type']);
		}

		if (!empty($data['ne_option_type'])) {
			$this->db->where("order.id NOT IN (select order_id from order_product where order_id = order.id AND order_product.option_type IN ('" . implode("','", $data['ne_option_type']) . "'))");
		}

		if (!empty($data['quantity_ge']) || !empty($data['quantity_le'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
			}

			$joined = true;

			!empty($data['quantity_ge']) && $this->db->where('order_product.quantity >= ', $data['quantity_ge']);
			!empty($data['quantity_le']) && $this->db->where('order_product.quantity <= ', $data['quantity_le']);
		}

		if (!empty($data['mobile']) || !empty($data['name']) || !empty($data['email']) || !empty($data['site_code'])) {
			$this->db->join('users', 'users.id = order.user_id', 'left');
			$this->db->join('site', 'site.id = users.site_id', 'left');

			if (!empty($data['name'])) {
				$this->db->like('CONCAT(users.first_name, " " , users.last_name)', $data['name'], 'after');
			}

			if (!empty($data['email'])) {
				$this->db->where('users.email', $data['email']);
			}

			if (!empty($data['mobile'])) {
				$this->db->where('users.mobile', $data['mobile']);
			}

			if (!empty($data['site_code'])) {
				$this->db->like('site.site_code', $data['site_code'], 'after');
			}
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('order.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (isset($data['stock_status'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
			}

			$this->db->join('book_stock', 'book_stock.book_id = order_product.product_id AND book_stock.version = order_product.version AND book_stock._deleted = 0', 'left');

			$joined = true;

			if ($data['stock_status'] == '1') {
				$this->db->where('book_stock.quantity > ', 0);
			} else {
				$this->db->where('book_stock.quantity =', 0);
			}
		}

		if (!empty($data['assignment_code'])) {
			$this->db->where("order.id IN (select printer_assign_logs.order_id from printer_assign_logs join printer_assignment on (printer_assignment.id =  printer_assign_logs.assignment_id AND printer_assign_logs._deleted = 0) where printer_assignment.code = '" . $data['assignment_code'] . "')");
		}

		if (!empty($data['order_country'])) {
			if (strtolower($data['order_country']) == 'usa') {
				$data['order_country'] = 'United States';
			} elseif (strtolower($data['order_country']) == 'uae') {
				$data['order_country'] = 'United Arab Emirates';
			}

			$this->db->where("order.address_id IN (select address.id from address where address.country = '" . $data['order_country'] . "')");
		}

		if (!empty($data['order_state'])) {
			$this->db->where("order.address_id IN (select address.id from address where address.state IN ('" . implode("','", explode(",", $data['order_state'])) . "'))");
		}

		if (!empty($data['page_count_ge'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
			}

			$joined = true;

			$this->db->where('order_product.product_id IN (
				select page_version.book_id from page_version
				where page_version._deleted = 0
				and page_version.book_id = order_product.product_id
				and page_version.version = order_product.version
				group by page_version.book_id
				having (count(page_version.id) * 2 + 1) >= ' . (int)$data['page_count_ge'] . '
			)');

			$this->db->where('order.id NOT IN (select op2.order_id from order_product op2 where op2.order_id = order.id and op2.product_id IN (
				select page_version.book_id from page_version
				where page_version._deleted = 0
				and page_version.book_id = op2.product_id
				and page_version.version = op2.version
				group by page_version.book_id
				having (count(page_version.id) * 2 + 1) < ' . (int)$data['page_count_ge'] . '
			))');
		}

		if (!empty($data['page_count_le'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
			}

			$joined = true;

			$this->db->where('order_product.product_id IN (
				select page_version.book_id from page_version
				where page_version._deleted = 0
				and page_version.book_id = order_product.product_id
				and page_version.version = order_product.version
				group by page_version.book_id
				having (count(page_version.id) * 2 + 1) <= ' . (int)$data['page_count_le'] . '
			)');

			$this->db->where('order.id NOT IN (select op2.order_id from order_product op2 where op2.order_id = order.id and op2.product_id IN (
				select page_version.book_id from page_version
				where page_version._deleted = 0
				and page_version.book_id = op2.product_id
				and page_version.version = op2.version
				group by page_version.book_id
				having (count(page_version.id) * 2 + 1) > ' . (int)$data['page_count_le'] . '
			))');
		}

		if (isset($data['is_dropshipper'])) {
			$this->db->where('order.pickup_location_id >', $this->config->item('default_pickup_location_id'));
		}

		$this->db->where('order._deleted', 0);
		$this->db->from('order');

		if ($joined) {
			$this->db->group_by('order.id');
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
		}

		$sort_data = [
			'order.total',
			'order.status',
			'order.date_added',
			'order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$row = array_map(function($item) {
			return self::get($item['id']);
		}, $this->db->get()->result_array());

		return ['rows' => $row, 'total' => $total];
	}

	public function add($data) {
		$this->db->insert('order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('order_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		if (!empty($order_info = self::get($id)) && !in_array($order_info['status'], [91, 92, 93, 94])) {
			if (!empty($data['status']) && in_array($data['status'], [1, 2, 8]) && in_array($order_info['status'], [21])) {
				return;
			}

			return $this->db->update('order', $data + [
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$id
			]);
		}

		return;
	}

	public function delete($order_id = 0) {
		$this->db->where('id', (int)$order_id);
		$this->db->update('order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function addProduct($data = []) {
		$this->db->insert('order_product', $data);

		$id = $this->db->insert_id();

		return $id;
	}

	public function getProducts($order_id = 0, $data = []) {
		$this->db->select('
			order_product.*,
			book.name AS name,
			book.user_id AS user_id,
			book.cover_image AS cover_image,
			book.author_name AS author_name,
			book.slug AS slug,
			book.unique_id,
			book.isbn
		');
		$this->db->join('book_version as book', 'book.book_id = order_product.product_id AND book.version = order_product.version', 'left');

		if (!empty($data['option_type'])) {
			$this->db->where_in('order_product.option_type', $data['option_type']);
		}

		if (!empty($data['ne_option_type'])) {
			$this->db->where("order_product.order_id NOT IN (select order_id from order_product where order_id = '$order_id' AND order_product.option_type IN ('" . implode("','", $data['ne_option_type']) . "'))");
		}

		return $this->db->get_where('order_product', [
			'order_id'					=> (int)$order_id,
			'order_product._deleted'	=> 0,
			'book._deleted'				=> 0,
		])->result_array();
	}

	public function getTopSoldBooks($data = []) {
		if (!empty($data['end_date'])) {
			$where_clause = $this->db
				->select('id')
				->where('date_added < ', date('Y-m-d H:i:s', strtotime($data['end_date'])))
				->from('order')
				->get_compiled_select();
		} else {
			$where_clause = $this->db
				->select('id')
				->where('status != ', 0)
				->where('_deleted', 0)
				->from('order')
				->get_compiled_select();
		}

		$this->db->select('book.*');
		$this->db->select_sum('order_product.quantity');

		if (!empty($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (!empty($data['product_id'])) {
			$this->db->where('order_product.product_id', (int)$data['product_id']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('users.state_id', (int)$data['state_id']);
		}

		if (!empty($data['city_id'])) {
			$this->db->where('users.city_id', (int)$data['city_id']);
		}

		if (!empty($data['grade_id'])) {
			$this->db->where('users.grade_id', (int)$data['grade_id']);
		}

		if (!empty($data['section_id'])) {
			$this->db->where('users.section_id', (int)$data['section_id']);
		}

		if (!empty($data['grade'])) {
			$this->db->where('site_grade.name', (int)$data['grade']);
		}

		if (!empty($data['site_code'])) {
			$this->db->like('site.site_code', $data['site_code'], 'after');
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search']);
			// $this->db->or_like('site.site_code', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search']);
			$this->db->group_end();
		}

		// if (!empty($data['end_date'])) {
		// 	$this->db->where("(`order_product`.`order_id` IN ($where_clause) || order_product.quantity IS NULL)", NULL, FALSE);
		// }

		$this->db->where('book.status', 1);
		$this->db->where('book._deleted', 0);

		$this->db->join('users', 'users.id = book.user_id', 'left');

		if (!empty($data['grade'])) {
			$this->db->join('site_grade', 'site_grade.id = users.grade_id', 'left');
		}

		$this->db->join('site', 'site.id = users.site_id', 'left');

		if (!empty($data['end_date'])) {
			$this->db->join('order_product', "order_product.product_id = book.id AND (`order_product`.`order_id` IN ($where_clause) || order_product.quantity IS NULL)", 'left');
		} else {
			$this->db->join('order_product', "order_product.product_id = book.id AND (`order_product`.`order_id` IN ($where_clause) || order_product.quantity IS NULL)", 'left');
		}

		$this->db->from('book');

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

		$this->db->group_by('book.id');
		$this->db->order_by('quantity DESC, book.views DESC');

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function getAuthorProducts($data = []) {
		$this->db->select_sum('order_product.quantity');
		$this->db->join('order', 'order.id = order_product.order_id', 'left');

		if (!empty($data['user_id'])) {
			$this->db->where('order.user_id', (int)$data['user_id']);
		}

		if (!empty($data['product_id'])) {
			$this->db->where('order_product.product_id', (int)$data['product_id']);
		}

		if (!empty($data['version'])) {
			$this->db->where('order_product.version', (int)$data['version']);
		}

		if (!empty($data['option_type'])) {
			$this->db->where('order_product.option_type', (int)$data['option_type']);
		}

		if (!empty($data['option'])) {
			$this->db->like('order_product.option', $data['option']);
		}

		// $this->db->where('order.status != ', 0);
		$this->db->where('order.parent_order_id', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);

		return $this->db->get('order_product')->row()->quantity;
	}

	public function getTotalProductsByProductId($product_id = 0) {
		$this->db->select_sum('order_product.quantity');

		$this->db->join('order', 'order.id = order_product.order_id', 'left');
		$this->db->join('book', 'book.id = order_product.product_id', 'left');

		$this->db->where('order.parent_order_id', 0);
		$this->db->where_not_in('order.status', [0, 91, 92]);
		$this->db->where('book.archived', 0);
		$this->db->where('book._deleted', 0);

		return $this->db->get_where('order_product', [
			'order_product.product_id'	=> (int)$product_id,
			'order._deleted'		=> 0,
		])->row()->quantity;
	}

	public function updatePaymentLinkStatus($code) {
		$this->db->update('payment_link', [
			'status'	=> 1,
		], [
			'code'		=> $code
		]);
	}

	public function generateOrderId($code = '', $amount = 0, $currency_code = '') {
		switch ($this->config->item('site_payment_gateway')) {
			case 'phonepe':
				return 'PH-' . $code;
			case 'stripe':
				return self::_generateStripeOrder($code, $amount, $currency_code);
			default:
				return self::_generateRazorpayOrder($code, $amount, $currency_code);
		}
	}

	private function _generateRazorpayOrder($code = '', $amount = 0, $currency_code = '') {
		try {

			$data = [
				'amount'			=> round($amount * 100),
				'currency'			=> $currency_code
					? $currency_code
					: $this->config->item('site_currency_code'),
				'receipt'			=> 'rcptid_' . $code,
				'payment_capture'	=> 1,
			];

			$ch = curl_init();

			$options = [
				CURLOPT_URL				=> 'https://api.razorpay.com/v1/orders',
				CURLOPT_POSTFIELDS		=> json_encode($data),
				CURLOPT_CUSTOMREQUEST	=> 'POST',
				CURLOPT_HTTPHEADER		=> ['Content-Type:application/json', 'Authorization: Basic ' . base64_encode(RAZORPAY_KEY . ':' . RAZORPAY_SECRET)],
				CURLOPT_HEADER		 	=> 0,
				CURLOPT_SSL_VERIFYPEER 	=> 0,
				CURLOPT_RETURNTRANSFER 	=> 1,
				CURLOPT_FOLLOWLOCATION 	=> 1,
				CURLOPT_FORBID_REUSE   	=> 1,
				CURLOPT_FRESH_CONNECT  	=> 1,
				CURLOPT_CONNECTTIMEOUT 	=> 10,
				CURLOPT_TIMEOUT			=> 20
			];

			curl_setopt_array($ch, $options);

			$response = curl_exec($ch);
			$response = json_decode($response, true);

			log_kb(['RazorPay::' => $response]);

			curl_close($ch);

			return $response['id'] ?? null;
		} catch (Exception $e) {
			log_kb(['Stripe::Error::' => $e->getMessage()]);
		}
	}

	private function _generateStripeOrder($code = '', $amount = 0, $currency_code = '') {
		try {
			$amount = (( $currency_code ? $currency_code : $this->config->item('site_currency_code')) === 'KWD') ? round($amount * 100).'0' : round($amount * 100);

			\Stripe\Stripe::setApiKey((get_settings('payment_provider') == 'stripe_sg') ? STRIPE_SECRET_SG : STRIPE_SECRET);
			$result = \Stripe\PaymentIntent::create([
				'amount' 					=> $amount,
				'currency' 					=> $currency_code
					? $currency_code
					: $this->config->item('site_currency_code'),
				'automatic_payment_methods' => [
					'enabled' => true,
				],
			]);

			log_kb(['Stripe::' => $result]);
			return $result->client_secret;
		} catch (Exception $e) {
			log_kb(['Stripe::Error::' => $e->getMessage()]);
		}
	}

	public function verifyOrder($data = []) {
		if ($this->config->item('site_payment_gateway') === 'stripe') {
			return self::verifyStripeOrder($data);
		}

		if ($this->config->item('site_payment_gateway') == 'phonepe') {
			return self::_verifyPhonepeOrder($data);
		}

		$payload = $data['order_id'] . '|' . $data['payment_id'];

		$expected_signature = hash_hmac('sha256', $payload, RAZORPAY_SECRET);

		return hash_equals($expected_signature, $data['signature']);
	}

	public function verifyStripeOrder($data = []) {
		try {
			log_kb(['Stripe Data::' => $data]);
			$stripe = new \Stripe\StripeClient(($data['order_info']['provider'] == 'stripe_sg') ? STRIPE_SECRET_SG : STRIPE_SECRET);
			$result = $stripe->paymentIntents->retrieve($data['data']['id'] ?? '');

			$amount = ($this->config->item('site_currency_code') === 'KWD') ? round($data['order_info']['amount'] * 100).'0' : round($data['order_info']['amount'] * 100);

			log_kb(['verifyStripeOrder::' => $result]);

			return $result->amount_received == $amount;
		} catch (Exception $e) {
			log_kb(['verifyStripeOrder::Error::' => $e->getMessage()]);
		}
	}

	private function _verifyPhonepeOrder($data = []) {
		try {
			if (empty($data)) return false;

			$merchant_id 				= PHONEPE_MID;
			$merchant_transaction_id 	= $data['data']['merchantTransactionId'];

			$salt_key 	= PHONEPE_SALT_KEY;
			$salt_index = PHONEPE_SALT_INDEX;
			$path 		= '/pg/v1/status/' . $merchant_id . '/' . $merchant_transaction_id;
			$verify_hash= hash('sha256', $path . $salt_key) . '###' . $salt_index;

			$ch = curl_init();

			curl_setopt_array($ch, [
				CURLOPT_URL 			=> PHONEPE_URL . $path,
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_ENCODING 		=> '',
				CURLOPT_MAXREDIRS 		=> 10,
				CURLOPT_TIMEOUT 		=> 30,
				CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST 	=> 'GET',
				CURLOPT_HTTPHEADER 		=> [
					'Content-Type: application/json',
					'X-VERIFY:' . $verify_hash,
					'X-MERCHANT-ID:' . $merchant_id
				],
			]);

			$response 	= curl_exec($ch);
			$err 		= curl_error($ch);

			curl_close($ch);

			log_kb(['verifyPhonepeOrder::' => $response]);

			if ($err) {
				log_kb(['Phonepe::curl_error' => $err]);
				return false;
			} else {
				$response = json_decode($response);

				if ($response->success == true && strtolower($response->data->state) == 'completed') {
					return $response;
				}

				return false;
			}
		} catch (Exception $e) {
			log_kb(['Phonepe::exception_error' => $e->getMessage()]);
			return false;
		}
	}

	public function orderedInReview($data = []) {
		$this->db->select('order.id as order_id, order.status as order_status, order.user_id, op.order_id as op_order_id, op.product_id as op_product_id, book.name as book_name, book.status, book.id as book_id, users.id as user_id, users.first_name, users.username, users.last_name, users.email, book.*');

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('order.id in (select order_id from event_order where event_id = %s)', (int)$data['event_id']));
		}

		if (isset($data['status'])) {
			$this->db->where('order.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			// $this->db->like('order.total', $data['search'], 'after');
			// $this->db->like('order.provider', $data['search'], 'after');
			$this->db->like('book.name', $data['search']);
			// $this->db->like('book.author_name', $data['search']);
		}

		if (!empty($data['name'])) {
			$this->db->like('users.first_name', $data['name'], 'after');
			$this->db->or_like('users.last_name', $data['name'], 'after');
		}

		if (isset($data['email'])) {
			$this->db->where('users.email', $data['email']);
		}

		if (isset($data['isbn'])) {
			if ($data['isbn'] == "filled") {
				$this->db->where('book.isbn !=', "");
			} else {
				$this->db->where('book.isbn =', "");
			}
		}

		if (isset($data['mobile'])) {
			$this->db->where('users.mobile', $data['mobile']);
		}

		$this->db->where('order._deleted', 0);

		$this->db->where('book.status', '2');
		$this->db->where('order.status', '1');
		$this->db->from('order as order');
		$this->db->join('order_product op', 'op.order_id = order.id', 'left');
		$this->db->join('book', 'book.id = op.product_id', 'left');
		$this->db->join('users', 'users.id = order.user_id', 'left');

		$total = $this->db->count_all_results('', FALSE);

		$this->db->group_by('order.id');
		$this->db->group_by('op_product_id');
		$this->db->group_by('op_order_id');

		$sort_data = [
			'order.total',
			'order.status',
			'order.date_added',
			'order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);
		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];

		// if (isset($data['startdate']) || isset($data['enddate'])) {
		// 	$this->db->where('order.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		// }
	}

	public function refundRazorpayOrder($order_id = '') {
		if (empty($order_id)) return;

		$this->load->model('order/OrderRefund_model', 'order_refund_model');

		$order_refund_info = $this->order_refund_model->getByOrderId($order_id);

		if (!empty($order_refund_info) && $order_refund_info['status'] == '1') return true;

		$order_info = $this->order_model->get($order_id);

		if (empty($order_info)) return;

		$payment_id = $order_info['ext_transaction_id'];
		$amount = round($order_info['total'] * 100);

		$url = "https://api.razorpay.com/v1/payments/$payment_id/refund";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['amount' => $amount]));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization: Basic ' . base64_encode(RAZORPAY_KEY . ':' . RAZORPAY_SECRET)]);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$api_response = curl_exec($ch);

		log_kb(['RazorPay::Refund::' => $api_response]);

		if (empty($api_response)) return;

		$response = json_decode($api_response, true);

		if (!empty($response['status']) && in_array(strtolower($response['status']), ['pending', 'processed'])) {
			$status = 1;
		} elseif (!empty($response['error']['description']) && (strtolower($response['error']['description']) == 'the total refund amount is greater than the refund payment amount')) {
			$status = 1;
		} else {
			$status = 0;
		}

		if (empty($order_refund_info)) {
			$this->order_refund_model->add([
				'order_id'				=> $order_id,
				'provider'				=> 'razorpay',
				'ext_transaction_id'	=> $payment_id,
				'api_response'			=> $api_response,
				'status'				=> $status
			]);
		} else {
			$this->order_refund_model->edit($order_refund_info['id'], [
				'api_response'			=> $api_response,
				'status'				=> $status
			]);
		}

		curl_close($ch);
		return $status;
	}

	public function refundStripeOrder($order_id) {
		if (empty($order_id)) return;

		$this->load->model('order/OrderRefund_model', 'order_refund_model');

		$order_refund_info = $this->order_refund_model->getByOrderId($order_id);

		if (!empty($order_refund_info) && $order_refund_info['status'] == '1') return;

		$order_info = $this->order_model->get($order_id);

		if (empty($order_info)) return;

		$payment_id = $order_info['ext_transaction_id'];
		$amount = round($order_info['total'] * 100);

		try {
			\Stripe\Stripe::setApiKey(($order_info['provider'] == 'stripe_sg') ? STRIPE_SECRET_SG : STRIPE_SECRET);
			$api_response = \Stripe\Refund::create([
				'payment_intent'=> $payment_id,
				'amount'		=> $amount
			]);

			log_kb(['Stripe::Refund::' => $api_response]);

			if (empty($api_response)) return;

			if (!empty($api_response->status) && (strtolower($api_response->status) == 'succeeded')) {
				$status = 1;
			} elseif (!empty($api_response->error->code) && (strtolower($api_response->error->code) == 'charge_already_refunded')) {
				$status = 1;
			} else {
				$status = 0;
			}

			if (empty($order_refund_info)) {
				$this->order_refund_model->add([
					'order_id'				=> $order_id,
					'provider'				=> 'stripe',
					'ext_transaction_id'	=> $payment_id,
					'api_response'			=> json_encode($api_response),
					'status'				=> $status
				]);
			} else {
				$this->order_refund_model->edit($order_refund_info['id'], [
					'api_response'			=> json_encode($api_response),
					'status'				=> $status
				]);
			}

			return $status;
		} catch (Exception $e) {
			log_kb(['Stripe::Refund::Error::' => $e->getMessage()]);

			if (strpos(strtolower($e->getMessage()), 'has been charged back; cannot issue a refund.') !== false) {
				$status = 1;
			} else {
				$status = 0;
			}

			if (empty($order_refund_info)) {
				$this->order_refund_model->add([
					'order_id'				=> $order_id,
					'provider'				=> 'stripe',
					'ext_transaction_id'	=> $payment_id,
					'api_response'			=> json_encode($e->getMessage()),
					'status'				=> $status
				]);
			} else {
				$this->order_refund_model->edit($order_refund_info['id'], [
					'api_response'			=> json_encode($e->getMessage()),
					'status'				=> $status
				]);
			}

			return;
		}
	}

	public function editById($id = 0, $data = []) {
		return $this->db->update('order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}
}
