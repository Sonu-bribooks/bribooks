<?php defined('BASEPATH') OR exit('No direct script access allowed');

class QualifiedSchool_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get_all($data = []) {
		$this->db->select('event_site.event_id, event_site.site_id, site.name as school_name, site.city_id, site.state_id, site.site_code');

		if (!empty($data['city_id'])) {
			$this->db->where('site.city_id', (int)$data['city_id']);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('site.state_id', (int)$data['state_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);

			if (!empty($data['type'])) {
				$this->db->where(sprintf('event_site.site_id NOT IN (select school_id from school_rank_%s where event_id = %s and _deleted = 0)', $data['type'], $data['event_id']));
			}
		}

		if (!empty($data['site_id'])) {
			$this->db->where('event_site.site_id', (int)$data['site_id']);
		}

		if (!empty($data['country_id'])) {
			$this->db->where('country.id', (int)$data['country_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('site.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_site._deleted', 0);
		$this->db->where('site._deleted', 0);

		$this->db->join('site', 'site.id = event_site.site_id');

		if (!empty($data['country_id'])) {
			$this->db->join('country', 'country.code = site.country_code');
		}

		$this->db->from('event_site');

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
			'site.name',
			'site.id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'site.name';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function get_qualified_teacher($data = []) {
		$this->db->select('event_teacher.event_id, event_teacher.teacher_id, concat(users.first_name, " ", users.last_name) as name, users.city_id, users.state_id, users.site_id, users.grade, users.section, site.name as school_name');

		if (!empty($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (!empty($data['event_id'])) {
			$this->db->where('event_teacher.event_id', (int)$data['event_id']);
			$this->db->where(sprintf('users.site_id IN (select site_id from event_site where event_id = %s and _deleted = 0)', $data['event_id']));

			if (isset($data['type'])) {
				$this->db->where(sprintf('event_teacher.teacher_id NOT IN (select teacher_id from teacher_rank_%s where event_id = %s and _deleted = 0)', $data['type'], $data['event_id']));
			}
		}

		if (!empty($data['teacher_id'])) {
			$this->db->where('event_teacher.teacher_id', (int)$data['teacher_id']);
		}

		if (!empty($data['city_id'])) {
			$this->db->where('users.city_id', (int)$data['city_id']);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('users.state_id', (int)$data['state_id']);
		}

		if (!empty($data['country_id'])) {
			$this->db->where('country.id', (int)$data['country_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('users.first_name', $data['search'], 'after');
			$this->db->or_like('users.last_name', $data['search'], 'after');
			$this->db->or_like('site.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_teacher._deleted', 0);
		$this->db->where('users._deleted', 0);

		$this->db->join('users', 'users.id = event_teacher.teacher_id');
		$this->db->join('site', 'site.id = users.site_id');
		
		if (!empty($data['country_id'])) {
			$this->db->join('country', 'country.code = site.country_code');
		}


		$this->db->from('event_teacher');

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
			'users.name',
			'users.id',
			'users.grade',
			'users.section',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by("{$sort} {$order}, users.section ASC");

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}