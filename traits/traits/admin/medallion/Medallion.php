<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Medallion {
	public function medallion($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->medallion_model->add($this->input->post());
			redirect(site_url('admin/medallion'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->medallion_model->edit($param2, $this->input->post());
			redirect(site_url('admin/medallion'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->medallion_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/medallion'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->medallion_model->delete($param2);
			redirect(site_url('admin/medallion'), 'refresh');
		}

		$data['page_name'] 		= 'medallion/medallion/index';
		$data['page_title'] 	= _l('medallion');
		$data['action_add'] 	= site_url('admin/medallion_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_medallion');

		$this->load->view('backend/index', $data);
	}

	public function medallion_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'medallion/medallion/form';
			$data['page_title'] 					= _l('medallion_add');
			$data['action'] 						= site_url('admin/medallion/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'medallion/medallion/form';
			$data['page_title'] 					= _l('medallion_edit');
			$data['action'] 						= site_url('admin/medallion/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->medallion_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_medallion() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->medallion_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'type'					=> $result['type'],
				'quantity'				=> $result['quantity'],
				'sold'					=> $result['sold'],
				'price'					=> $result['price'],
				'weight'				=> $result['weight'],
				'min_published'			=> $result['min_published'],
				'max_published'			=> $result['max_published'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_medallions() {
		$json = [];

		$filter_data = [
			'status'			=> 1,
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->medallion_model->get_all($filter_data)['rows'] ?? [];

		$json[] = [
			'id'				=> 0,
			'text'				=> 'No',
		];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
