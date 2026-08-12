<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TicketCategory_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('ticket_category.*');

		$this->db->where('ticket_category.id', (int)$id);
		$this->db->where('ticket_category._deleted', 0);
		return $this->db->get('ticket_category')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('ticket_category.*');

		if (isset($data['parent_id'])) {
			$this->db->where('ticket_category.parent_id', (int)$data['parent_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('ticket_category.name', $data['name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('ticket_category.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('ticket_category._deleted', 0);

		$this->db->from('ticket_category');

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
			'ticket_category.date_added',
			'ticket_category.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'ticket_category.id';
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
		$this->db->insert('ticket_category', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('ticket_category_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('ticket_category', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('ticket_category_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('ticket_category',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
