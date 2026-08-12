<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PodcastTimeSlots_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('podcast_time_slots.*');

		$this->db->where('podcast_time_slots.id', (int)$id);
		$this->db->where('podcast_time_slots._deleted', 0);

		return $this->db->get('podcast_time_slots')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('podcast_time_slots.*');

		if (isset($data['event_id'])) {
			$this->db->where('podcast_time_slots.event_id', (int)$data['event_id']);
		}

		if (isset($data['date'])) {
			$this->db->where('podcast_time_slots.book_id', (int)$data['book_id']);
		}

		if (isset($data['slot'])) {
			$this->db->where('podcast_time_slots.slot', (int)$data['slot']);
		}

        if (isset($data['occupied'])) {
			$this->db->where('podcast_time_slots.id NOT IN (select slot_id from book_podcast where _deleted = 0)');

		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('podcast_time_slots.event_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('podcast_time_slots._deleted', 0);

		$this->db->from('podcast_time_slots');

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
			'podcast_time_slots.date_added',
			'podcast_time_slots.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'podcast_time_slots.id';
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
		$this->db->insert('podcast_time_slots', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('slot_added_successfully'));

		return $id;
	}
	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('podcast_time_slots', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('slot_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('podcast_time_slots',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
