<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventPdf_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_pdf_id = 0) {
		$this->db->select('event_pdf.*');

		$this->db->where('event_pdf.id', (int)$event_pdf_id);
		$this->db->where('event_pdf._deleted', 0);

		return $this->db->get('event_pdf')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_pdf.*');

		if (isset($data['event_id'])) {
			$this->db->where('event_pdf.event_id', (int)$data['event_id']);
		}

		if (isset($data['template_id'])) {
			$this->db->where('event_pdf.template_id', $data['template_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_pdf.name', $data['search'], 'after');
			$this->db->or_like('event_pdf.id', $data['search'], 'after');
			$this->db->or_like('event_pdf.event_id', $data['search'], 'after');
			$this->db->or_like('event_pdf.template_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_pdf._deleted', 0);

		$this->db->from('event_pdf');

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
			'event_pdf.date_added',
			'event_pdf.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_pdf.id';
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
		self::_formatData($data);

		$this->db->insert('event_pdf', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_pdf_id = $this->db->insert_id();

		return $event_pdf_id;
	}

	public function edit($event_pdf_id = 0, $data = []) {
		self::_formatData($data);

		$this->db->where('id', (int)$event_pdf_id);
		$this->db->update('event_pdf', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_pdf_id = 0) {
		$this->db->where('id', (int)$event_pdf_id);
		$this->db->update('event_pdf',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	private function _formatData(&$data) {
        $data['template_id'] = !empty($data['name']) ? strtolower(str_replace(' ', '_', $data['name'])) : '';
	}
}
