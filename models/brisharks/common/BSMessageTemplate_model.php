<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSMessageTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('message_template.*');

		$this->bsdb->where('message_template.id', (int)$id);
		$this->bsdb->where('message_template._deleted', 0);
		return $this->bsdb->get('message_template')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('message_template.*');

		if (isset($data['country_code'])) {
			$this->bsdb->where('message_template.country_code', $data['country_code']);
		}

		if (isset($data['name'])) {
			$this->bsdb->where('message_template.name', $data['name']);
		}

		if (isset($data['code'])) {
			$this->bsdb->where('message_template.code', $data['code']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('message_template.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('message_template.name', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('message_template._deleted', 0);

		$this->bsdb->from('message_template');

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
			'message_template.id',
			'message_template.date_added',
			'message_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'message_template.id';
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
		$this->bsdb->insert('message_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('message_template_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('message_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('message_template_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('message_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('message_template', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('message_template_updated_successfully'));
	}

	public function getByCode($code = '', $site_id = '') {
		$this->bsdb->select('message_template.*');

		if (!empty($site_id)) {
			$this->bsdb->where('message_template.site_id', $site_id);
		}

		$this->bsdb->where('message_template.code', $code);
		$this->bsdb->where('message_template.status', 1);
		$this->bsdb->where('message_template._deleted', 0);

		return $this->bsdb->get('message_template')->row_array();
	}
}
