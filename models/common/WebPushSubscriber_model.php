<?php defined('BASEPATH') OR exit('No direct script access allowed');

class WebPushSubscriber_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('*');
		$this->db->where('id', (int)$id);
		return $this->db->get('web_push_subscriber')->row_array();
	}

    public function get_all($data = []) {
		$this->db->select('web_push_subscriber.*');

		if (isset($data['item_id'])) {
			$this->db->where('web_push_subscriber.item_id', (int)$data['item_id']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('web_push_subscriber.user_id', (int)$data['user_id']);
		}

		if (isset($data['temp_user_id'])) {
			$this->db->where('web_push_subscriber.temp_user_id', $data['temp_user_id']);
		}

		if (isset($data['ip'])) {
			$this->db->where('web_push_subscriber.ip', trim($data['ip']));
		}

		if (isset($data['item_type'])) {
			$this->db->where('web_push_subscriber.item_type', trim($data['item_type']));
		}

        if (isset($data['token'])) {
			$this->db->where('web_push_subscriber.token', trim($data['token']));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('web_push_subscriber.user_id', $data['search']);
			$this->db->or_like('web_push_subscriber.item_id', $data['search']);
			$this->db->group_end();
		}

		$this->db->where('web_push_subscriber._deleted', 0);
		$this->db->from('web_push_subscriber');

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
			'web_push_subscriber.item_id',
			'web_push_subscriber.status',
			'web_push_subscriber.user_id',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'web_push_subscriber.date_added';
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
		$this->db->insert('web_push_subscriber', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s')
		]);

		$token_id = $this->db->insert_id();

		return $token_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('web_push_subscriber', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function editByUser($user_id = 0, $data = []) {
		$this->db->where('user_id', (int)$user_id);
		$this->db->update('web_push_subscriber', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByUser($user_id = 0) {
		$this->db->select('*');
		$this->db->where('user_id', (int)$user_id);
		return $this->db->get('web_push_subscriber')->row_array();
	}
}
