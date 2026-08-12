<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CertificateTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($certificate_template_id = 0) {
		$this->db->select('certificate_template.*');
		$this->db->where('certificate_template.id', (int)$certificate_template_id);
		$this->db->where('certificate_template._deleted', 0);

		return $this->db->get('certificate_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('certificate_template.*');

		if (isset($data['event_id'])) {
			$this->db->where('certificate_template.event_id', (int)$data['event_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('certificate_template.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['challenge_type'])) {
			$this->db->where('certificate_template.challenge_type', $data['challenge_type']);
		}

		if (isset($data['has_rank'])) {
			$this->db->where('certificate_template.has_rank', (int)$data['has_rank']);
		}

		if (isset($data['achievement'])) {
			$this->db->where('certificate_template.achievement', (int)$data['achievement']);
		}

		if (isset($data['type'])) {
			$this->db->where('certificate_template.type', $data['type']);
		}

		if (isset($data['is_jury'])) {
			$this->db->where('certificate_template.is_jury', (int)$data['is_jury']);
		}

		if (!empty($data['country_code'])) {
			$this->db->where('certificate_template.country_code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->db->where('certificate_template.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('certificate_template.name', $data['search'], 'after');
			$this->db->or_like('certificate_template.type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('certificate_template._deleted', 0);

		$this->db->from('certificate_template');

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
			'certificate_template.name',
			'certificate_template.book_sold',
			'certificate_template.status',
			'certificate_template.date_added',
			'certificate_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'certificate_template.id';
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
		$this->db->insert('certificate_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$certificate_template_id = $this->db->insert_id();

		return $certificate_template_id;
	}

	public function edit($certificate_template_id = 0, $data = []) {
		$this->db->where('id', (int)$certificate_template_id);
		$this->db->update('certificate_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($certificate_template_id = 0) {
		$this->db->where('id', (int)$certificate_template_id);
		$this->db->update('certificate_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('certificate_template', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
