<?php defined('BASEPATH') or exit('No direct script access allowed');

class Consent_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($consent_id = 0) {
		$this->db->select('consent.*');

		$this->db->where('consent.id', (int)$consent_id);
		$this->db->where('consent._deleted', 0);

		return $this->db->get('consent')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('consent.*');

		if (isset($data['type'])) {
			$this->db->where('consent.type', (int)$data['type']);
		}

		if (isset($data['title'])) {
			$this->db->where('consent.title', (int)$data['title']);
		}

		if (isset($data['version'])) {
			$this->db->where('consent.version', $data['version']);
		}

		if (isset($data['status'])) {
			$this->db->where('consent.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('consent.type', $data['search'], 'after');
			$this->db->or_like('consent.title', $data['search'], 'after');
            $this->db->or_like('consent.version', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('consent._deleted', 0);
		$this->db->from('consent');

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
			'consent.id',
			'consent.type',
			'consent.status',
			'consent.date_added',
			'consent.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'consent.date_modified';
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
		$this->db->insert('consent', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$consent_id = $this->db->insert_id();

		return $consent_id;
	}

	public function edit($consent_id = 0, $data = []) {
		$this->db->where('id', (int)$consent_id);
		$this->db->update('consent', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($consent_id = 0) {
		$this->db->where('id', (int)$consent_id);
		$this->db->update('consent',  [
			'status'		  => 0,
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

    public function getActiveConsent() {
		$this->db->select('consent.*');

		$this->db->where('consent.status', 1);
		$this->db->where('consent._deleted', 0);

		return $this->db->get('consent')->row_array();
	}
}
