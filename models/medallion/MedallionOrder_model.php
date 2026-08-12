<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MedallionOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			medallion_order.*,
			medallion.name as medallion_name,
			book.name as book_name,
			users.first_name,
			users.last_name,
			users.email,
			users.mobile,
			CASE
				WHEN (book.name IS NULL OR book.name = "") 
					THEN CONCAT(medallion.name, "-", site.name)
				ELSE CONCAT(medallion.name, "-", book.name)
			END AS name,
			medallion.id as product_id,
			site.name as school_name
		', false);

		$this->db->where('medallion_order.id', (int)$id);
		$this->db->where('medallion_order._deleted', 0);

		$this->db->join('medallion', 'medallion.id = medallion_order.medallion_id', 'left');
		$this->db->join('users', 'users.id = medallion_order.user_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->join('book', 'book.id = medallion_order.book_id', 'left');

		return $this->db->get('medallion_order')->row_array();
	}

	public function getByCode($order_code = '') {
		$this->db->select('
			medallion_order.*,
			medallion.name as medallion_name,
			book.name as book_name,
			users.first_name,
			users.last_name,
			users.email,
			users.mobile,
			CASE
				WHEN (book.name IS NULL OR book.name = "") 
					THEN CONCAT(medallion.name, "-", site.name)
				ELSE CONCAT(medallion.name, "-", book.name)
			END AS name,
			medallion.id as product_id,
			site.name as school_name
		', false);

		$this->db->where('medallion_order.order_code', $order_code);
		$this->db->where('medallion_order._deleted', 0);

		$this->db->join('medallion', 'medallion.id = medallion_order.medallion_id', 'left');
		$this->db->join('users', 'users.id = medallion_order.user_id', 'left');
		$this->db->join('book', 'book.id = medallion_order.book_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');

		return $this->db->get('medallion_order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			medallion_order.*,
			medallion.name as medallion_name,
			book.name as book_name,
			users.first_name,
			users.last_name,
			users.email,
			users.mobile,
			CASE
				WHEN (book.name IS NULL OR book.name = "") 
					THEN CONCAT(medallion.name, "-", site.name)
				ELSE CONCAT(medallion.name, "-", book.name)
			END AS name,
			medallion.id as product_id,
			site.name as school_name
		');

		if (isset($data['type'])) {
			$this->db->where('medallion_order.type', $data['type']);
		}

		if (isset($data['parent_id'])) {
			$this->db->where('medallion_order.parent_id', (int)$data['parent_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('medallion_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('medallion_order.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('medallion_order.book_id', (int)$data['book_id']);
		}

		if (isset($data['medallion_id'])) {
			$this->db->where('medallion_order.medallion_id', (int)$data['medallion_id']);
		}

		if (isset($data['address_id'])) {
			$this->db->where('medallion_order.address_id', (int)$data['address_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('medallion_order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['pickup_location_id'])) {
			$this->db->where('medallion_order.pickup_location_id', (int)$data['pickup_location_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('medallion_order.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			if (is_array($data['ne_status'])) {
				$this->db->where_not_in('medallion_order.status', $data['ne_status']);
			} elseif ($data['ne_status']) {
				$this->db->where_not_in('medallion_order.status', [0, (int)$data['ne_status']]);
			} else {
				$this->db->where('medallion_order.status!=', (int)$data['ne_status']);
			}
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('medallion_order.date_added BETWEEN "' . date('Y-m-d', strtotime($data['startdate'])) . '" and "' . date('Y-m-d 23:59:59', strtotime($data['enddate'])) . '"');
		}

		if (isset($data['shipping_status'])) {
			$this->db->where('medallion_order.shipping_status', (int)$data['shipping_status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('medallion.name', $data['search'], 'after');
			$this->db->or_like('book.name', $data['search'], 'after');
			$this->db->or_like('book.id', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.id', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('medallion_order.order_code', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('medallion_order._deleted', 0);

		$this->db->join('medallion', 'medallion.id = medallion_order.medallion_id', 'left');
		$this->db->join('users', 'users.id = medallion_order.user_id', 'left');
		$this->db->join('book', 'book.id = medallion_order.book_id', 'left');
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->from('medallion_order');

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
			'medallion_order.date_added',
			'medallion_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'medallion_order.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('medallion_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('medallion_order_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('medallion_order_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion_order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getProducts($order_id = 0) {
		$order_info = self::get($order_id);
		$child_orders = self::get_all(['parent_id' => (int)$order_id])['rows'] ?? [];
		array_unshift($child_orders, $order_info);

		return $child_orders;
	}

	public function getDetailByWhere($where = []) {
		$this->db->select('medallion_order.*');

		$this->db->where($where);
		$this->db->where('medallion_order._deleted', 0);
		return $this->db->get('medallion_order')->row_array();
	}

	public function editById($id = 0, $data = []) {
		return $this->db->update('medallion_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}
}
