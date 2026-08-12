<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Currency_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->where('currency.id', (int)$id);

		return $this->db->get('currency')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('currency.*,');

		if (!empty($data['code'])) {
			$this->db->where('currency.code', $data['code']);
		}

		if (isset($data['name'])) {
			$this->db->where('currency.name', $data['name']);
		}

		if (isset($data['exchange_rate'])) {
			$this->db->where('currency.exchange_rate', (double)$data['exchange_rate']);
		}

		if (isset($data['exchange_rate_gt'])) {
			$this->db->where('currency.exchange_rate > ', (double)$data['exchange_rate_gt']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('currency.name', $data['search'], 'after');
			$this->db->or_like('currency.code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('currency._deleted', 0);

		$this->db->from('currency');

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
			'currency.name',
			'currency.date_added',
			'currency.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'currency.id';
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
		$this->db->insert('currency', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$currency_id = $this->db->insert_id();

		return $currency_id;
	}

	public function edit($currency_id = 0, $data = []) {
		$this->db->where('id', (int)$currency_id);
		$this->db->update('currency', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('currency', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function getByCode($code) {
		$this->db->where('currency.code', $code);
		$this->db->where('currency._deleted', 0);

		return $this->db->get('currency')->row_array();
	}
}
