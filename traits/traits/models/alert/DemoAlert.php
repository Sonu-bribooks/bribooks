<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait DemoAlert {
	public function demoRequest($lead_id) {
		// Send alert to admin for new lead request
		$lead_info = $this->lead_model->get($lead_id);

		self::cron($lead_info['id'], 'demoRequestCron');

		// Send alert to student
		self::sms($lead_info['mobile'], self::formatMessage('sms_demo_request', [
			'student_name'		=> $lead_info['name'],
			'parent_name'		=> $lead_info['parent_name'],
			'course_name'		=> $lead_info['course'],
			'datetime'			=> $lead_info['schedule'],
		]));
	}

	public function demoRequestCron($lead_id) {
		// Send alert to admin for new lead request
		$lead_info = $this->lead_model->get($lead_id);
		$city_info = $this->city_model->get($lead_info['city_id']);

		$data['title']		= $lead_info['site'] . ' | ' . _l('demo_request') . ' ' . $lead_info['course'];
		$data['course']		= $lead_info['course'];
		$data['mode']		= _l($lead_info['mode']);
		$data['center']		= $lead_info['center'] ? ($lead_info['center'] . ',' . $city_info['name']) : '';
		// $data['schedule']	= date('M j, Y h:i A', strtotime($lead_info['schedule']));
		$data['schedule']	= $lead_info['schedule'];
		$data['user']		= [
			'name'			=> $lead_info['name'],
			'age'			=> $lead_info['age'],
			'parent_name'	=> $lead_info['parent_name'],
			'mobile'		=> $lead_info['mobile'],
		];

		$data['link']		= site_url('login');

		$message = $this->load->view('common/mail/demo_request', $data, true);

		$bcc = self::additionalEmails($lead_info['site_id']);

		self::email(
			get_site($lead_info['site_id'], 'owner_email'),
			$data['title'],
			$message,
			[],
			$bcc
		);

		// Send alert to student
		$data['title']		= _l('demo_request') . ' ' . $lead_info['course'];
		$data['link']		= site_url();

		$message = $this->load->view('common/mail/demo_schedule', $data, true);

		$bcc = [];

		// $lead_info['email'] && self::email(
		// 	$lead_info['email'],
		// 	$data['title'],
		// 	$message,
		// 	[],
		// 	$bcc
		// );
	}

	public function demoNotResponding($lead_id) {
		$lead_info 	= $this->lead_model->get($lead_id);
		$lead_info['course'] = substr($lead_info['course'], 0, 18) . '..';

		self::sms($lead_info['mobile'], self::formatMessage('sms_demo_not_responding', [
			'student_name'		=> $lead_info['name'],
			'parent_name'		=> $lead_info['parent_name'],
			'course_name'		=> $lead_info['course'],
			'datetime'			=> $lead_info['schedule'],
		]));
	}

	public function demoFeeDetails($lead_id) {
		self::cron($lead_id, 'demoFeeDetailsCron');
	}

	public function demoFeeDetailsCron($lead_id) {
		$lead_info = $this->lead_model->get($lead_id);

		$data['title']			= _li('iCode Global Hackathon ' . date('Y') . ': Registration Confirmation');
		$data['heading']		= '';
		$data['subheading']		= '';
		$data['content']		= $this->load->view('common/mail/part/course_details', [
			'student_name'		=> $lead_info['name'],
			'emi_type'			=> _l('free'),
			'course_name'		=> $lead_info['course'],
		], true);
		$data['link']			= site_url();
		$data['link_text']		= _l('login_to_portal');

		$message 				= $this->load->view('common/mail/general', $data, true);

		$attachment 			= FCPATH . '/assets/pdf/' . ($this->config->item('site_country_code') === 'IN' ? 'ICode_India Brochure' : 'ICode_Global Brochure') .  '.pdf';

		$bcc = [];

		$lead_info['email'] && self::email(
			$lead_info['email'],
			$data['title'],
			$message,
			[],
			$bcc,
			$attachment
		);
	}

	public function demoConfirmed($lead_id) {
		// Send alert for confirm scheduled
		$lead_info = $this->lead_model->get($lead_id);

		self::cron($lead_info['id'], 'demoConfirmedCron');

		self::sms($lead_info['mobile'], self::formatMessage('sms_demo_confirmed', [
			'student_name'		=> $lead_info['name'],
			'parent_name'		=> $lead_info['parent_name'],
			'course_name'		=> $lead_info['course'],
			'datetime'			=> $lead_info['confirmed_schedule'],
		]));
	}

	public function demoConfirmedCron($lead_id) {
		// Send alert for confirm scheduled
		$lead_info = $this->lead_model->get($lead_id);
		$city_info = $this->city_model->get($lead_info['city_id']);

		$data['title']		= _l('demo_confirmed') . ' ' . $lead_info['course'];
		$data['course']		= $lead_info['course'];
		$data['mode']		= _l($lead_info['mode']);
		$data['center']		= $lead_info['center'] ? ($lead_info['center'] . ',' . $city_info['name']) : '';
		$data['schedule']	= date('M j, Y h:i A', strtotime($lead_info['confirmed_schedule']));
		$data['user']		= [
			'name'			=> $lead_info['name'],
			'age'			=> $lead_info['age'],
			'parent_name'	=> $lead_info['parent_name'],
			'mobile'		=> $lead_info['mobile'],
			'email'			=> $lead_info['email'],
		];
		$data['link']		= site_url();

		$message = $this->load->view('common/mail/demo_confirmed', $data, true);

		$bcc = [];

		$lead_info['email'] && self::email(
			$lead_info['email'],
			$data['title'],
			$message,
			[],
			$bcc
		);

		// Teacher alert
		$schedule_info = $this->schedule_model->get($lead_info['schedule_id']);

		//Teacher alert
		$data['title']		= $lead_info['site'] . ' | ' . _l('demo_schedule') . ' ' . $lead_info['course'];
		$data['link']		= site_url('login');

		$message = $this->load->view('common/mail/demo_request', $data, true);

		self::email(
			$schedule_info['email'],
			$data['title'],
			$message
		);
	}

	public function demoCompleted($lead_id) {
		// Send alert for confirm scheduled
		$lead_info = $this->lead_model->get($lead_id);
		$city_info = $this->city_model->get($lead_info['city_id']);

		self::cron($lead_info['id'], 'demoCompletedCron');

		$date 				= date('M j, Y', strtotime($lead_info['confirmed_schedule']));
		$time 				= date('h:i A', strtotime($lead_info['confirmed_schedule']));
		$location 			= $lead_info['mode'] == 'online' ? $data['mode'] : $data['center'];

		self::sms($lead_info['mobile'], self::formatMessage('sms_demo_completed', [
			'student_name'		=> $lead_info['name'],
			'parent_name'		=> $lead_info['parent_name'],
			'course_name'		=> $lead_info['course'],
			'datetime'			=> $lead_info['confirmed_schedule'],
		]));
	}

	public function demoCompletedCron($lead_id) {
		// Send alert for confirm scheduled
		$lead_info = $this->lead_model->get($lead_id);
		$city_info = $this->city_model->get($lead_info['city_id']);

		$data['title']			= _li('Demo completed ') . ' ' . $lead_info['course'];
		$data['heading']		= _li('Dear') . ' ' . $lead_info['parent_name'];
		$data['subheading']		= _li('Dear') . ' ' . $lead_info['parent_name'];
		$data['content']		= vsprintf(_li('We loved having %s in the class today. who is an exceptionally bright child who has successfully completed his coding trial class with LeapLearner.<br><br>We are excited to start %s’s coding journey with LeapLearner. Our enrolment team will be calling you soon for the next steps.<br><br>We are also detailing a few links highlighting the importance of coding for children.'), [
			$lead_info['name'],
			$lead_info['name']
		]);
		$data['link']			= '';
		$data['link_text']		= _l('Login');
		$data['term']			= false;

		$message = $this->load->view('common/mail/general', $data, true);

		$bcc = self::additionalEmails($lead_info['site_id']);

		$lead_info['email'] && self::email(
			$lead_info['email'],
			$data['title'],
			$message,
			[],
			$bcc
		);
	}

	public function generateParticipationCertificate ($event_id = 0, $date_added = '', $type = '') {
		if (empty($event_id) || empty($date_added)) return;

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if ($type == 'published') {
			$rows = $this->db->query("SELECT book.id,users.id as uid, book.name as book_name, book.author_name,
				users.mobile,
				users.email
				FROM event_book
				join book on book.id = event_book.book_id
				join users on users.id = book.user_id
				WHERE event_book.`event_id` = " . $event_id . "
				AND event_book.`_deleted` = '0'
				and book._deleted = 0
				and book.archived = 0
				and users._deleted = 0
				and users.id not in
				(
				select user_id
				FROM `event_order`
				join book on book.id = event_order.book_id
				WHERE event_order.`event_id` = " . $event_id . "
				AND event_order.`_deleted` = '0'
				and book._deleted = 0
				and book.archived = 0
				)
				group by users.id
				order by users.id"
			)->result_array();
		} else {
			$rows = $this->db->query("SELECT event_user.event_id, event_user.user_id as uid, concat(users.first_name, ' ', users.last_name) as name,
			users.site_id, users.location
			from event_user
			join users on users.id = event_user.user_id
			where event_id = " . $event_id . "
			and event_user._deleted = 0
			and users._deleted = 0
			and users.id not in (select user_id from certificates where event_id = 14 and _deleted = 0)"
			)->result_array();
		}

		foreach ($rows as $row) {

			$cert_info = $this->certificate_model->get_all([
				'event_id'		=> $event_id,
				'user_id'		=> $row['uid'],
				'achievement'	=> 0
			])['rows'][0] ?? '';

			if (empty($cert_info)) {
				if (empty($this->certificate_model->get_all([
					'event_id'		=> $event_id,
					'book_id'		=> 0,
					'user_id'		=> $row['uid']
				])['rows'][0] ?? '')) {
					$template_info = $this->certificate_template_model->get_all([
						'event_id' 	=> $event_id,
						'type'		=> 'participation_cert',
					])['rows'][0] ?? '';

					$author_info = $this->student_model->get($row['uid']);
					$certificate_key = sprintf('participation_cert_user_%s_%s', $row['uid'], $event_id);

					$certificate_id = $this->certificate_model->add([
						'site_id'					=> $author_info['site_id'] ?? 1,
						'event_id'					=> $event_id,
						'book_id'					=> 0,
						'user_id'					=> $row['uid'],
						'type'						=> 'participation_cert',
						'achievement'				=> $template_info['achievement'] ?? 0,
						'certificate_template_id'	=> $template_info['id'] ?? 0,
						'unique_id'					=> $template_info['id'] ?? 0,
						'name'						=> $certificate_key,
						'image'						=> $certificate_key,
					]);

					if (!empty($certificate_id)) {
						$unique_id = 'BB/' . sprintf('%08d', $certificate_id) . '/' . ($template_info['id'] ?? '12') ;
						$this->certificate_model->edit($certificate_id, [
							'unique_id' 	=> $unique_id,
							'date_added' 	=> $date_added
						]);
					}
				};
			}
		}
	}
	public function enrolBookInSummer2024($type = 0) {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->load->library('parsecsv');

		// $this->parsecsv->auto('assets/csv/enrol_summer_book.csv');
		// $rows = $this->parsecsv->data;

		if ($type == 1) {
			$rows = $this->db->query("SELECT users.id as user_id,book_version.user_id as book_user,book_version.version as book_version,users.location,book_version.book_id as book_id, Date(users.date_added) as users_date_added, Date(book_version.date_added) as book_date_added, Date(book_version.date_published) as date_published,
			(select date_added from access_log where module =  concat('book_published_', book_version.book_id) order by date_added asc limit 1) as first_published
			,(select date_added from book where book.id = book_version.book_id limit 1) as date_added
			from book_version
			join users on users.id = book_version.user_id
			WHERE book_version.book_id not in (select book_id from event_book)
			and users.location = 'India'
			and users._deleted = 0
			and book_version.version = 1
			and book_version._deleted = 0
			and book_version.archived = 0
			and book_version.status = 1
			and book_version.date_published >'2024-03-16'
			and book_version.date_added >'2023-06-01'
			and book_version.book_id in (select product_id from order_product join `order` on `order`.id = order_product.order_id where order_product._deleted = 0 and `order`.status not in (0,91,92))
			having first_published > '2024-03-16'   and first_published < '2024-07-01'
			order by book_version.date_added")->result_array();
		} else {
			$rows = $this->db->query("SELECT book_version.book_id,
			book_version.version,
			book_version.date_added as version_date_added,
			(select date_added from book where book.id = book_version.book_id limit 1) as date_added,
			book_version.date_published,
			(select date_added from access_log where module =  concat('book_published_', book_version.book_id) order by date_added asc limit 1) as first_published
			FROM `book_version`
			join users on users.id = book_version.user_id
			where book_version.status = 1
			and version = 1
			and date_published > '2024-03-16'
			and book_version.date_added > '2024-03-16'
			and book_id not in (select book_id from event_book)
			and book_version.archived = 0
			and book_version._deleted = 0
			and users.location = 'india'
			and users._deleted = 0
			having first_published > '2024-03-16' and first_published < '2024-07-01' and date_added > '2023-05-01'
			order by date_added asc")->result_array();
		}

		// pr($rows);

		foreach ($rows as $key =>$row) {
			// echo $row['book_id'];
			$book_info = $this->book_model->get($row['book_id']);
			// pr($book_info,1);

			if (!empty($book_info) && empty($this->event_book_model->get_all([
				'book_id'		=> $row['book_id']
			])['rows'][0] ?? '')) {
				if (!empty($this->event_book_model->add([
					'event_id'		=> 14,
					'book_id'		=> $row['book_id']
				]))) {
					if (empty($this->event_user_model->get_all([
						'event_id'		=> 14,
						'user_id'		=> $book_info['user_id']
					])['rows'][0] ?? '')) {
						$this->event_user_model->add([
							'event_id'		=> 14,
							'user_id'		=> $book_info['user_id']
						]);
					}

					if (!empty($products = $this->order_product_model->get_all([
						'product_id'	 => $book_info['id']
					])['rows'] ?? [])) {
						$order_ids = [];
						foreach ($products as $product) {
							$order_info = $this->order_model->get($product['order_id']);

							if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {
								$this->event_order_model->add([
									'event_id'		=> 14,
									'order_id'		=> $order_info['id'],
									'book_id'		=> $book_info['id'],
									'quantity'		=> $product['quantity']
								]);

								$order_ids[] = $order_info['id'];
							}
						}

						if (!empty($order_ids)) {
							rsort($order_ids);
							$this->ranking_lib->updateRank($order_ids[0]);
							$this->genericcertificate_lib->createCertificate($order_ids[0], false);

							if (!empty($certficates = $this->certificate_model->get_all([
								'event_id'	 => 0,
								'book_id'	 => $book_info['id']
							])['rows'] ?? [])) {

								$this->db->where_in('id', array_column($certficates, 'id'));
								$this->db->update('certificates',  [
									'_deleted'		=> 1,
									'date_deleted'	=> date('Y-m-d H:i:s'),
								]);
							}
						}
					}
				}
			}
		}
	}

	public function enrolUserAndBookInEvent() {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->load->library('parsecsv');

		$rows = $this->db->query("SELECT users.id
		FROM `users`
		WHERE users.`_deleted` = '0'
		AND users.role_id = 2
		AND users.`date_added` > '2024-04-14'
		AND users.`location` = 'United Kingdom'
		AND users.id NOT IN (select user_id from event_user where _deleted = 0)")->result_array();

		// pr($rows);

		foreach ($rows as $key =>$row) {
			$author_info = $this->student_model->get($row['id']);

			if (!empty($author_info) && empty($this->event_user_model->get_all([
				'event_id'		=> 15,
				'user_id'		=> $author_info['id']
			])['rows'][0] ?? '')) {
				$this->event_user_model->add([
					'event_id'		=> 15,
					'user_id'		=> $author_info['id']
				]);

				$books = $this->book_model->get_all([
					'user_id' => $author_info['id']
				]);

				foreach ($books as $book) {
					if (empty($this->event_book_model->get_all([
						'book_id'		=> $book['id']
					])['rows'][0] ?? '')) {
						if (!empty($this->event_book_model->add([
							'event_id'		=> 15,
							'book_id'		=> $book['id']
						]))) {

							if (!empty($products = $this->order_product_model->get_all([
								'product_id'	 => $book['id']
							])['rows'] ?? [])) {
								$order_ids = [];
								foreach ($products as $product) {
									$order_info = $this->order_model->get($product['order_id']);

									if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {
										$this->event_order_model->add([
											'event_id'		=> 15,
											'order_id'		=> $order_info['id'],
											'book_id'		=> $book['id'],
											'quantity'		=> $product['quantity']
										]);

										$order_ids[] = $order_info['id'];
									}
								}

								if (!empty($order_ids)) {
									rsort($order_ids);
									$this->genericcertificate_lib->createCertificate($order_ids[0], false);

									if (!empty($certficates = $this->certificate_model->get_all([
										'event_id'	 => 0,
										'book_id'	 => $book['id']
									])['rows'] ?? [])) {

										$this->db->where_in('id', array_column($certficates, 'id'));
										$this->db->update('certificates',  [
											'_deleted'		=> 1,
											'date_deleted'	=> date('Y-m-d H:i:s'),
										]);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function welcomeDirectUser($user_id = 0) {

		if (!empty($user_id) && !empty($author_info = $this->student_model->get($user_id))) {

			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));

			$subject 						= "Congratulations! You Are Now Enrolled in the National Young Authors' Fair 2024";
			$message						= $this->load->view('common/mail/part/direct_user_signup', [
				'author_name' 	=> ucwords($author_info['first_name'] . ' ' . $author_info['last_name']),
				'email' 		=> $author_info['email'],
				'username' 		=> $author_info['username'],
				'password' 		=> $password,
			], true);

			if ($author_info['email']) {

				self::email(
					trim($author_info['email']),
					$subject,
					$message,
					[],
					(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
					[]
				);

				$this->student_model->edit($author_info['id'], [
					'password'		=> $encoded_password,
				]);
			}

		}
	}

	public function enrolUserInSummerCamp2024() {

		$this->load->model('event/EventUser_model', 'event_user_model');

		$rows = $this->db->query("SELECT distinct book.user_id
		FROM `book`
		join users on users.id = book.user_id
		where book.status = 0
		and book._deleted = 0
		and book.archived = 0
		and users._deleted = 0
		and users.location = 'india'
		and book.date_added > '2024-04-01'
		and users.date_added > '2023-04-01'
		and book.user_id not in (select user_id from event_user where _deleted = 0)")->result_array();

		$new_rows = $this->db->query("SELECT users.id as user_id
		from users
		where
		users.location = 'india'
		and users.id not in (select user_id from book)
		and users.date_added > '2024-04-01'
		and users.source != 'bookstore'
		and users._deleted = 0
		and users.address_id = 0
		and users.role_id = 2
		and users.id not in (select user_id from event_user where event_id = 14)
		and users.id not in (SELECT user_id from review)
		and users.id not in (SELECT user_id from `order`)
		and users.id not in (select user_id from event_user);")->result_array();

		foreach ($rows as $row) {
			if (
				$row['user_id'] &&
				empty($this->event_user_model->getEventUserByUserId(14, $row['user_id']))
			) {
				$this->event_user_model->add([
					'event_id'	=> 14,
					'user_id'	=> (int)$row['user_id'],
				]);
			}
		}

		foreach ($new_rows as $new_row) {
			if (
				$new_row['user_id'] &&
				empty($this->event_user_model->getEventUserByUserId(14, $new_row['user_id']))
			) {
				$this->event_user_model->add([
					'event_id'	=> 14,
					'user_id'	=> (int)$new_row['user_id'],
				]);
			}
		}
	}

	public function buildEventRank() {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
		$this->load->library('SchoolRanking_lib', 'schoolranking_lib');


		$rows = $this->db->query("SELECT event_teacher.*,
		(SELECT event_book.book_id
		FROM `event_book`
		join book on book.id = book_id
		join users on users.id  = book.user_id
		WHERE `event_id` = '21' AND event_book.`_deleted` = '0'
		AND users.site_id = teacher.site_id
		AND users.grade = teacher.grade
		AND users.section = teacher.section
		LIMIT 1) as book_id
		FROM `event_teacher`
		JOIN users as teacher ON teacher.id = teacher_id
		WHERE `event_id` = '21'
		AND event_teacher.`_deleted` = '0'
		HAVING book_id IS NOT NULL")->result_array();

		// pr($rows,1);

		foreach ($rows as $key =>$row) {
			if (!empty($row['book_id'])) {
				// $this->schoolranking_lib->updateRank($row['book_id']);
				$this->teacherranking_lib->updateRank($row['book_id']);
			}
			// die;
		}
	}
	public function sendEventBookConfirmationMail() {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->library('Student_model', 'student_model');

		$rows = $this->db->query("SELECT event_book.event_id, event_book.book_id, event_book._deleted, book.user_id
		FROM `event_book`
		join book on book.id = event_book.book_id
		join users on users.id = book.user_id
		WHERE `event_id` = '14' AND event_book.`_deleted` = '0'
		AND book._deleted = 0
		AND book.archived = 0
		AND users._deleted = 0
		GROUP BY book.user_id")->result_array();

		// pr($rows,1);

		foreach ($rows as $key =>$row) {
			if (!empty($row['user_id']) && !empty($author_info = $this->student_model->get($row['user_id']))) {
				$books = $this->book_model->get_all([
					'user_id' 	=> $row['user_id'],
					'event_id'	=> $row['event_id'],
					'archived'	=> 0
				])['rows'] ?? [];

				if (!empty($books) && !empty($author_info['email'])) {
					$subject 						= "Publishing Deadline for Summer Book Writing Festival 2024";
					$message						= $this->load->view('common/mail/part/event_book_confirm', [
						'author_name' 	=> ucwords($author_info['first_name'] . ' ' . $author_info['last_name']),
						'books' 		=> $books,
					], true);

					self::email(
						trim($author_info['email']),
						$subject,
						$message,
						[],
						(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
						[]
					);
				}
			}
		}
	}

	public function updateTableData() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->library('parsecsv');

		$this->parsecsv->auto('assets/csv/table_update/school_address.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $key => $row) {
			if (!empty($school_info = $this->school_model->get($row['id']))) {
				$this->school_model->edit($school_info['id'], [
					'address' => $row['address'],
					'pincode' => $row['pincode']
				]);

				if (!empty($school_info['site_id'])) {
					$this->site_model->editById($school_info['site_id'], [
						'address' => $row['address'],
						'pincode' => $row['pincode']
					]);
				}
			}
			// break;
		}
	}

	public function registerGlobalSchoolCsv($parent_site_id = 0, $file_name = '', $event_id = 0) {

		$this->load->model('user/User_model', 'user_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->model('school/Site_model', 'site_model');

		if (empty($parent_site_id) || empty($file_name)) return;

		$site_info = $this->site_model->get($parent_site_id);

		if (empty($site_info)) return;

		// $this->load->library('parsecsv');

		// $this->parsecsv->auto(sprintf('assets/csv/schools/%s.csv', $file_name));
		// $rows = $this->parsecsv->data;

		$file_path = FCPATH . 'assets/csv/schools/' . $file_name . '.csv';

		$utf8_content = mb_convert_encoding(file_get_contents($file_path), 'UTF-8', 'auto');

		$this->load->library('parsecsv');
		$this->parsecsv->parse($utf8_content); // Use parse() instead of auto() for string input

		$rows = $this->parsecsv->data;

		foreach ($rows as $key => $row) {

			if (empty($row['school_name']) || empty($row['email']) || empty($row['city_id']) || empty($row['state_id'])) continue;


			if (!empty($row['email']) && !empty($user_email_info = $this->user_model->get_all([
				'email'                 => $row['email'],
			])['rows'][0] ?? '')) {
				$this->db->insert('garbage_school', [
					'event_id'		                => $event_id,
					'state_id'		                => $row['state_id'] ?? 1,
					'city_id'		                => $row['city_id'] ?? 1,
					'state'		                	=> $row['state'] ?? '',
					'city'		                	=> $row['city'] ?? '',
					'user_id'		                => $user_email_info['id'] ?? 1,
					'school_name'		            => $row['school_name'] ?? '',
					'email' 		  			    => $row['email'] ?? '',
					'mobile' 	      			    => $row['mobile'] ?? '',
					'owner_name'   					=> !empty($row['owner_name']) ? trim($row['owner_name']) : '',
					'authorized_person'   			=> !empty($row['authorized_person']) ? trim($row['authorized_person']) : '',
					'alternate_email' 		        => $row['alternate_email'] ?? '',
					'alternate_mobile' 	            => $row['alternate_mobile'] ?? '',
					'zipcode'   					=> $row['zipcode'] ?? '',
					'address'   					=> $row['address'] ?? '',
					'site_type'   					=> $row['site_type'] ?? 1,
					'date_added'	                => date('Y-m-d H:i:s')
				]);

				continue;
			}

			if (!empty($row['email']) && !empty($site_info_data = $this->site_model->get_all([
				'owner_email'        => $row['email'],
			])['rows'][0] ?? '')) {
				$this->db->insert('garbage_school', [
					'event_id'		                => $event_id,
					'state_id'		                => $row['state_id'] ?? 1,
					'city_id'		                => $row['city_id'] ?? 1,
					'state'		                	=> $row['state'] ?? '',
					'city'		                	=> $row['city'] ?? '',
					'site_id'		                => $site_info_data['id'] ?? 1,
					'school_name'		            => $row['school_name'] ?? '',
					'email' 		  			    => $row['email'] ?? '',
					'mobile' 	      			    => $row['mobile'] ?? '',
					'owner_name'   					=> !empty($row['owner_name']) ? trim($row['owner_name']) : '',
					'authorized_person'   			=> !empty($row['authorized_person']) ? trim($row['authorized_person']) : '',
					'alternate_email' 		        => $row['alternate_email'] ?? '',
					'alternate_mobile' 	            => $row['alternate_mobile'] ?? '',
					'zipcode'   					=> $row['zipcode'] ?? '',
					'address'   					=> $row['address'] ?? '',
					'site_type'   					=> $row['site_type'] ?? 1,
					'date_added'	                => date('Y-m-d H:i:s')
				]);
				continue;
			}

			if (!empty($row['email']) && !empty($school_info = $this->school_model->get_all([
				'owner_email'        => $row['email'],
			])['rows'][0] ?? '')) {
				$this->db->insert('garbage_school', [
					'event_id'		                => $event_id,
					'state_id'		                => $row['state_id'] ?? 1,
					'city_id'		                => $row['city_id'] ?? 1,
					'state'		                	=> $row['state'] ?? '',
					'city'		                	=> $row['city'] ?? '',
					'school_id'		                => $school_info['id'] ?? 1,
					'school_name'		            => $row['school_name'] ?? '',
					'email' 		  			    => $row['email'] ?? '',
					'mobile' 	      			    => $row['mobile'] ?? '',
					'owner_name'   					=> !empty($row['owner_name']) ? trim($row['owner_name']) : '',
					'authorized_person'   			=> !empty($row['authorized_person']) ? trim($row['authorized_person']) : '',
					'alternate_email' 		        => $row['alternate_email'] ?? '',
					'alternate_mobile' 	            => $row['alternate_mobile'] ?? '',
					'zipcode'   					=> $row['zipcode'] ?? '',
					'address'   					=> $row['address'] ?? '',
					'site_type'   					=> $row['site_type'] ?? 1,
					'date_added'	                => date('Y-m-d H:i:s')
				]);
				continue;
			}

			// $state_id 	= 0;
			// $city_id 	= 0;

			// if(!empty($row['state'])) {
			// 	if ($state_info = $this->db->get_where('state', [
			// 		'country_id'	=> $row['country_id'],
			// 		'name' 			=> trim($row['state'])
			// 	])->row_array()) {
			// 		$state_id = $state_info['id'];
			// 	}
			// }

			// if(!empty($state_id) && !empty($row['city'])) {
			// 	if ($city_info = $this->db->get_where('city', [
			// 		'name' 		=> trim($row['city']),
			// 		'state_id'	=> $state_id,
			// 	])->row_array()) {
			// 		$city_id = $city_info['id'];
			// 	} else {
			// 		$city_id = $this->city_model->add([
			// 			'name'		=> $row['city'],
			// 			'state_id'	=> $state_id,
			// 		]);
			// 	}
			// }

			// $row['state_id'] 	= $state_id;
			// $row['city_id'] 	= $city_id;

			$insert_school_data = [
				'parent_id' 		  			=> $row['parent_id'] ?? 0,
				'site_id' 		  				=> $row['site_id'] ?? 0,
				'name' 				  			=> trim($row['school_name']),
				'site_code' 		  			=> "-import-" . uniqid(),
				'site_type' 		  			=> $row['site_type'] ?? 1,
				'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
				'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
				'timezone' 			  			=> $site_info['timezone'] ?? '',
				'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
				'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
				'email_alert' 		  			=> $site_info['email_alert'] ?? '',
				'address' 			  			=> $row['address'] ?? '',
				'landmark' 			  			=> $row['landmark'] ?? '',
				'pincode' 			  			=> trim($row['zipcode']) ?? '',
				'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
				'country_code' 		  			=> $site_info['country_code'] ?? '',
				'currency_code' 	  			=> $site_info['currency_code'] ?? '',
				'country_id' 			  		=> $row['country_id'] ?? 0,
				'state_id' 			  			=> $row['state_id'] ?? 0,
				'city_id' 			  			=> $row['city_id'] ?? 0,
				'base_price' 		  			=> $site_info['base_price'] ?? 0,
				'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
				'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
				'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
				'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
				'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
				'black_white_price' 	  		=> $site_info['black_white_price'] ?? 0,
				'black_white_price_per_page' 	=> $site_info['black_white_price_per_page'] ?? 0,
				'tax' 				  			=> $site_info['tax'] ?? '',
				'tax_text' 			  			=> $site_info['tax_text'] ?? '',
				'owner_name' 	      			=> !empty($row['owner_name']) ? trim($row['owner_name']) : '',
				'authorized_person'   			=> !empty($row['authorized_person']) ? trim($row['authorized_person']) : '',
				'owner_email' 		  			=> $row['email'] ?? '',
				'owner_mobile' 	      			=> $row['mobile'] ?? '',
				'alternate_authorized_person'   => $row['alternate_authorized_person'] ?? '',
				'alternate_owner_email' 		=> $row['alternate_email'] ?? '',
				'alternate_owner_mobile' 	    => $row['alternate_mobile'] ?? '',
				'designation' 			  		=> $row['designation'] ?? '',
				'status' 			  			=> 1,
				'verified' 			  			=> 0,
				'tag'							=> 'unverified',
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
			];

			$school_id = $this->school_model->add($insert_school_data);

			if (!empty($school_id)) {
				$this->school_model->edit($school_id, [
					'site_code' => get_site_code_slug(trim($row['school_name'])) . "-" . $school_id
				]);
			}
			// break;
		}
	}

	public function removeEventRank() {
		$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
		$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
		$this->load->library('Redis_lib', 'redis_lib');

		// TEACHER RANKING AT CITY LEVEL

		// $rows = $this->db->query("SELECT `city_id`
		// 	FROM `teacher_rank_city`
		// 	WHERE `event_id` = '21' AND `_deleted` = '0'"
		// )->result_array();

		// // pr($rows,1);die;

		// foreach ($rows as $key =>$row) {
		// 	if (!empty($row['city_id']) && !empty($rank_key = vsprintf('live_teacher_%s_ranks_%s_%s_%s_%s', [
		// 		'city',
		// 		(ENVIRONMENT === 'production' ? 'live' : 'test'),
		// 		21,
		// 		4,
		// 		$row['city_id'],
		// 	]))) {
		// 		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);
		// 	}
		// 	// die;
		// }



		// TEACHER RANKING AT SCHOOL LEVEL

		// $rows = $this->db->query("SELECT `school_id`
		// 	FROM `teacher_rank_school`
		// 	WHERE `event_id` = '21' AND `_deleted` = '0' AND `event_challenge_school_id` = '4'"
		// )->result_array();

		// // pr($rows,1);die;

		// foreach ($rows as $key =>$row) {
		// 	if (!empty($rank_key = vsprintf('live_teacher_%s_ranks_%s_%s_%s_%s', [
		// 		'school',
		// 		(ENVIRONMENT === 'production' ? 'live' : 'test'),
		// 		21,
		// 		4,
		// 		$row['school_id'],
		// 	]))) {
		// 		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);
		// 	}
		// 	// die;
		// }

		// SCHOOL RANKING AT CITY LEVEL

		// $rows = $this->db->query("SELECT `city_id`
		// 	FROM `school_rank_city`
		// 	WHERE `event_id` = '21' AND `_deleted` = '0' AND `event_challenge_city_id` = '3'"
		// )->result_array();

		// // pr($rows,1);die;

		// foreach ($rows as $key =>$row) {
		// 	if (!empty($row['city_id']) && !empty($rank_key = vsprintf('live_school_%s_ranks_%s_%s_%s_%s', [
		// 		'city',
		// 		(ENVIRONMENT === 'production' ? 'live' : 'test'),
		// 		21,
		// 		3,
		// 		$row['city_id'],
		// 	]))) {
		// 		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);
		// 	}
		// 	// if ($key == 1) {

		// 	// 	die;
		// 	// }
		// }
	}
}
