<?php defined('BASEPATH') or exit('No direct script access allowed');

class EventSchoolInvite_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		$this->db->where('_deleted', 0);

		return $this->db->get('event_school_invite')->row_array();
	}

	public function get_all($data = []) {
		if (empty($data)) return false;

		$this->db->select('event_school_invite.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_school_invite.event_id', (int)$data['event_id']);
		}

		if (isset($data['template_id'])) {
			$this->db->where('event_school_invite.template_id', (int)$data['template_id']);
		}

		if (isset($data['challenge_id'])) {
			$this->db->where('event_school_invite.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['challenge_type'])) {
			$this->db->where('event_school_invite.challenge_type', $data['challenge_type']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('event_school_invite.site_id', (int)$data['site_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('event_school_invite.school_id', (int)$data['school_id']);
		}

		if (!empty($data['code'])) {
			$this->db->where('event_invite_guest.code', $data['code']);
		}

		if (isset($data['status'])) {
			$this->db->where('event_school_invite.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->db->where('event_school_invite.verified', (int)$data['verified']);
		}

		if (isset($data['is_pdf'])) {
			if (!empty($data['is_pdf'])) {
				$this->db->where('event_school_invite.pdf IS NOT NULL', null, false);
			} else {
				$this->db->where('event_school_invite.pdf IS NULL', null, false);
			}
		}

		$this->db->where('event_school_invite._deleted', 0);

		$this->db->from('event_school_invite');

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
			'event_school_invite.id',
			'event_school_invite.status',
			'event_school_invite.date_added',
			'event_school_invite.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_school_invite.id';
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
		$this->db->insert('event_school_invite', $data + [
			'status'		=> 1,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_school_invite', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_school_invite',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCode($code = '') {
		$this->db->select('*');
		$this->db->where('code', $code);
		$this->db->where('_deleted', 0);
		$this->db->from('event_school_invite');
		return $this->db->get()->row_array();
	}
}
