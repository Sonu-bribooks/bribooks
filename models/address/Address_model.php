<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Address_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($address_id = 0) {
		$this->db->select('address.*');

		$this->db->where('address.id', (int)$address_id);
		$this->db->where('address._deleted', 0);

		return $this->db->get('address')->row_array();
	}

	public function getByID($address_id = 0) {
		$this->db->select('address.*');

		$this->db->where('address.id', (int)$address_id);

		return $this->db->get('address')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('address.*');

		if (isset($data['user_id'])) {
			$this->db->where('address.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('address.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('address.name', $data['search'], 'after');
			$this->db->or_like('address.zipcode', $data['search'], 'after');
			$this->db->or_like('address.address', $data['search'], 'after');
			$this->db->or_like('address.mobile', $data['search'], 'after');
			$this->db->or_like('address.city', $data['search'], 'after');
			$this->db->or_like('address.state', $data['search'], 'after');
			$this->db->or_like('address.country', $data['search'], 'after');
		}

		$this->db->where('address._deleted', 0);

		$this->db->from('address');

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
			'address.name',
			'address.status',
			'address.date_added',
			'address.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'address.id';
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
		$this->db->insert('address', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$address_id = $this->db->insert_id();

		return $address_id;
	}

	public function edit($address_id = 0, $data = []) {
		$this->db->where('id', (int)$address_id);
		$this->db->update('address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($address_id = 0) {
		$this->db->where('id', (int)$address_id);
		$this->db->update('address',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
