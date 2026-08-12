<?php defined('BASEPATH') or exit('No direct script access allowed');

class Coupon_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($coupon_id = 0) {
		$this->db->select('coupon.*');

		$this->db->where('coupon.id', (int)$coupon_id);
		$this->db->where('coupon._deleted', 0);

		return $this->db->get('coupon')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('coupon.*');

		if (isset($data['event_id'])) {
			$this->db->where('coupon.event_id', (int)$data['event_id']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('coupon.site_id', (int)$data['site_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('coupon.user_id', (int)$data['user_id']);
		}

		if (isset($data['item_id'])) {
			$this->db->where('coupon.item_id', (int)$data['item_id']);
		}

		if (isset($data['end_date_ge'])) {
			$this->db->where('coupon.date_end >= ', date('Y-m-d H:i:s', strtotime($data['end_date_ge'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('coupon.name', $data['search'], 'after');
			$this->db->or_like('coupon.code', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('coupon._deleted', 0);

		$this->db->from('coupon');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'coupon.name',
			'coupon.status',
			'coupon.date_added',
			'coupon.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'coupon.id';
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
		$query = $this->db->get_where('coupon', [
			'code' => $data['code']
		]);

		if ($query->num_rows() > 0) {
			$this->session->set_flashdata('error', 'Code Already exists');
			return false;
		} else {
			$this->db->insert('coupon', $data + [
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);
			$insert_id = $this->db->insert_id();
			return $insert_id;
		}
	}

	public function edit($coupon_id = 0, $data = []) {
		$this->db->where('id', (int)$coupon_id);
		$this->db->update('coupon', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function status($coupon_id = 0, $status = 0) {
		$this->db->where('id', $coupon_id);
		$res = $this->db->update('coupon',  [
			'status'		=> $status,
		]);
		return $res;
	}

	public function delete($coupon_id = 0) {
		$this->db->where('id', $coupon_id);
		$this->db->update('coupon',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByCouponCode($data = []) {
		$this->db->select('coupon.*');

		if (!empty($data['code'])) {
			$this->db->where('coupon.code', $data['code']);
		}

		if (!empty($data['coupon_type'])) {
			$this->db->where('coupon.coupon_type', $data['coupon_type']);
		}

		if (!empty($data['item_id'])) {
			$this->db->group_start();
			$this->db->where('coupon.item_id', (int)$data['item_id']);
			$this->db->or_where('coupon.item_id', 0);
			$this->db->group_end();
		}

		if (!empty($data['user_id'])) {
			$this->db->group_start();
			$this->db->where('coupon.user_id', (int)$data['user_id']);
			$this->db->or_where('coupon.user_id', 0);
			$this->db->group_end();
		}

		$this->db->where('coupon.status', 1);

		$current_date = date('Y-m-d H:i:s');

		$this->db->where('coupon.date_start <=', $current_date);
		$this->db->where('coupon.date_end >=', $current_date);

		$this->db->where('coupon._deleted', 0);

		return $this->db->get('coupon')->row_array();
	}

	public function updateUsedCount($coupon_id = 0) {
		$this->db->set('used_count', 'used_count+1', FALSE);
		$this->db->set('date_last_used', date('Y-m-d H:i:s'));
		$this->db->where('id', (int)$coupon_id);
		$this->db->update('coupon');
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('coupon', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}
	}
}
