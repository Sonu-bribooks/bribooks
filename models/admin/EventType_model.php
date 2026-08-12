<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventType_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_id = 0) {
		$this->db->select('event_type.*');

		$this->db->where('event_type.id', (int)$event_id);
		$this->db->where('event_type._deleted', 0);

		return $this->db->get('event_type')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_type.*');

		if (isset($data['type_id'])) {
			$this->db->where('event_type.id', (int)$data['event_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_type.name', $data['search'], 'after');
			$this->db->or_like('event_type.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_type._deleted', 0);

		$this->db->from('event_type');

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
			'event_type.date_added',
			'event_type.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_type.id';
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
		$this->db->insert('event_type', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $event_id;
	}

	public function edit($event_id = 0, $data = []) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('event_type', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($event_id = 0) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('event_type',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
