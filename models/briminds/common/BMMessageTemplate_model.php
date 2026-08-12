<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMMessageTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($id = 0) {
		$this->bmdb->select('message_template.*');

		$this->bmdb->where('message_template.id', (int)$id);
		$this->bmdb->where('message_template._deleted', 0);
		return $this->bmdb->get('message_template')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('message_template.*');

		if (isset($data['country_code'])) {
			$this->bmdb->where('message_template.country_code', $data['country_code']);
		}

		if (isset($data['name'])) {
			$this->bmdb->where('message_template.name', $data['name']);
		}

		if (isset($data['code'])) {
			$this->bmdb->where('message_template.code', $data['code']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('message_template.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('message_template.name', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('message_template._deleted', 0);

		$this->bmdb->from('message_template');

		$total = $this->bmdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bmdb->limit($data['limit'], $data['start']);
		} else {
			$this->bmdb->limit(10, 0);
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

		$this->bmdb->order_by($sort, $order);

		$results = $this->bmdb->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->bmdb->insert('message_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bmdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('message_template_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bmdb->where('id', (int)$id);
		$this->bmdb->update('message_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('message_template_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bmdb->where('id', (int)$id);
		$this->bmdb->update('message_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bmdb->where('id', (int)$id);
			$this->bmdb->update('message_template', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('message_template_updated_successfully'));
	}

	public function getByCode($code = '', $country_code = '') {
		$this->bmdb->select('message_template.*');

		if (!empty($country_code)) {
			$this->bmdb->where('message_template.country_code', $country_code);
		}

		$this->bmdb->where('message_template.code', $code);
		$this->bmdb->where('message_template.status', 1);
		$this->bmdb->where('message_template._deleted', 0);

		return $this->bmdb->get('message_template')->row_array();
	}
}
