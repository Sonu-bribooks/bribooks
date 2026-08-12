<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CommonAlert {
	public function cron($id = 0, $type = NULL) {
		$this->cron_model->add([
			'code'			=> $type . '_' . $id,
			'action'		=> 'alert_model->' . $type,
			'data'			=> [$id],
			'alert_date'	=> date('Y-m-d H:i:s'),
		]);
	}

	private function additionalEmails($site_id = 0) {
		$alert_emails = explode(',', get_site($site_id, 'email_alert'));

		return array_unique(array_merge($this->admin_emails, $alert_emails));
	}

	private function formatMessage($key, $data, $site_id = 0) {
		$find = [
			'{name}',
			'{book_name}',
			'{order_id}',
		];

		$replace = [
			'name'				=> $data['name'] ?? '',
			'book_name'			=> $data['book_name'] ?? '',
			'order_id'			=> $data['order_id'] ?? '',
		];

		return str_replace($find, $replace, get_settings($key));
	}

	private function formatEmailMessage($key, $data, $site_id = 0) {
		$this->load->model('common/Site_model', 'site_model');

		$find = [
			'{otp}',
			'{author_name}',
			'{owner_name}',
			'{username}',
			'{password}',
			'{book_name}',
			'{book_thumb}',
			'{pages}',
			'{login_url}',
			'{url}',
			'{url_2}',
			'{date}',
			'{published_user_name}',
			'{name}',
			'{email}',
			'{mobile}',
			'{country}',
			'{state}',
			'{city}',
			'{designation}',
			'{grades}',
			'{sections}',
			'{authorized_person}',
			'{school_head}',
			'{school_name}',
			'{parent_name}',
			'{referral_name}',
			'{site_id}',
			'{my_certificates_url}',
			'{awards_url}',
			'{prizes_url}',
			'{book_author_name}',
			'{number}',
		];

		$replace = [
			'otp'					=> $data['otp'] ?? '',
			'author_name'			=> $data['author_name'] ?? '',
			'owner_name'			=> $data['owner_name'] ?? '',
			'username'				=> $data['username'] ?? '',
			'password'				=> $data['password'] ?? '',
			'book_name'				=> $data['book_name'] ?? '',
			'book_thumb'			=> $data['book_thumb'] ?? '',
			'pages'					=> $data['pages'] ?? '',
			'login_url'				=> $data['login_url'] ?? '',
			'url'					=> $data['url'] ?? '',
			'url_2'					=> $data['url_2'] ?? '',
			'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
			'published_user_name'	=> $data['published_user_name'] ?? '',
			'name'					=> $data['name'] ?? '',
			'email'					=> $data['email'] ?? '',
			'mobile'				=> $data['mobile'] ?? '',
			'country'				=> $data['country'] ?? '',
			'state'					=> $data['state'] ?? '',
			'city'					=> $data['city'] ?? '',
			'designation'   		=> $data['designation'] ?? '',
			'grades'   				=> $data['grades'] ?? '',
			'sections'   			=> $data['sections'] ?? '',
			'authorized_person'   	=> $data['authorized_person'] ?? '',
			'school_head'   		=> $data['school_head'] ?? '',
			'school_name'   		=> $data['school_name'] ?? '',
			'parent_name'   		=> $data['parent_name'] ?? '',
			'referral_name'   		=> $data['referral_name'] ?? '',
			'site_id'   			=> $data['site_id'] ?? '',
			'my_certificates_url'   => $data['my_certificates_url'] ?? '',
			'awards_url'   			=> $data['awards_url'] ?? '',
			'prizes_url'   			=> $data['prizes_url'] ?? '',
			'book_author_name'   	=> $data['book_author_name'] ?? '',
			'number'   				=> $data['number'] ?? 0,
		];

		$template_body = '';

		if (!empty($site_id)) {
			$template_body = self::getEmailTemplateContent($key, $site_id, ($data['event_id'] ?? 0));
		}

		if (empty($template_body)) {
			$site_info = $this->site_model->get($site_id);
			if (!empty($site_info) && $site_info['country_code'] !== 'IN') {
				$template_body = self::getEmailTemplateContent($key, 2);
			} else {
				$template_body = self::getEmailTemplateContent($key, 1);
			}
		}

		return str_replace($find, $replace, (!empty($template_body) ? $template_body : get_settings($key)));
	}

	public function contactUsAlert($data = []) {
		$this->cron_model->add([
			'code'			=> 'contactUsAlertCron',
			'action'		=> 'alert_model->contactUsAlertCron',
			'data'			=> [$data],
			'site_id'		=> 0,
			'alert_date'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function contactUsAlertCron($data = []) {
		$data['title']			= _li('New contact request ') . get_settings('system_name');
		$data['heading']		= '';
		$data['subheading']		= '';
		$data['content']		= $this->load->view('common/mail/part/contact_us', $data, true);
		$data['link']			= site_url();
		$data['link_text']		= _l('login');

		$message 				= $this->load->view('common/mail/templates/1/general', $data, true);

		self::email(
			'support@bribooks.com',
			$data['title'],
			$message,
			[],
			$this->admin_emails
		);
	}

	public function validationOtp($email = '', $subject = '', $otp = '') {
		$user_info = $this->user_model->get_all(['email' => $email])['rows'][0] ?? [];

		$role_id = 2;

		if (!empty($user_info)) {
			$role_id = $user_info['role_id'];
		}

		// Email Alert Parent/Student
		if ($role_id == 9) {
			$template_id 			= 'school_email_otp';
			$data['title']			= $this->formatEmailSubject('school_email_otp', 0, []) ?? (_l('Your login Verification code for School Dashboard'));
		} elseif ($role_id == 3) {
			$template_id 			= 'teacher_email_otp';
			$data['title']			= $this->formatEmailSubject('teacher_email_otp', 0, []) ?? (_l('Your login Verification code for the Teacher Dashboard'));
		} else {
			$template_id 			= 'email_otp';
			$data['title']			= _l('Your login Verification code for ') . get_settings('system_name');
		}

		$data['heading']		= '';
		$data['subheading']		= '';
		$data['content']		= self::formatEmailMessage($template_id, [
			'otp'			=> $otp,
			'author_name'	=> 'User',
		]);
		$data['link']			= '';
		$data['link_text']		= _l('login');

		// $message 				= $this->load->view('common/mail/general', $data, true);
		$message 				= $data['content'];

		self::email(
			$email,
			$data['title'],
			$message,
			[],
			[]
		);
	}

	public function formatEmailSubject($template_id = 0, $site_id = 0, $data = []) {
		if (!empty($template_id) && !empty($site_id)) {
			$this->load->model('common/AddTemplate_model', 'addtemplate_model');
			$this->load->model('common/Site_model', 'site_model');

			if (!empty($data['event_id'])) {
				$template_info = $this->addtemplate_model->getEventTemplate($data['event_id'], $template_id);
			} else {
				$template_info = $this->addtemplate_model->getByTemplateId($site_id, $template_id);
			}

			if (empty($template_info)) {
				$site_info = $this->site_model->get($site_id);

				if (!empty($site_info) && $site_info['country_code'] !== 'IN') {
					$template_info = $this->addtemplate_model->getByTemplateId(2, $template_id);
				} else {
					$template_info = $this->addtemplate_model->getByTemplateId(1, $template_id);
				}
			}

			if (!empty($template_info['subject'])) {
				$find = [
					'{otp}',
					'{author_name}',
					'{owner_name}',
					'{username}',
					'{password}',
					'{book_name}',
					'{book_thumb}',
					'{pages}',
					'{url}',
					'{url_2}',
					'{date}',
					'{published_user_name}',
					'{name}',
					'{email}',
					'{mobile}',
					'{country}',
					'{state}',
					'{city}',
					'{designation}',
					'{grades}',
					'{sections}',
					'{authorized_person}',
					'{school_head}',
					'{parent_name}',
					'{school_name}'
				];

				$replace = [
					'otp'					=> $data['otp'] ?? '',
					'author_name'			=> $data['author_name'] ?? '',
					'owner_name'			=> $data['owner_name'] ?? '',
					'username'				=> $data['username'] ?? '',
					'password'				=> $data['password'] ?? '',
					'book_name'				=> $data['book_name'] ?? '',
					'book_thumb'			=> $data['book_thumb'] ?? '',
					'pages'					=> $data['pages'] ?? '',
					'url'					=> $data['url'] ?? '',
					'url_2'					=> $data['url_2'] ?? '',
					'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
					'published_user_name'	=> $data['published_user_name'] ?? '',
					'name'					=> $data['name'] ?? '',
					'email'					=> $data['email'] ?? '',
					'mobile'				=> $data['mobile'] ?? '',
					'country'				=> $data['country'] ?? '',
					'state'					=> $data['state'] ?? '',
					'city'					=> $data['city'] ?? '',
					'designation'   		=> $data['designation'] ?? '',
					'grades'   				=> $data['grades'] ?? '',
					'sections'   			=> $data['sections'] ?? '',
					'authorized_person'   	=> $data['authorized_person'] ?? '',
					'school_head'   		=> $data['school_head'] ?? '',
					'parent_name'   		=> $data['parent_name'] ?? '',
					'school_name'   		=> $data['school_name'] ?? ''
				];

				return str_replace($find, $replace, $template_info['subject']);
			}

			$site_info = $this->site_model->get($site_id);

			if (empty($site_info['parent_id'])) return;
			if ($site_info['parent_id'] == $site_id) return;

			$parent_template_info = $this->addtemplate_model->getByTemplateId($site_info['parent_id'], $template_id);

			if (!empty($parent_template_info['subject'])) {
				$find = [
					'{otp}',
					'{author_name}',
					'{username}',
					'{password}',
					'{book_name}',
					'{book_thumb}',
					'{pages}',
					'{url}',
					'{url_2}',
					'{date}',
					'{published_user_name}',
					'{name}',
					'{email}',
					'{mobile}',
					'{country}',
					'{state}',
					'{city}',
					'{designation}',
					'{grades}',
					'{sections}',
					'{authorized_person}',
					'{school_head}',
					'{parent_name}',
					'{school_name}'
				];

				$replace = [
					'otp'					=> $data['otp'] ?? '',
					'author_name'			=> $data['author_name'] ?? '',
					'username'				=> $data['username'] ?? '',
					'password'				=> $data['password'] ?? '',
					'book_name'				=> $data['book_name'] ?? '',
					'book_thumb'			=> $data['book_thumb'] ?? '',
					'pages'					=> $data['pages'] ?? '',
					'url'					=> $data['url'] ?? '',
					'url_2'					=> $data['url_2'] ?? '',
					'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
					'published_user_name'	=> $data['published_user_name'] ?? '',
					'name'					=> $data['name'] ?? '',
					'email'					=> $data['email'] ?? '',
					'mobile'				=> $data['mobile'] ?? '',
					'country'				=> $data['country'] ?? '',
					'state'					=> $data['state'] ?? '',
					'city'					=> $data['city'] ?? '',
					'designation'   		=> $data['designation'] ?? '',
					'grades'   				=> $data['grades'] ?? '',
					'sections'   			=> $data['sections'] ?? '',
					'authorized_person'   	=> $data['authorized_person'] ?? '',
					'school_head'   		=> $data['school_head'] ?? '',
					'parent_name'   		=> $data['parent_name'] ?? '',
					'school_name'   		=> $data['school_name'] ?? ''
				];

				return str_replace($find, $replace, $parent_template_info['subject']);
			}

			return;
		}
	}

	public function getEmailTemplateContent($template_id = 0, $site_id = 0, $event_id = 0) {
		if (!empty($template_id) && !empty($site_id)) {
			$this->load->model('common/AddTemplate_model', 'addtemplate_model');

			if (!empty($event_id)) {
				$template_info = $this->addtemplate_model->getEventTemplate($event_id, $template_id);
			} else {
				$template_info = $this->addtemplate_model->getByTemplateId($site_id, $template_id);
			}

			if (!empty($template_info['body'])) return $template_info['body'];

			$site_info = $this->site_model->get($site_id);

			if (empty($site_info['parent_id'])) return;
			if ($site_info['parent_id'] == $site_id) return;

			$parent_template_info = $this->addtemplate_model->getByTemplateId($site_info['parent_id'], $template_id);

			if (!empty($parent_template_info['body'])) return $parent_template_info['body'];

			return;
		}
	}

	public function orderPrivyAlert($order_id = 0) {
		if (!empty($order_id) && !empty($order_info = $this->order_model->get($order_id))) {
			$bcc = [];

			if (!empty($order_privy_alert = get_settings('order_privy_alert'))) {
				$bcc = explode(',', $order_privy_alert);
			};
			$this->load->model('order/OrderProduct_model', 'order_product_model');

			$order_products = $this->order_product_model->get_all([
				'order_id' =>  $order_id
			])['rows'] ?? [];

			foreach ($order_products as $product_info) {
				$book_info = $this->book_model->get($product_info['product_id']);

				$book_event_info = $this->event_book_model->get_all([
					'book_id'			=> $book_info['id'],
					'is_active_event'	=> 1
				])['rows'][0] ?? [];

				$order_product[] = [
					'book_name'			=> $book_info['name'],
					'order_id'			=> $order_info['id'],
					'book_quantity'		=> $product_info['quantity'],
					'event_name'		=> $book_event_info['event_name'] ?? 'General'
				];
			}

			$subject = 'Urgent:Order Confirmation Required';

			$message			= $this->load->view('common/mail/part/order_privy_alert', [
				'order_code' 	=> $order_info['order_code'],
				'product_info'	=> $order_product,
				'currency_code' => $order_info['currency_code'],
				'total' 		=> $order_info['total'],
			], true);

			self::email(
				'communication@bribooks.com',
				$subject,
				$message,
				[],
				$bcc
			);
		}
	}

	public function enrolToHallOfFame($book_id = 0) {
		if (!empty($book_id) && !empty($book_info = $this->book_model->get($book_id))) {
			$subject = 'Celebrating Your Entry in the Global Hall of Fame for Authors!';

			$hall_of_fame_url = USER_URL . 'hall-of-fame';

			$content = '<p>Dear ' . $book_info['author_name'] . ',</p>
			<p>Congratulations! Your hard work and dedication have rightfully earned you a spot in the esteemed Global Hall of Fame with your book, ' . $book_info['name'] . '!</p>
			<p>This accomplishment crowns you as a globally recognized author, marking a significant milestone in your writing journey. You\'ve joined a prestigious list of Star authors from over 26 countries who have left their mark like yourself.</p>
			<p>To witness your success among these esteemed authors, please visit the Global Hall of Fame page: ' . $hall_of_fame_url . '.</p>
			<p>We take pride in celebrating your achievement as a distinguished published author.</p>
			<p>Warm Regards,<br>Team BriBooks</p>';

			$mobile = $user_info['mobile'];
			$email 	= $user_info['email'];

			$mobile && self::_sendWhatsappText(
				$mobile,
				[
					'template'		=>  '731503832248661',
					'parameters'	=> [
						$book_info['author_name'],
						$book_info['name'],
						$hall_of_fame_url
					]
				],
			);
		}
	}

	public function formatEventEmailSubject($template_id = '', $event_id = 0, $data = []) {
		if (!empty($template_id) && !empty($event_id)) {
			$this->load->model('event/EventTemplate_model', 'event_template_model');

			$template_info = $this->event_template_model->getByTemplateId($event_id, $template_id);

			if (!empty($template_info['subject'])) {
				$find = [
					'{author_name}',
					'{owner_name}',
					'{username}',
					'{book_name}',
					'{url}',
					'{date}',
					'{name}',
					'{email}',
					'{mobile}',
					'{country}',
					'{state}',
					'{city}',
					'{designation}',
					'{grades}',
					'{sections}',
					'{authorized_person}',
					'{school_name}'
				];

				$replace = [
					'author_name'			=> $data['author_name'] ?? '',
					'owner_name'			=> $data['owner_name'] ?? '',
					'username'				=> $data['username'] ?? '',
					'book_name'				=> $data['book_name'] ?? '',
					'url'					=> $data['url'] ?? '',
					'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
					'name'					=> $data['name'] ?? '',
					'email'					=> $data['email'] ?? '',
					'mobile'				=> $data['mobile'] ?? '',
					'country'				=> $data['country'] ?? '',
					'state'					=> $data['state'] ?? '',
					'city'					=> $data['city'] ?? '',
					'designation'   		=> $data['designation'] ?? '',
					'grades'   				=> $data['grades'] ?? '',
					'sections'   			=> $data['sections'] ?? '',
					'authorized_person'   	=> $data['authorized_person'] ?? '',
					'school_name'   		=> $data['school_name'] ?? ''
				];

				return str_replace($find, $replace, $template_info['subject']);
			}
		}
	}

	private function formatEventEmailMessage($template_id = 0, $data = [], $event_id = 0) {
		if (!empty($template_id) && !empty($event_id)) {
			$this->load->model('event/EventTemplate_model', 'event_template_model');

			$find = [
				'{author_name}',
				'{owner_name}',
				'{username}',
				'{password}',
				'{book_name}',
				'{url}',
				'{author_url}',
				'{partner_url}',
				'{rank_url}',
				'{date}',
				'{date_2}',
				'{name}',
				'{email}',
				'{mobile}',
				'{country}',
				'{state}',
				'{city}',
				'{designation}',
				'{grades}',
				'{sections}',
				'{authorized_person}',
				'{school_name}',
				'{site_id}',
				'{day}',
			];

			$replace = [
				'author_name'			=> $data['author_name'] ?? '',
				'owner_name'			=> $data['owner_name'] ?? '',
				'username'				=> $data['username'] ?? '',
				'password'				=> $data['password'] ?? '',
				'book_name'				=> $data['book_name'] ?? '',
				'url'					=> $data['url'] ?? '',
				'author_url'			=> $data['author_url'] ?? '',
				'partner_url'			=> $data['partner_url'] ?? '',
				'rank_url'				=> $data['rank_url'] ?? '',
				'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
				'date_2'				=> !empty($data['remaining_time']) ? date('M j, Y', strtotime($data['remaining_time'])) : '',
				'name'					=> $data['name'] ?? '',
				'email'					=> $data['email'] ?? '',
				'mobile'				=> $data['mobile'] ?? '',
				'country'				=> $data['country'] ?? '',
				'state'					=> $data['state'] ?? '',
				'city'					=> $data['city'] ?? '',
				'designation'   		=> $data['designation'] ?? '',
				'grades'   				=> $data['grades'] ?? '',
				'sections'   			=> $data['sections'] ?? '',
				'authorized_person'   	=> $data['authorized_person'] ?? '',
				'school_name'   		=> $data['school_name'] ?? '',
				'parent_name'   		=> $data['parent_name'] ?? '',
				'site_id'   			=> $data['site_id'] ?? '',
				'day'   			    => $data['day'] ?? '',
			];

			$template_info = $this->event_template_model->getByTemplateId($event_id, $template_id);

			return str_replace($find, $replace, $template_info['body']);
		}
	}

	public function formatCertificateMessage($body = null, $data = [], $event_id = 0) {
		$find = [
			'{author_name}',
			'{author_first_name}',
			'{book_name}',
			'{book_url}',
			'{book_isbn}',
			'{book_sold_count}',
			'{copies_sold}',
			'{certificate_url}',
			'{medallion_name}',
			'{medallion_url}',
			'{date}',
			'{school_name}',
			'{city}',
			'{state}',
			'{league_url}',
		];

		$replace = [
			'author_name'			=> $data['author_name'] ?? '',
			'author_first_name'		=> $data['author_first_name'] ?? '',
			'book_name'				=> $data['book_name'] ?? '',
			'book_url'				=> $data['book_url'] ?? '',
			'book_isbn'				=> $data['book_isbn'] ?? '',
			'book_sold_count'		=> $data['book_sold_count'] ?? '',
			'copies_sold'			=> $data['copies_sold'] ?? '',
			'certificate_url'		=> $data['certificate_url'] ?? '',
			'medallion_name'		=> $data['medallion_name'] ?? '',
			'medallion_url'			=> $data['medallion_url'] ?? '',
			'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
			'school_name'			=> $data['school_name'] ?? '',
			'city'					=> $data['city'] ?? '',
			'state'					=> $data['state'] ?? '',
			'league_url'			=> $data['league_url'] ?? '',
		];

		return str_replace($find, $replace, $body);
	}

	public function formatEmailSubjectByEvent($template_id = 0, $event_id = 0, $data = []) {
		if (!empty($template_id)) {
			$this->load->model('common/AddTemplate_model', 'addtemplate_model');

			$template_info = $this->addtemplate_model->getEventTemplate($event_id, $template_id);

			if (!empty($template_info['subject'])) {
				$find = [
					'{otp}',
					'{author_name}',
					'{owner_name}',
					'{username}',
					'{password}',
					'{book_name}',
					'{book_thumb}',
					'{pages}',
					'{url}',
					'{url_2}',
					'{date}',
					'{published_user_name}',
					'{name}',
					'{email}',
					'{mobile}',
					'{country}',
					'{state}',
					'{city}',
					'{designation}',
					'{grades}',
					'{sections}',
					'{authorized_person}',
					'{school_head}',
					'{parent_name}',
					'{school_name}'
				];

				$replace = [
					'otp'					=> $data['otp'] ?? '',
					'author_name'			=> $data['author_name'] ?? '',
					'owner_name'			=> $data['owner_name'] ?? '',
					'username'				=> $data['username'] ?? '',
					'password'				=> $data['password'] ?? '',
					'book_name'				=> $data['book_name'] ?? '',
					'book_thumb'			=> $data['book_thumb'] ?? '',
					'pages'					=> $data['pages'] ?? '',
					'url'					=> $data['url'] ?? '',
					'url_2'					=> $data['url_2'] ?? '',
					'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
					'published_user_name'	=> $data['published_user_name'] ?? '',
					'name'					=> $data['name'] ?? '',
					'email'					=> $data['email'] ?? '',
					'mobile'				=> $data['mobile'] ?? '',
					'country'				=> $data['country'] ?? '',
					'state'					=> $data['state'] ?? '',
					'city'					=> $data['city'] ?? '',
					'designation'   		=> $data['designation'] ?? '',
					'grades'   				=> $data['grades'] ?? '',
					'sections'   			=> $data['sections'] ?? '',
					'authorized_person'   	=> $data['authorized_person'] ?? '',
					'school_head'   		=> $data['school_head'] ?? '',
					'parent_name'   		=> $data['parent_name'] ?? '',
					'school_name'   		=> $data['school_name'] ?? ''
				];

				return str_replace($find, $replace, $template_info['subject']);
			}

			return;
		}
	}

	private function formatEmailMessageByEvent($template_id, $data, $event_id = 0) {
		$find = [
			'{otp}',
			'{author_name}',
			'{owner_name}',
			'{username}',
			'{password}',
			'{book_name}',
			'{book_thumb}',
			'{pages}',
			'{login_url}',
			'{url}',
			'{url_2}',
			'{date}',
			'{published_user_name}',
			'{name}',
			'{email}',
			'{mobile}',
			'{country}',
			'{state}',
			'{city}',
			'{designation}',
			'{grades}',
			'{sections}',
			'{authorized_person}',
			'{school_head}',
			'{school_name}',
			'{parent_name}',
			'{referral_name}',
			'{site_id}',
			'{my_certificates_url}',
			'{reward_url}',
			'{register_type}',
			'{register_type_value}',
			'{number}',
		];

		$replace = [
			'otp'					=> $data['otp'] ?? '',
			'author_name'			=> $data['author_name'] ?? '',
			'owner_name'			=> $data['owner_name'] ?? '',
			'username'				=> $data['username'] ?? '',
			'password'				=> $data['password'] ?? '',
			'book_name'				=> $data['book_name'] ?? '',
			'book_thumb'			=> $data['book_thumb'] ?? '',
			'pages'					=> $data['pages'] ?? '',
			'login_url'				=> $data['login_url'] ?? '',
			'url'					=> $data['url'] ?? '',
			'url_2'					=> $data['url_2'] ?? '',
			'date'					=> !empty($data['datetime']) ? date('M j, Y', strtotime($data['datetime'])) : '',
			'published_user_name'	=> $data['published_user_name'] ?? '',
			'name'					=> $data['name'] ?? '',
			'email'					=> $data['email'] ?? '',
			'mobile'				=> $data['mobile'] ?? '',
			'country'				=> $data['country'] ?? '',
			'state'					=> $data['state'] ?? '',
			'city'					=> $data['city'] ?? '',
			'designation'   		=> $data['designation'] ?? '',
			'grades'   				=> $data['grades'] ?? '',
			'sections'   			=> $data['sections'] ?? '',
			'authorized_person'   	=> $data['authorized_person'] ?? '',
			'school_head'   		=> $data['school_head'] ?? '',
			'school_name'   		=> $data['school_name'] ?? '',
			'parent_name'   		=> $data['parent_name'] ?? '',
			'referral_name'   		=> $data['referral_name'] ?? '',
			'site_id'   			=> $data['site_id'] ?? '',
			'my_certificates_url'   => $data['my_certificates_url'] ?? '',
			'reward_url'   			=> $data['reward_url'] ?? '',
			'register_type'   		=> $data['register_type'] ?? '',
			'register_type_value'   => $data['register_type_value'] ?? '',
			'number'   				=> $data['number'] ?? '',
		];

		$template_info = $this->addtemplate_model->getEventTemplate($event_id, $template_id);

		return str_replace($find, $replace, (!empty($template_info) ? $template_info['body'] : get_settings($template_id)));
	}
}
