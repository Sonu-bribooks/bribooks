<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventTeacher_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_teacher_id = 0) {
		$this->db->select('event_teacher.*');

		$this->db->where('event_teacher.id', (int)$event_teacher_id);
		$this->db->where('event_teacher._deleted', 0);

		return $this->db->get('event_teacher')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_teacher.*, event.name as event_name, users.first_name , users.last_name');

		if (isset($data['teacher_id'])) {
			$this->db->where('event_teacher.teacher_id', (int)$data['teacher_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_teacher.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['is_active_event'])) {
			$this->db->where(
				vsprintf('event_teacher.event_id IN (SELECT id FROM event where start_date <= \'%s\' AND end_date >= \'%s\')', [
					date('Y-m-d H:i:s'),
					date('Y-m-d H:i:s'),
				])
			);
		}

		$this->db->where('event_teacher._deleted', 0);
		$this->db->where('users._deleted', 0);

		$this->db->join('event', 'event.id = event_teacher.event_id', 'left');
		$this->db->join('users', 'users.id = event_teacher.teacher_id', 'left');

		$this->db->from('event_teacher');

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
			'event_teacher.date_added',
			'event_teacher.date_modified',
			'event_teacher.event_id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_teacher.id';
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
		$this->db->insert('event_teacher', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_teacher_id = $this->db->insert_id();

		return $event_teacher_id;
	}

	public function edit($event_teacher_id = 0, $data = []) {
		$this->db->where('id', (int)$event_teacher_id);
		$this->db->update('event_teacher', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_teacher_id = 0) {
		$this->db->where('id', (int)$event_teacher_id);
		$this->db->update('event_teacher',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getEventUserByUserId($event_id = 0, $teacher_id = 0) {
		$this->db->select('event_teacher.*');

		$this->db->where('event_teacher.event_id', (int)$event_id);
		$this->db->where('event_teacher.teacher_id', (int)$teacher_id);
		$this->db->where('event_teacher._deleted', 0);

		return $this->db->get('event_teacher')->row_array();
	}

	public function getEventNameByUserId($teacher_id = 0) {
		$this->db->select('event.name');

		$this->db->join('event', 'event.id = event_teacher.event_id');

		$this->db->where('event_teacher.teacher_id', (int)$teacher_id);
		$this->db->where('event_teacher._deleted', 0);

		$this->db->order_by('event_teacher.id', 'DESC');

		return $this->db->get('event_teacher')->row_array();
	}
}
