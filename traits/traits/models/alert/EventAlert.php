<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventAlert {
	public function eventSignup($event_id = 0, $user_id = 0) {
		$this->cron_model->add([
			'code'			=> 'eventSignupCron_' . $event_id . '_' . $user_id,
			'action'		=> 'alert_model->eventSignupCron',
			'data'			=> [$event_id, $user_id],
			'alert_date'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function eventSignupCron($event_id = 0, $user_id = 0) {
		// 1. published authors,
		// 2. not published having more than 5000 chars
		$user_info = $this->student_model->get($user_id);

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('book/Book_model', 'book_model');

		$event_info = $this->event_model->get($event_id);
		$site_id = $event_info['parent_site_id'];

		$password = uniqid();
		$encoded_password = sha1(md5($password . $this->config->item('password_salt')));

		$this->student_model->edit($user_info['id'], [
			'password'			=> $encoded_password,
		]);

		$template = 'yaf_invite_';
		$whatsapp_template_id = '';
		$parameters = [];

		// summercamp
		if ($this->event_user_model->get_all([
			'event_id'	=> 4,
			'user_id'	=> (int)$user_id,
		])['total'] > 0) {
			$template .= 'summercamp_unpublished';
			$whatsapp_template_id = '833489941899567';
			$parameters = [
				$user_info['first_name'] . ' ' . $user_info['last_name'],
				$user_info['username'],
				$password,
			];
		} elseif (!empty($book_info = $this->book_model->get_all([
			'status'	=> 1,
			'user_id'	=> (int)$user_id,
		])['rows'][0])) {
			$template .= 'yaf_published';
			$whatsapp_template_id = '114345545102187';
			$parameters = [
				$book_info['author_name'],
				$user_info['username'],
				$password,
			];
		} else {
			$template .= 'yaf_unpublished';
			$whatsapp_template_id = '1332978184004768';
			$parameters = [
				$user_info['first_name'] . ' ' . $user_info['last_name'],
				$user_info['username'],
				$password,
			];
		}

		$title = self::formatEmailSubject($template, $site_id, [
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
		]) ?? $title;

		$data['title']			= $title;
		$data['heading']		= $title;

		$data['content']		= self::formatEmailMessage($template, [
			'book_name'			=> $book_info['name'] ?? '',
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'username'			=> $user_info['username'],
			'password'			=> $password,
		], $site_id);;

		$message 	= $this->load->view('common/mail/templates/6/general', $data, true);

		self::email(
			$user_info['email'],
			$data['title'],
			$message,
			[],
			[]
		);

		!empty($user_info['mobile']) && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> $whatsapp_template_id,
				'parameters'	=> $parameters,
			],
		);
	}

	public function eventAuthorSignupCron($user_id = 0, $type = '', $event_id = '') {

		if (empty($user_id) || empty($user_info = $this->student_model->get($user_id))) {
			return;
		}

		$signup_type = '';

		if (strpos($type, 'earlyaccess') !== false) {
			$type  			= explode('_', $type)[0] ?? '';
			$signup_type  	= 'earlyaccess';
		}

		$reward_url = SC_USER_ADDRESS_URL . 'india/2024/rewards';

		$password = uniqid();
		$encoded_password = sha1(md5($password . $this->config->item('password_salt')));

		$site_info 	= $this->site_model->get($user_info['site_id']);
		$event_info = $this->event_model->get($event_id);

		$whatsapp_template_id = '';
		$parameters	= [];

		if (strtolower(trim($type)) == 'desktop') {
			if ($signup_type == 'earlyaccess') {
				$email_template_id = 'email_user_signup_desktop_earlyaccess';
			} else {
				$email_template_id = 'email_user_signup';
			}

			$template_event_id = $event_id;
			if (!empty($event_info) && in_array($event_info['event_type_id'], [1,6])) {
				$email_template_id = 'general_email_user_signup_desktop';
				$template_event_id = 0;
			}


			$title			= self::formatEmailSubjectByEvent($email_template_id, $template_event_id) ?? vsprintf(_li('Welcome to %s, your gateway to becoming a globally published author.'), [
				get_settings('system_name')
			]);
			$data['content']			= self::formatEmailMessageByEvent($email_template_id, [
				'author_name'			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'name'					=> $user_info['first_name'],
				'username'				=> $user_info['username'],
				'password'				=> $password,
				'reward_url'			=> $reward_url,
				'mobile'				=> $user_info['mobile'],
				'email'					=> $user_info['email'],
				'register_type'			=> strtolower($user_info['location']) != 'india' ? 'email' : 'mobile number',
				'register_type_value'	=> strtolower($user_info['location']) != 'india' ? $user_info['email'] : $user_info['mobile'],
				'url_2'					=> USER_URL . 'selectcategory'
			], $template_event_id);

			if (!empty($event_id) && $event_id == 14) {
				$whatsapp_template_id = '956418189542711';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$reward_url,
					$user_info['username'],
					$password,
					$user_info['mobile']
				];
			} elseif (!empty($event_id) && $event_id == 15) {
				$whatsapp_template_id = '396230936593187';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
				];
			} elseif (!empty($event_id) && $event_id == 16) {
				$whatsapp_template_id = '488610137197817';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
				];
			} elseif (!empty($event_id) && $event_id == 17) {
				$whatsapp_template_id = '1223506022348701';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
					$user_info['email'] ?? ''
				];
			} elseif (!empty($event_id) && $event_id == 18) {
				$whatsapp_template_id = '489436853645484';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
				];
			} elseif (!empty($event_id) && $event_id == 21) {
				if ($signup_type == 'earlyaccess') {
					$whatsapp_template_id = '1245710696559215';
					$parameters	= [
						ucwords(trim($user_info['first_name'])),
					];
				} else {
					$whatsapp_template_id = '412604958236591';
					$parameters	= [
						ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
						$user_info['mobile'],
						'https://www.youtube.com/watch?v=RWITiHkjoIs'
					];
				}
			} elseif (!empty($event_id) && $event_id == 23) {
				$whatsapp_template_id = '1141240397355947';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
					$user_info['email']
				];
			} elseif (!empty($event_id) && $event_id == 25) {
				$whatsapp_template_id = '2471734386369297';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					'are prizes to be won & the opportunity to be listed on Amazon and participate in the Brooklyn Book Festival,',
					$user_info['username'],
					$password,
					$user_info['email']
				];
			}
		} else if (strtolower(trim($type)) == 'mobile') {
			if ($signup_type == 'earlyaccess') {
				$email_template_id = 'email_user_signup_mobile_earlyaccess';
			} else {
				$email_template_id = 'email_user_signup_mobile';
			}

			$template_event_id = $event_id;
			if (!empty($event_info) && in_array($event_info['event_type_id'], [1,6])) {
				$email_template_id = 'general_email_user_signup_mobile';
				$template_event_id = 0;
			}
			$title			= self::formatEmailSubjectByEvent($email_template_id, $template_event_id) ?? vsprintf(_li('Welcome to %s, your gateway to becoming a globally published author.'), [
				get_settings('system_name')
			]);
			$data['content']			= self::formatEmailMessageByEvent($email_template_id, [
				'author_name'			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'name'					=> $user_info['first_name'],
				'username'				=> $user_info['username'],
				'password'				=> $password,
				'reward_url'			=> $reward_url,
				'mobile'				=> $user_info['mobile'],
				'email'					=> $user_info['email'],
				'register_type'			=> strtolower($user_info['location']) != 'india' ? 'email' : 'mobile number',
				'register_type_value'	=> strtolower($user_info['location']) != 'india' ? $user_info['email'] : $user_info['mobile'],
				'url'					=> 'https://apps.apple.com/us/app/bribooks/id6448090977',
				'url_2'					=> USER_URL . 'selectcategory',
			], $template_event_id);

			if (!empty($event_id) && $event_id == 14) {
				$whatsapp_template_id = '1704305953436476';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$reward_url,
					'https://apps.apple.com/us/app/bribooks/id6448090977',
					$user_info['username'],
					$password
				];
			} elseif (!empty($event_id) && $event_id == 15) {
				$whatsapp_template_id = '450852347393750';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					'https://apps.apple.com/us/app/bribooks/id6448090977',
					$user_info['username'],
					$password,
				];
			} elseif (!empty($event_id) && $event_id == 16) {
				$whatsapp_template_id = '774552181178296';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
				];
			} elseif (!empty($event_id) && $event_id == 17) {
				$whatsapp_template_id = '798071209081262';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
					$user_info['email'] ?? ''
				];
			} elseif (!empty($event_id) && $event_id == 18) {
				$whatsapp_template_id = '1021656699304920';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
				];
			} elseif (!empty($event_id) && $event_id == 21) {
				if ($signup_type == 'earlyaccess') {
					$whatsapp_template_id = '1245710696559215';
					$parameters	= [
						ucwords(trim($user_info['first_name'])),
					];
				} else {
					$whatsapp_template_id = '1054491776376723';
					$parameters	= [
						ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
						$user_info['mobile'],
						$user_info['username'],
						$password,
						'https://www.youtube.com/watch?v=LbHPuxmrNiA'
					];
				}
			} elseif (!empty($event_id) && $event_id == 23) {
				$whatsapp_template_id = '28548467454744118';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					$user_info['username'],
					$password,
					$user_info['email'],
					'https://www.youtube.com/watch?v=LbHPuxmrNiA'
				];
			} elseif (!empty($event_id) && $event_id == 25) {
				$whatsapp_template_id = '600758445713878';
				$parameters	= [
					ucwords(trim($user_info['first_name'] . ' ' . $user_info['last_name'])),
					'prizes to be won, a chance to have your book listed on Amazon and to participate in the Brooklyn Book Festival,',
					$user_info['username'],
					$password,
					$user_info['email'],
					'https://youtu.be/liJznxTzFDA'
				];
			}
		} else {
			return;
		}

		$this->student_model->edit($user_info['id'], [
			'password'			=> $encoded_password,
		]);

		$data['heading']		= '';
		$data['subheading']		= '';
		$data['site_id']		= $site_info['id'];
		$data['parent_id']		= $site_info['parent_id'];
		$data['site_code']		= $site_info['site_code'];
		$data['link']			= '';
		$data['link_text']		= '';

		$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

		$attachment		  	= [];

		$mobile = $user_info['mobile'];
		$email 	= $user_info['email'];

		self::email(
			$email,
			$title,
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			$attachment
		);

		if (in_array(strtolower($user_info['location']),['india', 'nepal', 'brazil', 'malaysia', 'kuwait', 'united arab emirates', 'uae', 'nigeria', 'czechia']) && !empty($mobile) && !empty($whatsapp_template_id) && !empty($parameters)) {
			self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> $whatsapp_template_id,
					'parameters'	=> $parameters,
				],
			);
		}
	}

	private function _getEventAuthorTCPDF($id = '', $data = []) {
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
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file 	= 'uploads/termandconditions/author/author_' . $id . '.pdf';
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return base_url($file);
	}

	public function sendSummerCampAuthorInvitation () {
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('event/EventBook_model', 'event_book_model');

		$rows = $this->db->select('
			event_book.event_id,
			event_book.book_id,
			book_version.user_id,
			book_version.name as book_name,
			book_version.author_name
		')
		->from('event_book')
		->join('book_version', 'book_version.book_id = event_book.book_id')
		->where('event_book.event_id', 14)
		->where('event_book._deleted', 0)
		->where('book_version._deleted', 0)
		->where('book_version.date_published <', '2024-05-13 00:00:00')
		->where('book_version.archived', 0)
		->group_by('book_version.user_id')
		->get()->result_array() ?? [];

		log_kb([
			'sendSummerCampAuthorInvitation' => $rows
		]);

		foreach ($rows as $key => $row) {
			$user_info = $this->user_model->get($row['user_id']);
			log_kb([
				'sendSummerCampAuthorInvitation-user_info' => $user_info
			]);

			if (!empty($user_info) && empty($user_info['_deleted'])) {
				$books = $this->db->select('
					event_book.event_id,
					event_book.book_id,
					book_version.user_id,
					book_version.name as book_name,
					book_version.author_name,
					book_version.date_published
				')
				->from('event_book')
				->join('book', 'book.id = event_book.book_id')
				->join('book_version', 'book_version.book_id = book.id and book_version.version = book.version')
				->where('book_version.user_id', $row['user_id'])
				->where('event_book.event_id', 14)
				->where('event_book._deleted', 0)
				->where('book_version._deleted', 0)
				->where('book_version.date_published <', '2024-05-13 00:00:00')
				->where('book_version.archived', 0)
				->get()->result_array() ?? [];

				// $books = $this->event_book_model->get_all([
				// 	'user_id' 			=> $user_info['id'],
				// 	'event_id'  		=> 14,
				// 	'active_book' 		=> 1,
				// 	'date_published' 	=> '2024-05-13 00:00:00'
				// ])['rows'] ?? [];

				log_kb([
					'sendSummerCampAuthorInvitation-book' => $books
				]);

				$subject = 'Summer Book Writing Festival Best-Seller & Jury Leagues Qualifiers: Barnes Lit Fest 2024';

				$message			= $this->load->view('common/mail/part/sc_ranking_invitation', [
					'author_name' 	=> $user_info['first_name']  . ' ' . $user_info['last_name'],
					'books' 		=> $books,
				], true);

				log_kb([
					'sendSummerCampAuthorInvitation-message' => $message
				]);


				!empty($user_info['email']) && self::email(
					$user_info['email'],
					$subject,
					$message,
					[],
					(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				);
			}
		}
	}

	public function sendUserEventInvite () {
		$this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('user/Student_model', 'student_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/user_sc_invite.csv');
		$rows = $this->parsecsv->data;

		log_kb([
			'sendUserEventInvite' => $rows
		]);

		foreach ($rows as $row) {
			if (empty($this->event_user_model->getEventUserByUserId(14, $row['user_id']))) {
				$this->event_user_model->add([
					'event_id'	=> 14,
					'user_id'	=> $row['user_id']
				]);
			}

			if (empty($this->user_event_invitation_model->get_all([
				'event_id'		=> 14,
				'user_id'		=> $row['user_id']
			])['rows'][0] ?? '')) {
				$this->user_event_invitation_model->add([
					'event_id'		=> 14,
					'user_id'		=> $row['user_id']
				]);
			};

			$reject_url = sprintf(SC_USER_ADDRESS_URL . 'india/2024/signup?uid=%s&code=%s&resp=%s',
				$row['user_id'],
				$row['verification_code'],
				'no'
			);

			$accept_url = sprintf(SC_USER_ADDRESS_URL . 'india/2024/signup?uid=%s&code=%s&resp=%s',
				$row['user_id'],
				$row['verification_code'],
				'yes'
			);

			$subject = ' Important Notification: Please Update Your Details to Continue in SBWF 2024';

			$message			= $this->load->view('common/mail/part/user_event_invitation', [
				'author_name' 	=> $row['name'],
				'reject_url' 	=> $reject_url,
				'accept_url' 	=> $accept_url
			], true);

			!empty($row['email']) && self::email(
				$row['email'],
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			);

			$user_info = $this->student_model->get($row['user_id']);

			if (!empty($user_info) && !empty($user_info['mobile'])) {
				$url_parameter = sprintf('?uid=%s&code=%s&resp=%s',
					$row['user_id'],
					$row['verification_code'],
					'yes'
				);

				self::_sendWhatsappText(
					$user_info['mobile'],
					// '917303234240',
					// '917367916262',
					[
						'template'		=> '7854362971321686',
						'parameters'	=> [
							trim($row['name']),
						],
						'url_parameters'=> $url_parameter,
					]
				);
			}

			// break;
		}
	}

	public function categoryEventSignupCron ($id = 0) {
		$this->load->model('user/User_model', 'user_model');

		if (empty($user_info = $this->user_model->get($id)) ) return;

		$subject 			= ' Join the UN SDG Action Campaign – Start Your Book Today!';

		$message			= $this->load->view('common/mail/part/sdg_event_invitation', [
			'author_name' 	=> $user_info['first_name']  . ' ' . $user_info['last_name'],
		], true);

		!empty($user_info['email']) && self::email(
			$user_info['email'],
			$subject,
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
		);
	}

	public function eventAuthorSignupTnc($user_id = 0, $type = '', $event_id = 0){
		$this->load->model('user/User_model', 'user_model');

		if (empty($user_info = $this->user_model->get($user_id)) ) return;

		$template_event_id = $event_id;
		$email_template_id = 'email_user_tnc';

		$title			= self::formatEmailSubjectByEvent($email_template_id, $template_event_id) ?? '';

		if (empty($title)) return;

		$data['content']			= self::formatEmailMessageByEvent($email_template_id, [
			'author_name'			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
		], $template_event_id);

		if (empty($data['content'])) return;

		$data['heading']		= '';
		$data['subheading']		= '';
		$data['site_id']		= 1;
		$data['parent_id']		= 0;
		$data['site_code']		= '';
		$data['link']			= '';
		$data['link_text']		= '';

		$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

		$attachment		  	= [];

		$mobile = $user_info['mobile'];
		$email 	= $user_info['email'];

		$email && self::email(
			$email,
			$title,
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			$attachment
		);

		$whatsapp_template_id 	= '495180443316896';
		$parameters 			= [
			ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
		];

		if (!empty($mobile) && !empty($whatsapp_template_id) && !empty($parameters)) {
			self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> $whatsapp_template_id,
					'parameters'	=> $parameters,
				],
			);
		}
	}
}
