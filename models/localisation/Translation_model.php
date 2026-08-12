<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Translation_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($translation_id = 0) {
		$this->db->select('translation.*');

		$this->db->where('translation.id', (int)$translation_id);
		$this->db->where('translation._deleted', 0);

		return $this->db->get('translation')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('translation.*');

		if (isset($data['text'])) {
			$this->db->where('translation.text', $data['text']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('translation.text', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('translation._deleted', 0);

		$this->db->from('translation');

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
			'translation.id',
			'translation.text',
			'translation.date_added',
			'translation.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'translation.id';
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
		$this->db->insert('translation', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$translation_id = $this->db->insert_id();

		return $translation_id;
	}

	public function edit($translation_id = 0, $data = []) {
		$this->db->where('id', (int)$translation_id);
		$this->db->update('translation', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($translation_id = 0) {
		$this->db->where('id', (int)$translation_id);
		$this->db->update('translation',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
