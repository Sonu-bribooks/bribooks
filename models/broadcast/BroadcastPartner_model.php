<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BroadcastPartner_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($broadcast_partners_id = 0) {
		$this->db->select('broadcast_partners.*');

		$this->db->where('broadcast_partners.id', (int)$broadcast_partners_id);
		$this->db->where('broadcast_partners._deleted', 0);

		return $this->db->get('broadcast_partners')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('broadcast_partners.*');

		if (isset($data['status'])) {
			$this->db->where('broadcast_partners.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('broadcast_partners.name', $data['search'], 'after');
		}

		$this->db->where('broadcast_partners._deleted', 0);

		$this->db->from('broadcast_partners');

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
			'broadcast_partners.name',
			'broadcast_partners.status',
			'broadcast_partners.date_added',
			'broadcast_partners.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'broadcast_partners.id';
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
		$this->db->insert('broadcast_partners', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$broadcast_partners_id = $this->db->insert_id();

		return $broadcast_partners_id;
	}

	public function edit($broadcast_partners_id = 0, $data = []) {
		$this->db->where('id', (int)$broadcast_partners_id);
		$this->db->update('broadcast_partners', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($broadcast_partners_id = 0) {
		$this->db->where('id', (int)$broadcast_partners_id);
		$this->db->update('broadcast_partners',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
