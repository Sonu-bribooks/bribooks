<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventUser {
	public function event_user($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
            $this->event_user_model->add([
				'event_id'  => $this->input->post('event_id'),
				'user_id'   => $this->input->post('user_id')
			]);
			redirect(site_url('admin/event_user'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->event_user_model->edit($param2,[
				'name' => trim(ucwords($this->input->post('name')))
			]);
			redirect(site_url('admin/event_user'), 'refresh');
		}

		$data['page_name'] 			= 'events/user/index';
		$data['page_title'] 		= _l('event_user');
		$data['action_add'] 		= site_url('admin/event_user_form/add');
		$data['action_ajax'] 		= site_url('admin/ajax_event_user');
        $data['events'] 			= $this->event_model->get_all()['rows'];

		$this->load->view('backend/index', $data);
	}

	public function event_user_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'events/user/form';
			$data['page_title'] 					= _l('event_user_add');
			$data['action'] 						= site_url('admin/event_user/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'events/user/form';
			$data['page_title'] 					= _l('event_user_edit');
			$data['action'] 						= site_url('admin/event_user/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->event_user_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_user() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

        if(!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->event_user_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

            $json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
                'event_name'			=> $result['event_name'],
				'user_name'			    => ucwords($result['first_name']. ' '. $result['last_name']),
				'date_added'			=> formatDate($result['date_added']),
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

    public function check_user_in_event() {
        $data = $this->event_user_model->getEventUserByUserId($this->input->post('event_id'), $this->input->post('user_id'));
        $json['status'] = false;
		if(!empty($data)) {
            $json['status'] = true;
        }
		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
