<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MessageTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('message_template.*');

		$this->db->where('message_template.id', (int)$id);
		$this->db->where('message_template._deleted', 0);
		return $this->db->get('message_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('message_template.*');

		if (isset($data['site_id'])) {
			$this->db->where('message_template.site_id', (int)$data['site_id']);
		}

		if (isset($data['template_type_id'])) {
			$this->db->where('message_template.template_type_id', (int)$data['template_type_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('message_template.name', $data['name']);
		}

		if (isset($data['code'])) {
			$this->db->where('message_template.code', $data['code']);
		}

		if (isset($data['status'])) {
			$this->db->where('message_template.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('message_template.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('message_template._deleted', 0);

		$this->db->from('message_template');

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

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('message_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('message_template_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('message_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('message_template_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('message_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('message_template', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('message_template_updated_successfully'));
	}

	public function getByCode($code = '', $site_id = '') {
		$this->db->select('message_template.*');

		if (!empty($site_id)) {
			$this->db->where('message_template.site_id', $site_id);
		}

		$this->db->where('message_template.code', $code);
		$this->db->where('message_template.status', 1);
		$this->db->where('message_template._deleted', 0);

		return $this->db->get('message_template')->row_array();
	}
}
