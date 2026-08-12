<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AppUserRedirect_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('app_user_redirect.*');

		$this->db->where('app_user_redirect.id', (int)$id);
		$this->db->where('app_user_redirect._deleted', 0);

		return $this->db->get('app_user_redirect')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('app_user_redirect.*');

		if (isset($data['user_id'])) {
			$this->db->where('app_user_redirect.user_id', $data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('app_user_redirect.status', (int)$data['status']);
		}

		$this->db->where('app_user_redirect._deleted', 0);

		$this->db->from('app_user_redirect');

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
			'app_user_redirect.status',
			'app_user_redirect.date_added',
			'app_user_redirect.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'app_user_redirect.date_added';
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
		$this->db->insert('app_user_redirect', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('app_user_redirect', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('app_user_redirect',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function deleteByUserId($user_id = 0) {
		$this->db->where('user_id', (int)$user_id);
		$this->db->update('app_user_redirect',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByUserId($user_id = 0) {
		$this->db->select('app_user_redirect.*');

		$this->db->where('app_user_redirect.user_id', (int)$user_id);
		$this->db->where('app_user_redirect.date_added >', date('Y-m-d H:i:s', strtotime('-1 hours')));
		$this->db->where('app_user_redirect._deleted', 0);
		$this->db->order_by('app_user_redirect.id', 'DESC');

		return $this->db->get('app_user_redirect')->row_array();
	}
}
