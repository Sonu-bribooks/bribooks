<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UtmSource_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($utm_source_id = 0) {
		$this->db->select('utm_source.*');

		$this->db->where('utm_source.id', (int)$utm_source_id);
		$this->db->where('utm_source._deleted', 0);

		return $this->db->get('utm_source')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('utm_source.*');

		if (isset($data['event_id'])) {
			$this->db->where('utm_source.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('utm_source.status', (int)$data['status']);
		}

		if (isset($data['type'])) {
			$this->db->where('utm_source.type', $data['type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('utm_source.key', $data['search'], 'after');
			$this->db->or_like('utm_source.value', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('utm_source._deleted', 0);

		$this->db->from('utm_source');

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
			'utm_source.name',
			'utm_source.status',
			'utm_source.date_added',
			'utm_source.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'utm_source.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('utm_source', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$utm_source_id = $this->db->insert_id();

		return $utm_source_id;
	}

	public function edit($utm_source_id = 0, $data = []) {
		$this->db->where('id', (int)$utm_source_id);
		$this->db->update('utm_source', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($utm_source_id = 0) {
		$this->db->where('id', (int)$utm_source_id);
		$this->db->update('utm_source',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
