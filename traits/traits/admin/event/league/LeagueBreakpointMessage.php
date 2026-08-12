<?php defined('BASEPATH') or exit('No direct script access allowed');

trait LeagueBreakpointMessage {
	public function league_breakpoint_message($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'type',
			'challenge',
			'breakpoint',
			'message',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->league_breakpoint_message_model->add($this->input->post());
			redirect(base_url('admin/league_breakpoint_message'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->league_breakpoint_message_model->edit($param2, $this->input->post());
			redirect(base_url('admin/league_breakpoint_message'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->league_breakpoint_message_model->delete($param2);
			redirect(base_url('admin/league_breakpoint_message'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('league_breakpoint_message');
		$data['action_add'] 	= base_url('admin/league_breakpoint_message_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_league_breakpoint_messages');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/league_breakpoint_message_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/league_breakpoint_message/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function league_breakpoint_message_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_league_breakpoint_message');
			$data['action'] 						= base_url('admin/league_breakpoint_message/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_league_breakpoint_message');
			$data['action'] 						= base_url('admin/league_breakpoint_message/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$template_info 							= $this->league_breakpoint_message_model->get($param2);
			$message_template_info 					= $this->certificate_message_template_model->get($template_info['certificate_message_template_id']);
			$event_info 							= $this->event_model->get($template_info['event_id']);
			$medallion_info						 	= $this->medallion_model->get($template_info['medallion_id']);
			$challenge_info 						= !empty($template_info['challenge_id']) && !empty($template_info['type'])
				? $this->{sprintf('event_challenge_%s_model', $template_info['type'])}->get($template_info['challenge_id'])
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
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_challenge_type'),
			'required'	=> false,
			'value'		=> $template_info['type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
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
			'type'		=> 'select',
			'key'		=> 'breakpoint',
			'label'		=> _l('select_breakpoint'),
			'required'	=> true,
			'value'		=> $template_info['breakpoint'] ??'',
			'options'	=> [
				[
					'label'	=> 1,
					'value'	=> 1,
				],
				[
					'label'	=> 3,
					'value'	=> 3,
				],
				[
					'label'	=> 5,
					'value'	=> 5,
				],
				[
					'label'	=> 7,
					'value'	=> 7,
				],
				[
					'label'	=> 10,
					'value'	=> 10,
				],
				[
					'label'	=> 20,
					'value'	=> 20,
				],
				[
					'label'	=> 30,
					'value'	=> 30,
				],
				[
					'label'	=> 40,
					'value'	=> 40,
				],
				[
					'label'	=> 50,
					'value'	=> 50,
				],
				[
					'label'	=> 70,
					'value'	=> 70,
				],
				[
					'label'	=> 100,
					'value'	=> 100,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'message',
			'label'		=> _l('message'),
			'required'	=> true,
			'value'		=> $template_info['message'] ?? '',
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

	public function ajax_league_breakpoint_messages() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->league_breakpoint_message_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info 	= $this->event_model->get($result['event_id']);
			$challenge_info = !empty($result['challenge_id']) && !empty($result['type'])
				? $this->{sprintf('event_challenge_%s_model', $result['type'])}->get($result['challenge_id'])
				: [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event'					=> !empty($result['event_id'])
					? sprintf('%s (%s)', $event_info['name'] ?? '', $result['event_id'])
					: '',
				'type'					=> $result['type'],
				'challenge'				=> !empty($result['challenge_id'])
					? sprintf('%s (%s-%s)', $challenge_info['name'] ?? '', $result['challenge_id'], $result['type'])
					: '',
				'breakpoint'			=> $result['breakpoint'],
				'message'				=> $result['message'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
