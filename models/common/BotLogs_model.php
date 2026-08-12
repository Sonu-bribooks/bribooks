<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BotLogs_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($log_id = 0) {
		$this->db->select('bot_logs.*');

		$this->db->where('bot_logs.id', (int)$log_id);
		$this->db->where('bot_logs._deleted', 0);

		return $this->db->get('bot_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('bot_logs.*');

		if (isset($data['user_id'])) {
			$this->db->where('bot_logs.user_id', (int)$data['user_id']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(bot_logs.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		$this->db->where('bot_logs._deleted', 0);
		$this->db->from('bot_logs');

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
			'bot_logs.date_added',
			'bot_logs.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'bot_logs.date_added';
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
		$this->db->insert('bot_logs', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$log_id = $this->db->insert_id();

		return $log_id;
	}

	public function edit($log_id = 0, $data = []) {
		$this->db->where('id', $log_id);
		$this->db->update('bot_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($log_id = 0) {
		$this->db->where('id', $log_id);
		$this->db->update('bot_logs',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
