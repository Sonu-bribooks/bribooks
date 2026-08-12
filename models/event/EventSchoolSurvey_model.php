<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventSchoolSurvey_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_school_survey_id = 0) {
		$this->db->select('event_school_survey.*');

		$this->db->where('event_school_survey.id', (int)$event_school_survey_id);
		$this->db->where('event_school_survey._deleted', 0);

		return $this->db->get('event_school_survey')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_school_survey.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_school_survey.event_id', $data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('event_school_survey.site_id', $data['site_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('event_school_survey.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_school_survey.school_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_school_survey._deleted', 0);

		$this->db->from('event_school_survey');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'event_school_survey.status',
			'event_school_survey.site_id',
			'event_school_survey.date_added',
			'event_school_survey.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_school_survey.id';
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
		$this->db->insert('event_school_survey', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_school_survey_id = $this->db->insert_id();

		return $event_school_survey_id;
	}

	public function edit($event_school_survey_id = 0, $data = []) {
		$this->db->where('id', (int)$event_school_survey_id);
		$this->db->update('event_school_survey', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_school_survey_id = 0) {
		$this->db->where('id', (int)$event_school_survey_id);
		$this->db->update('event_school_survey',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('event_school_survey', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
