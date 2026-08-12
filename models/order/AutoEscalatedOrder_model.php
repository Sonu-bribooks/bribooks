<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AutoEscalatedOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($auto_escalate_order_id = 0) {
		$this->db->select('auto_escalated_orders.*');
		
		$this->db->where('auto_escalated_orders.id', (int)$auto_escalate_order_id);
		$this->db->where('auto_escalated_orders._deleted', 0);
		return $this->db->get('auto_escalated_orders')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('order.*, auto_escalated_orders.status as escalated_status, auto_escalated_orders.manager_id, auto_escalated_orders.date_closed, auto_escalated_orders.comment, auto_escalated_orders.id as autoEscalateOrderId'); 

		if (isset($data['currency_id'])) {
			$this->db->where('order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['ne_currency_id'])) {
			$this->db->where('order.currency_id !=', (int)$data['ne_currency_id']);
		}
		if (isset($data['status'])) {
			$this->db->where('auto_escalated_orders.status', (int)$data['status']);
		}
		
		if (isset($data['order_status'])) {
			$this->db->where('order.status !=', (int)$data['order_status']);
		}

		if (isset($data['order_type'])) {
			if (is_array($data['order_type'])) {
				$this->db->where_in('order.order_type', $data['order_type']);
			} else {
				$this->db->where('order.order_type', (int)$data['order_type']);
			}
		}

		$this->db->join('order', 'auto_escalated_orders.order_id = order.id', 'inner');

		$joined = false;

		if (!empty($data['search'])) {
			$joined = true;
			$this->db->group_start();
			$this->db->join('order_product', 'order_product.order_id = order.id', 'left');
			$this->db->join('book_version as book', 'book.book_id = order_product.product_id AND book.version = order_product.version', 'left');
			$this->db->join('users', 'users.id = book.user_id', 'left');
			$this->db->like('order.order_code', $data['search'], 'after');
			$this->db->or_like('order.ext_transaction_id', $data['search'], 'after');
			$this->db->or_like('book.book_id', $data['search'], 'after');
			$this->db->or_like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('book.slug', $data['search'], 'after');
			$this->db->or_like('book.isbn', $data['search'], 'after');
			$this->db->or_like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.slug', $data['search'], 'after');
			$this->db->or_like('users.location', $data['search'], 'after');
			$this->db->or_like('users.source', $data['search'], 'after');
			$this->db->or_like('order.shipping_info', $data['search'], 'after');
			$this->db->or_like('order.shipping_tracking_info', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('auto_escalated_orders._deleted', 0);
		$this->db->where('order.status !=', 91);

		$this->db->from('auto_escalated_orders');

		if (!empty($data['ne_option_type'])) {
			$this->db->where("order.id NOT IN (select order_id from order_product where order_id=order.id AND order_product.option_type IN ('" . implode("','", $data['ne_option_type']) . "'))");
		}
		
		if (!empty($data['option_type'])) {
			if (!$joined) {
				$this->db->join('order_product', 'order_product.order_id = order.id');
			}

			$joined = true;

			$this->db->where_in('order_product.option_type', $data['option_type']);
		}

		$this->db->group_by('order.id');

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
			'auto_escalated_orders.date_added',
			'auto_escalated_orders.date_modified',
		];
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'auto_escalated_orders.date_added';
		}
	
		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}
	
		$this->db->order_by($sort, $order);

		$result = $this->db->get()->result_array();

		return ['rows' => $result, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('auto_escalated_orders', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$auto_escalate_order_id = $this->db->insert_id();

		return $auto_escalate_order_id;
	}

	public function edit($auto_escalate_order_id, $data = []) {
		$this->db->where('id', $auto_escalate_order_id);
	
		$this->db->update('auto_escalated_orders', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
	public function delete($auto_escalate_order_id = 0) {
		$this->db->where('id', $auto_escalate_order_id);
		$this->db->update('auto_escalated_orders',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
	
	public function getForAutoEscalateOrder($data = []) {
		$this->db->select('id as order_id');
		$this->db->from('order');
		$this->db->where_in('status', $data['status']);
	
		$current_datetime = date('Y-m-d H:i:s');
	
		$this->db->where('date_added <=', 'DATE_SUB("' . $current_datetime . '", INTERVAL ' . (int)$data['days'] . ' DAY)', false);
	
		$this->db->where('_deleted', 0);
	
		if (isset($data['currency_id'])) {
			$this->db->where('order.currency_id', (int)$data['currency_id']);
		}
	
		if (isset($data['ne_currency_id'])) {
			$this->db->where('order.currency_id !=', (int)$data['ne_currency_id']);
		}
		
		$this->db->where('id NOT IN (SELECT order_id FROM auto_escalated_orders WHERE _deleted = 0)', null, false);
	
		return $this->db->order_by('id', 'DESC')->get()->result_array();
	}
}
