<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait BookAlert {
	public function publishBook($book_id) {
		if (empty($book_info = $this->book_model->get($book_id))) return;

		if (!self::_createEventPublishBookCommunication($book_id)) {
			self::cron($book_id, 'publishBookCron');
		}

		$country_code 	= $this->input->cookie('user_country_code', true) ?? '';
		$options 		= bookBuyOptions($book_id, $country_code);

		if (!empty($options)) {
			if (!in_array('printed', $options)) {
				return;
			}
		}

		// publish book without order
		self::scheduleWhatsapp(
			[
				'id'		=> (int)$book_id,
				'schedule'	=> 24
			],
			'publishBookWithoutOrderCron',
			ENVIRONMENT == 'production' ? '+24 hours' : '+1 minutes'
		);

		// published book selling alert
		// self::scheduleWhatsapp(
		// 	[
		// 		'id'		=> (int)$book_id,
		// 		'schedule'	=> 12
		// 	],
		// 	'publishBookSellAlertCron',
		// 	ENVIRONMENT == 'production' ? '+12 hours' : '+1 minutes'
		// );
	}

	public function publishBookCron($book_id = 0) {
		if (
			($book_info = $this->book_model->get($book_id)) &&
			$book_info['status'] == 1 &&
			$user_info = $this->student_model->get($book_info['user_id'])
		) {
			$data['title']			= sprintf(_li('%s is published successfully on BriBooks bookstore.'), $book_info['name']);
			$data['heading']		= sprintf(_li('%s is published successfully on BriBooks bookstore.'), $book_info['name']);

			// mobile writing patch
			$this->load->model('user/UserCover_model', 'user_cover_model');

			if (
				empty($book_info['user_cover_id']) ||
				empty($user_cover_info = $this->user_cover_model->get($book_info['user_cover_id'])) ||
				empty($user_cover_info['design'])
			) {
				self::_generateCover($book_info);
				$book_info = $this->book_model->get($book_id);
			}

			$data['content']		= $this->load->view('common/mail/part/publish_book', [
				'book'			=> [
					'name'		=> $book_info['name'],
					'thumb'		=> $this->config->item('s3_base_url') . 'public/' . $book_info['cover_image'],
					'url'		=> USER_URL . 'bookstore/' . $book_info['slug'],
				],
				'user'			=> [
					'name'		=> $book_info['author_name'],
					'city'		=> $user_info['city'] ?? 'city',
					'state'		=> $user_info['state'] ?? 'state'
				],
			], true);

			$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

			$message 				= $this->load->view('common/mail/templates/' . (strpos($user_info['source'], 'NYAFIND') !== false ? 3 : 1) . '/general', $data, true);

			$mobile = $user_info['mobile'];
			$email 	= $user_info['email'];

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				['communication@bribooks.com']
			);

			self::_resetBookCaches($book_info);
		}
	}

	private function _generateCover($book_info = []) {
		$this->load->model('design/Cover_model', 'cover_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/Bookstore_model', 'bookstore_model');

		$this->load->library('Emoji_lib', 'emoji_lib');

		$cover_info = !empty($book_info['cover_id'])
			? $this->cover_model->get($book_info['cover_id'])
			: [];
		$heading_style = !empty($cover_info['heading_style'])
			? json_decode($cover_info['heading_style'], true)
			: [];

		$data['multiplier'] 	= 648 / 285;
		$data['fc_bleed'] 		= 1;
		$data['width'] 			= 648;
		$data['height'] 		= 913;
		$data['book'] 			= $book_info;
		$data['cover_info'] 	= $cover_info;
		$data['cover_img_url'] 	= $this->config->item('s3_base_url') . 'public/';;
		$data['heading_style'] 	= !empty($heading_style['style'])
			? $heading_style['style']
			: [];

		// generate pdf
		$html = $this->load->view('backend/admin/books/cover', $data, true);
		// echo $html;
		// die;

		$dompdf = new Dompdf([
			// 'debugLayout' 	=> true,
			// 'debugCss'		=> true,
			// 'debugPng'		=> true,
		]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		// $dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// $dompdf->loadHtml($html);
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper(
			[
				0,
				0,
				$data['width'] ,
				$data['height']
			],
			'portrait'
		);

		$dompdf->render();

		// $dompdf->stream('book_' . (ENVIRONMENT !== 'production' ? 't_' : '') . (int)$book_info['id'] . '.pdf');
		// exit;

		// Render the HTML as PDF
		$output = $dompdf->output();

		$file_path = FCPATH . 'uploads/pdfs/book_' . (ENVIRONMENT !== 'production' ? 't_' : '') . (int)$book_info['id'] . '_' . (int)$book_info['version'];

		file_put_contents($file_path . '.pdf', $output);

		log_kb([
			'book_' . (ENVIRONMENT !== 'production' ? 't_' : '') . (int)$book_info['id'] . '.png',
			strlen($output),
			rtrim($this->config->item('s3_book_covers'), '/')
		]);

		$imagick = new Imagick();
		$imagick->readImage($file_path . '.pdf[0]');
		$imagick->setResolution(648, 972);
		$imagick->setImageFormat('png');
		$imagick->writeImage($file_path . '.png');

		// save to s3 bucket
		$this->load->library('s3');

		log_kb($this->s3->amazonS3Upload(
			'book_' . (ENVIRONMENT !== 'production' ? 't_' : '') . (int)$book_info['id'] . '_' . (int)$book_info['version'] . '.png',
			$file_path . '.png',
			rtrim($this->config->item('s3_book_covers'), '/')
		));

		$update_data = [
			'cover_image'	=> 'BookCovers/book_' . (ENVIRONMENT !== 'production' ? 't_' : '') . (int)$book_info['id'] . '_' . (int)$book_info['version'] . '.png'
		];

		$this->book_model->edit($book_info['id'], $update_data);

		$this->book_version_model->editByBookId($book_info['id'], $book_info['version'], $update_data);

		if (!empty($bookstore_info = $this->bookstore_model->getByBookId($book_info['id']))) {
			$this->bookstore_model->edit($bookstore_info['id'], $update_data);
		}

		// update book cover
		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->ranking_lib->updateBookInfo($book_info['id']);

		unlink($file_path . '.pdf');
		unlink($file_path . '.png');
	}

	public function bookApproved($id, $subject) {
		if (
			($book_info = $this->book_model->get($id)) &&
			$user_info = $this->student_model->get($book_info['user_id'])
		) {

			$data['title']		  = vsprintf(_li($subject), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']	 	= '';

			if ($book_info['status'] == 0) {
				$data['content']		= self::formatEmailMessage('email_book_reject', [
					'author_name'	=> $book_info['author_name'],
					'book_name'		=> $book_info['name'],
				]);
			} else if ($book_info['status'] == 1){
				$data['content']		=  (strpos($user_info['source'], 'NYAFIND') !== false ? self::formatEmailMessage('book_approved_nyaf', [
					'author_name'	=> $book_info['author_name'],
					'book_name'		=> $book_info['name'],
				])  : self::formatEmailMessage('book_approved', [
					'author_name'	=> $book_info['author_name'],
					'book_name'		=> $book_info['name'],
				]));
			}
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

			$message				= $this->load->view('common/mail/templates/' . (strpos($user_info['source'], 'NYAFIND') !== false ? 3 : 2) . '/general', $data, true);

			if (!$this->db->get_where('unsubscribed', [
				'email'		=> $user_info['email'],
				'_deleted'	=> 0
			])->row_array()) {
				self::email(
					$user_info['email'],
					$data['title'],
					$message,
					[],
					[]
				);
			}
		}
	}

	public function bookWithoutOrder($id = 0) {
		if (
			($book_info = $this->book_model->get($id)) &&
			$book_info['status'] == 1 &&
			$user_info = $this->student_model->get($book_info['user_id'])
		) {
			$data['title']		  = vsprintf(_li($book_info['author_name'] . ' your book on our bookstore can earn you author royalty'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']	 	= '';
			$data['content']		= self::formatEmailMessage('book_selling_communication', [
				'url'   => USER_URL . 'bookstore/' . $book_info['slug'],
			]);
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

			$message				= $this->load->view('common/mail/templates/' . (strpos($user_info['source'], 'NYAFIND') !== false ? 3 : 2) . '/general', $data, true);

			if (!$this->db->get_where('unsubscribed', [
				'email'		=> $user_info['email'],
				'_deleted'	=> 0
			])->row_array()) {
				self::email(
					$user_info['email'],
					$data['title'],
					$message,
					[],
					[]
				);
			}
		}
	}

	public function amazonBookPublishAlert($book_id = 0) {
		if (
			($info = $this->book_model->get($book_id)) &&
			($info['status'] == 1) &&
			($user_info = $this->student_model->get($info['user_id']))
		) {
			$site_id = $user_info['site_id'];

			$site_info = $this->site_model->get($site_id);

			$template = 'book_published_amazon';

			$data['title']			= self::formatEmailSubject($template, $site_id, [
				'author_name'	  	=> $info['author_name'],
				'book_name'	  		=> $info['name'],
			]) ?? _li('You made it! Your Book Is Now on Amazon!');

			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage($template, [
				'author_name'		=> $info['author_name'],
				'book_name'	  		=> $info['name'],
				'url'				=> $info['amazon_url'],
			], $site_id);
			$data['site_id']		= $site_id;
			$data['parent_id']		= $site_info['parent_id'];
			$data['site_code']		= $site_info['site_code'];
			$data['link']			= '';
			$data['link_text']		= '';
			$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

			$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

			$mobile = $user_info['mobile'];
			$email = $user_info['email'];

			if (!$this->db->get_where('unsubscribed', [
				'email'		=> $user_info['email'],
				'_deleted'	=> 0
			])->row_array()) {
				self::email(
					$email,
					$data['title'],
					$message,
					[],
					[]
				);
			}
		}
	}

	public function messageAfterBookPublish($data = []) {
		if (
			($book_info = $this->book_model->get($data['book_id'] ?? 0)) &&
			($event_info = $this->event_model->get($data['event_id'] ?? 0)) &&
			($user_info = $this->student_model->get($book_info['user_id'] ?? 0)) &&
			(empty($this->event_book_model->getEventBookByBookId($event_info['id'], $book_info['id'])))
		) {
			$code = '';

			if (empty($code_info = $this->event_user_invite_code_model->get_all([
				'event_id' 	=> $event_info['id'],
				'user_id' 	=> $user_info['id'],
			])['rows'][0] ?? [])) {
				$password 	= uniqid();
				$code 		= sha1(md5(($user_info['username'] ??  ($user_info['slug'] ?? 'bbcode')) . $password . $this->config->item('password_salt') . $event_info['id']));

				$this->event_user_invite_code_model->add([
					'event_id'	=> $event_info['id'],
					'user_id' 	=> $user_info['id'],
					'code'		=> $code,
				]);
			}

			$code = !empty($code) ? $code : ($code_info['code'] ?? '');

			if (empty($title = self::formatEmailSubjectByEvent('yaf_invite_yaf_published', $event_info['id']))) return;

			$enrol_url = sprintf('%s/events/student/bookenroll/%s?uid=%s&code=%s&book_slug=%s&eid=%s',
				SC_USER_ADDRESS_URL,
				$event_info['slug'],
				$user_info['id'],
				$code,
				$book_info['slug'],
				$event_info['id']
			);

			$data['content']			= self::formatEmailMessageByEvent('yaf_invite_yaf_published', [
				'author_name'			=> $book_info['author_name'],
				'book_name'				=> $book_info['name'],
				'url'					=> $enrol_url,
			], $event_info['id']);


			$data['subheading']		= '';
			$data['site_id']		= 1;
			$data['parent_id']		= 1;
			$data['site_code']		= 1;
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

			self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> '653630190370101',
					'parameters'	=> [
						'Exciting Opportunity for You!',
						$book_info['author_name'],
						'Congratulations on publishing your very own book!',
						$book_info['name'],
						'the beginning of an incredible journey',
						'To unlock exciting rewards like *iPads, Samsung Tabs*, and even *national recognition*',
						'*global book festivals in Dubai, London, and more*, you first need to enrol in the World’s Largest Free Summer Camp',
						'Enrol now to qualify for these amazing opportunities!',
						$enrol_url,
						'Let’s make this happen!'
					],
				],
			);

			if (!empty($data['attempt']) && $data['attempt'] < 3) {
				$time = ENVIRONMENT == 'production' ? '+2 days' : '+5 minutes';

				$this->cron_model->editByCode(('messageAfterBookPublish_' . $book_info['id']), [
					'status' 		=> 0,
					'alert_date'	=> date('Y-m-d H:i:s', strtotime($time, strtotime(date('Y-m-d H:i:s')))),
					'data' 			=> [['event_id' => $event_info['id'], 'user_id' => $user_info['id'], 'book_id' => $book_info['id'], 'attempt' => ($data['attempt'] + 1)]]
				]);
			}
		}
	}

	public function bookReviewed($review_id = 0) {
		self::cron($review_id, 'bookReviewedCron');
	}

	public function bookReviewedCron($review_id = 0) {
		$this->load->model('book/Review_model', 'review_model');

		if (
			!empty($review_info = $this->review_model->get($review_id)) &&
			$review_info['status'] == 1 &&
			!empty($book_info = $this->book_model->get($review_info['book_id'])) &&
			!empty($user_info = $this->user_model->get($book_info['user_id']))
		) {

			$data['title']			= sprintf(_li('New Review by %s for %s!'), $review_info['author'], $book_info['name']);
			$data['heading']		= $data['title'];

			$data['content']		= $this->load->view('common/mail/part/review_book', [
				'book'			=> [
					'name'			=> $book_info['name'],
					'author_name'	=> $book_info['author_name'],
					'url'			=> USER_URL . 'bookstore/' . $book_info['slug'],
				],
				'review'			=> [
					'author_name'	=> $review_info['author'],
					'date'			=> formatDate($review_info['date_added'], $user_info['timezone']),
					'text'			=> $review_info['text'],
				],
			], true);

			$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			$mobile = $user_info['mobile'];
			$email 	= $user_info['email'];

			!empty($user_info['email']) && self::email(
				$email,
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	private function _createEventPublishBookCommunication($book_id = 0) {
		$result = false;

		if (
			empty($book_info = $this->book_model->get($book_id))
		) return $result;

		if (empty($event_book_info = $this->event_book_model->get_all([
			'book_id' => $book_id
		])['rows'][0] ?? [])) return $result;

		if (
			!empty($event_info = $this->event_model->get($event_book_info['event_id'])) &&
			!empty($event_info['book_writing_end_date']) &&
			$event_info['book_writing_end_date'] >= date('Y-m-d H:i:s')
		) {
			$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');

			$communication_kits = json_decode($this->event_communication_kit_model->get_all([
				'event_id' => $event_info['id']
			])['rows'][0]['book'] ?? '', true);

			if (empty($communication_kits)) return;

			foreach ($communication_kits as $index => $kit_info) {
				$code = sprintf('eventPublishBookCron_%s_%s', $book_id, $kit_info['alert_duration']);

				if (!empty($kit_info['book_version']) && ($book_info['version'] != $kit_info['book_version'])) continue;

				if (empty($kit_info['repeat']) && !empty($this->cron_model->getByCode($code))) continue;

				if (!empty($kit_info) && !empty($kit_info['alert_duration'])) {
					$alert_duration  = sprintf('%s %s', $kit_info['alert_duration'], $kit_info['duration_type']);

					$this->cron_model->add([
						'code'			=> sprintf('eventPublishBookCron_%s_%s' , $book_id, $kit_info['alert_duration']),
						'action'		=> 'alert_model->eventPublishBookCron',
						'data'			=> [$book_id, $event_info['id'], ($kit_info['alert_duration'] ?? 0), $index],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime("+{$alert_duration}", strtotime(date('Y-m-d H:i:s')))),
					]);

					$result = true;
				}
			}
		}

		return $result;
	}

	public function eventPublishBookCron($book_id = 0, $event_id = 0, $alert_duration = 0, $index = 0) {
		if (empty($book_info = $this->book_model->get($book_id))) return;
		if (empty($user_info = $this->user_model->get($book_info['user_id']))) return;

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('order/Coupon_model', 'coupon_model');

		$communication_kits = $this->event_communication_kit_model->get_all([
			'event_id' => $event_id
		])['rows'][0]['book'] ?? '';

		if (empty($communication_kits)) return;

		$communication_kits = json_decode($communication_kits, true);
		$kit_info 			= $communication_kits[$index] ?? [];

		if (empty($kit_info)) return;

		if (strpos(strtolower($kit_info['alert_condition']), 'coupon') !== false) {
			if (empty($coupon_info = $this->coupon_model->get_all([
				'event_id' 		=> $event_id,
				'item_id' 		=> $book_info['id'],
				'user_id' 		=> $book_info['user_id'],
			])['rows'][0] ?? [])) return;

			if ($kit_info['alert_condition'] === 'coupon_not_used' && !empty($coupon_info['used_count'])) {
				return;
			} elseif ($kit_info['alert_condition'] === 'coupon_used' && empty($coupon_info['used_count'])) {
				return;
			}
		}

		$book_url 	= vsprintf('%sbookstore/%s', [
			USER_URL,
			$book_info['slug'],
		]);

		$data 		= [
			'book_name'	  			=> $book_info['name'],
			'author_name'	  		=> $book_info['author_name'] ?? '',
			'book_slug'	  			=> $book_info['slug'],
			'url'	  				=> $book_url,
			'coupon_percent'	  	=> $kit_info['coupon_percent'],
			'duration'	  			=> $kit_info['coupon_duration'] ?? 24,
			'coupon'	  			=> $coupon_info['code'],
			'date'	  				=> date('M j, Y'),
			'duration_time'	  		=> $coupon_info['date_end'] ?? '',
			'event_id'	  			=> $event_id,
			'book_id'	  			=> $book_id,
			'user_id'	  			=> $user_info['id'],
		];
		log_kb(['eventPublishBookCron::log' => [
			$kit_info,
			$book_id,
			$event_id,
			$alert_duration,
			$index,
			$data,
		]]);

		$subject 	= format_message_with_data($kit_info['email']['subject'], $data);
		$message 	= format_message_with_data($kit_info['email']['message'], $data);

		$email  	= $user_info['email'];
		$mobile  	= $user_info['mobile'];
		$attachments= [];

		if (!empty($kit_info['email']['attachment'])) {
			$this->load->model('event/EventBrochure_model', 'event_brochure_model');

			$brochure_info 	= $this->event_brochure_model->get_all([
				'event_id' => $event_id,
			])['rows'][0] ?? [];
			$attachments	= self::_getCommunicationKitPDF($data, $kit_info['email']['attachment'], $brochure_info);
		}

		self::email(
			$email,
			$subject,
			$message,
			[],
			ENVIRONMENT === 'production'
				? ['communication@bribooks.com']
				: [],
			$attachments,
		);

		if (!empty($kit_info['whatsapp']['template'])) {
			$type 			= ucwords($kit_info['whatsapp']['type'] ?? 'text');
			$whatsapp_data 	= [
				'template'		=> $kit_info['whatsapp']['template'],
				'parameters'	=> self::_formatMarketingWhatsappMessage($kit_info['whatsapp']['message'], $data),
			];

			if ($kit_info['whatsapp']['gateway'] == 'onextel') {
				$whatsapp_data['template_id'] = $kit_info['whatsapp']['template'];

				if (in_array($kit_info['whatsapp']['type'], ['cta'])) {
					$whatsapp_data['buttons'][] = [
						'type'    => 'URL',
						'text'    => $book_info['slug'],
						'payload' => $book_info['slug']
					];
				}

				if (!empty($kit_info['whatsapp']['attachment']) &&
					!empty($attachments[0]) &&
					in_array($kit_info['whatsapp']['type'], ['document', 'image', 'video'])
				) {
					$onextel_attachment_type = [
						'document' 	=> 'DOC',
						'image'		=> 'IMAGE',
						'video'		=> 'VIDEO'
					];

					$whatsapp_data['media'] = [
						'type'  	=> $onextel_attachment_type[$kit_info['whatsapp']['type']],
						'url'		=> base_url(str_replace('/var/www/html', '', $attachments[0])),
						'fileName'	=> basename($attachments[0]),
					];
				}

				self::sendOnextelWhatsappMessage(
					$mobile,
					$whatsapp_data
				);

			} else {
				if (in_array($kit_info['whatsapp']['type'], ['cta'])) {
					$whatsapp_data['url_parameters'] = $book_info['slug'];
					$type = 'Text';
				}

				if (!empty($kit_info['whatsapp']['attachment']) &&
					!empty($attachments[0]) &&
					in_array($kit_info['whatsapp']['type'], ['document', 'image', 'video'])
				) {
					$whatsapp_data['document'] = [
						'name'	=> basename($attachments[0]),
						'link'	=> base_url(str_replace('/var/www/html', '', $attachments[0])),
					];
				}

				if (empty($type)) {
					$type = 'Text';
				}

				self::{'_sendWhatsapp' . $type}(
					$mobile,
					$whatsapp_data
				);
			}
		}
	}

	private function _resetBookCaches($book_info = []) {
		$url = vsprintf('%sapi/revalidate/?path1=bookstore&path2=%s&secret=bbsdgfhsdgj57635464ghfhgfh', [
			ENV_API ? USER_URL : str_replace('.uat.', '.dev.', USER_URL),
			$book_info['slug']
		]);

		$result = _curl($url, [], 'GET', [], '');

		log_kb(['_resetBookCaches' => $result, $url]);
	}
}
