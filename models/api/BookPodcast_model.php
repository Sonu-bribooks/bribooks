<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookPodcast_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('book_podcast.*');

		$this->db->where('book_podcast.id', (int)$id);
		$this->db->where('book_podcast._deleted', 0);

		return $this->db->get('book_podcast')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_podcast.*');

		if (isset($data['event_id'])) {
			$this->db->where('book_podcast.event_id', (int)$data['event_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_podcast.book_id', (int)$data['book_id']);
		}

		if (isset($data['slot_id'])) {
			$this->db->where('book_podcast.slot_id', (int)$data['slot_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_podcast.event_id', $data['search'], 'after');
			$this->db->or_like('book_podcast.book_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_podcast._deleted', 0);

		$this->db->from('book_podcast');

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
			'book_podcast.date_added',
			'book_podcast.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_podcast.id';
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
		$this->db->insert('book_podcast', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('slot_added_successfully'));

		return $id;
	}
	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_podcast', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('slot_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('book_podcast',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
