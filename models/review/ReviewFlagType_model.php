<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ReviewFlagType_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($flag_type_id = 0) {
		$this->db->select('review_flag_type.*');

		$this->db->where('review_flag_type.id', (int)$flag_type_id);
		$this->db->where('review_flag_type._deleted', 0);

		return $this->db->get('review_flag_type')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('review_flag_type.*');

		if (isset($data['status'])) {
			$this->db->where('review_flag_type.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('review_flag_type.name', $data['search'], 'after');
			$this->db->or_like('review_flag_type.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('review_flag_type._deleted', 0);

		$this->db->from('review_flag_type');

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
			'review_flag_type.date_added',
			'review_flag_type.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'review_flag_type.id';
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
		$this->db->insert('review_flag_type', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$flag_type_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $flag_type_id;
	}

	public function edit($flag_type_id = 0, $data = []) {
		$this->db->where('id', (int)$flag_type_id);
		$this->db->update('review_flag_type', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($flag_type_id = 0) {
		$this->db->where('id', (int)$flag_type_id);
		$this->db->update('review_flag_type',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('review_flag_type', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}
}
