<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ShortUrl_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		if ($id > 0) {
			$this->db->where('id', $id);
		}

		return $this->db->get('short_urls');
	}

	public function get_all($data = []) {
        $this->db->select('short_urls.*');

		if (isset($data['code'])) {
			$this->db->where('short_urls.short_code', (int)$data['code']);
		}

        if (isset($data['url'])) {
			$this->db->where('short_urls.url', (int)$data['url']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('short_urls.short_code', $data['search'], 'after');
			$this->db->like('short_urls.url', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('short_urls._deleted', 0);

		$this->db->from('short_urls');

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
			'short_urls.id',
			'short_urls.date_added',
			'short_urls.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'short_urls.id';
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
		$validity = $this->check_duplication('add');

		if ($validity) {
			$this->db->insert('short_urls', $data + [
                'date_added'	=> date('Y-m-d H:i:s'),
                'date_modified'	=> date('Y-m-d H:i:s'),
            ]);

            $short_url_id = $this->db->insert_id();

            return $short_url_id;
		}
	}

	public function edit($id = 0) {
		$validity = $this->check_duplication('update', $id);

		if ($validity) {
			$this->db->where('id', (int)$id);

            $this->db->update('short_urls', $data + [
                'date_modified'	=> date('Y-m-d H:i:s'),
            ]);

		}
	}

	public function check_duplication($action = null, $id = 0) {

		$duplicate_check = $this->db->get_where('short_urls', [
			'short_code'    => $this->input->post('short_code'),
		]);

		if ($action == 'add') {
			if ($duplicate_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'update') {
			if ($duplicate_check->num_rows() > 0) {
				if ($duplicate_check->row()->id == $id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

    public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('short_urls',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

    public function getByUrl($url = '') {
		return $this->db->get_where('short_urls',  [
			'url'			=> $url,
            'status'        => 1,
			'_deleted'		=> 0,
		])->row_array();
	}

    public function getByCode($code = '') {
		return $this->db->get_where('short_urls',  [
			'short_code'	=> $code,
            'status'        => 1,
			'_deleted'		=> 0,
		])->row_array();
	}
}
