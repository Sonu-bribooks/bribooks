<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ShareTemplateMessage {
	public function share_template($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->share_template_model->add([
				'event_id' => $this->input->post('event_id'),
				'message'  => $this->input->post('message'),
				'type'     => $this->input->post('type') ?? '0'
			]);
			redirect(site_url('admin/share_template'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->share_template_model->edit($param2, [
				'message' => $this->input->post('message')
			]);
			redirect(site_url('admin/share_template'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->share_template_model->delete($param2);
			redirect(site_url('admin/share_template'), 'refresh');
		}

		$data['page_name'] 		= 'share_template/index';
		$data['page_title'] 	= _l('share_template');
		$data['action_add'] 	= site_url('admin/share_template_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_share_template');

		$this->load->view('backend/index', $data);
	}

	public function share_template_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'share_template/form';
			$data['page_title'] 					= _l('share_template');
			$data['action'] 						= site_url('admin/share_template/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'share_template/form';
			$data['page_title'] 					= _l('share_template_edit');
			$data['action'] 						= site_url('admin/share_template/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->share_template_model->get($param2);
		}

		$data['events']	= $this->event_model->get_all()['rows'];

		$this->load->view('backend/index', $data);
	}

	public function ajax_share_template() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->share_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'event_name'			=> !empty($result['event_name']) ? $result['event_name'] : 'ALL',
				'type'					=> !empty($result['type']) ? 'Invite' : 'Normal',
				'message'				=> $result['message'],
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function check_event_share_template() {
		$temp_info = $this->share_template_model->get_all([
			'event_id'	=> $this->input->post('event_id'),
			'type'		=> $this->input->post('type') ?? '0'
		])['rows'][0] ?? [];

		$json['event_id']	= "";

		if(!empty($temp_info)){
			$json['event_id']	= $temp_info['event_id'];
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
