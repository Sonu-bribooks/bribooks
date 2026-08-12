<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MasterClass_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($master_class_id = 0) {
		$this->db->select('master_class.*');

		$this->db->where('master_class.id', (int)$master_class_id);
		$this->db->where('master_class._deleted', 0);

		return $this->db->get('master_class')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('master_class.*');

		if (isset($data['event_id'])) {
			$this->db->where('master_class.event_id', (int)$data['event_id']);
		}

		if (isset($data['event_ids'])) {
			$this->db->where(
				sprintf(
					'master_class.event_id = (
						SELECT event_id
						FROM master_class
						WHERE event_id IN (%s)
						AND _deleted = 0
						ORDER BY event_id DESC
						LIMIT 1
					)',
					$data['event_ids']
				),
				null,
				false
			);
		}

		if (isset($data['sort_order'])) {
			$this->db->where('master_class.sort_order', (int)$data['sort_order']);
		}

		if (isset($data['status'])) {
			$this->db->where('master_class.status', (int)$data['status']);
		}

		if (isset($data['grade'])) {
			$this->db->group_start();
				$this->db->where('master_class.grade IS NULL', null, false);
				$this->db->or_where('master_class.grade', '');

				if (!empty($data['grade'])) {
					$this->db->or_where(
						"FIND_IN_SET(" . (int)$data['grade'] . ", master_class.grade) > 0",
						null,
						false
					);
				}
			$this->db->group_end();
		}


		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('master_class.name', $data['search'], 'after');
			$this->db->or_like('master_class.description', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('master_class._deleted', 0);

		$this->db->from('master_class');

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
			'master_class.name',
			'master_class.sort_order',
			'master_class.status',
			'master_class.date_added',
			'master_class.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'master_class.id';
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
		$this->db->insert('master_class', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$master_class_id = $this->db->insert_id();

		return $master_class_id;
	}

	public function edit($master_class_id = 0, $data = []) {
		$this->db->where('id', (int)$master_class_id);
		$this->db->update('master_class', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($master_class_id = 0) {
		$this->db->where('id', (int)$master_class_id);
		$this->db->update('master_class',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('master_class', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
