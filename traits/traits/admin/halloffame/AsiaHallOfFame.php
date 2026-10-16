<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AsiaHallOfFame {
    public function asia_fame_country($param1 = NULL, $param2 = NULL) {
        $data['fields'] = [
			'sn',
			'id',
			'country_code',
            'country_name',
			'book_sold',
			'priority',
			'actions',
		];

        if ($param1 == 'add') {
            $data 			= $this->input->post();

           if (!empty($country_fame_info = $this->hall_of_fame_asia_country_model->get_all([
                'country_code'  => $data['country_code'],
                'is_country'    => 1,
                'start'         => 0,
                'limit'         => 1
            ])['rows'][0] ?? [])) {
                $this->session->set_flashdata('error_message', _li('asia_fame_already_exist_for_this_country.'));
                redirect(base_url('admin/asia_fame_country'), 'refresh');
            }

			$this->hall_of_fame_asia_country_model->add($data);
            redirect(base_url('admin/asia_fame_country'), 'refresh');
        } else if ($param1 == 'edit') {
			$country_fame_info = $this->hall_of_fame_asia_country_model->get($param2);

            if (empty($country_fame_info)) {
                $this->session->set_flashdata('error_message', _li('invalid_asia_fame_id.'));
                redirect(base_url('admin/asia_fame_country'), 'refresh');
            }

			$data 			= $this->input->post();
            $old_book_sold  = (int) ($country_fame_info['book_sold'] ?? 0);
            $new_book_sold  = (int) ($data['book_sold'] ?? 0);

			$this->hall_of_fame_asia_country_model->edit($param2, $data);

            if ($old_book_sold !== $new_book_sold) {
                $this->alert_model->enrolBookToHallOfFameAlert($param2, 'asia');
            }

			redirect(base_url('admin/asia_fame_country'), 'refresh');
		} else if ($param1 == 'delete') {

			$asia_fame_info 	= $this->hall_of_fame_asia_country_model->delete($param2);
			redirect(base_url('admin/asia_fame_country'), 'refresh');
		} 

		$data['page_name'] 		= 'hall_of_fame/index';
		$data['page_title'] 	= _l('asia_fame');
        $data['action_add'] 	= base_url('admin/asia_fame_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_asia_fame_country');
        $data['active_tab']		= 'country';
        $data['fame_type']		= 'asia';

        $data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/asia_fame_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/asia_fame_country/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

    public function ajax_asia_fame_country() {
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

        $results = $this->hall_of_fame_asia_country_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

        foreach ($results['rows'] ?? [] as $key => $result) {
            $country_info = $this->country_model->getByCode($result['country_code']);
			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
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

    public function asia_fame_form($param1 = NULL, $param2 = NULL) {

        if ($param1 == 'add') {
			$data['page_name'] 						= 'hall_of_fame/form';
			$data['page_title'] 					= _l('add_asia_hall_of_fame');
			$data['action'] 						= base_url('admin/asia_fame_country/add');
            $data['country_info']                   = $this->country_model->get_all()['rows'] ?? [];
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'hall_of_fame/form';
			$data['page_title'] 					= _l('edit_asia_hall_of_fame');
			$data['action'] 						= base_url('admin/asia_fame_country/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['info'] 						    = $this->hall_of_fame_asia_country_model->get($param2);
            $data['country_info']                   = $this->country_model->get_all()['rows'] ?? [];
		}

        $data['active_tab']		= 'country';
		$this->load->view('backend/index', $data);
	}

    public function asia_fame_event($param1 = NULL, $param2 = NULL) {
        $data['fields'] = [
			'sn',
			'id',
            'event_id',
            'event_name',
			'country_code',
            'country_name',
			'book_sold',
			'priority',
			'actions',
		];

        if ($param1 == 'add') {
            $data 			= $this->input->post();

            if (!empty($country_fame_info = $this->hall_of_fame_asia_country_model->get_all([
                'country_code'  => $data['country_code'],
                'event_id'      => $data['event_id'],
                'is_event'      => 1,
                'start'         => 0,
                'limit'         => 1
            ])['rows'][0] ?? [])) {
                $this->session->set_flashdata('error_message', _li('asia_fame_already_exist_for_this_event.'));
                redirect(base_url('admin/asia_fame_event'), 'refresh');
            }

			$this->hall_of_fame_asia_country_model->add($data);

            redirect(base_url('admin/asia_fame_event'), 'refresh');
        } else if ($param1 == 'edit') {
			$event_fame_info = $this->hall_of_fame_asia_country_model->get($param2);
            
            if (empty($event_fame_info)) {
                $this->session->set_flashdata('error_message', _li('invalid_asia_fame_id.'));
                redirect(base_url('admin/asia_fame_event'), 'refresh');
            }

			$data 			= $this->input->post();
            $old_book_sold  = (int) ($event_fame_info['book_sold'] ?? 0);
            $new_book_sold  = (int) ($data['book_sold'] ?? 0);

			$this->hall_of_fame_asia_country_model->edit($param2, $data);

            if ($old_book_sold !== $new_book_sold) {
                $this->alert_model->enrolBookToHallOfFameAlert($param2, 'asia');
            }

			redirect(base_url('admin/asia_fame_event'), 'refresh');
		} else if ($param1 == 'delete') {

			$asia_fame_info 	= $this->hall_of_fame_asia_country_model->delete($param2);
			redirect(base_url('admin/asia_fame_event'), 'refresh');
		} 

		$data['page_name'] 		= 'hall_of_fame/index';
		$data['page_title'] 	= _l('asia_fame');
        $data['action_add'] 	= base_url('admin/asia_fame_event_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_asia_fame_event');
        $data['active_tab']		= 'event';
        $data['fame_type']		= 'asia';

        $data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/asia_fame_event_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/asia_fame_event/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

    public function ajax_asia_fame_event() {
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

        $results = $this->hall_of_fame_asia_country_model->get_all($filter_data);

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

    public function asia_fame_event_form($param1 = NULL, $param2 = NULL) {

        if ($param1 == 'add') {
			$data['page_name'] 						= 'hall_of_fame/form';
			$data['page_title'] 					= _l('add_asia_hall_of_fame');
			$data['action'] 						= base_url('admin/asia_fame_event/add');
            $data['event_info'] 					= $this->event_model->get_all()['rows'] ?? [];
            $data['country_info']                   = $this->country_model->get_all()['rows'] ?? [];
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'hall_of_fame/form';
			$data['page_title'] 					= _l('edit_asia_hall_of_fame');
			$data['action'] 						= base_url('admin/asia_fame_event/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['info'] 						    = $this->hall_of_fame_asia_country_model->get($param2);
			$data['event_info'] 					= $this->event_model->get_all()['rows'] ?? [];
            $data['country_info']                   = $this->country_model->get_all()['rows'] ?? [];
		}

        $data['active_tab']		= 'event';
		$this->load->view('backend/index', $data);
	}
}