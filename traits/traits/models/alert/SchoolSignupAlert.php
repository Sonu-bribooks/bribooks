<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait SchoolSignupAlert {
	public function schoolLeadRegistration($id = 0) {
		self::cron($id, 'schoolLeadRegistrationCron');
	}

	public function schoolLeadRegistrationCron($id = 0) {
		log_kb(['schoolLeadRegistrationCron=> '  => $id]);

		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		if ($site_info = $this->site_model->get($id)) {
			$site_id  = $template_site_id	= $site_info['id'];

			// $site_info = $this->site_model->get($site_id);

			log_kb([
				'schoolLeadRegistrationCron' => $site_info
			]);
			/*$template = 'email_competition_signup';

			if ($site_id != $this->config->item('default_site_id')) {
				$template = 'email_competition_signup';
			}*/

			$username = '';
			$password = '';
			$login_url = USER_URL . 'school/login';
			$author_registration_link = '';
			$school_dashboard_link = '';

			$template = 'school_competition_signup';

			if($site_info['site_type'] == 1) {
				$template = 'school_competition_signup';
			} else if($site_info['site_type'] == 2) {
				$template = 'school_competition_signup_nursery';
			} else if($site_info['site_type'] == 3) {
				$template = 'school_competition_signup_university';
			} else if($site_info['site_type'] == 4) {
				$template = 'school_signup_community';

				$user_info = $this->user_model->get_all([
					'role_id' => 9,
					'site_id' => $site_info['id']
				])['rows'][0] ?? '';

				if (!empty($user_info)) {
					$template_site_id	= $site_info['parent_id'];
					$password 			= uniqid();
					$username 			= $user_info['username'];
					$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
					$verification_code 	= sha1(md5($user_info['username'] . $password . $this->config->item('password_salt')));

					$this->user_model->edit($user_info['id'], [
						'password'			=> $encoded_password,
						'verification_code'	=> $verification_code
					]);

					$author_registration_link = SC_USER_ADDRESS_URL . sprintf('india/student/communities/%s', $site_info['id']);
					$school_dashboard_link    = SC_USER_ADDRESS_URL . sprintf('india/student/communities/%s', $site_info['id']);
				}
			}

			$title = self::formatEmailSubject($template, $template_site_id, [
					'author_name'	  	=> $site_info['authorized_person'],
					'name'	  			=> $site_info['name'],
					'school_name'	  	=> $site_info['name'],
					'owner_name'	  	=> $site_info['owner_name']
				]) ?? vsprintf(_li('Welcome onboard - congratulations on being a part of the National Young Authors Fair'), [
				get_settings('system_name')
			]);
			$data['content']	= self::formatEmailMessage($template, [
				'author_name'	=> $site_info['authorized_person'],
				'name'	  		=> $site_info['name'],
				'school_name'	=> $site_info['name'],
				'owner_name'	=> $site_info['owner_name'],
				'site_id'	  	=> $site_id,
				'username'	  	=> $username,
				'password'	  	=> $password,
				'login_url'	  	=> $login_url,
				'url'			=> $author_registration_link,
				'url_2'	  		=> $school_dashboard_link,
			], $site_id);

			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['title']		  	= $title;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['link']		   	= '';
			$data['link_text']	  	= '';

			$mobile = $site_info['mobile'];
			$email = $site_info['email'];

			$attachment = [];

			if(!empty($site_info['site_code']) && in_array(strtolower($site_info['site_code']), [NYAF_US_SITE_CODE, NYAF_US_SITE_CODE.'-'.$id])) {
				self::communicationKitParentPdf($site_id);
				self::communicationKitTeacherPdf($site_id);

				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
				$attachment          	= [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_id.'.pdf',
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Teachers_'.$site_id.'.pdf',
					FCPATH . 'assets/backend/sendmail/ge-nyafus/NYAF_US_Digital_Poster.png',
				];

				/*$this->load->model('common/Cron_model', 'cron_model');
				$this->cron_model->add([
					'code'			=> 'schoolRegistrationApprovalCron_' . $site_id,
					'action'		=> 'alert_model->schoolRegistrationApprovalCron',
					'data'			=> [$site_id],
					'site_id'		=> 2,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime((ENVIRONMENT === 'production') ? '+24 hours' : '+1 minutes'))
				]);*/
			} else if(!empty($site_info['site_code']) && in_array(strtolower($site_info['site_code']), [BWF_SITE_CODE, BWF_SITE_CODE.'-'.$id])) {
				log_kb([
					'BWF_SITE_CODE' => $site_info
				]);
				$mobile = '';
				$email 	= '';

				self::communicationKitParentBWFPdf($site_id);
				self::communicationKitTeacherBWFPdf($site_id);
				self::reminderMessageParentBWFPdf($site_id);

				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
				$attachment          	= [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_id.'.pdf',
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Teachers_'.$site_id.'.pdf',
					FCPATH . 'uploads/communication_kit/parent/Reminder_Message_Parents_'.$site_id.'.pdf',
				];
			} else if(!empty($site_info['site_code']) && in_array(strtolower($site_info['site_code']), [SUMMER_CAMP_SITE_CODE, SUMMER_CAMP_SITE_CODE.'-'.$id])) {
				log_kb([
					'SC' => $site_info
				]);
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
				$attachment          	= [];
				// $attachment          	= [
				// 	FCPATH . 'assets/backend/sendmail/in-sc/Communication_Kit_Parents.pdf',
				// 	FCPATH . 'assets/backend/sendmail/in-sc/Communication_Kit_Teachers.pdf',
				// 	FCPATH . 'assets/backend/sendmail/in-sc/DigitalPoster_SummerBookWritingFestival.png',
				// ];

				// !empty($info['mobile']) && self::_sendWhatsappDocument(
				// 	$mobile,
				// 	[
				// 		'template'		=> '625684719019292',
				// 		'parameters'	=> [
				// 			$info['authorized_person'],
				// 			$info['name'],
				// 			$info['name'],
				// 		],
				// 		'document'	=> [
				// 			'name'	=> 'Communication Kit Parents',
				// 			'link'	=> base_url('assets/backend/sendmail/in-sc/Communication_Kit_Parents.pdf')
				// 		]
				// 	],
				// );


			} else if(0 && !empty($site_info['site_code']) && in_array(strtolower($site_info['site_code']), [UAE_SITE_CODE, UAE_SITE_CODE.'-'.$id])) {
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);

				if($site_info['site_type'] == 2) {
					$attachment          	= [
						FCPATH . '/assets/backend/sendmail/uae/Communication_Note_Nursury_UAE.pdf'
					];

					!empty($info['mobile']) && self::_sendWhatsappDocument(
						$mobile,
						[
							'template'		=> '768131467998815',
							'parameters'	=> [
								$site_info['name'],
								$site_info['name']
							],
							'document'	=> [
								'name'	=> 'Communication Note Parents',
								'link'	=> base_url('assets/backend/sendmail/uae/Communication_Note_Nursury_UAE.pdf')
							]
						],
					);
				} else {
					$attachment          	= [
						FCPATH . '/assets/backend/sendmail/uae/Communication_Note_Parents_NYAF_UAE.pdf',
						FCPATH . '/assets/backend/sendmail/uae/NYAF_UAE_Digital_Poster.png'
					];

					!empty($info['mobile']) && self::_sendWhatsappDocument(
						$mobile,
						[
							'template'		=> '950643772825341',
							'parameters'	=> [
								$site_info['name'],
								'https://www.yaf.bribooks.com/ae/student'
							],
							'document'	=> [
								'name'	=> 'Communication Note Parents',
								'link'	=> base_url('assets/backend/sendmail/uae/Communication_Note_Parents_NYAF_UAE.pdf')
							]
						],
					);
				}
			} else if(0 && !empty($site_info['site_code']) && in_array(strtolower($site_info['site_code']), [ISRAEL_SITE_CODE, ISRAEL_SITE_CODE.'-'.$id])) {
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);
				$attachment          	= [
					FCPATH . 'assets/backend/sendmail/nwfis/Notice_to_the_teachers.docx',
					FCPATH . 'assets/backend/sendmail/nwfis/Notice_to_all_teachers_in_the_school.docx',
					FCPATH . 'assets/backend/sendmail/nwfis/Spring_Homwork_assgiment.docx',
					FCPATH . 'assets/backend/sendmail/nwfis/Whatsapp_Message_to_Parents_and_Kids.docx'
				];
			} else {
				$message				= $this->load->view('common/mail/templates/3/general', $data, true);
				/*$attachment          	= [
					FCPATH . '/assets/backend/sendmail/NYAF_India_School_Communication.pdf',
					FCPATH . '/assets/backend/sendmail/Poster.png'
				];*/
				// $attachment          .= FCPATH . '/assets/backend/sendmail/YAF_Communication_Poster.pdf';
			}

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				$attachment
			);

			if (file_exists('uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_id.'.pdf')) {
				unlink(FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_id.'.pdf');
			}

			if (file_exists('uploads/communication_kit/parent/Communication_Kit_Teachers_'.$site_id.'.pdf')) {
				unlink(FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Teachers_'.$site_id.'.pdf');
			}
		}
	}

	public function otherSchoolLeadRegistration($id = 0) {
		self::cron($id, 'otherSchoolLeadRegistrationCron');
	}

	public function otherSchoolLeadRegistrationCron($id = 0) {
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('event/Event_model', 'event_model');

		if ($info = $this->school_lead_model->get($id)) {
			$template = 'email_competition_signup';

			if ($info['site_id'] != $this->config->item('default_site_id')) {
				$template = 'email_competition_signup';
			}

			$event_info = $this->event_model->get($info['event_id']);

			$data['title']		  	= self::formatEmailSubject('other_school_register', $event_info['parent_site_id']) ?? vsprintf(_li('Your Application for the National Young Authors Fair has been received!'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('other_school_register', [
				'author_name'	  	=> $info['authorized_person'],
				'name'	  			=> $info['name'],
			], $event_info['parent_site_id']);
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);
			$attachment          	= [];
			// $attachment          .= FCPATH . '/assets/backend/sendmail/YAF_Communication_Poster.pdf';

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[],
				$attachment
			);
		}
	}

	public function otherSchoolAutoApproval($id = 0) {
		if ($info = $this->school_lead_model->get($id)) {
			$template = 'Other_schools_auto_approval_email';

			if ($info['site_id'] != $this->config->item('default_site_id')) {
				$template = 'Other_schools_auto_approval_email';
			}

			$data['title']		  	= vsprintf(_li('Congratulations - Your application for the National Young Authors Fair- Powered by Education World has been approved!'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('Other_schools_auto_approval_email', [
				'author_name'	  	=> $info['authorized_person'],
				'name'	  			=> $info['name'],
			]);
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/3/general', $data, true);
			$attachment          	= [
				FCPATH . '/assets/backend/sendmail/NYAF_India_School_Communication.pdf',
				FCPATH . '/assets/backend/sendmail/Poster.png'
			];
			// $attachment          .= FCPATH . '/assets/backend/sendmail/YAF_Communication_Poster.pdf';

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[],
				$attachment
			);
		}
	}

	public function rejectLead($id = 0) {
		if ($info = $this->school_lead_model->get($id)) {
			$template = 'email_competition_signup';

			if ($info['site_id'] != $this->config->item('default_site_id')) {
				$template = 'email_competition_signup';
			}

			$data['title']		  	= vsprintf(_li('Your Application for the National Young Authors Fair has been rejected!'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('reject_lead', [
				'author_name'	  	=> $info['authorized_person'],
				'name'	  			=> $info['name'],
			]);
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/3/general', $data, true);
			$attachment          	= [];
			// $attachment          .= FCPATH . '/assets/backend/sendmail/YAF_Communication_Poster.pdf';

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[],
				$attachment
			);
		}
	}

	public function schoolLeadShare($id = 0) {
		self::cron($id, 'schoolLeadShareCron');
	}

	public function schoolLeadShareCron($id = 0) {
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		if ($info = $this->school_lead_model->get($id)) {
			$site_id = $info['site_id'];

			$site_info = $this->site_model->get($site_id);

			/*$template = 'email_competition_signup';

			if ($site_id != $this->config->item('default_site_id')) {
				$template = 'email_competition_signup';
			}*/

			$template = 'school_lead_share';

			$title = self::formatEmailSubject($template, $site_id) ?? vsprintf(_li('School participation lead'), [
				get_settings('system_name')
			]);

			$state = $this->state_model->get($info['state_id']);
			$city = $this->city_model->get($info['city_id']);

			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['title']		  	= $title;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']	 	= '';
			$data['content']		= self::formatEmailMessage($template, [
				'name'	  			=> $info['name'],
				'email' 			=> $info['email'],
				'mobile' 			=> $info['mobile'],
				'authorized_person'	=> $info['authorized_person'],
				'country'   		=> $info['country'],
				'state'   			=> $state['name'],
				'city' 				=> $city['name'],
				'designation'   	=> $info['designation'],
				'grades'   			=> $info['grades'],
				'sections'   		=> $info['sections'],
				'school_head'   	=> $info['school_head'],
			], $site_id);
			$data['link']		   	= '';
			$data['link_text']	  	= '';

			$email = 'schools@bribooks.com';

			if(0 && !empty($site_info['site_code']) && in_array(strtolower($site_info['site_code']), [ISRAEL_SITE_CODE, ISRAEL_SITE_CODE.'-'.$id])) {
				$email = 'israel@bribooks.com';

				$message = $this->load->view('common/mail/templates/site/general', $data, true);
			} else {
				$message = $this->load->view('common/mail/templates/site/general', $data, true);
			}

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['info@bribooks.com'] : []
			);
		}
	}

	private function communicationKitTeacherPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/teacher';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_teacher', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}'
			],
			[
				$site_info['name'],
				USER_YAF_URL . 'us/studentv2/' . $site_info['id']
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Teachers_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 9;

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}'
			],
			[
				$site_info['name'],
				USER_YAF_URL . 'us/studentv2/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes', $event_id)
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentEspanolPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 9;

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_espanol', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}'
			],
			[
				$site_info['name'],
				USER_YAF_URL . 'us/studentv2/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes', $event_id)
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_info['id'].'_Espanol.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentNyafInPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 10;

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info = $this->city_model->get($site_info['city_id']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_nyaf_in', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
				'{city}',
				'{state}'
			],
			[
				$site_info['name'],
				USER_YAF_URL . 'india/student/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes', $event_id),
				$city_info['name'] ?? '',
				$state_info['name'] ?? ''
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentSCPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 14;

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info = $this->city_model->get($site_info['city_id']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_sc_2024', [], true);

		if (in_array($site_info['state_id'], [2,16,17,25,29,30])) {
			$student_url = SC_USER_ADDRESS_URL . 'india/2024/student/south/' . $site_info['id'];
		} else {
			$student_url = SC_USER_ADDRESS_URL . 'india/2024/student/north/' . $site_info['id'];
		}

		generateQrCode($student_url , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');
		// generateQrCode(SC_USER_ADDRESS_URL . 'india/2024/student/' . $site_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
				'{city}',
				'{state}'
			],
			[
				$site_info['name'],
				$student_url,
				base_url() . 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png',
				$city_info['name'] ?? '',
				$state_info['name'] ?? ''
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentNYAFUKPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 15;

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info = $this->city_model->get($site_info['city_id']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_nyafuk', [], true);


		generateQrCode(USER_YAF_URL . 'uk/2024/student/' . $site_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
				'{city}',
				'{authorized_person}'
			],
			[
				$site_info['name'],
				USER_YAF_URL . 'uk/2024/student/' . $site_info['id'],
				base_url() . 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png',
				$city_info['name'] ?? '',
				$site_info['authorized_person'] ?? ''
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentNYAFMalaysPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 16;

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info = $this->city_model->get($site_info['city_id']);

		generateQrCode(USER_YAF_URL . 'malaysia/2024/student/' . $site_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$data = [
			'school_name' 			=> $site_info['name'],
			'authorized_person' 	=> $site_info['authorized_person'],
			'state' 				=> $state_info['name'],
			'city' 					=> $city_info['name'],
			'student_url' 			=> USER_YAF_URL . 'malaysia/2024/student/' . $site_info['id'],
			'qrcode_url' 			=> base_url(generateQrCode('www.yaf.bribooks.com/malaysia/2024/student/' . $site_id, 20, 2))
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_nyafmalays', $data, true);

		// $html = str_replace(
		// 	[
		// 		'{school_name}',
		// 		'{student_url}',
		// 		'{qrcode_url}',
		// 		'{state}',
		// 		'{authorized_person}'
		// 	],
		// 	[
		// 		$site_info['name'],
		// 		USER_YAF_URL . 'uk/2024/student/' . $site_info['id'],
		// 		base_url() . 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png',
		// 		$state_info['name'] ?? '',
		// 		$site_info['authorized_person'] ?? ''
		// 	],
		// 	$html
		// );

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentNYAFSingaporePdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 18;

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info  = $this->city_model->get($site_info['city_id']);

		generateQrCode(USER_YAF_URL . 'sg/2024/student/' . $site_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$data = [
			'school_name' 			=> $site_info['name'],
			'authorized_person' 	=> $site_info['authorized_person'],
			'state' 				=> $state_info['name'],
			'city' 					=> $city_info['name'],
			'student_url' 			=> USER_YAF_URL . 'sg/2024/student/' . $site_info['id'],
			'qrcode_url' 			=> base_url(generateQrCode('www.yaf.bribooks.com/sg/2024/student/' . $site_id, 20, 2))
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_nyafsingapore', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentNYAFAustraliaPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 19;

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info  = $this->city_model->get($site_info['city_id']);

		generateQrCode(USER_YAF_URL . 'au/2024/student/' . $site_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$data = [
			'school_name' 			=> $site_info['name'],
			'authorized_person' 	=> $site_info['authorized_person'],
			'state' 				=> $state_info['name'],
			'city' 					=> $city_info['name'],
			'student_url' 			=> USER_YAF_URL . 'au/2024/student/' . $site_info['id'],
			'qrcode_url' 			=> base_url(generateQrCode('www.yaf.bribooks.com/au/2024/student/' . $site_id, 20, 2))
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_nyafaustralia', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentGeneralSchoolPdf($site_id = false, $event_id = 0) {
		if(empty($site_id) || empty($site_id) || empty($site_info = $this->site_model->get($site_id)) || empty($event_info = $this->event_model->get($event_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info = $this->city_model->get($site_info['city_id']);

		$event_url 						= ENVIRONMENT != 'production' ?  'https://uat.events.bribooks.com/' :  'https://www.events.bribooks.com/';

		generateQrCode($event_url . 'student/' . $event_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$data = [
			'authorized_person' 		=> ucwords($site_info['authorized_person']),
			'school_name' 				=> ucwords($site_info['name']),
			'student_url'  				=> $event_url . 'student/' . $event_info['id'],
			'student_reg_end_date' 		=> 	date('d M Y', strtotime($event_info['student_reg_end_date'])),
			'state' 					=> $state_info['name'],
			'city' 						=> $city_info['name'],
			'qrcode_url' 				=> base_url(generateQrCode('www.events.bribooks.com/student/' . $event_info['id'], 20, 2))
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/general_communication_kit_parent_school', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file_name 	= 'Kit_'. $event_id . '_' .$site_info['id'].'.pdf';
		$s3_dirname = 'parent_communication_kit';
		$s3_dirname = ((ENVIRONMENT === 'production') ? $s3_dirname . '/live' : $s3_dirname . '/test');


		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('authorcertificates');

		// KIT URL LINK = { 'https://authorcertificates.s3.ap-south-1.amazonaws.com/parent_communication_kit/live/'. $file_name };
		$this->s3_lib->putData(
			$file_name,
			$s3_dirname,
			$dompdf->output(),
			false
		);

		$file = 'uploads/communication_kit/parent/Kit_'. $event_id . '_' .$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentGeneralTeacherPdf($author_id = false, $event_id = 0) {
		if(empty($author_id) || empty($author_info = $this->user_model->get($author_id)) || empty($event_info = $this->event_model->get($event_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$site_info = $this->site_model->get($author_info['site_id']);

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($author_info['state_id']);
		$city_info  = $this->city_model->get($author_info['city_id']);

		$event_url 						= ENVIRONMENT != 'production' ?  'https://uat.events.bribooks.com/' :  'https://www.events.bribooks.com/';

		generateQrCode($event_url . 'student/' . $event_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$author_info['id'].'.png');

		$data = [
			'school_name'				=> (empty($site_info['name']) || $site_info['id'] > 3) ? $site_info['name'] : 'Independent Educator',
			'name' 						=> ucwords($author_info['first_name'] . ' '. $author_info['last_name']),
			'student_url'  				=> $event_url . 'student/' . $event_info['id'],
			'student_reg_end_date' 		=> 	date('d M Y', strtotime($event_info['student_reg_end_date'])),
			'state' 					=> $state_info['name'],
			'city' 						=> $city_info['name'],
			'grade' 					=>  $author_info['section_id'] ?? '',
			'qrcode_url' 				=> base_url(generateQrCode('www.events.bribooks.com/student/' . $event_info['id'], 20, 2))
		];
		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/general_communication_kit_parent_teacher', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file_name 	= 'Kit_'. $event_id . '_' .$author_info['id'].'.pdf';
		$s3_dirname = 'parent_communication_kit';
		$s3_dirname = ((ENVIRONMENT === 'production') ? $s3_dirname . '/live' : $s3_dirname . '/test');

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('authorcertificates');

		// KIT URL LINK = { 'https://authorcertificates.s3.ap-south-1.amazonaws.com/parent_communication_kit/live/'. $file_name };
		$this->s3_lib->putData(
			$file_name,
			$s3_dirname,
			$dompdf->output(),
			false
		);

		$file = 'uploads/communication_kit/parent/Kit_'. $event_id . '_' .$author_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function generateEventBrochure($site_id = '', $event_id = '', $upload = 0) {
		if (!empty($site_id) && !empty($event_id)) {
			$this->load->model('common/Site_model', 'site_model');
			$this->load->library('S3_lib', 's3_lib');

			$site_info 	= $this->site_model->get($site_id);
			$event_info = $this->event_model->get($event_id);

			if ($event_id == 14) {

				if (in_array($site_info['state_id'], [2,16,17,25,29,30])) {
					$student_url = 'www.camp.bribooks.com/india/2024/student/south/' . $site_id;
				} else {
					$student_url = 'www.camp.bribooks.com/india/2024/student/north/' . $site_id;
				}

				$data = [
					'image1' 	=> base_url('assets/images/bro-india23/brochure_1.jpg'),
					'image2' 	=> base_url('assets/images/bro-india23/brochure_2.jpg'),
					'image3' 	=> base_url('assets/images/bro-india23/brochure_3.jpg'),
					'image4' 	=> base_url('assets/images/bro-india23/brochure_4.jpg'),
					'image5' 	=> base_url('assets/images/bro-india23/brochure_5.jpg'),
					'url' 		=> $student_url,
					'qr_file' 	=> base_url(generateQrCode($student_url, 20, 2))
				];
			} else if ($event_id == 15) {
				$data = [
					'image1' 	=> base_url('assets/images/bro-uk2024/brochure_1.jpg'),
					'image2' 	=> base_url('assets/images/bro-uk2024/brochure_2.jpg'),
					'image3' 	=> base_url('assets/images/bro-uk2024/brochure_3.jpg'),
					'image4' 	=> base_url('assets/images/bro-uk2024/brochure_4.jpg'),
					'image5' 	=> base_url('assets/images/bro-uk2024/brochure_5.jpg'),
					'url' 		=> 'www.yaf.bribooks.com/uk/2024/student/' . $site_id,
					'qr_file' 	=> base_url(generateQrCode('www.yaf.bribooks.com/uk/2024/student/' . $site_id, 20, 2))
				];
			} else if ($event_id == 16) {
				$data = [
					'image1' 	=> base_url('assets/images/bro-malaysia24/brochure_1.jpg'),
					'image2' 	=> base_url('assets/images/bro-malaysia24/brochure_2.jpg'),
					'image3' 	=> base_url('assets/images/bro-malaysia24/brochure_3.jpg'),
					'image4' 	=> base_url('assets/images/bro-malaysia24/brochure_4.jpg'),
					'image5' 	=> base_url('assets/images/bro-malaysia24/brochure_5.jpg'),
					'url' 		=> 'www.yaf.bribooks.com/malaysia/2024/student/' . $site_id,
					'qr_file' 	=> base_url(generateQrCode('www.yaf.bribooks.com/malaysia/2024/student/' . $site_id, 20, 2))
				];
			} else if ($event_id == 18) {
				$data = [
					'image1' 	=> sprintf('%spublic/EventGallery/brochure/event18/brochure_1.jpg', $this->config->item('cloudfront_url')),
					'image2' 	=> sprintf('%spublic/EventGallery/brochure/event18/brochure_2_v2.jpg', $this->config->item('cloudfront_url')),
					'image3' 	=> sprintf('%spublic/EventGallery/brochure/event18/brochure_3.jpg', $this->config->item('cloudfront_url')),
					'image4' 	=> sprintf('%spublic/EventGallery/brochure/event18/brochure_4.jpg', $this->config->item('cloudfront_url')),
					'image5' 	=> sprintf('%spublic/EventGallery/brochure/event18/brochure_5.jpg', $this->config->item('cloudfront_url')),
					'url' 		=> 'www.yaf.bribooks.com/sg/2024/student/' . $site_id,
					'qr_file' 	=> base_url(generateQrCode('www.yaf.bribooks.com/sg/2024/student/' . $site_id, 20, 2))
				];
			} else if ($event_id == 19) {
				$data = [
					'image1' 	=> sprintf('%spublic/EventGallery/brochure/event19/brochure_1.jpg', $this->config->item('cloudfront_url')),
					'image2' 	=> sprintf('%spublic/EventGallery/brochure/event19/brochure_2.jpg', $this->config->item('cloudfront_url')),
					'image3' 	=> sprintf('%spublic/EventGallery/brochure/event19/brochure_3.jpg', $this->config->item('cloudfront_url')),
					'image4' 	=> sprintf('%spublic/EventGallery/brochure/event19/brochure_4_v2.jpg', $this->config->item('cloudfront_url')),
					'image5' 	=> sprintf('%spublic/EventGallery/brochure/event19/brochure_5.jpg', $this->config->item('cloudfront_url')),
					'url' 		=> 'www.yaf.bribooks.com/au/2024/student/' . $site_id,
					'qr_file' 	=> base_url(generateQrCode('www.yaf.bribooks.com/au/2024/student/' . $site_id, 20, 2))
				];
			} else if (!empty($event_info) && $event_info['event_type_id'] == 1) {

				$data = [
					'image1' 	=> sprintf('%spublic/EventGallery/brochure/general_school/brochure_1.jpg', $this->config->item('cloudfront_url')),
					'image2' 	=> sprintf('%spublic/EventGallery/brochure/general_school/brochure_2.jpg', $this->config->item('cloudfront_url')),
					'image3' 	=> sprintf('%spublic/EventGallery/brochure/general_school/brochure_3.jpg', $this->config->item('cloudfront_url')),
					'image4' 	=> sprintf('%spublic/EventGallery/brochure/general_school/brochure_4.jpg', $this->config->item('cloudfront_url')),
					'image5' 	=> sprintf('%spublic/EventGallery/brochure/general_school/brochure_5.jpg', $this->config->item('cloudfront_url')),
					'url' 		=> 'www.events.bribooks.com/student/' . $event_info['id'],
					'qr_file' 	=> base_url(generateQrCode('www.events.bribooks.com/student/' . $event_info['id'], 20, 2))
				];
			} else if (!empty($event_info) && $event_info['event_type_id'] == 6) {
				$data = [
					'image1' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_1.jpg', $this->config->item('cloudfront_url')),
					'image2' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_2.jpg', $this->config->item('cloudfront_url')),
					'image3' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_3.jpg', $this->config->item('cloudfront_url')),
					'image4' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_4.jpg', $this->config->item('cloudfront_url')),
					'image5' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_5.jpg', $this->config->item('cloudfront_url')),
					'url' 		=> 'www.events.bribooks.com/student/' . $event_info['id'],
					'qr_file' 	=> base_url(generateQrCode('www.events.bribooks.com/student/' . $event_info['id'], 20, 2))
				];
			} else {
				return;
			}

			if (!empty($event_info) && in_array($event_info['event_type_id'],[1,6])) {
				$html = $this->load->view('frontend/default/general_brochure', $data, true);
			} else {
				$html = $this->load->view('frontend/default/brochure', $data, true);
			}

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
					844
				],
				'portrait'
			);

			$dompdf->render();

			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('authorcertificates');
			$file_name = 'school_' . $event_id . '_' . $site_id . '.pdf';

			$s3_dirname = ((ENVIRONMENT === 'production') ? 'brochure/live' : 'brochure/test');

			$this->s3_lib->putData(
				$file_name,
				$s3_dirname,
				$dompdf->output(),
				false
			);

			// $file = 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf';
			$file = 'uploads/pdfs/School_'. $event_id . '_' .$site_id.'.pdf';
			$output = $dompdf->output();
			file_put_contents(FCPATH.$file, $output);
		}
	}

	public function schoolAcknowledgeOnStudentSignupCron($site_id = 0, $event_id = 0) {
		log_kb([
			'schoolAcknowledgeOnStudentSignupCron::' => [
				'event_id'	=> $event_id,
				'site_id'	=> $site_id
			]
		]);

		$this->load->model('event/Event_model', 'event_model');

		if (
			($event_info = $this->event_model->get($event_id)) &&
			($event_site_info = $this->site_model->get($event_info['parent_site_id'])) &&
			($site_info = $this->site_model->getSchoolBySiteId($site_id))
		) {
			$site_id = $event_site_info['id'];

			$template = 'yaf_school_acknowledge_on_student_signup';

			$title = self::formatEmailSubject($template, $site_id, [
				'author_name'		=> $site_info['name']
			]) ?? '';

			$data['title']			= $title;
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $site_info['name'],
				'owner_name'		=> $site_info['owner_name'],
				'state'				=> $site_info['state'],
				'mobile'			=> '',
				'city'				=> $site_info['city'],
				'url'				=> "https://www.bribooks.com/school/login"
			], $site_id);
			$data['site_id']		= $site_id;
			$data['parent_id']		= "";
			$data['site_code']		= $site_info['site_code'];
			$data['link']			= '';
			$data['link_text']		= '';

			$message = $this->load->view('common/mail/templates/site/general', $data, true);

			$email 	= $site_info['email'];
			$mobile = $site_info['mobile'];

			if ($mobile) {
				self::_sendWhatsappImage(
					$mobile,
					[
						'template'		=> '1502502903945686',
						'parameters'	=> [
							$site_info['name'],
							$site_info['owner_name'],
							"https://www.bribooks.com/school/login",
							$site_info['city'],
							$site_info['state']
						],
						'document'	=> [
							'name'	=> 'NYAF_dashboard.png',
							'link'	=> base_url('assets/images/NYAF_dashboard.png')
						]
					]
				);
			}

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : []
			);
		}
	}

	public function schoolSignupWelcomeCron($site_id = 0, $event_id = 0) {
		if (
			$site_id &&
			($event_info = $this->event_model->get($event_id))
		) {

			$site_info = $this->site_model->getSchoolBySiteId($site_id);

			$mobile = $site_info['mobile'] ?? '';
			$email  = $site_info['email'] ?? '';

			if ($event_id == 14) {

				if (in_array($site_info['state_id'], [2,16,17,25,29,30])) {
					$student_url 				= SC_USER_ADDRESS_URL . 'india/2024/student/south/' . $site_info['id'];
					$customised_message_link 	= SC_USER_ADDRESS_URL . 'india/2024/communication/south/' . $site_info['id'];
				} else {
					$student_url 				= SC_USER_ADDRESS_URL . 'india/2024/student/north/' . $site_info['id'];
					$customised_message_link 	= SC_USER_ADDRESS_URL . 'india/2024/communication/north/' . $site_info['id'];
				}

				$subject 			= $site_info['name'] . '  Registered for the Summer Book Writing Fest';
				$message			= $this->load->view('common/mail/part/event_school_signup', [
					'authorized_person' 		=> ucwords($site_info['authorized_person']),
					'school_name' 				=> ucwords($site_info['name']),
					'student_link'  			=> $student_url,
					'customised_message_link'  	=> $customised_message_link,
					'dashboard_link' 			=> 	USER_URL . 'school/login'
				], true);

				self::communicationKitParentSCPdf($site_info['id']);
				self::generateEventBrochure($site_info['id'], $event_id);

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf'

				];
			} else if ($event_id == 15) {

				$subject 						= "Congratulations! " .$site_info['name'] . "  has been accepted to NYAF 2024";
				$message						= $this->load->view('common/mail/part/uk_school_signup', [
					'authorized_person' 		=> ucwords($site_info['authorized_person']),
					'school_name' 				=> ucwords($site_info['name']),
					'student_link'  			=> USER_YAF_URL . 'uk/2024/student/' . $site_info['id'],
					'customised_message_link'  	=> USER_YAF_URL . 'uk/2024/communication/' . $site_info['id'],
					'dashboard_link' 			=> 	USER_URL . 'school/login',
					'email' 					=> 	$site_info['owner_email']
				], true);

				self::communicationKitParentNYAFUKPdf($site_info['id']);
				self::generateEventBrochure($site_info['id'], $event_id);

				$bro_dir_name = ((ENVIRONMENT === 'production') ? 'brochure/live/' : 'brochure/test/');

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
				];
			} else if ($event_id == 16) {

				$subject 						= "Congratulations! " .$site_info['name'] . "   has been accepted to NYAF 2024";
				$message						= $this->load->view('common/mail/part/malays_school_signup', [
					'authorized_person' 		=> ucwords($site_info['authorized_person']),
					'school_name' 				=> ucwords($site_info['name']),
					'student_link'  			=> USER_YAF_URL . 'malaysia/2024/student/' . $site_info['id'],
					'customised_message_link'  	=> USER_YAF_URL . 'malaysia/2024/communication/' . $site_info['id'],
					'dashboard_link' 			=> 	USER_URL . 'school/login',
					'email' 					=> 	$site_info['owner_email']
				], true);

				self::communicationKitParentNYAFMalaysPdf($site_info['id']);
				self::generateEventBrochure($site_info['id'], $event_id);

				$bro_dir_name = ((ENVIRONMENT === 'production') ? 'brochure/live/' : 'brochure/test/');

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
				];
			}  else if ($event_id == 18) {

				$subject 						= "Congratulations! " .$site_info['name'] . " has been accepted to NYAF 2024";
				$message						= $this->load->view('common/mail/part/singapore_school_signup', [
					'authorized_person' 		=> ucwords($site_info['authorized_person']),
					'school_name' 				=> ucwords($site_info['name']),
					'student_link'  			=> USER_YAF_URL . 'sg/2024/student/' . $site_info['id'],
					'customised_message_link'  	=> USER_YAF_URL . 'sg/2024/communication/' . $site_info['id'],
					'dashboard_link' 			=> 	USER_URL . 'school/login',
					'email' 					=> 	$site_info['owner_email']
				], true);

				self::communicationKitParentNYAFSingaporePdf($site_info['id']);
				self::generateEventBrochure($site_info['id'], $event_id);

				$bro_dir_name = ((ENVIRONMENT === 'production') ? 'brochure/live/' : 'brochure/test/');

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
				];
			}  else if ($event_id == 19) {

				$subject 						= "Congratulations! " .$site_info['name'] . " has been accepted to NYAF 2024";
				$message						= $this->load->view('common/mail/part/australia_school_signup', [
					'authorized_person' 		=> ucwords($site_info['authorized_person']),
					'school_name' 				=> ucwords($site_info['name']),
					'student_link'  			=> USER_YAF_URL . 'au/2024/student/' . $site_info['id'],
					'customised_message_link'  	=> USER_YAF_URL . 'au/2024/communication/' . $site_info['id'],
					'dashboard_link' 			=> 	USER_URL . 'school/login',
					'email' 					=> 	$site_info['owner_email']
				], true);

				self::communicationKitParentNYAFAustraliaPdf($site_info['id']);
				self::generateEventBrochure($site_info['id'], $event_id);

				$bro_dir_name = ((ENVIRONMENT === 'production') ? 'brochure/live/' : 'brochure/test/');

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
				];
			} else if ($event_info['event_type_id'] == 1) {

				$event_url 						= ENVIRONMENT != 'production' ?  'https://uat.events.bribooks.com/' :  'https://www.events.bribooks.com/';
				$school_url 					= ENVIRONMENT != 'production' ?  'https://uat.schools.bribooks.com/' :  'https://www.schools.bribooks.com/';

				$subject 						= "Registration Confirmation & Communication Kit: " .$site_info['name'] ;
				$message						= $this->load->view('common/mail/part/general_school_signup', [
					'authorized_person' 		=> ucwords($site_info['authorized_person']),
					'school_name' 				=> ucwords($site_info['name']),
					'student_link'  			=> $event_url . 'student/' . $event_info['id'],
					'customised_message_link'  	=> $school_url . 'communication/' . $event_info['id'],
					'dashboard_link' 			=> 	USER_URL . 'school/login',
					'event_start_date' 			=> 	date('d M Y', strtotime($event_info['start_date'])),
					'student_reg_end_date' 		=> 	date('d M Y', strtotime($event_info['student_reg_end_date'])),
					'book_writing_end_date' 	=> 	date('d M Y', strtotime($event_info['book_writing_end_date'])),
					'email' 					=> 	$site_info['email']
				], true);

				self::communicationKitParentGeneralSchoolPdf($site_info['id'],$event_info['id']);
				self::generateEventBrochure($site_info['id'], $event_info['id'], 1);

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Kit_'. $event_id . '_' .$site_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
				];
			} else if (
				$event_info['event_type_id'] == 6 &&
				!empty($event_info['parent_site_id']) &&
				!empty($author_info = $this->user_model->get($event_info['parent_site_id']))
			) {

				$mobile = $author_info['mobile'] ?? '';
				$email  = $author_info['email'] ?? '';

				$event_url 						= ENVIRONMENT != 'production' ?  'https://uat.events.bribooks.com/' :  'https://www.events.bribooks.com/';
				$school_url 					= ENVIRONMENT != 'production' ?  'https://uat.schools.bribooks.com/' :  'https://www.schools.bribooks.com/';

				$subject 						= 'Registration Confirmation & Communication Kit';
				$message						= $this->load->view('common/mail/part/general_teacher_signup', [
					'name' 						=> ucwords($author_info['first_name'] . ' ' . $author_info['last_name']),
					'student_link'  			=> $event_url . 'student/' . $event_info['id'],
					'customised_message_link'  	=> $school_url . 'teacher/communication/' . $event_info['id'],
					'dashboard_link' 			=> 	USER_URL . 'teacher/login',
					'event_start_date' 			=> 	date('d M Y', strtotime($event_info['start_date'])),
					'student_reg_end_date' 		=> 	date('d M Y', strtotime($event_info['student_reg_end_date'])),
					'book_writing_end_date' 	=> 	date('d M Y', strtotime($event_info['book_writing_end_date'])),
					'email' 					=> 	$author_info['email']
				], true);

				self::communicationKitParentGeneralTeacherPdf($author_info['id'], $event_info['id']);
				self::generateEventBrochure($author_info['id'], $event_info['id'],1);

				$attachment = [
					FCPATH . 'uploads/communication_kit/parent/Kit_'. $event_id . '_' .$author_info['id'].'.pdf',
					FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$author_info['id'].'.pdf',
				];
			} else {
				return;
			}

			if ($email) {

				self::email(
					$email,
					$subject,
					$message,
					[],
					(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
					$attachment
				);
			}

			if (in_array($event_id, [14,16,18])) {
				if ($mobile) {

					if ($event_id == 14) {

						if (in_array($site_info['state_id'], [2,16,17,25,29,30])) {
							$wa_student_url 				= SC_USER_ADDRESS_URL . 'india/2024/student/south/' . $site_info['id'];
						} else {
							$wa_student_url 				= SC_USER_ADDRESS_URL . 'india/2024/student/north/' . $site_info['id'];
						}

						$template_id 	= '3267329213474587';
						$parameters 	= [
							$site_info['authorized_person'],
							$site_info['name'],
							$wa_student_url,
							USER_URL . 'school/login',
							$site_info['name']
						];
						$document	= [
							'name'	=> 'Communication Kit Parents',
							'link'	=> base_url('uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$site_info['id'].'.pdf')
						];

					} else if ($event_id == 16) {
						$template_id 	= '1614769322633206';
						$parameters 	= [
							$site_info['name'],
							$site_info['authorized_person'],
							$site_info['name'],
							USER_YAF_URL . 'sg/2024/student/' . $site_info['id'],
							USER_YAF_URL . 'sg/2024/communication/' . $site_info['id']
						];
						$document	= [
							'name'	=> 'Brochure',
							'link'	=> FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
						];
					} else if ($event_id == 18) {
						$template_id 	= '2971280326345783';
						$parameters 	= [
							$site_info['name'],
							$site_info['authorized_person'],
							$site_info['name'],
							USER_YAF_URL . 'sg/2024/student/' . $site_info['id'],
							USER_YAF_URL . 'sg/2024/communication/' . $site_info['id']
						];
						$document	= [
							'name'	=> 'Brochure',
							'link'	=> FCPATH . 'uploads/pdfs/School_'. $event_id . '_' .$site_info['id'].'.pdf',
						];
					} else {
						return;
					}

					self::_sendWhatsappDocument(
						$mobile,
						[
							'template'		=> $template_id,
							'parameters'	=> $parameters,
							'document'		=> $document
						],
					);
				}
			}
		}
	}

	public function schoolRegistrationApprovalCron($site_id = 0) {
		log_kb(['schoolRegistrationApprovalCron=> '  => $site_id]);
		if (
			$site_id &&
			($site_info = $this->site_model->getSchoolBySiteId($site_id))
		) {
			$mobile = $site_info['mobile'];
			$email 	= $site_info['email'];

			$registration_url = USER_YAF_URL . 'us/studentv2/' . $site_info['id'];
			$communication_url = USER_YAF_URL . 'communication/' . $site_info['id'];
			$dashboard_url = USER_URL . 'school/login';

			$subject = 'Registration Approved: National Young Authors Fair';

			$content = '<p>Dear '.$site_info['authorized_person'].',</p>
<p>We\'re excited to inform you that your registration for the National Young Authors\' Fair has been approved. We hope you received our letter.</p>
<p>It\'s time for <strong>'.$site_info['name'].'</strong> to shine! This historic event offers your students the opportunity to showcase their talents as young published authors, and your school has a chance to win the Literary Leadership Excellence Awards.</p>
<p>To maximize your success, there\'s just one thing you need to do—encourage as many students as possible to start writing a book.</p>
<p>Here are the steps to get started:</p>
<p>Step 1: Share the registration link with parents and students: '.$registration_url.'</p>
<p>Step 2: Use the leaflets to facilitate sign-ups among parents and teachers.</p>
<p>Step 3: Let us know if you\'d like to receive additional information from us for better communication.</p>
<p>We recommend printing the letter and handing it out to students. You can easily copy and paste the communication for parents and teachers from this '.$communication_url.'.</p>
<p>You can track your progress through the provided dashboard link:</p>
<p>'.$dashboard_url.'</p>
<p>Please use your registered email “'.$site_info['email'].'” to log into your school dashboard.</p>
<p>Should you have any questions, feel free to reach out to us at <a href="mailto:schools@bribooks.com">schools@bribooks.com</a>.</p>
<p>Let\'s embark on this journey of publishing books together!</p>
<p>Best regards,<br />Team BriBooks</p>';

			if ($email) {
				self::email(
					$email,
					$subject,
					$content,
					[],
					(ENVIRONMENT === 'production') ? ['schools@bribooks.com'] : []
				);
			}
		}
	}

	public function sendSchoolVerificationEmail($user_id = 0) {
		$this->load->model('school/SchoolUser_model', 'school_user_model');

		if ($user_info = $this->school_user_model->get($user_id)) {
			$html = $this->load->view('common/mail/school_verification_email', [], true);

			$email_url = USER_URL."verifyEmail?uid=".$user_info['id']."&code=".$user_info['verification_code']."";

			$html = str_replace(
				[
					'{email_url}'
				],
				[
					$email_url
				],
				$html
			);

			self::email(
				$user_info['email'],
				"Verify This Email Address",
				$html,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : []
			);
		}
	}

	private function communicationKitTeacherBWFPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/teacher';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 11;

		$school_logo_or_name = '<h2>'.$site_info['name'].'</h2>';
		if(!empty($site_info['image'])) {
			$image = $this->config->config["s3_base_url"] . "public/SiteImages/" . $site_info['image'];
			$school_logo_or_name = '<img src="'.$image.'" alt="Logo" style="height:80px;" />';
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_teacher_bwf', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
				'{school_logo_or_name}'
			],
			[
				$site_info['name'],
				'https://www.bwf.bribooks.com/student/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes', $event_id),
				$school_logo_or_name
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Teachers_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function communicationKitParentBWFPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 11;

		$school_logo_or_name = '<h2>'.$site_info['name'].'</h2>';
		if(!empty($site_info['image'])) {
			$image = $this->config->config["s3_base_url"] . "public/SiteImages/" . $site_info['image'];
			$school_logo_or_name = '<img src="'.$image.'" alt="Logo" style="height:80px;" />';
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_bwf', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
				'{school_logo_or_name}'
			],
			[
				$site_info['name'],
				'https://www.bwf.bribooks.com/student/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes', $event_id),
				$school_logo_or_name
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	private function reminderMessageParentBWFPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 11;

		$school_logo_or_name = '';
		if(!empty($site_info['image'])) {
			$image = $this->config->config["s3_base_url"] . "public/SiteImages/" . $site_info['image'];
			$school_logo_or_name = '<img src="'.$image.'" alt="Logo" style="height:80px;" />';
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/reminder_message_parent_bwf', [], true);

		$html = str_replace(
			[
				'{owner_name}',
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
				'{school_logo_or_name}'
			],
			[
				$site_info['owner_name'] ?? $site_info['authorized_person'],
				$site_info['name'],
				'https://www.bwf.bribooks.com/student/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes', $event_id),
				$school_logo_or_name
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Reminder_Message_Parents_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}
}
