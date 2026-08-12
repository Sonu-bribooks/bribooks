<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSUser {
	public function bs_user($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'email',
			'mobile',
			'grade',
			'age',
			'city',
			'subscription',
			'amount',
			'status',
			'date_added',
			'actions',
		];

		if ($param1 == 'edit') {
			$data = $this->input->post();

			self::_checkDuplicateUser($data, $param2);

			$this->bs_user_model->edit($param2, $data);
			redirect(base_url('admin/bs_user'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->bs_user_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/bs_user'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_user_model->delete($param2);
			redirect(base_url('admin/bs_user'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('bs_user');
		$data['action_ajax'] 	= base_url('admin/ajax_bs_user');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_user_form/edit/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function bs_user_form($param1 = NULL, $param2 = NULL) {
		$this->load->model('brisharks/school/BSSite_model', 'bs_site_model');
		$this->load->model('brisharks/localisation/BSCountry_model', 'bs_country_model');
		$this->load->model('brisharks/localisation/BSState_model', 'bs_state_model');
		$this->load->model('brisharks/localisation/BSCity_model', 'bs_city_model');

		if ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('bs_user_form_edit');
			$data['action'] 						= base_url('admin/bs_user/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_user_model->get($param2);

			$site_info 								= $this->bs_site_model->get($info['site_id']);
			$country_info 							= $this->bs_country_model->get($info['country_id']);
			$state_info 							= $this->bs_state_model->get($info['state_id']);
			$city_info 								= $this->bs_city_model->get($info['city_id']);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'first_name',
			'label'		=> _l('first_name'),
			'required'	=> false,
			'value'		=> $info['first_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'last_name',
			'label'		=> _l('last_name'),
			'required'	=> false,
			'value'		=> $info['last_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'email',
			'label'		=> _l('email'),
			'required'	=> false,
			'value'		=> $info['email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'mobile',
			'label'		=> _l('mobile'),
			'required'	=> false,
			'value'		=> $info['mobile'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'grade',
			'label'		=> _l('grade'),
			'required'	=> false,
			'value'		=> $info['grade'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'age',
			'label'		=> _l('age'),
			'required'	=> false,
			'value'		=> $info['age'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'site_id',
			'label'		=> _l('school'),
			'required'	=> true,
			'value'		=> [
				'value' => $site_info['id'] ?? '',
				'label' => $site_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_bs_site'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_id',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> [
				'value' => $country_info['id'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_bs_country'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'state_id',
			'label'		=> _l('state'),
			'required'	=> true,
			'value'		=> [
				'value' => $state_info['id'] ?? '',
				'label' => $state_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_bs_state'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'city_id',
			'label'		=> _l('city'),
			'required'	=> true,
			'value'		=> [
				'value' => $city_info['id'] ?? '',
				'label' => $city_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_bs_city'),
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_bs_user() {
		$this->load->model('brisharks/localisation/BSCity_model', 'bs_city_model');

		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->bs_user_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$city_info = $this->bs_city_model->get($result['city_id']);

			$payment_info = $this->bs_subscription_payment_model->get_all([
				'user_id' 	=> $result['id'],
				'status' 	=> 1,
			])['rows'][0] ?? [];


			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],

				'name'				=> sprintf('%s %s', $result['first_name'], $result['last_name']),

				'email'			  	=> $result['email'],
				'mobile'			=> $result['mobile'],


				'grade'			 	=> $result['grade'],
				'age'			   	=> $result['age'],
				'city'			  	=> $city_info['name'] ?? '',

				'subscription'		=> $result['subscription_plan_id'] == 0 ? _l('pending') : _l('success'),

				'amount'			=> $payment_info['amount'] ?? 0,

				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _checkDuplicateUser($data = [], $id = 0): void {
		$checks = [
			'email'  => _l('email_is_already_exist'),
			'mobile' => _l('mobile_number_is_already_exist'),
		];

		foreach ($checks as $field => $message) {

			if (empty($data[$field])) continue;

			$user = $this->bs_user_model->get_all([
				$field => $data[$field],
			])['rows'][0] ?? [];

			if (empty($user)) continue;

			if (empty($id) || ($user['id'] != $id)) {

				$this->session->unset_userdata([
					'flash_message',
					'error_message',
				]);

				$this->session->set_flashdata('error_message', $message);

				redirect(
					!empty($id)
						? base_url('admin/bs_user_form/edit/' . $id)
						: base_url('admin/bs_user'),
					'refresh'
				);
				return;
			}
		}
	}
}
