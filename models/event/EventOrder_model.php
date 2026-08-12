<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_order_id = 0) {
		$this->db->select('event_order.*');

		$this->db->where('event_order.id', (int)$event_order_id);
		$this->db->where('event_order._deleted', 0);

		return $this->db->get('event_order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_order.*');

		if (isset($data['order_id'])) {
			$this->db->where('event_order.order_id', (int)$data['order_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_order.book_id', (int)$data['book_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		$this->db->where('event_order._deleted', 0);

		if (isset($data['site_id']) || isset($data['user_id'])) {
			$this->db->join('book', 'book.id = event_order.book_id', 'left');

			if (isset($data['site_id'])) {
				$this->db->join('users', 'users.id = book.user_id', 'left');
			}
		}

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

		$sort_data = [
			'event_order.quantity',
			'event_order.date_added',
			'event_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_order.id';
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
		$this->db->insert('event_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_order_id = $this->db->insert_id();

		return $event_order_id;
	}

	public function edit($event_order_id = 0, $data = []) {
		$this->db->where('id', (int)$event_order_id);
		$this->db->update('event_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($event_order_id = 0) {
		$this->db->where('id', (int)$event_order_id);
		$this->db->update('event_order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getSoldByBook($data = []) {
		$grouped = false;

		$this->db->select('event_order.book_id, SUM(event_order.quantity) AS quantity');

		if (isset($data['order_id'])) {
			$this->db->where('event_order.order_id', (int)$data['order_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('event_order.book_id', (int)$data['book_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('event_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('users.site_id', (int)$data['site_id']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('users.city_id', (int)$data['city_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('users.state_id', (int)$data['state_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('book.user_id', (int)$data['user_id']);
		}

		if (isset($data['quantity_ge'])) {
			$this->db->having('quantity >= ', (int)$data['quantity_ge']);
			$this->db->group_by('event_order.book_id');
			$grouped = true;
		}

		if (isset($data['quantity_le'])) {
			$this->db->having('quantity <= ', (int)$data['quantity_le']);
			!$grouped && $this->db->group_by('event_order.book_id');
			$grouped = true;
		}

		$this->db->where('event_order._deleted', 0);

		if (
			isset($data['site_id']) ||
			isset($data['user_id']) ||
			isset($data['city_id']) ||
			isset($data['state_id'])
		) {
			$this->db->join('book', 'book.id = event_order.book_id', 'left');

			if (isset($data['site_id']) || isset($data['city_id']) || isset($data['state_id'])) {
				$this->db->join('users', 'users.id = book.user_id', 'left');
			}

			!$grouped && $this->db->group_by('event_order.book_id');
		}

		$this->db->from('event_order');

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
			'quantity',
			'event_order.date_added',
			'event_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_order.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array()];
	}

	public function getTotalSoldByBook($event_id = 0, $book_id = 0) {
		$this->db->select_sum('event_order.quantity');

		return $this->db->get_where('event_order', [
			'event_id'		=> (int)$event_id,
			'book_id'		=> (int)$book_id,
			'_deleted'		=> 0,
		])->row()->quantity;
	}
}
