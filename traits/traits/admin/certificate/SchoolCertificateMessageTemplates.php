<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolCertificateMessageTemplates {
	public function school_certificate_message_templates($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'event',
			'name',
			'subject',
			'whatsapp_template_id',
			'country_code',
			'min_published',
			'max_published',
			'is_fomo',
			'sort_order',
			'actions',
		];

		if ($param1 == 'add') {
			$_POST['type'] = gen_slug($this->input->post('name'));
			$this->school_certificate_message_template_model->add($this->input->post());
			redirect(base_url('admin/school_certificate_message_templates'), 'refresh');
		} elseif ($param1 == 'edit') {
			$_POST['type'] = gen_slug($this->input->post('name'));
			$this->school_certificate_message_template_model->edit($param2, $this->input->post());
			redirect(base_url('admin/school_certificate_message_templates'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->school_certificate_message_template_model->delete($param2);
			redirect(base_url('admin/school_certificate_message_templates'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('school_certificate_message_templates');
		$data['action_add'] 	= base_url('admin/school_certificate_message_template_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_school_certificate_message_templates');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/school_certificate_message_template_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/school_certificate_message_templates/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function school_certificate_message_template_form($param1=null,$param2=null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_school_certificate_message_template');
			$data['action'] 						= base_url('admin/school_certificate_message_templates/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_school_certificate_message_template');
			$data['action'] 						= base_url('admin/school_certificate_message_templates/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$template_info 							= $this->school_certificate_message_template_model->get($param2);
			$event_info 							= $this->event_model->get($template_info['event_id']);

			$event_name							 = ($template_info['event_id'] == 0) ? "Generic" : $event_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> false,
			'value'		=> [
				'value' => $template_info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $template_info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'subject',
			'label'		=> _l('subject'),
			'required'	=> true,
			'value'		=> $template_info['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'body',
			'label'		=> _l('message_body'),
			'required'	=> true,
			'value'		=> $template_info['body'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'whatsapp_template_id',
			'label'		=> _l('whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $template_info['whatsapp_template_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'whatsapp_message',
			'label'		=> _l('whatsapp_message'),
			'required'	=> false,
			'value'		=> $template_info['whatsapp_message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'country_code',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> $template_info['country_code'] ?? 'IN',
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
			'type'		=> 'number',
			'key'		=> 'min_published',
			'label'		=> _l('min_published'),
			'required'	=> true,
			'value'		=> $template_info['min_published'] ?? 1,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_published',
			'label'		=> _l('max_published'),
			'required'	=> true,
			'value'		=> $template_info['max_published'] ?? 5,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'fomo',
			'label'		=> _l('select_fomo'),
			'required'	=> true,
			'value'	 => $template_info['fomo'] ?? 0,
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
			'type'		=> 'number',
			'key'		=> 'sort_order',
			'label'		=> _l('sort_order'),
			'required'	=> true,
			'value'		=> $template_info['sort_order'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'	 => $template_info['status'] ?? 1,
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

		$this->load->view('backend/index', $data);
	}

	public function ajax_school_certificate_message_templates() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->school_certificate_message_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info 	= $this->event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'event'					=> !empty($result['event_id'])
					? sprintf('%s (%s)', $event_info['name'] ?? '', $result['event_id'])
					: '',
				'name'					=> $result['name'],
				'subject'				=> $result['subject'],
				'whatsapp_template_id'	=> $result['whatsapp_template_id'],
				'country_code'			=> $result['country_code'],
				'min_published'			=> $result['min_published'],
				'max_published'			=> $result['max_published'],
				'is_fomo'				=> _sd($result['is_fomo'] ?? ''),
				'sort_order'			=> $result['sort_order'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_school_certificate_message_templates() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->school_certificate_message_template_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
