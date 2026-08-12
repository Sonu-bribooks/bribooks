<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventJuryBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_jury_book.*');

		$this->db->where('event_jury_book.id', (int)$id);
		$this->db->where('event_jury_book._deleted', 0);

		return $this->db->get('event_jury_book')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_jury_book.*');

        if (isset($data['type'])) {
			$this->db->where('event_jury_book.type', $data['type']);
		}

		if (isset($data['jury_challenge_id'])) {
			$this->db->where('event_jury_book.jury_challenge_id', (int)$data['jury_challenge_id']);
		}

        if (isset($data['challenge_id'])) {
			$this->db->where('event_jury_book.challenge_id', (int)$data['challenge_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_jury_book.event_id', (int)$data['event_id']);
		}

		if (isset($data['slug'])) {
			$this->db->where('event_jury_book.challenge_slug', $data['slug']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_jury_book.book_id', (int)$data['book_id']);
		}

        if (isset($data['user_id'])) {
			$this->db->where('event_jury_book.user_id', (int)$data['user_id']);
		}

		if (isset($data['genre_id'])) {
			$this->db->where('event_jury_book.genre_id', (int)$data['genre_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('event_jury_book.city_id', (int)$data['city_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('event_jury_book.state_id', (int)$data['state_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('event_jury_book.country_id', (int)$data['country_id']);
		}

		if (isset($data['rank'])) {
			$this->db->where('event_jury_book.rank', (int)$data['rank']);
		}

		if (isset($data['rank_ge'])) {
			$this->db->where('event_jury_book.rank >=', (int)$data['rank_ge']);
		}

        if (isset($data['rank_le'])) {
			$this->db->where('event_jury_book.rank <=', (int)$data['rank_le']);
		}

		$this->db->where('event_jury_book._deleted', 0);

		$this->db->from('event_jury_book');

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
			'event_jury_book.rank',
			'event_jury_book.date_added',
			'event_jury_book.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_jury_book.id';
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
		$this->db->insert('event_jury_book', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_jury_book_id = $this->db->insert_id();

		return $event_jury_book_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_jury_book', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_jury_book',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
