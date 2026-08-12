<?php defined('BASEPATH') or exit('No direct script access allowed');

class EventBookQualificationPending_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_book_qualification_pending.*');

		$this->db->where('event_book_qualification_pending.id', (int)$id);
		$this->db->where('event_book_qualification_pending._deleted', 0);

		return $this->db->get('event_book_qualification_pending')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_book_qualification_pending.*');

		if (!empty($data['event_id'])) {
			$this->db->where('event_book_qualification_pending.event_id', (int)$data['event_id']);

			if (!empty($data['type'])) {
				$this->db->where(sprintf('event_book_qualification_pending.book_id NOT IN (select book_id from user_rank_%s where event_id = %s and _deleted = 0)', strtolower($data['type']), $data['event_id']));
			}
		}

        if (!empty($data['site_id'])) {
			$this->db->where('event_book_qualification_pending.site_id', (int)$data['site_id']);
		}

		if (!empty($data['city_id'])) {
			$this->db->where('event_book_qualification_pending.city_id', (int)$data['city_id']);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('event_book_qualification_pending.state_id', (int)$data['state_id']);
		}

        if (!empty($data['country_id'])) {
			$this->db->where('event_book_qualification_pending.country_id', (int)$data['country_id']);
		}

		if (!empty($data['user_id'])) {
			$this->db->where('event_book_qualification_pending.user_id', (int)$data['user_id']);
		}

		if (!empty($data['book_id'])) {
			$this->db->where('event_book_qualification_pending.book_id', (int)$data['book_id']);
		}

		if (!empty($data['score_ge'])) {
			$this->db->where('event_book_qualification_pending.score >=', (int)$data['score_ge']);
		}

		if (!empty($data['score'])) {
			$this->db->where('event_book_qualification_pending.score', (int)$data['score']);
		}

		if (!empty($data['status'])) {
			$this->db->where('event_book_qualification_pending.status', (int)$data['status']);
		}
        

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_book_qualification_pending.book_name', $data['search'], 'after');
			$this->db->or_like('event_book_qualification_pending.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_book_qualification_pending._deleted', 0);

		$this->db->from('event_book_qualification_pending');

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
			'event_book_qualification_pending.date_added',
			'event_book_qualification_pending.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_book_qualification_pending.score';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, event_book_qualification_pending.date_modified ASC");

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('event_book_qualification_pending', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_book_qualification_pending', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_book_qualification_pending',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
