<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserEventInvitation_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_event_invitation_id = 0) {
		$this->db->select('user_event_invitation.*');

		$this->db->where('user_event_invitation.id', (int)$user_event_invitation_id);
		$this->db->where('user_event_invitation._deleted', 0);
		return $this->db->get('user_event_invitation')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_event_invitation.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_event_invitation.user_id', (int)$data['user_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_event_invitation.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_event_invitation.status', (int)$data['status']);
		}

		$this->db->where('user_event_invitation._deleted', 0);
		$this->db->from('user_event_invitation');

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
			'user_event_invitation.user_id',
			'user_event_invitation.status',
			'user_event_invitation.date_added',
			'user_event_invitation.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_event_invitation.date_added';
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
		$this->db->insert('user_event_invitation', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_event_invitation', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_event_invitation',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
