<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class NYAFEventListener_lib {
	private static $CI = null;

	public static function init() {
		static::$CI =& get_instance();

		static::$CI->load->model('common/Site_model');
		static::$CI->load->model('event/EventUser_model');
		static::$CI->load->model('event/EventUser_model', 'event_user_model');
		static::$CI->load->model('event/EventSite_model', 'event_site_model');
		static::$CI->load->model('user/Student_model');
		static::$CI->load->model('Alert_model', 'alert_model');
		static::$CI->load->model('common/Cron_model', 'cron_model');
		static::$CI->load->model('school/SchoolUser_model', 'school_user_model');
		static::$CI->load->model('common/AsyncTask_model', 'async_task_model');
		static::$CI->load->model('book/BookWritingLogs_model', 'book_writing_logs_model');
		static::$CI->load->library('user_agent');
	}

	public static function eventStudentSignup(...$params) {
		self::init();
		list($data) = $params;

        if($data['event_id'] == 10){
			if(empty(static::$CI->event_site_model->getEventIdBySiteId($data['event_id'], $data['site_id']))) {
				static::$CI->event_site_model->add([
					'event_id'	=> $data['event_id'],
					'site_id'	=> $data['site_id']
				]);
			}

			$user_events_info = static::$CI->event_user_model->get_all([
				'event_id' => $data['event_id'],
				'site_id'  => $data['site_id']
			]);

			if(empty($user_events_info['rows'] ?? [])){
				static::$CI->cron_model->add([
					'code'			=> 'schoolAcknowledgeOnStudentSignupCron_' . $data['site_id'],
					'action'		=> 'alert_model->schoolAcknowledgeOnStudentSignupCron',
					'data'			=> [$data['site_id'],$data['event_id']],
					'site_id'		=>  1,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);
			}
		}
	}

	public static function eventSchoolSignup(...$params) {
		self::init();
		list($data) = $params;

		if (empty(static::$CI->cron_model->get_all([
			'code'			=> 'schoolSignupWelcomeCron_' . $data['site_id'] . '_' . $data['event_id'],
		])['rows'][0] ?? '')) {
			static::$CI->cron_model->add([
				'code'			=> 'schoolSignupWelcomeCron_' . $data['site_id'] . '_' . $data['event_id'],
				'action'		=> 'alert_model->schoolSignupWelcomeCron',
				'data'			=> [$data['site_id'], $data['event_id']],
				'site_id'		=>  1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
			]);
		}
	}

	public static function sendSchoolVerificationEmail(...$params) {
		self::init();
		list($data) = $params;

		if (!empty($data['user_id'])) {
			if (($user_info = static::$CI->school_user_model->get($data['user_id']))) {
				if (empty($user_info['verification_code'])) {
					$password 			= uniqid();
					$verification_code 	= sha1(md5($user_info['first_name'] . $password . static::$CI->config->item('password_salt')));

					static::$CI->school_user_model->edit($user_info['id'], [
						'verification_code' => $verification_code
					]);
				}

				static::$CI->async_task_model->add([
					'action'	=> 'Alert_model->sendSchoolVerificationEmail',
					'data' 		=> [$data['user_id']]
				]);
			}
		}
	}

	public static function bookWritingLog(...$params) {
		self::init();
		list($data) = $params;
		
		if (!empty(static::$CI->session->userdata('user_id'))) {
			self::_sendCustomThemeAlert($data);

			if ($info = static::$CI->book_writing_logs_model->get_all([
					'book_id'	=> (int)$data['id'] ?? 0,
					'user_id'	=> (int)static::$CI->session->userdata('user_id')
			])['rows'][0] ?? []) {
				static::$CI->book_writing_logs_model->edit($info['id'], [
					'browser'	=> !empty(static::$CI->input->post('app_os')) ? (!empty(static::$CI->input->post('is_tablet')) ? 'tablet' : 'mobile') : static::$CI->agent->browser(),
					'platform'	=> !empty(static::$CI->input->post('app_os')) ? static::$CI->input->post('app_os') : static::$CI->agent->platform(),
					'ip'		=> static::$CI->input->ip_address(),
				]);
			} else {
				static::$CI->book_writing_logs_model->add([
					'book_id'	=> (int)$data['id'] ?? 0,
					'user_id'	=> (int)static::$CI->session->userdata('user_id'),
					'browser'	=> !empty(static::$CI->input->post('app_os')) ? (!empty(static::$CI->input->post('is_tablet')) ? 'tablet' : 'mobile') : static::$CI->agent->browser(),
					'platform'	=> !empty(static::$CI->input->post('app_os')) ? static::$CI->input->post('app_os') : static::$CI->agent->platform(),
					'ip'		=> static::$CI->input->ip_address(),
				]);
			}
		}
	}

	private static function _sendCustomThemeAlert($data = []) {
		$code = sprintf('customThemeAlert_%s', $data['id']);

		if (
			!empty($category_info = static::$CI->category_model->get($data['category_id'])) &&
			$category_info['custom_theme'] == 1 &&
			empty(static::$CI->cron_model->getByCode($code))
		) {
			$document_id = sha1(md5(static::$CI->session->userdata('user_id') . time() . static::$CI->config->item('password_salt')));
			$custom_theme_log_id = static::$CI->custom_theme_log_model->add([
				'user_id'		=> static::$CI->session->userdata('user_id'),
				'book_id'		=> $data['id'] ?? 0,
				'document_id'	=> $document_id,
				'ip_address'	=> static::$CI->input->ip_address(),
				'status'		=> 1
			]);

			static::$CI->cron_model->add([
				'code'			=> $code,
				'action'		=> 'alert_model->customThemeAlert',
				'data'			=> [$custom_theme_log_id],
				'alert_date'	=> date('Y-m-d H:i:s', strtotime('+1 minutes'))
			]);
		}
	}

	public static function referralStudentSignup(...$params) {
		self::init();
		list($data) = $params;

        if ($data['event_id'] == 10) {

			static::$CI->cron_model->add([
				'code'			=> 'referralStudentSignup_' . $data['referral_id'],
				'action'		=> 'alert_model->referralStudentSignup',
				'data'			=> [$data['site_id'],$data['event_id'],$data['student_id'],$data['referral_id']],
				'site_id'		=>  1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
			]);
		}
	}
}
