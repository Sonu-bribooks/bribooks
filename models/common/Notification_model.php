<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Notification_model extends CI_Model {
	public function send($event = 'message', $data = '') {
		$this->output->set_header('Content-Type: text/event-stream');
		$this->output->set_header('Cache-Control: no-cache');

		$this->output->set_output("event: {$event}\ndata: {$data}\n\n");
	}

	public function getNotification($notification_id) {
		return $this->db->get_where('notification', [
			'id' 		=> $notification_id,
		])->row_array();
	}

	public function addNotification($data = []) {
		$this->db->insert('notification', $data);

		return $this->db->insert_id();
	}

	public function editNotification($notification_id, $data = []) {
		$this->db->set($data);

		$this->db->where('notification_id', $notification_id);

		$this->db->update('notification');
	}

	public function deleteNotification($notification_id, $data = []) {
		$this->db->where('notification_id', $notification_id);
		$this->db->delete('notification');
	}

	public function get_all($data = array()) {
		$this->db->select('notification.*');

		if (isset($data['user_id'])) {
			$this->db->where('notification.user_id', (int)$data['user_id']);
		}

		if (isset($data['filter_user_id'])) {
			$this->db->where('notification.user_id', (int)$data['filter_user_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('notification.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('notification.title', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('notification._deleted', 0);

		$this->db->from('notification');

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
			'notification.id',
			'notification.title',
			'notification.status',
			'notification.date_added',
			'notification.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'notification.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function markRead($notification_id) {
		return $this->db->get_where('notification', [
			'id' 		=> $notification_id,
		])->row_array();
	}

	public function save($event_id, $data = [], $save = false) {
		if (!is_dir(DIR_NOTIFICATION_STORAGE)) {
			mkdir(DIR_NOTIFICATION_STORAGE, 0777);
			chmod(DIR_NOTIFICATION_STORAGE, 0777);
			@touch(DIR_NOTIFICATION_STORAGE . '/' . 'index.html');
		}

		if (is_writable(DIR_NOTIFICATION_STORAGE)) {
			if ($save) {
				$explode = explode('_', $event_id);

				$notification_id = self::addNotification([
					'user_id'			=> array_shift($explode),
					'type'				=> array_shift($explode),
					'data'				=> json_encode($data + [
						'user_id'			=> $this->session->userdata('user_id'),
					])
				]);

				file_put_contents(DIR_NOTIFICATION_STORAGE . $event_id, serialize($data + ['notification_id' => $notification_id]));
			} else {
				file_put_contents(DIR_NOTIFICATION_STORAGE . $event_id, serialize($data));
			}
		}

		return false;
	}

	public function edit($event_id, $data = [], $save = false) {
		if ($event_data = $this->get($event_id)) {
			$this->save($event_id, $data + $event_data, $save);
		} else {
			$this->save($event_id, $data, $save);
		}
	}

	public function get($event_id) {
		if (is_file(DIR_NOTIFICATION_STORAGE . $event_id)) {
			$data = file_get_contents(DIR_NOTIFICATION_STORAGE . $event_id);
			$data = unserialize($data);

			if (is_array($data)) {
				$data += ['time' => filemtime(DIR_NOTIFICATION_STORAGE . $event_id)];
			}

			return $data;
		}
	}

	public function remove($event_id, $data = null) {
		if ($data && is_file(DIR_NOTIFICATION_STORAGE . $event_id)) {
			$event_data = $this->get($event_id);

			if (is_array($event_data)) {
				unset($event_data[$data]);
				$this->save($event_id, $event_data);
			}
		} else {
			return is_file(DIR_NOTIFICATION_STORAGE . $event_id) && unlink(DIR_NOTIFICATION_STORAGE . $event_id);
		}
	}

	public function getEventByUserId($user_id, $event = '') {
		if ($event && empty($user_id)) {
			$events = glob(DIR_NOTIFICATION_STORAGE . '*' . '_' . $event);
		} elseif ($event && !empty($user_id)) {
			$events = glob(DIR_NOTIFICATION_STORAGE . $user_id . '_' . $event . '*');
		} else {
			$events = glob(DIR_NOTIFICATION_STORAGE . $user_id . '*');
		}

		$event_data = [];

		foreach ($events as $event_i) {
			$event_name = trim(substr($event_i, strrpos($event_i, '_') + 1));
			$event_id = substr($event_i, strrpos($event_i, '/') + 1);

			$event_data_i = $this->get($event_id);
			$event_data_i = is_array($event_data_i) ? $event_data_i : [];

			if ($event) {
				$event_data[$event_name][] = $event_data_i + ['event_id' => $event_id];
			} else {
				$event_data[$event_name] = $event_data_i + ['event_id' => $event_id];
			}
		}

		return $event_data;
	}

	public function getById($notification_id = 0) {
		$this->db->select('notification.*');

		$this->db->where('notification.id', (int)$notification_id);
		$this->db->where('notification._deleted', 0);

		return $this->db->get('notification')->row_array();
	}

	public function add($data = []) {
		$this->db->insert('notification', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$notification_id = $this->db->insert_id();

		return $notification_id;
	}

	public function update($notification_id = 0, $data = []) {
		$this->db->where('id', $notification_id);
		$this->db->update('notification', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($notification_id = 0) {
		$this->db->where('id', $notification_id);
		$this->db->update('notification',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
