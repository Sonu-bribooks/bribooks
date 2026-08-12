<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ImportLeads_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('import_leads.*');

		$this->db->where('import_leads.id', (int)$id);
		$this->db->where('import_leads._deleted', 0);

		return $this->db->get('import_leads')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			import_leads.*
		');

		if (!empty($data['event_id'])) {
			$this->db->where('import_leads.event_id', (int)$data['event_id']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('import_leads.site_id', (int)$data['site_id']);
		}

		if (!empty($data['email'])) {
			$this->db->where('import_leads.email', $data['email']);
		}

		if (!empty($data['mobile'])) {
			$this->db->where('import_leads.mobile', $data['mobile']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('import_leads.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		$this->db->where('import_leads._deleted', 0);

		$this->db->from('import_leads');

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
			'import_leads.id',
			'import_leads.date_added',
			'import_leads.date_modified'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'import_leads.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('import_leads', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _li('Mark as user'));
		
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('import_leads', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('import_leads',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
