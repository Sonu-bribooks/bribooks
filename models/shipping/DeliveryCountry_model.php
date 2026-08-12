<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DeliveryCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_id = 0) {
		$this->db->select('delivery_country.*');

		$this->db->where('delivery_country.id', (int)$event_id);
		$this->db->where('delivery_country._deleted', 0);

		return $this->db->get('delivery_country')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('delivery_country.*');

		if (isset($data['name'])) {
			$this->db->where('delivery_country.name', $data['name']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('delivery_country.country_id', (int)$data['country_id']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('delivery_country.country_code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->db->where('delivery_country.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('delivery_country.name', $data['search'], 'after');
			$this->db->or_like('delivery_country.country_code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('delivery_country._deleted', 0);

		$this->db->from('delivery_country');

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
			'delivery_country.id',
			'delivery_country.name',
			'delivery_country.country_code',
			'delivery_country.date_added',
			'delivery_country.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'delivery_country.id';
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
		$this->db->insert('delivery_country', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('modified_successfully'));

		return $event_id;
	}

	public function edit($event_id = 0, $data = []) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('delivery_country', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('modified_successfully'));
	}

	public function delete($event_id = 0) {
		$this->db->where('id', (int)$event_id);
		$this->db->update('delivery_country',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('delivery_country', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}
}
