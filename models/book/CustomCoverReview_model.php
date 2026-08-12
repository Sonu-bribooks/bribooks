<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CustomCoverReview_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($custom_cover_id = 0) {
		$this->db->select('custom_cover_review.*');

		$this->db->where('custom_cover_review.id', (int)$custom_cover_id);
		$this->db->where('custom_cover_review._deleted', 0);

		return $this->db->get('custom_cover_review')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('custom_cover_review.*');

		if (isset($data['user_id'])) {
			$this->db->where('custom_cover_review.manager_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('custom_cover_review.status', (int)$data['status']);
		}

        if (isset($data['book_id']) && !empty($data['book_id'])) {
			$this->db->where('custom_cover_review.book_id', (int)$data['book_id']);
		}

        if (isset($data['version']) && !empty($data['version'])) {
			$this->db->where('custom_cover_review.version', (int)$data['version']);
		}

		if (isset($data['book_version_id']) && !empty($data['book_version_id'])) {
			$this->db->where('custom_cover_review.book_version_id', (int)$data['book_version_id']);
		}

		$this->db->where('custom_cover_review._deleted', 0);

		$this->db->from('custom_cover_review');

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
			'custom_cover_review.sort_order',
			'custom_cover_review.date_added',
			'custom_cover_review.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'custom_cover_review.id';
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
		if(empty($data['manager_id']))
			return;

		$this->db->insert('custom_cover_review', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$custom_cover_review_id = $this->db->insert_id();

		return $custom_cover_review_id;
	}

	public function edit($custom_cover_review_id = 0, $data = []) {
		$this->db->where('id', (int)$custom_cover_review_id);
		$this->db->update('custom_cover_review', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($custom_cover_review_id = 0) {
		$this->db->where('id', (int)$custom_cover_review_id);
		$this->db->update('custom_cover_review',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
