<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMCountry_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($id = 0) {
		$this->bmdb->where('country.id', (int)$id);
		$this->bmdb->where('country._deleted', 0);

		return $this->bmdb->get('country')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('country.*,');

		if (!empty($data['code'])) {
			$this->bmdb->where('country.code', $data['code']);
		}

		if (isset($data['name'])) {
			$this->bmdb->where('country.name', $data['name']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('country.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('country.name', $data['search'], 'after');
			$this->bmdb->or_like('country.code', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('country._deleted', 0);

		$this->bmdb->from('country');

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

		$this->bmdb->order_by($sort, $order);

		return ['rows' => $this->bmdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$validity = $this->check_duplication($data, 'add');

		if ($validity == false) {
			$this->session->set_flashdata('error_message', get_phrase('country_duplication'));
		} else {
			$this->bmdb->insert('country', $data + [
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->bmdb->insert_id();

			$this->session->set_flashdata('flash_message', get_phrase('country_added_successfully'));

			return $id;
		}
	}

	public function edit($id = 0, $data = []) {
		$validity = $this->check_duplication($data, 'edit', $id);

		if ($validity) {
			$this->bmdb->where('id', $id);
			$this->bmdb->update('country', $data + [
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('flash_message', get_phrase('country_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', get_phrase('country_duplication'));
		}
	}

	public function check_duplication($data = [], $action = null, $id = 0) {
		$duplicate_check = $this->bmdb->get_where('country', [
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
		$this->bmdb->where('id', (int)$id);
		$this->bmdb->update('country', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function get_country_code($name) {
		$this->bmdb->where('country.name', $name);

		return $this->bmdb->get('country')->row_array();
	}

	public function getByCode($code = '') {
		$this->bmdb->where('country.code', $code);

		return $this->bmdb->get('country')->row_array();
	}

	public function get_all_join_sites($data = []) {
		$this->bmdb->select('country.*');

		if (!empty($data['code'])) {
			$this->bmdb->where('country.code', $data['code']);
		}

		$this->bmdb->join('site', 'site.country_code = country.code');

		$this->bmdb->group_by('country.id');

		return $this->bmdb->get('country')->result_array();
	}
}
