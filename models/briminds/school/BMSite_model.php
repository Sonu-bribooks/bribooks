<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMSite_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($id = 0) {
		$this->bmdb->where('site.id', (int)$id);
		$this->bmdb->where('site._deleted', 0);

		return $this->bmdb->get('site')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('site.*,');

		if (!empty($data['site_id'])) {
			$this->bmdb->where('site.site_id', $data['site_id']);
		}

		if (!empty($data['state_id'])) {
			$this->bmdb->where('site.state_id', $data['state_id']);
		}

		if (!empty($data['city_id'])) {
			$this->bmdb->where('site.city_id', $data['city_id']);
		}

		if (isset($data['name'])) {
			$this->bmdb->where('site.name', $data['name']);
		}

		if (!empty($data['email'])) {
			$this->bmdb->where('site.owner_email', $data['email']);
		}

		if (!empty($data['owner_email'])) {
			$this->bmdb->where('site.owner_email', $data['owner_email']);
		}

		if (!empty($data['mobile'])) {
			$this->bmdb->where('site.owner_mobile', $data['mobile']);
		}

		if (!empty($data['owner_mobile'])) {
			$this->bmdb->where('site.owner_mobile', $data['owner_mobile']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('site.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('site.name', $data['search'], 'after');
			$this->bmdb->or_like('site.code', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('site._deleted', 0);

		$this->bmdb->from('site');

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
			'site.name',
			'site.status',
			'site.date_added',
			'site.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'site.id';
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
		$this->bmdb->insert('site', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bmdb->insert_id();

		$this->session->set_flashdata('flash_message', get_phrase('site_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bmdb->where('id', $id);
		$this->bmdb->update('site', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', get_phrase('site_update_successfully'));
	}
	public function delete($id = 0) {
		$this->bmdb->where('id', (int)$id);
		$this->bmdb->update('site', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function getByCode($code = '') {
		$this->bmdb->where('site.code', $code);

		return $this->bmdb->get('site')->row_array();
	}
}
