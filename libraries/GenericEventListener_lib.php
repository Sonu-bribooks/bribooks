<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class GenericEventListener_lib {
	public static function schoolEventAutoEnrol(...$params) {
		list($data) = $params;

		log_kb([
			'SchoolEventListener_lib::schoolEventAutoEnrol' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('event/Event_model');
		$CI->load->model('Alert_model');
		$CI->load->model('common/Site_model');
		$CI->load->model('school/SchoolUser_model');
		$CI->load->model('localisation/State_model');
		$CI->load->model('event/EventSite_model');
		$CI->load->model('common/Cron_model');

		$site_info = $CI->site_model->get($data['site_id']);
		$user_info = $CI->SchoolUser_model->get_all([
			'site_id' => $data['site_id']
		])['rows'][0] ?? [];

		if (!empty($event_info = $CI->event_model->get_all([
			'site_id'			=> $site_info['id'],
			'event_type_id'		=> 1,
			'end_date_ge'		=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [])) {
			log_kb([
				'schoolEventAutoEnrol:running::' => compact(['data', 'event_info'])
			]);
		}

		$state_info 	= $CI->state_model->get($site_info['state_id']);
		$currency_info 	= $CI->currency_model->getByCode($site_info['currency_code']);

		$event_id = $CI->event_model->add([
			'name'					=> $site_info['name'],
			'slug'					=> '',
			'label'					=> 'School Event',
			'parent_site_id'		=> $site_info['id'],
			'country_id'			=> $state_info['country_id'],
			'country_code'			=> $site_info['country_code'],
			'currency_id'			=> $currency_info['id'],
			'currency_code'			=> $site_info['currency_code'],
			'event_type_id'			=> 1,
			'status'				=> 1,
			'start_date'			=> date('Y-m-d H:i:s'),
			'school_reg_end_date'	=> date('Y-m-d H:i:s'),
			'student_reg_end_date'	=> date('Y-m-d H:i:s', self::_normalizeTimezone(15, $user_info)),
			'book_writing_end_date'	=> date('Y-m-d H:i:s', self::_normalizeTimezone(45, $user_info)),
			'selling_end_date'		=> date('Y-m-d H:i:s', self::_normalizeTimezone(75, $user_info)),
			'end_date'				=> date('Y-m-d H:i:s', self::_normalizeTimezone(90, $user_info)),
			'url'					=> 'http://events.bribooks.com/',
			'rank_url'				=> '',
		]);

		$CI->EventSite_model->add([
			'event_id'	=> $event_id,
			'site_id'	=> $site_info['id'],
		]);

		$CI->Cron_model->add([
			'code'			=> 'schoolSignupWelcomeCron_' . $site_info['id'],
			'action'		=> 'alert_model->schoolSignupWelcomeCron',
			'data'			=> [$site_info['id'],$event_id],
			'site_id'		=>  1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);
	}

	public static function teacherEventAutoEnrol(...$params) {
		list($data) = $params;

		log_kb([
			'SchoolEventListener_lib::teacherEventAutoEnrol' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('event/Event_model');
		$CI->load->model('user/teacher/Teacher_model');
		$CI->load->model('Alert_model');
		$CI->load->model('localisation/Country_model');
		$CI->load->model('localisation/State_model');
		$CI->load->model('common/Cron_model');

		$user_info  = $CI->teacher_model->get($data['teacher_id']);

		if (!empty($event_info = $CI->event_model->get_all([
			'site_id'			=> $user_info['id'],
			'event_type_id'		=> 6,
			'end_date_ge'		=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [])) {
			log_kb([
				'teacherEventAutoEnrol:running::' => compact(['data', 'event_info'])
			]);
		}

		$site_info 		= $CI->site_model->get($user_info['site_id']);
		$currency_info 	= $CI->currency_model->getByCode($site_info['currency_code']);

		$event_id = $CI->event_model->add([
			'name'					=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'slug'					=> '',
			'label'					=> 'Class Event',
			'parent_site_id'		=> $user_info['id'],
			'country_id'			=> $user_info['country_id'],
			'country_code'			=> $site_info['country_code'],
			'currency_id'			=> $currency_info['id'],
			'currency_code'			=> $currency_info['code'],
			'event_type_id'			=> 6,
			'status'				=> 1,
			'start_date'			=> date('Y-m-d H:i:s'),
			'school_reg_end_date'	=> date('Y-m-d H:i:s'),
			'student_reg_end_date'	=> date('Y-m-d H:i:s', self::_normalizeTimezone(15, $user_info)),
			'book_writing_end_date'	=> date('Y-m-d H:i:s', self::_normalizeTimezone(45, $user_info)),
			'selling_end_date'		=> date('Y-m-d H:i:s', self::_normalizeTimezone(75, $user_info)),
			'end_date'				=> date('Y-m-d H:i:s', self::_normalizeTimezone(90, $user_info)),
			'url'					=> 'http://events.bribooks.com/',
			'rank_url'				=> '',
		]);

		$CI->Cron_model->add([
			'code'			=> 'schoolSignupWelcomeCron_' . $user_info['id'],
			'action'		=> 'alert_model->schoolSignupWelcomeCron',
			'data'			=> [$user_info['id'],$event_id],
			'site_id'		=>  1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);
	}

	private function _normalizeTimezone($days = 0, $user_info = []) {
		return strtotime(sprintf('%s +%s days +%s minutes', date('Y-m-d'), $days, (-330 - $user_info['timezone'])));
	}

	public static function eventSignup(...$params) {
		list($data) = $params;

		log_kb([
			'GenericEventListener_lib::eventSignup' => [$params, $data]
		]);

		$CI =& get_instance();
		$CI->load->model('common/Cron_model');
		$CI->load->model('user/Lead_model', 'lead_model');

		$lead_info = $CI->lead_model->get($data['lead_id']);

		if (!empty($lead_info) && !empty($lead_info['parent_referral_id']) ) {
			$code	= sprintf('event%sReferralSignup_%s_%s', ucwords($data['type'] ?? 'school'), $data['event_id'] ?? 0, $data['lead_id'] ?? 0);
			$action	= sprintf('alert_model->event%sReferralSignup', ucwords($data['type'] ?? 'school'));
		} else {
			$code	= sprintf('event%sSignup_%s_%s', ucwords($data['type'] ?? 'school'), $data['event_id'] ?? 0, $data['lead_id'] ?? 0);
			$action	= sprintf('alert_model->event%sSignup', ucwords($data['type'] ?? 'school'));
		}

		$CI->Cron_model->add([
			'code'			=> $code,
			'action'		=> $action,
			'data'			=> [$data],
			'site_id'		=>  1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);
	}
}
