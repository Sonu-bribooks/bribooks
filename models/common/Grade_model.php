<?php defined('BASEPATH') or exit('No direct script access allowed');

class Grade_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($site_grade_id = 0) {
		$this->db->select('site_grade.*');

		$this->db->where('site_grade.id', (int)$site_grade_id);
		$this->db->where('site_grade._deleted', 0);

		$this->db->join('site', 'site.id = site_grade.site_id', 'left');

		return $this->db->get('site_grade')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('site_grade.*');

		if (isset($data['site_id'])) {
			$this->db->where('site_grade.site_id', (int)$data['site_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('site_grade.name', (int)$data['name']);
		}

		if (isset($data['site_code'])) {
			$this->db->like('site.site_code', $data['site_code'], 'after');
		}

		if (isset($data['status'])) {
			$this->db->where('site_grade.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('site_grade.name', $data['search'], 'after');
			$this->db->like('site.site_code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('site_grade._deleted', 0);

		$this->db->join('site', 'site.id = site_grade.site_id', 'left');
		$this->db->from('site_grade');

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
			'site_grade.name',
			'site_grade.sort_order',
			'site_grade.status',
			'site_grade.date_added',
			'site_grade.date_modified',
		];

		if (isset($data['sort']) && $data['sort'] == 'site_grade.name') {
			$sort = 'CAST(site_grade.name as UNSIGNED)';
		} else if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'site_grade.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function check_exists($data = []) {
		$this->db->where("name", $data['name']);
		$this->db->where("site_id", $data['site_id']);
		$this->db->where('_deleted', 0);
		$result = $this->db->get('site_grade');

		if ($result->num_rows() > 0) {
			$result = $result->result_array();
			return $result[0]['id'];
		} else {
			return false;
		}
	}

	public function add($data = []) {
		$id = $this->check_exists($data);
		if (!$id) {
			$this->db->insert('site_grade',  [
				'site_id'			=> $data['site_id'],
				'name'				=> $data['name'],
				'status'			=> 1,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$site_grade_id = $this->db->insert_id();
			return $site_grade_id;
		} else {
			return $id;
		}
	}

	public function edit($site_grade_id = 0, $data = []) {
		$this->db->where('id', (int)$site_grade_id);
		$this->db->update('site_grade', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($site_grade_id = 0) {
		$this->db->where('id', (int)$site_grade_id);
		$this->db->update('site_grade',  [
			'status'		  => 0,
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function site_grade($data = []) {
		$this->db->select('site_grade.*,site_section.name as section_name');

		if (isset($data['site_id'])) {
			$this->db->where('site_grade.site_id', (int)$data['site_id']);
		}

		$this->db->where('site_grade._deleted', 0);

		$this->db->join('site_section', 'site_section.grade_id = site_grade.id', 'left');
		$this->db->from('site_grade');

		$total = $this->db->count_all_results('', FALSE);

		$sort_data = [
			'site_grade.name',
			'site_grade.sort_order',
			'site_grade.status',
			'site_grade.date_added',
			'site_grade.date_modified',
		];
		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
