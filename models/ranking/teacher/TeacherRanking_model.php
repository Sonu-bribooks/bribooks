<?php defined('BASEPATH') or exit('No direct script access allowed');

class TeacherRanking_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($teacher_rank_id = 0) {
		$this->db->select('teacher_rank.*');

		$this->db->where('teacher_rank.id', (int)$teacher_rank_id);
		$this->db->where('teacher_rank._deleted', 0);

		return $this->db->get('teacher_rank')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('teacher_rank.*');

		if (isset($data['event_challenge_id'])) {
			$this->db->where('teacher_rank.event_challenge_id', (int)$data['event_challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('teacher_rank.event_id', (int)$data['event_id']);
		}

		if (isset($data['teacher_id'])) {
			$this->db->where('teacher_rank.teacher_id', (int)$data['teacher_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('teacher_rank.school_id', (int)$data['school_id']);
		}

		if (isset($data['grade'])) {
			$this->db->where('teacher_rank_daily.grade', (int)$data['grade']);
		}

		if (isset($data['section'])) {
			$this->db->where('teacher_rank_daily.section', $data['section']);
		}

		if (isset($data['score'])) {
			$this->db->where('teacher_rank.score', (int)$data['score']);
		}

		$this->db->where('teacher_rank._deleted', 0);

		$this->db->from('teacher_rank');

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
			'teacher_rank.date_added',
			'teacher_rank.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'teacher_rank.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, teacher_rank.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('teacher_rank', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$teacher_rank_id = $this->db->insert_id();

		return $teacher_rank_id;
	}

	public function edit($teacher_rank_id = 0, $data = []) {
		$this->db->where('id', (int)$teacher_rank_id);
		$this->db->update('teacher_rank', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($teacher_rank_id = 0) {
		$this->db->where('id', (int)$teacher_rank_id);
		$this->db->update('teacher_rank',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
