<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Site {
	public function sites($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$site_code = $this->input->get('site_code');

		if ($param1 == 'add') {
			$school_email_info = $this->site_model->getSiteByWhere([
				'owner_email'		=> $this->input->post('owner_email'),
			]) ?? '';

			$school_mobile_info = $this->site_model->getSiteByWhere([
				'owner_mobile'		=> $this->input->post('owner_mobile'),
			]) ?? '';

			$user_email_data = $this->user_model->get_all([
				'email'		=> $this->input->post('owner_email'),
			])['rows'][0] ?? [];

			$user_mobile_data = $this->user_model->get_all([
				'mobile'		=> $this->input->post('owner_mobile'),
			])['rows'][0] ?? [];

			$insert_error_message = '';
			if (!empty($school_email_info)) {
				$insert_error_message = _l('email_is_already_exist');
			} else if (!empty($user_email_data)) {
				$insert_error_message = _l('email_is_already_exist_as_user');
			} else if (!empty($school_mobile_info)) {
				$insert_error_message = _l('mobile_number_is_already_exist');
			} else if (!empty($user_mobile_data)) {
				$insert_error_message = _l('mobile_number_is_already_exist_as_user');
			}

			unset($_SESSION['flash_message']);
			unset($_SESSION['error_message']);

			if (empty($insert_error_message)) {
				if ($this->input->post('parent_id') && ($parent_site_info = $this->site_model->get($this->input->post('parent_id')))) {

					$last_row_data = $this->db->select('id')->order_by('id', 'desc')->limit(1)->get('site')->row();
					$site_code = !empty($last_row_data) ? $parent_site_info['site_code'] . '-' . ($last_row_data->id + 1) : $parent_site_info['site_code'] . '-' . uniqid();

					$discount_code 			= $parent_site_info['discount_code'];
					$discount_percentage 	= $parent_site_info['discount_percentage'];
				} else {
					$site_code 				= $this->input->post('site_code') ?? uniqid();
					$discount_code 			= $this->input->post('discount_code');
					$discount_percentage 	= $this->input->post('discount_percentage');
				}

				if (empty($discount_code)) {
					$discount_code = $this->input->post('discount_code');
				}

				if (empty($discount_percentage)) {
					$discount_percentage = $this->input->post('discount_percentage');
				}

				$insert_data = [
					'parent_id'				=> (int)$this->input->post('parent_id'),
					'site_type'				=> $this->input->post('site_type') ?? 1,
					'license_total'			=> $this->input->post('license_total') ?? 1,
					'name'					=> $this->input->post('name'),
					'image'					=> $this->input->post('site_image'),
					'payment_gateway'		=> $this->input->post('payment_gateway'),
					'sms_gateway'			=> $this->input->post('sms_gateway'),
					'email_alert'			=> $this->input->post('email_alert'),
					'address'				=> $this->input->post('address'),
					'pincode'				=> $this->input->post('pincode'),
					'mobile_length'			=> (int)$this->input->post('mobile_length'),
					'country_code'			=> $this->input->post('country_code'),
					'state_id'				=> $this->input->post('state_id'),
					'city_id'				=> $this->input->post('city_id'),
					'site_code'				=> $site_code,
					'discount_code'			=> $discount_code,
					'discount_percentage' 	=> $discount_percentage,
					'currency_code'			=> $this->input->post('currency_code'),
					'base_price'			=> $this->input->post('base_price'),
					'ebook_price'			=> $this->input->post('ebook_price'),
					'price_per_page'		=> $this->input->post('price_per_page'),
					'free_page_limit'		=> $this->input->post('free_page_limit'),
					'hard_cover_price'		=> $this->input->post('hard_cover_price'),
					'black_white_price_per_page'=> $this->input->post('black_white_price_per_page'),
					'tax'					=> $this->input->post('tax'),
					'tax_text'				=> $this->input->post('tax_text'),
					'timezone'				=> $this->input->post('timezone'),
					'owner_name'			=> $this->input->post('owner_name'),
					'authorized_person'		=> $this->input->post('authorized_person'),
					'owner_email'			=> $this->input->post('owner_email'),
					'owner_mobile'			=> $this->input->post('owner_mobile'),
					'can_add_site'			=> (int)$this->input->post('can_add_site'),
					'status'				=> (int)$this->input->post('status'),
					'verified'				=> $this->input->post('verified'),
				];

				$site_id = $this->site_model->add($insert_data);
				self::_addSchoolUser($site_id, $insert_data);
			} else {
				$this->session->set_flashdata('error_message', $insert_error_message);
			}

			redirect(base_url('/admin/sites'), 'refresh');
		} elseif ($param1 == 'edit') {
			$school_user_info = $this->school_user_model->get_all([
				'site_id'		=> $param2,
			])['rows'][0] ?? [];

			$user_email_data = $this->db->get_where('users', [
				'email'		=> $this->input->post('owner_email'),
				'_deleted'	=> 0
			])->row_array();

			$user_mobile_data = $this->db->get_where('users', [
				'mobile'		=> $this->input->post('owner_mobile'),
				'_deleted'	=> 0
			])->row_array();

			$error_message= '';

			if (!empty($user_email_data) && empty($school_user_info)) {
				$error_message = _l('email_is_already_exist_as_user');
			} else if (!empty($user_email_data) && !empty($school_user_info) && ($user_email_data['site_id'] != $school_user_info['site_id'])) {
				$error_message = _l('email_is_already_exist');
			}

			if (!empty($user_mobile_data) && empty($school_user_info)) {
				$error_message = _l('mobile_number_is_already_exist_as_user');
			} else if (!empty($user_mobile_data) && !empty($school_user_info) && ($user_mobile_data['site_id'] != $school_user_info['site_id'])) {
				$error_message = _l('mobile_number_is_already_exist');
			}

			unset($_SESSION['flash_message']);
			unset($_SESSION['error_message']);

			if (empty($error_message)) {

				$site_info = $this->site_model->get($param2);

				if ($this->input->post('parent_id') && ($parent_site_info = $this->site_model->get($this->input->post('parent_id')))) {

					$site_code = $parent_site_info['site_code'] . '-' . $param2;

					$discount_code 			= $parent_site_info['discount_code'];
					$discount_percentage 	= $parent_site_info['discount_percentage'];
				} else {
					$site_code 				= $this->input->post('site_code');
					$discount_code 			= $this->input->post('discount_code');
					$discount_percentage 	= $this->input->post('discount_percentage');
				}

				if (!empty($this->input->post('site_code'))) {
					$site_code = $this->input->post('site_code');
				}

				if (empty($discount_code)) {
					$discount_code = $this->input->post('discount_code');
				}

				if (empty($discount_percentage)) {
					$discount_percentage = $this->input->post('discount_percentage');
				}

				$update_data = [
					'parent_id'				=> (int)$this->input->post('parent_id') ?? $site_info['parent_id'],
					'site_type'				=> $this->input->post('site_type') ?? $site_info['site_type'],
					'license_total'			=> $this->input->post('license_total') ?? $site_info['license_total'],
					'name'					=> $this->input->post('name') ?? $site_info['name'],
					'image'					=> $this->input->post('site_image') ??  $site_info['image'],
					'payment_gateway'		=> $this->input->post('payment_gateway') ?? $site_info['payment_gateway'],
					'sms_gateway'			=> $this->input->post('sms_gateway')  ?? $site_info['sms_gateway'],
					'email_alert'			=> $this->input->post('email_alert') ?? $site_info['email_alert'],
					'address'				=> $this->input->post('address')  ?? $site_info['address'],
					'pincode'				=> $this->input->post('pincode') ?? $site_info['pincode'],
					'mobile_length'			=> (int)$this->input->post('mobile_length') ?? $site_info['mobile_length'],
					'country_code'			=> $this->input->post('country_code') ?? $site_info['country_code'],
					'state_id'				=> $this->input->post('state_id') ??  $site_info['state_id'],
					'city_id'				=> $this->input->post('city_id') ??  $site_info['city_id'],
					'site_code'				=> $site_code,
					'discount_code'			=> $discount_code,
					'discount_percentage' 	=> $discount_percentage,
					'currency_code'			=> $this->input->post('currency_code') ?? $site_info['currency_code'],
					'base_price'			=> $this->input->post('base_price') ?? $site_info['base_price'],
					'ebook_price'			=> $this->input->post('ebook_price') ??  $site_info['ebook_price'],
					'price_per_page'		=> $this->input->post('price_per_page') ?? $site_info['price_per_page'],
					'free_page_limit'		=> $this->input->post('free_page_limit') ?? $site_info['free_page_limit'],
					'hard_cover_price'		=> $this->input->post('hard_cover_price') ?? $site_info['hard_cover_price'],
					'black_white_price_per_page'=> $this->input->post('black_white_price_per_page') ?? $site_info['black_white_price_per_page'],
					'tax'					=> $this->input->post('tax') ?? $site_info['tax'],
					'tax_text'				=> $this->input->post('tax_text') ?? $site_info['tax_text'],
					'timezone'				=> $this->input->post('timezone') ?? $site_info[''],
					'owner_name'			=> $this->input->post('owner_name') ??  $site_info['timezone'],
					'authorized_person'		=> $this->input->post('authorized_person') ??  $site_info['authorized_person'],
					'owner_email'			=> $this->input->post('owner_email') ?? $site_info['owner_email'],
					'owner_mobile'			=> $this->input->post('owner_mobile') ?? $site_info['owner_mobile'],
					'can_add_site'			=> (int)$this->input->post('can_add_site') ?? $site_info['can_add_site'],
					'status'				=> (int)$this->input->post('status') ?? $site_info['status'],
					'verified'				=> $this->input->post('verified'),
				];

				$this->site_model->editById($param2, $update_data);

				self::_addSchoolUser($param2, $update_data);
				$this->session->set_flashdata('flash_message', _l('site_updated_successfully'));
			} else {
				$this->session->set_flashdata('error_message', $error_message);
			}
			redirect(base_url('admin/sites'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->site_model->delete($param2);
			redirect(base_url('admin/sites'), 'refresh');
		}

		$data['page_name'] 		= 'site/index';
		$data['page_title'] 	= _l('site');
		$data['sites'] 			= [];
		$data['action_add'] 	= base_url('admin/site_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_site/' . $site_code);
		$this->load->view('backend/index', $data);
	}

	private function _addSchoolUser($site_id, $data = []) {
		$user_id = 0;

		if ($row = $this->db->get_where('users', [
			'site_id'		=> $site_id,
			'role_id'		=> 9
		])->row_array()) {
			$update_data = [
				'first_name'		=> $data['name'],
				'email'				=> $data['owner_email'],
				'mobile'			=> $data['owner_mobile'],
				'email_verified'	=> !empty($data['owner_email']) ? 1 : 0,
				'mobile_verified'	=> !empty($data['owner_mobile']) ? 1 : 0,
				'role_id'			=> 9,
				'site_id'			=> (int)$site_id,
				'date_modified'		=> date('Y-m-d H:i:s'),
				'status'			=> (int)$data['status'],
			];

			if (!empty($data['owner_password'])) {
				$update_data['password'] = sha1($data['owner_password']);
			}

			$this->db->update('users', $update_data, [
				'id' => (int)$row['id']
			]);

			$user_id = $row['id'];
		} else {
			$this->db->insert('users', [
				'first_name'		=> $data['name'],
				'password'			=> sha1($data['owner_password']),
				'email'				=> $data['owner_email'],
				'mobile'			=> $data['owner_mobile'],
				'email_verified'	=> !empty($data['owner_email']) ? 1 : 0,
				'mobile_verified'	=> !empty($data['owner_mobile']) ? 1 : 0,
				'role_id'			=> 9,
				'site_id'			=> (int)$site_id,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
				'status'			=> (int)$data['status'],
				'state_id'			=> $data['state_id'] ?? 0,
				'city_id'			=> $data['city_id'] ?? 0
			]);

			$user_id = $this->db->insert_id();
		}
	}

	public function site_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'site/form';
		$data['site_types']		= $this->site_type_model->get_all()['rows'] ?? [];

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('site_add');
			$data['event_details'] 	= $this->event_model->get_all()['rows'] ?? [];
			$data['action'] 		= base_url('admin/sites/add');
		} elseif ($param1 == 'edit') {
			$data['site_id'] 		= $param2;
			$data['action'] 		= base_url('admin/sites/edit/' . (int)$param2);
			$data['details'] 		= $this->site_model->get($param2);
			$data['event_details'] 	= $this->event_model->get_all()['rows'] ?? [];
			$data['page_title'] 	= _l('site_edit');
		} elseif ($param1 == 'update_site') {
			$data['page_name'] 		= 'site/update_form';
			$data['site_id'] 		= $param2;
			$data['action'] 		= base_url('admin/sites/update_site/' . (int)$param2);
			$data['details'] 		= $this->site_model->get($param2);
			$data['event_details'] 	= $this->event_model->get_all()['rows'] ?? [];
			$data['page_title'] 	= _l('site_update');
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_site($site_code = '') {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($site_code) {
			$filter_data['site_code'] = $site_code;
		}

		$results = $this->site_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = !empty($result['event_id'])
				? $this->event_model->get($result['event_id'])
				: [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				// 'id'					=> $result['id'] . _sd($result['testing'] ^ 1),
				'name'					=> $result['name'],
				'total'					=> $this->student_model->get_all(['site_id' => $result['id']])['total'] ?? 0,
				'country_code'			=> $result['country_code'],
				'site_code'				=> $result['site_code'],
				'owner_details'			=> vsprintf('%s<br /><a href="tel:+%s">%s</a>', [
					$result['owner_email'],
					$result['owner_mobile'],
					$result['owner_mobile'],
				]),
				'verified'				=> _sd($result['verified']),
				'date_added'			=> formatDate($result['date_added']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_site() {
		$json = [];

		if ($this->input->get('search')) {
			$json['items'] = array_map(function($item) {
				return [
					'id'	=> $item['id'],
					'text'	=> $item['name'],
				];
			}, $this->site_model->get_all([
				'search'	=> $this->input->get('search'),
				'status'	=> 1,
			])['rows'] ?? []);
		}

		output_json($json);
	}

	public function ajax_search_sites() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->site_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s, %s)', $result['name'], $result['id'], $result['owner_email']),
			];
		}

		output_json($json);
	}

	public function ajax_search_schools() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->school_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s, %s)', $result['name'], $result['id'], $result['owner_email']),
			];
		}

		output_json($json);
	}

	public function ajax_site_update() {
		$json = [];

		if ($this->input->post()) {

			$site_info = $this->site_model->get($this->input->post('site_id'));

			if (empty($site_info)) {
				$json['error'] = _l('school_id_not_found');
				return output_json($json);
			}

			$site_email_info = $this->site_model->get_all([
				'owner_email' => trim($this->input->post('owner_email'))
			])['rows'][0] ?? '';

			if (!empty(trim($this->input->post('owner_email'))) && !empty($site_info) && !empty($site_email_info) && $site_info['id'] != $site_email_info['id']) {
				$json['error'] = _l('email_is_already_exist_for_this_school_:_ ') . $site_email_info['name'] . ' - (' . $site_email_info['id'] . ')';
				return output_json($json);
			}

			$site_mobile_info = $this->site_model->get_all([
				'owner_mobile' => trim($this->input->post('owner_mobile'))
			])['rows'][0] ?? '';

			if (!empty(trim($this->input->post('owner_mobile'))) && !empty($site_info) && !empty($site_mobile_info) && $site_info['id'] != $site_mobile_info['id']) {
				$json['error'] = _l('mobile_is_already_exist_for_this_school_:_ ') . $site_mobile_info['name'] . ' - (' . $site_mobile_info['id'] . ')';
				return output_json($json);
			}

			$school_user_info = $this->school_user_model->get_all([
				'site_id'		=> $this->input->post('site_id'),
			])['rows'][0] ?? [];

			if (!empty(trim($this->input->post('owner_email')))) {
				$user_email_data = $this->db->get_where('users', [
					'email'		=> trim($this->input->post('owner_email')),
					'_deleted'	=> 0
				])->row_array();

				if (!empty($user_email_data) && empty($school_user_info)) {
					$json['error'] = _l('email_is_already_exist_for_this_user_:_ ') . ucwords($user_email_data['first_name'] . ' ' . $user_email_data['last_name']) . ' - (' . $user_email_data['id'] . ')';
					return output_json($json);
				} else if (!empty($user_email_data) && !empty($school_user_info) && ($user_email_data['site_id'] != $school_user_info['site_id'])) {
					$json['error'] = _l('email_is_already_exist_for_this_user_:_ ') . ucwords($user_email_data['first_name'] . ' ' . $user_email_data['last_name']) . ' - (' . $user_email_data['id'] . ')';
					return output_json($json);
				}
			}

			if (!empty(trim($this->input->post('owner_mobile')))) {
				$user_mobile_data = $this->db->get_where('users', [
					'mobile'	=> trim($this->input->post('owner_mobile')),
					'_deleted'	=> 0
				])->row_array();

				if (!empty($user_mobile_data) && empty($school_user_info)) {
					$json['error'] = _l('mobile_number_is_already_exist_for_this_user_:_ ') . ucwords($user_mobile_data['first_name'] . ' ' . $user_mobile_data['last_name']) . ' - (' . $user_mobile_data['id'] . ')';
					return output_json($json);
				} else if (!empty($user_mobile_data) && !empty($school_user_info) && ($user_mobile_data['site_id'] != $school_user_info['site_id'])) {
					$json['error'] = _l('mobile_number_is_already_exist_for_this_user_:_ ') . ucwords($user_mobile_data['first_name'] . ' ' . $user_mobile_data['last_name']) . ' - (' . $user_mobile_data['id'] . ')';
					return output_json($json);
				}
			}

			$this->site_model->editById($site_info['id'], [
				'country_code' 		=> $this->input->post('country_code') 				?? $site_info['country_code'],
				'state_id' 			=> $this->input->post('state_id') 					?? $site_info['state_id'],
				'city_id' 			=> $this->input->post('city_id') 					?? $site_info['city_id'],
				'currency_code' 	=> $this->input->post('currency_code') 				?? $site_info['currency_code'],
				'site_type' 		=> $this->input->post('site_type') 					?? $site_info['site_type'],
				'name' 				=> trim($this->input->post('name')) 				?? $site_info['name'],
				'owner_email' 		=> trim($this->input->post('owner_email'))			?? $site_info['owner_email'],
				'owner_mobile' 		=> trim($this->input->post('owner_mobile')) 		?? $site_info['owner_mobile'],
				'owner_name' 		=> trim($this->input->post('owner_name'))			?? $site_info['owner_name'],
				'authorized_person' => trim($this->input->post('authorized_person'))	?? $site_info['authorized_person']
			]);

			$this->school_model->editBySite($site_info['id'], [
				'country_code' 		=> $this->input->post('country_code') 				?? $site_info['country_code'],
				'state_id' 			=> $this->input->post('state_id') 					?? $site_info['state_id'],
				'city_id' 			=> $this->input->post('city_id') 					?? $site_info['city_id'],
				'currency_code' 	=> $this->input->post('currency_code') 				?? $site_info['currency_code'],
				'site_type' 		=> $this->input->post('site_type') 					?? $site_info['site_type'],
				'name' 				=> trim($this->input->post('name')) 				?? $site_info['name'],
				'owner_email' 		=> trim($this->input->post('owner_email'))			?? $site_info['owner_email'],
				'owner_mobile' 		=> trim($this->input->post('owner_mobile')) 		?? $site_info['owner_mobile'],
				'owner_name' 		=> trim($this->input->post('owner_name'))			?? $site_info['owner_name'],
				'authorized_person' => trim($this->input->post('authorized_person'))	?? $site_info['authorized_person']
			]);

			// UPDATE SCHOOL USER DETAILS WHENEVER EMAIL OR MOBILE CHANGED..
			if (!empty($school_user_info)) {
				$this->school_user_model->edit($school_user_info['id'],[
					'first_name' 	=> trim($this->input->post('name')) 				?? $site_info['name'],
					'email' 		=> trim($this->input->post('owner_email'))			?? $site_info['owner_email'],
					'mobile' 		=> trim($this->input->post('owner_mobile')) 		?? $site_info['owner_mobile'],
				]);
			}

			$json['success'] = _l('school_updated_successfullly!');

		} else {
			$json['error'] = _l('something_went_wrong!');
		}
		output_json($json);
	}
}
