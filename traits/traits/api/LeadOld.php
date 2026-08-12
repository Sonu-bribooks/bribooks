<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait LeadOld {
	private function _getCourseByGrade($grade) {
		foreach (COURSE_GRADE as $course_id => $grades) {
			if (in_array($grade, $grades)) return $course_id;
		}
	}

	private function addLead() {
		$country_info = self::getCountry(true);

		$country_name = $this->input->post('country')
			? $this->input->post('country')
			: $country_info['country'];

		$country_code_info = $this->country_model->get_country_code($country_name);
		$country_code = $this->input->post('country_code')
			? $this->input->post('country_code')
			: ($country_code_info['tel_code'] ?? '+1');

		// Case 2. school with own landing page
		if ($this->config->item('site_discount_code')) {
			$site_id = $this->config->item('site_id');
		} else {
			// Case 1. b2c operation without discount code
			$site_info = $this->site_model->getSiteByName($country_name);

			$site_id = $site_info['id'] ?? 0;

			if (empty($site_id)) {
				$site_id = $this->config->item('default_site_id');
			}

			// Case 3. b2c operation with discount code
			// Step 1. check discount code added by user
			// Step 2. get site id by discount code
		}

		return $this->lead_model->add([
			'name'				=> $this->input->post('student_name'),
			'age'				=> $this->input->post('student_age'),
			'parent_name'		=> $this->input->post('parent_name'),
			'mobile'			=> $this->input->post('mobile'),
			'grade'				=> $this->input->post('student_grade'),
			'email'				=> $this->input->post('email'),
			'location'			=> $country_name,
			'country_code'		=> $country_code,
			'mobile_verified'	=> 0,
			'site_id'			=> (int)$site_id,
			'course_id'			=> (int)($this->input->post('programs')
				? $this->input->post('programs')
				: self::_getCourseByGrade($this->input->post('student_grade'))
			),
			'ip'				=> $this->input->ip_address(),
			'utm_source'		=> $this->input->post('utm_source') ?? '',
			'utm_medium'		=> $this->input->post('utm_medium') ?? '',
			'utm_campaign'		=> $this->input->post('utm_campaign') ?? '',
		]);
	}

	private function addSchoolLead() {
		return $this->school_lead_model->add([
			'name'				=> $this->input->post('name'),
			'type'				=> $this->input->post('type'),
			'country'			=> $this->input->post('country'),
			'city'				=> $this->input->post('city'),
			'mobile'			=> $this->input->post('mobile'),
			'email'				=> $this->input->post('email'),
			'mobile_verified'	=> 0,
			'email_verified'	=> 0,
			'utm_source'		=> $this->input->post('utm_source') ?? '',
			'utm_medium'		=> $this->input->post('utm_medium') ?? '',
			'utm_campaign'		=> $this->input->post('utm_campaign') ?? '',
		]);
	}

	public function getBillingPlan() {
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$lead_info = $this->lead_model->get($this->input->post('lead_id'));

			$site_info = $this->site_model->getSiteByName($lead_info['location']);
			$currency_info = $this->currency_model->getByCode($site_info['currency_code']);

			$site_info['mobile'] 	= $lead_info['mobile'];
			$site_info['sign'] 		= sprintf('<i>%s</i>', $currency_info['symbol'] ?? '');

			$this->json['error'] 	= false;
			$this->json['data'] 	= $site_info;
		}

		$this->setOutput();
	}

	public function setSubscription() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[' . implode(',', array_keys(EMI_CHARGE)) . ']');
		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);
		$this->form_validation->set_rules('discount_code', _l('discount_code'), 'trim|min_length[8]|max_length[16]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$this->json['redirect'] = self::_generatePaymentLink(
				$this->input->post('lead_id'),
				$this->input->post('type'),
				$this->input->post('discount_code')
			);
		}

		$this->setOutput();
	}

	private function _getFormattedAmount($lead_info = []) {
		$this->site_model->initConfig($lead_info['site_id']);

		if (
			$this->config->item('site_discount_code')
			&& $this->config->item('site_premium_plan')
		) {
			return $this->config->item('site_currency_symbol') . $this->config->item('site_premium_plan');
		} else {
			return (strtolower($lead_info['location']) === 'india'
				? '₹'
				: '$'
			) . EMI_CHARGE['premium'][(
				strtolower($lead_info['location']) === 'india'
					? 'india'
					: 'other'
			)];
		}
	}

	private function _generatePaymentLink($lead_id, $emi_type = 'premium', $discount_code = '') {
		$lead_info = $this->lead_model->get($lead_id);

		$this->lead_model->edit($lead_id, [
			'mobile_verified'	=> 1
		]);

		$this->site_model->initConfig($lead_info['site_id']);

		// $site_info = $this->site_model->get($lead_info['site_id']);

		// self::_createUser(
		// 	$lead_info,
		// 	$emi_type,
		// 	$discount_code
		// );

		return self::_getPlanPrice($lead_info, $emi_type, $discount_code);
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
		]);

		$this->lead_model->edit($lead_info['id'], [
			'student_id'	=> (int)$student_id,
		]);

		$this->load->model('user/User_model', 'user_model');

		$code = $this->user_model->addLoginCode([
			'user_id'	=> $student_id
		]);

		$this->input->set_cookie('login_code', $code, 4 * 3600);
	}

	private function _getPlanPrice($lead_info, $emi_type = 'premium', $discount_code = '') {
		// if (in_array($emi_type, ['free','base','premium'])) {
		// 	if (
		// 		(
		// 			$discount_code &&
		// 			in_array($discount_code, DEMO_DISCOUNT_CODES)
		// 		) || $emi_type == 'free'
		// 	) {
		// 		$this->alert_model->demoFeeDetails($lead_info['id']);
		//
		// 		$this->json['redirect'] = site_url('login/code/' . $code);
		// 	} else {
		// 		$plan = '';
		// 		$amount = '';
		//
		// 		if ($emi_type === 'premium') {
		// 			$plan = 'premium';
		// 			$amount = $site_info['premium_plan'];
		// 		} else {
		// 			$plan = 'base';
		// 			$amount = $site_info['base_plan'];
		// 		}
		//
		// 		$code = $this->lead_model->generatePaymentLink($lead_info['id'], $amount, $plan);
		//
		// 		$this->json['redirect'] = site_url('home/enrolment/' . $code);
		// 	}
		// } else {
		// 	$this->json['redirect'] = site_url();
		// }

		if ($emi_type === 'free') {
			return site_url('login/code/' . $code);
		} else {
			if (in_array($lead_info['email'], TESTING_EMAILS)) {
				$price = TESTING_PRICE;
			} else {
				if (
					$this->config->item('site_discount_code')
					&& $this->config->item('site_premium_plan')
				) {
					$price = $this->config->item('site_premium_plan');
				} else {
					$price = EMI_CHARGE['premium'][
						(strtolower($lead_info['location']) === 'india'
							? 'india'
							: 'other'
						)
					];
				}
			}

			$code = $this->lead_model->generatePaymentLink(
				$lead_info['id'],
				$price,
				'premium',
				true
			);

			return site_url('home/enrolment/' . $code);
		}
	}

	public function saveLead() {
		if ($this->config->item('site_country_code') === 'AE') {
			$this->demo_dates = DEMO_DATES_IS;
		}

		$this->form_validation->set_rules('parent_name', _l('parent_name'), 'trim|required|min_length[3]|max_length[40]');
		$this->form_validation->set_rules('student_name', _l('student_name'), 'trim|required|min_length[3]|max_length[40]');
		// $this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|exact_length[' . $this->config->item('site_mobile_length') . ']');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|max_length[30]');
		$this->form_validation->set_rules('learning_mode', _l('learning_mode'), 'trim|required|in_list[online,offline]');
		$this->form_validation->set_rules('utm_source', _l('utm_source'), 'trim|max_length[255]');
		$this->form_validation->set_rules('utm_medium', _l('utm_medium'), 'trim|max_length[255]');
		$this->form_validation->set_rules('utm_campaign', _l('utm_campaign'), 'trim|max_length[255]');

		$this->form_validation->set_rules('student_age', _l('student_age'), [
			'trim',
			'required',
			['student_age', [$this->validate_model, 'studentAge']]
		]);

		$this->form_validation->set_rules('programs', _l('programs'), [
			'trim',
			'required',
			'numeric',
			['programs', [$this->validate_model, 'program']]
		]);

		$this->form_validation->set_rules('demo_date', _l('demo_date'), [
			'trim',
			'required',
			'in_list[' . implode(',', $this->demo_dates) . ']',
			// ['demo_date', [$this->validate_model, 'demoDate']]
		]);

		$this->form_validation->set_rules('demo_time', _l('demo_time'), [
			'trim',
			'required',
			'in_list[' . implode(',', $this->demo_times) . ']',
			// 'numeric',
			// ['demo_time', [$this->validate_model, 'demoTime']]
		]);

		$this->form_validation->set_rules('lead_id', _l('lead_id'), [
			'trim',
			'required',
			'numeric',
			['lead', [$this->validate_model, 'lead']]
		]);

		$this->form_validation->set_rules('center', _l('center'), [
			'trim',
			'numeric',
			['center', [$this->validate_model, 'center']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$lead_info = $this->lead_model->get($this->input->post('lead_id'));

			$this->lead_model->edit($this->input->post('lead_id'), [
				'mobile_verified'	=> 1,
				'mode'				=> $this->input->post('learning_mode'),
				'schedule'			=> $this->input->post('demo_date') . ' ' . $this->input->post('demo_time'),
				'class_id'			=> 0, //(int)$this->input->post('demo_time'),
				'center_id'			=> (int)$this->input->post('center'),
				'utm_source'		=> $this->input->post('utm_source') ?? '',
				'utm_medium'		=> $this->input->post('utm_medium') ?? '',
				'utm_campaign'		=> $this->input->post('utm_campaign') ?? '',
			]);

			$this->alert_model->demoRequest($this->input->post('lead_id'));

			$this->json['error'] 	= $this->session->flashdata('error_message');
			$this->json['success'] 	= base64_encode(sprintf(_li('Your Demo Request for %s has been recieved for %s. Our enrolment team will call you soon to confirm the slot availablity.'),
				$lead_info['course'],
				$this->input->post('demo_date') . ' ' . $this->input->post('demo_time')
			));
		}

		$this->setOutput();
	}
}
