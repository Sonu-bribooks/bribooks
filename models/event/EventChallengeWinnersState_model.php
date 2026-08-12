<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventChallengeWinnersState_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_challenge_winners_state_id = 0) {
		$this->db->select('917303234240.*');

		$this->db->where('event_challenge_winners_state.id', (int)$event_challenge_winners_state_id);
		$this->db->where('event_challenge_winners_state._deleted', 0);

		return $this->db->get('event_challenge_winners_state')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_challenge_winners_state.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_challenge_winners_state.event_id', (int)$data['event_id']);
		}

		if (isset($data['event_challenge_id'])) {
			$this->db->where('event_challenge_winners_state.event_challenge_state_id', (int)$data['event_challenge_state_id']);
		}

		if (isset($data['event_challenge_daily_id'])) {
			$this->db->where('event_challenge_winners_state.state_id', (int)$data['state_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('event_challenge_winners_state.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_challenge_winners_state.book_id', (int)$data['book_id']);
		}

		$this->db->where('event_challenge_winners_state._deleted', 0);

		$this->db->from('event_challenge_winners_state');

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
			'event_challenge_winners_state.date_added',
			'event_challenge_winners_state.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_challenge_winners_state.id';
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
		$this->db->insert('event_challenge_winners_state', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_challenge_winners_state_id = $this->db->insert_id();

		return $event_challenge_winners_state_id;
	}

	public function edit($event_challenge_winners_state_id = 0, $data = []) {
		$this->db->where('id', (int)$event_challenge_winners_state_id);
		$this->db->update('event_challenge_winners_state', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_challenge_winners_state_id = 0) {
		$this->db->where('id', (int)$event_challenge_winners_state_id);
		$this->db->update('event_challenge_winners_state',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
