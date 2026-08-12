<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CertificateMessageTemplate {
	public function ajax_certificate_message_template($event_id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'type',
			'country_code',
			'subject',
			'whatsapp_template_id',
			'min_sold',
			'max_sold',
			'fomo',
			'league',
			'sort_order',
			'status',
			'date_modified',
			'actions',
		];

		$data['actions'] 		= [
			[
				'key'		=> 'edit',
				'type'		=> 'callback',
				'callback'	=> 'form_edit',
				'url'		=> sprintf('admin/ajax_certificate_message_template_form/%d/', $event_id),
			],
			[
				'key'		=> 'status',
				'type'		=> 'callback',
				'callback'	=> 'form_status',
				'url'		=> sprintf('admin/ajax_certificate_message_template_crud/%d/status/', $event_id),
			],
			[
				'key'		=> 'delete',
				'type'		=> 'callback',
				'callback'	=> 'form_delete',
				'url'		=> sprintf('admin/ajax_certificate_message_template_crud/%d/delete/', $event_id),
			],
		];

		$data['page_title'] 	= _l('certificate_message_template');
		$data['action_form'] 	= base_url(sprintf('admin/ajax_certificate_message_template_form/%d/', (int)$event_id));
		$data['action_crud'] 	= base_url(sprintf('admin/ajax_certificate_message_template_crud/%d/', (int)$event_id));
		$data['action_ajax'] 	= base_url(sprintf('admin/ajax_event_certificate_message_templates/%d/', (int)$event_id));

		$this->load->view('backend/admin/event/certificate_template/index', $data);
	}

	public function ajax_certificate_message_template_form($event_id = 0, $id = 0) {
		$info 					= !empty($id) ? $this->certificate_message_template_model->get($id) : [];
		$certificate_type_info 	= !empty($info['certificate_type_id']) ? $this->certificate_type_model->get($info['certificate_type_id']) : [];
		$certificate_types = array_map(function($item) {
			return [
				'label' => $item['name'],
				'value' => $item['type']
			];
		}, $this->certificate_type_model->get_all()['rows']);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
			'options'	=> $certificate_types,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'country_code',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> $info['country_code'] ?? 'IN',
			'options'	=> [
				[
					'label'	=> _l('IN'),
					'value'	=> 'IN',
				],
				[
					'label'	=> _l('GE'),
					'value'	=> 'GE',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'subject',
			'label'		=> _l('email_subject'),
			'required'	=> true,
			'value'		=> $info['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'body',
			'label'		=> _l('email_message'),
			'required'	=> true,
			'value'		=> $info['body'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'whatsapp_gateway',
			'label'		=> _l('whatsapp_gateway'),
			'required'	=> false,
			'value'		=> $info['whatsapp_gateway'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('imiconnect'),
					'value'	=> 'imiconnect',
				],
				[
					'label'	=> _l('onextel'),
					'value'	=> 'onextel',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'whatsapp_template_id',
			'label'		=> _l('whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $info['whatsapp_template_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'whatsapp_message',
			'label'		=> _l('whatsapp_message'),
			'required'	=> false,
			'value'		=> $info['whatsapp_message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'min_sold',
			'label'		=> _l('min_sold_quantity'),
			'required'	=> true,
			'value'		=> $info['min_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'max_sold',
			'label'		=> _l('max_sold_quantity'),
			'required'	=> true,
			'value'		=> $info['max_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'fomo',
			'label'		=> _l('is_fomo'),
			'required'	=> false,
			'value'		=> $info['fomo'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('yes'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('no'),
					'value'	=> 0,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'league',
			'label'		=> _l('is_league'),
			'required'	=> false,
			'value'		=> $info['league'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('yes'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('no'),
					'value'	=> 0,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('status'),
			'required'	=> false,
			'value'		=> $info['status'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('enable'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('disable'),
					'value'	=> 0,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'sort_order',
			'label'		=> _l('sort_order'),
			'required'	=> true,
			'value'		=> $info['sort_order'] ?? 1,
		];

		$data['page_title'] = _l('certificate_message_template');
		$data['action'] 	= base_url(sprintf('admin/ajax_certificate_message_template_crud/%d/%s/%d', $event_id, ($id ? 'edit' : 'add'), $id));
		$data['fields'] 	= $this->load->view('backend/admin/event/stage/generic', $data, true);

		$this->load->view('backend/admin/event/certificate_template/form', $data);
	}

	public function ajax_event_certificate_message_templates($event_id = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'event_id'			=> (int)$event_id,
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->certificate_message_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'country_code'			=> $result['country_code'],
				'subject'				=> $result['subject'],
				'whatsapp_template_id'	=> $result['whatsapp_template_id'],
				'min_sold'				=> $result['min_sold'],
				'max_sold'				=> $result['max_sold'],
				'fomo'					=> _sd($result['fomo']),
				'league'				=> _sd($result['league']),
				'sort_order'			=> $result['sort_order'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_certificate_message_template_crud($event_id = 0, $action = NULL, $id = 0) {
		$this->json = [];

		if ($action == 'add') {
			self::_validateCertificateMessageTemplateForm();

			if (empty($this->json['errors'])) {
				$data = $this->input->post(NULL, FALSE);

				$data['body'] 		= _allowSpecificHtmlTags($data['body']);

				$certificate_type_info 	= $this->certificate_type_model->get($data['certificate_type_id']);
				$event_info 			= $this->event_model->get($event_id);

				$data['event_id'] 		= (int)$event_id;

				$this->certificate_message_template_model->add($data);
			}
		} elseif ($action == 'edit') {
			self::_validateCertificateMessageTemplateForm($id);

			if (empty($this->json['errors'])) {
				$data = $this->input->post();

				$data['body'] 		= _allowSpecificHtmlTags($data['body']);

				$certificate_type_info 	= $this->certificate_type_model->get($data['certificate_type_id']);
				$event_info 			= $this->event_model->get($event_id);

				$data['event_id'] 		= (int)$event_id;

				$this->certificate_message_template_model->edit($id, $data);
			}
		} elseif ($action == 'status') {
			$this->certificate_message_template_model->enableDisable($id, $this->input->post());
		} elseif ($action == 'delete') {
			$this->certificate_message_template_model->delete($id);
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateCertificateMessageTemplateForm($id = 0) {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required');
		$this->form_validation->set_rules('country_code', _l('country'), 'trim|required');
		$this->form_validation->set_rules('subject', _l('email_subject'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('body', _l('email_message'), 'trim|required|min_length[3]|max_length[3000]');
		$this->form_validation->set_rules('min_sold', _l('min_sold'), 'trim|required|numeric');
		$this->form_validation->set_rules('max_sold', _l('max_sold'), 'trim|required|numeric');
		$this->form_validation->set_rules('fomo', _l('fomo'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('league', _l('league'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('status', _l('status'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('sort_order', _l('sort_order'), 'trim|required|numeric');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
