<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CustomThemeBookLogs_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('custom_theme_book_review_logs.*');

		$this->db->where('custom_theme_book_review_logs.id', (int)$id);
		$this->db->where('custom_theme_book_review_logs._deleted', 0);

		return $this->db->get('custom_theme_book_review_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('custom_theme_book_review_logs.*');

		if (!empty($data['user_id'])) {
			$this->db->where('custom_theme_book_review_logs.manager_id', (int)$data['user_id']);
		}

        if (!empty($data['book_id'])) {
			$this->db->where('custom_theme_book_review_logs.book_id', (int)$data['book_id']);
		}

		if (!empty($data['version'])) {
			$this->db->where('custom_theme_book_review.version', (int)$data['version']);
		}

		if (!empty($data['status'])) {
			$this->db->where('custom_theme_book_review_logs.status', (int)$data['status']);
		}

		$this->db->where('custom_theme_book_review_logs._deleted', 0);

		$this->db->from('custom_theme_book_review_logs');

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
			'custom_theme_book_review_logs.sort_order',
			'custom_theme_book_review_logs.date_added',
			'custom_theme_book_review_logs.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'custom_theme_book_review_logs.id';
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
		$this->db->insert('custom_theme_book_review_logs', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$custom_theme_book_review_logs_id = $this->db->insert_id();

		return $custom_theme_book_review_logs_id;
	}

	public function edit($custom_theme_book_review_logs_id = 0, $data = []) {
		$this->db->where('id', (int)$custom_theme_book_review_logs_id);
		$this->db->update('custom_theme_book_review_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($custom_theme_book_review_logs_id = 0) {
		$this->db->where('id', (int)$custom_theme_book_review_logs_id);
		$this->db->update('custom_theme_book_review_logs',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
