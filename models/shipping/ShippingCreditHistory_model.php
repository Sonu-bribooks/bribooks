<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ShippingCreditHistory_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($shipping_credit_history_id = 0) {
		$this->db->select('shipping_credit_history.*');

		$this->db->where('shipping_credit_history.id', (int)$shipping_credit_history_id);
		$this->db->where('shipping_credit_history._deleted', 0);

		return $this->db->get('shipping_credit_history')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('shipping_credit_history.*');

		if (isset($data['user_id'])) {
			$this->db->where('shipping_credit_history.user_id', (int)$data['user_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('shipping_credit_history.order_id', (int)$data['order_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('shipping_credit_history.type', (int)$data['type']);
		}

		if (isset($data['credit'])) {
			$this->db->where('shipping_credit_history.credit', (double)$data['credit']);
		}

		if (isset($data['credit_type'])) {
			$this->db->where('shipping_credit_history.credit_type', (int)$data['credit_type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('shipping_credit_history.credit', $data['search'], 'after');
			$this->db->or_like('shipping_credit_history.credit_type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('shipping_credit_history._deleted', 0);

		$this->db->from('shipping_credit_history');

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
			'shipping_credit_history.id',
			'shipping_credit_history.credit',
			'shipping_credit_history.date_added',
			'shipping_credit_history.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'shipping_credit_history.id';
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
		$this->db->insert('shipping_credit_history', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$shipping_credit_history_id = $this->db->insert_id();

		return $shipping_credit_history_id;
	}

	public function edit($shipping_credit_history_id = 0, $data = []) {
		$this->db->where('id', (int)$shipping_credit_history_id);
		$this->db->update('shipping_credit_history', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($shipping_credit_history_id = 0) {
		$this->db->where('id', (int)$shipping_credit_history_id);
		$this->db->update('shipping_credit_history',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
