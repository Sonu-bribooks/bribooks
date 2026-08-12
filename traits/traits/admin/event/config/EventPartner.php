<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventPartner {
	public function event_partner($action = NULL, $id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'thumb',
			'status',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			self::_validateEventPartnerForm();

			$this->event_partner_model->add($this->input->post());
			redirect(base_url('admin/event_partner'), 'refresh');
		} elseif ($action == 'edit') {
			self::_validateEventPartnerForm($id);

			$this->event_partner_model->edit($id, $this->input->post());
			redirect(base_url('admin/event_partner'), 'refresh');
		} elseif ($action == 'status') {
			$this->event_partner_model->enableDisable($id, $this->input->post());
			redirect(base_url('admin/event_partner'), 'refresh');
		} elseif ($action == 'delete') {
			$this->event_partner_model->delete($id);
			redirect(base_url('admin/event_partner'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_partner');
		$data['action_add'] 	= base_url('admin/event_partner_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_partners');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_partner_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/event_partner/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_partner/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_partner_form($action = NULL, $id = NULL) {
		if ($action == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_partner_add');
			$data['action'] 						= base_url('admin/event_partner/add');
		} elseif ($action == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_partner_edit');
			$data['action'] 						= base_url('admin/event_partner/edit/' . (int)$id);

			$data['id'] 							= (int)$id;
			$info 									= $this->event_partner_model->get($id);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> true,
			'value'		=> $info['image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
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

	public function ajax_event_partners() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_partner_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'thumb'					=> $this->image_model->thumb($result['image']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateEventPartnerForm($id = 0) {

	}

	public function ajax_search_event_partners() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->event_partner_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
