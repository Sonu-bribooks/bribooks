<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BrochureTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('brochure_template.*');

		$this->db->where('brochure_template.id', (int)$id);
		$this->db->where('brochure_template._deleted', 0);

		return $this->db->get('brochure_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('brochure_template.*');

		if (isset($data['event_id'])) {
			$this->db->where('brochure_template.event_id', (int)$data['event_id']);
		}

        if (isset($data['type'])) {
			$this->db->where('brochure_template.type', $data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('brochure_template.status', (int)$data['status']);
		}

		if (isset($data['site_type'])) {
			$this->db->where('brochure_template.site_type', (int)$data['site_type']);
		}

		if (!empty($data['search'])) {
			$this->db->like('brochure_template.name', $data['search'], 'after');
			$this->db->or_where('brochure_template.id', $data['search'], 'after');
		}

		$this->db->where('brochure_template._deleted', 0);

		$this->db->from('brochure_template');

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
			'brochure_template.sort_order',
			'brochure_template.status',
			'brochure_template.date_added',
			'brochure_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'brochure_template.id';
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
		$this->db->insert('brochure_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$brochure_template = $this->db->insert_id();

		return $brochure_template;
	}

	public function edit($brochure_template = 0, $data = []) {
		$this->db->where('id', (int)$brochure_template);
		$this->db->update('brochure_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
		return true;
	}

	public function delete($brochure_template = 0) {
		$this->db->where('id', (int)$brochure_template);
		$this->db->update('brochure_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
