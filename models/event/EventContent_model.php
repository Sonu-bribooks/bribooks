<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventContent_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_content_id = 0) {
		$this->db->select('event_content.*');

		$this->db->where('event_content.id', (int)$event_content_id);
		$this->db->where('event_content._deleted', 0);

		return $this->db->get('event_content')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_content.*');

		if (isset($data['event_content_id'])) {
			$this->db->where('event_content.id', (int)$data['event_content_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_content.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('event_content.site_id', (int)$data['site_id']);
		}

		$this->db->where('event_content._deleted', 0);

		$this->db->from('event_content');

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
			'event_content.date_added',
			'event_content.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_content.id';
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
		$this->db->insert('event_content', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_content_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_content_added_successfully'));

		return $event_content_id;
	}

	public function edit($event_content_id = 0, $data = []) {
		$this->db->where('id', (int)$event_content_id);
		$this->db->update('event_content', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_content_update_successfully'));
	}

	public function delete($event_content_id = 0) {
		$this->db->where('id', (int)$event_content_id);
		$this->db->update('event_content',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByEventId($event_id = 0) {
		$this->db->select('event_content.*');
		$this->db->where('event_content.event_id', (int)$event_id);
		return $this->db->get('event_content')->row_array();
	}
}
