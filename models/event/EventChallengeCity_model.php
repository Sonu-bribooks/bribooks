<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventChallengeCity_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_challenge_city_id = 0) {
		$this->db->select('event_challenge_city.*');

		$this->db->where('event_challenge_city.id', (int)$event_challenge_city_id);
		$this->db->where('event_challenge_city._deleted', 0);

		return $this->db->get('event_challenge_city')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_challenge_city.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_challenge_city.event_id', (int)$data['event_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('event_challenge_city.type', $data['type']);
		}

		if (isset($data['start_date_le'])) {
			$this->db->where('event_challenge_city.start_date <= ', date('Y-m-d H:i:s', strtotime($data['start_date_le'])));
		}

		if (isset($data['end_date_ge'])) {
			$this->db->where('event_challenge_city.end_date >= ', date('Y-m-d H:i:s', strtotime($data['end_date_ge'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_challenge_city.name', $data['search'], 'after');
			$this->db->or_like('event_challenge_city.id', $data['search'], 'after');
			$this->db->or_like('event_challenge_city.event_id', $data['search'], 'after');
			$this->db->or_like('event_challenge_city.slug', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_challenge_city._deleted', 0);

		$this->db->from('event_challenge_city');

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
			'event_challenge_city.date_added',
			'event_challenge_city.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_challenge_city.id';
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
		self::_formatData($data);

		$this->db->insert('event_challenge_city', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_challenge_city_id = $this->db->insert_id();

		$this->alert_model->scheduleAutoCloseLeague([
			'type'			=> 'city',
			'challenge_id'	=> $event_challenge_city_id,
		]);

		return $event_challenge_city_id;
	}

	public function edit($event_challenge_city_id = 0, $data = []) {
		self::_formatData($data);

		$this->db->where('id', (int)$event_challenge_city_id);
		$this->db->update('event_challenge_city', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->alert_model->scheduleAutoCloseLeague([
			'type'			=> 'city',
			'challenge_id'	=> $event_challenge_city_id,
		]);
	}

	public function delete($event_challenge_city_id = 0) {
		$this->db->where('id', (int)$event_challenge_city_id);
		$this->db->update('event_challenge_city',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getBySlug($slug = '') {
		return $this->db->get_where('event_challenge_city',  [
			'slug'			=> $slug,
			'_deleted'		=> 0,
		])->row_array();
	}

	private function _formatData(&$data) {
		$data['display_date'] 	= date('Y-m-d H:i:s', strtotime($data['display_date']));
		$data['start_date'] 	= date('Y-m-d H:i:s', strtotime($data['start_date']));
		$data['end_date'] 		= date('Y-m-d H:i:s', strtotime($data['end_date']));
	}
}
