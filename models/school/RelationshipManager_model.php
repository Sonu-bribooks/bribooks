<?php
defined('BASEPATH') or exit('No direct access allowed');

class RelationshipManager_model extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function get($id = false) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('relationship_managers')->row_array();
	}

	public function get_all($data = []) {
		if (empty($data))
			return false;

		$this->db->select('relationship_managers.*');

		if (isset($data['school_id'])) {
			$this->db->where('relationship_managers.school_id', (int)$data['school_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('relationship_managers.name', (int)$data['name']);
		}

		if (isset($data['email'])) {
			$this->db->where('relationship_managers.email', (int)$data['email']);
		}

		if (isset($data['status'])) {
			$this->db->where('relationship_managers.status', (int)$data['status']);
		}

		if (isset($data['mobile'])) {
			$this->db->where('relationship_managers.mobile', $data['mobile']);
		}

       if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('relationship_managers.name', $data['search'], 'after');
			$this->db->or_like('relationship_managers.school_id', $data['search'], 'after');
			$this->db->or_like('relationship_managers.site_id', $data['search'], 'after');
			$this->db->or_like('relationship_managers.email', $data['search'], 'after');
			$this->db->or_like('relationship_managers.mobile', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('relationship_managers._deleted', 0);

		$this->db->from('relationship_managers');

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
			'relationship_managers.status',
			'relationship_managers.date_added',
			'relationship_managers.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'relationship_managers.date_added';
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
		$this->db->insert('relationship_managers', $data + [
			'status'		=> 1,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('relationship_managers', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

    public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('relationship_managers',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}


}