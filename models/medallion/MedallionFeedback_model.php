<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MedallionFeedback_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('medallion_feedback.*');

		$this->db->where('medallion_feedback.id', (int)$id);
		$this->db->where('medallion_feedback._deleted', 0);
		return $this->db->get('medallion_feedback')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('medallion_feedback.*');

		if (isset($data['type'])) {
			$this->db->where('medallion_feedback.type', $data['type']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('medallion_feedback.event_id', (int)$data['event_id']);
		}

		if (isset($data['medallion_id'])) {
			$this->db->where('medallion_feedback.medallion_id', (int)$data['medallion_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('medallion_feedback.user_id', (int)$data['user_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('medallion_feedback.order_id', (int)$data['order_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('medallion_feedback.file', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('medallion_feedback._deleted', 0);

		$this->db->from('medallion_feedback');

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
			'medallion_feedback.id',
			'medallion_feedback.date_added',
			'medallion_feedback.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'medallion_feedback.id';
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
		$this->db->insert('medallion_feedback', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion_feedback', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('medallion_feedback',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
