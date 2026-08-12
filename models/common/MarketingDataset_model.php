<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MarketingDataset_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($marketing_dataset_id = 0) {
		$this->db->select('marketing_dataset.*');

		$this->db->where('marketing_dataset.id', (int)$marketing_dataset_id);
		$this->db->where('marketing_dataset._deleted', 0);

		return $this->db->get('marketing_dataset')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('marketing_dataset.*');

		if (isset($data['status'])) {
			$this->db->where('marketing_dataset.status', (int)$data['status']);
		}

		if (isset($data['name'])) {
			$this->db->where('marketing_dataset.name', $data['name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('marketing_dataset.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('marketing_dataset._deleted', 0);

		$this->db->from('marketing_dataset');

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
			'marketing_dataset.id',
			'marketing_dataset.name',
			'marketing_dataset.status',
			'marketing_dataset.date_added',
			'marketing_dataset.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'marketing_dataset.id';
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
		$this->db->insert('marketing_dataset', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$marketing_dataset_id = $this->db->insert_id();

		return $marketing_dataset_id;
	}

	public function edit($marketing_dataset_id = 0, $data = []) {
		$this->db->where('id', (int)$marketing_dataset_id);
		$this->db->update('marketing_dataset', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($marketing_dataset_id = 0) {
		$this->db->where('id', (int)$marketing_dataset_id);
		$this->db->update('marketing_dataset',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
