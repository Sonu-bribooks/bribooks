<?php defined('BASEPATH') OR exit('No direct script access allowed');

class QaQcLots_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function get($id = 0) {
		$this->rdb->select('qa_qc_lots_details.*');
		$this->rdb->where('qa_qc_lots_details.id', (int)$id);
		return $this->rdb->get('qa_qc_lots_details')->row_array();
	}

	public function get_all($data = []) {
		$this->rdb->select('qa_qc_lots_details.*');

		if (isset($data['assignment_id'])) {
			$this->rdb->where('qa_qc_lots_details.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->rdb->where('qa_qc_lots_details.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->rdb->where('qa_qc_lots_details.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->rdb->where('qa_qc_lots_details.option', $data['option']);
		}

		if (isset($data['action'])) {
			$this->rdb->where('qa_qc_lots_details.action', (int)$data['action']);
		}

		if (isset($data['status'])) {
			$this->rdb->where('qa_qc_lots_details.status', (int)$data['status']);
		}

		$this->rdb->from('qa_qc_lots_details');

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
			'qa_qc_lots_details.id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'qa_qc_lots_details.id';
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
		$this->db->insert('qa_qc_lots_details', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('qa_qc_lots_details', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}

	public function getByIds($data = []) {
		$this->rdb->select('qa_qc_lots_details.*');
		$this->rdb->where('qa_qc_lots_details.book_id', (int)$data['book_id']);
		$this->rdb->where('qa_qc_lots_details.assignment_id', (int)$data['assignment_id']);
		$this->rdb->where('qa_qc_lots_details.version', (int)$data['version']);
		$this->rdb->where('qa_qc_lots_details.option', $data['option']);
		$this->rdb->order_by('qa_qc_lots_details.id', 'desc');
		return $this->rdb->get('qa_qc_lots_details')->row_array();
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->delete('qa_qc_lots_details');
		$this->session->set_flashdata('flash_message', get_phrase('reset_successfully'));
	}
}
