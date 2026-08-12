<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserAnnouncements_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('user_announcements.*');

		$this->db->where('user_announcements.id', (int)$id);
		$this->db->where('user_announcements._deleted', 0);

		return $this->db->get('user_announcements')->row_array();
	}

	public function getByUserId($user_id = 0) {
		$this->db->select('user_announcements.*');
		$this->db->where('user_announcements.user_id', (int)$user_id);
		$this->db->where('user_announcements._deleted', 0);
		$this->db->order_by('user_announcements.id', 'DESC');
		return $this->db->get('user_announcements')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_announcements.*');

		if (isset($data['template_id'])) {
			$this->db->where('user_announcements.template_id', (int)$data['template_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('user_announcements.user_id', (int)$data['user_id']);
		}

		$this->db->where('user_announcements._deleted', 0);

		$this->db->from('user_announcements');

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
			'user_announcements.id',
			'user_announcements.date_added',
			'user_announcements.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_announcements.id';
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
		if (isset($data['files'])) {
			unset($data['files']);
		}

		$this->db->insert('user_announcements', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_announcements', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_announcements',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
