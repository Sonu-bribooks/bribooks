<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CustomThemeLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($custom_theme_log_id = 0) {
		$this->db->select('custom_theme_log.*');

		$this->db->where('custom_theme_log.id', (int)$custom_theme_log_id);
		$this->db->where('custom_theme_log._deleted', 0);

		return $this->db->get('custom_theme_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('custom_theme_log.*');

		if (isset($data['user_id'])) {
			$this->db->where('custom_theme_log.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('custom_theme_log.status', (int)$data['status']);
		}

		$this->db->where('custom_theme_log._deleted', 0);

		$this->db->from('custom_theme_log');

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
			'custom_theme_log.sort_order',
			'custom_theme_log.date_added',
			'custom_theme_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'custom_theme_log.id';
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
		$this->db->insert('custom_theme_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$custom_theme_log_id = $this->db->insert_id();

		return $custom_theme_log_id;
	}

	public function edit($custom_theme_log_id = 0, $data = []) {
		$this->db->where('id', (int)$custom_theme_log_id);
		$this->db->update('custom_theme_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($custom_theme_log_id = 0) {
		$this->db->where('id', (int)$custom_theme_log_id);
		$this->db->update('custom_theme_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
