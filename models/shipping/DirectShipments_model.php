<?php defined('BASEPATH') or exit('No direct script access allowed');

class DirectShipments_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->rdb = $this->load->database('replica', TRUE);
	}

	public function get($id = 0) {
		$this->rdb->where('direct_shipments.id', (int)$id);
		$this->rdb->where('direct_shipments._deleted', 0);
		return $this->rdb->get('direct_shipments')->row_array();
	}

	public function get_all($data = []) {
		$this->rdb->select('direct_shipments.*');

		if (!empty($data['event_name'])) {
			$this->rdb->where('direct_shipments.event_name', $data['event_name']);
		}

		if (!empty($data['type'])) {
			$this->rdb->where('direct_shipments.type', $data['type']);
		}

		if (!empty($data['consignee_name'])) {
			$this->rdb->where('direct_shipments.consignee_name', $data['consignee_name']);
		}

		if (!empty($data['consignee_pincode'])) {
			$this->rdb->where('direct_shipments.consignee_pincode', $data['consignee_pincode']);
		}

		if (isset($data['status'])) {
			$this->rdb->where('direct_shipments.status', (int)$data['status']);
		}

		if (isset($data['is_duplicate'])) {
			$this->rdb->where('direct_shipments.is_duplicate', (int)$data['is_duplicate']);
		}

		if (!empty($data['search'])) {
			$this->rdb->group_start();
			$this->rdb->like('direct_shipments.reference_no', $data['search'], 'after');
			$this->rdb->or_like('direct_shipments.event_name', $data['search'], 'after');
			$this->rdb->or_like('direct_shipments.type', $data['search'], 'both');
			$this->rdb->or_like('direct_shipments.consignee_name', $data['search'], 'both');
			$this->rdb->or_like('direct_shipments.consignee_address1', $data['search'], 'both');
			$this->rdb->or_like('direct_shipments.consignee_address2', $data['search'], 'both');
			$this->rdb->or_like('direct_shipments.consignee_pincode', $data['search'], 'after');
			$this->rdb->or_like('direct_shipments.consignee_mobile', $data['search'], 'after');
			$this->rdb->or_like('direct_shipments.consignee_email_id', $data['search'], 'after');
			$this->rdb->or_like('direct_shipments.shipping_tracking_info', $data['search'], 'both');
			$this->rdb->or_like('direct_shipments.cancel_remark', $data['search'], 'both');
			$this->rdb->group_end();
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->rdb->where('direct_shipments.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'] . ' 00:00:00')) . '" and "' . date('Y-m-d H:i:s', strtotime($data['enddate'] . ' 23:59:59')) . '"');
		}

		$this->rdb->where('direct_shipments._deleted', 0);

		$this->rdb->from('direct_shipments');

		$total = $this->rdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->rdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'direct_shipments.total',
			'direct_shipments.status',
			'direct_shipments.date_added',
			'direct_shipments.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'direct_shipments.id';
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
		$this->db->insert('direct_shipments', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$id = $this->db->insert_id();

		return $id;
	}

	public function edit($id = 0, $data = []) {
		return $this->db->update('direct_shipments', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('direct_shipments',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
