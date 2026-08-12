<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserAppNotification_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_app_notification_id = 0) {
		$this->db->select('user_app_notification.*');

		$this->db->where('user_app_notification.id', (int)$user_app_notification_id);
		$this->db->where('user_app_notification._deleted', 0);

		return $this->db->get('user_app_notification')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_app_notification.*');

		if (isset($data['user_id'])) {
			$this->db->where('user_app_notification.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_app_notification.name', $data['search'], 'after');
			$this->db->or_like('user_app_notification.code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_app_notification._deleted', 0);

		$this->db->from('user_app_notification');

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
			'user_app_notification.id',
			'user_app_notification.date_added',
			'user_app_notification.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_app_notification.id';
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
		$this->db->insert('user_app_notification', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_app_notification_id = $this->db->insert_id();

		return $user_app_notification_id;
	}

	public function edit($user_app_notification_id = 0, $data = []) {
		$this->db->where('id', (int)$user_app_notification_id);
		$this->db->update('user_app_notification', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_app_notification_id = 0) {
		$this->db->where('id', (int)$user_app_notification_id);
		$this->db->update('user_app_notification',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
