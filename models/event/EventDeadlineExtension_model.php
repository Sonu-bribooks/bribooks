<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventDeadlineExtension_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_deadline_extension.*');

		$this->db->where('event_deadline_extension.id', (int)$id);
		$this->db->where('event_deadline_extension._deleted', 0);

		return $this->db->get('event_deadline_extension')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_deadline_extension.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_deadline_extension.event_id', (int)$data['event_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('event_deadline_extension.type', $data['type']);
		}

		if (isset($data['item_id'])) {
			$this->db->where('event_deadline_extension.item_id', (int)$data['item_id']);
		}

		$this->db->where('event_deadline_extension._deleted', 0);

		$this->db->from('event_deadline_extension');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'event_deadline_extension.id',
			'event_deadline_extension.date_added',
			'event_deadline_extension.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_deadline_extension.id';
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
		$this->db->insert('event_deadline_extension', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_deadline_extension', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_deadline_extension',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
