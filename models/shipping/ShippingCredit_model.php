<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ShippingCredit_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($shipping_credit_id = 0) {
		$this->db->select('shipping_credit.*');

		$this->db->where('shipping_credit.id', (int)$shipping_credit_id);
		$this->db->where('shipping_credit._deleted', 0);

		return $this->db->get('shipping_credit')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('shipping_credit.*');

		if (isset($data['user_id'])) {
			$this->db->where('shipping_credit.user_id', (int)$data['user_id']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('shipping_credit.country_code', $data['country_code']);
		}

		if (isset($data['type'])) {
			$this->db->where('shipping_credit.type', (int)$data['type']);
		}

		if (isset($data['credit'])) {
			$this->db->where('shipping_credit.credit', (double)$data['credit']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('shipping_credit.credit', $data['search'], 'after');
			$this->db->or_like('shipping_credit.country_code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('shipping_credit._deleted', 0);

		$this->db->from('shipping_credit');

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
			'shipping_credit.id',
			'shipping_credit.credit',
			'shipping_credit.date_added',
			'shipping_credit.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'shipping_credit.id';
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
		$this->db->insert('shipping_credit', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$shipping_credit_id = $this->db->insert_id();

		return $shipping_credit_id;
	}

	public function edit($shipping_credit_id = 0, $data = []) {
		$this->db->where('id', (int)$shipping_credit_id);
		$this->db->update('shipping_credit', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($shipping_credit_id = 0) {
		$this->db->where('id', (int)$shipping_credit_id);
		$this->db->update('shipping_credit',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
