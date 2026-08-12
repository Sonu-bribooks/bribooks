<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventSite_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_site_id = 0) {
		$this->db->select('event_site.*');

		$this->db->where('event_site.id', (int)$event_site_id);
		$this->db->where('event_site._deleted', 0);

		return $this->db->get('event_site')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_site.*, event.name as event_name, site.name as site_name');

		if (isset($data['user_id'])) {
			$this->db->where('event_site.user_id', (int)$data['user_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('event_site.site_id', (int)$data['site_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_site.event_id', (int)$data['event_id']);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('site.state_id', (int)$data['state_id']);
		}

		$this->db->where('event_site._deleted', 0);

		if (!empty($data['state_id'])) {
			$this->db->join('site', 'site.id = event_site.site_id');
		}

		$this->db->join('event', 'event.id = event_site.event_id');
		$this->db->join('site', 'site.id = event_site.site_id');

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
			'event_site.date_added',
			'event_site.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_site.id';
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
		$this->db->insert('event_site', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_site_id = $this->db->insert_id();

		return $event_site_id;
	}

	public function edit($event_site_id = 0, $data = []) {
		$this->db->where('id', (int)$event_site_id);
		$this->db->update('event_site', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_site_id = 0) {
		$this->db->where('id', (int)$event_site_id);
		$this->db->update('event_site',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getEventIdBySiteId($event_id, $site_id) {
		$this->db->select('event_site.*');

		$this->db->where('event_site.event_id', (int)$event_id);
		$this->db->where('event_site.site_id', (int)$site_id);
		$this->db->where('event_site._deleted', 0);

		return $this->db->get('event_site')->row_array();
	}
	public function getDataByEventId($event_id = 0, $data = []) {
		$this->db->select('event_site.site_id, event_site.event_id, site.name,site.state_id, site.city_id, state.name as state, city.name as city,site.site_code, site.owner_email, site.owner_mobile, site.owner_name, site.authorized_person');

		$this->db->where('event_site.event_id', (int)$event_id);
		$this->db->where('site._deleted', 0);
		$this->db->join('site', 'site.id = event_site.site_id', 'left');
		$this->db->join('state', 'state.id = site.state_id', 'left');
		$this->db->join('city', 'city.id = site.city_id', 'left');
		if (!empty($data['state_id'])) {
			$this->db->where('site.state_id', (int)$data['state_id']);
		}

		if (!empty($data['site_type'])) {
			$this->db->where('site.site_type', $data['site_type']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('site.id', (int)$data['site_id']);
		}

		if (!empty($data['city_id'])) {
			$this->db->where('site.city_id', (int)$data['city_id']);
		}

		if (!empty($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (!empty($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (!empty($data['sort'])) {
			$sort = $data['sort'];
		} else {
			$sort = 'site.id';
		}

		if (!empty($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}
		$this->db->order_by($sort, $order);
		return $this->db->get('event_site')->result_array();
	}
}
