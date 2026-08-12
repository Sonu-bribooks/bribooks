<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Medallion {
	public function getMedallionAddress() {
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('oid', _l('medallion_order_code'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_info = $this->student_model->get_all([
				'user_id' 			=> (int)$this->input->post('uid'),
				'verification_code' => $this->input->post('code')
			])['rows'][0] ?? [])) {
				$this->json['error'] = _l('invalid_user');
				return;
			}

			if (empty($order_info = $this->medallion_order_model->getByCode($this->input->post('oid')))) {
				$this->json['error'] = _l('invalid_medallion_order_code');
				return;
			}

			$address_info = $this->medallion_address_model->get_all([
				'user_id'	=> (int)$this->input->post('uid')
			])['rows'][0] ?? [];

			if (empty($address_info)) {
				$address_info = !empty($user_info['address_id'])
					? $this->address_model->get($user_info['address_id'])
					: [
						'name'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
						'mobile'	=> $user_info['mobile'],
						'country'	=> $user_info['location'],
					]
				;
				$this->json['locked'] = false;
			} else {
				$this->json['locked'] = true;
			}

			$this->json['confirmed'] = $order_info['status'] != 1;

			$this->json['address'] = $address_info;
			$this->json['heading'] = sprintf('%s for %s', $order_info['medallion_name'], $order_info['book_name']);
		}
	}

	public function updateMedallionAddress() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[4]|max_length[128]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[10]|max_length[15]',[
			'min_length' => 'Please enter a valid 10 digit mobile number',
			'max_length' => 'Please enter a valid 15 digit mobile number'
		]);

		if (mb_strtolower($this->input->post('country')) !== 'india') {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|min_length[4]|max_length[10]');
		} else {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|numeric|exact_length[6]');
		}

		$this->form_validation->set_rules('address', _l('address'), 'trim|required|min_length[4]|max_length[255]');
		$this->form_validation->set_rules('city', _l('city'), 'trim|required|min_length[2]|max_length[128]');
		$this->form_validation->set_rules('country', _l('country'), 'trim|required|min_length[2]|max_length[255]');
		$this->form_validation->set_rules('state', _l('state'), 'trim|required|min_length[2]|max_length[255]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[Office,Other,Home]');

		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('oid', _l('medallion_order_code'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_info = $this->student_model->get_all([
				'user_id' 			=> (int)$this->input->post('uid'),
				'verification_code' => $this->input->post('code')
			])['rows'][0] ?? [])) {
				$this->json['error'] = _l('invalid_user');
				return;
			}

			if (empty($order_info = $this->medallion_order_model->getByCode($this->input->post('oid')))) {
				$this->json['error'] = _l('invalid_medallion_order_code');
				return;
			}

			if ($order_info['status'] != 1) {
				$this->json['error'] = _li('Your Medallion Order has already been confirmed.');
				return;
			}

			if (!empty($address_info = $this->medallion_address_model->get_all([
				'user_id'	=> (int)$this->input->post('uid')
			])['rows'][0] ?? [])) {
				// $this->json['error'] = _l('can\'t_update_medallion_address');
				// return;

				$address_id = $address_info['id'];
			} else {
				$address_id = $this->medallion_address_model->add([
					'user_id'	=> (int)$user_info['id'],
					'name'		=> $this->input->post('name'),
					'mobile'	=> $this->input->post('mobile'),
					'zipcode'	=> $this->input->post('zipcode'),
					'address'	=> $this->input->post('address'),
					'landmark'	=> $this->input->post('landmark'),
					'city'		=> $this->input->post('city'),
					'country'	=> $this->input->post('country'),
					'state'		=> $this->input->post('state'),
					'type'		=> $this->input->post('type'),
				]);
			}

			CI_Events::trigger('access_log', [
				'module'	=> 'update_medallion_address_' . (int)$address_id
			]);

			self::_confirmMedallionOrders($user_info['id'], $address_id);

			$this->json['success'] = _li('Your Medallion Order has been successfully confirmed.');
		}
	}

	private function _confirmMedallionOrders($user_id = 0, $address_id = 0) {
		$orders = $this->medallion_order_model->get_all([
			'user_id'			=> $user_id,
			'shipping_status'	=> 0,
			'ne_status'			=> [0, 4, 15, 91, 92, 93],
			'sort'				=> 'medallion_order.id',
			'order'				=> 'ASC',
		])['rows'] ?? [];

		if (empty($orders)) return;

		$parent_id = 0;

		$parent_order = array_filter($orders, function($item) {
			return !empty($item['parent_id']);
		});

		$parent_id = $parent_order['id'] ?? $orders[0]['id'] ?? 0;

		foreach ($orders as $order) {
			$this->medallion_order_model->edit($order['id'], [
				'parent_id'		=> $parent_id == $order['id'] ? 0 : $parent_id,
				'shipping_cost'	=> $parent_id == $order['id'] ? $order['shipping_cost'] : 0,
				'total'			=> $parent_id == $order['id'] ? (double)$order['total'] : (double)$order['subtotal'],
				'status'		=> 21,
				'address_id'	=> (int)$address_id,
			]);

			if ($order['status'] == 1) {
				// reduce medallion stock
				$medallion_info = $this->medallion_model->get($order['medallion_id']);

				$this->medallion_model->edit($medallion_info['id'], [
					'quantity'	=> ($medallion_info['quantity'] - 1)
				]);

				$this->medallion_stock_log_model->add([
					'medallion_id'			=> (int)$order['medallion_id'],
					'medallion_order_id'	=> (int)$order['id'],
					'quantity'				=> $medallion_info['quantity'],
					'quantity_order'		=> 1,
				]);
			}
		}
	}

	public function getSchoolMedallionAddress() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('soid', _l('medallion_order_code'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($school_code_info = $this->event_school_invite_code_model->get_all([
				'event_id'	  	=> $this->input->post('event_id'),
				'site_id'	 	=> $this->input->post('site_id'),
				'code'		  	=> $this->input->post('code'),
			])['rows'])) {
				return $this->json['error'] = _li('invalid_code');
			}

			if (empty($site_info = $this->site_model->get($this->input->post('site_id')))) {
				return $this->json['error'] = _li('site_is_invalid');
			}

			if (empty($order_info = $this->medallion_order_model->getByCode($this->input->post('soid')))) {
				$this->json['error'] = _l('invalid_medallion_order_code');
				return;
			}

			$address_info = $this->school_medallion_address_model->get_all([
				'site_id'	=> (int)$this->input->post('site_id')
			])['rows'][0] ?? [];

			if (empty($address_info)) {
				$country_info 	= $this->country_model->get($site_info['country_id'] ?? 0);
				$state_info 	= $this->state_model->get($site_info['state_id'] ?? 0);
				$city_info 		= $this->city_model->get($site_info['city_id'] ?? 0);
				$school_info 	= $this->school_model->getBySiteID($site_info['id'] ?? 0);

				if (!empty($school_info['designation']) && in_array(strtolower($school_info['designation']), ['principal', 'director'])) {
					$leader_mobile				= $site_info['owner_mobile'];
					$leader_email				= $site_info['owner_email'];
					$leader_name				= $site_info['authorized_person'];
					$leader_designation			= $school_info['designation'];
					$coordinator_mobile			= '';
					$coordinator_email			= '';
					$coordinator_name			= '';
					$coordinator_name			= '';
				} else {
					$coordinator_mobile			= $site_info['owner_mobile'];
					$coordinator_email			= $site_info['owner_email'];
					$coordinator_name			= $site_info['authorized_person'];
					$coordinator_designation	= $school_info['designation'];
					$leader_mobile				= '';
					$leader_email				= '';
					$leader_name				= '';
				}

				$address_info = [
					'site_id'					=> (int)$site_info['id'],
					'school_name'				=> $site_info['name'],
					'coordinator_name'			=> $coordinator_name,
					'leader_name'				=> $leader_name,
					'coordinator_mobile'		=> $coordinator_mobile,
					'leader_mobile'				=> $leader_mobile,
					'coordinator_email'			=> $coordinator_email,
					'leader_email'				=> $leader_email,
					'coordinator_designation'	=> $coordinator_designation,
					'leader_designation'		=> $leader_designation,
					'zipcode'					=> $site_info['pincode'],
					'address'					=> $site_info['address'],
					'landmark'					=> $site_info['landmark'] ?? '',
					'city_id'					=> $site_info['city_id'],
					'state_id'					=> $site_info['state_id'],
					'country_id'				=> $site_info['country_id'],
					'city'						=> $city_info['name'] ?? '',
					'state'						=> $state_info['name'] ?? '',
					'country'					=> $country_info['name'] ?? '',
					'type'						=> 'school',
				];

				$this->json['locked'] = false;
			} else {
				$this->json['locked'] = true;
			}

			$this->json['confirmed'] = $order_info['status'] != 1;

			$this->json['address'] = $address_info;
			$this->json['heading'] = sprintf('%s for %s', $order_info['medallion_name'], $site_info['name']);
		}
	}

	public function updateSchoolMedallionAddress() {
		$this->form_validation->set_rules('school_name', _l('school_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('coordinator_name', _l('coordinator_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('leader_name', _l('leader_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('coordinator_mobile', _l('coordinator_mobile'), 'trim|required|min_length[10]|max_length[15]',[
			'min_length' => 'Please enter a valid 10 digit mobile number',
			'max_length' => 'Please enter a valid 15 digit mobile number'
		]);

		$this->form_validation->set_rules('leader_mobile', _l('leader_mobile'), 'trim|required|min_length[10]|max_length[15]',[
			'min_length' => 'Please enter a valid 10 digit mobile number',
			'max_length' => 'Please enter a valid 15 digit mobile number'
		]);

		$this->form_validation->set_rules('coordinator_email', _l('coordinator_email'), 'trim|required|valid_email');
		$this->form_validation->set_rules('leader_email', _l('leader_email'), 'trim|required|valid_email');

		if (mb_strtolower($this->input->post('country_id') ?? 1) !== 1) {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|min_length[3]|max_length[10]');
		} else {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|numeric|exact_length[6]');
		}

		$this->form_validation->set_rules('address', _l('address'), 'trim|required|min_length[3]|max_length[255]');
		$this->form_validation->set_rules('city_id', _l('city_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('state_id', _l('state_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('country_id', _l('country_id'), 'trim|required|numeric');

		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('soid', _l('medallion_order_code'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($school_code_info = $this->event_school_invite_code_model->get_all([
				'event_id'	  	=> $this->input->post('event_id'),
				'site_id'	 	=> $this->input->post('site_id'),
				'code'		  	=> $this->input->post('code'),
			])['rows'])) {
				$this->json['error'] = _li('invalid_code');
				return;
			}

			if (empty($site_info = $this->site_model->get($this->input->post('site_id')))) {
				$this->json['error'] = _li('site_is_invalid');
				return;
			}

			if (empty($user_info = $this->user_model->get_all([
				'site_id' 	=> $site_info['id'],
				'role_id'	=> 9
			])['rows'][0] ?? [])) {
				$this->json['error'] = _li('school_is_invalid');
				return;
			}

			if (empty($order_info = $this->medallion_order_model->getByCode($this->input->post('soid')))) {
				$this->json['error'] = _li('invalid_medallion_order_code');
				return;
			}

			if ($this->input->post('coordinator_mobile') == $this->input->post('leader_mobile')) {
				$this->json['error'] = _li('coordinator_and_leader_mobile_numbers_cannot_be_the_same');
				return;
			}

			if ($this->input->post('coordinator_email') == $this->input->post('leader_email')) {
				$this->json['error'] = _li('coordinator_and_leader_email_cannot_be_the_same');
				return;
			}

			if ($order_info['status'] != 1) {
				$this->json['error'] = _li('Your Medallion Order has already been confirmed.');
				return;
			}

			if (!empty($address_info = $this->school_medallion_address_model->get_all([
				'site_id'	=> (int)$this->input->post('site_id')
			])['rows'][0] ?? [])) {
				$address_id 			= $address_info['id'];
				$medallion_address_id 	= $address_info['address_id'];
			} else {
				$country_info 	= $this->country_model->get($this->input->post('country_id') ?? 0);
				$state_info 	= $this->state_model->get($this->input->post('state_id') ?? 0);
				$city_info 		= $this->city_model->get($this->input->post('city_id') ?? 0);

				$address_id = $this->school_medallion_address_model->add([
					'site_id'					=> (int)$site_info['id'],
					'school_name'				=> $this->input->post('school_name'),
					'coordinator_name'			=> $this->input->post('coordinator_name'),
					'leader_name'				=> $this->input->post('leader_name'),
					'coordinator_mobile'		=> $this->input->post('coordinator_mobile'),
					'leader_mobile'				=> $this->input->post('leader_mobile'),
					'coordinator_email'			=> $this->input->post('coordinator_email'),
					'leader_email'				=> $this->input->post('leader_email'),
					'coordinator_designation'	=> $this->input->post('coordinator_designation'),
					'leader_designation'		=> $this->input->post('leader_designation'),
					'zipcode'					=> $this->input->post('zipcode'),
					'address'					=> $this->input->post('address'),
					'landmark'					=> $this->input->post('landmark') ?? '',
					'city_id'					=> $this->input->post('city_id'),
					'state_id'					=> $this->input->post('state_id'),
					'country_id'				=> $this->input->post('country_id'),
					'city'						=> $city_info['name'] ?? '',
					'state'						=> $state_info['name'] ?? '',
					'country'					=> $country_info['name'] ?? '',
					'type'						=> $this->input->post('type') ?? 'school',
				]);
			}

			if ($address_id) {
				$medallion_address_id = $this->medallion_address_model->add([
					'user_id'					=> $user_info['id'],
					'name'						=> $this->input->post('school_name'),
					'mobile'					=> $site_info['owner_mobile'] ?? '',
					'zipcode'					=> $this->input->post('zipcode'),
					'address'					=> $this->input->post('address'),
					'landmark'					=> $this->input->post('landmark') ?? '',
					'city'						=> $city_info['name'] ?? '',
					'state'						=> $state_info['name'] ?? '',
					'country'					=> $country_info['name'] ?? '',
					'type'						=> $this->input->post('type') ?? 'Other',
				]);

				if ($medallion_address_id) {
					$this->school_medallion_address_model->edit($address_id, [
						'address_id' => $medallion_address_id
					]);

					CI_Events::trigger('access_log', [
						'module'	=> 'update_school_medallion_address_' . (int)$medallion_address_id
					]);

					self::_confirmMedallionOrders($user_info['id'], $medallion_address_id);
				}
			}

			$this->json['success'] = _li('Your Medallion Order has been successfully confirmed.');
		}
	}

	public function addMedallionFeedback() {
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('oid', _l('medallion_order_code'), 'trim|required');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[image,video]');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($user_info = $this->student_model->get_all([
				'user_id' 			=> (int)$this->input->post('uid'),
				'verification_code' => $this->input->post('code')
			])['rows'][0] ?? [])) {
				$this->json['error'] = _l('invalid_user');
				return;
			}

			if (empty($order_info = $this->medallion_order_model->getByCode($this->input->post('oid')))) {
				$this->json['error'] = _l('invalid_medallion_order_code');
				return;
			}

			if ($order_info['status'] != 4) {
				$this->json['error'] = _l('wait_for_medallion_order_delivery');
				return;
			}

			$this->load->model('medallion/MedallionFeedback_model', 'medallion_feedback_model');

			if (!empty($info = $this->medallion_feedback_model->get_all([
				'user_id'		=> $user_info['id'],
				'order_id'		=> $order_info['id'],
			])['rows'][0] ?? [])) {
				$this->json['error'] = _li('You\'ve already submitted. Thank you!');
				return;
			}

			if (self::_validateFileUpload('file', false, $this->input->post('type'))) {
				$filename = vsprintf('medallion_%s_%s_%s.%s', [
					uniqid(),
					$user_info['id'],
					$order_info['id'],
					$this->input->post('type') === 'image' ? 'png' : 'mp4'
				]);

				log_kb(['Medallion Feedback Upload' => $this->s3->amazonS3Upload(
					$filename,
					$_FILES['file']['tmp_name'],
					rtrim($this->config->item('s3_medallion_feedback') . (ENVIRONMENT === 'production' ? '' : 'test'), '/')
				)]);

				$id = $this->medallion_feedback_model->add([
					'event_id'		=> $order_info['event_id'],
					'medallion_id'	=> $order_info['medallion_id'],
					'user_id'		=> $user_info['id'],
					'order_id'		=> $order_info['id'],
					'file'			=> $filename,
					'type'			=> $this->input->post('type'),
				]);

				CI_Events::trigger('medallion_feedback', [
					'id'				=> $id,
				]);
				CI_Events::trigger('access_log', [
					'module'	=> sprintf('add_medallion_feedback_%s_%s', $user_info['id'], $order_info['id'])
				]);

				$this->json['success'] = _li('Submission Received, Thank you!');
			}
		}
	}
}
