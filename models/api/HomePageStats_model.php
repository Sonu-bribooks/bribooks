<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HomePageStats_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function getBarneRanks($data = []) {
		$this->db->select('event_order.event_id, event_order.book_id, book.user_id, book.name as book_name, book.author_name, book.author_image, book.cover_image as book_image, book.slug as book_slug , sum(quantity) as score, book.date_published');

		if (isset($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_order.event_order', (int)$data['book_id']);
		}

		$this->db->where('event_order._deleted', 0);
		$this->db->where('book._deleted', 0);
		$this->db->where('book.date_published <', '2024-05-13 00:00:00');

		$this->db->join('book', 'book.id = event_order.book_id');
		$this->db->from('event_order');

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

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by('score', $order);

		$this->db->group_by('event_order.book_id');

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
