<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderComment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($order_comment_id = 0) {
		$this->db->select('order_comment.*');

		$this->db->where('order_comment.id', (int)$order_comment_id);
		$this->db->where('order_comment._deleted', 0);

		return $this->db->get('order_comment')->row_array();
	}

	public function getByOrder($order_code = ''){
		$this->db->select('order_comment.*');

		$this->db->where('order_comment.order_id', $order_code);
		return $this->db->get('order_comment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('order_comment.*');

		if (isset($data['order_id'])) {
			$this->db->where('order_comment.order_id', (int)$data['order_id']);
		}

		if (isset($data['description'])) {
			$this->db->where('order_comment.description', $data['description']);
		}

		if (!empty($data['search'])) {
			$this->db->like('order_comment.description', $data['search'], 'after');
		}

		$this->db->where('order_comment._deleted', 0);

		$this->db->from('order_comment');

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
			'order_comment.id',
			'order_comment.status',
			'order_comment.date_added',
			'order_comment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_comment.id';
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
		$this->db->insert('order_comment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$order_comment_id = $this->db->insert_id();

		return $order_comment_id;
	}

	public function edit($order_comment_id = 0, $data = []) {
		$this->db->where('id', $order_comment_id);
		$this->db->update('order_comment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($order_comment_id = 0) {
		$this->db->where('id', $order_comment_id);
		$this->db->update('order_comment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
