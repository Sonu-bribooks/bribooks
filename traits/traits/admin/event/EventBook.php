<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventBook {
	public function event_book($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
            $this->event_book_model->add([
				'event_id'  => $this->input->post('event_id'),
				'book_id'   => $this->input->post('book_id')
			]);
			redirect(site_url('admin/event_book'), 'refresh');
		}

		$data['page_name'] 			= 'events/book/index';
		$data['page_title'] 		= _l('event_book');
		$data['action_add'] 		= site_url('admin/event_book_form/add');
		$data['action_ajax'] 		= site_url('admin/ajax_event_book');
        $data['events'] 			= $this->event_model->get_all()['rows'];

		$this->load->view('backend/index', $data);
	}

	public function event_book_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'events/book/form';
			$data['page_title'] 					= _l('event_book_add');
			$data['action'] 						= site_url('admin/event_book/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'events/book/form';
			$data['page_title'] 					= _l('event_book_edit');
			$data['action'] 						= site_url('admin/event_book/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->event_book_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_book() {
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

		$results = $this->event_book_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

            $json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
                'event_name'			=> $result['event_name'],
				'book_name'			    => ucwords($result['book_name']),
				'date_added'			=> formatDate($result['date_added']),
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

    public function check_book_in_event() {
        $data = $this->event_book_model->getEventbookBybookId($this->input->post('event_id'), $this->input->post('book_id'));
        $json['status'] = false;
		if(!empty($data)) {
            $json['status'] = true;
        }
		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
