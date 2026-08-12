<?php defined('BASEPATH') or exit('No direct script access allowed');

class DropshipperAssignRollback_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($dropshipper_assign_rollback_id = 0) {
		$this->db->select('dropshipper_assign_rollback.*');

		$this->db->where('dropshipper_assign_rollback.id', (int)$dropshipper_assign_rollback_id);
		$this->db->where('dropshipper_assign_rollback._deleted', 0);

		return $this->db->get('dropshipper_assign_rollback')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('dropshipper_assign_rollback.*');

		if (isset($data['printer_id'])) {
			$this->db->where('dropshipper_assign_rollback.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['assignment_id'])) {
			$this->db->where('dropshipper_assign_rollback.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('dropshipper_assign_rollback.product_id', (int)$data['book_id']);
		}

		if (isset($data['product_id'])) {
			$this->db->where('dropshipper_assign_rollback.product_id', (int)$data['product_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('dropshipper_assign_rollback.order_id', (int)$data['order_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('dropshipper_assign_rollback.status', (int)$data['status']);
		}

		if (!empty($data['option'])) {
			$this->db->like('dropshipper_assign_rollback.option', $data['option'], 'both');
		}

		if (!empty($data['search'])) {
			$this->db->like('dropshipper_assign_rollback.comment', $data['search'], 'after');
		}

		$this->db->where('dropshipper_assign_rollback._deleted', 0);

		$this->db->from('dropshipper_assign_rollback');

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
			'dropshipper_assign_rollback.id',
			'dropshipper_assign_rollback.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'dropshipper_assign_rollback.id';
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
		$duplicate = $this->db->get_where('dropshipper_assign_rollback', [
			'order_id'     		=> $data['printer_id'],
			'user_id'     		=> $data['user_id'],
			'_deleted'  	 	=> 0,
		]);

		if ($duplicate->num_rows() == 0) {
			$this->db->insert('dropshipper_assign_rollback', $data + [
				'date_added'	=> date('Y-m-d H:i:s'),
			]);

			$user_bank_id = $this->db->insert_id();

			return $user_bank_id;
		}
	}

	public function edit($id = '', $data = []) {
		$this->db->update('dropshipper_assign_rollback', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id,
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('dropshipper_assign_rollback',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
