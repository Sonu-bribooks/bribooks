<?php
defined('BASEPATH') or exit('No direct access allowed');

class EventLiteraryLeader_model extends CI_Model {
    public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_literary_leader.*');

		$this->db->where('event_literary_leader.id', (int)$id);
		$this->db->where('event_literary_leader._deleted', 0);

		return $this->db->get('event_literary_leader')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_literary_leader.*');

        if (isset($data['type'])) {
			$this->db->where('event_literary_leader.type', $data['type']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('event_literary_leader.literary_leader_challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_literary_leader.event_id', (int)$data['event_id']);
		}

		if (isset($data['slug'])) {
			$this->db->where('event_literary_leader.challenge_slug', $data['slug']);
		}

        if (isset($data['user_id'])) {
			$this->db->where('event_literary_leader.user_id', (int)$data['user_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('event_literary_leader.city_id', (int)$data['city_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('event_literary_leader.state_id', (int)$data['state_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('event_literary_leader.country_id', (int)$data['country_id']);
		}

		if (isset($data['rank'])) {
			$this->db->where('event_literary_leader.rank', (int)$data['rank']);
		}

		if (isset($data['rank_ge'])) {
			$this->db->where('event_literary_leader.rank >=', (int)$data['rank_ge']);
		}

        if (isset($data['rank_le'])) {
			$this->db->where('event_literary_leader.rank <=', (int)$data['rank_le']);
		}

		$this->db->where('event_literary_leader._deleted', 0);

		$this->db->from('event_literary_leader');

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
			'event_literary_leader.rank',
			'event_literary_leader.date_added',
			'event_literary_leader.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_literary_leader.id';
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
		$this->db->insert('event_literary_leader', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_literary_leader_id = $this->db->insert_id();

		return $event_literary_leader_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_literary_leader', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_literary_leader',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

}