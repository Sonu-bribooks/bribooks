<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CertificateMessageTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($certificate_message_template = 0) {
		$this->db->select('certificate_message_template.*');

		$this->db->where('certificate_message_template.id', (int)$certificate_message_template);
		$this->db->where('certificate_message_template._deleted', 0);

		return $this->db->get('certificate_message_template')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('certificate_message_template.*');

		if (isset($data['event_id'])) {
			$this->db->where('certificate_message_template.event_id', (int)$data['event_id']);
		}

		if (isset($data['type'])) {
			$this->db->where('certificate_message_template.type', $data['type']);
		}

		if (isset($data['sold'])) {
			$this->db->where('certificate_message_template.sold', $data['sold']);
		}

		if (isset($data['min_sold_le'])) {
			$this->db->where('certificate_message_template.min_sold <= ', $data['min_sold_le']);
		}

		if (isset($data['max_sold_lt'])) {
			$this->db->where('certificate_message_template.max_sold > ', $data['max_sold_lt']);
		}

		if (isset($data['country_code'])) {
			$this->db->where('certificate_message_template.country_code', $data['country_code']);
		}

		if (isset($data['status'])) {
			$this->db->where('certificate_message_template.status', (int)$data['status']);
		}

		if (isset($data['fomo'])) {
			$this->db->where('certificate_message_template.fomo', (int)$data['fomo']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('certificate_message_template.name', $data['search'], 'after');
			$this->db->or_where('certificate_message_template.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('certificate_message_template._deleted', 0);

		$this->db->from('certificate_message_template');

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
			'certificate_message_template.name',
			'certificate_message_template.sort_order',
			'certificate_message_template.status',
			'certificate_message_template.date_added',
			'certificate_message_template.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'certificate_message_template.date_added';
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
		$this->db->insert('certificate_message_template', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$certificate_message_template = $this->db->insert_id();

		return $certificate_message_template;
	}

	public function edit($certificate_message_template = 0, $data = []) {
		$this->db->where('id', (int)$certificate_message_template);
		$this->db->update('certificate_message_template', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
		return true;
	}

	public function delete($certificate_message_template = 0) {
		$this->db->where('id', (int)$certificate_message_template);
		$this->db->update('certificate_message_template',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('certificate_message_template', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
