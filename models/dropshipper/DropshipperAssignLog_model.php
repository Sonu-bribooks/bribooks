<?php defined('BASEPATH') or exit('No direct script access allowed');

class DropshipperAssignLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($dropshipper_assign_logs_id = 0) {
		$this->db->select('dropshipper_assign_logs.*');

		$this->db->where('dropshipper_assign_logs.id', (int)$dropshipper_assign_logs_id);
		$this->db->where('dropshipper_assign_logs._deleted', 0);

		return $this->db->get('dropshipper_assign_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('dropshipper_assign_logs.*');

		if (isset($data['printer_id'])) {
			$this->db->where('dropshipper_assign_logs.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['assignment_id'])) {
			$this->db->where('dropshipper_assign_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('dropshipper_assign_logs.product_id', (int)$data['book_id']);
		}

		if (isset($data['product_id'])) {
			$this->db->where('dropshipper_assign_logs.product_id', (int)$data['product_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('dropshipper_assign_logs.order_id', (int)$data['order_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('dropshipper_assign_logs.status', (int)$data['status']);
		}

		if (!empty($data['option'])) {
			$this->db->like('dropshipper_assign_logs.option', $data['option'], 'both');
		}

		if (!empty($data['search'])) {
			$this->db->like('dropshipper_assign_logs.comment', $data['search'], 'after');
		}

		$this->db->where('dropshipper_assign_logs._deleted', 0);

		$this->db->from('dropshipper_assign_logs');

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
			'dropshipper_assign_logs.id',
			'dropshipper_assign_logs.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'dropshipper_assign_logs.id';
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
		$duplicate = $this->db->get_where('dropshipper_assign_logs', [
			'printer_id'     => $data['printer_id'],
			'product_id'     => $data['product_id'],
			'order_id'       => $data['order_id'],
			'assignment_id'  => $data['assignment_id'],
			'_deleted'  	 => 0,
		]);
		
		if ($duplicate->num_rows() <= 0) {
			$this->db->insert('dropshipper_assign_logs', $data + [
				'date_added'	=> date('Y-m-d H:i:s'),
			]);

			$user_bank_id = $this->db->insert_id();

			return $user_bank_id;
		}
	}

	public function edit($id = '', $data = []) {
		$this->db->update('dropshipper_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id,
		]);
	}

	public function editByProductId($where = [], $data = []) {
		$this->db->where('product_id', (int)$where['product_id']);
		$this->db->where('printer_id', (int)$where['printer_id']);
		$this->db->like('option', $where['option']);
		$this->db->like('version', (int)$where['version']);
		$this->db->like('status', (int)$where['status']);

		$this->db->update('dropshipper_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('dropshipper_assign_logs',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByAssignmentAndOrderId($order_id = '', $assignment_id = '', $data = []) {
		$this->db->update('dropshipper_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'assignment_id'	=> (int)$assignment_id,
			'order_id'		=> (int)$order_id,
		]);
	}

	public function editByOrderId($order_id = [], $data = []) {
		$this->db->update('dropshipper_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'order_id'		=> (int)$order_id,
		]);
	}

	public function getSumQuantity($data = []) {
		$this->db->select('SUM(dropshipper_assign_logs.quantity) AS total_quantity');
		$this->db->from('dropshipper_assign_logs');
		$this->db->join('dropshipper_assignment', 'dropshipper_assignment.id = dropshipper_assign_logs.assignment_id', 'left');

		if (!empty($data['printer_id'])) {
			$this->db->where('dropshipper_assign_logs.printer_id', (int)$data['printer_id']);
		}

		if (!empty($data['assignment_id'])) {
			$this->db->where('dropshipper_assign_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (!empty($data['book_id'])) {
			$this->db->where('dropshipper_assign_logs.product_id', (int)$data['book_id']);
		}

		if (!empty($data['product_id'])) {
			$this->db->where('dropshipper_assign_logs.product_id', (int)$data['product_id']);
		}

		if (!empty($data['order_id'])) {
			$this->db->where('dropshipper_assign_logs.order_id', (int)$data['order_id']);
		}

		if (isset($data['status'])) {
			$this->db->where_in('dropshipper_assign_logs.status', $data['status']);
		}

		if (!empty($data['option'])) {
			$this->db->like('dropshipper_assign_logs.option', $data['option'], 'both');
		}

		if (!empty($data['option_type'])) {
			$this->db->where('dropshipper_assignment.option_type', $data['option_type']);
		}

		if (!empty($data['search'])) {
			$this->db->like('dropshipper_assign_logs.comment', $data['search'], 'after');
		}

		$this->db->where('dropshipper_assign_logs._deleted', 0);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->row()->total_quantity ?? 0;
		}

		return 0;
	}
}
