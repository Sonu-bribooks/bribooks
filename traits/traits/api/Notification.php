<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Notification {
	public function getUserNotifications() {
		if (!$this->json) {
			$this->load->model('Alert_model', 'alert_model');
			$user_info = $this->user_model->get($this->session->userdata('user_id'));

			$this->json['notifications'] = array_map(function ($item) use ($user_info) {
				return [
					'event_id'		=> $item['event_id'],
					'status'		=> $item['status'],
					'type'			=> $item['type'],
					'heading'		=> $item['heading'],
					'description'	=> $this->alert_model->formatCommonEmailContent($item['description'], ['site_id' => $user_info['site_id'] ?? 0]) ?? '',
					'can_share'		=> $item['can_share'],
					'date_added'	=> $item['date_added'],
				];
			}, $this->user_notification_model->get_all([
				'event_id' => $this->input->post('event_id') ?? 0
			])['rows'] ?? []);
		}
	}

	public function saveWebPushSubscriber() {
		$this->form_validation->set_rules('token', _l('token'), 'trim|required');
		$this->form_validation->set_rules('url', _l('url'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [];

			if (!empty($this->session->userdata('user_id'))) {
				$filter_data['user_id'] 		= (int)$this->session->userdata('user_id');
			} elseif (!empty(get_bb_user_id())) {
				$filter_data['temp_user_id'] 	= get_bb_user_id();
			} else {
				return $this->json['success'] 	= true;
			}

			if (empty($filter_data['user_id']) && empty($filter_data['temp_user_id'])) {
				return $this->json['success'] = true;
			}

			if (!empty($web_user_info = $this->web_push_subscriber_model->get_all($filter_data)['rows'][0] ?? '')) {
				$this->web_push_subscriber_model->edit($web_user_info['id'], [
					'user_id'		=> (int)$this->session->userdata('user_id') ?? $web_user_info['id'],
					'temp_user_id'	=> (int)$this->session->userdata('user_id') ?? get_bb_user_id(),
					'item_id'		=> (int)$this->input->post('item_id') ?? 0,
					'item_type'		=> $this->input->post('item_type') ?? NULL,
					'token'			=> $this->input->post('token') ?? NULL,
					'url'			=> $this->input->post('url') ?? NULL,
					'ip'			=> $this->input->ip_address(),
				]);
			} else {
				$this->web_push_subscriber_model->add([
					'user_id'		=> (int)$this->session->userdata('user_id') ?? 0,
					'temp_user_id'	=> (int)$this->session->userdata('user_id') ?? get_bb_user_id(),
					'item_id'		=> (int)$this->input->post('item_id') ?? 0,
					'item_type'		=> $this->input->post('item_type') ?? NULL,
					'token'			=> $this->input->post('token') ?? NULL,
					'url'			=> $this->input->post('url') ?? NULL,
					'ip'			=> $this->input->ip_address(),
				]);
			}

			$this->json['success'] = true;
		}
	}

	public function getNotification() {
		$this->form_validation->set_rules('notification_id', _l('notification_id'), [
			'trim',
			'required',
			['notification', [$this->validate_model, 'notification']]
		]);

		$this->form_validation->set_rules('user_id', _l('user_id'), [
			'trim',
			'required',
			['user', [$this->validate_model, 'user']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$notification_info 	= $this->notification_model->getById($this->input->post('notification_id'));
			$user_info 			= $this->user_model->get($this->input->post('user_id'));
			$site_info 			= $this->site_model->get($user_info['site_id'] ?? 0);
			$school_info 		= $this->school_model->getBySiteID($site_info['id'] ?? 0);
			$book_info 			= $this->input->post('book_id') ? $this->book_model->get($this->input->post('book_id') ?? 0) : [];
			$event_info 		= $this->input->post('event_id') ? $this->event_model->get($this->input->post('event_id') ?? 0) : [];
			$state_info 		= $this->state_model->get($user_info['state_id'] ?? 0);
			$city_info 			= $this->city_model->get($user_info['city_id'] ?? 0);

			$data = [
				'user_id' 		=> $user_info['id'] ?? 0,
				'name' 			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'school_id' 	=> $school_info['id'] ?? 0,
				'site_id' 		=> $site_info['id'] ?? 0,
				'school_name' 	=> $site_info['name'] ?? '',
				'event_id' 		=> $event_info['id'] ?? 0,
				'event_name' 	=> $event_info['name'] ?? '',
				'event_slug' 	=> $event_info['slug'] ?? '',
				'book_name' 	=> $book_info['name'] ?? '',
				'author_name' 	=> $book_info['author_name'] ?? '',
				'book_slug' 	=> $book_info['slug'] ?? '',
				'state_id' 		=> $state_info['id'] ?? 0,
				'state_name' 	=> $state_info['name'] ?? '',
				'city_id' 		=> $city_info['id'] ?? 0,
				'city_name' 	=> $city_info['name'] ?? '',
			];

			$this->json['notification'] = $data + [
				'notification_id' 	=> $notification_info['id'] ?? 0,
				'subject' 			=> !empty($notification_info['subject']) ? format_message_with_data($notification_info['subject'], $data) : '',
				'message' 			=> !empty($notification_info['message']) ? format_message_with_data($notification_info['message'], $data) : '',
				'attachment_type' 	=> $notification_info['attachment_type'] ?? '',
				'attachment_file' 	=> !empty($notification_info['attachment_file']) ? ($this->config->item('cloudfront_url'). $this->config->item('s3_user_gallery') . $notification_info['attachment_file']) : ''
			];
		}
	}
}
