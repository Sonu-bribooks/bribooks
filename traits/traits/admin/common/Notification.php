<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Notification {
	public function getNotification($type = 'order') {
		$this->load->model('common/Notification_model', 'notification_model');
		// Get All Order Events
		$json = $this->notification_model->getEventByUserId($this->session->userdata('user_id'), $type);

		if ($json) {
			log_kb(['Notification::Recieved:: ' => $json]);
			$json['event_id'] = $this->session->userdata('user_id') . '_' . $type;
		}

		$this->notification_model->send($type, json_encode($json));
	}

	public function removeNotification() {
		$this->load->model('common/Notification_model', 'notification_model');
		$json = [];

		if ($this->input->method() == 'post' && $this->input->post('event_id')) {
			$this->notification_model->remove($this->input->post('event_id'));
			$json['success'] = _l('success');
		}

		output_json($json);
	}

	public function notification($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'subject',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();

			$data['message'] 	= $this->input->post('message', FALSE);
			$data['message'] 	= _allowSpecificHtmlTags($data['message']);

			$this->notification_model->add($data);

			redirect(base_url('admin/notification'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();

			$data['message'] 	= $this->input->post('message', FALSE);
			$data['message'] 	= _allowSpecificHtmlTags($data['message']);

			$this->notification_model->update($param2, $data);

			redirect(base_url('admin/notification'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->notification_model->delete($param2);
			
			redirect(base_url('admin/notification'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('notification');
		$data['action_add'] 	= base_url('admin/notification_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_notification');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/notification_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type'	=> 'confirm',
				'url'	=> 'admin/notification/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function notification_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_notification');
			$data['action'] 						= base_url('admin/notification/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('notification');
			$data['action'] 						= base_url('admin/notification/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->notification_model->getById($param2);
		}

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'subject',
			'label'		=> _l('subject'),
			'required'	=> true,
			'value'		=> $info['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'message',
			'label'		=> _l('message'),
			'required'	=> true,
			'value'		=> $info['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'attachment_type',
			'label'		=> _l('select_attachment_type'),
			'required'	=> false,
			'value'		=> $info['attachment_type'] ?? '',
			'options'	=> [
				[
					'value' => 'IMAGE',
					'label' => _l('Image'),
				],
				[
					'value' => 'VIDEO',
					'label' => _l('Video'),
				],
				[
					'value' => 'PDF',
					'label' => _l('pdf'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'file',
			'key'		=> 'attachment_file',
			'label'		=> _l('attachment_file'),
			'required'	=> false,
			'value'		=> $info['attachment_file'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_notification() {
		$json['data'] 	= [];
		$columns 		= $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->notification_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'subject'				=> $result['subject'] ?? '',
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
