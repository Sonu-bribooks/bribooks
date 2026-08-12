<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ReviewFlag_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($review_flag_id = 0) {
		$this->db->select('review_flags.*');

		$this->db->where('review_flags.id', (int)$review_flag_id);
		$this->db->where('review_flags._deleted', 0);

		return $this->db->get('review_flags')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('review_flags.*, review_flag_type.name as flag_type, CONCAT(users.first_name, " ", users.last_name) AS reporter_name');

		if (isset($data['review_flag_type'])) {
			$this->db->where('review_flags.review_flag_type', (int)$data['review_flag_type']);
		}

        if (isset($data['review_id'])) {
			$this->db->where('review_flags.review_id', (int)$data['review_id']);
		}

        if (isset($data['user_id'])) {
			$this->db->where('review_flags.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->or_like('review_flags.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('review_flags._deleted', 0);

		$this->db->join('review_flag_type', 'review_flag_type.id = review_flags.review_flag_type_id', 'left');
		$this->db->join('users', 'users.id = review_flags.user_id', 'left');

		$this->db->from('review_flags');

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
			'review_flags.date_added',
			'review_flags.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'review_flags.id';
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
		$this->db->insert('review_flags', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$review_flag_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $review_flag_id;
	}

	public function edit($review_flag_id = 0, $data = []) {
		$this->db->where('id', (int)$review_flag_id);
		$this->db->update('review_flags', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($review_flag_id = 0) {
		$this->db->where('id', (int)$review_flag_id);
		$this->db->update('review_flags',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('review_flags', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}
}
