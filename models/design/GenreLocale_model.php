<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GenreLocale_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('genre_locale.*');

		$this->db->where('genre_locale.id', (int)$id);
		$this->db->where('genre_locale._deleted', 0);

		return $this->db->get('genre_locale')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('genre_locale.*');

		if (isset($data['genre_id'])) {
			$this->db->where('genre_locale.genre_id', (int)$data['genre_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('genre_locale.status', (int)$data['status']);
		}

		$this->db->where('genre_locale._deleted', 0);

		$this->db->from('genre_locale');

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
			'genre_locale.status',
			'genre_locale.sort_order',
			'genre_locale.date_added',
			'genre_locale.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'genre_locale.id';
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
		$this->db->insert('genre_locale', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('genre_locale', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('genre_locale',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
