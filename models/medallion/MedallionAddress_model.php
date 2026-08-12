<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MedallionAddress_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('medallion_address.*');

		$this->db->where('medallion_address.id', (int)$id);
		$this->db->where('medallion_address._deleted', 0);
		return $this->db->get('medallion_address')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('medallion_address.*');

		if (isset($data['user_id'])) {
			$this->db->where('medallion_address.user_id', (int)$data['user_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('medallion_address.type', $data['type']);
		}

		if (isset($data['mobile'])) {
			$this->db->where('medallion_address.mobile', $data['mobile']);
		}

		if (isset($data['zipcode'])) {
			$this->db->where('medallion_address.zipcode', $data['zipcode']);
		}

		if (isset($data['city'])) {
			$this->db->where('medallion_address.city', $data['city']);
		}

		if (isset($data['state'])) {
			$this->db->where('medallion_address.state', $data['state']);
		}

		if (isset($data['country'])) {
			$this->db->where('medallion_address.country', $data['country']);
		}

		if (isset($data['status'])) {
			$this->db->where('medallion_address.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('medallion_address.mobile', $data['search'], 'after');
			$this->db->or_like('medallion_address.zipcode', $data['search'], 'after');
			$this->db->or_like('medallion_address.city', $data['search'], 'after');
			$this->db->or_like('medallion_address.state', $data['search'], 'after');
			$this->db->or_like('medallion_address.country', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('medallion_address._deleted', 0);

		$this->db->from('medallion_address');

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
			'medallion_address.id',
			'medallion_address.date_added',
			'medallion_address.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'medallion_address.id';
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
		$this->db->insert('medallion_address', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('medallion_address_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('medallion_address_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion_address',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('medallion_address', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('medallion_updated_successfully'));
	}
}
