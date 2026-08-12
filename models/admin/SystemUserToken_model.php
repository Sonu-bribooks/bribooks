<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SystemUserToken_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($system_user_token_id = 0) {
		$this->db->select('system_user_token.*');

		$this->db->where('system_user_token.id', (int)$system_user_token_id);
		$this->db->where('system_user_token._deleted', 0);

		return $this->db->get('system_user_token')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('system_user_token.*');

		if (isset($data['user_id'])) {
			$this->db->where('system_user_token.user_id', (int)$data['user_id']);
		}

		if (isset($data['token'])) {
			$this->db->where('system_user_token.token', $data['token']);
		}

		$this->db->where('system_user_token._deleted', 0);

		$this->db->from('system_user_token');

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
			'system_user_token.id',
			'system_user_token.date_added',
			'system_user_token.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'system_user_token.id';
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
		$this->db->insert('system_user_token', $data + [
			'ip'			=> $this->input->ip_address(),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$system_user_token_id = $this->db->insert_id();

		return $system_user_token_id;
	}

	public function edit($system_user_token_id = 0, $data = []) {
		$this->db->where('id', (int)$system_user_token_id);
		$this->db->update('system_user_token', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($system_user_token_id = 0) {
		$this->db->where('id', (int)$system_user_token_id);
		$this->db->update('system_user_token', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
