<?php defined('BASEPATH') or exit('No direct script access allowed');

class RankingSchool_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_rank_school_id = 0) {
		$this->db->select('user_rank_school.*');

		$this->db->where('user_rank_school.id', (int)$user_rank_school_id);
		$this->db->where('user_rank_school._deleted', 0);

		return $this->db->get('user_rank_school')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_rank_school.*');

		if (isset($data['event_challenge_school_id'])) {
			$this->db->where('user_rank_school.event_challenge_school_id', (int)$data['event_challenge_school_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('user_rank_school.event_challenge_school_id', (int)$data['challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_rank_school.event_id', (int)$data['event_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('user_rank_school.school_id', (int)$data['school_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('user_rank_school.school_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('user_rank_school.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('user_rank_school.book_id', (int)$data['book_id']);
		}

		if (isset($data['score_ge'])) {
			$this->db->where('user_rank_school.score >=', (int)$data['score_ge']);
		}

		if (isset($data['score'])) {
			$this->db->where('user_rank_school.score', (int)$data['score']);
		}

		if (isset($data['is_moved'])) {
			$this->db->where('user_rank_school.is_moved', (int)$data['is_moved']);
		}

		if (isset($data['rank_gte'])) {
			$this->db->where('user_rank_school.rank >= ', (int)$data['rank_gte']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_rank_school.book_name', $data['search'], 'after');
			$this->db->or_like('user_rank_school.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_rank_school._deleted', 0);

		$this->db->from('user_rank_school');

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
			'user_rank_school.date_added',
			'user_rank_school.date_modified',
			'user_rank_school.rank',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_rank_school.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, user_rank_school.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('user_rank_school', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_rank_school_id = $this->db->insert_id();

		return $user_rank_school_id;
	}

	public function edit($user_rank_school_id = 0, $data = []) {
		$this->db->where('id', (int)$user_rank_school_id);
		$this->db->update('user_rank_school', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_rank_school_id = 0) {
		$this->db->where('id', (int)$user_rank_school_id);
		$this->db->update('user_rank_school',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
