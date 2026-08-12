<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CertificateType_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($certificate_type_id = 0) {
		$this->db->select('certificate_type.*');
		$this->db->where('certificate_type.id', (int)$certificate_type_id);
		$this->db->where('certificate_type._deleted', 0);

		return $this->db->get('certificate_type')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('certificate_type.*');

		if (isset($data['achievement'])) {
			$this->db->where('certificate_type.achievement', (int)$data['achievement']);
		}

		if (isset($data['type'])) {
			$this->db->where('certificate_type.type', $data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('certificate_type.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('certificate_type.name', $data['search'], 'after');
			$this->db->or_like('certificate_type.type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('certificate_type._deleted', 0);

		$this->db->from('certificate_type');

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
			'certificate_type.name',
			'certificate_type.status',
			'certificate_type.date_added',
			'certificate_type.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'certificate_type.date_added';
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
		$this->db->insert('certificate_type', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$certificate_type_id = $this->db->insert_id();

		return $certificate_type_id;
	}

	public function edit($certificate_type_id = 0, $data = []) {
		$this->db->where('id', (int)$certificate_type_id);
		$this->db->update('certificate_type', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($certificate_type_id = 0) {
		$this->db->where('id', (int)$certificate_type_id);
		$this->db->update('certificate_type',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('certificate_type', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
