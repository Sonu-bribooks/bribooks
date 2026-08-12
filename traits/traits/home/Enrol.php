<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Enrol {
	public function getCity($center) {
		return $this->lead_model->getCityByCenter($center);
	}

	public function enrolment($code) {
		$level = $learning_mode = [];
		$res = $this->lead_model->getByCode($code);

		if ($res) {
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($res['site_id']);

			$prices = [
				'base'		=> $this->config->item('site_base_plan'),
				'premium'	=> $this->config->item('site_premium_plan'),
			];

			$city_data = $this->city_model->get_all()['rows'];

			$city = $this->getCity($res['center_id']);

			$center_data = $this->center_model->get_all(array('city_id' => $city['id'] ?? 0))->result_array();

			$data = [
				'user_image'	=>	$this->user_model->get_user_image_url($res['student_id']),
				'locked'		=>	$res['locked'],
				'instalment'	=>	$res['instalment'] ?? '',
				'instalment_text'=>	_l('first_instalment'),
				'scholarship'	=>	$res['scholarship'] ?? '',
				'name'			=>	$res['name'],
				'parent_name'	=>	$res['parent_name'],
				'mobile'		=>	$res['mobile'],
				'email'			=>	$res['email'],
				'course_id'		=>	$res['course_id'],
				'class_id'		=>	$res['class_id'],
				'course'		=>  $res['course'],
				'mode'			=>	$res['mode'],
				'price'			=> 	$res['price'],
				'amount'		=> 	$res['amount'],
				'base_amount'	=> 	$prices[$res['emi_type']] ?? $res['amount'],
				'emi_type'		=> 	$res['emi_type'],
				'order_id'		=>	$this->order_model->generateOrderId($res['id'], $res['price']),
				'lead_id'		=>	$res['id'],
				'emi'			=>	json_decode($res['emi'], true),
				'center_id'		=>	$res['center_id'],
				'center'		=>	$res['center'],
				'city_id'		=> 	$city['id'] ?? 0,
				'city_data'		=>	$city_data,
				'centers'		=>	$center_data,
				'price_plan'	=>	$price_plan ?? '',
				'payment_id'	=>	$code,
				'code'			=>	$code,
			];

			$data['prices']			= [];

			foreach ($prices as $key => $value) {
				$data['prices'][$key] = $value;
			}

			$data['page_name']		= 'enrol/enrolment';
			$data['page_title'] 	= _l('enrolment');
			$data['action']			= site_url('home/enrol/' . $code);
			$data['action_pay']		= site_url('home/updateTransaction');

			$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
		} else {
			$this->session->set_flashdata('error_message', _l('invalid_enrolment_id'));
			redirect(site_url('home'), 'refresh');
		}
	}

	public function enrol($code) {
		$json = [];

		$emi_type = $this->input->get_post('emi_type');

		if (!($lead_info = $this->lead_model->getByCode($code)) || ($lead_info['payment_link_status'] ?? 0) == 1) {
			$json['error'] = _li('invalid_link_or_already_paid');
		} else {
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($lead_info['site_id']);

			$prices = [
				'base'		=> $this->config->item('site_base_plan'),
				'premium'	=> $this->config->item('site_premium_plan'),
			];
		}

		if ($lead_info && $lead_info['locked'] == 0 && !in_array($emi_type, array_keys($prices))) {
			$json['error'] = _l('select_valid_emi_type');
		}

		if (!$json) {
			if ($lead_info['locked']) {
				$amount 	= $lead_info['amount'];
				$emi_type 	= $lead_info['emi_type'];
			} else {
				$amount = @$prices[$emi_type];

				$this->lead_model->updateByCode($code, $amount, $emi_type);
			}

			// $extra 	= $this->order_model->generateOrderId($lead_info['id'], $lead_info['amount']);
			$extra 		= $this->order_model->generateOrderId($lead_info['id'], $amount);
			// $user_info 	= $this->student_model->get($lead_info['student_id'])->row_array();

			$order_id 	= $this->order_model->add([
				'lead_id'			=> $lead_info['id'],
				'emi_type'			=> $emi_type,
				'amount'			=> $amount,
				/*'amount'			=> $lead_info['amount'],*/
				'course_id'			=> $lead_info['course_id'],
				'bulk_renewal'		=> 0,
				'payment_type'		=> 'razorpay',
				'user_id'			=> 0,
				'extra'				=> $extra
			]);

			$json['order'] = [
				'key'			=> 	RAZORPAY_KEY,
				'id'			=> 	$order_id,
				'amount'		=> 	$amount,
				'currency_code'	=> 	$this->config->item('site_currency_code'),
				/*'amount'		=> 	$lead_info['amount'],*/
				'name'			=>  $lead_info['course'],
				'description'	=>  $lead_info['course'],
				'image'			=> 	site_url('uploads/system/logo-dark.png'),
				'order_id'		=>	$extra,
				'user'			=>  [
					'name'		=>  $lead_info['name'],
					'email'		=>  $lead_info['email'],
					'mobile'	=>  $lead_info['mobile']
				],
				'address'		=>  '',
				'code'			=>  $code,
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function renewal($code) {
		$res = $this->enrol_model->getByCode($code);

		if ($res) {
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($res['site_id']);

			$user_info = $this->student_model->get($res['user_id'])->row();

			$prices = [
				'base'		=> $this->config->item('site_base_plan'),
				'premium'	=> $this->config->item('site_premium_plan'),
			];

			$data = [
				'user_image'	=>	$this->user_model->get_user_image_url($res['user_id']),
				'enrol_id'		=>	$res['id'],
				'locked'		=>	$res['amount'] > 0 ? $res['locked'] : false,
				'instalment'	=>	$res['instalment'] ?? '',
				'instalment_text'=>	_l('final_instalment'),
				'scholarship'	=>	$res['scholarship'] ?? '',
				'user_id'		=>	$res['user_id'],
				'name'			=>	$user_info->first_name . " " . $user_info->last_name,
				'parent_name'	=>	$user_info->parent_name,
				'mobile'		=>	$user_info->mobile,
				'email'			=>	$user_info->email,
				'course_id'		=>	$res['course_id'],
				'course'		=>  $res['course'],
				'mode'			=>	$res['mode'],
				'base_amount'	=> 	$prices[$res['emi_type']] ?? $res['amount'],
				'amount'		=> 	$res['amount'],
				'currency_code'	=> 	$this->config->item('site_currency_code'),
				'code'			=>	$code,
				'emi_type'		=>	$res['emi_type']
			];

			// pr($res);

			$data['page_name'] 	= 'enrol/enrolment';
			$data['page_title'] = _l('renewal');

			$data['prices'] 		= $prices;
			$data['action']			= site_url('home/renew/' . $code);
			$data['action_pay']		= site_url('home/renewTransaction');

			$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
		} else {
			$this->session->set_flashdata('error_message', _l('invalid_renewal_id'));
			redirect(site_url('home'), 'refresh');
		}
	}

	public function renew($code) {
		$json = $prices = [];

		$emi_type = $this->input->get_post('emi_type');

		if (!($enrol_info = $this->enrol_model->getByCode($code)) || $enrol_info['payment_link_status'] == 1) {
			$json['error'] = _li('invalid_link_or_already_paid');
		} else {
			$this->load->model('common/Site_model', 'site_model');
			$this->site_model->initConfig($enrol_info['site_id']);

			$prices = [
				'base'		=> $this->config->item('site_base_plan'),
				'premium'	=> $this->config->item('site_premium_plan'),
			];
		}

		if ($enrol_info
			&& $enrol_info['locked'] == 0
			&& !in_array($emi_type, array_keys($prices))
		) {
			$json['error'] = _l('select_valid_emi_type');
		}

		if (!$json) {
			if ($enrol_info['locked'] && $enrol_info['amount'] > 0) {
				$amount 	= $enrol_info['amount'];
				$emi_type 	= $enrol_info['emi_type'];
			} else {
				$amount = @$prices[$emi_type];

				if (empty($amount)) {
					$amount = $enrol_info['amount'];
				}

				$this->enrol_model->updateByCode($code, $amount, $emi_type);
			}

			$extra = $this->order_model->generateOrderId($enrol_info['id'], $amount);

			$order_id = $this->order_model->add([
				'enrol_id'			=> $enrol_info['id'],
				'emi_type'			=> $emi_type,
				'amount'			=> (double)$amount,
				'course_id'			=> $enrol_info['course_id'],
				'bulk_renewal'		=> 0,
				'payment_type'		=> 'razorpay',
				'user_id'			=> (int)$enrol_info['user_id'],
				'extra'				=> $extra
			]);

			$student_info = $this->student_model->get($enrol_info['user_id'])->row_array();

			$json['order'] = [
				'key'			=> 	RAZORPAY_KEY,
				'id'			=> 	$order_id,
				'amount'		=> 	(double)$amount,
				'currency_code'	=> 	$this->config->item('site_currency_code'),
				'name'			=>  $enrol_info['course'],
				'description'	=>  $enrol_info['course'],
				'image'			=> 	site_url('uploads/system/logo-dark.png'),
				'order_id'		=>	$extra,
				'user'			=>  array(
					'name'		=>  $student_info['first_name'].' '.$student_info['last_name'],
					'email'		=>  $student_info['email'],
					'mobile'	=>  $student_info['mobile']
				),
				'address'		=>  '',
				'code'			=>  $code,
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function updateTransaction() {
		$json = [];

		if (($order_id 		= $this->input->post('order_id')) &&
			($payment_id	= $this->input->post('payment_id')) &&
			($signature 	= $this->input->post('signature')) &&
			($id 			= $this->input->post('id')) &&
			($code 			= $this->input->post('code'))
		) {

			if (
				($order_info = $this->order_model->get($id)) &&
				($lead_info = $this->lead_model->getByCode($code)) &&
				$this->order_model->verifyOrder([
					'order_id'		=> $order_id,
					'payment_id'	=> $payment_id,
					'signature'		=> $signature,
				])
			) {
				$this->load->model('common/Site_model', 'site_model');
				$this->site_model->initConfig($lead_info['site_id']);

				$order_id 	= $order_info['id'];
				$lead_id 	= $order_info['lead_id'];
				$user_id 	= $order_info['user_id'];
				$type 		= $order_info['payment_type'];

				$emi_type 	= $lead_info['emi_type'];
				$amount 	= $lead_info['amount'];
				$course 	= $lead_info['course'];
				$course_id 	= $lead_info['course_id'];
				$mode 		= $lead_info['mode'];

				$user_id	= self::_createUser(
					$lead_info,
					'premium',
				);

				$enrol_id = $this->enrol_model->enrol([
					'user_id'		=> $user_id,
					'lead_id'		=> $lead_id,
					'course_id'		=> $course_id,
					'mode'			=> $mode,
					'payment_type'	=> $type,
					'emi_type'		=> $emi_type,
					'instalment'	=> $lead_info['instalment'] ?? '',
					'scholarship'	=> $lead_info['scholarship'] ?? '',
					'amount'		=> $amount,
					'order_id'		=> $order_id,
				]);

				$this->order_model->edit($order_id, [
					'transaction_id' 	=> $payment_id,
					'enrol_id' 			=> $enrol_id,
					'user_id' 			=> $user_id,
					'status' 			=> 1
				]);

				$json['data'] = [
					'amount'		=> $amount,
					'course' 		=> $course,
					'payment_id'	=> $payment_id
				];

				// Update lead status to enrolled
				$this->lead_model->edit($lead_info['id'], [
					'status'		=> 4,
				]);

				// Update payment link to expired
				$this->order_model->updatePaymentLinkStatus($code);

				$this->alert_model->enrolled($enrol_id);

				$json['redirect'] = site_url('home/success');

				$this->session->set_userdata('order_type', $type);
				$this->session->set_userdata('order_amount', $amount);
				$this->session->set_userdata('order_payment_id', $payment_id);
				$this->session->set_userdata('order_course', $course);
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function renewTransaction() {
		$json = [];

		if (($order_id 		= $this->input->post('order_id')) &&
			($payment_id	= $this->input->post('payment_id')) &&
			($signature 	= $this->input->post('signature')) &&
			($id 			= $this->input->post('id')) &&
			($code 			= $this->input->post('code'))
		) {

			if (
				($order_info = $this->order_model->get($id)) &&
				($enrol_info = $this->enrol_model->getByCode($code)) &&
				$this->order_model->verifyOrder([
					'order_id'		=> $order_id,
					'payment_id'	=> $payment_id,
					'signature'		=> $signature,
				])
			) {
				$this->load->model('common/Site_model', 'site_model');
				$this->site_model->initConfig($enrol_info['site_id']);

				$order_id 	= $order_info['id'];
				$lead_id 	= $order_info['lead_id'];
				$user_id 	= $order_info['user_id'];
				$type 		= $order_info['payment_type'];

				$emi_type 	= $enrol_info['emi_type'];
				$amount 	= $enrol_info['amount'];
				$course 	= $enrol_info['course'];
				$course_id 	= $enrol_info['course_id'];
				$mode 		= $enrol_info['mode'];

				$enrol_id = $this->enrol_model->enrol([
					'user_id'		=> $user_id,
					'lead_id'		=> $lead_id,
					'course_id'		=> $course_id,
					'mode'			=> $mode,
					'payment_type'	=> $type,
					'emi_type'		=> $emi_type,
					'instalment'	=> $enrol_info['instalment'],
					'scholarship'	=> $enrol_info['scholarship'],
					'amount'		=> $amount,
					'order_id'		=> $order_id,
				]);

				$this->order_model->edit($order_id, [
					'transaction_id' 	=> $payment_id,
					'enrol_id' 			=> $enrol_id,
					'status' 			=> 1
				]);

				$json['data'] = [
					'amount'		=> $amount,
					'course' 		=> $course,
					'payment_id'	=> $payment_id
				];

				// Update payment link to expired
				$this->order_model->updatePaymentLinkStatus($code);

				// Alert for renewal
				$this->alert_model->enrolled($enrol_id);

				$json['redirect'] = site_url('home/success');

				$this->session->set_userdata('order_type', $type);
				$this->session->set_userdata('order_amount', $amount);
				$this->session->set_userdata('order_payment_id', $payment_id);
				$this->session->set_userdata('order_course', $course);
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function success() {
		$data['page_name'] 	= 'payment_success';
		$data['page_title'] = _l('success');

		if ($this->session->has_userdata('order_amount')) {
			$data['amount'] = $this->session->order_amount;
		}

		if ($this->session->has_userdata('order_payment_id')) {
			$data['payment_id'] = $this->session->order_payment_id;
		}

		if ($this->session->has_userdata('order_course')) {
			$data['course'] = $this->session->order_course;
		}

		if ($this->session->has_userdata('order_type')) {
			$data['type'] = $this->session->order_type;
		}

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	private function _createUser($lead_info = [], $emi_type = 'premium', $discount_code = '') {
		$names = explode(' ', $lead_info['name'], 2);

		$student_id = $this->lead_model->addStudent([
			'first_name'		=> array_shift($names),
			'last_name'			=> array_shift($names),
			'lead_id'			=> $lead_info['id'],
			'parent_name'		=> $lead_info['parent_name'],
			'course_id'			=> $lead_info['course_id'],
			'schedule_id'		=> 0,
			'email'				=> $lead_info['email'],
			'mobile'			=> $lead_info['mobile'],
			'grade'				=> $lead_info['grade'],
			'location'			=> $lead_info['location'],
			'discount_code'		=> $discount_code,
			'emi_type'			=> $emi_type
		], false);

		$this->lead_model->edit($lead_info['id'], [
			'student_id'	=> (int)$student_id,
		]);

		$this->load->model('user/User_model', 'user_model');

		$code = $this->user_model->addLoginCode([
			'user_id'	=> $student_id
		]);

		$this->input->set_cookie('login_code', $code, 4 * 3600);

		return $student_id;
	}

	public function invoice($order_id = 0) {
		if ($order_info = $this->order_model->get($order_id)) {
			$student_info 				= $this->student_model->get($order_info['user_id'])->row_array();

			$data['items'] 				= $this->order_model->getPaymentsByOrderId($order_info['id']);

			$data['title']				= 'INVLL' . $order_info['id'];
			$data['total']				= $order_info['amount'];
			$data['invoice_number']		= 'INVLL' . $order_info['id'];
			$data['invoice_date']		= date('F j, Y', strtotime($order_info['date_added']));
			$data['name']				= $student_info['first_name'] . ' ' . $student_info['last_name'];
			$data['mobile']				= $student_info['mobile'];


			$html = $this->load->view('common/invoice/invoice', $data, true);

			echo $html;
			/*die;

			$dompdf = new Dompdf();
			$dompdf->loadHtml($html);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			// (Optional) Setup the paper size and orientation
			//$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream();*/
		}
	}
}
