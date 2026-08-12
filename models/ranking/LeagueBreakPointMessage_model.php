<?php defined('BASEPATH') or exit('No direct script access allowed');

class LeagueBreakPointMessage_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('league_breakpoint_message.*');

		$this->db->where('league_breakpoint_message.id', (int)$id);
		$this->db->where('league_breakpoint_message._deleted', 0);

		return $this->db->get('league_breakpoint_message')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('league_breakpoint_message.*');

		if (isset($data['event_id'])) {
			$this->db->where('league_breakpoint_message.event_id', (int)$data['event_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('league_breakpoint_message.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('league_breakpoint_message.type', $data['type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('league_breakpoint_message.event_id', $data['search'], 'after');
			$this->db->or_like('league_breakpoint_message.challenge_id', $data['search'], 'after');
			$this->db->or_like('league_breakpoint_message.type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('league_breakpoint_message._deleted', 0);

		$this->db->from('league_breakpoint_message');

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
			'league_breakpoint_message.date_added',
			'league_breakpoint_message.breakpoint',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'league_breakpoint_message.id';
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
		$this->db->insert('league_breakpoint_message', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('league_breakpoint_message', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('league_breakpoint_message',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
