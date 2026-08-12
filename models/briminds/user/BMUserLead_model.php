<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMUserLead_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($id = 0) {
		$this->bmdb->where('user_lead.id', (int)$id);
		$this->bmdb->where('user_lead._deleted', 0);

		return $this->bmdb->get('user_lead')->row_array();
	}

	public function get_all($data = []) {
		$this->bmdb->select('user_lead.*,');

		if (!empty($data['bb_user_id'])) {
			$this->bmdb->where('user_lead.bb_user_id', $data['bb_user_id']);
		}

		if (!empty($data['user_id'])) {
			$this->bmdb->where('user_lead.user_id', $data['user_id']);
		}

		if (!empty($data['email'])) {
			$this->bmdb->where('user_lead.owner_email', $data['email']);
		}

		if (!empty($data['mobile'])) {
			$this->bmdb->where('user_lead.owner_mobile', $data['mobile']);
		}

		if (isset($data['status'])) {
			$this->bmdb->where('user_lead.status', (int)$data['status']);
		}

		if (isset($data['verified'])) {
			$this->bmdb->where('user_lead.verified', (int)$data['verified']);
		}

		if (!empty($data['search'])) {
			$this->bmdb->group_start();
			$this->bmdb->like('user_lead.name', $data['search'], 'after');
			$this->bmdb->or_like('user_lead.code', $data['search'], 'after');
			$this->bmdb->group_end();
		}

		$this->bmdb->where('user_lead._deleted', 0);

		$this->bmdb->from('user_lead');

		$total = $this->bmdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bmdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'user_lead.name',
			'user_lead.status',
			'user_lead.date_added',
			'user_lead.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user_lead.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->bmdb->order_by($sort, $order);

		return ['rows' => $this->bmdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->bmdb->insert('user_lead', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bmdb->insert_id();

		$this->session->set_flashdata('flash_message', get_phrase('user_lead_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bmdb->where('id', $id);
		$this->bmdb->update('user_lead', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', get_phrase('user_lead_update_successfully'));
	}
	public function delete($id = 0) {
		$this->bmdb->where('id', (int)$id);
		$this->bmdb->update('user_lead', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}
}
