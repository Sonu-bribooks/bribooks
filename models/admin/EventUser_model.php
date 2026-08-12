<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventUser_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_user_id = 0) {
		$this->db->select('event_user.*');

		$this->db->where('event_user.id', (int)$event_user_id);
		$this->db->where('event_user._deleted', 0);

		return $this->db->get('event_user')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_user.*, event.name as event_name, users.first_name , users.last_name, event.category_ids');

		if (isset($data['user_id'])) {
			$this->db->where('event_user.user_id', (int)$data['user_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_user.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['is_active_event'])) {
			$this->db->where(
				vsprintf('event_user.event_id IN (SELECT id FROM event where start_date <= \'%s\' AND end_date >= \'%s\')', [
					date('Y-m-d H:i:s'),
					date('Y-m-d H:i:s'),
				])
			);
		}

		if (!empty($data['is_active_book_writing'])) {
			$this->db->where(
				vsprintf('event_user.event_id IN (SELECT id FROM event where book_writing_start_date <= \'%s\' AND book_writing_end_date >= \'%s\')', [
					date('Y-m-d H:i:s'),
					date('Y-m-d H:i:s'),
				])
			);
		}

		$this->db->where('event_user._deleted', 0);
		$this->db->where('users._deleted', 0);

		$this->db->join('event', 'event.id = event_user.event_id', 'left');
		$this->db->join('users', 'users.id = event_user.user_id', 'left');

		$this->db->from('event_user');

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
			'event_user.date_added',
			'event_user.date_modified',
			'event_user.event_id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_user.id';
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
		$this->db->insert('event_user', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_user_id = $this->db->insert_id();

		return $event_user_id;
	}

	public function edit($event_user_id = 0, $data = []) {
		$this->db->where('id', (int)$event_user_id);
		$this->db->update('event_user', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_user_id = 0) {
		$this->db->where('id', (int)$event_user_id);
		$this->db->update('event_user',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getEventUserByUserId($event_id = 0, $user_id = 0) {
		$this->db->select('event_user.*');

		$this->db->where('event_user.event_id', (int)$event_id);
		$this->db->where('event_user.user_id', (int)$user_id);
		$this->db->where('event_user._deleted', 0);

		return $this->db->get('event_user')->row_array();
	}

	public function getEventNameByUserId($user_id = 0) {
		$this->db->select('event.name');

		$this->db->join('event', 'event.id = event_user.event_id');

		$this->db->where('event_user.user_id', (int)$user_id);
		$this->db->where('event_user._deleted', 0);

		$this->db->order_by('event_user.id', 'DESC');

		return $this->db->get('event_user')->row_array();
	}
}
