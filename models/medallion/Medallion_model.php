<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Medallion_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('medallion.*');

		$this->db->where('medallion.id', (int)$id);
		$this->db->where('medallion._deleted', 0);
		return $this->db->get('medallion')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('medallion.*');

		if (isset($data['type'])) {
			$this->db->where('medallion.type', $data['type']);
		}
		
		if (isset($data['quantity'])) {
			$this->db->where('medallion.quantity', (int)$data['quantity']);
		}

		if (isset($data['sold'])) {
			$this->db->where('medallion.sold', $data['sold']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('medallion.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('medallion._deleted', 0);

		$this->db->from('medallion');

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
			'medallion.date_added',
			'medallion.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'medallion.id';
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
		$this->db->insert('medallion', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('medallion_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('medallion_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('medallion', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('medallion_updated_successfully'));
	}

	public function getDetailByWhere($where = []) {
		$this->db->select('medallion.*');

		$this->db->where($where);
		$this->db->where('medallion._deleted', 0);
		return $this->db->get('medallion')->row_array();
	}
}
