<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Competition_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($competition_id = 0) {
		$this->db->select('competition.*, currency.code, currency.symbol');

		$this->db->where('competition.id', (int)$competition_id);
		$this->db->where('competition._deleted', 0);

		$this->db->join('currency', 'currency.id = competition.currency_id', 'left');

		$row = $this->db->get('competition')->row_array();

		$row['price'] = json_decode($row['price'], true);

		return $row;
	}

	public function get_all($data = []) {
		$this->db->select('competition.*, currency.code, currency.symbol');

		if (isset($data['site_id'])) {
			$this->db->where('competition.site_id', (int)$data['site_id']);
		}

		if (!empty($data['search'])) {
			$this->db->like('competition.name', $data['search'], 'after');
			$this->db->or_like('competition.price', $data['search'], 'after');
		}

		$this->db->where('competition._deleted', 0);

		$this->db->join('currency', 'currency.id = competition.currency_id', 'left');
		$this->db->from('competition');

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
			'competition.name',
			'competition.status',
			'competition.date_added',
			'competition.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'competition.date_added';
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
		$this->db->insert('competition', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$competition_id = $this->db->insert_id();

		return $competition_id;
	}

	public function edit($competition_id = 0, $data = []) {
		$this->db->where('id', $competition_id);
		$this->db->update('competition', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($competition_id = 0) {
		$this->db->where('id', $competition_id);
		$this->db->update('competition',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function addUser($data = []) {
		$this->db->insert('competition_user', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$competition_user_id = $this->db->insert_id();

		return $competition_user_id;
	}

	public function checkUser($data = []) {
		if (!empty($data['competition_id'])) {
			$this->db->where('competition_id', (int)$data['competition_id']);
		}

		if (!empty($data['user_id'])) {
			$this->db->where('user_id', (int)$data['user_id']);
		}

		return $this->db->get_where('competition_user', [
			'site_id'		=> (int)$this->config->item('site_id'),
		])->row_array();
	}
}
