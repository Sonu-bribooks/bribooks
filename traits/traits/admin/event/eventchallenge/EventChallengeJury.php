<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventChallengeJury {
	public function event_challenge_jury($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'challenge_id',
			'event_id',
			'event',
			'type',
			'heading',
			'slug',
			'url',
			'actions',
		];

		if ($param1 == 'add') {
            $challenge_info 						= !empty($this->input->post('challenge_id')) && !empty($this->input->post('type'))
				? $this->{sprintf('event_challenge_%s_model', $this->input->post('type'))}->get($this->input->post('challenge_id'))
				: [];

            $_POST['slug'] = $challenge_info['slug'] ?? '';

			$this->event_challenge_jury_model->add($this->input->post());
			redirect(base_url('admin/event_challenge_jury'), 'refresh');
		} elseif ($param1 == 'edit') {
            $challenge_info 						= !empty($this->input->post('challenge_id')) && !empty($this->input->post('type'))
				? $this->{sprintf('event_challenge_%s_model', $this->input->post('type'))}->get($this->input->post('challenge_id'))
				: [];

            $_POST['slug'] = $challenge_info['slug'] ?? '';

			$this->event_challenge_jury_model->edit($param2, $this->input->post());
			redirect(base_url('admin/event_challenge_jury'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_challenge_jury_model->delete($param2);
			redirect(base_url('admin/event_challenge_jury'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_challenge_jury');
		$data['action_add'] 	= base_url('admin/event_challenge_jury_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_challenge_jury');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_challenge_jury_form/edit/',
			],
			[
				'key'	=> 'build_jury_cert',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_challenges_jury_build_cert/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_challenge_jury/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_challenge_jury_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_challenge_jury');
			$data['action'] 						= base_url('admin/event_challenge_jury/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_challenge_jury');
			$data['action'] 						= base_url('admin/event_challenge_jury/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_challenge_jury_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);

			$event_name							 	= ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
		}

        $data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
			'options'	=> CHALLENGE_TYPES,
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=type,event_id'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_id',
			'label'		=> _l('select_challenge'),
			'required'	=> false,
			'value'		=> $info['challenge_id'] ?? '',
		];

		$layouts = [];

		for ($i = 1; $i <= 10; $i++) {
			$layouts[] = [
				'value' => $i,
				'label' => $i,
			];
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'layout',
			'label'		=> _l('select_layout'),
			'required'	=> true,
			'value'		=> $info['layout'] ?? 0,
			'options'	=> $layouts,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'is_dark',
			'label'		=> _l('select_is_dark'),
			'required'	=> true,
			'value'		=> $info['is_dark'] ?? 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('light'),
				],
				[
					'value' => 1,
					'label' => _l('dark'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'event_logo',
			'label'		=> _l('event_logo'),
			'required'	=> true,
			'value'		=> $info['event_logo'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'background',
			'label'		=> _l('desktop_background'),
			'required'	=> true,
			'value'		=> $info['background'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'mobile_background',
			'label'		=> _l('mobile_background'),
			'required'	=> true,
			'value'		=> $info['mobile_background'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'heading',
			'label'		=> _l('heading'),
			'required'	=> true,
			'value'		=> $info['heading'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'base_url',
			'label'		=> _l('base_url'),
			'required'	=> true,
			'value'		=> $info['base_url'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'limit',
			'label'		=> _l('limit'),
			'required'	=> true,
			'value'		=> $info['limit'] ?? 50,
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_challenge_jury() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_challenge_jury_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'challenge_id'			=> $result['challenge_id'],
				'event_id'				=> $result['event_id'],
				'event'					=> $event_info['name'] ?? '',
				'type'					=> $result['type'],
				'heading'				=> $result['heading'],
				'slug'				    => $result['slug'],
				'url'					=> !empty($result['slug']) ? sprintf('<a href="%s/%s" target="_blank">%s</a>', $result['base_url'] , $result['slug'], _l('visit')) : '',
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function event_challenges_jury_build_cert($challenge_id = 0) {
		$challenge_info = $this->event_challenge_jury_model->get($challenge_id);

		if (empty($challenge_info)) {
			$this->session->set_flashdata('error_message', _li('event_challenge_jury_not_found'));
			redirect(base_url('admin/event_challenge_jury'), 'refresh');
		}

		if (empty($template_info = $this->certificate_template_model->get_all([
			'event_id'			=> $challenge_info['event_id'],
			'challenge_id'		=> $challenge_info['challenge_id'],
			'challenge_type'	=> $challenge_info['type'],
			'is_jury'			=> 1
		])['rows'][0] ?? [])) {
			$this->session->set_flashdata('error_message', _li('event_challenge_jury_certificate_not_found'));
			redirect(base_url('admin/event_challenge_jury'), 'refresh');
		}

		$this->cron_model->add([
			'code'		=> 'buildJuryCertCron_' . (int)$challenge_id,
			'site_id'	=> 1,
			'action'	=> 'alert_model->buildJuryCertCron',
			'data'		=> [[
				'event_id'		=> (int)$challenge_info['event_id'],
				'challenge_id'	=> (int)$challenge_info['challenge_id'],
				'challenge_type'=> $challenge_info['type'],
			]],
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);

		$this->session->set_flashdata('flash_message', _li('event_challenges_jury_build_cert_is_added'));
		redirect(base_url('admin/event_challenge_jury'), 'refresh');
	}
}
