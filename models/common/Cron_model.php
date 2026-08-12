<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($cron_id = 0) {
		if ($cron_id > 0) {
			$this->db->where('id', (int)$cron_id);
		}

		$this->db->where('_deleted', 0);

		return $this->db->get('cron')->row_array();
	}

	public function get_all($data = []) {
		if (!empty($data['code'])) {
			$this->db->where('code', $data['code']);
		}

		if (!empty($data['action'])) {
			$this->db->where('action', $data['action']);
		}

		if (isset($data['status'])) {
			$this->db->where('status', (int)$data['status']);
		}

		if (isset($data['type'])) {
			$this->db->where('type', (int)$data['type']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('site_id', (int)$data['site_id']);
		}

		$this->db->where('alert_date <=', date('Y-m-d H:i:s'));
		//$this->db->where('alert_date >', date('Y-m-d H:i:s', strtotime('-10 minutes')));
		$this->db->where('_deleted', 0);

		return $this->db->get('cron')->result_array();
	}

	public function get_all_distinct_site($data = []) {
		$this->db->select('id, site_id');
		$this->db->distinct('site_id');
		if (!empty($data['action'])) {
			$this->db->where('action', $data['action']);
		}

		if (isset($data['status'])) {
			$this->db->where('status', (int)$data['status']);
		}

		if (isset($data['type'])) {
			$this->db->where('type', (int)$data['type']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('site_id', (int)$data['site_id']);
		}

		$this->db->where('site_id > 0');
		$this->db->where('_deleted', 0);

		return $this->db->get('cron')->result_array();
	}

	public function add($data = []) {
		$data['data'] = (!empty($data['data']) && is_array($data['data'])) ? json_encode($data['data']) : ($data['data'] ?? '');

		if (empty($data['data']) || is_array($data['data'])) {
			unset($data['data']);
		}

		$add_data = [
			'code'			=> $data['code'],
			'action'		=> $data['action'],
			'data'			=> $data['data'],
			'site_id'		=> $data['site_id'] ?? ($this->config->item('site_id') ?? 0),
			'alert_date'	=> date('Y-m-d H:i:00', strtotime($data['alert_date'])),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		];

		if (isset($data['status'])) {
			$add_data['status'] = (int)$data['status'];
		}

		$this->db->insert('cron', $add_data);

		$cron_id = $this->db->insert_id();

		// $this->session->set_flashdata('flash_message', _l('cron_added_successfully'));

		return $cron_id;
	}

	public function edit($cron_id = 0, $data = []) {
		$data['data'] = (!empty($data['data']) && is_array($data['data'])) ? json_encode($data['data']) : ($data['data'] ?? '');

		if (empty($data['data']) || is_array($data['data'])) {
			unset($data['data']);
		}

		$this->db->update('cron', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$cron_id
		]);

		$this->session->set_flashdata('flash_message', _l('cron_edited_successfully'));
	}

	public function editByCode($code = null, $data = []) {
		$data['data'] = (!empty($data['data']) && is_array($data['data'])) ? json_encode($data['data']) : ($data['data'] ?? '');

		if (empty($data['data']) || is_array($data['data'])) {
			unset($data['data']);
		}

		$this->db->update('cron', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'code'			=> $code
		]);

		$this->session->set_flashdata('flash_message', _l('cron_edited_successfully'));
	}

	public function getByCode($code = '') {
		$this->db->where('code', $code);
		$this->db->where('_deleted', 0);

		return $this->db->get('cron')->row_array();
	}
}
