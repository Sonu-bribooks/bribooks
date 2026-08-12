<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($certificate_id = 0) {
		$this->db->select('certificates.*');

		$this->db->where('certificates.id', (int)$certificate_id);
		$this->db->where('certificates._deleted', 0);

		return $this->db->get('certificates')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('certificates.*');

		if (isset($data['user_id'])) {
			$this->db->where('certificates.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('certificates.book_id', (int)$data['book_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('certificates.event_id', (int)$data['event_id']);
		}

		if (isset($data['certificate_template_id'])) {
			$this->db->where('certificates.certificate_template_id', (int)$data['certificate_template_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('certificates.site_id', (int)$data['site_id']);
		}

		if (isset($data['code'])) {
			$this->db->where('certificates.code', $data['code']);
		}

		if (isset($data['name'])) {
			$this->db->where('certificates.name', $data['name']);
		}

		if (isset($data['achievement'])) {
			$this->db->where('certificates.achievement', (int)$data['achievement']);
		}

		if (isset($data['status'])) {
			$this->db->where('certificates.status', (int)$data['status']);
		}

		$this->db->where('certificates._deleted', 0);

		$this->db->from('certificates');

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
			'certificates.id',
			'certificates.name',
			'certificates.status',
			'certificates.date_added',
			'certificates.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'certificates.id';
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
		$this->db->insert('certificates', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$certificate_id = $this->db->insert_id();

		$unique_id = 'BB/' . sprintf('%08d', $certificate_id) . '/' . (!empty($data['unique_id']) ? sprintf('%02d', $data['unique_id']) : '01') ;

		log_kb([
			'unique_id' => $unique_id,
		]);

		self::edit($certificate_id, [
			'unique_id' => $unique_id
		]);

		return $certificate_id;
	}

	public function edit($certificate_id = 0, $data = []) {
		$this->db->where('id', (int)$certificate_id);
		$this->db->update('certificates', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($certificate_id = 0) {
		$this->db->where('id', (int)$certificate_id);
		$this->db->update('certificates',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCode($code = '') {
		$this->db->select('certificates.*');

		$this->db->where('unique_id', $code);
		// $this->db->where('certificates._deleted', 0);

		return $this->db->get('certificates')->row_array();
	}
}
