<?php defined('BASEPATH') or exit('No direct script access allowed');

class UserAwardAddress_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);

		return $this->db->get('user_award_address')->row_array();
	}

	public function get_all($data = []) {
		if (empty($data)) return false;

		$this->db->select('user_award_address.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_award_address.user_id', (int)$data['user_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_award_address.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_award_address.status', (int)$data['status']);
		}

		if (isset($data['ship_status'])) {
			$this->db->where('user_award_address.ship_status', $data['ship_status']);
		}

		$this->db->where('user_award_address._deleted', 0);

		$this->db->from('user_award_address');

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
			'user_award_address.status',
			'user_award_address.date_added',
			'user_award_address.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_award_address.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function getByIds($user_id = false, $type = false) {
		if (!$user_id)
			return false;

		$this->db->select('*');
		$this->db->where('user_id', (int)$user_id);
		if ($type) { $this->db->where('type', $type); }
		return $this->db->get('user_award_address')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('user_award_address', $data + [
			'status'		=> 1,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_award_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByIds($user_id = false, $data = [], $type = false) {
		if (!$user_id)
			return false;

		if ($type) {
			$this->db->where('type', $type);
		}

		$this->db->where('ship_status=0');
		$this->db->where('user_id', (int)$user_id);
		$this->db->update('user_award_address', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_award_address',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
