<?php defined('BASEPATH') OR exit('No direct script access allowed');

class QaQcLogs_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function get($id = 0) {
		$this->rdb->select('qa_qc_logs.*');
		$this->rdb->where('qa_qc_logs.id', (int)$id);
		return $this->rdb->get('qa_qc_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->rdb->select('qa_qc_logs.*');

		if (isset($data['assignment_id'])) {
			$this->rdb->where('qa_qc_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['manager_id'])) {
			$this->rdb->where('qa_qc_logs.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['book_id'])) {
			$this->rdb->where('qa_qc_logs.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->rdb->where('qa_qc_logs.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->rdb->where('qa_qc_logs.option', $data['option']);
		}

		if (isset($data['action'])) {
			$this->rdb->where('qa_qc_logs.action', (int)$data['action']);
		}

		if (isset($data['status'])) {
			$this->rdb->where('qa_qc_logs.status', (int)$data['status']);
		}

		if (isset($data['_deleted'])) {
			$this->rdb->where('qa_qc_logs._deleted', (int)$data['_deleted']);
		}

		if (isset($data['date_added'])) {
			$this->rdb->where('DATE(qa_qc_logs.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (isset($data['month'])) {
			$this->rdb->where('MONTH(qa_qc_logs.date_added)', date('m', strtotime($data['month'])));
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->rdb->where('qa_qc_logs.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		$this->rdb->from('qa_qc_logs');

		$total = $this->rdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->rdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'qa_qc_logs.id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'qa_qc_logs.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->rdb->order_by($sort, $order);

		return ['rows' => $this->rdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('qa_qc_logs', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('qa_qc_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}

	public function getByIds($data = []) {
		$this->rdb->select('qa_qc_logs.*');
		$this->rdb->where('qa_qc_logs.book_id', (int)$data['book_id']);
		$this->rdb->where('qa_qc_logs.assignment_id', (int)$data['assignment_id']);
		$this->rdb->where('qa_qc_logs.version', (int)$data['version']);
		$this->rdb->where('qa_qc_logs.option', $data['option']);
		$this->rdb->order_by('qa_qc_logs.id', 'desc');
		return $this->rdb->get('qa_qc_logs')->row_array();
	}

	public function delete($data = [])
	{
		if (isset($data['assignment_id'])) {
			$this->db->where('qa_qc_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('qa_qc_logs.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('qa_qc_logs.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->where('qa_qc_logs.option', $data['option']);
		}

		$this->db->where('qa_qc_logs._deleted', 0);

		$this->db->update('qa_qc_logs', [
			'_deleted'				=> 1,
			'date_deleted'			=> date('Y-m-d H:i:s'),
			'_deleted_manager_id'	=> $this->session->userdata('user_id'),
		]);

		$this->session->set_flashdata('flash_message', get_phrase('printer_deleted_successfully'));
	}

	public function get_all_managers($data = [])
	{
		$this->rdb->select("GROUP_CONCAT(DISTINCT(CONCAT(users.first_name, ' ', users.last_name))) AS manager_name");

		if (isset($data['assignment_id'])) {
			$this->rdb->where('qa_qc_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->rdb->where('qa_qc_logs.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->rdb->where('qa_qc_logs.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->rdb->where('qa_qc_logs.option', $data['option']);
		}

		// $this->rdb->where('qa_qc_logs._deleted', 0);

		$this->rdb->from('qa_qc_logs');
        $this->rdb->join('users', 'qa_qc_logs.manager_id=users.id');
		$this->rdb->group_by('qa_qc_logs.assignment_id');
		$this->rdb->group_by('qa_qc_logs.book_id');
		$this->rdb->group_by('qa_qc_logs.option');
		$this->rdb->group_by('qa_qc_logs.version');

		$results = $this->rdb->get()->row_array();

		return $results['manager_name'] ?? '';
	}

	public function getQaQcBookTitles($data = []) {
		$this->rdb->select('GROUP_CONCAT(DISTINCT(qa_qc_logs.book_id)) AS count_titles');

		if (isset($data['manager_id'])) {
			$this->rdb->where('qa_qc_logs.manager_id', (int)$data['manager_id']);
		}

		if (isset($data['status'])) {
			$this->rdb->where('qa_qc_logs.status', (int)$data['status']);
		}
		
		if (isset($data['_deleted'])) {
			$this->rdb->where('qa_qc_logs._deleted', (int)$data['_deleted']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->rdb->where('qa_qc_logs.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		$this->rdb->from('qa_qc_logs');
		$this->rdb->group_by('qa_qc_logs.manager_id');
		$this->rdb->group_by('qa_qc_logs.book_id');
		$this->rdb->group_by('qa_qc_logs.option');
		$this->rdb->group_by('qa_qc_logs.version');

		$total = $this->rdb->count_all_results('', FALSE);

		return ['rows' => $this->rdb->get()->result_array(), 'total' => $total];
	}
}
