<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_id = 0) {
		$this->db->select('event.*');

		$this->db->where('event.id', (int)$event_id);
		$this->db->where('event._deleted', 0);

		return $this->db->get('event')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event.*, event_type.name as event_type');

		if (isset($data['event_id'])) {
			$this->db->where('event.id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('event.parent_site_id', (int)$data['site_id']);
		}

		if (isset($data['event_type_id'])) {
			$this->db->where('event.event_type_id', (int)$data['event_type_id']);
		}

		if (isset($data['slug'])) {
			$this->db->where('event.slug', $data['slug']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('event.country_id', (int)$data['country_id']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('event.country_code', $data['country_code']);
		}

		if (isset($data['start_date_le'])) {
			$this->db->where('event.start_date <= ', date('Y-m-d H:i:s', strtotime($data['start_date_le'])));
		}

		if (isset($data['end_date_ge'])) {
			$this->db->where('event.end_date >= ', date('Y-m-d H:i:s', strtotime($data['end_date_ge'])));
		}

		if (isset($data['selling_start_date_le'])) {
			$this->db->where('event.selling_end_date <= ', date('Y-m-d H:i:s', strtotime($data['selling_start_date_le'])));
		}

		if (isset($data['selling_end_date_ge'])) {
			$this->db->where('event.selling_end_date >= ', date('Y-m-d H:i:s', strtotime($data['selling_end_date_ge'])));
		}
		
		if (!empty($data['required_event_ids'])) {
			$this->db->where_in('event.id', $data['required_event_ids']);
		}

		if (!empty($data['not_required_event_ids'])) {
			$this->db->where_not_in('event.id', $data['not_required_event_ids']);
		}

		if (!empty($data['is_active_event'])) {
			$this->db->where('event.start_date <= ', date('Y-m-d H:i:s'));
			$this->db->where('event.end_date >= ', date('Y-m-d H:i:s'));
		}

		if (!empty($data['force_enrol'])) {
			$this->db->where('event.force_enrol', (int)$data['force_enrol']);
		}

		if (!empty($data['force_enrol_in'])) {
			$this->db->where_in('event.force_enrol', $data['force_enrol_in']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event.name', $data['search'], 'after');
			$this->db->or_like('event.id', $data['search'], 'after');
			$this->db->or_like('event.slug', $data['search'], 'after');
			$this->db->or_like('event.label', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->join('event_type', 'event_type.id = event.event_type_id', 'left');

		$this->db->where('event._deleted', 0);

		$this->db->from('event');

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
			'event.date_added',
			'event.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event.id';
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
		$this->db->insert('event', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $id;
	}
	public function edit($event_id = 0, $data = []) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('event', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($event_id = 0) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('event',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('event', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}

	public function getEventIdBySiteCode($site_code = '') {
		$this->db->select('event.*');
		$this->db->from('event');
		$this->db->where('site.site_code', $site_code);
		$this->db->join('site', 'site.id=event.parent_site_id');
		$this->db->group_by('event.id');
		$result = $this->db->get()->row_array();
		return $result;
	}

	public function getEventBySiteId($parent_site_id = 0) {
		$this->db->select('event.*');

		$this->db->where('event.parent_site_id', (int)$parent_site_id);
		$this->db->where('event._deleted', 0);

		return $this->db->get('event')->row_array();
	}
}
