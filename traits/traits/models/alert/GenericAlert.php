<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GenericAlert {
	public function invoiceGenerateCron($currency_id = 0) {
		$this->load->model('address/Address_model', 'address_model');

		$results = $this->order_model->searchProductName([
			'ne_status'		=> [0, 91, 92],
			'currency_id'	=> 47,
		])['rows'] ?? [];

		log_kb(['invoiceGenerateCron:: ' => count($results)]);

		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$s3_dirname = vsprintf('%s', [
			(ENVIRONMENT === 'production' ? '' : 'test_invoicepdfs') . 'invoicepdfs',
		]);

		foreach ($results as $key => $item) {
			$this->s3_lib->setBucket('bbvideolessons');
			$pdf_data  = self::_orderInvoice($item['id'], true);

			$filename = vsprintf('invoice_%s.pdf', [
				$item['id'],
			]);

			$this->zip->add_data($filename, $pdf_data);

			log_kb(['invoiceGenerateCron::file:: ' => $filename]);

			if (ENVIRONMENT !== 'production' && $key > 2) break;
		}

		$this->s3_lib->setBucket('bbvideolessons');
		$this->s3_lib->putData(
			sprintf('invoice_%s.zip', date('Y_m_d_H_i_s')),
			$s3_dirname,
			$this->zip->get_zip()
		);
	}

	public function franchiseAuthorAlert($id) {
		self::cron($id, 'franchiseAuthorAlertCron');
	}

	public function franchiseAuthorAlertCron($id) {
		$students = $this->student_model->get_all([
			'site_id'	=> (int)$id,
		])['rows'];

		log_kb(['Sending::franchiseAuthorAlertCron:: ' => $students]);

		$site_info = $this->site_model->get($id);

		foreach ($students as $student) {
			// self::_franchiseAuthorAlert($student, $site_info);
			// self::_updateBankAlertCron($student, $site_info);
			self::_sendInvitationAlert($student, $site_info);
			// self::_sendRoyaltyAlert($student, $site_info);
		}
	}

	private function _franchiseAuthorAlert($info = [], $site_info = []) {
		if (
			($books = $this->book_model->get_all([
				'user_id'	=> $info['id'],
				'ne_status'	=> 0,
			])['rows'])
		) {
			foreach ($books as $book) {
				if (empty($this->order_model->getAuthorProducts(['product_id' => $book['id']]))) {
					$book_info = $book;
					break;
				}
			}

			if (empty($book_info)) {
				return;
			}

			log_kb(['Sending::_franchiseAuthorAlert::Book:: ' => $book_info]);

			self::_sendWhatsappText(
				$info['mobile'],
				[
					'template'		=> '1239906133491135',
					'parameters'	=> [
						$info['first_name'] . ' ' . $info['last_name'],
						$book_info['name'],
						USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
						USER_URL . 'bookstore/' . $book_info['slug'],
					]
				]
			);

			$data['title']			= vsprintf(_li('Your Book %s is now published !'), [
				$book_info['name']
			]);
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('email_franchise_author_no_order', [
				'author_name'		=> $info['first_name'] . ' ' . $info['last_name'],
				'book_name'			=> $book_info['name'],
				'url'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				'url_2'				=> USER_URL . 'bookstore/' . $book_info['slug'],
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

	public function updateBankAlertCron($id) {
		$this->load->model('user/Bank_model', 'bank_model');
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');

		$results = $this->author_earning_model->get_all([
			'status'	=> 0,
		])['rows'] ?? [];

		log_kb(['Sending::updateBankAlertCron:: ' => $results]);

		$exclude = [];

		foreach ($results as $item) {
			if (
				$this->bank_model->get_all(['user_id' => $item['author_id']])['total'] == 0 &&
				!in_array($item['author_id'], $exclude)
			) {
				$user_info = $this->student_model->get($item['author_id']);
				self::_updateBankAlertCron($user_info);
				$exclude[] = $item['author_id'];
			}
		}
	}

	private function _updateBankAlertCron($user_info = []) {
		if (
			($books = $this->book_model->get_all([
				'user_id'	=> $user_info['id'],
				'ne_status'	=> 0,
			])['rows'])
		) {
			log_kb([
				$user_info['mobile'],
				$user_info['email'],
			]);

			log_kb([
				$user_info['mobile'],
				[
					'template'		=> '579959610486886',
					'parameters'	=> [
						trim($user_info['first_name'] . ' ' . $user_info['last_name']),
					],
				]
			]);

			$user_info['mobile'] && self::_sendWhatsappText(
				$user_info['mobile'],
				[
					'template'		=> '579959610486886',
					'parameters'	=> [
						3 => trim($user_info['first_name'] . ' ' . $user_info['last_name']),
					],
				]
			);

			$data['title']			= _li('Reminder for updating bank details');
			$data['heading']		= '';

			$data['content']		= $this->load->view('common/mail/part/royalty_program', [
				'books'				=> $books,
				'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name']
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
	}

	private function _sendRoyaltyAlert($user_info = [], $site_info = []) {
		$user_info['mobile'] && self::_sendWhatsappImage(
			$user_info['mobile'],
			[
				'template'		=> '638308127934694',
				'parameters'	=> [
				],
				'document'	=> [
					'name'	=> 'eventschedule',
					'link'	=> base_url('assets/marketing/eventschedule.png')
				]
			],
		);
	}

	private function _sendInvitationAlert($user_info = [], $site_info = []) {
		$user_info['mobile'] && self::_sendWhatsappImage(
			$user_info['mobile'],
			[
				'template'		=> '1441405319682870',
				// 'parameters'	=> [
				// 	trim($user_info['first_name'] . ' ' . $user_info['last_name'])
				// ],
				// 'document'	=> [
				// 	'name'	=> 'Invitation',
				// 	'link'	=> base_url('assets/marketing/invitation.png')
				// ]
			],
		);

		$data['title']			= _li('Invitation to St Thomas Young Author Fair Awards');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/franchise_invitation_alert', [
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name']
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
}
