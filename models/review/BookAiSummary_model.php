<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookAiSummary_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_ai_summary_id = 0) {
		$this->db->select('book_ai_summary.*');

		$this->db->where('book_ai_summary.id', (int)$book_ai_summary_id);
		$this->db->where('book_ai_summary._deleted', 0);

		return $this->db->get('book_ai_summary')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_ai_summary.*');

		if (isset($data['event_id'])) {
			$this->db->where('book_ai_summary.event_id', (int)$data['event_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_ai_summary.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('book_ai_summary.version', (int)$data['version']);
		}

		if (isset($data['name'])) {
			$this->db->where('book_ai_summary.name', $data['name']);
		}

		if (isset($data['author'])) {
			$this->db->where('book_ai_summary.author', $data['author']);
		}

		if (isset($data['slug'])) {
			$this->db->where('book_ai_summary.slug', $data['slug']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_ai_summary.name', $data['search'], 'after');
			$this->db->or_like('book_ai_summary.author', $data['search'], 'after');
			$this->db->or_like('book_ai_summary.slug', $data['search'], 'after');
			$this->db->or_like('book_ai_summary.book_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_ai_summary._deleted', 0);

		$this->db->from('book_ai_summary');

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
			'book_ai_summary.id',
			'book_ai_summary.name',
			'book_ai_summary.author',
			'book_ai_summary.date_added',
			'book_ai_summary.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_ai_summary.id';
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
		$this->db->insert('book_ai_summary', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$book_ai_summary_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $book_ai_summary_id;
	}

	public function edit($book_ai_summary_id = 0, $data = []) {
		$this->db->where('id', (int)$book_ai_summary_id);
		$this->db->update('book_ai_summary', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($book_ai_summary_id = 0) {
		$this->db->where('id', (int)$book_ai_summary_id);
		$this->db->update('book_ai_summary',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
