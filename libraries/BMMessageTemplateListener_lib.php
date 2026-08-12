<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class BMMessageTemplateListener_lib {
	public static function bmUserOtp(...$params) {
		list($data) = $params;

		log_kb([
			'Event::bmUserOtp' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('Alert_model');

		if (!empty($data['mobile']) &&
			strlen($data['mobile']) == 12 &&
			substr($data['mobile'], 0, 2) == 91
		) {
			$data['site_id'] = 1;
		}

		log_kb([
			'Event::bmuserOtp::template' => [$data]
		]);

		$payload = [
			'mobile'	=> $data['mobile'] ?? '',
			'email'		=> $data['email'] ?? '',
			'otp'		=> $data['otp'],
		];

		$CI->Alert_model->genericBMMessageTemplate([
			'code'				=> 'user_otp',
			'country_code'		=> $data['country_code'] ?? 'IN',
			'email'		   		=> $data['email'] ?? '',
			'mobile'		  	=> $data['mobile'] ?? '',
			'includes'			=> [$data['type'] ?? 'sms'],
			'data'				=> $payload,
		]);
	}

	public static function bmSchoolSignup(...$params) {
		list($data) = $params;

		log_kb([
			'Event::bmSchoolSignup' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('briminds/school/BMSite_model', 'bm_site_model');

		$CI->load->model('Alert_model');

		if (empty($site_info = $CI->bm_site_model->get($data['id']))) return;

		$payload = [
			'id'					=> $site_info['id'] ?? 0,
			'mobile'				=> $site_info['owner_mobile'] ?? '',
			'email'					=> $site_info['owner_email'] ?? '',
			'authorized_person'		=> $site_info['authorized_person'] ?? '',
		];

		$CI->Alert_model->genericBMMessageTemplate([
			'code'				=> 'school_signup',
			'country_code'		=> $data['country_code'] ?? 'IN',
			'email'		   		=> $site_info['owner_email'] ?? '',
			'mobile'		  	=> $site_info['owner_mobile'] ?? '',
			'includes'			=> [],
			'data'				=> $payload,
		]);
	}

	public static function bmAfterSchoolSignup(...$params) {
		list($data) = $params;

		log_kb([
			'Event::bmAfterSchoolSignup' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('briminds/school/BMSite_model', 'bm_site_model');

		$CI->load->model('Alert_model');

		if (empty($site_info = $CI->bm_site_model->get($data['id']))) return;

		$payload = [
			'id'					=> $site_info['id'] ?? 0,
			'mobile'				=> $site_info['owner_mobile'] ?? '',
			'email'					=> $site_info['owner_email'] ?? '',
			'authorized_person'		=> $site_info['authorized_person'] ?? '',
		];

		$CI->Alert_model->genericBMMessageTemplate([
			'code'				=> 'after_school_signup',
			'country_code'		=> $data['country_code'] ?? 'IN',
			'email'		   		=> $site_info['owner_email'] ?? '',
			'mobile'		  	=> $site_info['owner_mobile'] ?? '',
			'includes'			=> [],
			'data'				=> $payload,
		]);
	}
}
