<?php defined('BASEPATH') or exit('No direct script access allowed');

class RankingAmazon_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_rank_amazon_id = 0) {
		$this->db->select('user_rank_amazon.*');

		$this->db->where('user_rank_amazon.id', (int)$user_rank_amazon_id);
		$this->db->where('user_rank_amazon._deleted', 0);

		return $this->db->get('user_rank_amazon')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_rank_amazon.*');

		if (isset($data['event_challenge_amazon_id'])) {
			$this->db->where('user_rank_amazon.event_challenge_amazon_id', (int)$data['event_challenge_amazon_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('user_rank_amazon.event_challenge_amazon_id', (int)$data['challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('user_rank_amazon.event_id', (int)$data['event_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('user_rank_amazon.country_id', (int)$data['country_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('user_rank_amazon.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('user_rank_amazon.book_id', (int)$data['book_id']);
		}

		if (isset($data['score_ge'])) {
			$this->db->where('user_rank_amazon.score >=', (int)$data['score_ge']);
		}

		if (isset($data['score'])) {
			$this->db->where('user_rank_amazon.score', (int)$data['score']);
		}

		if (isset($data['rank_gte'])) {
			$this->db->where('user_rank_amazon.rank >= ', (int)$data['rank_gte']);
		}

		if (isset($data['site_id'])) {
			$this->db->join('users', 'users.id = user_rank_amazon.user_id', 'left');
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_rank_amazon.book_name', $data['search'], 'after');
			$this->db->or_like('user_rank_amazon.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('user_rank_amazon._deleted', 0);

		$this->db->from('user_rank_amazon');

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
			'user_rank_amazon.rank',
			'user_rank_amazon.date_added',
			'user_rank_amazon.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_rank_amazon.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, user_rank_amazon.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('user_rank_amazon', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_rank_amazon_id = $this->db->insert_id();

		return $user_rank_amazon_id;
	}

	public function edit($user_rank_amazon_id = 0, $data = []) {
		$this->db->where('id', (int)$user_rank_amazon_id);
		$this->db->update('user_rank_amazon', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_rank_amazon_id = 0) {
		$this->db->where('id', (int)$user_rank_amazon_id);
		$this->db->update('user_rank_amazon',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
