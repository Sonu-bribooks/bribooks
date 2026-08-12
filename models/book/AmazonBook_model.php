<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AmazonBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('amazon_book.*');

		$this->db->where('amazon_book.id', (int)$id);
		$this->db->where('amazon_book._deleted', 0);

		return $this->db->get('amazon_book')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			amazon_book.*
		');

		if (isset($data['book_id'])) {
			$this->db->where('amazon_book.book_id', (int)$data['book_id']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('amazon_book.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		$this->db->where('amazon_book._deleted', 0);

		$this->db->from('amazon_book');

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
			'amazon_book.id',
			'amazon_book.book_id',
			'amazon_book.date_added',
			'amazon_book.date_modified'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'amazon_book.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		if(!empty($data['book_id']) && !empty(self::get_all([
			'book_id' => $data['book_id']
		])['rows'] ?? [])) {
			$this->session->set_flashdata('flash_message', _li('Already mark'));
			return;
		}

		$this->db->insert('amazon_book', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _li('Mark as upload to amazon'));
		
		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('amazon_book', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('amazon_book',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
