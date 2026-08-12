<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TeacherLead_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($teacher_lead_id = 0) {
		$this->db->select('teacher_lead.*');

		$this->db->where('teacher_lead.id', (int)$teacher_lead_id);
		$this->db->where('teacher_lead._deleted', 0);

		return $this->db->get('teacher_lead')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('teacher_lead.*');

		if (isset($data['user_id'])) {
			$this->db->where('teacher_lead.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('teacher_lead.status', (int)$data['status']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('teacher_lead.event_id', (int)$data['event_id']);
		}

		if (isset($data['source_not_null'])) {
			$this->db->where('teacher_lead.utm_source !=', $data['source_not_null']);
		}

		if (isset($data['utm_source'])) {
			$this->db->where('teacher_lead.utm_source', $data['utm_source']);
		}

		if (isset($data['email_mobile_verified'])) {
			$this->db->where('(teacher_lead.mobile_verified = ' . (int)$data['email_mobile_verified'] . ' OR teacher_lead.email_verified = ' . (int)$data['email_mobile_verified'] . ')');
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('teacher_lead.name', $data['search'], 'after');
			$this->db->or_like('teacher_lead.zipcode', $data['search'], 'after');
			$this->db->or_like('teacher_lead.mobile', $data['search'], 'after');
			$this->db->or_like('teacher_lead.city', $data['search'], 'after');
			$this->db->or_like('teacher_lead.state', $data['search'], 'after');
			$this->db->or_like('teacher_lead.country', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('teacher_lead._deleted', 0);

		$this->db->from('teacher_lead');

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
			'teacher_lead.name',
			'teacher_lead.status',
			'teacher_lead.date_added',
			'teacher_lead.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'teacher_lead.id';
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
		$this->db->insert('teacher_lead', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$teacher_lead_id = $this->db->insert_id();

		return $teacher_lead_id;
	}

	public function edit($teacher_lead_id = 0, $data = []) {
		$this->db->where('id', (int)$teacher_lead_id);
		$this->db->update('teacher_lead', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($teacher_lead_id = 0) {
		$this->db->where('id', (int)$teacher_lead_id);
		$this->db->update('teacher_lead',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
