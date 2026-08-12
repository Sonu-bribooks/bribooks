<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait PortalLogin {
	public function sendPortalOtp() {
		$json = [];

		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('portal_code', _l('portal_code'), [
			'trim',
			'required',
			['portal_code', [$this->validate_model, 'portalCode']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!($row  = $this->db->get_where('users', [
			'email'		=> $this->input->post('email'),
			'role_id'	=> 9
		])->row_array())) {
			$json['error'] = _l('error_unauthorized');
		}

		if (!$json) {
			// Hit the sms Api
			$exclude = ['9599910278', '9818651520'];

			// Config set for global sms gateway
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($row['site_id'] ?? 0);

			if (in_array($this->input->post('mobile'), $exclude)) {
				$otp = 333333;
			} else {
				$otp = mt_rand(100000, 999999);
			}

			// !in_array($this->input->post('mobile'), $exclude) && $this->alert_model->sms(
			// 	$this->input->post('mobile'),
			// 	str_replace('{otp}', $otp, get_settings('sms_otp'))
			// );

			!in_array($this->input->post('email'), TESTING_EMAILS) && $this->alert_model->validationOtp(
				$this->input->post('email'),
				_l('validation_code'),
				$otp
			);

			$this->otp_model->add([
				'mobile'		=> $this->input->post('email'),
				'otp'			=> $otp,
			]);

			$json['error'] 		= $this->session->flashdata('error_message');
			$json['success'] 	= $this->session->flashdata('flash_message');

			$json['success'] 	= _l('validation_code_sent_to_ur_email');

			$json['error'] 		= empty($json['error']) ? false : $json['error'];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function validatePortalOtp() {
		$json = [];

		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[8]|max_length[30]');
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('otp', _l('validation_code'), 'trim|required|numeric|exact_length[6]');
		$this->form_validation->set_rules('portal_code', _l('portal_code'), [
			'trim',
			'required',
			['portal_code', [$this->validate_model, 'portalCode']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		$login_data = [
			'email'		=> $this->input->post('email'),
			'role_id'	=> 9
		];

		if (
			$this->input->post('portal_code')
			&& ($site_info = $this->site_model->getByCode($this->input->post('portal_code')))
		) {
			$login_data['site_id'] = $site_info['id'];
		}

		if (!($row  = $this->db->get_where('users', $login_data)->row_array())) {
			$json['error'] = _l('error_unauthorized');
		}

		if (!$json) {
			// Config set for global sms gateway
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($row['site_id'] ?? 0);

			if ($this->otp_model->get([
				'mobile'		=> $this->input->post('email'),
				'otp'			=> $this->input->post('otp'),
			])) {
				$this->otp_model->edit([
					'mobile'		=> $this->input->post('email'),
					'otp'			=> $this->input->post('otp'),
				]);

				$this->db->order_by('date_added', 'DESC');
				$query = $this->db->get_where('users', [
					'email'			=> $this->input->post('email'),
					'role_id'		=> 9,
					'site_id'		=> $row['site_id'] ?? 0,
				]);

				if ($row = $query->row()) {
					$this->session->set_userdata('user_id', $row->id);
					$this->session->set_userdata('role_id', $row->role_id);
					$this->session->set_userdata('role', get_user_role('user_role', $row->id));
					$this->session->set_userdata('name', $row->first_name.' '.$row->last_name);
					$this->session->set_userdata('additional_role_id', $row->additional_role_id);
					$this->session->set_userdata('user_email', $row->email);

					$this->session->set_userdata('user_site', $row->site_id ?? 0);
					$this->session->set_userdata('portal_site', $row->site_id ?? 0);

					if ($row->role_id == 9) {
						$json['success'] = _l('email_verified');

						$this->session->set_userdata('portal_login', '1');

						$json['redirect'] = site_url('portal');
					} else {
						$json['error'] = _l('error_unauthorized');
					}
				} else {
					$json['error'] = _l('error_unauthorized');
				}
			} else {
				$json['error'] = _l('your_validation_code_is_expired_or_invalid');
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function enrol() {
		$json = [];

		$this->load->library('form_validation');

		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
		// $this->form_validation->set_rules('email', _l('email'), 'trim|valid_email|required|min_length[3]|max_length[40]|is_unique[users.email]');
		// $this->form_validation->set_rules('parent_mobile', _l('parent_mobile'), 'trim|required|exact_length[10]|is_unique[users.mobile]');
		$this->form_validation->set_rules('parent_mobile', _l('parent_mobile'), 'trim|numeric|required|exact_length[10]');
		// $this->form_validation->set_rules('mode', _l('mode'), 'trim|required|in_list[online,offline]');
		$this->form_validation->set_rules('price', _l('price'), 'trim|required');

		$valid = $this->form_validation->run();
		$course_level = html_escape($this->input->post('course_level'));
		if($course_level != "other") {
			$plan = explode(",", $this->input->post('price'));
			// echo "<pre>".$course_level;print_r($plan);die;
		}
		if (!$valid) {
			$this->session->set_flashdata('error_message', validation_errors());
		} else if($course_level != "other" && !is_numeric($plan[1])) {
			$this->session->set_flashdata('error_message', "Price format is not supported");
		} else {

			$data['name'] = html_escape($this->input->post('name'));
			$data['parent_name']  = html_escape($this->input->post('parent_name'));
			$email  = html_escape($this->input->post('email'));
			$data['parent_mobile']  = html_escape($this->input->post('parent_mobile'));
			// $data['mode']  = html_escape($this->input->post('mode'));
			$data['course']  = html_escape($this->input->post('course'));
			$other_price  = html_escape($this->input->post('other_price'));
			$data['price'] = $course_level != "other" ? $plan[1] : $other_price;
			$payment_id  = html_escape($this->input->post('payment_id'));
			$data['order_id'] = $this->lead_model->generateOrderId($payment_id, $data['price']);

			$mode = html_escape($this->input->post('mode'));

			$order_data['lead_id'] = html_escape($this->input->post('lead_id'));
			$order_data['course_id'] = html_escape($this->input->post('course_id'));
			$order_data['extra'] = $data['order_id'];
			$order_data['emi_type'] = $course_level != "other" ? $course_level."_".$mode."_".$plan[0] : "other";
			$order_data['amount'] = $data['price'];

			$verification_code =  md5(rand(100000000, 200000000));
			$data['verification_code'] = $verification_code;

			if (get_settings('student_email_verification') == 'enable') {
				$data['status'] = 0;
			} else {
				$data['status'] = 1;
			}

			$data['wishlist'] = json_encode(array());
			$data['watch_history'] = json_encode(array());
			$data['date_added'] = strtotime(date("Y-m-d H:i:s"));
			$social_links = array(
				'facebook' => "",
				'twitter'  => "",
				'linkedin' => ""
			);
			$data['social_links'] = json_encode($social_links);
			$data['role_id']  = 2;

			// Add paypal keys
			$paypal_info = array();
			$paypal['production_client_id'] = "";
			array_push($paypal_info, $paypal);
			$data['paypal_keys'] = json_encode($paypal_info);
			// Add Stripe keys
			$stripe_info = array();
			$stripe_keys = array(
				'public_live_key' => "",
				'secret_live_key' => ""
			);

			array_push($stripe_info, $stripe_keys);

			$data['stripe_keys'] = json_encode($stripe_info);

			$validity = true;

			if ($validity) {
				$res_order = $this->order_model->add($order_data);

				$json['order'] = [
					'name'			=> $data['course'],
					'description'	=> '',
					'image'			=> site_url('uploads/system/logo-dark.png'),
					'amount'		=> $data['price'],
					'order_id'		=> $data['order_id'],
					'key'			=> 'rzp_test_478eNBYxz13rBj',
					'id'			=> $res_order,
					'course'		=> $data['course'],
					'course_id'		=> $order_data['course_id'],
					'lead_id'		=> $order_data['lead_id'],
					'emi_type'		=> $order_data['emi_type']

				];
				$json['order']['user'] = [
					'name'			=>	$data['name'],
					'email'			=>	$email,
					'mobile'		=>	$data['parent_mobile'],
					'mode'			=>  $mode
				];
				$this->session->set_userdata('order', $json['order']);

				// echo "<pre>";print_r($this->session->userdata['order']);die;

				$json['redirect'] = '';

			} else {
				$this->session->set_flashdata('error_message', _l('email_duplication'));
			}
		}

		$json['error'] = $this->session->flashdata('error_message');
		$json['success'] = $this->session->flashdata('flash_message');

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
