<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolCertificate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($certificate_id = 0) {
		$this->db->select('school_certificates.*');

		$this->db->where('school_certificates.id', (int)$certificate_id);
		$this->db->where('school_certificates._deleted', 0);

		return $this->db->get('school_certificates')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('school_certificates.*');

		if (isset($data['event_id'])) {
			$this->db->where('school_certificates.event_id', (int)$data['event_id']);
		}

		if (isset($data['school_certificate_template_id'])) {
			$this->db->where('school_certificates.school_certificate_template_id', (int)$data['school_certificate_template_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('school_certificates.site_id', (int)$data['site_id']);
		}

		if (isset($data['code'])) {
			$this->db->where('school_certificates.unique_id', $data['code']);
		}

		if (isset($data['name'])) {
			$this->db->where('school_certificates.name', $data['name']);
		}

		if (isset($data['achievement'])) {
			$this->db->where('school_certificates.achievement', (int)$data['achievement']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_certificates.status', (int)$data['status']);
		}

		$this->db->where('school_certificates._deleted', 0);

		$this->db->from('school_certificates');

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
			'school_certificates.id',
			'school_certificates.name',
			'school_certificates.status',
			'school_certificates.date_added',
			'school_certificates.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_certificates.id';
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
		$this->db->insert('school_certificates', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$certificate_id = $this->db->insert_id();

		$unique_id = 'BB/' . sprintf('%08d', $certificate_id) . '/' . (!empty($data['unique_id']) ? sprintf('%02d', $data['unique_id']) : '01') ;

		log_kb([
			'unique_id' => $unique_id,
		]);

		self::edit($certificate_id, [
			'unique_id' => $unique_id
		]);

		return $certificate_id;
	}

	public function edit($certificate_id = 0, $data = []) {
		$this->db->where('id', (int)$certificate_id);
		$this->db->update('school_certificates', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($certificate_id = 0) {
		$this->db->where('id', (int)$certificate_id);
		$this->db->update('school_certificates',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCode($code = '') {
		$this->db->select('school_certificates.*');

		$this->db->where('unique_id', $code);
		// $this->db->where('school_certificates._deleted', 0);

		return $this->db->get('school_certificates')->row_array();
	}
}
