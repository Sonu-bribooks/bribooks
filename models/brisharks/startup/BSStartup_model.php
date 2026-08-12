<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSStartup_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('startup.*');

		$this->bsdb->where('startup.id', (int)$id);
		$this->bsdb->where('startup._deleted', 0);
		return $this->bsdb->get('startup')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('startup.*');

		if (isset($data['type'])) {
			$this->bsdb->where('startup.type', $data['type']);
		}

		if (isset($data['code'])) {
			$this->bsdb->where('startup.code', $data['code']);
		}

		if (isset($data['user_id'])) {
			$this->bsdb->where('startup.user_id', $data['user_id']);
		}

		if (isset($data['bb_user_id'])) {
			$this->bsdb->where('startup.bb_user_id', $data['bb_user_id']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('startup.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('startup.code', $data['search'], 'after');
			$this->bsdb->or_like('startup.user_id', $data['search'], 'after');
			$this->bsdb->or_like('startup.bb_user_id', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('startup._deleted', 0);

		$this->bsdb->from('startup');

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
			'startup.id',
			'startup.date_added',
			'startup.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'startup.id';
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
		$this->bsdb->insert('startup', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('startup_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('startup', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('startup_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('startup',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('startup', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('startup_updated_successfully'));
	}
}
