<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventTaskAlert {
	public function eventCertificateRetrospective($event_id = 0) {
		if (empty($event_id)) return;

		$event_id = (int)$event_id;

		$results = $this->db->query("
			SELECT `event_id`, max(`order_id`) as order_id, `book_id`, sum(`quantity`) as sold
			FROM `event_order`
			join `order` on `order`.id = `order_id`
			WHERE `event_id` = '" . $event_id . "' AND `event_order`.`_deleted` = '0'
			AND `order`.`_deleted` = 0
			AND `order`.`status` NOT IN (0,91,92)
			AND `book_id` NOT IN (SELECT `book_id`
			FROM `certificates`
			WHERE `event_id` = '" . (int)$event_id . "'  AND `_deleted` = '0')
			GROUP BY `book_id`"
		)->result_array();

		$this->load->library('GenericCertificate_lib');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		foreach ($results as $result) {
			$this->genericcertificate_lib->createCertificate($result['order_id'], false);

			if (!empty($certficates = $this->certificate_model->get_all([
				'event_id'	 => 0,
				'book_id'	 => $result['book_id']
			])['rows'] ?? [])) {
				$this->db->where_in('id', array_column($certficates, 'id'));
				$this->db->update('certificates',  [
					'_deleted'		=> 1,
					'date_deleted'	=> date('Y-m-d H:i:s'),
				]);
			}
		}
	}

	public function eventCertificateMessageRetrospective($event_id = 0) {
		if (empty($event_id)) return;

		$results = $this->db->query("
			SELECT (SELECT id
			FROM cron
			WHERE code = concat('genericCertificateCreatedCron_', certificates.id)
			) as cert
			, certificates.id as certificate_id
			FROM certificates
			WHERE event_id = '" . (int)$event_id . "'
			HAVING cert IS NULL
			ORDER BY id ASC"
		)->result_array();

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		foreach ($results as $result) {
			if (empty($certificate_info = $this->certificate_model->get($result['certificate_id']))) return;

			if (!empty($this->cron_model->getByCode(sprintf('genericCertificateCreatedCron_%s', $result['certificate_id'])))) return;

			$certificate_template_info = $this->certificate_template_model->get($certificate_info['certificate_template_id']);

			$this->cron_model->add([
				'code'			=> sprintf('genericCertificateCreatedCron_%s', $result['certificate_id']),
				'action'		=> 'alert_model->genericCertificateCreatedCron',
				'data'			=> [$result['certificate_id'], ($certificate_template_info['book_sold'] ?? 0), null],
				'site_id'		=> 1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 1 : 1))),
			]);
		}
	}

	public function eventParticipationCertificate ($event_id = 0, $cert_temp_id = 0, $date_added = '', $type = 'all') {
		if (empty($event_id) || empty($date_added)) return;

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if (empty($template_info = $this->certificate_template_model->get($cert_temp_id))) return;

		if (strtolower($type) == 'sold') {
			$rows = $this->db->query("
				SELECT `event_id`, `book_id`, book.user_id as uid
				FROM `event_order`
				join `order` on `order`.id = `order_id`
				join book ON book.id = event_order.book_id
				WHERE `event_id` = " . (int)$event_id . " AND `event_order`.`_deleted` = '0'
				AND `order`.`_deleted` = 0
				AND `order`.`status` NOT IN (0,91,92)
				AND `book`.user_id NOT IN (SELECT `user_id` FROM `certificates` WHERE `event_id` = " . (int)$event_id . "  AND `_deleted` = '0' AND `certificate_template_id` = " . (int)$cert_temp_id . ")
				GROUP BY book.`user_id`
			")->result_array();

		} else if (strtolower($type) == 'pns') {
			$rows = $this->db->query("
				SELECT event_user.event_id, event_user.user_id as uid, concat(users.first_name, ' ', users.last_name) as name,
				users.site_id, users.location
				from event_user
				join users on users.id = event_user.user_id
				join site On site.id = users.site_id
				where event_id = " . (int)$event_id . "
				and event_user._deleted = 0
				and users._deleted = 0
				and users.id not in (select user_id from certificates where event_id = " . (int)$event_id . " and _deleted = 0)
				and users.id IN (select book.user_id from event_book join book on book.id = event_book.book_id where event_id = " . (int)$event_id . " and event_book._deleted = 0 and book._deleted = 0)
				AND site.site_type != 7
				AND users.site_id NOT IN (1,2,721,727)
			")->result_array();
		} else if (strtolower($type) == 'both') {
			$rows = $this->db->query("
				SELECT event_user.event_id, event_user.user_id as uid, concat(users.first_name, ' ', users.last_name) as name,
				users.site_id, users.location
				from event_user
				join users on users.id = event_user.user_id
				join site On site.id = users.site_id
				where event_id = " . (int)$event_id . "
				and event_user._deleted = 0
				and users._deleted = 0
				and users.id not in (select user_id from certificates where event_id = " . (int)$event_id . " and _deleted = 0 and `certificate_template_id` = " . (int)$cert_temp_id . ")
				and users.id IN (select book.user_id from event_book join book on book.id = event_book.book_id where event_id = " . (int)$event_id . " and event_book._deleted = 0 and book._deleted = 0)
				AND site.site_type != 7
				AND users.site_id NOT IN (1,2,721,727)
			")->result_array();
		} else {
			return;
		}


		foreach ($rows as $row) {
			$cert_info = $this->certificate_model->get_all([
				'event_id'		=> $event_id,
				'user_id'		=> $row['uid'],
				'achievement'	=> 2
			])['rows'][0] ?? '';

			if (empty($cert_info)) {
				if (empty($this->certificate_model->get_all([
					'event_id'		=> $event_id,
					'book_id'		=> 0,
					'user_id'		=> $row['uid']
				])['rows'][0] ?? '')) {
					$author_info 		= $this->student_model->get($row['uid']);
					$certificate_key 	= sprintf('participation_cert_user_%s_%s', $row['uid'], $event_id);

					$certificate_id = $this->certificate_model->add([
						'site_id'					=> $author_info['site_id'] ?? 1,
						'event_id'					=> $event_id,
						'book_id'					=> 0,
						'user_id'					=> $row['uid'],
						'type'						=> 'participation_cert',
						'achievement'				=> $template_info['achievement'] ?? 0,
						'certificate_template_id'	=> $template_info['id'] ?? 0,
						'unique_id'					=> $template_info['id'] ?? 0,
						'name'						=> $template_info['name'] ?? 0,
						'image'						=> $certificate_key,
					]);

					if (!empty($certificate_id)) {
						$unique_id = 'BB/' . sprintf('%08d', $certificate_id) . '/' . ($template_info['id'] ?? '12') ;
						$this->certificate_model->edit($certificate_id, [
							'unique_id' 	=> $unique_id,
							'date_added' 	=> $date_added
						]);
					}
				}
			}
		}
	}

	public function enrolAuthorBookDetailsInEvent($data = []) {
		if (empty($data)) return;

		$event_id = (int)$data['event_id'];

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventTemplate_model', 'event_template_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');

		if (empty($event_info = $this->event_model->get($event_id))) return;

		$not_event_ids = $data['not_event_ids'] ?? [];

		$where = "
			book_version.date_added >= '{$event_info['start_date']}'
			AND book_version.date_added <= '{$event_info['book_writing_end_date']}'
			AND book._deleted = '0'
			AND book.status = '1'
			AND site.country_code = '{$event_info['country_code']}'
		";

		if (!empty($data['is_buyer'])) {
			$where .= " AND users.source NOT LIKE '%buyer%'";
		}

		if (!empty($data['is_reviewer'])) {
			$where .= " AND users.source NOT LIKE '%reviewer%'";
		}

		$sql = "
			SELECT
				book.id as book_id,
				book.name as book_name,
				book.author_name as author_name,
				book_version.date_added
			FROM book
			JOIN users ON users.id = book.user_id
			JOIN book_version ON book_version.book_id = book.id AND book_version.version = 1
			JOIN site ON site.id = users.site_id
			WHERE $where
			AND users.id NOT IN (
				SELECT user_id
				FROM event_user
				WHERE _deleted = 0
				AND event_id IN ({$not_event_ids})
			)
			AND book.id NOT IN (
				SELECT book_id
				FROM event_book
				WHERE _deleted = 0
			)
			ORDER BY book_version.date_added DESC
		";

		$rows = $this->db->query($sql)->result_array();

		foreach ($rows as $key =>$row) {
			$book_info = $this->book_model->get($row['book_id']);

			if (!empty($book_info) && empty($this->event_book_model->get_all([
				'book_id'		=> $row['book_id']
			])['rows'][0] ?? '')) {
				if (!empty($event_book_id = $this->event_book_model->add([
					'event_id'		=> $event_id,
					'book_id'		=> $row['book_id']
				]))) {
					$first_date_published = $this->book_version_model->get_all([
						'version' => 1,
						'book_id' => $row['book_id'],
					])['rows'][0]['date_added'] ?? date('Y-m-d H:i:s');

					$this->event_book_model->edit($event_book_id, [
						'date_added'	=> $first_date_published
					]);

					if (!empty($template_info = $this->event_template_model->getByTemplateId($event_id, 'force_enrol_book'))) {
						$this->cron_model->add([
							'code'			=> sprintf('eventForceEnrolBookCron_%s_%s', $event_id, $book_info['id']),
							'action'		=> 'alert_model->eventForceEnrolBookCron',
							'data'			=> [$event_id, $book_info['id']],
							'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes',
								ENVIRONMENT === 'production' ? 30 : 1
							))),
						]);
					}

					if (empty($this->event_user_model->get_all([
						'event_id'		=> $event_id,
						'user_id'		=> $book_info['user_id']
					])['rows'][0] ?? '')) {
						$this->event_user_model->add([
							'event_id'		=> $event_id,
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
								if (empty($this->event_order_model->get_all([
									'event_id'		=> $event_id,
									'book_id'		=> $book_info['id'],
									'order_id'		=> $order_info['id']
								])['rows'][0] ?? '')) {
									$event_order_id = $this->event_order_model->add([
										'event_id'		=> $event_id,
										'order_id'		=> $order_info['id'],
										'book_id'		=> $book_info['id'],
										'quantity'		=> $product['quantity']
									]);

									$this->event_order_model->edit($event_order_id, [
										'date_added'	=> $order_info['date_added']
									]);
								}

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

					if (!empty($data['self_test'])) {
						die;
					}
				}

			}
		}
	}

	public function generateLeagueCertificate($data = []) {
		log_kb([
			'generateLeagueCertificate' => $data
		]);

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if (empty($data) || empty($data['event_id']) || empty($data['challenge_id']) || empty($data['type']) || empty($data['limit']) || empty($data['certificate_template_id'])) return;

		$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

		$ranks = [];

		if (file_exists($model_file_path)) {
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), sprintf('ranking_%s_model', strtolower($data['type'])));

			$model_name = sprintf('ranking_%s_model', strtolower($data['type']));

			if (!empty($data['is_rank'])) {
				$ranks = $this->{$model_name}->get_all([
					'event_id' 		=> $data['event_id'],
					'challenge_id' 	=> $data['challenge_id'],
					'country_id' 	=> $data['country_id'] ?? 0,
					'state_id' 		=> $data['state_id'] ?? 0,
					'city_id' 		=> $data['city_id'] ?? 0,
					'sort'			=> sprintf('user_rank_%s.rank', strtolower($data['type'])),
					'order'			=> 'ASC',
					'start'			=> 0,
					'limit' 		=> $data['limit'] ?? 50,
				])['rows'] ?? [];
			} else {
				$ranks = $this->{$model_name}->get_all([
					'event_id' 		=> $data['event_id'],
					'challenge_id' 	=> $data['challenge_id'],
					'country_id' 	=> $data['country_id'] ?? 0,
					'state_id' 		=> $data['state_id'] ?? 0,
					'city_id' 		=> $data['city_id'] ?? 0,
					'start'			=> 0,
					'limit' 		=> $data['limit'] ?? 50,
				])['rows'] ?? [];
			}

			log_kb([
				'generateLeagueCertificate::ranks' => $ranks
			]);

			if (empty($ranks)) return;

			if (empty($template_info = $this->certificate_template_model->get($data['certificate_template_id']))) return;

			foreach ($ranks as $key => $rank) {
				if (empty($certificate_info = $this->certificate_model->get_all([
					'book_id' 					=> $rank['book_id'],
					'certificate_template_id' 	=> $template_info['id'],
				])['rows'] ?? [])) {
					$certificate_key = sprintf('%s_user_%s_%s', $template_info['type'], $rank['user_id'], $rank['book_id']);

					$this->certificate_model->add([
						'site_id'					=> 1,
						'event_id'					=> $data['event_id'],
						'book_id'					=> $rank['book_id'],
						'user_id'					=> $rank['user_id'],
						'rank'						=> !empty($rank['rank']) ? $rank['rank'] : ($key + 1),
						'type'						=> $template_info['type'],
						'certificate_template_id'	=> $template_info['id'],
						'achievement'				=> $template_info['achievement'],
						'unique_id'					=> $template_info['id'],
						'name'						=> $certificate_key,
						'image'						=> $certificate_key,
					]);

					if (!empty($data['self_test'])) {
						die;
					}
				}
			}
		}
	}

	public function sendLeagueMessage($data = []) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/Country_model', 'country_model');

		if (empty($data) || empty($data['league_id']) || empty($template_info = $this->league_template_model->get($data['league_id'])) || empty($template_info['event_id'])) return;

		log_kb([
			'sendLeagueMessage' => $template_info
		]);

		$rows = [];

		if (!empty($data['is_csv']) && !empty($template_info['csv'])) {
			$this->load->library('parsecsv');

			$this->parsecsv->auto(sprintf('assets/csv/%s', $template_info['csv']));
			$rows = $this->parsecsv->data;
		} else {
			$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

			if (file_exists($model_file_path)) {
				$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), sprintf('ranking_%s_model', strtolower($data['type'])));

				$model_name = sprintf('ranking_%s_model', strtolower($data['type']));

				if (!empty($data['is_rank'])) {
					$rows = $this->{$model_name}->get_all([
						'event_id' 		=> $template_info['event_id'],
						'challenge_id' 	=> $template_info['challenge_id'],
						'city_id' 		=> $data['city_id'] ?? 0,
						'state_id' 		=> $data['state_id'] ?? 0,
						'sort'			=> sprintf('user_rank_%s.rank', strtolower($data['type'])),
						'order'			=> 'ASC',
						'start'			=> $data['start'] ?? 0,
						'limit' 		=> $data['limit'] ?? 50,
					])['rows'] ?? [];
				} else {
					$rows = $this->{$model_name}->get_all([
						'event_id' 		=> $template_info['event_id'],
						'challenge_id' 	=> $template_info['challenge_id'],
						'city_id' 		=> $data['city_id'] ?? 0,
						'state_id' 		=> $data['state_id'] ?? 0,
						'start'			=> $data['start'] ?? 0,
						'limit' 		=> $data['limit'] ?? 50,
					])['rows'] ?? [];
				}
			}
		}

		log_kb([
			'sendLeagueMessage-rows' => $rows
		]);

		if (empty($rows)) return;

		$event_info 	= $this->event_model->get($template_info['event_id'] ?? 0);

		$cert_url = USER_URL . '/account/mycertificates';

		foreach ($rows as $key =>$row) {
			if(empty($author_info 	= $this->student_model->get($row['user_id']))) continue;

			$site_info 		= $this->site_model->get($author_info['site_id'] ?? 0);
			$city_info 		= $this->city_model->get($row['city_id'] ?? 0);
			$state_info 	= $this->state_model->get($row['state_id'] ?? 0);
			$country_info 	= $this->country_model->get($row['country_id'] ?? 0);

			if (!empty($template_info['certificate_template_id']) && !empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $template_info['certificate_template_id'],
			])['rows'][0] ?? '')) {
				$cert_url = 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/AuthorCertificate/' . $certificate_info['name'] . '.png';
			}

			if (!empty($code_info = $this->event_user_invite_code_model->get_all([
				'event_id' 	=> $row['event_id'],
				'user_id' 	=> $row['user_id'],
			])['rows'][0] ?? [])) {
				$code = $code_info['code'];
			} else {
				$password 	= uniqid();
				$code 		= sha1(md5(($data['user_id']) . $password . $this->config->item('password_salt') . $data['event_id']));

				$this->event_user_invite_code_model->add([
					'event_id'	=> $row['event_id'],
					'user_id'	=> $row['user_id'],
					'code'		=> $code,
				]);
			}

			// $invite_url = sprintf(USER_YAF_URL . 'addressrequest?uid=%s&code=%s&eid=%s',
			// $author_info['id'], $author_info['verification_code'], $template_info['event_id']);

			switch ($data['type']) {
				case 'state':
					$league_url =  $event_info['rank_url'] . 'state/' . $row['state_id'] . '/' . $event_info['slug'];
					break;

				case 'city':
					$league_url =  $event_info['rank_url'] . 'city/' . $row['city_id'] . '/'  . $event_info['slug'];
					break;

				case 'country':
					$league_url =  $event_info['rank_url'] . $event_info['slug'];
					break;

				case 'general':
					$league_url =  $event_info['rank_url'] . $event_info['slug'];
					break;

				case 'genre':
					$league_url =  $event_info['rank_url'] . $event_info['slug'];
					break;

				default:
					$league_url = '';
					break;
			}


			$invite_url = sprintf('https://www.bribooks.com/imagerequest?uid=%s&code=%s&eid=%s', $author_info['id'], $code, $row['event_id']);

			$student_url = sprintf('https://www.yaf.bribooks.com/ae/2024/dashboard');

			$variables = [
				'first_name'	  		=> $author_info['first_name'],
				'author_name'	  		=> !empty($row['author_name']) ? $row['author_name'] : ($author_info['first_name'] . ' ' . $author_info['last_name']),
				'book_name'	  			=> $row['book_name'],
				'rank'	  				=> !empty($row['rank']) ? $row['rank'] : ($key + 1),
				'student_url'			=> $student_url,
				'url'					=> $invite_url,
				'cert_url'				=> $cert_url,
				'school_name'			=> $site_info['name'] ?? '',
				'city'					=> $city_info['name'] ?? '',
				'state'					=> $state_info['name'] ?? '',
				'country'				=> $country_info['name'] ?? '',
				'league_url'			=> $league_url,
			];

			$subject = self::formatCommonEmailSubject($template_info['subject'], $variables) ?? '';

			$content = self::formatCommonEmailContent($template_info['body'], $variables) ?? '';

			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			$attachment = [];

			if (!empty($data['self_check'])) {
				$email  = '';
				$mobile = '917303234240';
				$cc 	= '';
			} else {
				$email  = $author_info['email'];
				$mobile = $author_info['mobile'];
				$cc 	= 'communication@bribooks.com';
			}

			if (!empty($subject) && !empty($content)) {
				self::email(
					$email,
					$subject,
					$message,
					[],
					[$cc],
					$attachment
				);
			}

			if (!empty($template_info['whatsapp_template_id'])) {
				$variables['cert_url'] 	= 'https://www.bribooks.com/account/mycertificates?active=league';

				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> $template_info['whatsapp_template_id'],
						'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], $variables),
					]
				);
			}

			if (!empty($data['invite_user_entry'])) {
				if (empty($this->db->get_where('school_details_nyaf_invites', [
					'event_id' 	=> $template_info['event_id'],
					'user_id' 	=> $author_info['id'],
					'_deleted' 	=> 0,
				])->row_array())) {
					$this->school_details_invite_model->add([
						'event_id' 	=> $template_info['event_id'],
						'user_id' 	=> $author_info['id'],
						'site_id' 	=> $author_info['site_id'] ?? 0,
						'book_id' 	=> $row['book_id'],
						'book_sold' => $row['score'] ?? 0,
						'book_rank' => $row['rank'] ?? 0,
					]);
				}
			}

			if (!empty($data['award_user_entry'])) {
				$this->load->model('user/UserAwardAddress_model', 'user_award_address_model');

				$address_info = $this->user_award_address_model->get_all([
					'user_id'	=> (int)$row['user_id'],
					'event_id'	=> (int)$row['event_id'] ?? 0,
				])['rows'][0] ?? '';

				if (empty($address_info)) {
					$this->user_award_address_model->add([
						'user_id'	=> (int)$row['user_id'] ?? 0,
						'event_id'	=> (int)$row['event_id'] ?? 0,
						'status'	=> 0,
					]);
				}
			}

			if (!empty($data['self_test'])) {
				die;
			}
		}
	}

	public function generateEventLeagueRank($event_id = 0) {
		$event_id = (int)$event_id;

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('event/EventBookQualificationPending_model', 'event_book_qualification_pending_model');
		$this->load->library('Ranking_lib', 'ranking_lib');

		if (empty($event_info = $this->event_model->get($event_id))) return;

		$results = $this->db->query("
			SELECT
			event_order.event_id,
			event_order.book_id,
			event_order.order_id as o_id,
			MAX(event_order.order_id) as order_id,
			sum(event_order.quantity) as sold_count
			FROM `event_order`
			JOIN `order` ON `order`.id = event_order.order_id
			JOIN book ON book.id = event_order.book_id
			JOIN users ON users.id = book.user_id
			WHERE event_order.`_deleted` = '0'
			AND event_order.event_id = " . $event_id . "
			AND `order`._deleted = 0
			AND book._deleted = 0
			AND book.archived = 0
			AND book.status = 1
			AND users._deleted = 0
			AND `order`.status NOT IN (0,91,92,94)
			-- AND book.id NOT IN (SELECT book_id FROM `user_rank_general` WHERE `challenge_id`IN ('8','9') AND `_deleted` = '0')
			-- AND book.id NOT IN (SELECT book_id FROM `user_rank_state` WHERE event_id = 92 AND `_deleted` = '0')
			GROUP BY event_order.book_id
		")->result_array();

		foreach ($results as $key => $result) {
			if (!empty($book_info = $this->book_model->get($result['book_id'])) && !empty($author_info = $this->student_model->get($book_info['user_id']))) {

				// if (empty($this->site_model->get($author_info['site_id'])) || empty($author_info['city_id']) || empty($author_info['state_id'])) {
				// 	continue;
				// }

				$this->ranking_lib->updateRank($result['order_id']);
				// die;

				// $no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

				// if ($qualified_user_info = $this->event_book_qualification_pending_model->get_all([
				// 	'event_id'		=> (int)$event_info['id'],
				// 	'book_id'		=> (int)$book_info['id'],
				// ])['rows'][0] ?? []) {
				// 	$this->event_book_qualification_pending_model->edit($qualified_user_info['id'], [
				// 		'site_id'		=> (int)$author_info['site_id'] ?? 0,
				// 		'city_id'		=> (int)$author_info['city_id'] ?? 0,
				// 		'state_id'		=> (int)$author_info['state_id'] ?? 0,
				// 		'country_id'	=> (int)$author_info['country_id'] ?? 0,
				// 		'user_id'		=> (int)$book_info['user_id'] ?? 0,
				// 		'book_id'		=> (int)$book_info['id'] ?? 0,
				// 		'book_name'		=> $book_info['name'] ?? '',
				// 		'author_name'	=> $book_info['author_name'] ?? '',
				// 		'book_slug'		=> $book_info['slug'] ?? '',
				// 		'book_image'	=> $book_info['cover_image'] ?? '',
				// 		'author_image'	=> $book_info['author_image'] ?? '',
				// 		'score'			=> (int)$no_sold,
				// 	]);
				// } else {

				// 	$this->event_book_qualification_pending_model->add([
				// 		'event_id'		=> (int)$event_info['id'],
				// 		'site_id'		=> (int)$author_info['site_id'] ?? 0,
				// 		'city_id'		=> (int)$author_info['city_id'] ?? 0,
				// 		'state_id'		=> (int)$author_info['state_id'] ?? 0,
				// 		'country_id'	=> (int)$author_info['country_id'] ?? 0,
				// 		'user_id'		=> (int)$book_info['user_id'] ?? 0,
				// 		'book_id'		=> (int)$book_info['id'] ?? 0,
				// 		'book_name'		=> $book_info['name'] ?? '',
				// 		'author_name'	=> $book_info['author_name'] ?? '',
				// 		'book_slug'		=> $book_info['slug'] ?? '',
				// 		'book_image'	=> $book_info['cover_image'] ?? '',
				// 		'author_image'	=> $book_info['author_image'] ?? '',
				// 		'score'			=> (int)$no_sold,
				// 	]);
				// }

				// $products = $this->order_product_model->get_all([
				// 	'product_id'	 => $book_info['id']
				// ])['rows'] ?? [];

				// if (!empty($products)) {
				// 	log_kb([
				// 		'darwish-lastproduct'=> $products[0]['order_id'],
				// 	]);

				// 	if (!empty($products[0]['order_id'])) {
				// 		$this->ranking_lib->updateRank($products[0]['order_id']);
				// 	}
				// }
			}
			// die;
		}
	}

	public function sendUserEventInviteMessage ($template_id = 0) {
		if (empty($template_id)) return;

		$this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->library('parsecsv');

		// $rows = $this->certificate_model->get_all([
		// 	'certificate_template_id'	=> $template_id,
		// 	'order'						=> 'ASC',
		// ])['rows'];

		// $rows = $this->db->get_where('user_event_invitation', ['event_id' => 14])->result_array();


		$this->parsecsv->auto('assets/csv/league/event_user_invite.csv');
		$rows = $this->parsecsv->data;

		log_kb([
			'sendUserEventInviteMessage' => $rows
		]);

		foreach ($rows as $row) {
			if (empty($row['user_id']) || empty($user_info = $this->user_model->get($row['user_id'])) || empty($book_info = $this->book_model->get($row['book_id']))) return;

			// if (!empty($invite_info = $this->user_event_invitation_model->get_all([
			// 	'user_id'		=> $row['user_id'],
			// 	'event_id'		=> $row['event_id']
			// ])['rows'][0] ?? '')) {
			// 	$this->user_event_invitation_model->add([
			// 	'user_id'		=> $row['user_id'],
			// 	'event_id'		=> $row['event_id']
			// 	]);
			// };

			$reject_url = sprintf(SC_USER_ADDRESS_URL . 'india/2024/response?uid=%s&eid=%s&res=%s',
				$row['user_id'],
				$row['event_id'],
				'no'
			);

			$accept_url = sprintf(SC_USER_ADDRESS_URL . 'india/2024/response?uid=%s&eid=%s&res=%s',
				$row['user_id'],
				$row['event_id'],
				'yes'
			);

			$subject = 'Gentle Reminder: Congratulations, ' . ucwords($user_info['first_name'] . ' ' . $user_info['last_name']) . '! Invitation to the National Awards and Exhibition Ceremony';

			$message			= $this->load->view('common/mail/part/user_event_invitation', [
				'author_name' 	=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
				'book_name' 	=> $book_info['name'],
				'reject_url' 	=> $reject_url,
				'accept_url' 	=> $accept_url
			], true);

			if (ENVIRONMENT == 'production') {
				$email 	= $user_info['email'];
				$mobile = $user_info['mobile'];
			}

			!empty($email) && self::email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			);

			$url_parameter = sprintf('%s&eid=%s&res=%s',
				$row['user_id'],
				$row['event_id'],
				'yes'
			);

			!empty($mobile) && self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> '579665604912707',
					'parameters'	=> [
						ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
						$book_info['name'],

					],
					'url_parameters'=> $url_parameter,
				]
			);

			// break;
		}
	}

	public function notifyPendingProfileUser($event_id = 0) {
		if (empty($event_info = $this->event_model->get($event_id))) return;

		$this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->library('parsecsv');

		$rows = $this->db->query("SELECT event_user.event_id, event_user.user_id, users.site_id, users.state_id,
		users.city_id, users.grade, users.section, users.verification_code,
		(SELECT book.user_id
		FROM event_book
		JOIN book ON book.id = event_book.book_id
		WHERE event_id = '" . $event_id . "'
		AND event_book._deleted = '0'
		AND book.user_id = users.id
		GROUP BY user_id) as publish_book
		FROM event_user
		JOIN users ON users.id = event_user.user_id
		WHERE event_id = '" . $event_id . "'
		AND event_user._deleted = '0'
		AND users._deleted = 0
		AND (users.state_id = 0 OR users.city_id = 0 OR users.grade = 0 OR (users.section IS NULL))
		HAVING publish_book IS NOT NULL")->result_array();

		foreach ($rows as $row) {
			if (empty($row['user_id']) || empty($user_info = $this->user_model->get($row['user_id'])) || empty($user_info['verification_code'])) return;

			$url = sprintf('https://www.bribooks.com/submitdetail?uid=%s&code=%s', $user_info['id'], $user_info['verification_code']);

			$subject = 'Urgent Reminder: Last Day to Complete Your NYAF India Profile';

			$message			= $this->load->view('common/mail/part/notify_pending_profile_user', [
				'author_name' 	=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
				'url' 			=> $url
			], true);

			$email 	= $user_info['email'];
			$mobile = $user_info['mobile'];

			!empty($email) && self::email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			);

			// break;
		}
	}

	public function removeLeagueUser($event_id = 0, $challenge_id = 0, $self_test = 0) {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('ranking/RankingSchool_model', 'ranking_school_model');
		$this->load->model('ranking/RankingGeneral_model', 'ranking_general_model');
		$this->load->model('ranking/RankingCountry_model', 'ranking_country_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('event/EventBookQualificationPending_model', 'event_book_qualification_pending_model');
		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->load->library('Redis_lib', 'redis_lib');

		if (empty($challenge_id)) return;

		if (empty($event_info = $this->event_model->get($event_id))) return;

		$results = $this->ranking_country_model->get_all([
			'event_id'		=> (int)$event_info['id'],
			'challenge_id'	=> $challenge_id,
		])['rows'] ?? [];

		foreach ($results as $key => $rank_info) {

			if (empty($rank_info)) continue;

			$rank_key  = vsprintf('live_author_country_ranks_%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				$event_id,
				$challenge_id,
				0,
			]);

			$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
			$this->ranking_country_model->delete($rank_info['id']);

			if (!empty($self_test)) {
				die;
			}
		}
	}

	public function sendInvitePass($event_id = 0) {
		if (empty($event_info = $this->event_model->get($event_id))) return;

		if ($event_id == 14) {
			$temp_id 	= '1673063210088831';
			$subject 	= 'Your Passes for the Summer Book Writing Festival Awards & Exhibition';
			$view_file 	= 'invite_pass_sbwf';
		} else {
			$subject 	= 'Your Exclusive Passes for National Young Authors’ Fair Awards and Exhibition Ceremony Are Ready';
			$view_file 	= 'invite_pass_nyaf';
			$temp_id 	= '440216169116972';
		}

		$rows = $this->db->query("SELECT *
		FROM `user_details_nyaf_guest`
		WHERE `event_id` = '" . $event_id . "' AND `_deleted` = '0' AND `book_id` != '0'")->result_array();

		foreach ($rows as $row) {
			if (empty($row['user_id']) || empty($user_info = $this->user_model->get($row['user_id']))) return;

			$message			= $this->load->view('common/mail/part/' . $view_file . '', [
				'author_name' 	=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
			], true);

			if (ENVIRONMENT == 'production') {
				$email 	= $user_info['email'];
				$mobile = $user_info['mobile'];
			}

			!empty($email) && self::email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				[FCPATH . 'uploads/eventpass/pdfs/entry_pass_' . $row['code'] . '.pdf']
			);

			// break;
		}
	}

	public function sendSchoolInvitePass($event_id = 0) {
		if (empty($event_info = $this->event_model->get($event_id))) return;

		$rows = $this->db->query("SELECT *
		FROM `school_details_nyaf_guest`
		WHERE `event_id` = '" . $event_id . "'")->result_array();

		foreach ($rows as $row) {
			if (empty($row['site_id']) || empty($site_info = $this->site_model->get($row['site_id']))) return;

			$message			= $this->load->view('common/mail/part/school_invite_pass_nyaf', [
				'name' 	=> ucwords($site_info['authorized_person']),
			], true);

			$email 	= $site_info['owner_email'];
			$mobile = $site_info['owner_mobile'];

			$subject 	= 'Your Exclusive Passes for National Young Authors’ Fair Awards and Exhibition Ceremony Are Ready';

			!empty($email) && self::email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				[FCPATH . 'uploads/eventpass/pdfs/school_entry_pass_' . $row['code'] . '.pdf']
			);

			!empty($mobile) && self::_sendWhatsappDocument(
				$mobile,
				[
					'template'		=> '440216169116972',
					'parameters'	=> [$site_info['authorized_person']],
					'document'	=> [
						'name'	=> 'InvitePass.pdf',
						'link'	=> (FCPATH . 'uploads/eventpass/pdfs/school_entry_pass_' . $row['code'] . '.pdf')
					]
				]
			);

			// break;
		}
	}

	public function sendTeacherInvitePass($event_id = 0) {
		if (empty($event_info = $this->event_model->get($event_id))) return;

		$rows = $this->db->query("SELECT *
		FROM `user_details_nyaf_guest`
		WHERE `event_id` = '" . $event_id . "' AND `_deleted` = '0' AND `book_id` = '0'")->result_array();

		foreach ($rows as $row) {
			if (empty($row['user_id']) || empty($user_info = $this->user_model->get($row['user_id']))) return;

			$message			= $this->load->view('common/mail/part/school_invite_pass_nyaf', [
				'name' 	=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
			], true);

			$email 	= $user_info['email'];
			$mobile = $user_info['mobile'];

			$subject 	= 'Your Exclusive Passes for National Young Authors’ Fair Awards and Exhibition Ceremony Are Ready';

			!empty($email) && self::email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				[FCPATH . 'uploads/eventpass/pdfs/teacher_entry_pass_' . $row['code'] . '.pdf']
			);

			!empty($mobile) && self::_sendWhatsappDocument(
				$mobile,
				[
					'template'		=> '440216169116972',
					'parameters'	=> [ucwords($user_info['first_name'] . ' ' . $user_info['last_name'])],
					'document'	=> [
						'name'	=> 'InvitePass.pdf',
						'link'	=> (FCPATH . 'uploads/eventpass/pdfs/teacher_entry_pass_' . $row['code'] . '.pdf')
					]
				]
			);

			// break;
		}
	}

	public function generateEventJuryBookCert($event_id = 0, $challenge_id = 0, $type = '', $cert_id = 0, $limit = 10) {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/EventJuryBook_model', 'event_jury_book_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if (empty($type) || empty($event_info = $this->event_model->get($event_id))) return;

		if (empty($template_info = $this->certificate_template_model->get($cert_id))) return;

		$rows = $this->event_jury_book_model->get_all([
			'type' 			=> $type,
			'event_id' 		=> $event_id,
			'challenge_id' 	=> $challenge_id,
			'start' 		=> 0,
			'limit' 		=> $limit,
			'sort'			=> 'event_jury_book.rank',
			'order'			=> 'ASC',
		])['rows'] ?? [];

		foreach ($rows as $key => $row) {
			if (empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $template_info['id'],
			])['rows'] ?? [])) {
				$certificate_key = sprintf('%s_user_%s_%s', $template_info['type'], $row['user_id'], $row['book_id']);

				$this->certificate_model->add([
					'site_id'					=> 1,
					'event_id'					=> $row['event_id'],
					'book_id'					=> $row['book_id'],
					'user_id'					=> $row['user_id'],
					'rank'						=> $row['rank'],
					'type'						=> $template_info['type'],
					'certificate_template_id'	=> $template_info['id'],
					'achievement'				=> $template_info['achievement'],
					'unique_id'					=> $template_info['id'],
					'name'						=> $certificate_key,
					'image'						=> $certificate_key,
				]);
			}
			// die;
		}
	}

	public function enrolAuthorDetailsInEvent($data = []) {
		log_kb([
			'enrolAuthorDetailsInEvent-data' => $data,
		]);

		if (empty($data) || empty($data['event_id']) || empty($data['date_added'])) return;

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/Bookstore_model', 'bookstore_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');

		if (empty($event_info = $this->event_model->get($data['event_id']))) return;

		$rows = $this->db->query("SELECT
		users.*, users.id as user_id
		FROM `users`
		LEFT JOIN site ON site.id = users.site_id
		WHERE users.`_deleted` = '0'
		AND users.`role_id` = '2'
		AND users.`date_added` > '" . $data['date_added'] . "'
		AND users.`date_added` < '" . $data['end_date_added'] . "'
		AND users.`location` LIKE '%india%'
		AND users.`source` NOT LIKE '%buyer%'
		AND users.`source` NOT LIKE '%reviewer%'
		AND site.country_code = 'in'
		AND users.id NOT IN (select user_id from event_user where event_id IN (" . $data['event_ids'] . ") and _deleted = 0)")->result_array();

		log_kb([
			'enrolAuthorDetailsInEvent-query' => $this->db->last_query(),
		]);

		foreach ($rows as $key =>$row) {
			if (empty($this->event_user_model->get_all([
				'event_id'		=> $event_info['id'],
				'user_id'		=> $row['user_id']
			])['rows'][0] ?? '')) {
				$this->event_user_model->add([
					'event_id'		=> $event_info['id'],
					'user_id'		=> $row['user_id']
				]);
			}

			$books = $this->bookstore_model->get_all([
				'user_id' => $row['user_id']
			])['rows'] ?? [];

			if (!empty($books)) {
				foreach ($books as $book) {
					if (empty($book_info = $this->book_model->get($book['book_id']))) continue;

					if (!empty($data['first_date_added']) && $book_info['date_added'] < $data['first_date_added']) {
						continue;
					}

					if (!empty($book_info) && empty($this->event_book_model->get_all([
						'book_id'		=> $book_info['id']
					])['rows'][0] ?? [])) {
						if (!empty($event_book_id = $this->event_book_model->add([
							'event_id'		=> $event_info['id'],
							'book_id'		=> $book_info['id']
						]))) {
							$first_date_published = $this->book_version_model->get_all([
								'version' 		=> 1,
								'book_id' 		=> $book_info['id'],
							])['rows'][0]['date_published'] ?? date('Y-m-d H:i:s');

							$this->event_book_model->edit($event_book_id, [
								'date_added'	=>$first_date_published
							]);

							if (!empty($products = $this->order_product_model->get_all([
								'product_id'	 => $book_info['id']
							])['rows'] ?? [])) {
								$order_ids = [];
								foreach ($products as $product) {
									$order_info = $this->order_model->get($product['order_id']);

									if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {

										if (empty($this->event_order_model->get_all([
											'event_id'		=> $event_info['id'],
											'book_id'		=> $book_info['id'],
											'order_id'		=> $order_info['id']
										])['rows'][0] ?? '')) {
											$event_order_id = $this->event_order_model->add([
												'event_id'		=> $event_info['id'],
												'order_id'		=> $order_info['id'],
												'book_id'		=> $book_info['id'],
												'quantity'		=> $product['quantity']
											]);

											$this->event_order_model->edit($event_order_id, [
												'date_added'	=> $order_info['date_added']
											]);

										}

										$order_ids[] = $order_info['id'];
									}
								}

								if (!empty($order_ids)) {
									rsort($order_ids);
									if (!empty($data['is_rank'])) {
										$this->ranking_lib->updateRank($order_ids[0]);
									}

									if (!empty($data['is_cert'])) {
										$this->genericcertificate_lib->createCertificate($order_ids[0], false);
									}

									if (!empty($data['remove_old_cert'])) {
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

							if (!empty($data['self_order_test'])) {
								die;
							}
						}
					}

					if (!empty($data['self_user_test'])) {
						die;
					}
				}
			}
		}
	}

	public function buildCountryLeagueRank($event_id = 0, $self_test = '') {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->library('Ranking_lib', 'ranking_lib');

		if (empty($event_info = $this->event_model->get($event_id))) return;

		$results = $this->db->query("SELECT event_order.event_id, event_order.book_id, event_order.order_id as o_id, MAX(event_order.order_id) as order_id
		, sum(event_order.quantity) as sold_count
		FROM `event_order`
		JOIN `order` ON `order`.id = event_order.order_id
		JOIN book ON book.id = event_order.book_id
		JOIN users ON users.id = book.user_id
		WHERE event_order.`_deleted` = '0'
		AND event_order.event_id = " . $event_id . "
		AND `order`._deleted = 0
		AND book._deleted = 0
		AND book.archived = 0
		AND book.status = 1
		AND users._deleted = 0
		AND `order`.status NOT IN (0,91,92,94)
		GROUP BY event_order.book_id
		ORDER BY sold_count DESC")->result_array();

		foreach ($results as $key => $result) {
			if (!empty($book_info = $this->book_model->get($result['book_id'])) && !empty($author_info = $this->student_model->get($book_info['user_id']))) {

				$this->ranking_lib->updateRank($result['order_id']);

				if (!empty($self_test)) {
					die;
				}
			}
		}
	}

	public function insertLeagueRank($data = []) {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->library('Redis_lib');

		if (empty($data) || empty($data['event_id']) || empty($event_info = $this->event_model->get($data['event_id'])) || empty($data['challenge_id']) || empty($data['type'])) return;

		$rows = [];

		$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

		if (file_exists($model_file_path)) {
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), sprintf('ranking_%s_model', strtolower($data['type'])));

			$model_name = sprintf('ranking_%s_model', strtolower($data['type']));

			$filter = [
				'event_id' 		=> $data['event_id'],
				'challenge_id' 	=> $data['challenge_id'],
				'start'			=> 0,
				'limit'			=> $data['limit'] ?? 200,
			];

			if (!empty($data['city_id'])) {
				$filter['city_id'] = $data['city_id'] ?? 0;
			}

			if (!empty($data['state_id'])) {
				$filter['state_id'] = $data['state_id'] ?? 0;
			}

			$rows = $this->{$model_name}->get_all($filter)['rows'] ?? [];
		}

		log_kb([
			'sendLeagueMessage-rows' => $rows
		]);

		if (empty($rows)) return;

		if (empty($rank_key 	= self::_getLeagueRankKey($data['type'], $data['event_id'], $data['challenge_id'], $data['genre_id'] ?? 0))) return;

		$table_name = sprintf('user_rank_%s', strtolower($data['type']));

		foreach ($rows as $key =>$row) {
			if (empty($rank_info 	= $this->{$model_name}->get($row['id']))) continue;

			$rank = $this->redis_lib->getRank($rank_key, $rank_info['id']) + 1;

			log_kb([
				'sendLeagueMessage-rank_info' => $rank_info
			]);

			log_kb([
				'sendLeagueMessage-rank' => $rank
			]);

			if ($rank > 0) {
				$this->db->update($table_name, [
					'rank'			=> $rank
				], [
					'id'			=> (int)$rank_info['id']
				]);

				if (!empty($data['self_test'])) {
					die;
				}
			}
		}

	}

	private function _getLeagueRankKey($type = '', $event_id = 0, $challenge_id = 0, $genre_id = 0) {
		$rank_key = '';

		// if ($type == 'country') {
		// 	$rank_key = vsprintf('live_author_country_ranks_%s_%s_%s', [
		// 		(ENVIRONMENT === 'production' ? 'live' : 'test'),
		// 		$event_id,
		// 		$challenge_id,
		// 	]);
		// }

		$environment = (ENVIRONMENT === 'production' ? 'live' : 'test');

		switch ($type) {
			case 'daily':
				$rank_key = vsprintf('live_author_daily_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			case 'weekly':
				$rank_key = vsprintf('live_author_weekly_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			case 'general':
				$rank_key = vsprintf('live_author_general_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					0,
				]);
				break;

			case 'genre':
				$rank_key = vsprintf('live_author_genre_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					$genre_id,
				]);
				break;

			case 'city':
				$rank_key = vsprintf('live_author_city_ranks_%s_%s_%s', [
					(ENVIRONMENT === 'production' ? 'live' : 'test'),
					$event_id,
					$challenge_id,
				]);
				break;

			case 'state':
				$rank_key = vsprintf('live_author_state_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			case 'country':
				$rank_key = vsprintf('live_author_country_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			default:
				$rank_key = '';
				break;
		}


		return $rank_key;
	}

	public function eventForceEnrolBookMessageCron($data = []) {
		if (empty($data)) return;

		$event_id = (int)$data['event_id'];

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventTemplate_model', 'event_template_model');

		if (empty($event_info = $this->event_model->get($event_id))) return;

		if (empty($template_info = $this->event_template_model->getByTemplateId($event_id, 'force_enrol_book'))) return;

		$rows = $this->db->query("
			SELECT
			book.id as book_id,
			book.name as book_name,
			book.author_name as author_name,
			book_version.date_added
			FROM book
			JOIN users on users.id = book.user_id
			JOIN book_version ON book_version.book_id = book.id AND book_version.version = 1
			WHERE book_version.date_added >= '{$event_info['start_date']}'
			AND book_version.date_added <= '{$event_info['book_writing_end_date']}'
			AND book._deleted = '0'
			AND book.status = '1'
			AND users.id NOT IN (select user_id from event_user where _deleted = 0 and event_id IN ({$event_id}))
			AND book.id NOT IN (select book_id from event_book where _deleted = 0)
			ORDER BY book_version.date_added desc
		")->result_array();

		foreach ($rows as $key =>$row) {
			$book_info = $this->book_model->get($row['book_id']);

			if (!empty($book_info) && empty($this->event_book_model->get_all([
				'book_id'		=> $row['book_id']
			])['rows'][0] ?? '')) {
				$code = sprintf('eventForceEnrolBookCron_%s_%s', $event_id, $book_info['id']);

				if (empty($this->cron_model->getByCode($code))) {
					$this->cron_model->add([
						'code'			=> sprintf('eventForceEnrolBookCron_%s_%s', $event_id, $book_info['id']),
						'action'		=> 'alert_model->eventForceEnrolBookCron',
						'data'			=> [$event_id, $book_info['id']],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes',
							ENVIRONMENT === 'production' ? 30 : 1
						))),
					]);
				}

				if (!empty($data['self_test'])) {
					die;
				}
			}
		}
	}

	public function generateBookCertificate($data = []) {
		$this->load->library('GenericCertificate_lib');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$book_ids = $data['book_ids'] ?? [];
		$event_id = $data['event_id'] ?? 0;

		if (empty($book_ids)) return;

		$rows = $this->db->query("
			SELECT MAX(`order_id`) as order_id, `product_id`
			FROM `order_product`
			JOIN `order` ON `order`.id = order_id
			WHERE order_product.`product_id` IN ({$book_ids})
			AND order_product.`_deleted` = '0'
			AND `order`._deleted = 0
			AND `order`.status NOT IN (0,91,92)
			GROUP BY product_id
		")->result_array();

		foreach ($rows as $key =>$row) {
			if (empty($row['order_id']) || empty($row['product_id']) || empty($book_info = $this->book_model->get($row['product_id']))) continue;

			$this->genericcertificate_lib->createCertificate($row['order_id'], false);

			if (!empty($event_id) || !empty($event_info = $this->event_model->get($event_id))) {
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

			if (!empty($data['self_test'])) {
				die;
			}
		}
	}

	public function sendDailyBookReport($book_id = 0) {
		if (empty($book_id) || empty($book_info = $this->book_model->get($book_id))) return;

		if (empty($user_info = $this->user_model->get($book_info['user_id'] ?? 0)) || empty($user_info['email'])) return;

		$rows = $this->db->query("
			SELECT o.order_code, o.date_added, order_product.option_type
			, concat(users.first_name, ' ', users.last_name) as buyer_name
			, SUM(quantity) as quantity
			, o.status
			, users.state_id, users.city_id
			FROM `order_product`
			JOIN `order` as o ON o.`id` = order_id
			JOIN users ON users.id = o.user_id
			WHERE order_product.`product_id` = '{$book_id}'
			AND order_product.`_deleted` = '0'
			AND o.status NOT IN (0,91,92)
			AND o._deleted = 0
			GROUP BY order_id
			ORDER BY order_id;
		")->result_array();

		$data['title']			= _li('Daily Book Order Status Report');

		$data['content']		= $this->load->view('common/mail/part/daily_book_report', [
			'author_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'book_name'			=> $book_info['name'],
		], true);

		$message 				= $this->load->view('common/mail/templates/site/general', $data, true);

		// $attachment 			= FCPATH . 'uploads/pdfs/order_invoice_' . $info['id'] . '.pdf';
		// file_put_contents($attachment, self::_orderInvoice($id, true));

		$user_info['email'] && self::email(
			$user_info['email'],
			$data['title'],
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			// $attachment
		);

	}

	public function generateBookCertificateAndMedallion($data = []) {
		$this->load->library('GenericCertificate_lib');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');

		$this->load->model('medallion/Medallion_model', 'medallion_model');
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$this->load->model('medallion/MedallionAddress_model', 'medallion_address_model');

		$book_ids = !empty($data['book_ids']) ? explode(',', $data['book_ids']) : [];
		$event_id = $data['event_id'] ?? 0;

		if (empty($book_ids) || empty($event_info = $this->event_model->get($data['event_id']))) return;

		$in_certificate_templates = $this->certificate_template_model->get_all([
			'country_code'	=> 'IN',
			'event_id'		=> (int)$event_info['id'],
			'status'		=> 1,
			'has_rank'		=> 0,
			'sort'			=> 'certificate_template.book_sold',
			'order'			=> 'ASC',
		])['rows'] ?? [];

		$ge_certificate_templates = $this->certificate_template_model->get_all([
			'country_code'	=> 'GE',
			'event_id'		=> (int)$event_info['id'],
			'status'		=> 1,
			'has_rank'		=> 0,
			'sort'			=> 'certificate_template.book_sold',
			'order'			=> 'ASC',
		])['rows'] ?? [];


		foreach($book_ids as $book_id) {
			if (empty($book_info = $this->book_model->get($book_id)) ||
				empty($event_book_info = $this->event_book_model->get_all(['book_id' => (int)$book_id, 'event_id' => $event_info['id']])['rows'][0] ?? []) ||
				empty($author_info = $this->user_model->get($book_info['user_id']))
			) {
				continue;
			}

			$certificate_templates  = [];
			$certificate_templates	= strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? $in_certificate_templates : $ge_certificate_templates;

			if (empty($certificate_templates)) continue;

			$sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], ($book_info['id'] ?? 0));

			if (empty($sold) || $sold < 1) continue;

			foreach ($certificate_templates as $key => $template) {
				if ($template['achievement'] == 2) continue;

				if ($sold >= $template['book_sold']) {
					$certificate_key = sprintf('%s_user_%s_%s', $template['type'], $book_info['user_id'], $book_info['id']);

					$certificate_info = $this->certificate_model->get_all([
						'book_id'					=> $book_info['id'],
						'certificate_template_id'	=> $template['id'],
					])['rows'][0] ?? [];

					if (empty($certificate_info)) {
						$certificate_id = $this->certificate_model->add([
							'site_id'					=> $author_info['site_id'],
							'event_id'					=> $event_id,
							'book_id'					=> $book_info['id'],
							'user_id'					=> $book_info['user_id'],
							'type'						=> $template['type'],
							'certificate_template_id'	=> $template['id'],
							'achievement'				=> $template['achievement'],
							'unique_id'					=> $template['id'],
							'name'						=> $template['name'],
							'image'						=> $certificate_key,
						]);
					} else {
						$certificate_id = $certificate_info['id'];
					}

					$medallion_order_code = '';

					if (!empty($template['medallion_id'])) {
						if (empty($medallion_order_info = $this->medallion_order_model->get_all([
							'book_id'		=> $book_info['id'],
							'medallion_id'	=> $template['medallion_id']
						])['rows'][0] ?? [])) {
							$medallion_info = $this->medallion_model->get($template['medallion_id']);
							$address_info 	= $this->medallion_address_model->get_all([
								'user_id'	=> (int)$book_info['user_id']
							])['rows'][0] ?? [];

							$medallion_order_code = vsprintf('BBM-%s%s%s%s', [
								time(),
								$event_id,
								$template['medallion_id'],
								$book_info['id'],
							]);

							$author_currency_code = get_author_currency_code($book_info['user_id']);

							$this->medallion_order_model->add([
								'order_code'		=> $medallion_order_code,
								'event_id'			=> (int)$event_id,
								'address_id'		=> (int)($address_info['id'] ?? 0),
								'book_id'			=> (int)$book_info['id'],
								'user_id'			=> (int)$book_info['user_id'],
								'medallion_id'		=> (int)($template['medallion_id'] ?? 0),
								'weight'			=> (double)($medallion_info['weight'] ?? 0),
								'subtotal'			=> (double)apply_currency_exchange($medallion_info['price'] ?? 0, $author_currency_code),
								'shipping_cost'		=> (double)apply_currency_exchange($medallion_info['shipping_cost'] ?? 0, $author_currency_code),
								'total'				=> (double)apply_currency_exchange(($medallion_info['price'] ?? 0) + ($medallion_info['shipping_cost'] ?? 0), $author_currency_code),
								'currency_id'		=> (int)get_author_currency_id($book_info['user_id']),
								'currency_code'		=> $author_currency_code,
								'currency_symbol'	=> get_author_currency_symbol($book_info['user_id']),
							]);
						} else {
							$medallion_order_code = $medallion_order_info['order_code'];
						}
					}

					if (!empty($data['alert'])) {
						if (empty($certificate_id)) continue;
						if (empty($certificate_info = $this->certificate_model->get($certificate_id))) continue;

						if (!empty($this->cron_model->getByCode(sprintf('genericCertificateCreatedCron_%s', $certificate_id)))) continue;

						$this->cron_model->add([
							'code'			=> sprintf('genericCertificateCreatedCron_%s', $certificate_id),
							'action'		=> 'alert_model->genericCertificateCreatedCron',
							'data'			=> [$certificate_id, $sold, $medallion_order_code],
							'site_id'		=> 1,
							'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 1 : 1))),
						]);
					}
				}
			}
		}
	}

	public function createSchoolMedallionRestroSpective($data = []) {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('medallion/Medallion_model', 'medallion_model');
		$this->load->model('medallion/MedallionStockLog_model', 'medallion_stock_log_model');
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$this->load->model('medallion/MedallionAddress_model', 'medallion_address_model');
		$this->load->model('medallion/SchoolMedallionAddress_model', 'school_medallion_address_model');

		if (empty($data) || empty($data['event_id']) || empty($event_info = $this->event_model->get($data['event_id'])) || empty($event_info['school_medallion_ids'])) return;

		$medallion_ids  = explode(',', $event_info['school_medallion_ids']);

		if (empty($medallion_ids)) return;

		$publish_limit = $data['publish_limit'] ?? 1;

		$results = $this->db->query("
			SELECT event_book.`event_id`, users.site_id, count(event_book.book_id) as book_published
			FROM `event_book`
			JOIN book ON book.id = event_book.book_id
			JOIN users ON users.id = book.user_id
			WHERE event_book.`event_id` = '{$event_info['id']}'
			AND event_book.`_deleted` = '0'
			AND book._deleted =0
			AND book.archived = 0
			AND users._deleted =0
			AND users.site_id NOT IN (1,2,721,727,71588)
			GROUP BY users.site_id
			HAVING book_published >= {$publish_limit}
			ORDER BY book_published DESC"
		)->result_array();

		log_kb([
			'createSchoolMedallionRestroSpective::results' => $results
		]);

		foreach($results as $result){
			if (empty($school_info 	= $this->site_model->get($result['site_id'] ?? 0)) ||
				empty($user_info 		= $this->user_model->get_all([
					'site_id' 	=> $school_info['id'],
					'role_id'	=> 9
				])['rows'][0] ?? [])
			) {
				continue;
			}

			$no_published 	=  count(array_unique(array_column($this->event_book_model->get_all([
				'event_id'	=> $event_info['id'],
				'site_id'	=> $school_info['id'],
			])['rows'] ?? [], 'book_id')));

			log_kb([
				'createSchoolMedallionRestroSpective::site_id' => $school_info['id'],
				'createSchoolMedallionRestroSpective::no_published' => $no_published,
			]);

			foreach($medallion_ids as $medallion_id) {
				if (empty($medallion_info = $this->medallion_model->get($medallion_id))) continue;

				if (empty($medallion_info['min_published']) || $no_published < $medallion_info['min_published']) continue;
				if (!empty($medallion_info['max_published']) && $no_published > $medallion_info['max_published']) continue;

				if (empty($medallion_order_info = $this->medallion_order_model->get_all([
					'event_id'		=> $event_info['id'],
					'user_id'		=> $user_info['id'],
					'medallion_id'	=> $medallion_info['id']
				])['rows'][0] ?? [])) {
					$address_info 	= $this->medallion_address_model->get_all([
						'user_id'	=> (int)$user_info['id']
					])['rows'][0] ?? [];

					log_kb([
						'createSchoolMedallionRestroSpective::site_id' => $school_info['id'],
						'createSchoolMedallionRestroSpective::address_info' => $address_info,
					]);

					$medallion_order_code = vsprintf('BBSM-%s%s%s%s', [
						time(),
						$event_info['id'],
						$medallion_info['id'],
						$school_info['id'],
					]);

					$school_currency_code = $school_info['currency_code'];

					$this->medallion_order_model->add([
						'type'				=> 'school',
						'order_code'		=> $medallion_order_code,
						'event_id'			=> (int)$event_info['id'],
						'address_id'		=> (int)($address_info['id'] ?? 0),
						'user_id'			=> (int)$user_info['id'],
						'medallion_id'		=> (int)($medallion_info['id'] ?? 0),
						'pickup_location_id'=> 1,
						'status'			=> !empty($address_info['id']) ? 21 : 1,
						'weight'			=> (double)($medallion_info['weight'] ?? 0),
						'subtotal'			=> (double)apply_currency_exchange($medallion_info['price'] ?? 0, $school_currency_code),
						'shipping_cost'		=> (double)apply_currency_exchange($medallion_info['shipping_cost'] ?? 0, $school_currency_code),
						'total'				=> (double)apply_currency_exchange(($medallion_info['price'] ?? 0) + ($medallion_info['shipping_cost'] ?? 0), $school_currency_code),
						'currency_id'		=> (int)$school_info['country_id'] ?? 0,
						'currency_code'		=> $school_info['currency_code'],
						'currency_symbol'	=> get_currency_symbol($school_info['currency_code']),
					]);

					if (!empty($address_info)) {
						$orders = $this->medallion_order_model->get_all([
							'user_id'			=> $user_info['id'],
							'shipping_status'	=> 0,
							'ne_status'			=> [0, 4, 15, 91, 92, 93],
							'sort'				=> 'medallion_order.id',
							'order'				=> 'ASC',
						])['rows'] ?? [];

						if (empty($orders)) return;

						$parent_id = 0;

						$parent_order = array_filter($orders, function($item) {
							return !empty($item['parent_id']);
						});

						$parent_id = $parent_order['id'] ?? $orders[0]['id'] ?? 0;

						foreach ($orders as $order) {
							$this->medallion_order_model->edit($order['id'], [
								'parent_id'		=> $parent_id == $order['id'] ? 0 : $parent_id,
								'shipping_cost'	=> $parent_id == $order['id'] ? $order['shipping_cost'] : 0,
								'total'			=> $parent_id == $order['id'] ? (double)$order['total'] : (double)$order['subtotal'],
								'status'		=> 21,
								'address_id'	=> (int)$address_info['id'],
							]);

							if ($order['status'] == 1) {
								// reduce medallion stock
								$medallion_info = $this->medallion_model->get($order['medallion_id']);

								$this->medallion_model->edit($medallion_info['id'], [
									'quantity'	=> ($medallion_info['quantity'] - 1)
								]);

								$this->medallion_stock_log_model->add([
									'medallion_id'			=> (int)$order['medallion_id'],
									'medallion_order_id'	=> (int)$order['id'],
									'quantity'				=> $medallion_info['quantity'],
									'quantity_order'		=> 1,
								]);
							}
						}
					}

					if (!empty($data['self_test'])) {
						die;
					}
				}
			}
		}
	}

	public function sendLeagueInviteMessage($event_id = 0) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/Country_model', 'country_model');

		$this->load->library('parsecsv');

		if ($event_id == 99) {
			$this->parsecsv->auto('assets/csv/nyaf_user_invite_2026.csv');
			$rows = $this->parsecsv->data;
		} else if($event_id == 92) {
			$this->parsecsv->auto('assets/csv/sbwf_user_invite_2026.csv');
			$rows = $this->parsecsv->data;
		} else {
			return;
		}

		log_kb([
			'sendLeagueInviteMessage::rows' => $rows
		]);

		foreach ($rows as $key =>$row) {


			// $pdf_path = FCPATH . sprintf('uploads/eventpass/pdfs/user_entry_pass_%s.pdf', $row['invite_id']);
			$pdf_path = sprintf('https://cms.bribooks.com/uploads/eventpass/pdfs/user_entry_pass_%s.pdf', $row['invite_id']);

			log_kb([
				'sendLeagueInviteMessage::pdf_path' => $pdf_path
			]);


			$variables = [
				'author_name'	  		=> $row['author_name'],
			];


			if ($event_id == 99) {
				$subject_head = 'Your Entry Pass – National Awards & Exhibition';

				$body = '<p dir="ltr">Dear {author_name},</p>
					<p dir="ltr">Thank you for confirming your presence for the National Awards &amp; Exhibition.</p>
					<p dir="ltr">Please find your Entry Pass attached with this email. Kindly carry a copy of the pass (digital) while attending the event, as entry to the venue will be permitted only upon verification of the pass.</p>
					<h3 dir="ltr">Event Details</h3>
					<p dir="ltr">Location: Apparel House, Sector 44, Gurugram, Haryana<br> Date: 28th March, Saturday<br> Reporting Time: 2:00 PM</p>
					<h3 dir="ltr">Important Instructions</h3>
					<ul>
					<li dir="ltr" aria-level="1">
					<p dir="ltr" role="presentation">Please arrive at the venue at least 15 minutes prior to the reporting time to complete the entry formalities.</p>
					</li>
					<li dir="ltr" aria-level="1">
					<p dir="ltr" role="presentation">Ensure that you carry your Entry Pass and a valid ID proof.</p>
					</li>
					<li dir="ltr" aria-level="1">
					<p dir="ltr" role="presentation">Entry will be permitted strictly as per the confirmed attendee list.</p>
					</li>
					<li dir="ltr" aria-level="1">
					<p dir="ltr" role="presentation">For any queries related to the event, kindly write to support@bribooks.com before the event date.</p>
					</li>
					</ul>
					<p dir="ltr">We look forward to hosting you and celebrating your achievement.</p>
					<p><strong id="docs-internal-guid-2f5f3758-7fff-e7e4-c787-26074be10f15">Regards,<br>Team BriBooks<br>National Young Authors Fair 2025</strong></p>';
			} else if($event_id == 92) {
				$subject_head = 'Your Entry Pass – National Awards & Exhibition';

				$body = '<p dir="ltr">Dear {author_name},</p>
				<p dir="ltr">Thank you for confirming your presence for the National Awards &amp; Exhibition.</p>
				<p dir="ltr">Please find your Entry Pass attached with this email. Kindly carry a copy of the pass (digital) while attending the event, as entry to the venue will be permitted only upon verification of the pass.</p>
				<h3 dir="ltr">Event Details</h3>
				<p dir="ltr"> Location: Apparel House, Sector 44, Gurugram, Haryana<br> Date: 28th March, Saturday<br> Reporting Time: 9:30 AM</p>
				<h3 dir="ltr">Important Instructions</h3>
				<ul>
				<li dir="ltr" aria-level="1">
				<p dir="ltr" role="presentation">Please arrive at the venue at least 15 minutes prior to the reporting time to complete the entry formalities.</p>
				</li>
				<li dir="ltr" aria-level="1">
				<p dir="ltr" role="presentation">Ensure that you carry your Entry Pass and a valid ID proof.</p>
				</li>
				<li dir="ltr" aria-level="1">
				<p dir="ltr" role="presentation">Entry will be permitted strictly as per the confirmed attendee list.</p>
				</li>
				<li dir="ltr" aria-level="1">
				<p dir="ltr" role="presentation">For any queries related to the event, kindly write to support@bribooks.com before the event date.</p>
				</li>
				</ul>
				<p dir="ltr">We look forward to hosting you and celebrating your achievement.</p>
				<p><strong id="docs-internal-guid-3febaf10-7fff-68cf-971d-9aad831419f2">Regards,<br>Team BriBooks<br>Summer Book Writing Festival 2025</strong></p>';
			} else {
				continue;
			}

			$subject = self::formatCommonEmailSubject($subject_head, $variables) ?? '';

			$content = self::formatCommonEmailContent($body, $variables) ?? '';

			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			log_kb([
				'sendLeagueInviteMessage::data' => $data
			]);


			$attachment = [$pdf_path];

			$email  = '';
			$mobile = '917303234240';
			$cc 	= [];

			// $email  = $row['email'];
			// $mobile = $row['mobile'];
			// $cc 	= 'communication@bribooks.com';

			if (!empty($subject) && !empty($content)) {
				self::email(
					$email,
					$subject,
					$message,
					[],
					[$cc],
					$attachment
				);
			}

			// die;
		}
	}

	public function sendSchoolLeagueInviteMessage($event_id = 0) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/Country_model', 'country_model');

		$this->load->library('parsecsv');

		if ($event_id == 99) {
			$this->parsecsv->auto('assets/csv/nyaf_school_invite_2026.csv');
			$rows = $this->parsecsv->data;
		} else if($event_id == 92) {
			$this->parsecsv->auto('assets/csv/sbwf_school_invite_2026.csv');
			$rows = $this->parsecsv->data;
		} else {
			return;
		}

		log_kb([
			'sendSchoolLeagueInviteMessage::rows' => $rows
		]);

		foreach ($rows as $key =>$row) {


			// $pdf_path = FCPATH . sprintf('uploads/eventpass/pdfs/user_entry_pass_%s.pdf', $row['invite_id']);
			$pdf_path = sprintf('https://cms.bribooks.com/uploads/eventpass/pdfs/school_entry_pass_%s.pdf', $row['invite_id']);

			log_kb([
				'sendSchoolLeagueInviteMessage::pdf_path' => $pdf_path
			]);


			$variables = [
				'school_name'	  		=> $row['school_name'],
				'authorized_person'	  	=> $row['authorized_person'],
			];


			$subject_head = 'Entry Pass Confirmation | National Awards & Exhibition 2026';

			$body = '<p dir="ltr">Dear {authorized_person},</p>
			<p dir="ltr">Thank you for confirming your institution&rsquo;s representation at the National Awards &amp; Exhibition 2026.</p>
			<p dir="ltr">Please find the Entry Pass for {school_name} attached with this email. Kindly ensure that the attending representative carries this pass (digital or printed) at the venue, as entry will be permitted only upon verification.</p>
			<h3 dir="ltr">Event Details</h3>
			<p dir="ltr">Location: Apparel House, Sector 44, Gurugram, Haryana<br> Date: 28th March, Saturday<br>Reporting Time: 2:00 PM</p>
			<h3 dir="ltr">Important Instructions</h3>
			<ul>
			<li dir="ltr" aria-level="1">
			<p dir="ltr" role="presentation">Representatives are requested to arrive at the venue at least 15 minutes prior to the reporting time to complete entry formalities.</p>
			</li>
			<li dir="ltr" aria-level="1">
			<p dir="ltr" role="presentation">Kindly carry the Entry Pass and a valid identification document for verification at the venue.</p>
			</li>
			<li dir="ltr" aria-level="1">
			<p dir="ltr" role="presentation">Entry will be permitted strictly as per the confirmed attendee list.</p>
			</li>
			<li dir="ltr" aria-level="1">
			<p dir="ltr" role="presentation">For any queries related to the event, please write to support@bribooks.com before the event date.</p>
			</li>
			</ul>
			<p dir="ltr">We look forward to welcoming your institution and recognising its contribution to nurturing young authors.</p>
			<p><strong id="docs-internal-guid-3dc99f43-7fff-49ce-dbe3-a972c24ee9c7">Regards,<br>Team BriBooks</strong></p>';

			$subject = self::formatCommonEmailSubject($subject_head, $variables) ?? '';

			$content = self::formatCommonEmailContent($body, $variables) ?? '';

			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			log_kb([
				'sendLeagueInviteMessage::data' => $data
			]);


			$attachment = [$pdf_path];

			die;
		}
	}
}
