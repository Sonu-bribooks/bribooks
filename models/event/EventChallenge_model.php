<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventChallenge_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_challenge_id = 0) {
		$this->db->select('event_challenge.*');

		$this->db->where('event_challenge.id', (int)$event_challenge_id);
		$this->db->where('event_challenge._deleted', 0);

		return $this->db->get('event_challenge')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_challenge.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_challenge.event_id', (int)$data['event_id']);
		}

		if (isset($data['start_date_le'])) {
			$this->db->where('event_challenge.start_date <= ', date('Y-m-d H:i:s', strtotime($data['start_date_le'])));
		}

		if (isset($data['end_date_ge'])) {
			$this->db->where('event_challenge.end_date >= ', date('Y-m-d H:i:s', strtotime($data['end_date_ge'])));
		}

		$this->db->where('event_challenge._deleted', 0);

		$this->db->from('event_challenge');

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
			'event_challenge.date_added',
			'event_challenge.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_challenge.id';
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
		$this->db->insert('event_challenge', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_challenge_id = $this->db->insert_id();

		return $event_challenge_id;
	}

	public function edit($event_challenge_id = 0, $data = []) {
		$this->db->where('id', (int)$event_challenge_id);
		$this->db->update('event_challenge', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_challenge_id = 0) {
		$this->db->where('id', (int)$event_challenge_id);
		$this->db->update('event_challenge',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
