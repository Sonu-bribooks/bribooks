<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CertififcateMessageAlert {
	public function genericCertificateCreatedCron($certificate_id = 0, $sold = 0, $medallion_order_code = null) {
		if (empty($certificate_id)) return ;

		$this->load->model('medallion/Medallion_model', 'medallion_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('certificate/CertificateMessageTemplate_model', 'certificate_message_template_model');

		if (empty($certificate_info = $this->certificate_model->get($certificate_id))) return;
		if (empty($certificate_template_info = $this->certificate_template_model->get($certificate_info['certificate_template_id']))) return;
		if (empty($template_info = $this->certificate_message_template_model->get($certificate_template_info['certificate_message_template_id']))) return;

		$book_info = $this->book_model->get($certificate_info['book_id']);

		if (empty($book_info) || empty($author_info = $this->student_model->get($book_info['user_id']))) return;

		if (!empty($certificate_template_info['medallion_id'])) {
			$medallion_info = $this->medallion_model->get($certificate_template_info['medallion_id']);
		}

		$medallion_url = $medallion_order_code
			? vsprintf(USER_URL . 'medallionconfirmation?uid=%s&code=%s&oid=%s', [
				$author_info['id'],
				$author_info['verification_code'],
				$medallion_order_code,
			])
			: ''
		;

		$site_info 	= $this->site_model->get($author_info['site_id'] ?? 0);
		$state_info = $this->state_model->get($author_info['state_id'] ?? 0);
		$city_info 	= $this->city_model->get($author_info['city_id'] ?? 0);

		$mobile = $author_info['mobile'];
		$email  = $author_info['email'];

		$event_info 	= $this->event_model->get($template_info['event_id']);

		$league_url = '';

		if (!empty($certificate_template_info['achievement']) && !empty($certificate_template_info['challenge_id']) && !empty($certificate_template_info['challenge_type'])) {
			$challenge_model = sprintf('event_challenge_%s_model', strtolower($certificate_template_info['challenge_type']));

			$this->load->model(sprintf('event/EventChallenge%s_model', ucwords($certificate_template_info['challenge_type'])), $challenge_model);
			$challenge_info = $this->{$challenge_model}->get($certificate_template_info['challenge_id']);

			if (!empty($challenge_info)) {
				$league_url = sprintf('%s/%s/%s/%d/?trid=%d&bid=%d',
					$event_info['rank_url'],
					strtolower($certificate_template_info['challenge_type']),
					$challenge_info['slug'],
					(strtolower($certificate_template_info['challenge_type']) == 'school') ? $author_info['site_id'] : ($author_info[sprintf('%s_id', strtolower($certificate_template_info['challenge_type']))] ?? 0),
					(int)$book_info['user_id'] ?? 0,
					(int)$book_info['id'] ?? 0,

				);
			}
		}

		$variables = [
			'author_name'			=> $book_info['author_name'] ?? '',
			'author_first_name'		=> $book_info['author_name'] ?? '',
			'book_name'				=> $book_info['name'] ?? '',
			'book_isbn'				=> $book_info['isbn'] ?? '',
			'book_url'				=> sprintf('%sbookstore/%s', USER_URL, $book_info['slug']),
			'book_sold_count'		=> $sold,
			'copies_sold'			=> $sold,
			'certificate_url'		=> USER_URL . 'account/mycertificates' ,
			'medallion_name'		=> $medallion_info['name'] ?? '',
			'medallion_url'			=> $medallion_url,
			'school_name'			=> $site_info['name'] ?? '',
			'state'					=> $state_info['name'] ?? '',
			'city'					=> $city_info['name'] ?? '',
			'league_url'			=> $league_url,
			'awards_url'			=> ($event_info['url'] ?? 'https://www.yaf.bribooks.com/india/2024/') . 'student/awards' ,

			'msgheader'			    => 'The Published Author Certificate Awaits You!',
			'publishcert'		    => 'Publish your book soon to earn the prestigious Published Author Certificate and stand a chance to win more exciting rewards, including a fully paid trip to AFCC Singapore!',
			'rewards_await'         => 'Exciting rewards await you',
			'pinnacle_award'        => 'Pinnacle Awards: Win a fully paid trip to AFCC Singapore, launch your book & win an iPad!',
			'bestseller_award'      => 'Jury Choice & Best Seller Awards: Samsung Tabs, NDTV interviews, and features in top outlets like Business World and Times of India.',
			'discountedrate'        => 'Get your first printed copy at a discounted rate here to qualify',
			'special_gift'          => 'But wait, there\'s more! We have a special gift for you, and we\'ll be sending another email shortly',
			'exicting_opp'          => 'You’ve unlocked exciting opportunities!',
			'book_prom'             => 'Need help promoting your book? Watch this masterclass by Ami Dror',
			'champ'                 => 'You are a Champion!',
			'exicting_news'         => '🏅 Stay tuned for another email from us - we\'ve got some exciting news coming your way! 🎁',
			'another_email'         => 'Stay tuned for another email from us. We have a SURPRISE for you!',
			'surprise'              => 'Stay tuned! We have a BIG SURPRISE for you!',
			'time'                  => 'Time to CELEBRATE!',

			'cong_pub' 				=> 'Congrats on Your \'Published Author Certificate\'!',
			'earned' 				=> 'Exciting news! You\'ve earned the \'Published Author Certificate\' for selling the first',
			'touch' 				=> 'But wait, there\'s more! We have a special gift for you, and we\'ll be in touch soon.',
			'famous' 				=> 'Become Famous as an Emerging Author',
			'away' 					=> 'away from earning the prestigious Emerging Author Certificate & Silver Star Medallion, pocketing up to 25% in author royalties.',
			'share' 				=> 'Share your',
			'sure' 					=> 'Not sure how to promote',
			'tuned' 				=> 'Stay tuned for more exciting news from us.',
			'rock' 					=> 'Congratulations again! You\'re a ROCKSTAR AUTHOR!',
			'push' 					=> 'Pushing! You Can Be a Gold Star',
			'earn' 					=> 'away from earning the prestigious Gold Star Young Author Certificate & Gold Star Medallion, pocketing up to 25% in author royalties.',
			'buy' 					=> 'read and buy your amazing book.',
			'status' 				=> 'Hurray! You\'ve achieved the status of a Gold Star Young',
			'once' 					=> 'Congratulations once again!',
			'close' 				=> 'So Close to Becoming an Entrepreneur',
			'esteemed' 				=> 'away from earning the esteemed Entrepreneur Author Certificate & Platinum Star Medallion, pocketing up to 25% in author royalties.',
			'get' 					=> 'Get Ready, ROCKSTAR!',
			'have' 					=> 'We have a SURPRISE for you!',
			'keep' 					=> 'Keep SHINING!',
			'know' 					=> 'Did you know that an ISBN serves as a worldwide declaration that you are the book\'s ',
			'cost' 					=> 'cost more than $100 USD, but guess what?',
			'gift' 					=> 'as a gift, completely free of charge.',
			'prest' 				=> 'But that\'s not all, you will also earn the prestigious',
			'alloted' 				=> 'allotted the ISBN Number by the Ministry of Education, Government of India/National Library - UAE.',
			'excit' 				=> 'tuned for another email from us. We have EXCITING NEWS for you!',
			'place' 				=> 'from securing your place in the Amazon global',
			'globally' 				=> 'that\'s not all, you will also earn the prestigious Globally Published',
			'pocket' 				=> 'shine as an international author. Pocket up to 25% in author royalties.',
			'amazon' 				=> 'You\'re a Champion Author on Amazon!',
			'among' 				=> 'you\'ve secured your place among the top authors featured on Amazon.com.',
			'recog' 				=> 'achieved the highest recognition as a young published author!',

			'won'                     =>'Congratulations! You\'ve Won the \'Published Author Certificate\'',
			'news'                    =>'We\'re pleased to share the exciting news that you\'ve won the ‘Published Author Certificate’ with the first printed copy of',
			'wait'                    =>'But wait, there\'s more! We have a special gift for you, and you\'ll receive another notification about it shortly.',
			'rock_already'            =>'You are already a rock star!',
			'you'                     =>'Special Surprise for You!',
			'unlocked'                =>'unlocked exciting opportunities!',
			'preg_em'                 =>' earning the prestigious ‘Emerging Young Author’ certificate!',
			'your_book'               =>'promoting your book? Watch this masterclass',
			'good'                    =>'Good luck—you\'re a champion!',
			'celeb'                   =>'Time to CELEBRATE!',
		];

		$title 					= self::formatCertificateMessage(trim($template_info['subject']), $variables);
		$content_body 			= self::formatCertificateMessage(trim($template_info['body']), $variables);

		$data['site_id']		= $author_info['site_id'];
		$data['parent_id']		= '';
		$data['site_code']		= '';
		$data['title']		  	= $title;
		$data['heading']		= '';
		$data['subheading']	 	= '';
		$data['subheading']	 	= '';
		$data['content']		= $content_body;
		$data['link']		   	= '';
		$data['link_text']	  	= '';

		$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

		!empty($email) && self::email(
			$email,
			$title,
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[]
		);

		if (!empty($template_info['whatsapp_template_id'])) {
			if ($template_info['whatsapp_gateway'] == 'onextel') {
				self::sendOnextelWhatsappMessage(
					$mobile,
					[
						'template_id'	=> $template_info['whatsapp_template_id'],
						'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], $variables),
					]
				);
			} else {
				!empty($mobile) && self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> $template_info['whatsapp_template_id'],
						'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], $variables),
					]
				);
			}
		}
	}

	public function genericCertificateFomoCron($template_id = 0, $data = []) {
		if (empty($template_id) && empty($data)) return ;

		$this->load->model('certificate/CertificateMessageTemplate_model', 'certificate_message_template_model');

		$template_info = $this->certificate_message_template_model->get($template_id);

		if (!empty($template_info)) {
			$book_info = $this->book_model->get($data['book_id']);

			if (empty($book_info) || empty($author_info = $this->student_model->get($book_info['user_id']))) return;

			$site_info 	= $this->site_model->get($author_info['site_id']);
			$state_info = $this->state_model->get($author_info['state_id']);
			$city_info 	= $this->city_model->get($author_info['city_id']);

			$mobile = $author_info['mobile'];
			$email  = $author_info['email'];

			$event_info 	= $this->event_model->get($template_info['event_id']);

			$league_url = '';

			if (!empty($template_info['challenge_id']) && !empty($template_info['challenge_type'])) {
				$challenge_model = sprintf('event_challenge_%s_model', strtolower($template_info['challenge_type']));

				$this->load->model(sprintf('event/EventChallenge%s_model', ucwords($template_info['challenge_type'])), $challenge_model);
				$challenge_info = $this->{$challenge_model}->get($template_info['challenge_id']);

				if (!empty($challenge_info)) {
					$league_url = sprintf('%s/%s/%s/%d/?trid=%d&bid=%d',
						$event_info['rank_url'],
						strtolower($template_info['challenge_type']),
						$challenge_info['slug'],
						(strtolower($template_info['challenge_type']) == 'school') ? $author_info['site_id'] : ($author_info[sprintf('%s_id', strtolower($template_info['challenge_type']))] ?? 0),
						(int)$book_info['user_id'] ?? 0,
						(int)$book_info['id'] ?? 0,

					);
				}
			}

			$variables = [
				'author_name'			=> $book_info['author_name'] ?? '',
				'author_first_name'		=> $book_info['author_name'] ?? '',
				'book_name'				=> $book_info['name'] ?? '',
				'book_isbn'				=> $book_info['isbn'] ?? '',
				'book_url'				=> sprintf('%sbookstore/%s', USER_URL, $book_info['slug']),
				'book_sold_count'		=> !empty($data['sold']) ? abs($template_info['max_sold'] - $data['sold']) : '',
				'copies_sold'			=> $data['sold'] ?? '',
				'certificate_url'		=> USER_URL . 'account/mycertificates' ,
				'school_name'			=> $site_info['name'] ?? '',
				'state'					=> $state_info['name'] ?? '',
				'city'					=> $city_info['name'] ?? '',
				'league_url'			=> $league_url,

				'msgheader'			    => 'The Published Author Certificate Awaits You!',
				'publishcert'		    => 'Publish your book soon to earn the prestigious Published Author Certificate and stand a chance to win more exciting rewards, including a fully paid trip to AFCC Singapore!',
				'rewards_await'         => 'Exciting rewards await you',
				'pinnacle_award'        => 'Pinnacle Awards: Win a fully paid trip to AFCC Singapore, launch your book & win an iPad!',
				'bestseller_award'      => 'Jury Choice & Best Seller Awards: Samsung Tabs, NDTV interviews, and features in top outlets like Business World and Times of India.',
				'discountedrate'        => 'Get your first printed copy at a discounted rate here to qualify',
				'special_gift'          => 'But wait, there\'s more! We have a special gift for you, and we\'ll be sending another email shortly',
				'exicting_opp'          => 'You’ve unlocked exciting opportunities!',
				'book_prom'             => 'Need help promoting your book? Watch this masterclass by Ami Dror',
				'champ'                 => 'You are a Champion!',
				'exicting_news'         => '🏅 Stay tuned for another email from us - we\'ve got some exciting news coming your way! 🎁',
				'another_email'         => 'Stay tuned for another email from us. We have a SURPRISE for you!',
				'surprise'              => 'Stay tuned! We have a BIG SURPRISE for you!',
				'time'                  => 'Time to CELEBRATE!',

				'cong_pub' 				=> 'Congrats on Your \'Published Author Certificate\'!',
				'earned' 				=> 'Exciting news! You\'ve earned the \'Published Author Certificate\' for selling the first',
				'touch' 				=> 'But wait, there\'s more! We have a special gift for you, and we\'ll be in touch soon.',
				'famous' 				=> 'Become Famous as an Emerging Author',
				'away' 					=> 'away from earning the prestigious Emerging Author Certificate & Silver Star Medallion, pocketing up to 25% in author royalties.',
				'share' 				=> 'Share',
				'sure' 					=> 'Not sure how to promote',
				'tuned' 				=> 'Stay tuned for more exciting news from us.',
				'rock' 					=> 'Congratulations again! You\'re a ROCKSTAR AUTHOR!',
				'push' 					=> 'Pushing! You Can Be a Gold Star',
				'earn' 					=> 'away from earning the prestigious Gold Star Young Author Certificate & Gold Star Medallion, pocketing up to 25% in author royalties.',
				'buy' 					=> 'read and buy your amazing book.',
				'status' 				=> 'Hurray! You\'ve achieved the status of a Gold Star Young',
				'once' 					=> 'Congratulations once again!',
				'close' 				=> 'So Close to Becoming an Entrepreneur',
				'esteemed' 				=> 'away from earning the esteemed Entrepreneur Author Certificate & Platinum Star Medallion, pocketing up to 25% in author royalties.',
				'get' 					=> 'Get Ready, ROCKSTAR!',
				'have' 					=> '"We have a SURPRISE for you! 🎁 Congratulations once again! 🎉',
				'keep' 					=> 'Keep SHINING!',
				'know' 					=> 'Did you know that an ISBN serves as a worldwide declaration that you are the book\'s ',
				'cost' 					=> 'cost more than $100 USD, but guess what?',
				'gift' 					=> 'as a gift, completely free of charge.',
				'prest' 				=> 'But that\'s not all, you will also earn the prestigious',
				'alloted' 				=> 'allotted the ISBN Number by the Ministry of Education, Government of India/National Library - UAE.',
				'excit' 				=> 'tuned for another email from us. We have EXCITING NEWS for you!',
				'place' 				=> 'from securing your place in the Amazon global',
				'globally' 				=> 'that\'s not all, you will also earn the prestigious Globally Published',
				'pocket' 				=> 'shine as an international author. Pocket up to 25% in author royalties.',
				'amazon' 				=> 'You\'re a Champion Author on Amazon!',
				'among' 				=> 'you\'ve secured your place among the top authors featured on Amazon.com.',
				'recog' 				=> 'achieved the highest recognition as a young published author!',

				'won'                   =>'Congratulations! You\'ve Won the \'Published Author Certificate\'',
				'news'                  =>'We\'re pleased to share the exciting news that you\'ve won the ‘Published Author Certificate’ with the first printed copy of',
				'wait'                  =>'But wait, there\'s more! We have a special gift for you, and you\'ll receive another notification about it shortly.',
				'rock_already'          =>'You are already a rock star!',
				'you'                   =>'Special Surprise for You!',
				'unlocked'              =>'unlocked exciting opportunities!',
				'preg_em'               =>' earning the prestigious ‘Emerging Young Author’ certificate!',
				'your_book'             =>'promoting your book? Watch this masterclass',
				'good'                  =>'Good luck—you\'re a champion!',
				'celeb'                 =>'Time to CELEBRATE!',
			];

			$title 					= self::formatCertificateMessage(trim($template_info['subject']), $variables);
			$content_body 			= self::formatCertificateMessage(trim($template_info['body']), $variables);

			$data['site_id']		= $author_info['site_id'];
			$data['parent_id']		= '';
			$data['site_code']		= '';
			$data['title']		  	= $title;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']	 	= '';
			$data['content']		= $content_body;
			$data['link']		   	= '';
			$data['link_text']	  	= '';

			$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

			!empty($email) && self::email(
				$email,
				$title,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				[]
			);

			if (!empty($template_info['whatsapp_template_id'])) {
				if ($template_info['whatsapp_gateway'] == 'onextel') {
					self::sendOnextelWhatsappMessage(
						$mobile,
						[
							'template_id'	=> $template_info['whatsapp_template_id'],
							'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], $variables),
						]
					);
				} else {
					!empty($mobile) && self::_sendWhatsappText(
						$mobile,
						[
							'template'		=> $template_info['whatsapp_template_id'],
							'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], $variables),
						]
					);
				}
			}
		}
	}

	private function _formatWhatsappMessage($message, $data = []) {
		preg_match_all('/\{(.+?)\}/ims', $message, $output);
		$message_data = [];

		foreach ($output[1] ?? [] as $key) {
			$value = isset($data[$key]) ? $data[$key] : $key;

			$message_data[] = $value;
		}

		return $message_data;
	}
}
