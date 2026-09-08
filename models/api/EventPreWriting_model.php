<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventPreWriting_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

    public function get($id = 0) {
		$this->db->select('pre_writing.*');

		$this->db->where('pre_writing.id', (int)$id);
		$this->db->where('pre_writing._deleted', 0);

		return $this->db->get('pre_writing')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('pre_writing.*');

		if (isset($data['event_id'])) {
			$this->db->where('pre_writing.event_id', (int)$data['event_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('pre_writing.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('pre_writing.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('pre_writing.event_id', $data['search'], 'after');
			$this->db->or_like('pre_writing.book_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('pre_writing._deleted', 0);

		$this->db->from('pre_writing');

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
			'pre_writing.date_added',
			'pre_writing.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'pre_writing.id';
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
		$this->db->insert('pre_writing', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('pre_writing_added_successfully'));

		return $id;
	}
	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('pre_writing', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('pre_writing_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('pre_writing',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}