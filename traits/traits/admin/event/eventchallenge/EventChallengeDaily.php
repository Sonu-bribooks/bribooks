<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventChallengeDaily {
	public function event_challenges_daily($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'event_id',
			'name',
			'book_sold',
			'display_date',
			'start_date',
			'end_date',
			'actions',
		];

		if ($param1 == 'add') {
			$this->event_challenge_daily_model->add($this->input->post());
			redirect(base_url('admin/event_challenges_daily'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->event_challenge_daily_model->edit($param2, $this->input->post());
			redirect(base_url('admin/event_challenges_daily'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_challenge_daily_model->delete($param2);
			redirect(base_url('admin/event_challenges_daily'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_challenges_daily');
		$data['action_add'] 	= base_url('admin/event_challenge_daily_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_challenges_daily');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_challenge_daily_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_challenges_daily/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_challenge_daily_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_challenge_daily');
			$data['action'] 						= base_url('admin/event_challenges_daily/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_challenge_daily');
			$data['action'] 						= base_url('admin/event_challenges_daily/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_challenge_daily_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);

			$event_name							 = ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
		}

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
		];


		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'event_challenge_id',
			'label'		=> _l('event_challenge_id'),
			'required'	=> true,
			'value'		=> $info['event_challenge_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'book_sold',
			'label'		=> _l('book_sold'),
			'value'		=> $info['book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'display_date',
			'label'		=> _l('display_date'),
			'required'	=> true,
			'value'		=> $info['display_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'start_date',
			'label'		=> _l('start_date'),
			'required'	=> true,
			'value'		=> $info['start_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'end_date',
			'label'		=> _l('end_date'),
			'required'	=> true,
			'value'		=> $info['end_date'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'display_end_date',
			'label'		=> _l('display_end_date'),
			'required'	=> true,
			'value'		=> $info['display_end_date'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_challenges_daily() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_challenge_daily_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'event_id'				=> $result['event_id'],
				'name'					=> $result['name'],
				'book_sold'				=> $result['book_sold'],
				'display_date'			=> $result['display_date'],
				'start_date'			=> $result['start_date'],
				'end_date'				=> $result['end_date'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
