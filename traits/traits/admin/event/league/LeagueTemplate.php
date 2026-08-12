<?php defined('BASEPATH') or exit('No direct script access allowed');

trait LeagueTemplate {
	public function league_template($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'type',
			'challenge',
			'min_rank',
			'max_rank',
			'subject',
			'bcc',
			'whatsapp_template_id',
			'whatsapp_message',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->league_template_model->add($this->input->post());
			redirect(base_url('admin/league_template'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->league_template_model->edit($param2, $this->input->post());
			redirect(base_url('admin/league_template'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->league_template_model->delete($param2);
			redirect(base_url('admin/league_template'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('league_template');
		$data['action_add'] 	= base_url('admin/league_template_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_league_templates');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/league_template_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/league_template/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function league_template_form($param1 = null, $param2 = null) {
		$variables = [
			'author_name'	  		=> _l('author_name'),
			'book_name'	  			=> _l('book_name'),
			'rank'	  				=> 1,
			'url'					=> USER_URL,
			'cert_url'				=> USER_URL . 'account/mycertificates',
			'school_name'			=> _l('school_name'),
			'city'					=> _l('city'),
			'state'					=> _l('state'),
			'country'				=> _l('country'),
			'invite_url'			=> USER_URL,
			'league_url'			=> USER_URL,
		];

		$data['info'] = sprintf('<h4>%s</h4>', _l('available_variables')) . implode('<br />', array_map(fn($k, $v) => sprintf('<b>{%s}</b> : %s', $k, $v), array_keys($variables), $variables));

		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_league_template');
			$data['action'] 						= base_url('admin/league_template/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_league_template');
			$data['action'] 						= base_url('admin/league_template/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->league_template_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
			$challenge_info 						= !empty($info['challenge_id']) && !empty($info['type'])
				? $this->{sprintf('event_challenge_%s_model', $info['type'])}->get($info['challenge_id'])
				: [];

			$event_name							 	= ($info['event_id'] == 0) ? _l('generic') : $event_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
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
			'value'		=> $info['type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
			'options'	=> CHALLENGE_TYPES,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_id',
			'label'		=> _l('select_challenge'),
			'required'	=> true,
			'value'		=> $info['challenge_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'min_rank',
			'label'		=> _l('min_rank (included)'),
			'required'	=> true,
			'value'		=> $info['min_rank'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_rank',
			'label'		=> _l('max_rank (included)'),
			'required'	=> true,
			'value'		=> $info['max_rank'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'subject',
			'label'		=> _l('email_subject'),
			'required'	=> true,
			'value'		=> $info['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'bcc',
			'label'		=> _l('email_bcc'),
			'required'	=> false,
			'value'		=> $info['bcc'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'body',
			'label'		=> _l('email_message'),
			'required'	=> true,
			'value'		=> $info['body'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'whatsapp_template_id',
			'label'		=> _l('whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $info['whatsapp_template_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'whatsapp_message',
			'label'		=> _l('whatsapp_message'),
			'required'	=> false,
			'value'		=> $info['whatsapp_message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'	 	=> $info['status'] ?? 1,
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

	public function ajax_league_templates() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->league_template_model->get_all($filter_data);

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
				'min_rank'				=> $result['min_rank'],
				'max_rank'				=> $result['max_rank'],
				'subject'				=> $result['subject'],
				'bcc'					=> $result['bcc'],
				'whatsapp_template_id'	=> $result['whatsapp_template_id'],
				'whatsapp_message'		=> $result['whatsapp_message'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
