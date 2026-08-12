<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Unsubscribed {
	public function unsubscribed($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			if ($this->unsubscribed_model->get_all([
				'email'	=> $this->input->post('email'),
			])['total'] > 0) {
				$this->session->set_flashdata('error_message', _l('email_exists'));
			} else {
				$this->unsubscribed_model->add($this->input->post());
			}

			redirect(site_url('admin/unsubscribed'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->unsubscribed_model->edit($param2, $this->input->post());
			redirect(site_url('admin/unsubscribed'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->unsubscribed_model->enableDisable($param2);
			redirect(site_url('admin/unsubscribed'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->unsubscribed_model->delete($param2);
			redirect(site_url('admin/unsubscribed'), 'refresh');
		}

		$data['page_name'] 		= 'unsubscribed/index';
		$data['page_title'] 	= _l('unsubscribed');
		$data['action_add'] 	= site_url('admin/unsubscribed_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_unsubscribed');

		$this->load->view('backend/index', $data);
	}

	public function unsubscribed_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('unsubscribed_add');
			$data['action'] 		= base_url('admin/unsubscribed/add');
		} elseif ($param1 == 'edit') {
			$data['unsubscribed_id']= (int)$param2;
			$data['details'] 		= $this->unsubscribed_model->get($param2);
			$data['page_title'] 	= _l('unsubscribed_edit');
			$data['action'] 		= base_url('admin/unsubscribed/edit/' . (int)$param2);
		}

		$data['page_name'] 			= 'unsubscribed/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_unsubscribed() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->unsubscribed_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'			=> $filter_data['start'] + 1 + $key,
				'id'			=> $result['id'],
				'email'			=> $result['email'],
				'date_added'	=> formatDate($result['date_added']),
				'actions'		=> [
					'id' 		=> $result['id'],
				],
			];
		}

		output_json($json);
	}
}
