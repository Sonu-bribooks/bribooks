<?php defined('BASEPATH') or exit('No direct script access allowed');

class ShareTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = '0') {
		$this->db->select('*');

		$this->db->where('share_template.id', $id);

		return $this->db->get('share_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('share_template.*, event.name as event_name');

		if (isset($data['id'])) {
			$this->db->where('share_template.id', (int)$data['id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('share_template.event_id', (int)$data['event_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('share_template.type', (int)$data['type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->or_like('event.name', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('share_template._deleted', 0);

		$this->db->join('event', 'event.id = share_template.event_id', 'left');

		$this->db->from('share_template');

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
			'share_template.date_added',
			'share_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'share_template.date_added';
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
		$this->db->insert('share_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('share_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('share_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
