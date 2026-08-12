<?php defined('BASEPATH') or exit('No direct script access allowed');

trait MessageTemplate {
	public function message_template($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();

			if (self::_validateTemplateData($data)) {
				if (is_array($data['email'])) {
					$data['email'] 	= $this->input->post('email', FALSE);
					$data['email']  = json_encode($data['email']);
				}

				if (is_array($data['sms'])) {
					$data['sms'] 	= $this->input->post('sms', FALSE);
					$data['sms']	= json_encode($data['sms']);
				}

				if (is_array($data['whatsapp'])) {
					$data['whatsapp'] 	= $this->input->post('whatsapp', FALSE);
					$data['whatsapp']   = json_encode($data['whatsapp']);
				}

				$this->message_template_model->add($data);
			}

			redirect(base_url(sprintf('admin/message_template_view/%d/%d' , (int)$data['site_id'], (int)$data['template_type_id'])));

		} elseif ($param1 == 'edit') {
			$data = $this->input->post();

			if (self::_validateTemplateData($data, $param2)) {
				if (is_array($data['email'])) {
					$data['email'] 	= $this->input->post('email', FALSE);
					$data['email']  = json_encode($data['email']);
				}

				if (is_array($data['sms'])) {
					$data['sms'] 	= $this->input->post('sms', FALSE);
					$data['sms']	= json_encode($data['sms']);
				}

				if (is_array($data['whatsapp'])) {
					$data['whatsapp'] 	= $this->input->post('whatsapp', FALSE);
					$data['whatsapp']   = json_encode($data['whatsapp']);
				}

				$this->message_template_model->edit($param2, $data);
			}

			redirect(base_url(sprintf('admin/message_template_view/%d/%d' , (int)$data['site_id'], (int)$data['template_type_id'])));

		} elseif ($param1 == 'delete') {
			$this->message_template_model->delete($param2);
			redirect(base_url('admin/message_template'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('message_template');
		$data['action_add'] 	= base_url('admin/message_template_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_message_template_type');

		$data['actions'] 		= [
			[
				'key'	=> 'view_india',
				'url'	=> 'admin/message_template_view/1/',
			],
			[
				'key'	=> 'view_global',
				'url'	=> 'admin/message_template_view/2/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function message_template_view($region = null, $template_type_id = null) {
		$data['fields'] = [
			'sn',
			'id',
			'region',
			'type',
			'template_name',
			'status',
			'actions',
		];

		$data['page_name'] 			= 'generic/index';
		$data['page_title'] 		= _l('view_message_template');
		$data['action_add'] 		= base_url('admin/message_template_form/add?region=1&type=1');
		$data['action_ajax'] 		= base_url(sprintf('admin/ajax_message_template/%d/%d' , (int)$region, (int)$template_type_id));


		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/message_template_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/message_template/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function message_template_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_message_template');
			$data['action'] 						= base_url('admin/message_template/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('message_template');
			$data['action'] 						= base_url('admin/message_template/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->message_template_model->get($param2);
			$type_info 								= $this->message_template_type_model->get($info['template_type_id']);
			$type_name								=  $type_info['name'] ?? '';

			if (!empty($info['email'])) {
				$info['email'] = json_decode($info['email'], true);
			}

			if (!empty($info['sms'])) {
				$info['sms'] = json_decode($info['sms'], true);
			}

			if (!empty($info['whatsapp'])) {
				$info['whatsapp'] = json_decode($info['whatsapp'], true);
			}
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'site_id',
			'label'		=> _l('select_region'),
			'required'	=> true,
			'value'		=> $info['site_id'] ?? 0,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('india'),
				],
				[
					'value' => 2,
					'label' => _l('global'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'template_type_id',
			'label'		=> _l('select_template_type'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['template_type_id'] ?? '',
				'label' => $type_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_message_template_type'),
		];

		$listeners = CI_Events::getListeners();

		if (!empty($listeners)) {
			$listener_keys 		= array_keys($listeners);
			$listener_options 	= [];

			foreach ($listener_keys as $k) {
				$listener_options[] = [
					'value' => $k,
					'label' => _l($k),
				];
			}
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'code',
			'label'		=> _l('select_message_code'),
			'required'	=> true,
			'value'		=> $info['code'] ?? '',
			'options'	=> $listener_options,
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'email[subject]',
			'label'		=> _l('email_subject'),
			'required'	=> false,
			'value'		=> $info['email']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'email[message]',
			'label'		=> _l('email_message'),
			'required'	=> false,
			'value'		=> $info['email']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'email[bcc]',
			'label'		=> _l('email_bcc'),
			'required'	=> false,
			'value'		=> $info['email']['bcc'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'sms[template_id]',
			'label'		=> _l('sms_template_id'),
			'required'	=> false,
			'value'		=> $info['sms']['template_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'sms[message]',
			'label'		=> _l('sms_message'),
			'required'	=> false,
			'value'		=> $info['sms']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'sms[gateway]',
			'label'		=> _l('select_sms_gateway'),
			'required'	=> false,
			'value'		=> $info['sms']['gateway'] ?? '',
			'options'	=> array_map(fn($item) => [
				'label' => $item['name'],
				'value' => $item['code'],
			], SMS_GATEWAYS)
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'whatsapp[template_id]',
			'label'		=> _l('whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $info['whatsapp']['template_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'whatsapp[message]',
			'label'		=> _l('whatsapp_message'),
			'required'	=> false,
			'value'		=> $info['whatsapp']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'whatsapp[cta_type]',
			'label'		=> _l('select_whatsapp_cta_type'),
			'required'	=> false,
			'value'		=> $info['whatsapp']['cta_type'] ?? 'none',
			'options'	=> [
				[
					'value' => 'URL',
					'label' => _l('URL'),
				],
				[
					'value' => 'QUICK_REPLY',
					'label' => _l('QUICK_REPLY'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'whatsapp[cta_var]',
			'label'		=> _l('whatsapp_cta_var'),
			'required'	=> false,
			'value'		=> $info['whatsapp']['cta_var'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'whatsapp[type]',
			'label'		=> _l('select_whatsapp_type'),
			'required'	=> false,
			'value'		=> $info['whatsapp']['type'] ?? 'none',
			'options'	=> [
				[
					'value' => 'NONE',
					'label' => _l('none'),
				],
				[
					'value' => 'IMAGE',
					'label' => _l('document'),
				],
				[
					'value' => 'DOC',
					'label' => _l('image'),
				],
				[
					'value' => 'VIDEO',
					'label' => _l('video'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'file',
			'key'		=> 'whatsapp[attachment_file]',
			'label'		=> _l('whatsapp_attachment_file'),
			'required'	=> false,
			'value'		=> $info['whatsapp']['attachment_file'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'schedule_time',
			'label'		=> _l('scheduled_minutes_time'),
			'required'	=> false,
			'value'		=> $info['schedule_time'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('enable'),
				],
				[
					'value' => 0,
					'label' => _l('disable'),
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_message_template($region = 0, $template_type_id = 0) {
		$json['data'] 	= [];
		$columns 		= $this->input->get('columns');

		$filter_data = [
			'site_id'			=> (int)$region,
			'template_type_id'	=> (int)$template_type_id,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->message_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$template_type_info = $this->message_template_type_model->get($result['template_type_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'region'				=> $result['site_id'] == 2 ? 'Global' : 'India',
				'type'					=> $template_type_info['name'] ?? '',
				'template_name'			=> $result['name'] ?? '',
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateTemplateData($data = [], $id = 0) {
		if (empty($data) || empty($data['site_id']) || empty($data['code'])) return true;

		$message_template_info = $this->message_template_model->getByCode($data['code'], $data['site_id']);

		if (empty($message_template_info)) return true;

		if ($message_template_info['id'] == $id) return true;

		return false;
	}
}
