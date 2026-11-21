<?php defined('BASEPATH') or exit('No direct script access allowed');

trait HallOfFames {
    public function hall_of_fame_country($param1 = NULL, $param2 = NULL) {
        $data['fields'] = [
			'sn',
			'id',
			'type',
			'country_code',
            'country_name',
			'book_sold',
			'priority',
			'actions',
		];

        if ($param1 == 'add') {
            $data 			= $this->input->post();

           if (!empty($country_fame_info = $this->hall_of_fame_country_model->get_all([
                'country_code'  => $data['country_code'],
				'type'			=> $data['type'],
                'is_country'    => 1,
                'start'         => 0,
                'limit'         => 1
            ])['rows'][0] ?? [])) {
                $this->session->set_flashdata('error_message', _li('hall_of_fame_already_exist_for_this_country.'));
                redirect(base_url('admin/hall_of_fame_country'), 'refresh');
            }

			$this->hall_of_fame_country_model->add($data);
            redirect(base_url('admin/hall_of_fame_country'), 'refresh');
        } else if ($param1 == 'edit') {
			$country_fame_info = $this->hall_of_fame_country_model->get($param2);

            if (empty($country_fame_info)) {
                $this->session->set_flashdata('error_message', _li('invalid_hall_of_fame_id.'));
                redirect(base_url('admin/hall_of_fame_country'), 'refresh');
            }

			$data 			= $this->input->post();
            $old_book_sold  = (int) ($country_fame_info['book_sold'] ?? 0);
            $new_book_sold  = (int) ($data['book_sold'] ?? 0);

			$this->hall_of_fame_country_model->edit($param2, $data);

            if ($old_book_sold !== $new_book_sold) {
                $this->alert_model->enrolBookToHallOfFameAlert($param2, $data['type']);
            }

			redirect(base_url('admin/hall_of_fame_country'), 'refresh');
		} else if ($param1 == 'delete') {

			$hall_of_fame_info 	= $this->hall_of_fame_country_model->delete($param2);
			redirect(base_url('admin/hall_of_fame_country'), 'refresh');
		} 

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('hall_of_fame');
        $data['action_add'] 	= base_url('admin/hall_of_fame_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_hall_of_fame_country');
		$data['nav']			= 'country';
		$data['navs']			= [
			[
				'key'	=> 'country',
				'url'	=> 'admin/hall_of_fame_country',
				'name'	=> 'country_list'
			],
			[
				'key'	=> 'event',
				'url'	=> 'admin/hall_of_fame_event',
				'name'	=> 'event_list'
			]
		];

        $data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/hall_of_fame_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/hall_of_fame_country/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

    public function ajax_hall_of_fame_country() {
        $json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
            'is_country'        => 1
		];

		if (!empty($this->input->get('search_value'))) {
			$filter_data['search'] = $this->input->get('search_value');
		}

		if (!empty($this->input->get('type'))) {
			$filter_data['type'] = $this->input->get('type');
		}

        $results = $this->hall_of_fame_country_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

        foreach ($results['rows'] ?? [] as $key => $result) {
            $country_info = $this->country_model->getByCode($result['country_code']);
			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'type'				=> $result['type'] ?? '',
				'country_code'		=> $result['country_code'],
                'country_name'      => $country_info['name'] ?? '',
				'book_sold'			=> $result['book_sold'] ?? '',
				'priority' 		    => $result['priority'],
				'actions'			=> [
					'id' 				=> $result['id'],
					'status' 			=> $result['status'] ?? 0
				],
			];
		}

