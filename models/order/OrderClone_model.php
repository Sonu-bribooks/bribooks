<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderClone_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = '') {
		$this->db->where('id', $id);
		return $this->db->get('order_clone')->row_array();
	}

	public function get_all($data = []) {
		if (isset($data['parent_order_id'])) {
			$this->db->where('order_clone.parent_order_id', $data['parent_order_id']);
		}

		$this->db->from('order_clone');

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
			'order_clone.id'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_clone.id';
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

	public function add($data = []) {
		$this->db->insert('order_clone', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);
	}

	public function getByIds($data = []) {
		if (isset($data['parent_order_id'])) {
			$this->db->where('order_clone.parent_order_id', $data['parent_order_id']);
		}

		if (isset($data['clone_order_id'])) {
			$this->db->where('order_clone.clone_order_id', $data['clone_order_id']);
		}

		return $this->db->get('order_clone')->result_array();
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('order_clone', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}
}
