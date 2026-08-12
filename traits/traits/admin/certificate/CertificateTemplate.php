<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CertificateTemplates {
	public function certificate_templates($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'challenge',
			'medallion',
			'message_template_id',
			'name',
			'type',
			'country_code',
			'book_sold',
			'has_isbn',
			'has_rank',
			'achievement',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->certificate_template_model->add($this->input->post());
			redirect(base_url('admin/certificate_templates'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->certificate_template_model->edit($param2, $this->input->post());
			redirect(base_url('admin/certificate_templates'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->certificate_template_model->delete($param2);
			redirect(base_url('admin/certificate_templates'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('certificate_templates');
		$data['action_add'] 	= base_url('admin/certificate_template_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_certificate_templates');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/certificate_template_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/certificate_templates/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function certificate_template_form($param1=null,$param2=null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_certificate_template');
			$data['action'] 						= base_url('admin/certificate_templates/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_certificate_template');
			$data['action'] 						= base_url('admin/certificate_templates/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$template_info 							= $this->certificate_template_model->get($param2);
			$message_template_info 					= $this->certificate_message_template_model->get($template_info['certificate_message_template_id']);
			$event_info 							= $this->event_model->get($template_info['event_id']);
			$medallion_info						 	= $this->medallion_model->get($template_info['medallion_id']);
			$challenge_info 						= !empty($template_info['challenge_id']) && !empty($template_info['challenge_type'])
				? $this->{sprintf('event_challenge_%s_model', $template_info['challenge_type'])}->get($template_info['challenge_id'])
				: [];

			$event_name							 	= ($template_info['event_id'] == 0) ? "Generic" : $event_info['name'];
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
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=challenge_type,event_id'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_type',
			'label'		=> _l('select_challenge_type'),
			'required'	=> false,
			'value'		=> $template_info['challenge_type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=challenge_type,event_id'),
			'options'	=> CHALLENGE_TYPES,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_id',
			'label'		=> _l('select_challenge'),
			'required'	=> false,
			'value'		=> $template_info['challenge_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'certificate_message_template_id',
			'label'		=> _l('select_certificate_message_template'),
			'required'	=> false,
			'value'		=> [
				'value' => $message_template_info['id'] ?? '',
				'label' => $message_template_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_certificate_message_templates'),
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'book_sold',
			'label'		=> _l('book_sold'),
			'min'		=> 1,
			'step'		=> 1,
			'max'		=> '',
			'required'	=> true,
			'value'		=> $template_info['book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $template_info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'medallion_id',
			'label'		=> _l('select_medallion'),
			'required'	=> false,
			'value'		=> [
				'value' => $template_info['medallion_id'] ?? '',
				'label' => $medallion_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_medallions'),
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
			'type'		=> 'number',
			'key'		=> 'rank_x_axis',
			'label'		=> _l('rank_x_axis'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> false,
			'value'		=> $template_info['rank_x_axis'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'rank_y_axis',
			'label'		=> _l('rank_y_axis'),
			'min'		=> 0,
			'step'		=> 1,
			'max'		=> 2048,
			'required'	=> false,
			'value'		=> $template_info['rank_y_axis'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'has_isbn',
			'label'		=> _l('has_isbn'),
			'required'	=> true,
			'value'	 	=> $template_info['has_isbn'] ?? 0,
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
			'key'		=> 'has_rank',
			'label'		=> _l('has_rank'),
			'required'	=> true,
			'value'	 	=> $template_info['has_rank'] ?? 0,
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
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'is_jury',
			'label'		=> _l('is_jury'),
			'required'	=> true,
			'value'	 	=> $template_info['is_jury'] ?? 0,
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

	public function ajax_certificate_templates() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->certificate_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info 	= $this->event_model->get($result['event_id']);
			$challenge_info = !empty($result['challenge_id']) && !empty($result['challenge_type'])
				? $this->{sprintf('event_challenge_%s_model', $result['challenge_type'])}->get($result['challenge_id'])
				: [];
			$medallion_info = !empty($result['medallion_id'])
				? $this->medallion_model->get($result['medallion_id'])
				: [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event'					=> !empty($result['event_id'])
					? sprintf('%s (%s)', $event_info['name'] ?? '', $result['event_id'])
					: '',
				'challenge'				=> !empty($result['challenge_id'])
					? sprintf('%s (%s-%s)', $challenge_info['name'] ?? '', $result['challenge_type'], $result['challenge_id'])
					: '',
				'medallion'				=> !empty($result['medallion_id'])
					? sprintf('%s (%s)', $medallion_info['name'] ?? '', $result['medallion_id'])
					: '',
				'message_template_id'	=> $result['certificate_message_template_id'],
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'country_code'			=> $result['country_code'],
				'book_sold'				=> $result['book_sold'],
				'has_isbn'				=> $result['has_isbn'],
				'has_rank'				=> $result['has_rank'],
				'achievement'			=> $result['achievement'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_certificate_challenge() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 100,
			'order'				=> 'ASC',
		];

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		if ($this->input->get('challenge_type')) {
			$type = $this->input->get('challenge_type');
		} elseif ($this->input->get('type')) {
			$type = $this->input->get('type');
		} else {
			$type = 'general';
		}

		$results = $this->{sprintf('event_challenge_%s_model', $type)}->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['id']),
			];
		}

		output_json($json);
	}
}
