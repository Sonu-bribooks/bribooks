<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LeadVerificationCode_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('lead_verification_code.*');

		$this->db->where('lead_verification_code.id', (int)$id);
		$this->db->where('lead_verification_code._deleted', 0);

		return $this->db->get('lead_verification_code')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('lead_verification_code.*');

		if (!empty($data['event_id'])) {
			$this->db->where('lead_verification_code.event_id', (int)$data['event_id']);
		}

        if (!empty($data['lead_id'])) {
			$this->db->where('lead_verification_code.lead_id', (int)$data['lead_id']);
		}

        if (isset($data['type'])) {
			$this->db->where('lead_verification_code.type', $data['type']);
		}

		if (isset($data['code'])) {
			$this->db->where('lead_verification_code.code', $data['code']);
		}

		if (isset($data['status'])) {
			$this->db->where('lead_verification_code.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('lead_verification_code.name', $data['search'], 'after');
			$this->db->or_where('lead_verification_code.id', $data['search'], 'after');
		}

		$this->db->where('lead_verification_code._deleted', 0);

		$this->db->from('lead_verification_code');

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
			'lead_verification_code.name',
			'lead_verification_code.status',
			'lead_verification_code.date_added',
			'lead_verification_code.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'lead_verification_code.date_added';
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
		$this->db->insert('lead_verification_code', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$lead_verification_code = $this->db->insert_id();

		return $lead_verification_code;
	}

	public function edit($lead_verification_code = 0, $data = []) {
		$this->db->where('id', (int)$lead_verification_code);
		$this->db->update('lead_verification_code', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
		return true;
	}

	public function delete($lead_verification_code = 0) {
		$this->db->where('id', (int)$lead_verification_code);
		$this->db->update('lead_verification_code',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
