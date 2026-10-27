<?php defined('BASEPATH') or exit('No direct script access allowed');

class SchoolRankingGrade_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($school_rank_grade_id = 0) {
		$this->db->select('school_rank_grade.*');

		$this->db->where('school_rank_grade.id', (int)$school_rank_grade_id);
		$this->db->where('school_rank_grade._deleted', 0);

		return $this->db->get('school_rank_grade')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('school_rank_grade.*');

		if (isset($data['event_challenge_grade_id'])) {
			$this->db->where('school_rank_grade.event_challenge_grade_id', (int)$data['event_challenge_grade_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('school_rank_grade.event_id', (int)$data['event_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('school_rank_grade.city_id', (int)$data['city_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('school_rank_grade.school_id', (int)$data['school_id']);
		}

		if (isset($data['school_code'])) {
			$this->db->where('school_rank_grade.school_code', $data['school_code']);
		}

		if (isset($data['grade'])) {
			$this->db->where('school_rank_grade.grade', (int)$data['grade']);
		}

		if (isset($data['section'])) {
			$this->db->where('school_rank_grade.section', $data['section']);
		}

		if (isset($data['score_ge'])) {
			$this->db->where('school_rank_grade.score >=', (int)$data['score_ge']);
		}

		if (isset($data['score'])) {
			$this->db->where('school_rank_grade.score', (int)$data['score']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('school_rank_grade.school_name', $data['search'], 'after');
			$this->db->or_like('school_rank_grade.school_code', $data['search'], 'after');
			$this->db->or_like('school_rank_grade.grade', $data['search'], 'after');
			$this->db->or_like('school_rank_grade.section', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('school_rank_grade._deleted', 0);

		$this->db->from('school_rank_grade');

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
			'school_rank_grade.date_added',
			'school_rank_grade.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_rank_grade.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, school_rank_grade.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('school_rank_grade', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$school_rank_grade_id = $this->db->insert_id();

		return $school_rank_grade_id;
	}

	public function edit($school_rank_grade_id = 0, $data = []) {
		$this->db->where('id', (int)$school_rank_grade_id);
		$this->db->update('school_rank_grade', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($school_rank_grade_id = 0) {
		$this->db->where('id', (int)$school_rank_grade_id);
		$this->db->update('school_rank_grade',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getSchoolGradeTotals($data = []) {
		$this->db->select('
			COALESCE(SUM(school_rank_grade.total_students), 0) AS total_students,
			COALESCE(SUM(school_rank_grade.total_published_author), 0) AS total_published_author
		', FALSE);

		$this->db->from('school_rank_grade');

		if (isset($data['event_id'])) {
			$this->db->where('school_rank_grade.event_id', (int)$data['event_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('school_rank_grade.school_id', (int)$data['school_id']);
		}

		if (isset($data['event_challenge_grade_id'])) {
			$this->db->where('school_rank_grade.event_challenge_grade_id', (int)$data['event_challenge_grade_id']);
		}

		$this->db->where('school_rank_grade._deleted', 0);

		return $this->db->get()->row_array();
	}
}
