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

		$email_attachment			= !empty($template_info['attachment']) ? json_decode($template_info['attachment'], true) : [];

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
			'date'					=> date('Y-m-d')
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
		$attachment 			= !empty($email_attachment['attachment'])
			? self::_generateEmailAttachmentPDF($email_attachment['attachment'] ?? '', $email_attachment['attachment_name'] ?? '', $variables)
			: [];

		!empty($email) && self::email(
			$email,
			$title,
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			$attachment
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

			$email_attachment			= !empty($template_info['attachment']) ? json_decode($template_info['attachment'], true) : [];
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
				'date'					=> date('Y-m-d')
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
			$attachment 			= !empty($email_attachment['attachment'])
				? self::_generateEmailAttachmentPDF($email_attachment['attachment'] ?? '', $email_attachment['attachment_name'] ?? '', $variables)
				: [];

			!empty($email) && self::email(
				$email,
				$title,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				$attachment
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
