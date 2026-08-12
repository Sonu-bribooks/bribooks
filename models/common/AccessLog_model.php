<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AccessLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($access_log_id = 0) {
		$this->db->select('access_log.*');

		$this->db->where('access_log.id', (int)$access_log_id);
		$this->db->where('access_log._deleted', 0);

		return $this->db->get('access_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('access_log.*');

		if (isset($data['module'])) {
			$this->db->where('access_log.module', $data['module']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('access_log.user_id', (int)$data['user_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('access_log.ip', $data['ip']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(access_log.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		$this->db->where('access_log._deleted', 0);

		$this->db->from('access_log');

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
			'access_log.date_added',
			'access_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'access_log.id';
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
		$this->db->insert('access_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$access_log_id = $this->db->insert_id();

		return $access_log_id;
	}

	public function edit($access_log_id = 0, $data = []) {
		$this->db->where('id', (int)$access_log_id);
		$this->db->update('access_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($access_log_id = 0) {
		$this->db->where('id', (int)$access_log_id);
		$this->db->update('access_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
