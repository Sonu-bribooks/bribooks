<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Announcement {
	public function announcement($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->user_announcements_model->add($this->input->post());
			redirect(site_url('admin/announcement'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->user_announcements_model->edit($param2, $this->input->post());
			redirect(site_url('admin/announcement'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->user_announcements_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/announcement'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->user_announcements_model->delete($param2);
			redirect(site_url('admin/announcement'), 'refresh');
		}

		$data['page_name'] 		= 'announcement/index';
		$data['page_title'] 	= _l('announcement');
		$data['action_add'] 	= site_url('admin/announcement_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_announcement');

		$this->load->view('backend/index', $data);
	}

	public function announcement_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'announcement/form';
			$data['page_title'] 					= _l('announcement_add');
			$data['action'] 						= site_url('admin/announcement/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'announcement/form';
			$data['page_title'] 					= _l('announcement_edit');
			$data['action'] 						= site_url('admin/announcement/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->user_announcements_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_announcement() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->user_announcements_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'subject'				=> $result['subject'],
				'message'				=> $result['message'],
				'status'				=> _sd($result['status']),
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_get_announcements() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->user_announcements_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['name'],
				];
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
