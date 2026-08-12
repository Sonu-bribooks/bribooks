<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Online_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($online_id = 0) {
		$this->db->select('online.*');

		$this->db->where('online.id', (int)$online_id);
		$this->db->where('online._deleted', 0);

		return $this->db->get('online')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('online.*');

		if (isset($data['user_id'])) {
			$this->db->where('online.user_id', (int)$data['user_id']);
		}

		if (isset($data['temp_id'])) {
			$this->db->where('online.temp_id', $data['temp_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('online.ip', $data['ip']);
		}

		if (isset($data['browser'])) {
			$this->db->where('online.browser', $data['browser']);
		}

		if (isset($data['platform'])) {
			$this->db->where('online.platform', $data['platform']);
		}

		if (isset($data['url'])) {
			$this->db->where('online.url', $data['url']);
		}

		if (isset($data['referer'])) {
			$this->db->where('online.referer', $data['referer']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('online.user_id', $data['search'], 'after');
			$this->db->like('online.temp_id', $data['search'], 'after');
			$this->db->or_like('online.ip', $data['search'], 'after');
			$this->db->or_like('online.url', $data['search'], 'after');
			$this->db->or_like('online.referer', $data['search'], 'after');
			$this->db->or_like('online.browser', $data['search'], 'after');
			$this->db->or_like('online.platform', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('online._deleted', 0);

		$this->db->from('online');

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
			'online.id',
			'online.date_added',
			'online.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'online.id';
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
		$insert_data = $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		];

		$update_data = $data + [
			'date_modified'	=> date('Y-m-d H:i:s')
		];

		$sql = $this->db->insert_string('online', $insert_data);
		$sql .= vsprintf(" ON DUPLICATE KEY UPDATE %s", [
			implode(', ', array_map(fn($item) => sprintf('%s=VALUES(%s)', $item, $item), array_keys($update_data)))
		]);

		$this->db->query($sql);

		$online_id = $this->db->insert_id();

		return $online_id;
	}

	public function edit($online_id = 0, $data = []) {
		$this->db->where('id', (int)$online_id);
		$this->db->update('online', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($online_id = 0) {
		$this->db->where('id', (int)$online_id);
		$this->db->update('online',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
