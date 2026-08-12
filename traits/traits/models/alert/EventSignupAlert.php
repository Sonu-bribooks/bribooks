<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventSignupAlert {
	// 1. Email
	// 2. Whatsapp
	// 3. CommunicationKit PDF (leaflet, user kit, teacher kit, ebrochure)

	public function eventSchoolSignup($data = []) {
		log_kb([
			'eventSchoolSignup' => $data
		]);

		$lead_info 	= $this->school_lead_model->get($data['lead_id']);
		$site_info 	= $this->site_model->get($lead_info['site_id']);
		$school_info= $this->school_model->get($lead_info['school_id']);
		$city_info 	= $this->city_model->get($site_info['city_id']);
		$event_info	= $this->event_model->get($lead_info['event_id']);

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		$communication_kit_info = $this->event_communication_kit_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0]['school'] ?? '';

		$brochure_info 			= $this->event_brochure_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0] ?? [];

		if (empty($communication_kit_info)) return;

		$communication_kit_info = json_decode($communication_kit_info, true);

		$state_id 	= $site_info['state_id'];
		$tag 		= $school_info['tag'];

		if (empty($communication_kit_info['email'])) {
			$communication_kit_info = self::_filterSchoolCommunication($communication_kit_info, $state_id, trim($tag));
			log_kb([
				'eventSchoolSignup-filter-info' => $communication_kit_info,
			]);
			if (empty($communication_kit_info)) return;
		}

		$student_url 			= vsprintf('%sevents/student/signup/%s?sid=%d', [
			$event_info['url'],
			$event_info['slug'],
			$site_info['id']
		]);

		// log_kb(compact('data', 'communication_kit_info'));

		$school_dashboard_url   = USER_SCHOOL_URL . 'login';
		$teacher_dashboard_url  = USER_SCHOOL_URL . 'teacher/login';
		$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

		$data = [
			'site_id'	  			=> $site_info['id'],
			'school_id'	  			=> $lead_info['school_id'] ?? 0,
			'event_id'	  			=> $lead_info['event_id'],
			'authorized_person'	  	=> $site_info['authorized_person'],
			'school_name'	  		=> $lead_info['name'],
			'owner_name'	  		=> $site_info['owner_name'],
			'email'	  				=> $site_info['owner_email'],
			'mobile'	  			=> $site_info['owner_mobile'],
			'state' 				=> $city_info['state'],
			'city' 					=> $city_info['name'],
			'designation' 			=> strtoupper($lead_info['designation']),
			'student_url' 			=> $student_url,
			'qrcode_url' 			=> $qrcode_url,
			'event_slug'			=> $event_info['slug'],
			'qrcode_file' 			=> sprintf('<div class="text-center"><img style="width: 100px;" src="%s" alt="Registration QR Code"></div>', $qrcode_url),
			'student_url_link' 		=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
			'school_dashboard_url' 	=> $school_dashboard_url,
			'teacher_dashboard_url' => $teacher_dashboard_url
		];

		log_kb(['eventSchoolSignup' => $data]);

		$subject 		= format_message_with_data($communication_kit_info['email']['subject'], $data) ?? '';
		$message 		= format_message_with_data($communication_kit_info['email']['message'], $data) ?? '';
		$attachments 	= self::_getCommunicationKitPDF($data, $communication_kit_info['email']['attachment'], $brochure_info);

		self::_sendEventSignupEmail(
			$data['email'],
			$subject,
			$message,
			$attachments,
			$site_info['country_code']
		);

		$attachments 	= self::_getCommunicationKitPDF($data, [$communication_kit_info['whatsapp']['attachment']], $brochure_info);

		self::_sendEventSignupWhatsapp(
			$data['mobile'],
			$communication_kit_info['whatsapp']['template'],
			$communication_kit_info['whatsapp']['message'],
			$data,
			$attachments[0] ?? ''
		);
	}

	public function eventTeacherSignup($data = []) {
		$lead_info = $this->teacher_lead_model->get($data['lead_id']);
		$user_info = $this->user_model->get($lead_info['teacher_id']);
		$site_info = $this->site_model->get($lead_info['site_id']);
		$city_info = $this->city_model->get($site_info['city_id']);
		$event_info= $this->event_model->get($lead_info['event_id']);

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		$communication_kit_info = $this->event_communication_kit_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0]['teacher'] ?? '';

		$brochure_info 			= $this->event_brochure_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0] ?? [];

		if (empty($communication_kit_info)) return;

		$communication_kit_info = json_decode($communication_kit_info, true);

		$student_url 			= vsprintf('%sevents/student/signup/%s?sid=%d', [
			$event_info['url'],
			$event_info['slug'],
			$site_info['id']
		]);

		// log_kb(compact('data', 'communication_kit_info'));

		$school_dashboard_url   = USER_SCHOOL_URL . 'login';
		$teacher_dashboard_url  = USER_SCHOOL_URL . 'teacher/login';
		$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

		$data = [
			'site_id'	  			=> $site_info['id'],
			'school_id'	  			=> $lead_info['school_id'] ?? 0,
			'event_id'	  			=> $lead_info['event_id'],
			'name'					=> $lead_info['name'],
			'school_name'			=> $site_info['name'],
			'grade'					=> $lead_info['grades'],
			'section'				=> $lead_info['sections'],
			'email'	  				=> $user_info['email'],
			'mobile'	  			=> $user_info['mobile'],
			'state' 				=> $city_info['state'],
			'city' 					=> $city_info['name'],
			'student_url' 			=> $student_url,
			'qrcode_url' 			=> $qrcode_url,
			'event_slug'			=> $event_info['slug'],
			'qrcode_file' 			=> sprintf('<div class="text-center"><img style="width: 100px;" src="%s" alt="Registration QR Code"></div>', $qrcode_url),
			'student_url_link' 		=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
			'school_dashboard_url' 	=> $school_dashboard_url,
			'teacher_dashboard_url' => $teacher_dashboard_url
		];

		$subject 		= format_message_with_data($communication_kit_info['email']['subject'], $data) ?? '';
		$message 		= format_message_with_data($communication_kit_info['email']['message'], $data) ?? '';
		$attachments 	= self::_getCommunicationKitPDF($data, $communication_kit_info['email']['attachment'], $brochure_info);

		self::_sendEventSignupEmail(
			$data['email'],
			$subject,
			$message,
			$attachments,
			$site_info['country_code']
		);

		$attachments 	= self::_getCommunicationKitPDF($data, [$communication_kit_info['whatsapp']['attachment']], $brochure_info);

		self::_sendEventSignupWhatsapp(
			$data['mobile'],
			$communication_kit_info['whatsapp']['template'],
			$communication_kit_info['whatsapp']['message'],
			$data,
			$attachments[0] ?? ''
		);
	}

	public function eventUserSignup($data = []) {
		$lead_info = $this->lead_model->get($data['lead_id']);
		$user_info = $this->user_model->get($lead_info['student_id']);
		$site_info = $this->site_model->get($lead_info['site_id']);
		$city_info = $this->city_model->get($site_info['city_id']);
		$event_info= $this->event_model->get($lead_info['event_id']);

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		$communication_kit_info = $this->event_communication_kit_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0] ?? [];

		$brochure_info 			= $this->event_brochure_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0] ?? [];

		if (empty($communication_kit_info['user'])) return;

		$message_type = (strpos($lead_info['utm_medium'], 'mobile') !== false) ? 'mobile' : 'laptop';

		$user_communication_kit_info 				= json_decode($communication_kit_info['user'], true);
		$user_early_access_communication_kit_info 	= json_decode($communication_kit_info['user_early_access'], true);

		$student_url 			= vsprintf('%sevents/student/signup/%s?sid=%d', [
			$event_info['url'],
			$event_info['slug'],
			$site_info['id']
		]);

		// log_kb(compact('data', 'communication_kit_info'));

		$school_dashboard_url   = USER_SCHOOL_URL . 'login';
		$teacher_dashboard_url  = USER_SCHOOL_URL . 'teacher/login';
		$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

		$buyer_book_name 			= '';
		$buyer_book_author_name 	= '';

		if (strpos($user_info['source'], 'buyer') !== false) {
			$source_id = preg_replace('/\D/', '', $user_info['source']);

			if (!empty($source_id) && !empty($buyer_book_info = $this->book_model->get($source_id ?? 0))){
				$buyer_book_name 			= $buyer_book_info['name'];
				$buyer_book_author_name 	= $buyer_book_info['author_name'];
			}
		}

		$reviewer_book_name 			= '';
		$reviewer_book_author_name 		= '';

		if (strpos($user_info['source'], 'reviewer') !== false) {
			$source_id = preg_replace('/\D/', '', $user_info['source']);

			if (!empty($source_id) && !empty($reviewer_book_info = $this->book_model->get($source_id ?? 0))){
				$reviewer_book_name 			= $reviewer_book_info['name'];
				$reviewer_book_author_name 		= $reviewer_book_info['author_name'];
			}
		}

		$password = uniqid();
		$encoded_password = sha1(md5($password . $this->config->item('password_salt')));

		$this->student_model->edit($user_info['id'], [
			'password'			=> $encoded_password,
		]);

		$data = [
			'site_id'	  				=> $site_info['id'],
			'school_id'	  				=> $lead_info['school_id'] ?? 0,
			'event_id'	  				=> $lead_info['event_id'],
			'name'						=> $lead_info['name'],
			'grade'						=> $lead_info['grades'],
			'section'					=> $lead_info['sections'],
			'email'	  					=> $user_info['email'],
			'mobile'	  				=> $user_info['mobile'],
			'parent_name' 				=> $user_info['parent_name'],
			'username' 					=> $user_info['username'],
			'buyer_book_name' 			=> $buyer_book_name,
			'buyer_book_author_name' 	=> $buyer_book_author_name,
			'reviewer_book_name' 		=> $reviewer_book_name,
			'reviewer_book_author_name' => $reviewer_book_author_name,
			'password' 					=> $password,
			'state' 					=> $city_info['state'],
			'city' 						=> $city_info['name'],
			'student_url' 				=> $student_url,
			'event_slug'				=> $event_info['slug'],
			'student_url_link' 			=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
			'qrcode_url' 				=> $qrcode_url,
			'school_dashboard_url' 		=> $school_dashboard_url,
			'teacher_dashboard_url' 	=> $teacher_dashboard_url
		];

		if ((strpos($lead_info['utm_source'], 'earlyaccess') !== false) && !empty($user_early_access_communication_kit_info)) {
			$filter_communication_kit_info = self::_filterUserCommunication($user_early_access_communication_kit_info, $user_info['id'], $message_type);

			// log_kb([
			// 	'early_acces::filter_kit_info' => $filter_communication_kit_info
			// ]);

			if (!empty($filter_communication_kit_info)) {
				$user_communication_kit_info = $filter_communication_kit_info;

				// log_kb([
				// 	'early_acces::kit_info' => $filter_communication_kit_info
				// ]);
			}
		}

		if ($message_type == 'mobile') {
			$communication_email_subject 		= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['mobile_email_earlyaccess']['subject'] : $user_communication_kit_info['mobile_email']['subject'];
			$communication_email_message 		= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['mobile_email_earlyaccess']['message'] : $user_communication_kit_info['mobile_email']['message'];
			$communication_whatsapp_template 	= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['mobile_whatsapp_earlyaccess']['template'] : $user_communication_kit_info['mobile_whatsapp']['template'];
			$communication_whatsapp_message 	= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['mobile_whatsapp_earlyaccess']['message'] : $user_communication_kit_info['mobile_whatsapp']['message'];
		} else {
			$communication_email_subject 		= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['laptop_email_earlyaccess']['subject'] : $user_communication_kit_info['laptop_email']['subject'];
			$communication_email_message 		= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['laptop_email_earlyaccess']['message'] : $user_communication_kit_info['laptop_email']['message'];
			$communication_whatsapp_template 	= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['laptop_whatsapp_earlyaccess']['template'] : $user_communication_kit_info['laptop_whatsapp']['template'];
			$communication_whatsapp_message 	= (strpos($lead_info['utm_source'], 'earlyaccess') !== false) ? $user_communication_kit_info['laptop_whatsapp_earlyaccess']['message'] :  $user_communication_kit_info['laptop_whatsapp']['message'];
		}


		$subject 		= format_message_with_data($communication_email_subject, $data) ?? '';
		$message 		= format_message_with_data($communication_email_message, $data) ?? '';
		// $attachments 	= self::_getCommunicationKitPDF($data, $communication_kit_info['email']['attachment'], $brochure_info);

		self::_sendEventSignupEmail(
			$data['email'],
			$subject,
			$message,
			[],
			$site_info['country_code']
		);

		// $attachments 	= self::_getCommunicationKitPDF($data, [$communication_kit_info['whatsapp']['attachment']], $brochure_info);

		self::_sendEventSignupWhatsapp(
			$data['mobile'],
			$communication_whatsapp_template,
			$communication_whatsapp_message,
			$data,
			''
		);
	}

	public function eventUserReferralSignup($data = []) {
		$lead_info 			= $this->lead_model->get($data['lead_id']);
		$user_info 			= $this->user_model->get($lead_info['student_id']);
		$referrer_user_info = $this->user_model->get($lead_info['parent_referral_id']);
		$site_info 			= $this->site_model->get($lead_info['site_id']);
		$city_info 			= $this->city_model->get($site_info['city_id']);
		$event_info			= $this->event_model->get($lead_info['event_id']);

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('user/UserReferral_model', 'user_referral_model');

		$communication_kit_info = $this->event_communication_kit_model->get_all([
			'event_id' => $lead_info['event_id']
		])['rows'][0] ?? [];

		if (empty($communication_kit_info['user'])) return;

		$message_type = (strpos($lead_info['utm_medium'], 'mobile') !== false) ? 'mobile' : 'laptop';

		$user_communication_kit_info = json_decode($communication_kit_info['user_referral'], true);

		foreach ($user_communication_kit_info as $kit_info) {
			log_kb([
				'referral_kit_info::type' => $kit_info['type']
			]);
			$student_url 			= vsprintf('%sevents/student/signup/%s?sid=%d', [
				$event_info['url'],
				$event_info['slug'],
				$site_info['id']
			]);

			// log_kb(compact('data', 'communication_kit_info'));

			$school_dashboard_url   = USER_SCHOOL_URL . 'login';
			$teacher_dashboard_url  = USER_SCHOOL_URL . 'teacher/login';
			$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

			$password = uniqid();
			$encoded_password = sha1(md5($password . $this->config->item('password_salt')));

			if ($kit_info['message_type'] == 'referral_message') {
				$this->student_model->edit($user_info['id'], [
					'password'			=> $encoded_password,
				]);
				$email 	= $user_info['email'] ?? '';
				$mobile = $user_info['mobile'] ?? '';
			} else {
				$email 	= $referrer_user_info['email'] ?? '';
				$mobile = $referrer_user_info['mobile'] ?? '';
			}

			$event_user_info = $this->event_user_model->getEventUserByUserId($lead_info['event_id'], $user_info['id']);

			$user_code_info = $this->event_user_invite_code_model->get_all([
				'event_id'	  => $lead_info['event_id'],
				'user_id'	  => $lead_info['parent_referral_id'],
			])['rows'][0] ?? [];

			$referral_count = $this->user_referral_model->get_all([
				'event_id' 		=> (int)$lead_info['event_id'],
				'referrer_id' 	=> (int)$lead_info['parent_referral_id'],
			])['total'] ?? 0;

			$remaining_count = $user_code_info['referral_limit'] - $referral_count;

			if ($remaining_count > 0 && $kit_info['type'] == 'referrer_limit_exceed') continue;
			if ($kit_info['type'] == 'referrer_limit_revoked') continue;

			$data = [
				'site_id'	  				=> $site_info['id'],
				'school_id'	  				=> $lead_info['school_id'] ?? 0,
				'event_id'	  				=> $lead_info['event_id'],
				'name'						=> $lead_info['name'],
				'grade'						=> $lead_info['grades'],
				'section'					=> $lead_info['sections'],
				'email'	  					=> $email,
				'mobile'	  				=> $mobile,
				'referrer_name'	  			=> $referrer_user_info['first_name'] . ' ' . $referrer_user_info['last_name'],
				'parent_name' 				=> $user_info['parent_name'],
				'username' 					=> $user_info['username'],
				'password' 					=> $password,
				'state' 					=> $city_info['state'],
				'city' 						=> $city_info['name'],
				'event_slug' 				=> $event_info['slug'],
				'student_url' 				=> $student_url,
				'student_url_link' 			=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
				'qrcode_url' 				=> $qrcode_url,
				'school_dashboard_url' 		=> $school_dashboard_url,
				'teacher_dashboard_url' 	=> $teacher_dashboard_url,
				'count' 					=> $remaining_count,
				'datetime' 					=> $event_user_info['date_added'],
				'time' 						=> $event_user_info['date_added'],
				'date'						=> !empty($event_user_info['date_added']) ? date('M j, Y', strtotime($event_user_info['date_added'])) : '',
				'time_format'				=> !empty($event_user_info['date_added']) ? date('h:i A', strtotime($event_user_info['date_added'])) : '',
			];

			if ($message_type == 'mobile') {
				$communication_email_subject 		= $kit_info['mobile_email']['subject'];
				$communication_email_message 		= $kit_info['mobile_email']['message'];
				$communication_whatsapp_template 	= $kit_info['mobile_whatsapp']['template'];
				$communication_whatsapp_message 	= $kit_info['mobile_whatsapp']['message'];
			} else {
				$communication_email_subject 		= $kit_info['laptop_email']['subject'];
				$communication_email_message 		= $kit_info['laptop_email']['message'];
				$communication_whatsapp_template 	= $kit_info['laptop_whatsapp']['template'];
				$communication_whatsapp_message 	= $kit_info['laptop_whatsapp']['message'];
			}

			log_kb([
				'referral_kit_info::subject' => $communication_email_subject,
				'referral_kit_info::message' => $communication_email_message,
			]);

			$subject 		= format_message_with_data($communication_email_subject, $data) ?? '';
			$message 		= format_message_with_data($communication_email_message, $data) ?? '';

			self::_sendEventSignupEmail(
				$data['email'],
				$subject,
				$message,
				[],
				$site_info['country_code']
			);

			self::_sendEventSignupWhatsapp(
				$data['mobile'],
				$communication_whatsapp_template,
				$communication_whatsapp_message,
				$data,
				'',
				'onextel'
			);
		}
	}

	private function _sendEventSignupEmail($to = '', $subject = '', $message = '', $attachments = [], $country_code = '') {
		$data['title']		  	= $subject;
		$data['content']		= $message;

		if (!empty($country_code) && in_array(strtolower($country_code), ['us', 'usa'])) {
			$message				= $this->load->view('common/mail/templates/event/content', $data, true);
		} else {
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);
		}

		$email  = $to;

		log_kb(compact('email', 'subject', 'attachments'));

		self::email(
			$email,
			$subject,
			$message,
			[],
			ENVIRONMENT === 'production'
				? ['communication@bribooks.com']
				: [],
			$attachments
		);
	}

	private function _sendEventSignupWhatsapp($to = '', $template_id = '', $message = '', $variables = [], $attachment = '', $gateway = 'onextel') {
		$mobile = ENVIRONMENT === 'production' ? $to : '917367916262';

		if ($gateway == 'onextel') {
			return self::sendOnextelWhatsappMessage(
				$mobile,
				[
					'template_id'	=> $template_id,
					'parameters'	=> format_whatsapp_sms_message($message, $variables),
				]
			);
		} else {
			if (!empty($attachment)) {
				return self::_sendWhatsappDocument(
					$mobile,
					[
						'template'		=> $template_id,
						'parameters'	=> self::_formatMarketingWhatsappMessage($message, $variables),
						'document'	=> [
							'name'	=> 'BriBooks.pdf',
							'link'	=> str_replace('var/www/html/', '', base_url($attachment))

						]
					]
				);
			}

			return self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> $template_id,
					'parameters'	=> format_whatsapp_sms_message($message, $variables),
				]
			);
		}
	}

	private function _getCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		if (empty($attachment_types)) return [];

		$attachments = [];

		foreach ($attachment_types as $attachment_type) {
			$attachment_type 	= preg_replace(['/[^a-zA-Z0-9\s]/', '/\s+/'], [' ', ''], $attachment_type);
			$method 			= sprintf('_create%sCommunicationKitPDF', ucwords($attachment_type));

			if (method_exists($this, $method)) {
				$attachments[] = self::{$method}($data, $attachments, $brochure_info);
			}
		}

		return $attachments;
	}

	private function _createUserCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		if (empty($data)) return;

		$dir = FCPATH . 'uploads/communication_kit/user';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view(
			'common/communication_kit/user/content',
			[
				'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
				'content' 	=> format_message_with_data($brochure_info['user_content'], $data),
				'header' 	=> $brochure_info['user_header'],
				'footer' 	=> $brochure_info['user_footer'],
			],
			true
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file = vsprintf('uploads/communication_kit/user/communication_kit_user_%d_%d.pdf',[
			$data['event_id'],
			$data['site_id']
		]);
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _createTeacherCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		if (empty($data)) return;

		$dir = FCPATH . 'uploads/communication_kit/teacher';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view(
			'common/communication_kit/teacher/content',
			[
				'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
				'content' 	=> format_message_with_data($brochure_info['teacher_content'], $data),
				'header' 	=> $brochure_info['teacher_header'],
				'footer' 	=> $brochure_info['teacher_footer'],
			],
			true
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file = vsprintf('uploads/communication_kit/teacher/communication_kit_teacher_%d_%d.pdf',[
			$data['event_id'],
			$data['site_id']
		]);
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _createEbrochureCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		$dir = FCPATH . 'uploads/communication_kit/ebrochure';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');
		$data['brochures'] 	= json_decode($brochure_info['ebrochure'], true);
		$data['dynamic'] 	= $brochure_info['ebrochure_dynamic'];

		// $qrcode_url 	= str_replace('var/www/html/', '', base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schooltestqr_%s.png', $site_info['id']))));

		$data['student_url'] 	= str_replace('https://', '', $data['student_url']);
		$data['qrcode_url'] 	= base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id'])));


		$html = $this->load->view('common/communication_kit/ebrochure/v1', $data, true);

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper(
			[
				0,
				0,
				390,
				780
			],
			'portrait'
		);

		$dompdf->render();

		$file 	= vsprintf('uploads/communication_kit/ebrochure/Student_brochure_%s_%s.pdf',[
			$data['event_id'],
			$data['site_id']
		]);

		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _createLeafletCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		$dir = FCPATH . 'uploads/communication_kit/leaflet';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$data['student_url'] 	= str_replace('https://', '', $data['student_url']);
		$data['qrcode_url'] 	= base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id'])));

		$data['leaflet'] 	= $brochure_info['leaflet'];
		$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');

		$html = $this->load->view('common/communication_kit/leaflet/v1', $data, true);

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper('A4', 'potrait');

		$dompdf->render();

		$file 	= vsprintf('uploads/communication_kit/leaflet/Student_notification_%s_%s.pdf',[
			$data['event_id'],
			$data['site_id']
		]);

		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _filterSchoolCommunication($communication_kits = [], $state_id = 0, $tag = '') {
		$format_message = [];

		if (!empty($communication_kits)) {
			$format_message = array_values(array_filter($communication_kits, function($item) {
				return isset($item['region']) &&
					$item['region'] === 'ALL' &&
					(empty($item['tags']) || !isset($item['tags']));
			}));

			if (!empty($format_message[0])) {
				$format_message = $format_message[0];
			}

			foreach($communication_kits as $kit_info) {
				if (($kit_info['region'] ?? '') != 'ALL') {
					if (!empty($region_info = $this->group_region_model->get_all([
						'region_id' => $kit_info['region'] ?? 0,
						'state_id' 	=> $state_id,
					])['rows'][0] ?? []) && (!empty($region_info['state_name'] ?? ''))) {
						if (!empty($tag) && self::_applyTagFormatting($kit_info, $tag)) {
							$format_message = $kit_info;
							break;
						}

						$format_message = $kit_info;
						break;
					}
				} elseif (!empty($kit_info['tags'])) {
					if (!empty($tag) && self::_applyTagFormatting($kit_info, $tag)) {
						$format_message = $kit_info;
						break;
					}
				}
			}
		}

		return $format_message;
	}

	private function _applyTagFormatting($kit_info = [], $tag = '') {
		if (
			!empty($kit_info['tags']) &&
			in_array($tag, $kit_info['tags'])
		) {
			return true;
		}

		return false;
	}

	private function _filterUserCommunication($communication_kits = [], $user_id = 0, $type = '') {
		$format_message = [];

		if (!empty($communication_kits)) {
			$format_message = array_values(array_filter($communication_kits, function($item) {
				return (empty($item['tags']) || !isset($item['tags']));
			}));

			if (!empty($format_message[0])) {
				$format_message = $format_message[0];
			}

			foreach($communication_kits as $kit_info) {
				$tag = self::_applyUserTagFormatting($user_id, $kit_info['tags']);

				if (!empty($kit_info['tags'] ?? '') && ($tag)) {
					$format_message = $kit_info;
				}
			}
		}

		return $format_message;
	}

	private function _applyUserTagFormatting($user_id = 0, $kit_tag = []) {
		if (empty($user_info 	= $this->user_model->get($user_id))) return '';

		$tag 		= '';

		if (strpos($user_info['source'], 'reviewer') !== false) {
			$tag = 'Reviewer';
		}

		if (strpos($user_info['source'], 'buyer') !== false || !empty($this->order_model->get_all([
			'_deleted' 	=> 0,
			'user_id' 	=> $user_info['id'],
			'ne_status' => [0,91,92]
		])['rows'][0] ?? [])) {
			$tag = 'Buyer';
		}

		$written_book_info = $this->book_model->get_all([
			'_deleted' => 0,
			'archived' => 0,
			'user_id'  => $user_info['id'],
		])['rows'][0] ?? [];

		$published_book_info = $this->book_model->get_all([
			'_deleted' => 0,
			'archived' => 0,
			'status'   => 1,
			'user_id'  => $user_info['id'],
		])['rows'][0] ?? [];

		if (
			strpos($user_info['source'], 'buyer') === false &&
			strpos($user_info['source'], 'reviewer') === false &&
			empty($written_book_info)
		) {
			$tag = 'SNW';
		}

		if (!empty($written_book_info) && empty($published_book_info)) {
			$tag = 'WNP';
		}

		$total_sold = $this->order_model->getTopSoldBooks([
			'user_id'	=> $user_info['id']
		])['total'];

		if (!empty($published_book_info) && $total_sold > 0) {
			$tag = 'PNS';
		}

		if (
			!empty($kit_tag) &&
			in_array($tag, $kit_tag)
		) {
			return true;
		}

		return false;

	}

	private function _createPersonalnoteCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		if (empty($data)) return;

		$brochure_info['personal_note'] = json_decode($brochure_info['personal_note'], true);

		$dir = FCPATH . 'uploads/communication_kit/personal_note';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_pdf_rows = $this->event_pdf_model->get_all([
			'event_id' => $data['event_id'] ?? 0,
		])['rows'] ?? [];

		$event_pdf_info = !empty($event_pdf_rows) ? $event_pdf_rows[array_rand($event_pdf_rows)] : '';

		if (!empty($event_pdf_info) && !empty($event_pdf_info['content'])) {
			CI_Events::trigger('access_log', [
				'module'	=> sprintf('event_random_pdf_%s_%s' , $event_pdf_info['id'], ($data['user_id'] ?? 0)),
			]);

			$html = $this->load->view(
				'common/communication_kit/user/content',
				[
					'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
					'content' 	=> format_message_with_data($event_pdf_info['content'], $data),
					'header' 	=> $brochure_info['personal_note']['header'],
					'footer' 	=> $brochure_info['personal_note']['footer'],
				],
				true
			);

		} else {
			CI_Events::trigger('access_log', [
				'module'	=> 'event_random_pdf_default_%s' . ($data['user_id'] ?? 0),
			]);

			$html = $this->load->view(
				'common/communication_kit/user/content',
				[
					'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
					'content' 	=> format_message_with_data($brochure_info['personal_note']['body'], $data),
					'header' 	=> $brochure_info['personal_note']['header'],
					'footer' 	=> $brochure_info['personal_note']['footer'],
				],
				true
			);
		}

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file = vsprintf('uploads/communication_kit/user/communication_kit_personal_note_%d_%d.pdf',[
			$data['event_id'],
			$data['book_id']
		]);
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}
}
