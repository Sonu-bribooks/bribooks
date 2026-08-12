<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSUserRankCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('user_rank_country.*');

		$this->bsdb->where('user_rank_country.id', (int)$id);
		$this->bsdb->where('user_rank_country._deleted', 0);
		return $this->bsdb->get('user_rank_country')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('user_rank_country.*');

		if (isset($data['name'])) {
			$this->bsdb->where('user_rank_country.name', $data['name']);
		}

		if (isset($data['event_id'])) {
			$this->bsdb->where('user_rank_country.event_id', (int)$data['event_id']);
		}

		if (isset($data['event_challenge_id'])) {
			$this->bsdb->where('user_rank_country.event_challenge_id', (int)$data['event_challenge_id']);
		}

		if (isset($data['country_id'])) {
			$this->bsdb->where('user_rank_country.country_id', (int)$data['country_id']);
		}

		if (isset($data['user_id'])) {
			$this->bsdb->where('user_rank_country.user_id', (int)$data['user_id']);
		}

		if (isset($data['startup_id'])) {
			$this->bsdb->where('user_rank_country.startup_id', (int)$data['startup_id']);
		}

		if (isset($data['startup_name'])) {
			$this->bsdb->where('user_rank_country.startup_name', $data['startup_name']);
		}

		if (isset($data['founder_name'])) {
			$this->bsdb->where('user_rank_country.founder_name', $data['founder_name']);
		}

		if (isset($data['slug'])) {
			$this->bsdb->where('user_rank_country.slug', $data['slug']);
		}

		if (isset($data['score'])) {
			$this->bsdb->where('user_rank_country.score', (int)$data['score']);
		}

		if (isset($data['rank'])) {
			$this->bsdb->where('user_rank_country.rank', (int)$data['rank']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('user_rank_country.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('user_rank_country.startup_name', $data['search'], 'after');
			$this->bsdb->or_like('user_rank_country.founder_name', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('user_rank_country._deleted', 0);

		$this->bsdb->from('user_rank_country');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
		} else {
			$this->bsdb->limit(10, 0);
		}

		$sort_data = [
			'user_rank_country.id',
			'user_rank_country.date_added',
			'user_rank_country.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_rank_country.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->bsdb->order_by($sort, $order);

		$results = $this->bsdb->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->bsdb->insert('user_rank_country', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('user_rank_country_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('user_rank_country', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('user_rank_country_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('user_rank_country',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('user_rank_country', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('user_rank_country_updated_successfully'));
	}

	public function getBySlug($slug = '') {
		$this->bsdb->select('user_rank_country.*');

		$this->bsdb->where('user_rank_country.slug', $slug);
		$this->bsdb->where('user_rank_country.status', 1);
		$this->bsdb->where('user_rank_country._deleted', 0);

		return $this->bsdb->get('user_rank_country')->row_array();
	}
}
