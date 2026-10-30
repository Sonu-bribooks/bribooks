<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolCertificateMessageTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($school_certificate_message_template = 0) {
		$this->db->select('school_certificate_message_template.*');

		$this->db->where('school_certificate_message_template.id', (int)$school_certificate_message_template);
		$this->db->where('school_certificate_message_template._deleted', 0);

		return $this->db->get('school_certificate_message_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('school_certificate_message_template.*');

		if (isset($data['event_id'])) {
			$this->db->where('school_certificate_message_template.event_id', (int)$data['event_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('school_certificate_message_template.type', $data['type']);
		}

		if (isset($data['min_published_le'])) {
			$this->db->where('school_certificate_message_template.min_published <= ', $data['min_published_le']);
		}

		if (isset($data['max_published_lt'])) {
			$this->db->where('school_certificate_message_template.max_published > ', $data['max_published_lt']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('school_certificate_message_template.country_code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_certificate_message_template.status', (int)$data['status']);
		}

		if (isset($data['fomo'])) {
			$this->db->where('school_certificate_message_template.fomo', (int)$data['fomo']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('school_certificate_message_template.name', $data['search'], 'after');
			$this->db->or_where('school_certificate_message_template.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('school_certificate_message_template._deleted', 0);

		$this->db->from('school_certificate_message_template');

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
			'school_certificate_message_template.name',
			'school_certificate_message_template.sort_order',
			'school_certificate_message_template.status',
			'school_certificate_message_template.date_added',
			'school_certificate_message_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_certificate_message_template.date_added';
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
		$this->db->insert('school_certificate_message_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$school_certificate_message_template = $this->db->insert_id();

		return $school_certificate_message_template;
	}

	public function edit($school_certificate_message_template = 0, $data = []) {
		$this->db->where('id', (int)$school_certificate_message_template);
		$this->db->update('school_certificate_message_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
		return true;
	}

	public function delete($school_certificate_message_template = 0) {
		$this->db->where('id', (int)$school_certificate_message_template);
		$this->db->update('school_certificate_message_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('school_certificate_message_template', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
