<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolMedallionAddress_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			school_medallion_address.*,
			school_medallion_address.school_name as name,
			site.owner_email as email,
			site.owner_mobile as mobile
		');

		$this->db->where('school_medallion_address.id', (int)$id);
		$this->db->where('school_medallion_address._deleted', 0);

		$this->db->join('site', 'site.id = school_medallion_address.site_id', 'left');

		return $this->db->get('school_medallion_address')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			school_medallion_address.*,
			school_medallion_address.school_name as name,
			site.owner_email as email,
			site.owner_mobile as mobile
		');

		if (isset($data['site_id'])) {
			$this->db->where('school_medallion_address.site_id', (int)$data['site_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('school_medallion_address.type', $data['type']);
		}

		if (isset($data['coordinator_mobile'])) {
			$this->db->where('school_medallion_address.coordinator_mobile', $data['coordinator_mobile']);
		}

		if (isset($data['leader_mobile'])) {
			$this->db->where('school_medallion_address.leader_mobile', $data['leader_mobile']);
		}

		if (isset($data['coordinator_email'])) {
			$this->db->where('school_medallion_address.coordinator_email', $data['coordinator_email']);
		}

		if (isset($data['leader_email'])) {
			$this->db->where('school_medallion_address.leader_email', $data['leader_email']);
		}

		if (isset($data['zipcode'])) {
			$this->db->where('school_medallion_address.zipcode', $data['zipcode']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('school_medallion_address.city_id', $data['city_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('school_medallion_address.state_id', $data['state_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('school_medallion_address.country_id', $data['country_id']);
		}

		if (isset($data['city'])) {
			$this->db->where('school_medallion_address.city', $data['city']);
		}

		if (isset($data['state'])) {
			$this->db->where('school_medallion_address.state', $data['state']);
		}

		if (isset($data['country'])) {
			$this->db->where('school_medallion_address.country', $data['country']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_medallion_address.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('school_medallion_address.mobile', $data['search'], 'after');
			$this->db->or_like('school_medallion_address.zipcode', $data['search'], 'after');
			$this->db->or_like('school_medallion_address.city', $data['search'], 'after');
			$this->db->or_like('school_medallion_address.state', $data['search'], 'after');
			$this->db->or_like('school_medallion_address.country', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->join('site', 'site.id = school_medallion_address.site_id', 'left');

		$this->db->where('school_medallion_address._deleted', 0);

		$this->db->from('school_medallion_address');

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
			'school_medallion_address.id',
			'school_medallion_address.date_added',
			'school_medallion_address.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_medallion_address.id';
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
		$this->db->insert('school_medallion_address', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('school_medallion_address_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_medallion_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('school_medallion_address_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_medallion_address',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('school_medallion_address', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('medallion_updated_successfully'));
	}
}
