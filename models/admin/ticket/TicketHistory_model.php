<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TicketHistory_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('ticket_history.*');

		$this->db->where('ticket_history.id', (int)$id);
		$this->db->where('ticket_history._deleted', 0);
		return $this->db->get('ticket_history')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('ticket_history.*');

		if (isset($data['agent_id'])) {
			$this->db->where('ticket_history.agent_id', (int)$data['agent_id']);
		}

		if (isset($data['ticket_id'])) {
			$this->db->where('ticket_history.ticket_id', (int)$data['ticket_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('ticket_history.name', $data['name']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('ticket_history.name', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('ticket_history._deleted', 0);

		$this->db->from('ticket_history');

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
			'ticket_history.date_added',
			'ticket_history.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'ticket_history.id';
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
		$this->db->insert('ticket_history', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		self::_notifyUsers($data);

		$this->session->set_flashdata('flash_message', _l('ticket_history_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('ticket_history', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('ticket_history_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('ticket_history',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	private function _notifyUsers($data = []) {
		$ticket_info = $this->ticket_model->get($data['ticket_id']);

		if ($ticket_info['agent_id'] == $this->session->userdata('user_id')) {
			$results = $this->department_model->getUsers($ticket_info['department_id']);

			foreach ($results as $item) {
				$this->notification_model->save($item['user_id'] . '_ticket', [
					'ticket_id'	=> $data['ticket_id'],
					'code'		=> $ticket_info['code'],
					'subject'	=> $data['message'],
					'message'	=> sprintf(_li('Ticket <a href="%s">(%s)</a> is updated'), base_url('admin/ticket_details/' . $ticket_info['id']), $ticket_info['code']),
					'action'	=> 'history_add',
				]);

				self::_sendWebPush([
					'user_id'		=> $item['user_id'],
					'subject'		=> $data['message'],
					'ticket_code'	=> $ticket_info['code'],
					'ticket_id'		=> $ticket_info['id'],
				]);
			}
		} else {
			$this->notification_model->save($ticket_info['agent_id'] . '_ticket', [
				'ticket_id'	=> $data['ticket_id'],
				'code'		=> $ticket_info['code'],
				'subject'	=> $data['message'],
				'message'	=> sprintf(_li('Ticket <a href="%s">(%s)</a> is updated'), base_url('admin/ticket_details/' . $ticket_info['id']), $ticket_info['code']),
				'action'	=> 'history_add',
			]);

			self::_sendWebPush([
				'user_id'		=> $ticket_info['agent_id'],
				'subject'		=> $data['message'],
				'ticket_code'	=> $ticket_info['code'],
				'ticket_id'		=> $ticket_info['id'],
			]);
		}
	}

	private function _sendWebPush($data = []) {
		if (!empty($token_info = $this->web_push_subscriber_model->get_all([
			'user_id' 		=> $data['user_id'],
			'item_type'		=> 'bb_cms',
		])['rows'][0] ?? [])) {
			send_webpush_notification(
				$token_info['token'],
				[
					'title' 	=> sprintf(_li('Ticket (%s) is updated'), $data['ticket_code']),
					'body' 		=> $data['subject'],
					'url' 		=> base_url('admin/ticket_details/' . $data['ticket_id']),
				]
			);
		}
	}
}
