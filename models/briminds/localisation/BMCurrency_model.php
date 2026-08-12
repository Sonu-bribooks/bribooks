<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMCurrency_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($id = 0) {
		$this->bmdb->where('currency.id', (int)$id);

		return $this->bmdb->get('currency')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('currency.*,');

		if (!empty($data['code'])) {
			$this->bmdb->where('currency.code', $data['code']);
		}

		if (isset($data['name'])) {
			$this->bmdb->where('currency.name', $data['name']);
		}

		if (isset($data['exchange_rate'])) {
			$this->bmdb->where('currency.exchange_rate', (double)$data['exchange_rate']);
		}

		if (isset($data['exchange_rate_gt'])) {
			$this->bmdb->where('currency.exchange_rate > ', (double)$data['exchange_rate_gt']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('currency.name', $data['search'], 'after');
			$this->bmdb->or_like('currency.code', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('currency._deleted', 0);

		$this->bmdb->from('currency');

		$total = $this->bmdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bmdb->limit($data['limit'], $data['start']);
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

		$this->bmdb->order_by($sort, $order);

		return ['rows' => $this->bmdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->bmdb->insert('currency', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$currency_id = $this->bmdb->insert_id();

		return $currency_id;
	}

	public function edit($currency_id = 0, $data = []) {
		$this->bmdb->where('id', (int)$currency_id);
		$this->bmdb->update('currency', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->bmdb->where('id', (int)$id);
		$this->bmdb->update('currency', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function getByCode($code) {
		$this->bmdb->where('currency.code', $code);
		$this->bmdb->where('currency._deleted', 0);

		return $this->bmdb->get('currency')->row_array();
	}
}
