<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventTopRanker_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get_all($data = []) {
		$this->db->select('event_top_rankers.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_top_rankers.event_id', (int)$data['event_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_top_rankers.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('event_top_rankers.user_id', (int)$data['user_id']);
		}

		$this->db->where('event_top_rankers._deleted', 0);

		$this->db->from('event_top_rankers');

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
			'event_top_rankers.score',
			'event_top_rankers.date_added',
			'event_top_rankers.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_top_rankers.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_top_rankers', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
