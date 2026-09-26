<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventOrderAmazon_model extends CI_Model {
    public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('event_order_amazon.*');
		
		$this->db->where('event_order_amazon.id', (int)$id);
		$this->db->where('event_order_amazon._deleted', 0);

		return $this->db->get('event_order_amazon')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('event_order_amazon.*');

		if (!empty($data['event_id'])) {
			$this->db->where('event_order_amazon.event_id', (int)$data['event_id']);
		}

		if (!empty($data['book_id'])) {
			$this->db->where('event_order_amazon.book_id', (int)$data['book_id']);
		}

        if (!empty($data['user_id'])) {
			$this->db->where('event_order_amazon.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('event_order_amazon.event_id', $data['search'], 'after');
			$this->db->or_like('event_order_amazon.book_id', $data['search'], 'after');
			$this->db->or_like('event_order_amazon.user_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('event_order_amazon._deleted', 0);

		$this->db->from('event_order_amazon');

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
			'event_order_amazon.date_added',
			'event_order_amazon.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_order_amazon.id';
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
		$this->db->insert('event_order_amazon', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$event_order_amazon_id = $this->db->insert_id();

		return $event_order_amazon_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_order_amazon', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('event_order_amazon',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

    public function getTotalSoldByBook($event_id = 0, $book_id = 0) {
		$this->db->select_sum('event_order_amazon.quantity');

		return $this->db->get_where('event_order_amazon', [
			'event_id'		=> (int)$event_id,
			'book_id'		=> (int)$book_id,
			'_deleted'		=> 0,
		])->row()->quantity;
	}

}