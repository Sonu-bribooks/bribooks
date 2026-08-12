<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventSignupForm_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_signup_form.*');

		$this->db->where('event_signup_form.id', (int)$id);
		$this->db->where('event_signup_form._deleted', 0);

		return $this->db->get('event_signup_form')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_signup_form.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_signup_form.event_id', (int)$data['event_id']);
		}

		$this->db->where('event_signup_form._deleted', 0);

		$this->db->from('event_signup_form');

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
			'event_signup_form.id',
			'event_signup_form.date_added',
			'event_signup_form.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_signup_form.id';
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
		$this->db->insert('event_signup_form', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_signup_form_id = $this->db->insert_id();

		return $event_signup_form_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_signup_form', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_signup_form',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
