<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SiteType_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($site_type_id = 0) {
		$this->db->select('site_type.*');
		$this->db->where('site_type.id', (int)$site_type_id);
		$this->db->where('site_type._deleted', 0);

		return $this->db->get('site_type')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('site_type.*');

		if (isset($data['achievement'])) {
			$this->db->where('site_type.achievement', (int)$data['achievement']);
		}

		if (isset($data['type'])) {
			$this->db->where('site_type.type', $data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('site_type.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('site_type.name', $data['search'], 'after');
			$this->db->or_like('site_type.type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('site_type._deleted', 0);

		$this->db->from('site_type');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'site_type.name',
			'site_type.date_added',
			'site_type.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'site_type.id';
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
		$this->db->insert('site_type', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$site_type_id = $this->db->insert_id();

		return $site_type_id;
	}

	public function edit($site_type_id = 0, $data = []) {
		$this->db->where('id', (int)$site_type_id);
		$this->db->update('site_type', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($site_type_id = 0) {
		$this->db->where('id', (int)$site_type_id);
		$this->db->update('site_type',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('site_type', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
