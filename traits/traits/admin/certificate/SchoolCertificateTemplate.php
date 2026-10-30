<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolCertificateTemplate {
	public function school_certificate_templates($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'message_template_id',
			'name',
			'country_code',
			'published_author_count',
			'achievement',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();
			$data['design'] = json_encode($data['design']);
			$this->school_certificate_template_model->add($data);
			redirect(base_url('admin/school_certificate_templates'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();
			$data['design'] = json_encode($data['design']);
			$this->school_certificate_template_model->edit($param2, $data);
			redirect(base_url('admin/school_certificate_templates'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->school_certificate_template_model->delete($param2);
			redirect(base_url('admin/school_certificate_templates'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('school_certificate_templates');
		$data['action_add'] 	= base_url('admin/school_certificate_template_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_school_certificate_templates');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/school_certificate_template_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/school_certificate_templates/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function school_certificate_template_form($param1=null,$param2=null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_school_certificate_template');
			$data['action'] 						= base_url('admin/school_certificate_templates/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_school_certificate_template');
			$data['action'] 						= base_url('admin/school_certificate_templates/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$template_info 							= $this->school_certificate_template_model->get($param2);
			$message_template_info 					= $this->certificate_message_template_model->get($template_info['school_certificate_message_template_id'] ?? '');
			$event_info 							= $this->event_model->get($template_info['event_id']);

			$event_name							 	= ($template_info['event_id'] == 0) ? "Generic" : $event_info['name'] ?? '';
			$template_info['design']				= !empty($template_info['design']) ? json_decode($template_info['design'], true) : [];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $template_info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'school_certificate_message_template_id',
			'label'		=> _l('select_certificate_message_template'),
			'required'	=> false,
			'value'		=> [
				'value' => $message_template_info['id'] ?? '',
				'label' => $message_template_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_school_certificate_message_templates'),
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'published_author_count',
			'label'		=> _l('published_author_count'),
			'min'		=> 1,
			'step'		=> 1,
			'max'		=> '',
			'required'	=> true,
			'value'		=> $template_info['published_author_count'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $template_info['name'] ?? '',
		];


		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'country_code',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> $template_info['country_code'] ??'',
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
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> false,
			'value'		=> $template_info['image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'design[layout]',
			'label'		=> _l('layout_mode'),
			'required'	=> true,
			'value'		=> $template_info['design']['layout'] ?? 'landscape',
			'options'	=> [
				[
					'label'	=> _l('landscape'),
					'value'	=> 'landscape',
				],
				[
					'label'	=> _l('portrait'),
					'value'	=> 'portrait',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[font_size]',
			'label'		=> _l('font_size'),
			'required'	=> true,
			'min'		=> 28,
			'step'		=> 1,
			'max'		=> 60,
			'value'		=> $template_info['design']['font_size'] ?? 28,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'design[school_name][align]',
			'label'		=> _l('school_name_align'),
			'required'	=> true,
			'value'		=> $template_info['design']['school_name']['align'] ?? 'left',
			'options'	=> [
				[
					'label'	=> _l('left'),
					'value'	=> 'left',
				],
				[
					'label'	=> _l('center'),
					'value'	=> 'center',
				],
				[
					'label'	=> _l('right'),
					'value'	=> 'right',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[school_name][x]',
			'label'		=> _l('school_name_x'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['school_name']['x'] ?? 120,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[school_name][y]',
			'label'		=> _l('school_name_y'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['school_name']['y'] ?? 610,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'design[city][align]',
			'label'		=> _l('city_align'),
			'required'	=> true,
			'value'		=> $template_info['design']['city']['align'] ?? 'left',
			'options'	=> [
				[
					'label'	=> _l('left'),
					'value'	=> 'left',
				],
				[
					'label'	=> _l('center'),
					'value'	=> 'center',
				],
				[
					'label'	=> _l('right'),
					'value'	=> 'right',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[city][x]',
			'label'		=> _l('city_x'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['city']['x'] ?? 120,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[city][y]',
			'label'		=> _l('city_y'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['city']['y'] ?? 810,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'design[state][align]',
			'label'		=> _l('state_align'),
			'required'	=> true,
			'value'		=> $template_info['design']['state']['align'] ?? 'left',
			'options'	=> [
				[
					'label'	=> _l('left'),
					'value'	=> 'left',
				],
				[
					'label'	=> _l('center'),
					'value'	=> 'center',
				],
				[
					'label'	=> _l('right'),
					'value'	=> 'right',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[state][x]',
			'label'		=> _l('state_x'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['state']['x'] ?? 260,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[state][y]',
			'label'		=> _l('state_y'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['state']['y'] ?? 820,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[qr][x]',
			'label'		=> _l('qr_x'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['qr']['x'] ?? 1440,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[qr][y]',
			'label'		=> _l('qr_y'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['qr']['y'] ?? 870,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[unique_id][x]',
			'label'		=> _l('unique_id_x'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['unique_id']['x'] ?? 1450,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[unique_id][y]',
			'label'		=> _l('unique_id_y'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['unique_id']['y'] ?? 1040,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[date][x]',
			'label'		=> _l('date_x'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['date']['x'] ?? 1450,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'design[date][y]',
			'label'		=> _l('date_y'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> true,
			'value'		=> $template_info['design']['date']['y'] ?? 1070,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'achievement',
			'label'		=> _l('achievement'),
			'required'	=> true,
			'value'	 	=> $template_info['achievement'] ?? 0,
			'options'	=> [
				[
					'label'	=> _l('yes'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('no'),
					'value'	=> 0,
				],
				[
					'label'	=> _l('participation'),
					'value'	=> 2,
				],
			],
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

		$data['img_anchor']	  		= $this->image_model->resize(!empty($template_info['image']) ? ($this->config->item('s3_user_gallery') . $template_info['image']) : 'no_image.png', 100, 100);
		$data['img_src']		 	= $this->image_model->resize(!empty($template_info['image']) ? $this->config->item('cloudfront_url') . ($this->config->item('s3_user_gallery') . $template_info['image']) : 'no_image.png', 100, 100);
		$data['img_placeholder'] 	= $this->image_model->resize('no_image.png', 100, 100);
		$data['img_value']	   		= $template_info['image'] ?? '';

		$this->load->view('backend/index', $data);
	}

	public function ajax_school_certificate_templates() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->school_certificate_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info 	= $this->event_model->get($result['event_id']);
	
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event'					=> !empty($result['event_id'])
					? sprintf('%s (%s)', $event_info['name'] ?? '', $result['event_id'])
					: '',
				'message_template_id'	=> $result['school_certificate_message_template_id'] ?? '',
				'name'					=> $result['name'],
				'country_code'			=> $result['country_code'] ?? '',
				'published_author_count'=> $result['published_author_count'] ?? '',
				'achievement'			=> $result['achievement'] ?? '',
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
