<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventUserInviteCode_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_user_invite_code.*');

		$this->db->where('event_user_invite_code.id', (int)$id);
		$this->db->where('event_user_invite_code._deleted', 0);

		return $this->db->get('event_user_invite_code')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_user_invite_code.*');

		if (isset($data['type'])) {
			$this->db->where('event_user_invite_code.type', (int)$data['type']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_user_invite_code.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('event_user_invite_code.user_id', (int)$data['user_id']);
		}

		if (isset($data['code'])) {
			$this->db->where('event_user_invite_code.code', $data['code']);
		}

		$this->db->where('event_user_invite_code._deleted', 0);

		$this->db->from('event_user_invite_code');

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
			'event_user_invite_code.event_id',
			'event_user_invite_code.user_id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_user_invite_code.id';
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
		$this->db->insert('event_user_invite_code', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_user_invite_code_id = $this->db->insert_id();

		return $event_user_invite_code_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_user_invite_code', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_user_invite_code',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
