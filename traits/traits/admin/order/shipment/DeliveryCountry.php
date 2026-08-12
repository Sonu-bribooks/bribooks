<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DeliveryCountry {
	public function delivery_country($action = NULL, $id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'country_code',
			'free_shipping',
			'status',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			self::_validateDeliveryCountryForm();

			$this->delivery_country_model->add([
				'country_id' 	=> $this->input->post('country_id'),
				'name' 			=> $this->country_model->get($this->input->post('country_id'))['name'] ?? '',
				'country_code' 	=> $this->country_model->get($this->input->post('country_id'))['code'] ?? '',
				'status' 		=> $this->input->post('status'),
				'buying_options'=> json_encode($this->input->post('buying_options')),
				'free_shipping' => $this->input->post('free_shipping'),
			]);
			redirect(base_url('admin/delivery_country'), 'refresh');
		} elseif ($action == 'edit') {
			self::_validateDeliveryCountryForm($id);

			$this->delivery_country_model->edit($id, [
				'status' 		=> $this->input->post('status'),
				'buying_options'=> json_encode($this->input->post('buying_options')),
				'free_shipping' => $this->input->post('free_shipping'),
			]);
			redirect(base_url('admin/delivery_country'), 'refresh');
		} elseif ($action == 'status') {
			$this->delivery_country_model->enableDisable($id, $this->input->post());
			redirect(base_url('admin/delivery_country'), 'refresh');
		} elseif ($action == 'delete') {
			$this->delivery_country_model->delete($id);
			redirect(base_url('admin/delivery_country'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('delivery_country');
		$data['action_add'] 	= base_url('admin/delivery_country_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_delivery_country');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/delivery_country_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/delivery_country/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/delivery_country/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function delivery_country_form($action = NULL, $id = NULL) {
		if ($action == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('delivery_country_add');
			$data['action'] 						= base_url('admin/delivery_country/add');
		} elseif ($action == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('delivery_country_edit');
			$data['action'] 						= base_url('admin/delivery_country/edit/' . (int)$id);

			$data['id'] 							= (int)$id;
			$info 									= $this->delivery_country_model->get($id);
			$info['buying_options'] 				= json_decode($info['buying_options'], TRUE);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_id',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> [
				'value'	=> $info['country_id'] ?? '',
				'label'	=> $info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country')
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'buying_options[ebook]',
			'label'		=> _l('ebook'),
			'required'	=> true,
			'value'		=> $info['buying_options']['ebook'] ?? 0,
			'options'	=> [
				[
					'label'	=> _l('force_enable'),
					'value'	=> 2,
				],
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

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'buying_options[audio_book]',
			'label'		=> _l('audio_book'),
			'required'	=> true,
			'value'		=> $info['buying_options']['audio_book'] ?? 0,
			'options'	=> [
				[
					'label'	=> _l('force_enable'),
					'value'	=> 2,
				],
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

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'free_shipping',
			'label'		=> _l('free_shipping'),
			'required'	=> true,
			'value'		=> $info['free_shipping'] ?? 1,
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

	public function ajax_delivery_country() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->delivery_country_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'country_code'			=> $result['country_code'],
				'free_shipping'			=> _sd($result['free_shipping']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateDeliveryCountryForm($id = 0) {
		$info = $id ? $this->delivery_country_model->get($id) : [];

		if (
			!empty($this->delivery_country_model->get_all([
				'country_id' => (int)$this->input->post('country_id')
			])['rows'][0] ?? []) &&
			(($info && $info['country_id'] != $this->input->post('country_id')) || empty($info))
		) {
			$this->session->set_flashdata('error_message', _l('already_registered_country'));
			redirect(base_url('admin/delivery_country'), 'refresh');
		}

		$country_info = $this->country_model->get($this->input->post('country_id'));

		$_POST['name'] 			= $country_info['name'];
		$_POST['country_code'] 	= $country_info['code'];
	}

	public function ajax_search_delivery_country() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->delivery_country_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
