<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->where('country.id', (int)$id);
		$this->bsdb->where('country._deleted', 0);

		return $this->bsdb->get('country')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('country.*,');

		if (!empty($data['code'])) {
			$this->bsdb->where('country.code', $data['code']);
		}

		if (isset($data['name'])) {
			$this->bsdb->where('country.name', $data['name']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('country.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('country.name', $data['search'], 'after');
			$this->bsdb->or_like('country.code', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('country._deleted', 0);

		$this->bsdb->from('country');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'country.name',
			'country.status',
			'country.date_added',
			'country.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'country.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->bsdb->order_by($sort, $order);

		return ['rows' => $this->bsdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$validity = $this->check_duplication($data, 'add');

		if ($validity == false) {
			$this->session->set_flashdata('error_message', get_phrase('country_duplication'));
		} else {
			$this->bsdb->insert('country', $data + [
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->bsdb->insert_id();

			$this->session->set_flashdata('flash_message', get_phrase('country_added_successfully'));

			return $id;
		}
	}

	public function edit($id = 0, $data = []) {
		$validity = $this->check_duplication($data, 'edit', $id);

		if ($validity) {
			$this->bsdb->where('id', $id);
			$this->bsdb->update('country', $data + [
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('flash_message', get_phrase('country_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', get_phrase('country_duplication'));
		}
	}

	public function check_duplication($data = [], $action = null, $id = 0) {
		$duplicate_check = $this->bsdb->get_where('country', [
			'code'		=> $data['code'],
		]);

		if ($action == 'add') {
			if ($duplicate_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'edit') {
			if ($duplicate_check->num_rows() > 0) {
				if ($duplicate_check->row()->id == $id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('country', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function get_country_code($name) {
		$this->bsdb->where('country.name', $name);

		return $this->bsdb->get('country')->row_array();
	}

	public function getByCode($code = '') {
		$this->bsdb->where('country.code', $code);

		return $this->bsdb->get('country')->row_array();
	}

	public function get_all_join_sites($data = []) {
		$this->bsdb->select('country.*');

		if (!empty($data['code'])) {
			$this->bsdb->where('country.code', $data['code']);
		}

		$this->bsdb->join('site', 'site.country_code = country.code');

		$this->bsdb->group_by('country.id');

		return $this->bsdb->get('country')->result_array();
	}
}
