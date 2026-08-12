<?php defined('BASEPATH') or exit('No direct script access allowed');

class SchoolDetailsInvite_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = false) {
		if (!$id)
			return false;

		$this->db->select('*');
		$this->db->where('id', (int)$id);

		return $this->db->get('school_details_nyaf_invites')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('school_details_nyaf_invites', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_details_nyaf_invites', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getNyafSchoolInvite($data = []) {
		$this->db->select('*, school_details_nyaf_invites.status AS invite_status, school_details_nyaf_invites.date_modified AS invite_date_added');

		if (isset($data['event_id'])) {
			$this->db->where('school_details_nyaf_invites.event_id', (int)$data['event_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_details_nyaf_invites.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->db->where(sprintf('school_details_nyaf_invites.site_id IN (SELECT school_details_nyaf_guest.site_id from school_details_nyaf_guest WHERE school_details_nyaf_guest.event_id = school_details_nyaf_invites.event_id AND school_details_nyaf_guest.site_id = school_details_nyaf_invites.site_id AND school_details_nyaf_guest.verified = %s)', (int)$data['verified']));
		}

		if (!empty($data['search'])) {
			$this->db->like('site.name', $data['search'], 'after');
			$this->db->or_like('site.owner_email', $data['search'], 'after');
			$this->db->or_like('site.owner_mobile', $data['search'], 'after');
			$this->db->or_like('site.site_code', $data['search'], 'after');
		}

		$this->db->join('site', 'site.id = school_details_nyaf_invites.site_id', 'left');
		$this->db->from('school_details_nyaf_invites');

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
			'site.id',
			'site.name',
			'school_details_nyaf_invites.date_added',
			'school_details_nyaf_invites.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_details_nyaf_invites.id';
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
