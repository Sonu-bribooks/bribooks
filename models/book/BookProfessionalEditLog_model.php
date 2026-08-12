<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookProfessionalEditLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('book_professional_edit_log.*, book.name, book.author_name');

		$this->db->where('book_professional_edit_log.id', (int)$id);
		$this->db->where('book_professional_edit_log._deleted', 0);

		$this->db->join('book', 'book_professional_edit_log.src_book_id = book.id');

		return $this->db->get('book_professional_edit_log')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_professional_edit_log.*, book.name, book.author_name');

		if (isset($data['book_id'])) {
			$this->db->where('book_professional_edit_log.book_id', (int)$data['book_id']);
		}

		if (isset($data['src_book_id'])) {
			$this->db->where('book_professional_edit_log.src_book_id', (int)$data['src_book_id']);
		}

		if (isset($data['editor_id'])) {
			$this->db->where('book_professional_edit_log.editor_id', (int)$data['editor_id']);
		}

		if (isset($data['author_id'])) {
			$this->db->where('book_professional_edit_log.author_id', (int)$data['author_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_professional_edit_log._deleted', 0);

		$this->db->join('book', 'book_professional_edit_log.src_book_id = book.id');

		$this->db->from('book_professional_edit_log');

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
			'book_professional_edit_log.id',
			'book_professional_edit_log.date_added',
			'book_professional_edit_log.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_professional_edit_log.id';
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
		$this->db->insert('book_professional_edit_log', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('book_professional_edit_log_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_professional_edit_log', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('book_professional_edit_log_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_professional_edit_log',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('book_professional_edit_log_deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('book_professional_edit_log', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('book_professional_edit_log_updated_successfully'));
	}
}
