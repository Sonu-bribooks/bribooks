<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolOrderComment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('school_order_comment.*');

		$this->db->where('school_order_comment.id', (int)$id);
		$this->db->where('school_order_comment._deleted', 0);
		return $this->db->get('school_order_comment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('school_order_comment.*');

		if (isset($data['manager_id'])) {
			$this->db->where('school_order_comment.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['school_order_id'])) {
			$this->db->where('school_order_comment.school_order_id', (int)$data['school_order_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_order_comment.status', (int)$data['status']);
		}

		$this->db->where('school_order_comment._deleted', 0);

		$this->db->from('school_order_comment');

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
			'school_order_comment.id',
			'school_order_comment.date_added',
			'school_order_comment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_order_comment.id';
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
		$this->db->insert('school_order_comment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('school_order_comment_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_order_comment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('school_order_comment_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_order_comment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
