<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MarketingSurvey_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($marketing_survey_id = 0) {
		$this->db->select('marketing_survey.*');

		$this->db->where('marketing_survey.id', (int)$marketing_survey_id);
		$this->db->where('marketing_survey._deleted', 0);

		return $this->db->get('marketing_survey')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('marketing_survey.*');

		if (!empty($data['marketing_id'])) {
			$this->db->where('marketing_id', $data['marketing_id']);
		}

        if (!empty($data['user_id'])) {
			$this->db->where('user_id', $data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('marketing_survey.marketing_id', $data['search'], 'after');
			$this->db->or_like('marketing_survey.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('marketing_survey._deleted', 0);

		$this->db->from('marketing_survey');

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
			'marketing_survey.marketing_id',
			'marketing_survey.user_id',
			'marketing_survey.date_added',
			'marketing_survey.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'marketing_survey.id';
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
		$this->db->insert('marketing_survey', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$marketing_survey_id = $this->db->insert_id();

		return $marketing_survey_id;
	}

	public function edit($marketing_survey_id = 0, $data = []) {
		$this->db->where('id', (int)$marketing_survey_id);
		$this->db->update('marketing_survey', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($marketing_survey_id = 0) {
		$this->db->where('id', (int)$marketing_survey_id);
		$this->db->update('marketing_survey',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
