<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lesson_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($lesson_id = 0) {
		$this->db->select('lesson.*');

		$this->db->where('lesson.id', (int)$lesson_id);
		$this->db->where('lesson._deleted', 0);

		return $this->db->get('lesson')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('lesson.*');

		if (isset($data['event_id'])) {
			$this->db->where('lesson.event_id', (int)$data['event_id']);
		}

		if (isset($data['event_ids'])) {
			$this->db->where(
				sprintf(
					'lesson.event_id = (
						SELECT event_id
						FROM lesson
						WHERE event_id IN (%s)
						AND _deleted = 0
						ORDER BY event_id DESC
						LIMIT 1
					)',
					$data['event_ids']
				),
				null,
				false
			);
		}

		if (isset($data['sort_order'])) {
			$this->db->where('lesson.sort_order', (int)$data['sort_order']);
		}

		if (isset($data['status'])) {
			$this->db->where('lesson.status', (int)$data['status']);
		}

		if (isset($data['grade'])) {
			$this->db->group_start();
				$this->db->where('lesson.grade IS NULL', null, false);
				$this->db->or_where('lesson.grade', '');

				if (!empty($data['grade'])) {
					$this->db->or_where(
						"FIND_IN_SET(" . (int)$data['grade'] . ", lesson.grade) > 0",
						null,
						false
					);
				}
			$this->db->group_end();
		}


		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('lesson.name', $data['search'], 'after');
			$this->db->or_like('lesson.description', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('lesson._deleted', 0);

		$this->db->from('lesson');

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
			'lesson.name',
			'lesson.sort_order',
			'lesson.status',
			'lesson.date_added',
			'lesson.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'lesson.id';
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
		$this->db->insert('lesson', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$lesson_id = $this->db->insert_id();

		return $lesson_id;
	}

	public function edit($lesson_id = 0, $data = []) {
		$this->db->where('id', (int)$lesson_id);
		$this->db->update('lesson', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($lesson_id = 0) {
		$this->db->where('id', (int)$lesson_id);
		$this->db->update('lesson',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', (int)$id);
			$this->db->update('lesson', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
