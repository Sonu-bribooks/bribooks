<?php defined('BASEPATH') or exit('No direct script access allowed');

class TeacherRankingSchool_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($teacher_rank_school_id = 0) {
		$this->db->select('teacher_rank_school.*');

		$this->db->where('teacher_rank_school.id', (int)$teacher_rank_school_id);
		$this->db->where('teacher_rank_school._deleted', 0);

		return $this->db->get('teacher_rank_school')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('teacher_rank_school.*');

		if (isset($data['event_challenge_school_id'])) {
			$this->db->where('teacher_rank_school.event_challenge_school_id', (int)$data['event_challenge_school_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('teacher_rank_school.event_id', (int)$data['event_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('teacher_rank_school.school_id', (int)$data['school_id']);
		}

		if (isset($data['teacher_id'])) {
			$this->db->where('teacher_rank_school.teacher_id', (int)$data['teacher_id']);
		}

		if (isset($data['grade'])) {
			$this->db->where('teacher_rank_daily.grade', (int)$data['grade']);
		}

		if (isset($data['section'])) {
			$this->db->where('teacher_rank_daily.section', $data['section']);
		}

		if (isset($data['score_ge'])) {
			$this->db->where('teacher_rank_school.score >=', (int)$data['score_ge']);
		}

		if (isset($data['score'])) {
			$this->db->where('teacher_rank_school.score', (int)$data['score']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('teacher_rank_school.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('teacher_rank_school._deleted', 0);

		$this->db->from('teacher_rank_school');

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
			'teacher_rank_school.date_added',
			'teacher_rank_school.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'teacher_rank_school.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, teacher_rank_school.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('teacher_rank_school', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$teacher_rank_school_id = $this->db->insert_id();

		return $teacher_rank_school_id;
	}

	public function edit($teacher_rank_school_id = 0, $data = []) {
		$this->db->where('id', (int)$teacher_rank_school_id);
		$this->db->update('teacher_rank_school', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($teacher_rank_school_id = 0) {
		$this->db->where('id', (int)$teacher_rank_school_id);
		$this->db->update('teacher_rank_school',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
