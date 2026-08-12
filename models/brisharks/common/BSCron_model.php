<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BSCron_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->bsdb = $this->load->database('brisharks', TRUE);
	}

	public function get($id = 0) {
		$this->bsdb->select('cron.*');

		$this->bsdb->where('cron.id', (int)$id);
		$this->bsdb->where('cron._deleted', 0);
		return $this->bsdb->get('cron')->row_array();
	}

	public function get_all($data = []) {
		$this->bsdb->select('cron.*');

		if (isset($data['code'])) {
			$this->bsdb->where('cron.code', $data['code']);
		}

		if (isset($data['action'])) {
			$this->bsdb->where('cron.action', $data['action']);
		}

		if (isset($data['status'])) {
			$this->bsdb->where('cron.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->bsdb->group_start();
			$this->bsdb->like('cron.code', $data['search'], 'after');
			$this->bsdb->group_end();
		}

		$this->bsdb->where('cron._deleted', 0);

		$this->bsdb->from('cron');

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
			'cron.id',
			'cron.date_added',
			'cron.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'cron.id';
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
		$data['data'] = (!empty($data['data']) && is_array($data['data']))
			? json_encode($data['data'])
			: ($data['data'] ?? '');

		if (empty($data['data']) || is_array($data['data'])) {
			unset($data['data']);
		}

		$this->bsdb->insert('cron', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->bsdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('cron_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$data['data'] = (!empty($data['data']) && is_array($data['data']))
			? json_encode($data['data'])
			: ($data['data'] ?? '');

		if (empty($data['data']) || is_array($data['data'])) {
			unset($data['data']);
		}

		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('cron', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('cron_update_successfully'));
	}

	public function delete($id = 0) {
		$this->bsdb->where('id', (int)$id);
		$this->bsdb->update('cron',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->bsdb->where('id', (int)$id);
			$this->bsdb->update('cron', [
				'status'		=> (int)$status,
				'date_modified' => date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('cron_updated_successfully'));
	}

	public function getByCode($code = '') {
		$this->bsdb->select('cron.*');

		$this->bsdb->where('cron.code', $code);
		$this->bsdb->where('cron.status', 1);
		$this->bsdb->where('cron._deleted', 0);

		return $this->bsdb->get('cron')->row_array();
	}
}
