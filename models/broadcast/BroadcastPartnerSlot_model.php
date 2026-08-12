<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BroadcastPartnerSlot_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($broadcast_partner_slots_id = 0) {
		$this->db->select('broadcast_partner_slots.*');

		$this->db->where('broadcast_partner_slots.id', (int)$broadcast_partner_slots_id);
		$this->db->where('broadcast_partner_slots._deleted', 0);

		return $this->db->get('broadcast_partner_slots')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('broadcast_partner_slots.*');

		if (isset($data['user_id'])) {
			$this->db->where('broadcast_partner_slots.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('broadcast_partner_slots.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('broadcast_partner_slots.rank', $data['search'], 'after');
		}

		$this->db->where('broadcast_partner_slots._deleted', 0);

		$this->db->from('broadcast_partner_slots');

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
			'broadcast_partner_slots.rank',
			'broadcast_partner_slots.status',
			'broadcast_partner_slots.start_date',
			'broadcast_partner_slots.date_added',
			'broadcast_partner_slots.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'broadcast_partner_slots.id';
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
		$this->db->insert('broadcast_partner_slots', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$broadcast_partner_slots_id = $this->db->insert_id();

		return $broadcast_partner_slots_id;
	}

	public function edit($broadcast_partner_slots_id = 0, $data = []) {
		$this->db->where('id', (int)$broadcast_partner_slots_id);
		$this->db->update('broadcast_partner_slots', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($broadcast_partner_slots_id = 0) {
		$this->db->where('id', (int)$broadcast_partner_slots_id);
		$this->db->update('broadcast_partner_slots',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
