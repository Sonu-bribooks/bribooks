<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ThemeLocale_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('theme_locale.*');

		$this->db->where('theme_locale.id', (int)$id);
		$this->db->where('theme_locale._deleted', 0);

		return $this->db->get('theme_locale')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('theme_locale.*');

		if (isset($data['theme_id'])) {
			$this->db->where('theme_locale.theme_id', (int)$data['theme_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('theme_locale.status', (int)$data['status']);
		}

		$this->db->where('theme_locale._deleted', 0);

		$this->db->from('theme_locale');

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
			'theme_locale.status',
			'theme_locale.sort_order',
			'theme_locale.date_added',
			'theme_locale.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'theme_locale.date_added';
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
		$this->db->insert('theme_locale', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('theme_locale', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('theme_locale',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
