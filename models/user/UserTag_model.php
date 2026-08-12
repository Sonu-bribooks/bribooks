<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserTag_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			user_tag.*
		');

		$this->db->where('user_tag.id', (int)$id);
		$this->db->where('user_tag._deleted', 0);

		return $this->db->get('user_tag')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			user_tag.*
		');

		if (!empty($data['name'])) {
			$this->db->where('user_tag.name', $data['name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('user_tag.name', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('user_tag._deleted', 0);
		$this->db->from('user_tag');

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
			'user_tag.id',
			'user_tag.priority',
			'user_tag.date_added',
			'user_tag.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_tag.id';
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
		$this->db->insert('user_tag', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$user_tag_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('user_tag_added_successfully'));

		return $user_tag_id;
	}

	public function edit($user_tag_id = 0, $data = []) {
		$this->db->where('id', $user_tag_id);
		$this->db->update('user_tag', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('user_tag_edited_successfully'));
	}

	public function delete($user_tag_id = 0) {
		$this->db->where('id', $user_tag_id);
		$this->db->update('user_tag', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
