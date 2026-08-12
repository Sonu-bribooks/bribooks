<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AudioBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('audio_books.*');
		
		$this->db->where('audio_books.id', (int)$id);
		$this->db->where('audio_books._deleted', 0);
		return $this->db->get('audio_books')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('slug_name, book_id, file'); 
       
		$this->db->where('audio_books._deleted', 0);
		$this->db->where('audio_books.status !=', 0);

        if (isset($data['book_id'])) {
            $this->db->where('audio_books.book_id', $data['book_id']);
        }

		if (isset($data['slug_name'])) {
            $this->db->where('audio_books.slug_name', $data['slug_name']);
        }

		$this->db->from('audio_books');

		$total = $this->db->count_all_results('', FALSE);

		$result = $this->db->get()->result_array();

		return ['rows' => $result, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('audio_books', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$audio_books_id = $this->db->insert_id();

		return $audio_books_id;
	}

	public function edit($id, $data = []) {
		$this->db->where('id', $id);
	
		$this->db->update('audio_books', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
	public function delete($id = 0) {
		$this->db->where('id', $id);
		$this->db->update('audio_books',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
