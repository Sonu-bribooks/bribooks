<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CustomTheme_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($custom_theme_id = 0) {
		$this->db->select('custom_theme.*');

		$this->db->where('custom_theme.id', (int)$custom_theme_id);
		$this->db->where('custom_theme._deleted', 0);

		return $this->db->get('custom_theme')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('custom_theme.*');

		if (isset($data['user_id'])) {
			$this->db->where('custom_theme.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('custom_theme.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('custom_theme.image', $data['search'], 'after');
		}

		$this->db->where('custom_theme._deleted', 0);

		$this->db->from('custom_theme');

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
			'custom_theme.sort_order',
			'custom_theme.date_added',
			'custom_theme.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'custom_theme.id';
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
		if(empty($data['user_id']))
			return;

		$this->db->insert('custom_theme', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$custom_theme_id = $this->db->insert_id();

		return $custom_theme_id;
	}

	public function edit($custom_theme_id = 0, $data = []) {
		$this->db->where('id', (int)$custom_theme_id);
		$this->db->update('custom_theme', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($custom_theme_id = 0) {
		$this->db->where('id', (int)$custom_theme_id);
		$this->db->update('custom_theme',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
