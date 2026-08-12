<?php defined('BASEPATH') or exit('No direct script access allowed');

class UserDetailsInvite_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('user_details_nyaf_invites')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('user_details_nyaf_invites', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('user_details_nyaf_invites', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getNyafAuthorInvite($data = []) {
		$this->db->select('*, user_details_nyaf_invites.status AS invite_status, user_details_nyaf_invites.date_modified AS invite_date_added');

		if (isset($data['status'])) {
			$this->db->where('user_details_nyaf_invites.status', (int)$data['status']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('user_details_nyaf_invites.event_id', (int)$data['event_id']);
		}

		if (isset($data['verified'])) {
			$this->db->where(sprintf('user_details_nyaf_invites.user_id IN (SELECT user_details_nyaf_guest.user_id from user_details_nyaf_guest WHERE user_details_nyaf_guest.event_id = user_details_nyaf_invites.event_id AND user_details_nyaf_guest.user_id = user_details_nyaf_invites.user_id AND user_details_nyaf_guest.verified = %s)', (int)$data['verified']));
		}

		if (!empty($data['empty_book'])) {
			$this->db->where('user_details_nyaf_invites.book_id', 0);
		}

		
		if (!empty($data['not_empty_book'])) {
			$this->db->where('user_details_nyaf_invites.book_id !=', 0);
		}

		if (!empty($data['search'])) {
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.source', $data['search'], 'after');
		}

		$this->db->join('users', 'users.id = user_details_nyaf_invites.user_id', 'left');
		$this->db->from('user_details_nyaf_invites');

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
			'users.amount',
			'users.status',
			'users.first_name',
			'users.date_added',
			'users.date_modified',
			'user_details_nyaf_invites.book_sold',
			'user_details_nyaf_invites.book_rank',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_details_nyaf_invites.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
