<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PrinterStatusLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($printer_status_logs_id = 0) {
		$this->db->select('printer_status_logs.*,
			category.name AS category,
			CONCAT(users.first_name, " ", users.last_name) AS author
		');

		$this->db->where('printer_status_logs.id', (int)$printer_status_logs_id);
		$this->db->where('printer_status_logs._deleted', 0);

		$this->db->join('category', 'category.id = printer_status_logs.category_id', 'left');
		$this->db->join('users', 'users.id = printer_status_logs.user_id', 'left');

		return $this->db->get('printer_status_logs')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('printer_status_logs.*,
			category.name AS category,
			CONCAT(users.first_name, " ", users.last_name) AS author
		');

		if (isset($data['user_id'])) {
			$this->db->where('printer_status_logs.user_id', (int)$data['user_id']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('printer_status_logs.category_id', (int)$data['category_id']);
		}

		if (isset($data['sort_order'])) {
			$this->db->where('printer_status_logs.sort_order', (int)$data['sort_order']);
		}

		if (isset($data['status'])) {
			$this->db->where('printer_status_logs.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('printer_status_logs.name', $data['search'], 'after');
			$this->db->or_like('printer_status_logs.description', $data['search'], 'after');
		}

		$this->db->where('printer_status_logs._deleted', 0);

		$this->db->join('category', 'category.id = printer_status_logs.category_id', 'left');
		$this->db->join('users', 'users.id = printer_status_logs.user_id', 'left');
		$this->db->from('printer_status_logs');

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
			'printer_status_logs.name',
			'printer_status_logs.sort_order',
			'printer_status_logs.status',
			'printer_status_logs.date_added',
			'printer_status_logs.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'printer_status_logs.date_added';
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
		$this->db->insert('printer_status_logs', $data + [
			'printer_id'		=> (int)$this->session->userdata('user_id'),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$printer_status_logs_id = $this->db->insert_id();

		return $printer_status_logs_id;
	}

	public function edit($printer_status_logs_id = 0, $data = []) {
		unset($data['files']);

		if (is_array($data['related'])) {
			$data['related'] = implode(',', $data['related']);
		}

		$this->db->where('id', (int)$printer_status_logs_id);
		$this->db->update('printer_status_logs', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
