<?php defined('BASEPATH') or exit('No direct script access allowed');

class PrinterAssignLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($printer_assign_logs_id = 0) {
		$this->db->select('printer_assign_logs.*');

		$this->db->where('printer_assign_logs.id', (int)$printer_assign_logs_id);
		$this->db->where('printer_assign_logs._deleted', 0);

		return $this->db->get('printer_assign_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('printer_assign_logs.*');

		if (isset($data['printer_id'])) {
			$this->db->where('printer_assign_logs.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['assignment_id'])) {
			$this->db->where('printer_assign_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('printer_assign_logs.product_id', (int)$data['book_id']);
		}

		if (isset($data['product_id'])) {
			$this->db->where('printer_assign_logs.product_id', (int)$data['product_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('printer_assign_logs.order_id', (int)$data['order_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('printer_assign_logs.status', (int)$data['status']);
		}

		if (!empty($data['option'])) {
			$this->db->like('printer_assign_logs.option', $data['option'], 'both');
		}

		if (!empty($data['search'])) {
			$this->db->like('printer_assign_logs.comment', $data['search'], 'after');
		}

		$this->db->where('printer_assign_logs._deleted', 0);

		$this->db->from('printer_assign_logs');

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
			'printer_assign_logs.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'printer_assign_logs.date_added';
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
		$duplicate = $this->db->get_where('printer_assign_logs', [
			'printer_id'     => $data['printer_id'],
			'product_id'     => $data['product_id'],
			'order_id'       => $data['order_id'],
			'assignment_id'  => $data['assignment_id'],
			'_deleted'  	 => '0',
		]);
		if ($duplicate->num_rows() <= 0) {
			$this->db->insert('printer_assign_logs', $data + [
				'date_added'	=> date('Y-m-d H:i:s'),
			]);

			$user_bank_id = $this->db->insert_id();

			return $user_bank_id;
		}
	}

	public function editById($id = [], $data = []) {
		$this->db->update('printer_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id,
		]);
	}

	public function edit($search = [], $data = []) {
		$this->db->where('order_id', (int)$search['order_id']);
		$this->db->where('product_id', (int)$search['product_id']);

		if (isset($search['version'])) {
			$this->db->where('version', (int)$search['version']);
		}

		if (isset($search['status'])) {
			$this->db->where('status', (int)$search['status']);
		}

		$this->db->like('option', $search['option']);
		$this->db->update('printer_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByProductId($where = [], $data = []) {
		$this->db->where('product_id', (int)$where['product_id']);
		$this->db->where('printer_id', (int)$where['printer_id']);
		$this->db->like('option', $where['option']);
		$this->db->like('version', (int)$where['version']);
		$this->db->like('status', (int)$where['status']);
		$this->db->update('printer_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_bank_id = 0) {
		$this->db->where('id', (int)$user_bank_id);
		$this->db->update('printer_assign_logs',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByAssignmentAndOrderId($order_id = '', $assignment_id = '', $data = []) {
		$this->db->update('printer_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'assignment_id'	=> (int)$assignment_id,
			'order_id'		=> (int)$order_id,
		]);
	}

	public function editByOrderId($order_id = [], $data = []) {
		$this->db->update('printer_assign_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'order_id'		=> (int)$order_id,
		]);
	}
}
