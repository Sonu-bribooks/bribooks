<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CustomCover_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($custom_cover_id = 0) {
		$this->db->select('custom_cover.*');

		$this->db->where('custom_cover.id', (int)$custom_cover_id);
		$this->db->where('custom_cover._deleted', 0);

		return $this->db->get('custom_cover')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('custom_cover.*');

		if (isset($data['user_id'])) {
			$this->db->where('custom_cover.user_id', (int)$data['user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('custom_cover.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('custom_cover.image', $data['search'], 'after');
		}

		$this->db->where('custom_cover._deleted', 0);

		$this->db->from('custom_cover');

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
			'custom_cover.sort_order',
			'custom_cover.date_added',
			'custom_cover.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'custom_cover.id';
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
		if(empty($data['user_id']))
			return;

		$this->db->insert('custom_cover', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$custom_cover_id = $this->db->insert_id();

		return $custom_cover_id;
	}

	public function edit($custom_cover_id = 0, $data = []) {
		$this->db->where('id', (int)$custom_cover_id);
		$this->db->update('custom_cover', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($custom_cover_id = 0) {
		$this->db->where('id', (int)$custom_cover_id);
		$this->db->update('custom_cover',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
	public function getCustomCoverOrderedBook($data) {
		$this->db->select('
			op.product_id as id,
			op.version,
			bv.name,
			bv.author_name,
			bv.category_id,
			bv.user_id,
			bv.status,
			bv.date_added,
			bv.date_published,
			bv.date_approved,
			bs.sold,
			b.isbn,
			b.date_modified,
			bv.id as book_version_id
		');
	
		$this->db->from('book_version AS bv');
		$this->db->join('book AS b', 'b.id = bv.book_id', 'inner');
		$this->db->join('bookstore AS bs', 'bs.book_id = b.id', 'inner');
		$this->db->join('user_cover AS uc', 'uc.id = bv.user_cover_id', 'inner');
		$this->db->join('custom_cover AS cc', 'cc.id = uc.custom_cover_id', 'inner');
		$this->db->join('order_product AS op', 'op.product_id = b.id AND op.version = bv.version', 'inner');
		$this->db->join('order AS o', 'o.id = op.order_id', 'inner');
	
		$this->db->where('bv._deleted', 0);
		$this->db->where('b._deleted', 0);
		$this->db->where('bs._deleted', 0);
		$this->db->where('cc._deleted', 0);
		$this->db->where('uc._deleted', 0);
		$this->db->where('uc.custom_cover_id !=', 0);
		$this->db->where('op._deleted', 0);
		$this->db->where('o._deleted', 0);
		$this->db->where('o.status IN (1, 93)', null, false);
	
		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('b.name', $data['search']);
			$this->db->or_like('bv.author_name', $data['search']);
			if (is_numeric($data['search'])) {
				$this->db->or_where('b.id', $data['search']);
			}
			$this->db->group_end();
		}
	
		$this->db->group_by(['b.id', 'bv.version']);
	
		$total_query = clone $this->db;
		$total = $total_query->count_all_results();
	
		$sort_data = ['b.date_added', 'b.date_modified', 'b.id'];
		$sort = isset($data['sort']) && in_array($data['sort'], $sort_data) ? $data['sort'] : 'o.date_added';
		$order = isset($data['order']) && $data['order'] == 'ASC' ? 'ASC' : 'DESC';
	
		$this->db->order_by($sort, $order);
	
		if (isset($data['start']) && isset($data['limit'])) {
			$start = max(0, (int)$data['start']);
			$limit = max(1, (int)$data['limit']);
			$this->db->limit($limit, $start);
		}
	
		$result = $this->db->get()->result_array();

		return [
			'rows' => $result,
			'total' => $total
		];
	}
	
	
}
