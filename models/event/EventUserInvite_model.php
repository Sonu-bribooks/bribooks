<?php defined('BASEPATH') or exit('No direct script access allowed');

class EventUserInvite_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		$this->db->where('_deleted', 0);

		return $this->db->get('event_user_invite')->row_array();
	}

	public function get_all($data = []) {
		if (empty($data)) return false;

		$this->db->select('event_user_invite.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_user_invite.event_id', (int)$data['event_id']);
		}

		if (isset($data['template_id'])) {
			$this->db->where('event_user_invite.template_id', (int)$data['template_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('event_user_invite.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['challenge_type'])) {
			$this->db->where('event_user_invite.challenge_type', $data['challenge_type']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_user_invite.book_id', (int)$data['book_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('event_user_invite.user_id', (int)$data['user_id']);
		}

		if (!empty($data['code'])) {
			$this->db->where('event_user_invite.code', $data['code']);
		}

		if (isset($data['status'])) {
			$this->db->where('event_user_invite.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->db->where('event_user_invite.verified', (int)$data['verified']);
		}

		if (isset($data['is_jury'])) {
			$this->db->where('event_user_invite.is_jury', (int)$data['is_jury']);
		}

		if (isset($data['is_pdf'])) {
			if (!empty($data['is_pdf'])) {
				$this->db->where('event_user_invite.pdf IS NOT NULL', null, false);
			} else {
				$this->db->where('event_user_invite.pdf IS NULL', null, false);
			}
		}

		if (!empty($data['is_active_event'])) {
			$this->db->where(
				vsprintf('event_user_invite.event_id IN (SELECT id FROM event where start_date <= \'%s\' AND end_date >= \'%s\')', [
					date('Y-m-d H:i:s'),
					date('Y-m-d H:i:s'),
				])
			);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_user_invite._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->join('book', 'book.id = event_user_invite.book_id', 'left');
		}

		$this->db->from('event_user_invite');

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
			'event_user_invite.id',
			'event_user_invite.status',
			'event_user_invite.date_added',
			'event_user_invite.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_user_invite.id';
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
		$this->db->insert('event_user_invite', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_user_invite', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_user_invite',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCode($code = '') {
		$this->db->select('*');
		$this->db->where('code', $code);
		$this->db->where('_deleted', 0);
		$this->db->from('event_user_invite');
		return $this->db->get()->row_array();
	}
}
