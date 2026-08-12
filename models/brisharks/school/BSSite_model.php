<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSSite_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('site.*');

		$this->bsdb->where('site.id', (int)$id);
		$this->bsdb->where('site._deleted', 0);
		return $this->bsdb->get('site')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('site.*');

		if (isset($data['type'])) {
			$this->bsdb->where('site.type', $data['type']);
		}

		if (isset($data['email'])) {
			$this->bsdb->where('site.owner_email', $data['email']);
		}

		if (isset($data['site_id'])) {
			$this->bsdb->where('site.id', $data['site_id']);
		}

		if (isset($data['mobile'])) {
			$this->bsdb->where('site.owner_mobile', $data['mobile']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('site.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('site.name', $data['search'], 'after');
			$this->bsdb->or_like('site.owner_email', $data['search'], 'after');
			$this->bsdb->or_like('site.owner_mobile', $data['search'], 'after');
			$this->bsdb->or_like('site.id', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('site._deleted', 0);

		$this->bsdb->from('site');

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
			'site.id',
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

		$this->bsdb->order_by($sort, $order);

		$results = $this->bsdb->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->bsdb->insert('site', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('site_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('site', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('site_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('site',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('site', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('site_updated_successfully'));
	}
}
