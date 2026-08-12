<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuthorEarningExchangeRateHistory_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->where('author_earning_exchange_rate_history.id', (int)$id);
		$this->db->where('author_earning_exchange_rate_history._deleted', 0);

		return $this->db->get('author_earning_exchange_rate_history')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('author_earning_exchange_rate_history.*,');

		if (!empty($data['author_earning_id'])) {
			$this->db->where('author_earning_exchange_rate_history.author_earning_id', (int)$data['author_earning_id']);
		}

        if (!empty($data['currency_id'])) {
			$this->db->where('author_earning_exchange_rate_history.currency_id', (int)$data['currency_id']);
		}

		if (!empty($data['currency_code'])) {
			$this->db->where('author_earning_exchange_rate_history.currency_code', $data['currency_code']);
		}

		if (isset($data['rate'])) {
			$this->db->where('author_earning_exchange_rate_history.old_rate', (double)$data['old_rate']);
		}

		$this->db->from('author_earning_exchange_rate_history');

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
			'author_earning_exchange_rate_history.id',
			'author_earning_exchange_rate_history.date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'author_earning_exchange_rate_history.id';
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
		$this->db->insert('author_earning_exchange_rate_history', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
		]);

		$author_earning_exchange_rate_history_id = $this->db->insert_id();

		return $author_earning_exchange_rate_history_id;
	}

	public function edit($author_earning_exchange_rate_history_id = 0, $data = []) {
		$this->db->where('id', (int)$author_earning_exchange_rate_history_id);
		$this->db->update('author_earning_exchange_rate_history', $data);
	}
}
