<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolOrder_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('school_order.*,
			school_order.type as name,
			schools.name as school_name,
			schools.authorized_person,
			schools.owner_email,
			schools.owner_mobile,
			schools.address,
			schools.pincode as zipcode
		');

		$this->db->where('school_order.id', (int)$id);
		$this->db->where('school_order._deleted', 0);

		$this->db->join('schools', 'schools.id = school_order.school_id', 'left');

		return $this->db->get('school_order')->row_array();
	}

	public function getByCode($order_code = '') {
		$this->db->select('school_order.*,
			schools.name as school_name,
			schools.authorized_person,
			schools.owner_email,
			schools.owner_mobile,
		');

		$this->db->where('school_order.order_code', $order_code);
		$this->db->where('school_order._deleted', 0);

		$this->db->join('schools', 'schools.id = school_order.school_id', 'left');

		return $this->db->get('school_order')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('school_order.*,
			school_order.type as name,
			schools.name as school_name,
			schools.authorized_person,
			schools.alternate_authorized_person,
			schools.owner_email,
			schools.owner_mobile,
			schools.alternate_owner_mobile,
			schools.alternate_owner_email,
			schools.address
		');

		if (isset($data['parent_id'])) {
			$this->db->where('school_order.parent_id', (int)$data['parent_id']);
		}

		if (isset($data['order_id'])) {
			$this->db->where('school_order.id', (int)$data['order_id']);
		}

		if (isset($data['event_id'])) {
			$this->db->where('school_order.event_id', (int)$data['event_id']);
		}

		if (isset($data['school_id'])) {
			$this->db->where('school_order.school_id', (int)$data['school_id']);
		}

		if (isset($data['currency_id'])) {
			$this->db->where('school_order.currency_id', (int)$data['currency_id']);
		}

		if (isset($data['pickup_location_id'])) {
			$this->db->where('school_order.pickup_location_id', (int)$data['pickup_location_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('school_order.status', (int)$data['status']);
		}

		if (isset($data['ne_status'])) {
			if (is_array($data['ne_status'])) {
				$this->db->where_not_in('school_order.status', $data['ne_status']);
			} elseif ($data['ne_status']) {
				$this->db->where_not_in('school_order.status', [0, (int)$data['ne_status']]);
			} else {
				$this->db->where('school_order.status!=', (int)$data['ne_status']);
			}
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('school_order.date_added BETWEEN "' . date('Y-m-d', strtotime($data['startdate'])) . '" and "' . date('Y-m-d 23:59:59', strtotime($data['enddate'])) . '"');
		}

		if (isset($data['shipping_status'])) {
			$this->db->where('school_order.shipping_status', (int)$data['shipping_status']);
		}

		if (isset($data['is_registered']) && isset($data['event_id'])) {
			$this->db->where(sprintf('school_id IN (SELECT school_id FROM school_lead WHERE event_id = %s AND verified = %s AND _deleted = 0)', (int)$data['event_id'], (int)$data['is_registered']));
		} elseif (isset($data['is_registered'])) {
			$this->db->where(sprintf('school_id IN (SELECT school_id FROM school_lead WHERE verified = %s AND _deleted = 0)', (int)$data['is_registered']));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('schools.name', $data['search'], 'after');
			$this->db->or_like('school_order.school_id', $data['search'], 'after');
			$this->db->or_like('schools.site_id', $data['search'], 'after');
			$this->db->or_like('schools.authorized_person', $data['search'], 'after');
			$this->db->or_like('schools.owner_email', $data['search'], 'after');
			$this->db->or_like('schools.owner_mobile', $data['search'], 'after');
			$this->db->or_like('school_order.order_code', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('school_order._deleted', 0);

		$this->db->join('schools', 'schools.id = school_order.school_id', 'left');
		$this->db->from('school_order');

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
			'school_order.date_added',
			'school_order.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_order.id';
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
		$this->db->insert('school_order', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('school_order_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('school_order_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('school_order',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getProducts($order_id = 0) {
		$order_info = self::get_all(['order_id' => (int)$order_id])['rows'] ?? [];

		return $order_info;
	}
	public function editById($id = 0, $data = []) {
		return $this->db->update('school_order', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}
}
