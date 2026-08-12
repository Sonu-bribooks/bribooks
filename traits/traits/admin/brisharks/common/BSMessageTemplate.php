<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BSMessageTemplate {
	public function bs_message_template($param1 = '', $param2 = '') {
		$data['fields'] = [
			'sn',
			'id',
			'country_code',
			'name',
			'template_code',
			'schedule',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();

			if (self::_bsValidateTemplateData($data)) {
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

				$this->bs_message_template_model->add($data);
			}

			$this->session->set_flashdata('flash_message', _('Added successfully!'));

			redirect(base_url('admin/bs_message_template'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();

			if (self::_bsValidateTemplateData($data, $param2)) {
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

				$this->bs_message_template_model->edit($param2, $data);
			}

			$this->session->set_flashdata('flash_message', _('Edit successfully!'));

			redirect(base_url('admin/bs_message_template'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_message_template_model->delete($param2);
			redirect(base_url('admin/bs_message_template'), 'refresh');
		}

		$data['page_name'] 			= 'generic/index';
		$data['page_title'] 		= _l('message_template');
		$data['action_add'] 		= base_url('admin/bs_message_template_form/add');
		$data['action_ajax'] 		= base_url('admin/ajax_bs_message_template');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_message_template_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_message_template/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function bs_message_template_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_message_template');
			$data['action'] 						= base_url('admin/bs_message_template/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_message_template');
			$data['action'] 						= base_url('admin/bs_message_template/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_message_template_model->get($param2);

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
			'key'		=> 'country_code',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> $info['country_code'] ?? 'IN',
			'options'	=> [
				[
					'value' => 'IN',
					'label' => _l('india'),
				],
				[
					'value' => 'US',
					'label' => _l('global'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'code',
			'label'		=> _l('select_message_code'),
			'required'	=> true,
			'value'		=> $info['code'] ?? '',
			'options'	=> [
				[
					'label' => _l('user_otp'),
					'value' => 'user_otp',
				],
				[
					'label' => _l('user_signup'),
					'value' => 'user_signup',
				],
				[
					'label' => _l('event_user_signup'),
					'value' => 'event_user_signup',
				],
				[
					'label' => _l('subscription_invoice'),
					'value' => 'subscription_invoice',
				],
				[
					'label' => _l('startup_notify'),
					'value' => 'startup_notify',
				],
				[
					'label' => _l('startup_voted'),
					'value' => 'startup_voted',
				],
				[
					'label' => _l('contact us'),
					'value' => 'contact',
				],
			],
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
			'key'		=> 'schedule',
			'label'		=> _l('scheduled_minutes_time'),
			'required'	=> false,
			'value'		=> $info['schedule'] ?? 0,
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

	public function ajax_bs_message_template($region = 0, $template_type_id = 0) {
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

		$results = $this->bs_message_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'country_code'			=> $result['country_code'],
				'name'					=> $result['name'],
				'template_code'			=> $result['code'],
				'schedule'				=> $result['schedule'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _bsValidateTemplateData($data = [], $id = 0) {
		return true;
	}
}
