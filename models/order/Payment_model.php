<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model {
	public function get($payment_id = 0) {
		$this->db->where('payment.id', (int)$payment_id);
		$this->db->where('payment._deleted', 0);

		return $this->db->get('payment')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('payment.*');

		if (isset($data['site_id'])) {
			$this->db->where('payment.site_id', (int)$data['site_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('payment.order_id', (int)$data['order_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('payment.user_id', (int)$data['user_id']);
		}

		if (isset($data['address_id'])) {
			$this->db->where('payment.address_id', (int)$data['address_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('payment.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['coupon_id'])) {
			$this->db->where('payment.coupon_id', (int)$data['coupon_id']);
		}

		if (isset($data['provider'])) {
			$this->db->where('payment.provider', $data['provider']);
		}

		if (isset($data['total'])) {
			$this->db->where('payment.total', (double)$data['total']);
		}

		if (isset($data['status'])) {
			$this->db->where('payment.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('payment.total', $data['search'], 'after');
			$this->db->like('payment.provider', $data['search'], 'after');
		}

		$this->db->where('payment._deleted', 0);

		$this->db->from('payment');

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
			'payment.total',
			'payment.status',
			'payment.date_added',
			'payment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'payment.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function getList($data = []) {

		$this->db->select('payment.*, users.first_name, users.last_name, users.email, users.mobile');

		if (isset($data['email'])) {
			$this->db->where('users.email', $data['email']);
		}
		if (isset($data['mobile'])) {
			$this->db->where('users.mobile', $data['mobile']);
		}

		if (isset($data['status'])) {
			$this->db->where('payment.status', (int)$data['status']);
		}

		if (!empty($data['date_start'])) {
			$this->db->where('payment.date_added >= ', date('Y-m-d', strtotime($data['date_start'])));
		}

		if (!empty($data['date_end'])) {
			$this->db->where('payment.date_added < ', date('Y-m-d', strtotime($data['date_end'])));
		}

		$this->db->where('payment._deleted', 0);

		$this->db->join('users', 'users.id = payment.user_id', 'left');

		$this->db->from('payment');

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
			'payment.status',
			'payment.date_added',
			'payment.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'payment.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data) {
		if (self::get_all([
			'order_id'	=> (int)$data['order_id'],
		])['total'] !== 0) {
			return;
		}

		$this->db->insert('payment', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);

		$id = $this->db->insert_id();

		// Give royalty to the author
		if (!empty($data['order_id'])) {
			CI_Events::trigger('payment_created', [
				'order_id'	=> $data['order_id']
			]);
		}

		$this->session->set_flashdata('flash_message', _l('payment_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->update('payment', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);

		$this->session->set_flashdata('flash_message', _l('payment_updated_successfully'));
	}

	public function delete($payment_id = 0) {
		$this->db->where('id', (int)$payment_id);
		$this->db->update('payment',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
