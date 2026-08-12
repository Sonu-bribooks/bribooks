<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('ticket.*');

		$this->db->where('ticket.id', (int)$id);
		$this->db->where('ticket._deleted', 0);
		return $this->db->get('ticket')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('ticket.*');

		if (isset($data['code'])) {
			$this->db->where('ticket.code', $data['code']);
		}

		if (isset($data['user_type'])) {
			$this->db->where('ticket.user_type', $data['user_type']);
		}

		if (isset($data['user_id'])) {
			$this->db->where('ticket.user_id', (int)$data['user_id']);
		}

		if (isset($data['agent_id'])) {
			$this->db->where('ticket.agent_id', (int)$data['agent_id']);
		}

		if (!empty($data['department_ids']) && is_array($data['department_ids'])) {
			$this->db->where_in('ticket.department_id', $data['department_ids']);
		}

		if (isset($data['category_id'])) {
			$this->db->where('ticket.category_id', (int)$data['category_id']);
		}

		if (isset($data['priority_id'])) {
			$this->db->where('ticket.priority_id', (int)$data['priority_id']);
		}

		if (isset($data['status_id'])) {
			$this->db->where('ticket.status_id', (int)$data['status_id']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('ticket.subject', $data['search'], 'after');
			$this->db->or_like('ticket.code', $data['search'], 'after');
			$this->db->or_like('ticket.description', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('ticket._deleted', 0);

		$this->db->from('ticket');

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
			'ticket.date_added',
			'ticket.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'ticket.id';
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
		$this->db->insert('ticket', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();

		self::_notifyUsers($data + [
			'ticket_id'	=> $id,
			'date'		=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('ticket_added_successfully'));

		return $id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', (int)$id);
		$this->db->update('ticket', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('ticket_update_successfully'));
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('ticket', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	private function _notifyUsers($data = []) {
		$results = $this->department_model->getUsers($data['department_id']);

		$to = NULL;
		$cc = [];

		foreach ($results as $item) {
			$this->notification_model->save($item['user_id'] . '_ticket', [
				'ticket_id'	=> $data['ticket_id'],
				'code'		=> $data['code'],
				'subject'	=> $data['subject'],
				'message'	=> sprintf(_li('Ticket <a href="%s">(%s)</a> is assigned to you'), base_url('admin/ticket_details/' . $data['ticket_id']), $data['code']),
				'action'	=> 'ticket_add',
			]);

			self::_sendWebPush([
				'user_id'		=> $item['user_id'],
				'subject'		=> $data['subject'],
				'ticket_code'	=> $data['code'],
				'ticket_id'		=> $data['ticket_id'],
			]);

			$user_info = $this->user_model->get($item['user_id']);

			if (empty($user_info)) continue;

			if (empty($to)) {
				$to 	= $user_info['email'];
			} else {
				$cc[] 	= $user_info['email'];
			}
		}

		$subject 	= sprintf(_li('New Ticket Assigned %s'), $data['code']);
		$message 	= vsprintf(_li('
			Hi Team,<br><br>
			A new ticket has been raised against your department.<br><br>
			Ticket ID: <a href="%s">%s</a><br>
			Date: %s<br><br>
			Please review and take the necessary action.<br><br>
			Thanks
		'), [
			base_url('admin/ticket_details/' . $data['ticket_id']),
			$data['code'],
			$data['date'],
		]);

		!empty($to) && $this->alert_model->email(
			$to,
			$subject,
			$message,
			$cc,
		);
	}

	private function _sendWebPush($data = []) {
		if (!empty($token_info = $this->web_push_subscriber_model->get_all([
			'user_id' 		=> $data['user_id'],
			'item_type'		=> 'bb_cms',
		])['rows'][0] ?? [])) {
			send_webpush_notification(
				$token_info['token'],
				[
					'title' 	=> sprintf(_li('A new ticket (%s) is assigned to you'), $data['ticket_code']),
					'body' 		=> $data['subject'],
					'url' 		=> base_url('admin/ticket_details/' . $data['ticket_id']),
				]
			);
		}
	}
}
