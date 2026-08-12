<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventDetail {
	public function event_detail($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {

            $this->event_detail_model->add([
				'event_id'              => (int)$this->input->post('event_id'),
				'logo'              	=> $this->input->post('logo'),
				'powered_by'            => trim($this->input->post('powered_by')),
				'short_description'     => trim($this->input->post('short_description')),
				'event_heading'         => trim($this->input->post('event_heading')),
				'long_description'      => trim($this->input->post('long_description')),
				'url'                   => trim($this->input->post('url')),
				'url_heading'           => trim($this->input->post('url_heading')),
				'url_description'       => trim($this->input->post('url_description')),
				'award'                 => json_encode(array_values($this->input->post('award'))),
				'medallion'             => !empty($this->input->post('medallion')) ? json_encode(array_values($this->input->post('medallion'))) : NULL,
				'partner'             	=> !empty($this->input->post('partner')) ? json_encode(array_values($this->input->post('partner'))) : NULL,
				'certificate'           => !empty($this->input->post('certificate')) ? json_encode(array_values($this->input->post('certificate'))) : NULL,
            ]);

			if (!empty($this->input->post('send_email'))) {
				CI_Events::trigger('event_school_signup', [
					'event_id' 	=> (int)$this->input->post('event_id')
				]);
			}

			redirect(site_url('admin/event_detail'), 'refresh');
		} elseif ($param1 == 'edit') {

			$this->event_detail_model->edit($param2,[
				'logo'              	=> $this->input->post('logo'),
				'powered_by'            => trim($this->input->post('powered_by')),
				'short_description'     => trim($this->input->post('short_description')),
				'event_heading'         => trim($this->input->post('event_heading')),
				'long_description'      => trim($this->input->post('long_description')),
				'url'                   => trim($this->input->post('url')),
				'url_heading'           => trim($this->input->post('url_heading')),
				'url_description'       => trim($this->input->post('url_description')),
				'award'                 => json_encode(array_values($this->input->post('award'))),
				'medallion'             => !empty($this->input->post('medallion')) ? json_encode(array_values($this->input->post('medallion'))) : NULL,
				'partner'             	=> !empty($this->input->post('partner')) ? json_encode(array_values($this->input->post('partner'))) : NULL,
				'certificate'           => !empty($this->input->post('certificate')) ? json_encode(array_values($this->input->post('certificate'))) : NULL,
            ]);
            $this->event_detail_model->add([
				'event_id'              => $this->input->post('event_id'),
				'powered_by'            => $this->input->post('powered_by'),
				'short_description'     => $this->input->post('short_description'),
				'event_heading'         => $this->input->post('event_heading'),
				'long_description'      => $this->input->post('long_description'),
				'url'                   => $this->input->post('url'),
				'url_heading'           => $this->input->post('url_heading'),
				'url_description'       => $this->input->post('url_description'),
				'award'                 => json_encode($this->input->post('award'))
            ]);

			redirect(site_url('admin/event_detail'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->event_detail_model->edit($param2,[
				'powered_by'            => $this->input->post('powered_by'),
				'short_description'     => $this->input->post('short_description'),
				'event_heading'         => $this->input->post('event_heading'),
				'long_description'      => $this->input->post('long_description'),
				'url'                   => $this->input->post('url'),
				'url_heading'           => $this->input->post('url_heading'),
				'url_description'       => $this->input->post('url_description'),
				'award'                 => json_encode($this->input->post('award'))
			]);
			redirect(site_url('admin/event_detail'), 'refresh');
		}

		$data['page_name'] 			= 'event_details/index';
		$data['page_title'] 		= _l('event_detail');
		$data['action_add'] 		= site_url('admin/event_detail_form/add');
		$data['action_ajax'] 		= site_url('admin/ajax_event_detail');
        $data['event_detail'] 			= $this->event_model->get_all()['rows'];

		$this->load->view('backend/index', $data);
	}

	public function event_detail_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			// $data['page_name'] 						= 'event_details/form_old';
			$data['page_name'] 						= 'event_details/form';
			$data['page_title'] 					= _l('event_detail_add');
			$data['action'] 						= site_url('admin/event_detail/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'event_details/form';
			$data['page_title'] 					= _l('event_detail_edit');
			$data['action'] 						= site_url('admin/event_detail/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->event_detail_model->get($param2);
			$data['partner'] 						= !empty($data['details']['partner']) ? array_column(json_decode($data['details']['partner']), 'name') : [];
			$data['medallion_data'] 			    = !empty($data['details']['medallion']) ? array_column(json_decode($data['details']['medallion']), 'id') : [];
			$data['certificate_data'] 			    = !empty($data['details']['certificate']) ? array_column(json_decode($data['details']['certificate']), 'id') : [];
		} 

		$data['medallion_types'] 						= $this->medallion_model->get_all()['rows'];
		// $data['certificate_types'] 						= $this->certificate_type_model->get_all([
		// 	'order' => 'ASC'
		// ])['rows'];
		$data['certificate_types'] 						= [];




		$this->load->view('backend/index', $data);
	}

	public function ajax_event_detail() {
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

		$results = $this->event_detail_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

            $json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
                'event_name'			=> $result['event_name'],
				'powered_by'			=> $result['powered_by'],
				'date_added'			=> formatDate($result['date_added']),
				'status'				=> _sd($result['status'] ? 1 : 0),
				'action' => '<div class="dropright dropright">
				<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="mdi mdi-dots-vertical"></i></button>
				<ul class="dropdown-menu">
				<li>
				<a class="dropdown-item " href="'.site_url('admin/event_detail_form/edit/').$result['id'].'" > Edit </a>
				</ul></div>'
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
