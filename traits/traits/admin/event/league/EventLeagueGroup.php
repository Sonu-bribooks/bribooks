<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventLeagueGroup {
	public function event_league_group($param1 = null,$param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'name',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$this->event_league_group_model->add($this->input->post());
			redirect(base_url('admin/event_league_group'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->event_league_group_model->edit($param2, $this->input->post());
			redirect(base_url('admin/event_league_group'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_league_group_model->delete($param2);
			redirect(base_url('admin/event_league_group'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_league_group');
		$data['action_add'] 	= base_url('admin/event_league_group_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_league_groups');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_league_group_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_league_group/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_league_group_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_league_group');
			$data['action'] 						= base_url('admin/event_league_group/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_event_league_group');
			$data['action'] 						= base_url('admin/event_league_group/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_league_group_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
			$event_name							 	= ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
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
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'	 => $info['status'] ?? 1,
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

	public function ajax_event_league_groups() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_league_group_model->get_all($filter_data);

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
				'name'					=> $result['name'],
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
