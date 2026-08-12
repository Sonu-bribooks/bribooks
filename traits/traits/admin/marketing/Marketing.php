<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Marketing {
	public function marketing($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data = $this->input->post(NULL, FALSE);
			// $data['user_type'] 	= !empty($data['marketing_dataset_user_type']) ? $data['marketing_dataset_user_type'] : $data['user_type'];
			// $data['message'] 	= _allowSpecificHtmlTags($data['message']);

			if (($data['user_type'] ?? '') === 'marketing_dataset' && !empty($data['marketing_dataset_user_type'])) {
				$data['user_type'] = $data['marketing_dataset_user_type'];
			}
			if (isset($data['marketing_dataset_user_type'])) {
				unset($data['marketing_dataset_user_type']);
			}

			$this->marketing_model->add($data);
			redirect(base_url('admin/marketing'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post(NULL, FALSE);
			// $data['user_type'] 	= !empty($data['marketing_dataset_user_type']) ? $data['marketing_dataset_user_type'] : $data['user_type'];
			// $data['message'] 	= _allowSpecificHtmlTags($data['message']);
			
			if (($data['user_type'] ?? '') === 'marketing_dataset' && !empty($data['marketing_dataset_user_type'])) {
				$data['user_type'] = $data['marketing_dataset_user_type'];
			}
			if (isset($data['marketing_dataset_user_type'])) {
				unset($data['marketing_dataset_user_type']);
			}

			$this->marketing_model->edit($param2, $data);
			redirect(base_url('admin/marketing'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->marketing_model->enableDisable($param2);
			redirect(base_url('admin/marketing'), 'refresh');
		} elseif ($param1 == 'copy') {
			$this->marketing_model->copy($param2);
			redirect(base_url('admin/marketing'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->marketing_model->delete($param2);
			redirect(base_url('admin/marketing'), 'refresh');
		}

		$data['page_name'] 		= 'marketing/index';
		$data['page_title'] 	= _l('marketing');
		$data['action_add'] 	= base_url('admin/marketing_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_marketing');

		$this->load->view('backend/index', $data);
	}

	public function marketing_form($param1 = NULL, $param2 = NULL) {
		$data['user_types'] = [
			[
				'key'		=> 'csv',
				'value'		=> _l('csv')
			],
			[
				'key'		=> 'marketing_dataset',
				'value'		=> _l('marketing_dataset')
			],
		];

		$data['types'] = [
			[
				'key'		=> 'email',
				'value'		=> _l('email')
			],
			[
				'key'		=> 'whatsapp',
				'value'		=> _l('whatsapp')
			],
			[
				'key'		=> 'sms',
				'value'		=> _l('sms')
			],
			[
				'key'		=> 'whatsapp_email',
				'value'		=> _l('whatsapp_email')
			],
			[
				'key'		=> 'whatsapp_sms',
				'value'		=> _l('whatsapp_sms')
			],
			[
				'key'		=> 'email_sms',
				'value'		=> _l('email_sms')
			],
			[
				'key'		=> 'email_annoncement',
				'value'		=> _l('email_annoncement')
			],
			[
				'key'		=> 'whatsapp_annoncement',
				'value'		=> _l('whatsapp_annoncement')
			],
			[
				'key'		=> 'sms_annoncement',
				'value'		=> _l('sms_annoncement')
			],
			[
				'key'		=> 'email_referral',
				'value'		=> _l('email_referral')
			],
			[
				'key'		=> 'whatsapp_referral',
				'value'		=> _l('whatsapp_referral')
			],
			[
				'key'		=> 'email_whatsapp_referral',
				'value'		=> _l('email_whatsapp_referral')
			],
			[
				'key'		=> 'sms_referral',
				'value'		=> _l('sms_referral')
			],
			[
				'key'		=> 'app_notifications',
				'value'		=> _l('app_notifications')
			],
			[
				'key'		=> 'push_notifications',
				'value'		=> _l('push_notifications')
			],
			[
				'key'		=> 'all',
				'value'		=> _l('all')
			],
		];

		$data['frequencies'] = [
			[
				'key'		=> 'none',
				'value'		=> _l('none')
			],
			[
				'key'		=> 'daily',
				'value'		=> _l('daily')
			],
			[
				'key'		=> 'weekly',
				'value'		=> _l('weekly')
			],
		];

		$data['sms_gateway'] = [
			[
				'key'		=> 'none',
				'value'		=> _l('none')
			],
			[
				'key'		=> 'textlocal',
				'value'		=> _l('textlocal')
			],
			[
				'key'		=> 'vonage',
				'value'		=> _l('vonage')
			],
			[
				'key'		=> 'twilio',
				'value'		=> _l('twilio')
			],
			[
				'key'		=> 'all',
				'value'		=> _l('all')
			],
		];

		$data['whatsapp_gateway'] = [
			[
				'key'		=> 'imiconnect',
				'value'		=> _l('imiconnect')
			],
			[
				'key'		=> 'onextel',
				'value'		=> _l('onextel')
			],
			[
				'key'		=> 'onextel_brisharks',
				'value'		=> _l('onextel_brisharks')
			],
			[
				'key'		=> 'onextel_briminds',
				'value'		=> _l('onextel_briminds')
			],
		];

		$data['email_templates'] = [
			[
				'key'		=> 3,
				'value'		=> 3
			],
			[
				'key'		=> 5,
				'value'		=> 5
			],
		];

		$data['events'] = $this->event_model->get_all([
			'start_date_le'	=> date('Y-m-d H:i:s'),
			// 'end_date_ge'	=> date('Y-m-d H:i:s'),
		])['rows'] ?? [];

		array_unshift($data['events'], [
			'id'	=> 0,
			'name'	=> _l('all'),
		]);

		if ($param1 == 'add') {
			$data['page_name'] 						= 'marketing/form';
			$data['page_title'] 					= _l('marketing_add');
			$data['action'] 						= base_url('admin/marketing/add');
			$data['units']							= [];
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'marketing/form';
			$data['page_title'] 					= _l('marketing_edit');
			$data['action'] 						= base_url('admin/marketing/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->marketing_model->get($param2);
			$data['marketing_dataset'] 				= (strpos($data['details']['user_type'], 'marketing_dataset_') !== false)
				? $this->marketing_dataset_model->get(substr($data['details']['user_type'], strlen('marketing_dataset_')))
				: '';

			$data['users'] 							= !empty($data['details']['filters']['users_id'])
				? ($this->student_model->get_all(['user_ids' => $data['details']['filters']['users_id']])['rows'] ?? [])
				: [];
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_marketing() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->marketing_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = !empty($result['event_id'])
				? $this->event_model->get($result['event_id'])
				: [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'] . _sd($result['testing'] ^ 1),
				'status'				=> _sd($result['status']),
				'event'					=> $event_info['name'] ?? '',
				'user_type'				=> $result['user_type'],
				'name'					=> $result['name'],
				'stats'					=> $result['total_users'] . '/' . $result['sent_users'],
				'type'					=> $result['type'],
				'to'					=> $result['to'],
				'csv_file'				=> $result['user_type'] == 'csv' ? $result['csv_file'] : $this->marketing_dataset_model->get(substr($result['user_type'], strlen('marketing_dataset_')))['name'] ?? '-',
				'template_id'			=> $result['template_id'],
				'message'				=> $result['message'],
				'attachment_type'		=> _attachment_type($result['attachment_type']),
				'attachment_file'		=> vsprintf('<img src="%s" />', [
					$this->image_model->resize($result['attachment_file'], 100, 100)
				]),
				
				'alert_date'			=> formatDate($result['alert_date']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_event_state() {
		if ($this->input->post('event_id')) {
			$event_info = $this->event_model->get($this->input->post('event_id'));

			$json = [];
			$json = array_map(function($item) {
				return [
					'id'	=> $item['id'],
					'text'	=> $item['name'],
				];
			}, $this->state_model->get_all([
				'country_id'	=> $event_info['country_id'],
				'sort'			=> 'state.name',
				'order'			=> 'ASC',
			])['rows'] ?? []);
		}

		output_json($json);
	}

	public function marketing_settings($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['marketing_email'] 	= !empty(get_settings('marketing_email')) ? get_settings('marketing_email') : '' ;
		$data['marketing_mobile']  	= !empty(get_settings('marketing_mobile')) ? get_settings('marketing_mobile') : '' ;

		if ($param1 == 'update') {
			if (!empty($this->input->post('marketing_mobile'))) {
				$this->crud_model->update_setting_template('marketing_mobile', $this->input->post('marketing_mobile'));
			}

			if (!empty($this->input->post('marketing_email'))) {
				$this->crud_model->update_setting_template('marketing_email', $this->input->post('marketing_email'));
			}

			$this->session->set_flashdata('flash_message', _l('Markrting_contact_updated_successfully'));
			redirect(base_url('admin/marketing_settings'), 'refresh');
		}

		$data['page_name'] 	= 'marketing_settings';
		$data['page_title'] = _l('marketing_settings');

		$this->load->view('backend/index', $data);
	}
}
