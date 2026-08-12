<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Unsubscribed_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($unsubscribed_id = 0) {
		$this->db->select('unsubscribed.*');

		$this->db->where('unsubscribed.id', (int)$unsubscribed_id);
		$this->db->where('unsubscribed._deleted', 0);

		return $this->db->get('unsubscribed')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('unsubscribed.*');

		if (isset($data['email'])) {
			$this->db->where('email', $data['email']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('unsubscribed.email', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('unsubscribed._deleted', 0);

		$this->db->from('unsubscribed');

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
			'book.email',
			'unsubscribed.date_added',
			'unsubscribed.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'unsubscribed.date_added';
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
		$this->db->insert('unsubscribed', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$unsubscribed_id = $this->db->insert_id();

		return $unsubscribed_id;
	}

	public function edit($unsubscribed_id = 0, $data = []) {
		$this->db->where('id', (int)$unsubscribed_id);
		$this->db->update('unsubscribed', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($unsubscribed_id = 0) {
		$this->db->where('id', (int)$unsubscribed_id);
		$this->db->update('unsubscribed',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
