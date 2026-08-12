<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Language_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($language_id = 0) {
		$this->db->select('language.*');

		$this->db->where('language.id', (int)$language_id);
		$this->db->where('language._deleted', 0);

		return $this->db->get('language')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('language.*');

		if (isset($data['name'])) {
			$this->db->where('language.name', $data['name']);
		}

		if (isset($data['code'])) {
			$this->db->where('language.code', $data['code']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('language.name', $data['search'], 'after');
			$this->db->or_like('language.code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('language._deleted', 0);

		$this->db->from('language');

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
			'language.id',
			'language.name',
			'language.code',
			'language.date_added',
			'language.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'language.id';
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('language', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$language_id = $this->db->insert_id();

		return $language_id;
	}

	public function edit($language_id = 0, $data = []) {
		$this->db->where('id', (int)$language_id);
		$this->db->update('language', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($language_id = 0) {
		$this->db->where('id', (int)$language_id);
		$this->db->update('language',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
