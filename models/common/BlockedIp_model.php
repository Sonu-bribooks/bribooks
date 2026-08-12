<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BlockedIp_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('blocked_ips.*');

		$this->db->where('blocked_ips.id', (int)$id);
		$this->db->where('blocked_ips._deleted', 0);
		return $this->db->get('blocked_ips')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('blocked_ips.*');

		if (!empty($data['ip'])) {
			$this->db->where('blocked_ips.ip', $data['ip']);
		}

		if (!empty($data['attempt'])) {
			$this->db->where('blocked_ips.attempt', $data['attempt']);
		}

		if (!empty($data['blocked'])) {
			$this->db->where('blocked_ips.blocked', $data['blocked']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('blocked_ips.ip', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->from('blocked_ips');

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
			'blocked_ips.id',
			'blocked_ips.date_added',
			'blocked_ips.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'blocked_ips.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('blocked_ips', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('blocked_ips', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['blocked']);
			$this->db->where('id', (int)$id);
			$this->db->update('blocked_ips', [
				'blocked'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}
}
