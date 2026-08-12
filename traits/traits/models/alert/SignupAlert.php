<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait SignupAlert{
	public function signup($id, $type = 'signup', $event_id = 0) {
		if ($type === 'competition') {
			self::cron($id, 'competitionSignupCron');
		} elseif (!empty($event_id) || $event_id != 0) {
			$this->cron_model->add([
				'code'			=> 'eventAuthorSignupCron_' . $id,
				'action'		=> 'alert_model->eventAuthorSignupCron',
				'data'			=> [$id, $type, $event_id],
				'alert_date'	=> date('Y-m-d H:i:s'),
			]);

			$this->cron_model->add([
				'code'			=> 'eventAuthorSignupTnc_' . $id,
				'action'		=> 'alert_model->eventAuthorSignupTnc',
				'data'			=> [$id, $type, $event_id],
				'alert_date'	=> date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime(date('Y-m-d H:i:s')))),
			]);
		} else {
			self::cron($id, 'signupCron');
		}
	}

	public function signupCron($id = 0) {
		log_kb([
			'signupCron::' => [
				'id'		=> $id
			]
		]);

		if ($info = $this->student_model->get($id)) {
			if (!empty($info['source']) && (in_array(strtolower($info['source']), ['bookstore', 'referral']))) {
				return false;
			}

			$site_id = $template_site_id = $info['site_id'];

			$book_name 			= 'This Book';
			$book_author_name 	= 'This Author';

			$this->load->model('event/EventUser_model', 'event_user_model');

			$filter_data 				= [];
			$filter_data['sort'] 		= 'event_user.id';
			$filter_data['order'] 		= 'DESC';
			$filter_data['event_id'] 	= 10;
			$filter_data['user_id'] 	= (int)$id;
			$event_users = $this->event_user_model->get_all($filter_data);

			if (!empty($event_users['rows'][0])) {
				$site_id = 2273;
			}

			$site_info = $this->site_model->get($site_id);

			if (empty($site_info)) return;

			// generate password and store in db
			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

			$this->student_model->edit($id, [
				'password'			=> $encoded_password,
				'verification_code'	=> $verification_code
			]);

			if (!empty($info['parent_referral_id']) && !empty($referral_info = $this->student_model->get($info['parent_referral_id']))) {
				$template = 'email_referral_user_signup';
				$referral_name = trim($referral_info['first_name'] . ' ' . $referral_info['last_name']);
			} else {
				if (strpos($info['source'], 'buyer') !== false) {
					$source_id = preg_replace('/\D/', '', $info['source']);

					if (!empty($source_id) && !empty($book_info = $this->book_model->get($source_id ?? 0))){
						$book_name 			= $book_info['name'];
						$book_author_name 	= $book_info['author_name'];
					}

					$template = 'email_buyer_signup';
				} elseif ($info['source'] == 'signup_mobile') {
					$template = 'email_user_signup_mobile';
				} else {
					$template = 'email_user_signup';
				}

				$referral_name = '';
			}

			if ($site_info['site_type'] == 2) {
				$template = 'email_user_signup_nursery';
			} elseif ($site_info['site_type'] == 3) {
				$template = 'email_user_signup_university';
			} elseif ($site_info['site_type'] == 4) {
				$template = 'email_user_signup_community';
				$template_site_id = $site_info['parent_id'];
			}

			$title = vsprintf(_li('Welcome to %s, your gateway to becoming a globally published author.'), [
				get_settings('system_name')
			]);

			$title = self::formatEmailSubject($template, $template_site_id, [
				'author_name'		=> trim($info['first_name'] . ' ' . $info['last_name']),
				'parent_name'		=> $info['parent_name'],
				'school_name'		=> $site_info['name'],
			]) ?? $title;

			log_kb([
				'template' 	=> $template,
				'title' 	=> $title
			]);

			$reset_url = vsprintf(USER_URL . 'resetpassword?uid=%s&code=%s', [
				$info['id'],
				$verification_code,
			]);

			$login_url = USER_URL . 'login?tab=username';

			$data['title']			= $title;
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'parent_name'		=> $info['parent_name'] ?? trim($info['first_name'] . ' ' . $info['last_name']),
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'username'			=> $info['username'],
				'school_name'		=> $site_info['name'],
				'referral_name'		=> $referral_name,
				'password'			=> $password,
				'url'				=> $reset_url,
				'url_2'				=> $login_url,
				'email'				=> $info['email'],
				'mobile'			=> $info['mobile'],
				'book_name' 		=> $book_name,
				'book_author_name' 	=> $book_author_name,
			], $site_id);
			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['link']			= '';
			$data['link_text']		= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($info['email']);

			// log_kb([
			// 	'signupCron' 	=> 'signupCron',
			// 	'site_info' 	=> $site_info,
			// 	'template' 		=> $template,
			// 	'title' 		=> $title,
			// 	'message' 		=> $data['content']
			// ]);

			$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

			$mobile = $info['mobile'];
			$email 	= $info['email'];

			$attachment = [];

			$whatsapp_temp_id 	= '';
			$whatsapp_param 	= [];

			if ($mobile && (strpos(strtolower($site_info['site_code']), NYAF_IN_SITE_CODE) !== false)) {
				if (!empty($info['parent_referral_id']) && !empty($referral_info = $this->student_model->get($info['parent_referral_id']))) {

					$whatsapp_temp_id		= '672353495003506';
					$whatsapp_param	= [
						trim($info['first_name'] . ' ' . $info['last_name']),
						$info['username'],
						$password,
						$info['mobile']
					];
				}
			} else {
				if (strpos($info['source'], 'buyer') !== false) {
					$whatsapp_temp_id		= '01kcrsq6renp9tz6qgh2z8ec0f';
					// $whatsapp_temp_id		= '2916754095139842';
					$whatsapp_param	= [
						trim($info['first_name'] . ' ' . $info['last_name']),
						$book_name,
						$book_author_name,
					];
				} elseif ($info['source'] == 'signup_desktop') {
					$whatsapp_temp_id		= '01kcrsmsgdxqhrtcav86ya9tr6';
					// $whatsapp_temp_id		= '3773945916162458';
					$whatsapp_param	= [
						trim($info['first_name'] . ' ' . $info['last_name']),
						$info['email']
					];
				} elseif ($info['source'] == 'signup_mobile') {
					$whatsapp_temp_id		= '01kcrrrgemxm6v9am7315f3rfn';
					// $whatsapp_temp_id		= '555644803508253';
					$whatsapp_param	= [
						trim($info['first_name'] . ' ' . $info['last_name']),
						$info['email']
					];
				}
			}

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				[],
				$attachment
			);

			if (!empty($mobile) && !empty($whatsapp_temp_id) && !empty($whatsapp_param)) {
				// self::_sendWhatsappText(
				// 	$mobile,
				// 	[
				// 		'template'		=> $whatsapp_temp_id,
				// 		'parameters'	=> $whatsapp_param
				// 	],
				// );

				self::sendOnextelWhatsappMessage(
					$mobile,
					[
						'template_id'	=> $whatsapp_temp_id,
						'parameters'	=> $whatsapp_param
					],
				);
			}
		}
	}

	public function tncSignupCron($id = 0) {
		log_kb([
			'tncSignupCron::' => [
				'id'		=> $id
			]
		]);

		if ($info = $this->student_model->get($id)) {
			$site_id 	= $info['site_id'];
			$site_info 	= $this->site_model->get($site_id);

			if (empty($site_info)) return;

			$template = 'email_user_tnc';

			if ($site_info['site_type'] == 2) {
				$template = 'email_user_tnc_nursery';
			} elseif ($site_info['site_type'] == 3) {
				$template = 'email_user_tnc_university';
			}

			$title = self::formatEmailSubject($template, $site_id, [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'parent_name'		=> $info['parent_name']
			]) ?? '';

			if (empty($title)) return;

			$data['title']			= $title;
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name']
			], $site_id);
			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['link']			= '';
			$data['link_text']		= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($info['email']);

			$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

			$mobile = $info['mobile'];
			$email 	= $info['email'];

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				[],
				[]
			);
		}
	}

	public function competitionSignupCron($id = 0) {
		if ($info = $this->student_model->get($id)) {
			$site_id 	= $info['site_id'];
			$site_info 	= $this->site_model->get($site_id);

			// generate password and store in db
			$password = uniqid();
			$encoded_password = sha1(md5($password . $this->config->item('password_salt')));
			$verification_code = sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

			$this->student_model->edit($id, [
				'password'			=> $encoded_password,
				'verification_code'	=> $verification_code
			]);

			$template = 'email_competition_signup';

			$data['title']			= self::formatEmailSubject($template, $site_id) ?? vsprintf(_li('Welcome to %s, your gateway to becoming a globally published author.'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'username'			=> $info['username'],
				'password'			=> $password,
				'url'				=> vsprintf(USER_URL . 'resetpassword?uid=%s&code=%s', [
					$info['id'],
					$verification_code,
				])
			], $site_id);
			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['link']			= '';
			$data['link_text']		= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($info['email']);

			if(!empty($site_info['site_code']) && (strpos(strtolower($site_info['site_code']), ISRAEL_SITE_CODE) !== false)) {
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
			} else {
				$message 				= $this->load->view('common/mail/templates/2/general', $data, true);
			}

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function mobileSignupCron($id = 0) {
		if ($info = $this->student_model->get($id)) {
			$site_id   = $info['site_id'];
			$site_info = $this->site_model->get($site_id);

			// generate password and store in db
			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

			$this->student_model->edit($id, [
				'password'			=> $encoded_password,
				'verification_code'	=> $verification_code
			]);

			$template = 'email_user_signup_with_mobile';

			$data['title']			= self::formatEmailSubject($template, $site_info['parent_id']) ?? vsprintf(_li('Welcome to %s, your gateway to becoming a globally published author.'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'username'			=> $info['username'],
				'password'			=> $password,
				'url'				=> vsprintf(USER_URL . 'resetpassword?uid=%s&code=%s', [
					$info['id'],
					$verification_code,
				])
			], $site_id);
			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['link']			= '';
			$data['link_text']		= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($info['email']);

			if (!empty($site_info['site_code']) && (strpos(strtolower($site_info['site_code']), ISRAEL_SITE_CODE) !== false)) {
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
			} else {
				$message 				= $this->load->view('common/mail/templates/2/general', $data, true);
			}

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function enrolUserEventCron($event_id = false, $id = false, $lead_id = false) {
		$this->load->model('event/Event_model', 'event_model');

		if (
			($event_info = $this->event_model->get($event_id)) &&
			($info = $this->student_model->get($id)) &&
			($lead_info = $this->lead_model->get($lead_id))
		) {
			$reward_url = SC_USER_ADDRESS_URL . 'india/2024/rewards';

			if (strtolower(trim($lead_info['utm_medium'])) == 'desktop') {
				$subject 	= 'Congratulations! You Are Now Enrolled in the World\'s Largest Summer Camp';
				$template 	= '379787081570580';

				$message			= $this->load->view('common/mail/part/event_signup', [
					'author_name' 	=> ucwords($info['first_name'].' '.$info['last_name']),
					'reward_url' 	=> $reward_url,
					'mobile' 		=> $info['mobile'],
				], true);

				$parameters = [
					ucwords(trim($info['first_name'] . ' ' . $info['last_name'])),
					$reward_url,
					$info['mobile'],
				];
			} elseif (strtolower(trim($lead_info['utm_medium'])) == 'mobile') {
				$subject 	= 'Congratulations! You Are Now Enrolled in the World\'s Largest Summer Camp';
				$template 	= '781725980102313';

				$message			= $this->load->view('common/mail/part/event_signup_mobile', [
					'author_name' 	=> ucwords($info['first_name'].' '.$info['last_name']),
					'reward_url'   	=> $reward_url,
					'mobile' 		=> $info['mobile'],
				], true);

				$parameters = [
					ucwords(trim($info['first_name'] . ' ' . $info['last_name'])),
					$reward_url,
					'https://apps.apple.com/us/app/bribooks/id6448090977'	,
					$info['mobile'],
				];
			} else {
				return;
			}

			$mobile = $info['mobile'];
			$email 	= $info['email'];

			self::_getAuthorTCPDF($info['id'], [
				'author_name' => ucwords(trim($info['first_name'] . ' ' . $info['last_name']))
			]);

			$attachment		  	= [
				FCPATH . 'uploads/termandconditions/author/author_'.$info['id'].'.pdf'
			];

			self::email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				$attachment
			);

			if ($mobile) {
				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> $template,
						'parameters'	=> $parameters
					],
				);
			}
		}
	}

	private function _getAuthorTCPDF($id = '', $data = []) {
		$dir = FCPATH . 'uploads/termandconditions/author';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/author_term_and_conditions', [
			'author_name' 		=> $data['author_name'],
			'letterhead_head' 	=> base_url('assets/images/sc_logo.png')
		], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file 	= 'uploads/termandconditions/author/author_' . $id . '.pdf';
		$output = $dompdf->output();

		file_put_contents(FCPATH.$file, $output);

		return base_url($file);
	}

	public function referralStudentSignup($site_id = 0, $event_id = 0, $student_id = 0, $referral_id = 0) {
		if (
			$site_id &&
			($site_info = $this->site_model->getSchoolBySiteId($site_id)) &&
			($referral_info = $this->student_model->get($referral_id)) &&
			($student_info = $this->student_model->get($student_id))
		) {
			$mobile = $referral_info['mobile'];
			$email 	= $referral_info['email'];

			$total_registered = $this->student_model->getReferralUser($referral_id)['total'] ?? 0;

			$subject = 'Your Impact: A Literary Success at NYAF!';
			$content = '';

			if ($email) {
				self::email(
					$email,
					$subject,
					$content,
					[],
					(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				);
			}
		}
	}

	public function inviteStudent($id) {
		self::cron($id, 'inviteStudentCron');
	}

	public function inviteStudentCron($id = 0, $event_id = 0) {
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');

		if ($info = $this->student_model->get($id)) {
			$template_filter = [
				'event_id'	  	=> $event_id,
				'template_type' => 'email_student_invite',
			];

			$teacher_name 	= 'Class Teacher';

			if (strpos($info['source'], 'teacher_dashboard') !== false) {
				$source_id = preg_replace('/\D/', '', $info['source']);

				if (!empty($source_id) && !empty($teacher_info = $this->teacher_model->get($source_id ?? 0))){
					$teacher_name 	= ucwords($teacher_info['first_name'] . ' ' . $teacher_info['last_name']);
				}
			}

			$school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';
			$event_info			 	= $this->event_model->get($event_id);

			$site_id = $info['site_id'];

			$site_info = $this->site_model->get($site_id);

			if (!empty($school_template_info)) {
				$this->load->model('localisation/State_model', 'state_model');
				$this->load->model('localisation/City_model', 'city_model');

				$state_info = $this->state_model->get($info['state_id']);
				$city_info  = $this->city_model->get($info['city_id']);

				$password		 	= uniqid();
				$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
				$verification_code 	= sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

				$this->teacher_model->edit($id, [
					'password'			=> $encoded_password,
					'verification_code'	=> $verification_code
				]);

				if ($site_info['site_type'] == 5) {
					$url			= sprintf('%s/v2/student/signup/%s?uid=%s&code=%s&utm_source=nyaf2024_%s', $event_info['url'], $site_info['id'], $info['id'], $verification_code, $id);
				} else {
					$url			= sprintf('%s/student/signup/%s?uid=%s&code=%s&utm_source=nyaf2024_%s', $event_info['url'], $site_info['id'], $info['id'], $verification_code, $id);
				}

				$variables = [
					'author_name'			=> trim($info['first_name'] . ' ' . $info['last_name']),
					'name'					=> $teacher_name,
					'site_id'	  			=> $site_info['id'] ?? 0,
					'event_id'	  			=> $event_id,
					'authorized_person'	  	=> $site_info['authorized_person'] ?? '',
					'school_name'			=> $site_info['name'] ?? '',
					'owner_name'	  		=> $site_info['owner_name'] ?? '',
					'email'	  				=> $info['email'] ?? '',
					'mobile'	  			=> $info['mobile'] ?? '',
					'state' 				=> $state_info['name'] ?? '',
					'city' 					=> $city_info['name'] ?? '',
					'url'					=> $url,
				];

				$subject = self::formatCommonEmailSubject($school_template_info['subject'], $variables) ?? '';

				$content = self::formatCommonEmailContent($school_template_info['body'], $variables) ?? '';

				$data['title']		  	= $subject;
				$data['heading']		= '';
				$data['subheading']	 	= '';
				$data['subheading']		= '';
				$data['content']		= $content;
				$data['site_id']		= $site_id;
				$data['site_code']		= $site_info['site_code'];
				$data['link']			= '';
				$data['link_text']		= '';
				$data['unsubscribe_url']= gen_unsubscribe_url($info['email']);

				$message				= $this->load->view('common/mail/templates/site/general', $data, true);

				$attachment = [];

				$email  = $info['email'];
				$mobile = $info['mobile'];

				if (!empty($subject) && !empty($content)) {
					self::email(
						$email,
						$subject,
						$message,
						[],
						ENVIRONMENT === 'production'
							? ['communication@bribooks.com']
							: [],
						$attachment
					);
				}
			}
		}
	}
}
