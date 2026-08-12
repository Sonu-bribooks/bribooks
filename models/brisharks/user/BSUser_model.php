<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSUser_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('user.*');

		$this->bsdb->where('user.id', (int)$id);
		$this->bsdb->where('user._deleted', 0);
		return $this->bsdb->get('user')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('user.*');

		if (isset($data['type'])) {
			$this->bsdb->where('user.type', $data['type']);
		}

		if (isset($data['email'])) {
			$this->bsdb->where('user.email', $data['email']);
		}

		if (isset($data['user_id'])) {
			$this->bsdb->where('user.id', $data['user_id']);
		}

		if (isset($data['mobile'])) {
			$this->bsdb->where('user.mobile', $data['mobile']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('user.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('user.email', $data['search'], 'after');
			$this->bsdb->or_like('user.mobile', $data['search'], 'after');
			$this->bsdb->or_like('user.id', $data['search'], 'after');
			$this->bsdb->or_like('user.grade', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('user._deleted', 0);

		$this->bsdb->from('user');

		$total = $this->bsdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->bsdb->limit($data['limit'], $data['start']);
		} else {
			$this->bsdb->limit(10, 0);
		}

		$sort_data = [
			'user.id',
			'user.date_added',
			'user.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'user.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->bsdb->order_by($sort, $order);

		$results = $this->bsdb->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->bsdb->insert('user', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('user_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('user', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('user_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('user',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('user', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('user_updated_successfully'));
	}
}
