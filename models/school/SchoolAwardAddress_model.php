<?php defined('BASEPATH') or exit('No direct script access allowed');

class SchoolAwardAddress_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('school_award_address')->row_array();
	}

	public function get_all($data = []) {
		if (empty($data))
			return false;

		$this->db->select('school_award_address.*');

		if (isset($data['school_id'])) {
			$this->db->where('school_award_address.school_id', (int)$data['school_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('school_award_address.site_id', (int)$data['site_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('school_award_address.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_award_address.status', (int)$data['status']);
		}

		if (isset($data['ship_status'])) {
			$this->db->where('school_award_address.ship_status', $data['ship_status']);
		}

		$this->db->where('school_award_address._deleted', 0);

		$this->db->from('school_award_address');

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
			'school_award_address.status',
			'school_award_address.date_added',
			'school_award_address.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_award_address.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function getByIds($site_id = false, $type = false) {
		if (!$site_id)
			return false;

		$this->db->select('*');
		$this->db->where('site_id', (int)$site_id);
		if ($type) { $this->db->where('type', $type); }
		return $this->db->get('school_award_address')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('school_award_address', $data + [
			'status'		=> 1,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_award_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByIds($site_id = false, $data = [], $type = false) {
		if (!$site_id)
			return false;

		if ($type) {
			$this->db->where('type', $type);
		}
		
		$this->db->where('ship_status=0');
		$this->db->where('site_id', (int)$site_id);
		$this->db->update('school_award_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

    public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_award_address',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
