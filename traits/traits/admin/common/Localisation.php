<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Localisation {
	public function countries($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'lang_code',
			'tel_code',
			'code',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->country_model->add($this->input->post());
			redirect(base_url('admin/countries'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->country_model->edit($param2, $this->input->post());
			redirect(base_url('admin/countries'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->country_model->delete($param2);
			redirect(base_url('admin/countries'), 'refresh');
		}

		$data['page_name'] = 'country/index';
		$data['page_title'] = _l('country');

		$data['action_add'] 	= base_url('admin/country_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_countries');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/country_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/countries/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function country_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'country/form';

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('country_add');
			$data['action'] 		= base_url('admin/countries/add');
		} elseif ($param1 == 'edit') {
			$data['page_title'] 	= _l('country_edit');
			$data['country_id'] 	= (int)$param2;
			$data['action'] 		= base_url('admin/countries/edit/' . (int)$param2);
			$data['details'] 		= $this->country_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_countries() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->country_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'code'					=> $result['code'],
				'lang_code'				=> $result['lang_code'],
				'tel_code'				=> $result['tel_code'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function states($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'country',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->state_model->add($this->input->post());
			redirect(base_url('admin/states'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->state_model->edit($param2, $this->input->post());
			redirect(base_url('admin/states'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->state_model->delete($param2);
			redirect(base_url('admin/states'), 'refresh');
		}

		$data['page_name'] = 'state/index';
		$data['page_title'] = _l('state');

		$data['action_add'] 	= base_url('admin/state_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_states');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/state_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/states/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function state_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] = _l('state_add');
			$data['action'] 	= base_url('admin/states/add');
		} elseif ($param1 == 'edit') {
			$data['state_id'] 	= (int)$param2;
			$data['action'] 	= base_url('admin/states/edit/' . (int)$param2);
			$data['details'] 	= $this->state_model->get($param2);
			$data['page_title'] = _l('state_edit');
		}

		$data['page_name'] 	= 'state/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_states() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->state_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'country'				=> $result['country'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function cities($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'state',
			'country',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->city_model->add($this->input->post());
			redirect(base_url('admin/cities'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->city_model->edit($param2, $this->input->post());
			redirect(base_url('admin/cities'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->city_model->delete($param2);
			redirect(base_url('admin/cities'), 'refresh');
		}

		$data['page_name'] 	= 'city/index';
		$data['page_title'] = _l('city');

		$data['action_add'] 	= base_url('admin/city_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_cities');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/city_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/cities/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function city_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] = _l('city_add');
			$data['action'] 	= base_url('admin/cities/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 	= 'city/form';
			$data['city_id'] 	= (int)$param2;
			$data['action'] 	= base_url('admin/cities/edit/' . (int)$param2);
			$data['details'] 	= $this->city_model->get($param2);
			$data['page_title'] = _l('city_edit');
		}

		$data['page_name'] = 'city/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_cities() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->city_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'state'					=> $result['state'],
				'country'				=> $result['country'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function currencies($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'code',
			'symbol',
			'exchange_rate',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$this->currency_model->add($this->input->post());
			redirect(base_url('admin/currencies'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->currency_model->edit($param2, $this->input->post());
			redirect(base_url('admin/currencies'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->currency_model->delete($param2);
			redirect(base_url('admin/currencies'), 'refresh');
		}

		$data['page_name'] 	= 'currency/index';
		$data['page_title'] = _l('currency');

		$data['action_add'] 	= base_url('admin/currency_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_currencies');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/currency_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/currencies/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function currency_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('currency_add');
			$data['action'] 		= base_url('admin/currencies/add');
		} elseif ($param1 == 'edit') {
			$data['page_title'] 	= _l('currency_edit');
			$data['country_id'] 	= (int)$param2;
			$data['action'] 		= base_url('admin/currencies/edit/' . (int)$param2);
			$data['details'] 		= $this->currency_model->get($param2);
		}

		$data['page_name'] 		= 'currency/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_currencies() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->currency_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'code'					=> $result['code'],
				'symbol'				=> $result['symbol'],
				'exchange_rate'			=> $result['exchange_rate'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function payment_settings($param1 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'system_currency') {
			$this->crud_model->update_system_currency();
			redirect(base_url('admin/payment_settings'), 'refresh');
		}
		if ($param1 == 'paypal_settings') {
			$this->crud_model->update_paypal_settings();
			redirect(base_url('admin/payment_settings'), 'refresh');
		}
		if ($param1 == 'stripe_settings') {
			$this->crud_model->update_stripe_settings();
			redirect(base_url('admin/payment_settings'), 'refresh');
		}

		$data['page_name'] = 'payment_settings';
		$data['page_title'] = _l('payment_settings');
		$this->load->view('backend/index', $data);
	}

	public function ajax_search_country() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->country_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}

	public function ajax_search_state() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->state_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['country']),
			];
		}

		output_json($json);
	}

	public function ajax_search_city() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->city_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s, %s)', $result['name'], $result['state'], $result['country']),
			];
		}

		output_json($json);
	}

	public function ajax_search_currency() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->currency_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s, %s)', $result['name'], $result['symbol'], $result['code']),
			];
		}

		output_json($json);
	}

	public function language($action = NULL, $id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'code',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			self::_validateLanguageForm();

			$data = $this->input->post();

			$this->language_model->add($data);
			redirect(base_url('admin/language'), 'refresh');
		} elseif ($action == 'edit') {
			self::_validateLanguageForm($id);

			$data = $this->input->post();

			$this->language_model->edit($id, $data);
			redirect(base_url('admin/language'), 'refresh');
		} elseif ($action == 'delete') {
			$this->language_model->delete($id);
			redirect(base_url('admin/language'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('language');
		$data['action_add'] 	= base_url('admin/language_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_languages');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/language_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/language/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function language_form($action = NULL, $id = NULL) {
		if ($action == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('language_add');
			$data['action'] 						= base_url('admin/language/add');
		} elseif ($action == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('language_edit');
			$data['action'] 						= base_url('admin/language/edit/' . (int)$id);

			$data['id'] 							= (int)$id;
			$info 									= $this->language_model->get($id);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'code',
			'label'		=> _l('code'),
			'required'	=> true,
			'value'		=> $info['code'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_languages() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->language_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'code'					=> $result['code'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateLanguageForm($id = 0) {

	}

	public function translation($action = NULL, $id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'text',
			'date_modified',
			'actions',
		];

		if ($action == 'add') {
			self::_validateTranslationForm();

			$data = $this->input->post();

			$data['translations'] = is_array($data['translations']) ? json_encode($data['translations']) : $data['translations'];

			$this->translation_model->add($data);
			redirect(base_url('admin/translation'), 'refresh');
		} elseif ($action == 'edit') {
			self::_validateTranslationForm($id);

			$data = $this->input->post();

			$data['translations'] = is_array($data['translations']) ? json_encode($data['translations']) : $data['translations'];

			$this->translation_model->edit($id, $data);

			// Remove cache
			_alu($data['text']);

			redirect(base_url('admin/translation'), 'refresh');
		} elseif ($action == 'delete') {
			$this->translation_model->delete($id);
			redirect(base_url('admin/translation'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('translation');
		$data['action_add'] 	= base_url('admin/translation_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_translations');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/translation_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/translation/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function translation_form($action = NULL, $id = NULL) {
		if ($action == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('translation_add');
			$data['action'] 						= base_url('admin/translation/add');
		} elseif ($action == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('translation_edit');
			$data['action'] 						= base_url('admin/translation/edit/' . (int)$id);

			$data['id'] 							= (int)$id;
			$info 									= $this->translation_model->get($id);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'text',
			'label'		=> _l('phrase'),
			'required'	=> true,
			'value'		=> $info['text'] ?? '',
		];

		$info['translations'] = !empty($info['translations']) ? json_decode($info['translations'], true) : [];

		foreach ($this->language_model->get_all()['rows'] ?? [] as $item) {
			$data['fields'][] = [
				'type'		=> 'text',
				'key'		=> sprintf('translations[%s]', $item['code']),
				'label'		=> $item['name'],
				'required'	=> true,
				'value'		=> $info['translations'][$item['code']] ?? '',
			];
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_translations() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->translation_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'text'					=> $result['text'],
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateTranslationForm($id = 0) {

	}
}
