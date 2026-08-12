<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HallOfFameCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('hall_of_fame_country.*');

		$this->db->where('hall_of_fame_country.id', (int)$id);
		$this->db->where('hall_of_fame_country._deleted', 0);

		return $this->db->get('hall_of_fame_country')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			hall_of_fame_country.*
		');

		if (isset($data['country_code'])) {
			$this->db->where('hall_of_fame_country.country_code', $data['country_code']);
		}

		$this->db->where('hall_of_fame_country._deleted', 0);

		$this->db->from('hall_of_fame_country');

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
			'hall_of_fame_country.id',
			'hall_of_fame_country.priority',
			'hall_of_fame_country.book_sold',
			'hall_of_fame_country.country_code',
			'hall_of_fame_country.date_added',
			'hall_of_fame_country.date_modified'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'hall_of_fame_country.priority';
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
		$this->db->insert('hall_of_fame_country', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();
		
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('hall_of_fame_country', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('hall_of_fame_country',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
