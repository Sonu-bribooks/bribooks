<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LeagueTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('league_template.*');

		$this->db->where('league_template.id', (int)$id);
		$this->db->where('league_template._deleted', 0);

		return $this->db->get('league_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('league_template.*');

		if (!empty($data['event_id'])) {
			$this->db->where('league_template.event_id', (int)$data['event_id']);
		}

		if (isset($data['template_type'])) {
			$this->db->where('league_template.template_type', $data['template_type']);
		}

		if (isset($data['type'])) {
			$this->db->where('league_template.type', $data['type']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('league_template.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('league_template.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('league_template.name', $data['search'], 'after');
			$this->db->or_where('league_template.id', $data['search'], 'after');
		}

		$this->db->where('league_template._deleted', 0);

		$this->db->from('league_template');

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
			'league_template.name',
			'league_template.status',
			'league_template.date_added',
			'league_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'league_template.date_added';
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
		$this->db->insert('league_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$league_template = $this->db->insert_id();

		return $league_template;
	}

	public function edit($league_template = 0, $data = []) {
		$this->db->where('id', (int)$league_template);
		$this->db->update('league_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
		return true;
	}

	public function delete($league_template = 0) {
		$this->db->where('id', (int)$league_template);
		$this->db->update('league_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
