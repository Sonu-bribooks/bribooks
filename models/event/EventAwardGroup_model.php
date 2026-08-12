<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventAwardGroup_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_award_group.*');
		
		$this->db->where('event_award_group.id', (int)$id);
		$this->db->where('event_award_group._deleted', 0);

		return $this->db->get('event_award_group')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_award_group.*');

		if (!empty($data['award_ids'])) {
			$this->db->where_in('event_award_group.id', $data['award_ids']);
		}

		if (!empty($data['name'])) {
			$this->db->like('event_award_group.name', $data['name'], 'after');
		}

		if (!empty($data['type'])) {
			$this->db->where('event_award_group.type', $data['type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_award_group.name', $data['search'], 'after');
			$this->db->or_like('event_award_group.type', $data['search'], 'after');
			$this->db->or_like('event_award_group.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_award_group._deleted', 0);

		$this->db->from('event_award_group');

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
			'event_award_group.date_added',
			'event_award_group.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_award_group.id';
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
		$this->db->insert('event_award_group', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $event_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_award_group', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_award_group',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('event_award_group', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}
}
