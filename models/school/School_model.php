<?php defined('BASEPATH') OR exit('No direct script access allowed');

class School_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			schools.*,
			site.name AS site,
			state.name AS state,
			city.name AS city,
			country.name AS country
		');

		$this->db->where('schools.id', (int)$id);
		$this->db->where('schools._deleted', 0);

		$this->db->join('site', 'site.id = schools.site_id', 'left');
		$this->db->join('state', 'state.id = schools.state_id', 'left');
		$this->db->join('city', 'city.id = schools.city_id', 'left');
		$this->db->join('country', 'country.id = schools.country_id', 'left');

		return $this->db->get('schools')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			schools.*,
			site.name AS site,
			state.name AS state,
			city.name AS city,
			country.name AS country
		');

		if (!empty($data['name'])) {
			$this->db->where('schools.name', $data['name']);
		}

		if (!empty($data['country_code'])) {
			$this->db->where('schools.country_code', $data['country_code']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where_in('schools.site_id', $data['site_id']);
		}

		if (!empty($data['site_id_ne'])) {
			$this->db->where('schools.site_id !=', $data['site_id_ne']);
		}

		if (!empty($data['not_school_id'])) {
			$this->db->where('schools.id !=', $data['not_school_id']);
		}

		if (!empty($data['school_id'])) {
			$this->db->where('schools.id', $data['school_id']);
		}

		if (!empty($data['email'])) {
			$this->db->where('schools.owner_email', trim($data['email']));
		}

		if (!empty($data['owner_email'])) {
			$this->db->where('schools.owner_email', trim($data['owner_email']));
		}

		if (!empty($data['owner_mobile'])) {
			$this->db->where('schools.owner_mobile', trim($data['owner_mobile']));
		}

		if (!empty($data['alternate_owner_email'])) {
			$this->db->where('schools.alternate_owner_email', trim($data['alternate_owner_email']));
		}

		if (!empty($data['alternate_owner_mobile'])) {
			$this->db->where('schools.alternate_owner_mobile', trim($data['alternate_owner_mobile']));
		}

		if (!empty($data['site_ids'])) {
			$this->db->where_in('schools.site_id', $data['site_ids']);
		}

		if (!empty($data['site_type'])) {
			$this->db->where('schools.site_type', $data['site_type']);
		}

		if (isset($data['parent_id'])) {
			$this->db->where('schools.parent_id', (int)$data['parent_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('schools.state_id', (int)$data['state_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('schools.city_id', (int)$data['city_id']);
		}

		if (!empty($data['site_code'])) {
			$this->db->like('schools.site_code', $data['site_code'], 'after');
		}

		if (!empty($data['site_codes'])) {
			$this->db->where_in('site.site_code', $data['site_codes']);
		}

		if (!empty($data['parent_ids'])) {
			$this->db->where_in('schools.parent_id', $data['parent_ids']);
		}

		if (isset($data['status'])) {
			$this->db->where('schools.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->db->where('schools.verified', (int)$data['verified']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('schools.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('schools.id', $data['search'], 'both');
			$this->db->or_like('schools.site_id', $data['search'], 'both');
			$this->db->or_like('schools.name', $data['search'], 'both');
			$this->db->or_like('schools.owner_email', $data['search'], 'after');
			$this->db->or_like('schools.owner_mobile', $data['search'], 'after');
			$this->db->or_like('schools.authorized_person', $data['search'], 'both');
			$this->db->or_like('schools.alternate_authorized_person', $data['search'], 'both');
			$this->db->or_like('schools.alternate_owner_email', $data['search'], 'after');
			$this->db->or_like('schools.alternate_owner_mobile', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->join('site', 'site.id = schools.site_id', 'left');
		$this->db->join('country', 'country.id = schools.country_id', 'left');
		$this->db->join('state', 'state.id = schools.state_id', 'left');
		$this->db->join('city', 'city.id = schools.city_id', 'left');

		$this->db->where('schools._deleted', 0);
		$this->db->from('schools');

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
			'schools.name',
			'schools.status',
			'schools.date_added',
			'schools.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'schools.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('schools', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$data['site_id'] ?? (int)$this->config->item('site_id'),
		]);

		$schools_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('schools_added_successfully'));

		return $schools_id;
	}

	public function edit($schools_id = 0, $data = []) {
		$this->db->where('id', $schools_id);
		$this->db->update('schools', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('schools_edited_successfully'));
	}

	public function editBySite($site_id = 0, $data = []) {
		$this->db->where('site_id', $site_id);
		$this->db->update('schools', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('schools_edited_successfully'));
	}

	public function delete($schools_id = 0) {
		$this->db->where('id', $schools_id);
		$this->db->update('schools', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCode($site_code = '', $site_type = '', $site_id = '') {
		$this->db->select('
			schools.*,
			site.name AS site,
			state.name AS state,
			city.name AS city,
			country.name AS country
		');
		
		$this->db->where('schools.site_code', $site_code);

		if (!empty($site_type)) {
			$this->db->where('schools.site_type', $site_type);
		}

		if (!empty($site_id)) {
			$this->db->where('schools.site_id', $site_id);
		}

		$this->db->where('schools._deleted', 0);

		$this->db->join('site', 'site.id = schools.site_id', 'left');
		$this->db->join('state', 'state.id = schools.state_id', 'left');
		$this->db->join('city', 'city.id = schools.city_id', 'left');
		$this->db->join('country', 'country.id = schools.country_id', 'left');

		return $this->db->get('schools')->row_array();
	}

	public function getSchoolAddress($school_id = 0) {
		$this->db->select('
			schools.id,
			schools.parent_id,
			schools.name,
			schools.owner_name,
			schools.owner_email as email,
			schools.owner_mobile as mobile,
			schools.site_code,
			schools.site_type,
			schools.state_id,
			schools.city_id, 
			state.name as state,
			city.name as city,
			schools.verified,
			schools.authorized_person,
			schools.image,
			address,
			landmark,
			schools.pincode as zipcode,
			country_code,
			country.name as country
		');

		$this->db->where('schools.id', (int)$school_id);
		$this->db->where('schools._deleted', 0);

		$this->db->join('state', 'state.id = schools.state_id', 'left');
		$this->db->join('city', 'city.id = schools.city_id', 'left');
		$this->db->join('country', 'country.code = schools.country_code', 'left');

		return $this->db->get('schools')->row_array();
	}

	public function getSchoolStates($country_code = '', $site_type = 0, $parent_id = 0) {
		$this->db->select('distinct schools.state_id as id, state.name as name', FALSE);

		$this->db->where('schools.country_code', $country_code);

		if (!empty($site_type)) {
			$this->db->where('schools.site_type', $site_type);
		}

		if (!empty($parent_id)) {
			$this->db->where('schools.parent_id', $parent_id);
		}

		$this->db->where('schools._deleted', 0);
		$this->db->where('state._deleted', 0);

		$this->db->join('state', 'state.id = schools.state_id', 'left');
		$this->db->order_by('state.name', 'ASC');

		return $this->db->get('schools')->result_array();
	}

	public function getSchoolCities($state_id = 0, $site_type = 0, $parent_id = 0) {
		$this->db->select('distinct schools.city_id as id, city.name as name', FALSE);

		$this->db->where('city.state_id', (int)$state_id);

		if (!empty($site_type)) {
			$this->db->where('schools.site_type', $site_type);
		}

		if (!empty($parent_id)) {
			$this->db->where('schools.parent_id', $parent_id);
		}

		$this->db->where('schools._deleted', 0);
		$this->db->where('city._deleted', 0);

		$this->db->join('city', 'city.id = schools.city_id', 'left');
		$this->db->order_by('city.name', 'ASC');

		return $this->db->get('schools')->result_array();
	}

	public function getBySiteID($site_id = 0) {
		if (empty($site_id)) return;

		$this->db->select('
			schools.*,
			site.name AS site,
			state.name AS state,
			city.name AS city,
			country.name AS country
		');

		$this->db->where('schools.site_id', (int)$site_id);
		$this->db->where('schools._deleted', 0);

		$this->db->join('site', 'site.id = schools.site_id', 'left');
		$this->db->join('state', 'state.id = schools.state_id', 'left');
		$this->db->join('city', 'city.id = schools.city_id', 'left');
		$this->db->join('country', 'country.id = schools.country_id', 'left');

		return $this->db->get('schools')->row_array();
	}
}
