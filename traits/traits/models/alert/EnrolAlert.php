<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EnrolAlert {
	public function enrol($lead_id = 0, $amount = 0, $emi_type = NULL) {
		$code = $this->lead_model->generatePaymentLink($lead_id, $amount, $emi_type);

		self::cron($code, 'enrolCron');
	}

	public function enrolCron($code = NULL) {
		$lead_info = $this->lead_model->getByCode($code);

		$data['title']			= _li('Enrol for ') . ' ' . $lead_info['course'];
		$data['heading']		= _li('Dear') . ' ' . $lead_info['parent_name'];
		$data['subheading']		= _li('Dear') . ' ' . $lead_info['parent_name'];
		$data['content']		= vsprintf(_li('Congratulations on starting %s’s online coding journey. Please pay %s for %s class to enrol by clicking on the payment link. Please ignore if already paid.'), [
			$lead_info['name'],
			currency($lead_info['amount'], 0, $lead_info['currency_code']),
			$lead_info['course']
		]);
		$data['link']			= site_url('/home/enrolment/' . $code);
		$data['link_text']		= _l('Pay Online');
		$data['term']			= true;

		$message = $this->load->view('common/mail/general', $data, true);

		self::sms($lead_info['mobile'], self::formatMessage('sms_enrol', [
			'student_name'		=> $lead_info['name'],
			'parent_name'		=> $lead_info['parent_name'],
			'course_name'		=> $lead_info['course'],
			'amount'			=> currency($lead_info['amount'], 0, $lead_info['currency_code']),
			'payment_link'		=> $data['link'],
		]));

		$bcc = [];

		$lead_info['email'] && self::email(
			$lead_info['email'],
			$data['title'],
			$message,
			[],
			$bcc
		);
	}

	public function enrolled($enrol_id = 0) {
		self::cron($enrol_id, 'enrolledCron');
	}

	public function enrolledCron($enrol_id = 0) {
		$enrol_info 			= $this->enrol_model->get($enrol_id);

		$enrol_info['parent_name'] = empty($enrol_info['parent_name']) ? $enrol_info['user'] : $enrol_info['parent_name'];

		self::sms($enrol_info['mobile'], self::formatMessage('sms_enrolled', [
			'student_name'		=> $enrol_info['user'],
			'parent_name'		=> $enrol_info['parent_name'],
			'course_name'		=> $enrol_info['course'],
			'amount'			=> $amount,
		]));

		// Email Alert Parent/Student
		$data['title']			= _li('Welcome to ICode Global Hackathon ' . date('Y') . ': Registration Confirmation');
		$data['heading']		= '';
		$data['subheading']		= '';
		$data['content']		= $this->load->view('common/mail/part/enrolled', [
			'student_name'		=> $enrol_info['user'],
			'emi_type'			=> _l($enrol_info['emi_type']),
			'course_name'		=> $enrol_info['course'],
		], true);
		$data['link']			= site_url();
		$data['link_text']		= _l('login_to_portal');

		$message 				= $this->load->view('common/mail/general', $data, true);

		$attachment 			= ''; //FCPATH . '/assets/pdf/' . ($this->config->item('site_country_code') === 'IN' ? 'ICode_India Brochure' : 'ICode_Global Brochure') .  '.pdf';

		$bcc = [];

		$enrol_info['email'] && self::email(
			$enrol_info['email'],
			$data['title'],
			$message,
			[],
			$bcc,
			$attachment
		);

		// Admin alerts
		$data['title']			= $enrol_info['site'] . ' | ' . _l('course_enrolled') . ' ' . $enrol_info['course'];
		$data['heading']		= $enrol_info['user'] . _li('enrolled for course') . ' ' . $enrol_info['course'];
		$data['subheading']		= vsprintf(_li('%s amount received'), [$amount]);
		$data['content']		= vsprintf(_li('%s enrolled for course %s on %s'), [$enrol_info['user'], $enrol_info['course'], date('M j, Y h:i A')]);
		$data['link']			= site_url('login');
		$data['link_text']		= _l('check_now');

		$message 				= $this->load->view('common/mail/general', $data, true);

		$bcc = self::additionalEmails($enrol_info['site_id']);

		self::email(
			get_site($lead_info['site_id'], 'owner_email'),
			$data['title'],
			$message,
			[],
			$bcc
		);

		// foreach ($this->admin_mobiles as $mobile) {
		// 	self::sms($mobile, vsprintf(_li('new demo request from %s on %s for the course %s follow the link to check details %s'), [$lead_info['name'], $data['schedule'], $lead_info['course'], $data['link']]));
		// }
	}

	public function renew($enrol_id = 0, $amount = 0) {
		$code = $this->enrol_model->generatePaymentLink($enrol_id, $amount);

		self::cron($code, 'renewCron');
	}

	public function renewCron($code = NULL) {
		$enrol_info = $this->enrol_model->getByCode($code);
		$amount 	= currency(round($enrol_info['amount']), 0, $enrol_info['currency_code']);
		$enrol_info = $this->enrol_model->get($enrol_info['id']);


		// Global configuration
		$this->site_model->initConfig($enrol_info['site_id'] ?? 0);

		$enrol_info['parent_name'] = empty($enrol_info['parent_name']) ? $enrol_info['user'] : $enrol_info['parent_name'];

		$data['title']			= _l('course_renew') . ' ' . $enrol_info['course'];
		$data['heading']		= _li('Dear') . ' ' . $enrol_info['parent_name'];
		$data['subheading']		= '';
		$data['content']		= vsprintf(_li('%s has completed %s of Teaching Learning in %s. We are super excited to see him excel in the course work and would love to see him continue his learning process. <br><br>Kindly renew the Course Enrolment by paying %s by Clicking the Payment Link. <br><br>Please ignore if already paid. '), [
			$enrol_info['user'],
			5,
			$enrol_info['course'],
			$amount
		]);
		$data['link']			= site_url('/home/renewal/' . $code);
		$data['link_text']		= _l('pay_online');
		$data['term']			= true;

		// Generate payment link here
		self::sms($enrol_info['mobile'], self::formatMessage('sms_renew', [
			'student_name'		=> $enrol_info['user'],
			'parent_name'		=> $enrol_info['parent_name'],
			'course_name'		=> $enrol_info['course'],
			'amount'			=> $amount,
			'payment_link'		=> $data['link'],
		]));

		$message = $this->load->view('common/mail/general', $data, true);

		$bcc = [];

		$enrol_info['email'] && self::email(
			$enrol_info['email'],
			$data['title'],
			$message,
			[],
			$bcc
		);
	}

	public function renewalAlert() {
		foreach ($this->enrol_model->pendingPayments() as $enrol) {
			self::renew($enrol['id']);
		}

		foreach ($this->enrol_model->expired() as $enrol) {
			$this->enrol_model->edit($enrol['id'], [
				'status'	=> 0,
			]);
		}
	}

	public function eventForceEnrolBookCron($event_id = 0, $book_id = 0) {
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('event/EventTemplate_model', 'event_template_model');

		if (empty($book_info = $this->book_model->get($book_id) ?? [])) return;

		if (empty($user_info = $this->user_model->get($book_info['user_id']) ?? [])) return;

		if (empty($event_book_info = $this->event_book_model->get_all([
			'book_id'		=> $book_info['id']
		])['rows'][0] ?? '')) return;

		if (empty($first_date_published = $this->book_version_model->get_all([
			'version' => 1,
			'book_id' => $book_info['id'],
		])['rows'][0])) return;

		if (empty($template_info = $this->event_template_model->getByTemplateId($event_id, 'force_enrol_book'))) return;

		$data['title']		  	= self::formatEventEmailSubject('force_enrol_book', $event_id, [
			'book_name' => $book_info['name']
		]);
		$data['heading']		= '';
		$data['subheading']	 	= '';
		$data['subheading']		= '';
		$data['content']		= self::formatEventEmailMessage('force_enrol_book', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'datetime'	  		=> date('Y-m-d', strtotime($first_date_published['date_added'])),
			'remaining_time'	=> date('Y-m-d', strtotime($event_book_info['date_modified'] . ' +15 days'))
		], $event_id);
		$data['link']		   	= '';
		$data['link_text']	  	= '';
		$message				= $this->load->view('common/mail/templates/site/general', $data, true);

		if (empty($data['title']) || empty($data['content'])) return;

		$mobile = ENVIRONMENT == 'production' ? $user_info['mobile'] : '919935343128';

		$user_info['email'] && self::email(
			$user_info['email'],
			$data['title'],
			$message,
			[],
			(ENVIRONMENT == 'production') ? ['communication@bribooks.com'] : []
		);

		if (!empty($user_info['mobile']) && !empty($template_info['whatsapp_template_id']) && !empty($template_info['whatsapp_message'])) {
			self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> $template_info['whatsapp_template_id'],
					'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], [
						'author_name'		=> $book_info['author_name'],
						'book_name'			=> $book_info['name'],
						'datetime'	  		=> date('M j, Y', strtotime($first_date_published['date_added'])),
						'remaining_time'	=> date('M j, Y', strtotime($event_book_info['date_modified'] . ' +15 days'))
					]),
				]
			);
		}
	}
}
