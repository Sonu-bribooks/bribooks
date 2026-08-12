<?php defined('BASEPATH') or exit('No direct script access allowed');

class RankingCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_rank_country_id = 0) {
		$this->db->select('user_rank_country.*');

		$this->db->where('user_rank_country.id', (int)$user_rank_country_id);
		$this->db->where('user_rank_country._deleted', 0);

		return $this->db->get('user_rank_country')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_rank_country.*');

		if (isset($data['event_challenge_country_id'])) {
			$this->db->where('user_rank_country.event_challenge_country_id', (int)$data['event_challenge_country_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('user_rank_country.event_challenge_country_id', (int)$data['challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_rank_country.event_id', (int)$data['event_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('user_rank_country.country_id', (int)$data['country_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('user_rank_country.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('user_rank_country.book_id', (int)$data['book_id']);
		}

		if (isset($data['score_ge'])) {
			$this->db->where('user_rank_country.score >=', (int)$data['score_ge']);
		}

		if (isset($data['score'])) {
			$this->db->where('user_rank_country.score', (int)$data['score']);
		}

		if (isset($data['rank_gte'])) {
			$this->db->where('user_rank_country.rank >= ', (int)$data['rank_gte']);
		}

		if (isset($data['site_id'])) {
			$this->db->join('users', 'users.id = user_rank_country.user_id', 'left');
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_rank_country.book_name', $data['search'], 'after');
			$this->db->or_like('user_rank_country.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_rank_country._deleted', 0);

		$this->db->from('user_rank_country');

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
			'user_rank_country.rank',
			'user_rank_country.date_added',
			'user_rank_country.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_rank_country.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, user_rank_country.date_modified ASC");

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('user_rank_country', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_rank_country_id = $this->db->insert_id();

		return $user_rank_country_id;
	}

	public function edit($user_rank_country_id = 0, $data = []) {
		$this->db->where('id', (int)$user_rank_country_id);
		$this->db->update('user_rank_country', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_rank_country_id = 0) {
		$this->db->where('id', (int)$user_rank_country_id);
		$this->db->update('user_rank_country',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
