<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('import');

class ImportJob_model extends CI_Model {
	use ImportInit,
		ImportCommon,
		ImportGeneric,
		ImportLocalisation,
		ImportUser,
		ImportSchool,
		ImportOrder,
		ImportInviteCode,
		ImportPincodeZone,
		ImportAuthorCalendar,
		ImportAuthorWall,
		ImportEventExhibition,
		ImportEventCertificate,
		ImportEventLiteraryLeader
	;

	public function __construct() {
		parent::__construct();

		self::_loadModel();
	}

	public function get($import_job_id = 0) {
		$this->db->select('import_job.*');

		$this->db->where('import_job.id', (int)$import_job_id);
		$this->db->where('import_job._deleted', 0);

		return $this->db->get('import_job')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('import_job.*');

		if (isset($data['name'])) {
			$this->db->where('import_job.name', $data['name']);
		}

		if (isset($data['csv'])) {
			$this->db->where('import_job.csv', $data['csv']);
		}

		if (isset($data['action'])) {
			$this->db->where('import_job.action', $data['action']);
		}

		if (isset($data['counter'])) {
			$this->db->where('import_job.counter', (int)$data['counter']);
		}

		if (isset($data['status'])) {
			$this->db->where('import_job.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('import_job.name', $data['search'], 'after');
			$this->db->or_like('import_job.csv', $data['search'], 'after');
			$this->db->or_like('import_job.action', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('import_job._deleted', 0);

		$this->db->from('import_job');

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
			'import_job.id',
			'import_job.name',
			'import_job.action',
			'import_job.status',
			'import_job.date_added',
			'import_job.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'import_job.id';
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
		$this->db->insert('import_job', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$import_job_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $import_job_id;
	}

	public function edit($import_job_id = 0, $data = []) {
		$this->db->where('id', (int)$import_job_id);
		$this->db->update('import_job', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($import_job_id = 0) {
		$this->db->where('id', (int)$import_job_id);
		$this->db->update('import_job',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('import_job', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}

	public function updateCounter($id = 0) {
		if (empty($id)) return;

		$this->db->set('counter', 'counter+1', FALSE);
		$this->db->where('id', (int)$id);
		$this->db->update('import_job');
	}

	public function updateSkipped($id = 0) {
		if (empty($id)) return;

		$this->db->set('skipped', 'skipped+1', FALSE);
		$this->db->where('id', (int)$id);
		$this->db->update('import_job');
	}
}
