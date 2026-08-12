<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserSource_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($user_source_id = 0) {
		$this->db->select('user_source.*');
		$this->db->where('user_source.id', (int)$user_source_id);

		return $this->db->get('user_source')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('user_source.*');

		if (!empty($data['user_id'])) {
			$this->db->where('user_source.user_id', (int)$data['user_id']);
		}

		if (!empty($data['lead_id'])) {
			$this->db->where('user_source.lead_id', (int)$data['lead_id']);
		}

		$this->db->from('user_source');

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
			'user_source.name',
			'user_source.status',
			'user_source.date_added',
			'user_source.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_source.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('user_source', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_source_id = $this->db->insert_id();

		return $user_source_id;
	}

	public function edit($user_source_id = 0, $data = []) {
		$this->db->where('id', $user_source_id);
		$this->db->update('user_source', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByStudentId($student_id = 0, $data = []) {
		$this->db->where('user_id', $student_id);
		$this->db->update('user_source', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($user_source_id = 0) {
		$this->db->where('id', $user_source_id);
		$this->db->update('user_source', [
			'_deleted'	=> 0,
		]);
	}
}