		output_json($json);
    }

    public function hall_of_fame_form($param1 = NULL, $param2 = NULL) {

        if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_hall_of_fame');
			$data['action'] 						= base_url('admin/hall_of_fame_country/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_hall_of_fame');
			$data['action'] 						= base_url('admin/hall_of_fame_country/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info						    		= $this->hall_of_fame_country_model->get($param2);
            $country_info                  			= $this->country_model->get_all(['code' => $info['country_code']])['rows'][0] ?? '';
		}

        $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_code',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['country_code'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country_code'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
			'options'	=> [
				[
					'value' => 'global',
					'label' => _l('global'),
				],
				[
					'value' => 'asia',
					'label' => _l('asia'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'book_sold',
			'label'		=> _l('book_sold'),
			'required'	=> true,
			'value'		=> $info['book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'priority',
			'label'		=> _l('priority'),
			'required'	=> true,
			'value'		=> $info['priority'] ?? '',
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
					'label' => _l('enabled'),
				],
				[
					'value' => 0,
					'label' => _l('disabled'),
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

    public function hall_of_fame_event($param1 = NULL, $param2 = NULL) {
        $data['fields'] = [
			'sn',
			'id',
            'event_id',
            'event_name',
			'type',
			'country_code',
            'country_name',
			'book_sold',
			'priority',
			'actions',
		];

        if ($param1 == 'add') {
            $data 			= $this->input->post();

            if (!empty($country_fame_info = $this->hall_of_fame_country_model->get_all([
                'country_code'  => $data['country_code'],
                'event_id'      => $data['event_id'],
				'type'			=> $data['type'],
                'is_event'      => 1,
                'start'         => 0,
                'limit'         => 1
            ])['rows'][0] ?? [])) {
                $this->session->set_flashdata('error_message', _li('hall_of_fame_already_exist_for_this_event.'));
                redirect(base_url('admin/hall_of_fame_event'), 'refresh');
            }

			$this->hall_of_fame_country_model->add($data);

            redirect(base_url('admin/hall_of_fame_event'), 'refresh');
        } else if ($param1 == 'edit') {
			$event_fame_info = $this->hall_of_fame_country_model->get($param2);
            
            if (empty($event_fame_info)) {
                $this->session->set_flashdata('error_message', _li('invalid_hall_of_fame_id.'));
                redirect(base_url('admin/hall_of_fame_event'), 'refresh');
            }

			$data 			= $this->input->post();
            $old_book_sold  = (int) ($event_fame_info['book_sold'] ?? 0);
            $new_book_sold  = (int) ($data['book_sold'] ?? 0);

			$this->hall_of_fame_country_model->edit($param2, $data);

            if ($old_book_sold !== $new_book_sold) {
                $this->alert_model->enrolBookToHallOfFameAlert($param2, $data['type']);
            }

			redirect(base_url('admin/hall_of_fame_event'), 'refresh');
		} else if ($param1 == 'delete') {

			$hall_of_fame_info 	= $this->hall_of_fame_country_model->delete($param2);
			redirect(base_url('admin/hall_of_fame_event'), 'refresh');
		} 

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('hall_of_fame');
        $data['action_add'] 	= base_url('admin/hall_of_fame_event_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_hall_of_fame_event');
		$data['nav']			= 'event';
		$data['navs']			= [
			[
				'key'	=> 'country',
				'url'	=> 'admin/hall_of_fame_country',
				'name'	=> 'country_list'
			],
			[
				'key'	=> 'event',
				'url'	=> 'admin/hall_of_fame_event',
				'name'	=> 'event_list'
			]
		];

        $data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/hall_of_fame_event_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/hall_of_fame_event/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

    public function ajax_hall_of_fame_event() {
        $json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
            'is_event'          => 1
		];

		if (!empty($this->input->get('search_value'))) {
			$filter_data['search'] = $this->input->get('search_value');
		}

		if (!empty($this->input->get('type'))) {
			$filter_data['type'] = $this->input->get('type');
		}

        $results = $this->hall_of_fame_country_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

        foreach ($results['rows'] ?? [] as $key => $result) {
            $country_info = $this->country_model->getByCode($result['country_code']);
            $event_info = $this->event_model->get($result['event_id'] ?? '');
			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
                'event_id'          => $result['event_id'] ?? '',
                'event_name'        => !empty($event_info) ? $event_info['name'] : '',
				'type'				=> $result['type'] ?? '',
				'country_code'		=> $result['country_code'],
                'country_name'      => $country_info['name'] ?? '',
				'book_sold'			=> $result['book_sold'] ?? '',
				'priority' 		    => $result['priority'],
				'actions'			=> [
					'id' 				=> $result['id'],
					'status' 			=> $result['status'] ?? 0
				],
			];
		}

		output_json($json);
    }

    public function hall_of_fame_event_form($param1 = NULL, $param2 = NULL) {

        if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_hall_of_fame');
			$data['action'] 						= base_url('admin/hall_of_fame_event/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_hall_of_fame');
			$data['action'] 						= base_url('admin/hall_of_fame_event/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info						    		= $this->hall_of_fame_country_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
            $country_info                   		= $this->country_model->get_all(['code' => $info['country_code']])['rows'][0] ?? '';
			$event_name							 	= ($info['event_id'] == 0) ? "Generic" : $event_info['name'];
		}

        $data['active_tab']		= 'event';

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
			'type'		=> 'select2',
			'key'		=> 'country_code',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['country_code'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country_code'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
			'options'	=> [
				[
					'value' => 'global',
					'label' => _l('global'),
				],
				[
					'value' => 'asia',
					'label' => _l('asia'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'book_sold',
			'label'		=> _l('book_sold'),
			'required'	=> true,
			'value'		=> $info['book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'priority',
			'label'		=> _l('priority'),
			'required'	=> true,
			'value'		=> $info['priority'] ?? '',
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
					'label' => _l('enabled'),
				],
				[
					'value' => 0,
					'label' => _l('disabled'),
				],
			],
		];

		$this->load->view('backend/index', $data);
	}
}