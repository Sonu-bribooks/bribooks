<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BroadcastPartner {
	public function broadcast_partner($param1 = '', $param2 = '') {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'actions',
		];

		if ($param1 == 'add') {
			$this->broadcast_partner_model->add([
				'name'	=> $this->input->post('name') ?? '',
			]);
			$this->session->set_flashdata('flash_message', _l('Broadcast Partner added successfully!'));
			redirect(base_url('admin/broadcast_partner'), 'refresh');
		} elseif ($param1 == 'edit') {
			$broadcast_partner_info	 = $this->broadcast_partner_model->get($param2);

			$this->broadcast_partner_model->edit($param2, [
				'name'		  => $this->input->post('name')	   ?? $broadcast_partner_info['name'],
			]);
			redirect(base_url('admin/broadcast_partner'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->broadcast_partner_model->delete($param2);
			redirect(base_url('admin/broadcast_partner'), 'refresh');
		}

		$data['page_name'] 		= 'broadcast_partner/index';
		$data['page_title'] 	= _l('broadcast_partner');
		$data['action_add'] 	= base_url('admin/broadcast_partner_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_broadcast_partner');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/broadcast_partner_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/broadcast_partner/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function broadcast_partner_form($param1 = NULL, $param2 = NULL) {
		$data['page_name'] 		= 'broadcast_partner/form';
		$data['page_title'] 	= _l('broadcast_partner_Add');
		$data['action'] 		= base_url('admin/broadcast_partner/add');

		if ($param1 == 'edit') {
			$data['page_title'] 	= _l('broadcast_partner_edit');
			$data['action'] 		= base_url('admin/broadcast_partner/edit/' . (int)$param2);
			$data['id'] 			= (int)$param2;
			$data['details'] 		= $this->broadcast_partner_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_broadcast_partner() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->broadcast_partner_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
