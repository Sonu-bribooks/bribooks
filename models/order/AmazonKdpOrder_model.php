<?php defined('BASEPATH') or exit('No direct script access allowed');

class AmazonKdpOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function get($id = 0) {
		$this->rdb->where('amazon_kdp_order.id', (int)$id);
		$this->rdb->where('amazon_kdp_order._deleted', 0);
		return $this->rdb->get('amazon_kdp_order')->row_array();
	}

	public function get_all($data = []) {
		$this->rdb->select('amazon_kdp_order.*');

		if (!empty($data['user_id'])) {
			$this->rdb->where('amazon_kdp_order.user_id', $data['user_id']);
		}

		if (!empty($data['book_id'])) {
			$this->rdb->where('amazon_kdp_order.book_id', $data['book_id']);
		}

		if (!empty($data['order_id'])) {
			$this->rdb->where('amazon_kdp_order.order_id', $data['order_id']);
		}

		if (!empty($data['royalty_date'])) {
			$this->rdb->where('amazon_kdp_order.royalty_date', $data['royalty_date']);
		}

		if (!empty($data['order_date'])) {
			$this->rdb->where('amazon_kdp_order.order_date', $data['order_date']);
		}

		if (!empty($data['book_name'])) {
			$this->rdb->where('amazon_kdp_order.book_name', $data['book_name']);
		}

		if (!empty($data['isbn'])) {
			$this->rdb->where('amazon_kdp_order.isbn', $data['isbn']);
		}

		if (!empty($data['author_name'])) {
			$this->rdb->where('amazon_kdp_order.author_name', $data['author_name']);
		}

		if (!empty($data['marketplace'])) {
			$this->rdb->where('amazon_kdp_order.marketplace', $data['marketplace']);
		}

		if (isset($data['quantity'])) {
			$this->rdb->where('amazon_kdp_order.quantity', (int)$data['quantity']);
		}

		if (isset($data['status'])) {
			$this->rdb->where('amazon_kdp_order.status', (int)$data['status']);
		}

		if (isset($data['is_duplicate'])) {
			$this->rdb->where('amazon_kdp_order.is_duplicate', (int)$data['is_duplicate']);
		}

		if (!empty($data['search'])) {
			$this->rdb->group_start();
			$this->rdb->like('amazon_kdp_order.isbn', $data['search'], 'both');
			$this->rdb->or_like('amazon_kdp_order.book_name', $data['search'], 'both');
			$this->rdb->or_like('amazon_kdp_order.author_name', $data['search'], 'both');
			$this->rdb->or_like('amazon_kdp_order.order_date', $data['search'], 'both');
			$this->rdb->or_like('amazon_kdp_order.currency', $data['search'], 'both');
			$this->rdb->or_like('amazon_kdp_order.marketplace', $data['search'], 'both');
			$this->rdb->or_like('amazon_kdp_order.price_without_tax', $data['search'], 'both');
			$this->rdb->group_end();
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->rdb->where('amazon_kdp_order.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		$this->rdb->where('amazon_kdp_order._deleted', 0);

		$this->rdb->from('amazon_kdp_order');

		$total = $this->rdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->rdb->limit($data['limit'], $data['start']);
		} else {
			$this->rdb->limit(10, 0);
		}

		$sort_data = [
			'amazon_kdp_order.id',
			'amazon_kdp_order.status',
			'amazon_kdp_order.date_added',
			'amazon_kdp_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'amazon_kdp_order.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->rdb->order_by($sort, $order);

		$row = $this->rdb->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}

	public function add($data) {
		$this->db->insert('amazon_kdp_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('amazon_kdp_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('amazon_kdp_order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
