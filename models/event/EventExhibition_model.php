<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventExhibition_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_exhibition.*');
		$this->db->where('event_exhibition.id', (int)$id);
		$this->db->where('event_exhibition._deleted', 0);

		return $this->db->get('event_exhibition')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_exhibition.*');

        if (isset($data['type'])) {
			$this->db->where('event_exhibition.type', $data['type']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_exhibition.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('event_exhibition.user_id', (int)$data['user_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('event_exhibition.site_id', (int)$data['site_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_exhibition.book_id', (int)$data['book_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_exhibition.event_id', $data['search'], 'after');
			$this->db->or_like('event_exhibition.user_id', $data['search'], 'after');
			$this->db->or_like('event_exhibition.site_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_exhibition._deleted', 0);
		$this->db->from('event_exhibition');

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
			'event_exhibition.date_added',
			'event_exhibition.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_exhibition.id';
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
		$this->db->insert('event_exhibition', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_exhibition', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_exhibition',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
