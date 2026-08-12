<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Font_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('font.*');

		$this->db->where('font.id', (int)$id);
		$this->db->where('font._deleted', 0);
		return $this->db->get('font')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('font.*');

		if (isset($data['name'])) {
			$this->db->where('font.name', $data['name']);
		}

		if (isset($data['tags'])) {
			$this->db->where_in('font.tags', $data['tags']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('font.name', $data['search'], 'both');
			$this->db->or_like('font.tags', $data['search'], 'both');
			$this->db->or_like('font.image', $data['search'], 'both');
			$this->db->or_like('font.url', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('font._deleted', 0);

		$this->db->from('font');

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
			'font.id',
			'font.name',
			'font.date_added',
			'font.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'font.id';
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
		$this->db->insert('font', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('font_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('font', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('font_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('font',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('font', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('font_updated_successfully'));
	}
}
