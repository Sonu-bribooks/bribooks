<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DailyAlert {
	public function franchiseAuthorNoBookAlertCron($site_id = 0) {
		$students = $this->student_model->get_all([
			'site_id'	=> (int)$site_id,
		])['rows'];

		log_kb(['Sending::franchiseAuthorNoBookAlertCron:: ' => $students]);

		$site_info = $this->site_model->get($site_id);

		foreach ($students as $student) {
			if ($this->book_model->get_all([
				'user_id'	=> $student['id'],
				'ne_status'	=> 0,
			])['total'] == 0) {
				self::_franchiseAuthorNoBookAlert($student, $site_info);
			}
		}
	}

	private function _franchiseAuthorNoBookAlert($user_info = [], $site_info = []) {
		$data['title']			= _li('BriBooks: Become globally published author & win awards');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/franchise_author_no_book_alert', [
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'school_name'		=> $site_info['name'],
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '5806138716097915',
				'parameters'	=> [
					$user_info['first_name'] . ' ' . $user_info['last_name'],
					$site_info['name'],
					USER_URL . 'login',
				]
			],
		);
	}

	public function franchisePublishedNoOrderAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'franchisePublishedNoOrderAlertCron:: ' => $results,
		]);

		$site_info = $this->site_model->get($site_id);

		foreach ($results as $rank => $item) {
			if (!empty($item['quantity'])) continue;

			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_franchisePublishedNoOrderAlert(
				$user_info,
				$book_info,
				$site_info,
			);
		}
	}

	public function _franchisePublishedNoOrderAlert($user_info = [], $book_info = [], $site_info = []) {
		$data['title']			= _li('BriBooks: Become a Best-Selling Author with your book');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/franchise_published_no_order_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
			'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '2940533392759419',
				'parameters'	=> [
					$book_info['author_name'],
					$book_info['name'],
					USER_URL . 'bookstore/' . $book_info['slug'],
					USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				]
			],
		);
	}

	public function franchiseAnnouncementAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'franchiseAnnouncementAlertCron:: ' => $results,
		]);

		$site_info = $this->site_model->get($site_id);

		foreach ($results as $rank => $item) {
			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_franchiseAnnouncementAlert(
				$user_info,
				$book_info,
				$site_info,
			);
		}
	}

	public function _franchiseAnnouncementAlert($user_info = [], $book_info = [], $site_info = []) {
		$data['title']			= _li('BriBooks: Important Update On St Thomas Young Author Fair');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/franchise_announcement_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
			'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '876377513740933',
				'parameters'	=> [
					$book_info['author_name'],
					// $book_info['name'],
					// USER_URL . 'bookstore/' . $book_info['slug'],
					// USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				]
			],
		);
	}

	public function franchiseBestSellingAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'franchiseBestSellingAlertCron:: ' => $results,
		]);

		$site_info = $this->site_model->get($site_id);

		foreach ($results as $rank => $item) {
			if (!empty($item['quantity'])) continue;

			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_franchiseBestSellingAlert(
				$user_info,
				$book_info,
				$site_info,
			);
		}
	}

	public function _franchiseBestSellingAlert($user_info = [], $book_info = [], $site_info = []) {
		$data['title']			= _li('Meet Avni Goyal the Best Selling Author of the Vega School');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/franchise_best_selling', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
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

		$user_info['mobile'] && self::_sendWhatsappImage(
			$user_info['mobile'],
			[
				'template'		=> '438354204987801',
				'parameters'	=> [
					$book_info['author_name'],
					$book_info['name'],
					USER_URL . 'bookstore/' . $book_info['slug'],
				],
				'document'	=> [
					'name'	=> 'avnibestseller',
					'link'	=> base_url('assets/marketing/avnibestseller.jpg')
				]
			],
		);
	}

	public function authorRoyaltyDailyAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'authorRoyaltyDailyAlertCron:: ' => $results,
		]);

		foreach ($results as $rank => $item) {
			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_authorRoyaltyDailyAlert(
				$user_info,
				$book_info,
			);
		}
	}

	private function _authorRoyaltyDailyAlert($user_info = [], $book_info = []) {
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');

		$site_info = $this->site_model->get($user_info['site_id']);

		$royalty_amount = $this->author_earning_model->getTotalEarning([
			'author_id'	=> $user_info['id'],
			'book_id'	=> $book_info['id'],
		]);

		$data['title']			= _li('BriBooks: Your book is earning you Author Royalty');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/author_royalty_daily_alert', [
			'book_name'			=> $book_info['name'],
			'author_name'		=> $book_info['author_name'],
			'royalty_amount'	=> currency($royalty_amount, 0, $site_info['currency_code']),
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '388047006707749',
				'parameters'	=> [
					$book_info['author_name'],
					$book_info['name'],
					currency($royalty_amount, 0, $site_info['currency_code']),
				]
			],
		);
	}

	public function liveRankingAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'liveRankingAlertCron:: ' => $results,
		]);

		foreach ($results as $rank => $item) {
			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_liveRankingAlert(
				$user_info,
				$book_info,
				$rank + 1,
				readable_format(!empty($item['quantity']) ? $item['quantity'] : 0)
			);
		}
	}

	private function _liveRankingAlert($user_info = [], $book_info = [], $rank = 0, $sold = 0) {
		$site_info = $this->site_model->get($user_info['site_id']);

		$this->load->model('user/AuthorEarning_model', 'author_earning_model');

		$royalty_amount = $this->author_earning_model->getTotalEarning([
			'author_id'	=> $user_info['id'],
			'book_id'	=> $book_info['id'],
		]);

		$data['title']			= _li('BriBooks: Author Royalty and ranking update. 5 hours to go!');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/live_ranking_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'royalty'			=> currency($royalty_amount, 0, $site_info['currency_code']),
			'rank'				=> $rank,
			'rank_url'			=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
			'sold'				=> $sold,
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '861416944844918',
				'parameters'	=> [
					$book_info['author_name'],
					$book_info['name'],
					(string)$rank,
					currency($royalty_amount, 0, $site_info['currency_code']),
					// USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				]
			],
		);
	}

	public function isbnAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'isbnAlertCron:: ' => $results,
		]);

		foreach ($results as $rank => $item) {
			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			!empty($item['quantity']) && self::_isbnAlert(
				$user_info,
				$book_info,
				$rank + 1,
				readable_format(!empty($item['quantity']) ? $item['quantity'] : 0)
			);
		}
	}

	private function _isbnAlert($user_info = [], $book_info = [], $rank = 0, $sold = 0) {
		$site_info = $this->site_model->get($user_info['site_id']);

		$this->load->model('user/AuthorEarning_model', 'author_earning_model');

		$royalty_amount = $this->author_earning_model->getTotalEarning([
			'author_id'	=> $user_info['id'],
			'book_id'	=> $book_info['id'],
		]);

		$data['title']			= _li('BriBooks: Verify your book\'s ISBN');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/isbn_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '1069211933669008',
				'parameters'	=> [
					$book_info['author_name'],
					$book_info['name'],
				]
			],
		);
	}

	public function reviewerAlertCron($site_id = 0) {
		$this->load->model('book/Review_model', 'review_model');

		$results = $this->review_model->get_all([
			'user_site_id'	=> $site_id,
		])['rows'] ?? [];

		log_kb([
			'reviewerAlertCron:: ' => $results,
		]);

		$exclude = [];

		foreach ($results as $key => $item) {
			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['book_id']);

			if (!in_array($item['user_id'], $exclude)) {
				self::_reviewerAlert(
					$user_info,
					$book_info,
				);

				$exclude[] = $item['user_id'];
			}
		}
	}

	private function _reviewerAlert($user_info = [], $book_info = [], $rank = 0, $sold = 0) {
		$site_info = $this->site_model->get($user_info['site_id']);

		$data['title']			= _li('Your child can also Write, Publish & Sell books on BriBooks');
		$data['heading']		= '';

		$data['content']		= $this->load->view('common/mail/part/reviewer_alert', [
			'book_name'			=> $book_info['name'],
			'author_name'		=> $book_info['author_name'],
			'user_name'			=> $user_info['first_name'] . ' ' . $user_info['last_name'],
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '493446469330030',
				'parameters'	=> [
					$user_info['first_name'] . ' ' . $user_info['last_name'],
					$book_info['author_name'],
					$book_info['name'],
				]
			],
		);
	}

	public function franchiseBestSellingAwardAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'franchiseBestSellingAwardAlertCron:: ' => $results,
		]);

		$site_info = $this->site_model->get($site_id);

		foreach ($results as $rank => $item) {
			// if (!empty($item['quantity'])) continue;

			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_franchiseBestSellingAwardAlert(
				$user_info,
				$book_info,
				$site_info,
			);
		}
	}

	public function _franchiseBestSellingAwardAlert($user_info = [], $book_info = [], $site_info = []) {
		$data['title']			= _li('BriBooks: Become a Best-Selling Author with your book');
		$data['heading']		= '';

		$date1 = new DateTime(date('Y-m-d'));
		$date2 = new DateTime('2022-11-03');
		$counter = ($date2->diff($date1))->d;

		$data['content']		= $this->load->view('common/mail/part/franchise_best_selling_award_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
			'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
			'counter'			=> $counter,
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

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '3226069324324006',
				'parameters'	=> [
					$book_info['author_name'],
					(string)$counter,
					// $book_info['name'],
					// USER_URL . 'bookstore/' . $book_info['slug'],
					// USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				]
			],
		);
	}

	public function franchiseQualifierAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'franchiseQualifierAlertCron:: ' => $results,
		]);

		$site_info = $this->site_model->get($site_id);

		foreach ($results as $rank => $item) {
			// if (!empty($item['quantity'])) continue;

			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_franchiseQualifierAlert(
				$user_info,
				$book_info,
				$site_info,
				$item,
			);
		}
	}

	public function _franchiseQualifierAlert($user_info = [], $book_info = [], $site_info = [], $sold_info = []) {
		$data['title']			= _li('BriBooks: Become a Best-Selling Author with your book');
		$data['heading']		= '';

		$date1 = new DateTime(date('Y-m-d'));
		$date2 = new DateTime('2022-11-03');
		$counter = ($date2->diff($date1))->d;

		$qualifier = 'National';
		$next_sold = 0;
		$sold_info['quantity'] = (int)($sold_info['quantity'] ?? 0);

		if ($sold_info['quantity'] >= 30) {
			$next_sold = 0;
			$qualifier = 'Global';
		} elseif ($sold_info['quantity'] >=10 && $sold_info['quantity'] < 30) {
			$next_sold = 30 - $sold_info['quantity'];
			$qualifier = 'Global';
		} elseif ($sold_info['quantity'] < 10) {
			$next_sold = 10 - $sold_info['quantity'];
			$qualifier = 'National';
		}

		$data['content']		= $this->load->view('common/mail/part/franchise_qualifier_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'sold'				=> $sold_info['quantity'],
			'next_sold'			=> $next_sold,
			'qualifier'			=> $qualifier,
			'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
			'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
			'counter'			=> $counter,
		], true);

		$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

		log_kb([
			$user_info['email'],
			$data['title'],
			[
				'author_name'		=> $book_info['author_name'],
				'book_name'			=> $book_info['name'],
				'sold'				=> $sold_info['quantity'],
				'next_sold'			=> $next_sold,
				'qualifier'			=> $qualifier,
				'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
				'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				'counter'			=> $counter,
			]
		]);

		$user_info['email'] && self::email(
			$user_info['email'],
			$data['title'],
			$message,
			[],
			[],
		);

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '805346564021991',
				'parameters'	=> [
					$book_info['author_name'],
					$book_info['name'],
					(string)$sold_info['quantity'],
					(string)$next_sold,
					$qualifier,
					(string)$counter,
					USER_URL . 'bookstore/' . $book_info['slug'],
					// USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				]
			],
		);
	}

	public function franchiseQualifierFinalAlertCron($site_id = 0) {
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		log_kb([
			'franchiseQualifierFinalAlertCron:: ' => $results,
		]);

		$site_info = $this->site_model->get($site_id);

		foreach ($results as $rank => $item) {
			// if (!empty($item['quantity'])) continue;

			$user_info = $this->student_model->get($item['user_id']);
			$book_info = $this->book_model->get($item['id']);

			self::_franchiseQualifierFinalAlert(
				$user_info,
				$book_info,
				$site_info,
				$item,
			);
		}
	}

	public function _franchiseQualifierFinalAlert($user_info = [], $book_info = [], $site_info = [], $sold_info = []) {
		$data['title']			= _li('BriBooks: Countdown to Best-seller Author Awards has begun');
		$data['heading']		= '';

		$date1 = new DateTime(date('Y-m-d H:i:s'));
		$date2 = new DateTime('2022-11-03 21:00:00');
		$counter_d = ($date2->diff($date1))->d;
		$counter_h = ($date2->diff($date1))->h;
		$counter = $counter_d ? $counter_d : $counter_h;
		$counter_type = $counter_d ? 'days' : 'hours';

		$qualifier = 'National';
		$next_sold = 0;
		$sold_info['quantity'] = (int)($sold_info['quantity'] ?? 0);

		if ($sold_info['quantity'] >= 30) {
			$next_sold = 0;
			$qualifier = 'Global';
		} elseif ($sold_info['quantity'] >=10 && $sold_info['quantity'] < 30) {
			$next_sold = 30 - $sold_info['quantity'];
			$qualifier = 'Global';
		} elseif ($sold_info['quantity'] < 10) {
			$next_sold = 10 - $sold_info['quantity'];
			$qualifier = 'National';
		}

		$data['content']		= $this->load->view('common/mail/part/franchise_qualifier_final_alert', [
			'author_name'		=> $book_info['author_name'],
			'book_name'			=> $book_info['name'],
			'sold'				=> $sold_info['quantity'],
			'next_sold'			=> $next_sold,
			'qualifier'			=> $qualifier,
			'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
			'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
			'counter'			=> $counter,
			'counter_type'		=> $counter_type,
		], true);

		$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

		log_kb([
			$user_info['email'],
			$data['title'],
			[
				'author_name'		=> $book_info['author_name'],
				'book_name'			=> $book_info['name'],
				'sold'				=> $sold_info['quantity'],
				'next_sold'			=> $next_sold,
				'qualifier'			=> $qualifier,
				'url'				=> USER_URL . 'bookstore/' . $book_info['slug'],
				'url_2'				=> USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				'counter'			=> $counter,
				'counter_type'		=> $counter_type,
			]
		]);

		$user_info['email'] && self::email(
			$user_info['email'],
			$data['title'],
			$message,
			[],
			[],
		);

		$user_info['mobile'] && self::_sendWhatsappText(
			$user_info['mobile'],
			[
				'template'		=> '1059051321429244',
				'parameters'	=> [
					$book_info['author_name'],
					(string)$counter,
					$counter_type,
					$book_info['name'],
					(string)$sold_info['quantity'],
					(string)$next_sold,
					$qualifier,
					USER_URL . 'bookstore/' . $book_info['slug'],
					// USER_URL . 'af/' . $site_info['site_code'] . '/bookstore',
				]
			],
		);
	}
}
