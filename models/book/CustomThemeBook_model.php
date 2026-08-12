<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CustomThemeBook_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('custom_theme_book_review.*');

		$this->db->where('custom_theme_book_review.id', (int)$id);
		$this->db->where('custom_theme_book_review._deleted', 0);

		return $this->db->get('custom_theme_book_review')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('custom_theme_book_review.*');

		if (!empty($data['user_id'])) {
			$this->db->where('custom_theme_book_review.manager_id', (int)$data['user_id']);
		}

        if (!empty($data['book_id'])) {
			$this->db->where('custom_theme_book_review.book_id', (int)$data['book_id']);
		}

		if (!empty($data['version'])) {
			$this->db->where('custom_theme_book_review.version', (int)$data['version']);
		}

		if (!empty($data['status'])) {
			$this->db->where('custom_theme_book_review.status', (int)$data['status']);
		}

		$this->db->where('custom_theme_book_review._deleted', 0);

		$this->db->from('custom_theme_book_review');

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
			'custom_theme_book_review.sort_order',
			'custom_theme_book_review.date_added',
			'custom_theme_book_review.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'custom_theme_book_review.id';
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
		$this->db->insert('custom_theme_book_review', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$custom_theme_book_review_id = $this->db->insert_id();

		return $custom_theme_book_review_id;
	}

	public function edit($custom_theme_book_review_id = 0, $data = []) {
		$this->db->where('id', (int)$custom_theme_book_review_id);
		$this->db->update('custom_theme_book_review', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($custom_theme_book_review_id = 0) {
		$this->db->where('id', (int)$custom_theme_book_review_id);
		$this->db->update('custom_theme_book_review',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getCustomThemeOrderedBook($data) {
		$this->db->select('
			order_product.product_id as id, order_product.version, book_version.name, book_version.author_name, book_version.category_id, book_version.user_id, book_version.status,
			book_version.date_added, book_version.date_published,book_version.date_approved,
			sum(quantity) as sold
		');

		if (!empty($data['user_id'])) {
			$this->db->where('book_version.user_id', (int)$data['user_id']);
		}

        if (!empty($data['book_id'])) {
			$this->db->where('order_product.product_id', (int)$data['book_id']);
		}

		if (isset($data['custom_theme'])) {
			$this->db->where("order_product.product_id IN (select page_version.book_id from page_version WHERE page_version.version = order_product.version AND page_version._deleted=0 AND page_version.custom_theme_id != 0)");
		}

		if (isset($data['custom_review_status'])) {
			$this->db->where(sprintf("order_product.product_id IN (select custom_theme_book_review.book_id from custom_theme_book_review WHERE custom_theme_book_review._deleted=0 AND custom_theme_book_review.book_id = order_product.book_id AND custom_theme_book_review.status = %s)", $data['custom_review_status']));
		}

		$this->db->where('order_product._deleted', 0);
		$this->db->where('order._deleted', 0);
		$this->db->where('order.status', 1);
		$this->db->join('order', 'order.id = order_product.order_id');
		$this->db->join('book_version', 'book_version.book_id = order_product.product_id AND book_version.version = order_product.version');

		$this->db->from('order_product');

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
			'order_product.id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'order_product.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->group_by('order_product.product_id, order_product.version');

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
