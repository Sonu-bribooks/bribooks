<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('whatsapp');

trait WhatsappAlert {
	use CommonWhatsapp;

	public function bookIncompleteAlert($data = []) {
		if ($this->cron_model->get_all([
			'code'	=> 'bookIncompleteCron_' . (int)$data['id']
		])) {
			return;
		}
	}

	public function bookIncompleteCron($data = []) {
		$this->load->model('competition/Competition_model', 'competition_model');

		if (
			($info = $this->book_model->get($data['id'] ?? 0)) &&
			($user_info = $this->student_model->get($info['user_id']))
		) {
			self::_sendWhatsappImage(
				$user_info['mobile'],
				[
					'template'		=> '364213019205881',
					'parameters'	=> [
						trim($user_info['first_name'] . ' ' . $info['last_name']),
					],
					'document'	=> [
						'name'	=> 'complete book',
						'link'	=> base_url('assets/marketing/incomplete.jpeg')
					]
				],
			);

			$data['title']			= vsprintf(_li('Publish your first book on %s for free'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('email_book_incomplete', [
				'author_name'			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'pages'					=> $data['pages'],
				'url'					=> USER_URL . 'account/mybooks',
			]);
			$data['link']			= '';
			$data['link_text']		= '';

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			self::email(
				$user_info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function competitionSignupScheduleCron($data = []) {
		$this->load->model('competition/Competition_model', 'competition_model');

		if (
			($info = $this->student_model->get($data['id'] ?? 0)) &&
			!($this->competition_model->checkUser([
				'user_id'	=> $info['id'],
			]))
		) {
			self::_sendWhatsappImage(
				$info['mobile'],
				[
					'template'		=> '3366669950272818',
					'parameters'	=> [
						trim($info['first_name'] . ' ' . $info['last_name']),
						(
							$info['site_id'] == $this->config->item('default_site_id')
								? '14'
								: '14'
						),
						(
							$info['site_id'] == $this->config->item('default_site_id')
								? 'Noga'
								: 'Tanish'
						),
					],
					'document'	=> [
						'name'	=> 'payment reminder',
						'link'	=> base_url('assets/marketing/user_' . $info['site_id'] . '.jpeg')
					]
				],
			);

			$data['title']			= vsprintf(_li('Your entry in %s\' Global Short Story Contest'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('email_competition_signup', [
				'author_name'			=> $info['first_name'] . ' ' . $info['last_name'],
				'published_user_name'	=> $info['site_id'] == $this->config->item('default_site_id')
					? 'Noga'
					: 'Tanish',
				'url'					=> USER_URL . 'competition/payment',
			]);
			$data['link']			= '';
			$data['link_text']		= '';

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function signupNoBookCron($data = []) {
		if (
			($info = $this->student_model->get($data['id'] ?? 0)) &&
			($this->book_model->get_all([
				'user_id'	=> $info['id']
			])['total'] == 0)
		) {
			self::_sendWhatsappImage(
				$info['mobile'],
				[
					'template'		=> '836768587309929',
					'parameters'	=> [
						$info['site_id'] == $this->config->item('default_site_id')
							? 'Noga'
							: 'Tanish',
						$info['site_id'] == $this->config->item('default_site_id')
							? 'USD 2000'
							: 'INR 95000',
					],
					'document'	=> [
						'name'	=> 'payment reminder',
						'link'	=> base_url('assets/marketing/user_' . $info['site_id'] . '.jpeg')
					]
				]
			);
		}
	}

	public function paymentReminderCron($data = []) {
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');

		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');

		if (
			($info = $this->student_model->get($data['id'] ?? 0)) &&
			(
				empty($info['subscription_plan_id']) ||
				(
					$subscription_info = $this->subscription_plan_model->get($info['subscription_plan_id']) &&
					$subscription_info['price'] == 0
				)
			)
		) {
			$path_parts = pathinfo($data['image']);

			self::_sendWhatsappImage(
				$info['mobile'],
				[
					'template'		=> $data['template'] ?? '',
					'parameters'	=> $data['parameters'] ?? [],
					'document'		=> [
						'name'		=> $path_parts['filename'],
						'link'		=> base_url('assets/marketing/' . $data['image'])
					]
				]
			);

			$data['title']			= vsprintf(_li('%s: Start earning with your book'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('email_user_completes_book', [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'book_name'			=> $book_info['name'],
				'url'				=> USER_URL . 'pricing',
			]);
			$data['link']			= '';
			$data['link_text']		= '';

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			self::email(
				$info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function publishBookWithoutOrderCron($data = []) {
		if (
			($info = $this->book_model->get($data['id'] ?? 0)) &&
			($user_info = $this->student_model->get($info['user_id'] ?? 0)) &&
			$info['status'] != 0
		) {
			$book_event_info = $this->event_book_model->get_all([
				'book_id'	=> $info['id'],
			])['rows'][0] ?? [];

			$site_id = $user_info['site_id'];

			$site_info = $this->site_model->get($site_id);

			$template = 'email_user_publish_book_without_order';

			$ordered_author_copies = $this->order_model->getAuthorProducts([
				'product_id'	=> $info['id'],
				'user_id'		=> $info['user_id']
			]);

			if ($ordered_author_copies > 0) return;

			$path_parts = pathinfo($data['image'] ?? '');

			// $data['title']			= self::formatEmailSubject($template, $site_id, [
			// 		'author_name'	  	=> $info['author_name'],
			// 		'book_name'	  		=> $info['name'],
			// 		'event_id'	  		=> $book_event_info['event_id'] ?? 0,
			// 	]) ?? vsprintf(_li('%s: Order printed copies of your recently published book'), [
			// 	get_settings('system_name')
			// ]);

			// $data['heading']		= '';
			// $data['subheading']		= '';
			// $data['content']		= self::formatEmailMessage($template, [
			// 	'event_id'	  		=> $book_event_info['event_id'] ?? 0,
			// 	'author_name'		=> $info['author_name'],
			// 	'school_name'		=> $site_info['name'],
			// 	'book_name'	  		=> $info['name'],
			// 	'url'				=> USER_URL . 'bookstore/' . $info['slug'],
			// 	'my_certificates_url'	=> USER_URL . 'account/mycertificates',
			// ], $site_id);
			// $data['site_id']		= $site_id;
			// $data['parent_id']		= $site_info['parent_id'];
			// $data['site_code']		= $site_info['site_code'];
			// $data['link']			= '';
			// $data['link_text']		= '';
			// $data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);;

			// $message 				= $this->load->view('common/mail/templates/site/general', $data, true);

			// $mobile		= $user_info['mobile'];
			// $email		= $user_info['email'];

			// if ($mobile) {
			// 	self::sendOnextelWhatsappMessage(
			// 		$mobile,
			// 		[
			// 			'template_id'	=> '01kev9ct425e3nfyy2ag5azy50',
			// 			'parameters'	=> [
			// 				trim($info['author_name']),
			// 				USER_URL . 'bookstore/' . $info['slug']
			// 			]
			// 		],
			// 	);
			// }

			// self::email(
			// 	$email,
			// 	$data['title'],
			// 	$message,
			// 	[],
			// 	(ENVIRONMENT == 'production') ? ['communication@bribooks.com'] : []
			// );

			//*********new message template code by sonu************ */
			
			$data['mobile']					= $user_info['mobile'];
			$data['email']					= $user_info['email'];
			$data['event_id']				= $book_event_info['event_id'] ?? 0;
			$data['author_name']			= $info['author_name'];
			$data['school_name']			= $site_info['name'];
			$data['book_name']	  			= $info['name'];
			$data['book_url']				= USER_URL . 'bookstore/' . $info['slug'];
			$data['my_certificates_url']	= USER_URL . 'account/mycertificates';
			$data['site_id']				= $site_id;
			$data['parent_id']				= $site_info['parent_id'];
			$data['site_code']				= $site_info['site_code'];
			$data['link']					= '';
			$data['link_text']				= '';
			$data['unsubscribe_url']		= gen_unsubscribe_url($user_info['email']);
			$data['system_name']			= get_settings('system_name');

			CI_Events::trigger('publish_book_without_order', [
				'book_id'	=> $info['id'],
				'data'		=> $data
			]);

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('user_publish_book_without_order_%d_%d', (int)$user_info['id'], (int)$info['id'])
			]);

		}
	}

	public function firstOrderAlert() {
		$results = $this->order_model->get_all([
			'ne_status'	=> 0,
		])['rows'] ?? [];

		log_kb([
			'orders'	=> $results
		]);

		$exclude = [];

		foreach ($results as $item) {
			$ordered_books = $this->order_model->getProducts($item['id']);

			foreach ($ordered_books as $book) {
				if (!in_array($book['product_id'], $exclude)) {
					self::firstOrderAlertCron($book['product_id']);

					$exclude[] = $book['product_id'];
				}
			}
		}
	}

	public function firstOrderAlertCron($book_id = 0) {
		$book_info = $this->book_model->get($book_id);
		$user_info = $this->student_model->get($book_info['user_id']);

		if (empty($book_info) || empty($user_info)) return;

		log_kb([
			$book_id,
			$user_info['mobile'],
			$user_info['email'],
		]);

		log_kb([
			$user_info['mobile'],
			[
				'template'		=> '814025376399085',
				'parameters'	=> [
					trim($book_info['author_name']),
				],
			]
		]);

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '814025376399085',
				'parameters'	=> [
					trim($book_info['author_name']),
				],
			]
		);

		$data['title']			= _li('Author Royalty Program');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/royalty_program', [
			'books'				=> $this->book_model->get_all([
				'user_id'		=> $book_info['user_id'],
				'ne_status'		=> 0,
			])['rows'] ?? [],
			'author_name'		=> $book_info['author_name']
		], true);

		$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

		log_kb([
			$user_info['email'],
			$data['title'],
		]);

		$user_info['email'] && self::email(
			$user_info['email'],
			$data['title'],
			$message,
			[],
			[],
		);
	}

	public function publishBookSellAlertCron($data = []) {
		if (
			($info = $this->book_model->get($data['id'] ?? 0)) &&
			($user_info = $this->student_model->get($info['user_id'] ?? 0)) &&
			$info['status'] != 0
		) {

			$ordered_author_copies = $this->order_model->getAuthorProducts([
				'product_id'	=> $info['id'],
				'user_id'		=> $info['user_id']
			]);

			if ($ordered_author_copies > 0) return;

			$path_parts = pathinfo($data['image']);

			self::_sendWhatsappText(
				$user_info['mobile'],
				[
					'template'		=> '396959322609660',
					'parameters'	=> [
						trim($info['author_name']),
						USER_URL . 'bookstore/' . $info['slug']
					],
				]
			);

			$data['title']			= vsprintf(_li('%s: You book can earn you Author Royalty'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('email_book_selling_communication', [
				'author_name'		=> $info['author_name'],
				'url'				=> USER_URL . 'bookstore/' . $info['slug'],
			]);
			$data['link']			= '';
			$data['link_text']		= '';

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			self::email(
				$user_info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	private function scheduleWhatsapp($data = [], $method = 'paymentReminderCron', $time = '+24 hours') {
		$this->cron_model->add([
			'code'			=> $method . '_' . (int)$data['id'],
			'action'		=> 'alert_model->' . $method,
			'data'			=> [$data],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime($time)),
		]);
	}

	public function publishBookAfterDateCron($book_id) {
	}

	public function publishBookAfterDatePCCron($book_id) {
	}
}
