<?php defined('BASEPATH') or exit('No direct script access allowed');

class UserLimit_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}
	public function get($id = 0) {
		if (empty($id)) return false;

		$this->db->select('*');

		$this->db->where('id', (int)$id);
		$this->db->where('_deleted', 0);

		return $this->db->get('user_limit')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_limit.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_limit.user_id', (int)$data['user_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_limit.event_id', (int)$data['event_id']);
		}

		if (isset($data['publishing_limit'])) {
			$this->db->where('user_limit.publishing_limit', $data['publishing_limit']);
		}

        if (isset($data['publishing_limit_ge'])) {
			$this->db->where('user_limit.publishing_limit >=', $data['publishing_limit_ge']);
		}

		if (isset($data['publishing_limit_le'])) {
			$this->db->where('user_limit.publishing_limit <=', $data['publishing_limit_le']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_limit.status', (int)$data['status']);
		}

		$this->db->where('user_limit._deleted', 0);

		$this->db->from('user_limit');

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
			'user_limit.publishing_limit',
			'user_limit.status',
			'user_limit.date_added',
			'user_limit.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_limit.id';
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
		$this->db->insert('user_limit', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$user_limit_id = $this->db->insert_id();

		return $user_limit_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', $id);
		$this->db->update('user_limit', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', $id);
		$this->db->update('user_limit', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
	
	public function updateCanPublish($id, $inc = true){
		if ($inc) {
			$this->db->set('current', 'current + 1', FALSE);
			$this->db->set('can_publish', '0', FALSE);
		} else {
			$this->db->set('current', 'current - 1', FALSE);
			$this->db->set('can_publish', 'IF(can_publish = 1, 0, 1)', FALSE);
		}

		$this->db->where('id', $id);
		$this->db->update('user_limit');

		return $this->db->affected_rows();
	}
}
