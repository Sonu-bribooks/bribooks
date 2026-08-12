<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CoverLocale_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('cover_locale.*');

		$this->db->where('cover_locale.id', (int)$id);
		$this->db->where('cover_locale._deleted', 0);

		return $this->db->get('cover_locale')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('cover_locale.*');

		if (isset($data['cover_id'])) {
			$this->db->where('cover_locale.cover_id', (int)$data['cover_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('cover_locale.status', (int)$data['status']);
		}

		$this->db->where('cover_locale._deleted', 0);

		$this->db->from('cover_locale');

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
			'cover_locale.status',
			'cover_locale.sort_order',
			'cover_locale.date_added',
			'cover_locale.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'cover_locale.date_added';
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
		$this->db->insert('cover_locale', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('cover_locale', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('cover_locale',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
