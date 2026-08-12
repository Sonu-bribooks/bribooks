<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventCommunicationKit_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_communication_kit.*');

		$this->db->where('event_communication_kit.id', (int)$id);
		$this->db->where('event_communication_kit._deleted', 0);

		return $this->db->get('event_communication_kit')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_communication_kit.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_communication_kit.event_id', (int)$data['event_id']);
		}

		$this->db->where('event_communication_kit._deleted', 0);

		$this->db->from('event_communication_kit');

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
			'event_communication_kit.id',
			'event_communication_kit.date_added',
			'event_communication_kit.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_communication_kit.id';
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
		$this->db->insert('event_communication_kit', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_communication_kit_id = $this->db->insert_id();

		return $event_communication_kit_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_communication_kit', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_communication_kit',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
