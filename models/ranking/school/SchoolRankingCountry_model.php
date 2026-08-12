<?php defined('BASEPATH') or exit('No direct script access allowed');

class SchoolRankingCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($school_rank_country_id = 0) {
		$this->db->select('school_rank_country.*');

		$this->db->where('school_rank_country.id', (int)$school_rank_country_id);
		$this->db->where('school_rank_country._deleted', 0);

		return $this->db->get('school_rank_country')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('school_rank_country.*');

		if (isset($data['event_challenge_country_id'])) {
			$this->db->where('school_rank_country.event_challenge_country_id', (int)$data['event_challenge_country_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('school_rank_country.event_id', (int)$data['event_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('school_rank_country.country_id', (int)$data['country_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('school_rank_country.school_id', (int)$data['school_id']);
		}

		if (isset($data['school_code'])) {
			$this->db->where('school_rank_country.school_code', $data['school_code']);
		}

		if (isset($data['score_ge'])) {
			$this->db->where('school_rank_country.score >=', (int)$data['score_ge']);
		}

		if (isset($data['score'])) {
			$this->db->where('school_rank_country.score', (int)$data['score']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('school_rank_country.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('school_rank_country._deleted', 0);

		$this->db->from('school_rank_country');

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
			'school_rank_country.date_added',
			'school_rank_country.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_rank_country.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, school_rank_country.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('school_rank_country', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$school_rank_country_id = $this->db->insert_id();

		return $school_rank_country_id;
	}

	public function edit($school_rank_country_id = 0, $data = []) {
		$this->db->where('id', (int)$school_rank_country_id);
		$this->db->update('school_rank_country', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($school_rank_country_id = 0) {
		$this->db->where('id', (int)$school_rank_country_id);
		$this->db->update('school_rank_country',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
