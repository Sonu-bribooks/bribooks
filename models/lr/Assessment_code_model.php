<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assessment_code_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->lrdb = $this->db;
	}

	public function get($assessment_code_id = 0) {
		$this->lrdb->select('assessment_code.*, category.name AS category');

		$this->lrdb->where('assessment_code.id', (int)$assessment_code_id);

		$this->lrdb->join('category', 'category.id = assessment_code.category_id');

		return $this->lrdb->get('assessment_code')->row_array();
	}

	public function get_all($data = []) {
		$this->lrdb->select('
			assessment_code.*,
			category.name AS category
		');

		if (isset($data['status'])) {
			$this->lrdb->where('assessment_code.status', (int)$data['status']);
		}

		if (isset($data['user_id'])) {
			$this->lrdb->where('assessment_code.user_id', (int)$data['user_id']);
		}

		if (isset($data['level'])) {
			$this->lrdb->where('assessment_code.level', $data['level']);
		}

		if (!empty($data['code'])) {
			$this->lrdb->where('assessment_code.code', $data['code']);
		}

		if (!empty($data['attempt'])) {
			$this->lrdb->where('assessment_code.attempt', $data['attempt']);
		}

		if (!empty($data['search'])) {
			$this->lrdb->group_start();
			$this->lrdb->like('assessment_code.code', $data['search'], 'after');
			$this->lrdb->or_like('category.name', $data['search'], 'after');
			$this->lrdb->group_end();
		}

		$this->lrdb->from('assessment_code');
		$this->lrdb->join('category', 'category.id = assessment_code.category_id');

		$total = $this->lrdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->lrdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'id',
			'name',
			'status',
			'date_added',
			'date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'assessment_code.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->lrdb->order_by($sort, $order);

		return ['rows' => $this->lrdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->lrdb->insert('assessment_code', $data + [
			'date_added' 		=> date('Y-m-d H:i:s'),
			'date_modified' 	=> date('Y-m-d H:i:s'),
		]);

		$assessment_code_id = $this->lrdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('assessment_code_added_successfully'));

		return $assessment_code_id;
	}

	public function edit($assessment_code_id = 0, $data = []) {
		$this->lrdb->where('id', (int)$assessment_code_id);
		$this->lrdb->update('assessment_code', $data + [
			'date_modified' 		=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('assessment_code_edited_successfully'));
	}

	public function delete($assessment_code_id = 0) {
		$this->lrdb->where('id', (int)$assessment_code_id);
		$this->lrdb->delete('assessment_code');

		$this->session->set_flashdata('flash_message', _l('assessment_code_deleted_successfully'));
	}

	public function updateAttempt($assessment_code_id = 0) {
		$this->lrdb->where('id', (int)$assessment_code_id);
		$this->lrdb->set('attempt', 'attempt+1', FALSE);
		$this->lrdb->update('assessment_code');
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->lrdb->where('id', (int)$id);
			$this->lrdb->update('assessment_code', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('class_updated_successfully'));
	}
}
