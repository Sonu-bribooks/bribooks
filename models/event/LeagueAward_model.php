<?php defined('BASEPATH') or exit('No direct script access allowed');

class LeagueAward_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('league_award.*');

		$this->db->where('league_award.id', (int)$id);
		$this->db->where('league_award._deleted', 0);

		return $this->db->get('league_award')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('league_award.*');

		if (isset($data['event_id'])) {
			$this->db->where('league_award.event_id', (int)$data['event_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('league_award.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('league_award.type', $data['type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('league_award.event_id', $data['search'], 'after');
			$this->db->or_like('league_award.challenge_id', $data['search'], 'after');
			$this->db->or_like('league_award.type', $data['search'], 'after');
            $this->db->or_like('league_award.rank', $data['search'], 'after');
			$this->db->or_like('league_award.award', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('league_award._deleted', 0);

		$this->db->from('league_award');

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
			'league_award.date_added',
			'league_award.display_order',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'league_award.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('league_award', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('league_award', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('league_award',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

    public function getRankByPosition($event_id, $challenge_id, $rank, $table) {
        if ($rank <= 0) {
            return [];
        }

        return $this->db
            ->select('id, score, user_id, book_id')
            ->from('user_rank_general')
            ->where('event_id', (int)$event_id)
            ->where('challenge_id', (int)$challenge_id)
            ->where('_deleted', 0)
            ->order_by('score', 'DESC')
            ->order_by('id', 'ASC')
            ->limit(1, $rank - 1)
            ->get()
            ->row_array();
    }
}
