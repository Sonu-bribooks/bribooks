<?php defined('BASEPATH') or exit('No direct script access allowed');

class ReprintOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($reprint_order_id = 0) {
		$this->db->select('reprint_order.*');

		$this->db->where('reprint_order.id', (int)$reprint_order_id);

		return $this->db->get('reprint_order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('reprint_order.*');

		if (isset($data['order_id'])) {
			$this->db->where('reprint_order.order_id', (int)$data['order_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('reprint_order.version', (int)$data['version']);
		}

		if (isset($data['product_id'])) {
			$this->db->where('reprint_order.product_id', (int)$data['product_id']);
		}

		if (isset($data['quantity'])) {
			$this->db->where('reprint_order.quantity', (int)$data['quantity']);
		}

		if (isset($data['option'])) {
			$this->db->where('reprint_order.option', $data['option']);
		}

		if (isset($data['type'])) {
			$this->db->like('reprint_order.option', $data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('reprint_order.status', (int)$data['status']);
		}

		if (isset($data['printer_id'])) {
			$this->db->where('reprint_order.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['manager_id'])) {
			$this->db->where('reprint_order.manager_id', (int)$data['manager_id']);
		}

		if (!empty($data['search'])) {
			$this->db->like('reprint_order.comment', $data['search'], 'after');
		}

		// $this->db->where('reprint_order._deleted', 0);

		$this->db->from('reprint_order');

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
			'reprint_order.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'reprint_order.date_added';
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
		$this->db->insert('reprint_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
		]);

		$reprint_order_id = $this->db->insert_id();

		return $reprint_order_id;
	}

	public function edit($id, $data = []) {
		$this->db->update('reprint_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id,
		]);
	}

	public function reprintOrders($data = []) {
		$this->db->select("
			product.product_id,
			SUM(product.quantity) as quantity,
			book.name,
			product.option,
			book.id,
			product.version,
			DATE(product.date_added) as date_added,
			GROUP_CONCAT(product.order_id) as order_ids,
			GROUP_CONCAT(product.id) as ids,
		");

		if (isset($data['assign_printer_id'])) {
			$this->db->where('order.assign_printer_id', $data['assign_printer_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('product.status', (int)$data['status']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('product.product_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('product.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->like('product.option', $data['option'], 'both');
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(product.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('book.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('order._deleted', 0);
		$this->db->from('order');
		$this->db->join('reprint_order as product', 'order.id = product.order_id', 'left');
		$this->db->join('book', 'product.product_id = book.id', 'left');
		$this->db->group_by('product.product_id');
		$this->db->group_by('product.option');
		$this->db->group_by('product.version');
		$this->db->group_by('DATE(product.date_added)');

		$total = $this->db->count_all_results('', FALSE);

		$this->db->order_by('DATE(product.date_added)', 'ASC');

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}

	public function countCopies($data = []) {
		$this->db->select_sum('reprint_order.quantity');

		if (isset($data['assign_printer_id'])) {
			$this->db->where('reprint_order.printer_id', (int)$data['assign_printer_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('reprint_order.product_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('reprint_order.version', (int)$data['version']);
		}

		if (isset($data['type'])) {
			$this->db->like('reprint_order.option', $data['type']);
		}

		if (isset($data['option'])) {
			$this->db->like('reprint_order.option', $data['option']);
		}

		if (isset($data['status'])) {
			$this->db->where('reprint_order.status', (int)$data['status']);
		}

		if (isset($data['status_ge'])) {
			$this->db->where('reprint_order.status >= ', (int)$data['status_ge']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(reprint_order.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		$this->db->from('reprint_order');

		return $this->db->get()->row()->quantity;
	}
}
