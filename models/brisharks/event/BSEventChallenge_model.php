<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSEventChallenge_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('event_challenge.*');

		$this->bsdb->where('event_challenge.id', (int)$id);
		$this->bsdb->where('event_challenge._deleted', 0);
		return $this->bsdb->get('event_challenge')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('event_challenge.*');

		if (isset($data['name'])) {
			$this->bsdb->where('event_challenge.name', $data['name']);
		}

		if (isset($data['slug'])) {
			$this->bsdb->where('event_challenge.slug', $data['slug']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('event_challenge.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('event_challenge.name', $data['search'], 'after');
			$this->bsdb->or_like('event_challenge.slug', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('event_challenge._deleted', 0);

		$this->bsdb->from('event_challenge');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
		} else {
			$this->bsdb->limit(10, 0);
		}

		$sort_data = [
			'event_challenge.id',
			'event_challenge.date_added',
			'event_challenge.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_challenge.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->bsdb->order_by($sort, $order);

		$results = $this->bsdb->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		self::_formatData($data);

		$this->bsdb->insert('event_challenge', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_challenge_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		self::_formatData($data);

		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('event_challenge', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_challenge_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('event_challenge',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('event_challenge', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('event_challenge_updated_successfully'));
	}

	public function getBySlug($slug = '') {
		$this->bsdb->select('event_challenge.*');

		$this->bsdb->where('event_challenge.slug', $slug);
		$this->bsdb->where('event_challenge.status', 1);
		$this->bsdb->where('event_challenge._deleted', 0);

		return $this->bsdb->get('event_challenge')->row_array();
	}

	private function _formatData(&$data) {
		$data['start_date'] 			= date('Y-m-d H:i:s', strtotime($data['start_date']));
		$data['end_date'] 				= date('Y-m-d H:i:s', strtotime($data['end_date']));
		$data['display_date'] 			= date('Y-m-d H:i:s', strtotime($data['display_date']));
	}
}
