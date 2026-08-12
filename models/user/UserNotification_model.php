<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserNotification_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->where('user_notification.id', (int)$id);
		$this->db->where('user_notification._deleted', 0);

		return $this->db->get('user_notification')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_notification.*,');

		if (isset($data['event_id'])) {
			$this->db->where('user_notification.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('user_notification.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_notification.heading', $data['search'], 'after');
			$this->db->or_like('user_notification.description', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_notification._deleted', 0);

		$this->db->from('user_notification');

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
			'user_notification.id',
			'user_notification.heading',
			'user_notification.status',
			'user_notification.date_added',
			'user_notification.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_notification.id';
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
		$this->db->insert('user_notification', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_notification_id = $this->db->insert_id();

		return $user_notification_id;
	}

	public function edit($user_notification_id = 0, $data = []) {
		$this->db->where('id', (int)$user_notification_id);
		$this->db->update('user_notification', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_notification', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('user_notification', [
				'status'	=> (int)$status
			]);
		}
	}
}
