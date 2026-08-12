<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TeacherCertificateTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('teacher_certificate_template.*');
		$this->db->where('teacher_certificate_template.id', (int)$id);
		$this->db->where('teacher_certificate_template._deleted', 0);

		return $this->db->get('teacher_certificate_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('teacher_certificate_template.*');

		if (isset($data['event_id'])) {
			$this->db->where('teacher_certificate_template.event_id', (int)$data['event_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('teacher_certificate_template.type', $data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('teacher_certificate_template.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('teacher_certificate_template.name', $data['search'], 'after');
			$this->db->or_like('teacher_certificate_template.type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('teacher_certificate_template._deleted', 0);

		$this->db->from('teacher_certificate_template');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'teacher_certificate_template.name',
			'teacher_certificate_template.status',
			'teacher_certificate_template.date_added',
			'teacher_certificate_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'teacher_certificate_template.id';
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
		$this->db->insert('teacher_certificate_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$teacher_certificate_template_id = $this->db->insert_id();

		return $teacher_certificate_template_id;
	}

	public function edit($teacher_certificate_template_id = 0, $data = []) {
		$this->db->where('id', (int)$teacher_certificate_template_id);
		$this->db->update('teacher_certificate_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($teacher_certificate_template_id = 0) {
		$this->db->where('id', (int)$teacher_certificate_template_id);
		$this->db->update('teacher_certificate_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('teacher_certificate_template', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
