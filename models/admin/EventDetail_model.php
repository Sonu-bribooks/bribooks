<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventDetail_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_detail.*, event.name as event_name');

		$this->db->where('event_detail.id', (int)$id);
		$this->db->where('event_detail._deleted', 0);
		$this->db->join('event', 'event.id = event_detail.event_id', 'left');

		return $this->db->get('event_detail')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_detail.*, event.name as event_name, event.slug as event_slug');

		if (isset($data['event_id'])) {
			$this->db->where('event_detail.event_id', (int)$data['event_id']);
		}

		$this->db->join('event', 'event.id = event_detail.event_id', 'left');

		$this->db->where('event_detail._deleted', 0);

		$this->db->from('event_detail');

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
			'event_detail.date_added',
			'event_detail.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_detail.id';
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
		$this->db->insert('event_detail', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_detail_added_successfully'));

		return $event_id;
	}

	public function edit($event_id = 0, $data = []) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('event_detail', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_detail_update_successfully'));
	}

	public function delete($event_id = 0) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('event_detail',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
	public function getDetailByWhere ($where) {
		$this->db->select('event_detail.*, event.name as event_name');

		$this->db->where($where);
		$this->db->where('event_detail._deleted', 0);
		$this->db->join('event', 'event.id = event_detail.event_id', 'left');
		return $this->db->get('event_detail')->row_array();
	}
}
