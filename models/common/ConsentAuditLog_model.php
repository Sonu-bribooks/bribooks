<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ConsentAuditLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($consent_audit_log_id = 0) {
		$this->db->select('consent_audit_log.*');

		$this->db->where('consent_audit_log.id', (int)$consent_audit_log_id);
		$this->db->where('consent_audit_log._deleted', 0);

		return $this->db->get('consent_audit_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('consent_audit_log.*');

		if (isset($data['version'])) {
			$this->db->where('consent_audit_log.version', $data['version']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('consent_audit_log.user_id', (int)$data['user_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('consent_audit_log.ip', $data['ip']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(consent_audit_log.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		$this->db->from('consent_audit_log');

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
			'consent_audit_log.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'consent_audit_log.id';
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
		$this->db->insert('consent_audit_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
		]);

		$consent_audit_log_id = $this->db->insert_id();

		return $consent_audit_log_id;
	}

	public function edit($consent_audit_log_id = 0, $data = []) {
		$this->db->where('id', (int)$consent_audit_log_id);
		$this->db->update('consent_audit_log', $data);
	}

	public function delete($consent_audit_log_id = 0) {
		$this->db->where('id', (int)$consent_audit_log_id);
		$this->db->update('consent_audit_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
