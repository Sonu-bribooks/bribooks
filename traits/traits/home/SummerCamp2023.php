<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('whatsapp');

trait SummerCamp2023
{
	use CommonWhatsapp;

	public function summerCampCertificates() {
		return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventUser_model', 'event_user_model');

		$order_products = $this->order_product_model->getBookIdsByEventIdNotInCertificates(SC_EVENT_ID);

		if(!empty($order_products)) {
			pr(count($order_products));
			// pr($order_products, 1);

			$chunk_size = (ENVIRONMENT === 'production') ? 100 : 10;

			$data = [];
			$count_certificate = 0;
			$count_medallion = 0;

			foreach(array_chunk($order_products, $chunk_size) as $order_product) {
				foreach ($order_product as $book_info) {
					$event_user_info = $this->event_user_model->getEventUserByUserId(SC_EVENT_ID, $book_info['user_id']);
					$certificate_info = []; // $this->certificate_model->getByUserId($book_info['user_id'], $book_info['book_id']);

					$data[$book_info['book_id']]['book_info'] = $book_info;
					$data[$book_info['book_id']]['event_user_info'] = $event_user_info;
					$data[$book_info['book_id']]['certificate_info'] = $certificate_info;

					if(empty($certificate_info)) {
						if(empty($cron_info = $this->cron_model->getByCode('genericMsgCertificateSCCron_' . $book_info['book_id']))) {
							$this->cron_model->add([
								'code'			=> 'createCertificateSC_' . $book_info['order_id'],
								'action'		=> 'alert_model->createCertificateSC',
								'data'			=> [$book_info['order_id']],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
							]);
							$this->cron_model->add([
								'code'			=> 'genericMsgCertificateSCCron_' . $book_info['book_id'],
								'action'		=> 'alert_model->genericMsgCertificateSCCron',
								'data'			=> [$book_info['book_id'], $book_info],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+5 minutes')),
							]);

							$count_certificate++;

							// pr('genericMsgCertificateSCCron');
							// pr($book_info);
						}
						if(($book_info['quantity'] >= 10) && empty($cron_info = $this->cron_model->getByCode('genericMsgMedallionSCCron_' . $book_info['book_id'])) && (strtotime($book_info['date_published']) <= strtotime(date('2023-06-15 23:59:59')))) {
							$this->cron_model->add([
								'code'			=> 'createMedallionOnBookSoldSC_' . $book_info['order_id'],
								'action'		=> 'alert_model->createMedallionOnBookSoldSC',
								'data'			=> [$book_info['order_id']],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
							]);
							$this->cron_model->add([
								'code'			=> 'genericMsgMedallionSCCron_' . $book_info['book_id'],
								'action'		=> 'alert_model->genericMsgMedallionSCCron',
								'data'			=> [$book_info['book_id'], $book_info],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+5 minutes')),
							]);

							$count_medallion++;

							// pr('genericMsgMedallionSCCron');
							// pr($book_info);
						}
					}
				}

				// die;
			}

			pr($count_certificate);
			pr($count_medallion);
			// pr($order_products, 1);
		}
	}

	public function summerCampSchoolPDF() {
		return;

		// $this->load->model('order/OrderProduct_model', 'order_product_model');
		// $sc_order_data = $this->order_product_model->getOrderProductQuantityByEventId(4, 176461);
		// pr($sc_order_data, 1);

		// $order_id = 2142;
		// self::createAwardsOnBookSoldSC($order_id);


		if(0) {
			$this->load->model('order/OrderProduct_model', 'order_product_model');
			$this->load->model('common/Cron_model', 'cron_model');

			$order_products = $this->order_product_model->getOrderQuantityByEventId(SC_EVENT_ID);

			pr($order_products, 1);
			$count = 0;
			foreach ($order_products as $key => $order_product) {
				if(empty($this->cron_model->getByCode('createCertificateSCCron_' . $order_product['order_id']))) {
					$this->cron_model->add([
						'code'			=> 'createCertificateSCCron_' . $order_product['order_id'],
						'action'		=> 'alert_model->createCertificateSCCron',
						'data'			=> [$order_product['order_id']],
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);

					pr($order_product, 1);
				}

				/*if(empty($this->cron_model->getByCode('createAwardsOnBookSoldSC_' . $order_product['order_id']))) {
					$this->cron_model->add([
						'code'			=> 'createAwardsOnBookSoldSC_' . $order_product['order_id'],
						'action'		=> 'alert_model->createAwardsOnBookSoldSC',
						'data'			=> [$order_product['order_id']],
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+2 minutes')),
					]);
				}*/

				/*if($order_product['quantity'] >= 10 && empty($this->cron_model->getByCode('createMedallionOnBookSoldSC_' . $order_product['order_id']))) {
					$this->cron_model->add([
						'code'			=> 'createMedallionOnBookSoldSC_' . $order_product['order_id'],
						'action'		=> 'alert_model->createMedallionOnBookSoldSC',
						'data'			=> [$order_product['order_id']],
						'site_id'		=> '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);

					$count++;
				}*/
			}

			pr('Count: ' . $count);
			pr($order_products, 1);

			return;
		}

		$dir = FCPATH . 'uploads/summercamp/school_nominations/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/summercamp/school_nominations/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->get_where('school_nominations', ['status' => 0])->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/School-Nominations-Letter-Head.jpg');
			$font_path = FCPATH . 'assets/global/fonts/MYRIADPRO-BOLD.OTF';
			$font_path_regular = FCPATH . 'assets/global/fonts/MYRIADPRO-REGULAR.OTF';

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';

				$image_name = $result['nomination_code'] . '.jpeg';

				$p = 'BriBooks in partnership with EducationWorld, invites your school ' . $result['school_name'] . ', to participate in the Summer Book Writing Festival with NDTV and Disney as media partners.';

				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 100) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 100) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 100) {
						$str3 .= ' ' . $school;
					}
				}

				$sn_length = strlen($result['school_name']);
				$p_length = strlen($p);

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/School-Nominations-Letter-Head.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$grey 		= imagecolorallocate($image, 110, 110, 110);

				imagettftext($image, 36, 0, 1160, 280, $darkgrey, $font_path, $result['nomination_code']);
				imagettftext($image, 38, 0, 108, 540, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 108, 605, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 108, 670, $darkgrey, $font_path_regular, $str3);
				}

				imagettftext($image, 36, 0, 370, 2188, $darkgrey, $font_path, $result['school_name']);

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				// pr($image, 1);
				self::_generateSCCertificate($image_name);

				$this->db->where('id', (int)$result['id']);
				$this->db->update('school_nominations', [
					'status'		=> 1,
					'date_added'	=> date('Y-m-d H:i:s'),
				]);
			}

			/*return;*/
		}
	}

	private function _generateSCCertificate($file = '') {
		if(empty($file))
			return;

		$html = '<style>@page{margin:0;padding:0;}</style><img
			src="' . site_url('uploads/summercamp/school_nominations/') . $file . '"
			style="width:100%;max-height:100%;"
		/>';

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'potrait');

		// Render the HTML as PDF
		$dompdf->render();

		$path_info = pathinfo($file);

		$dir = FCPATH . 'uploads/summercamp/school_nominations/pdf/';

		file_put_contents(
			$dir . $path_info['filename'] . '.pdf',
			$dompdf->output()
		);
	}

	public function oldSchoolMail() {
		return;

		if (!empty($results = $this->db->limit(1000)->get_where('event_site', ['date_added' => null])->result_array())) {
			$attachment = [
				FCPATH . 'assets/backend/sendmail/in-sc/Communication_Kit_Parents.docx',
				FCPATH . 'assets/backend/sendmail/in-sc/Communication_Kit_Teachers.docx',
				FCPATH . 'assets/backend/sendmail/in-sc/DigitalPoster_SummerBookWritingFestival.png',
			];

			foreach ($results as $key => $sites) {
				$site_info = $this->db->get_where('school_lead', ['archived' => '0', 'site_id' => $sites['site_id']])->row_array();

				if(empty($site_info) || empty($site_info['mobile_verified']))
					continue;

				$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/sc_old_school_mail', [], true);

				$html = str_replace(
					[
						'{owner_name}',
						'{school_name}',
						'{school_email}',
						'{school_mobile}'
					],
					[
						$site_info['school_head'],
						$site_info['name'],
						$site_info['email'],
						'+'.$site_info['mobile']
					],
					$html
				);

				$this->alert_model->email(
					$site_info['email'],
					$site_info['name'] . ', your school has been accepted in the Summer Book Writing Festival',
					$html,
					['schools@bribooks.com'],
					[],
					$attachment
				);
			}
		}

		pr(count($results));
		// pr($results, 1);
	}

	public function sendPublishedAuthorWithBookSold() {
		return;

		$results = $this->db->query('SELECT `users`.`id` AS `user_id`, `users`.`site_id`, `order_product`.`product_id` AS `book_id`, `book`.`name` AS `book_name`, book.author_name,
			users.email, users.mobile, `site`.`site_code`, `book`.`isbn` AS `book_isbn`, `book`.`date_added` AS `book_date_added`,
			SUM(order_product.quantity) AS quantity
			FROM `order_product`
			LEFT JOIN `order` ON `order`.`id`=`order_product`.`order_id`
			LEFT JOIN `book` ON `book`.`id`=`order_product`.`product_id`
			LEFT JOIN `users` ON `users`.`id`=`book`.`user_id`
			LEFT JOIN `site` ON `site`.`id` = `users`.`site_id`
			JOIN `event_user` ON `event_user`.`user_id`=`users`.`id`
			WHERE `event_user`.`event_id` = 4
			AND `event_user`.`_deleted` = 0
			AND `order`.`_deleted` = 0
			AND `order_product`.`_deleted` = 0
			AND `book`.`_deleted` = 0
			AND `book`.`archived` = 0
			AND `order`.`status` NOT IN(0, 91, 92)
			AND `book`.`date_added` BETWEEN "2023-04-01 00:00:00" and "2023-06-30 00:00:00"
			GROUP BY `order_product`.`product_id`
			ORDER by quantity DESC')->result_array();

		foreach ($results as $result) {
			if(empty($this->cron_model->getByCode('scWeeklyChallengeCron_' . $result['book_id']))) {
				$this->cron_model->add([
					'code'			=> 'scWeeklyChallengeCron_' . $result['book_id'],
					'action'		=> 'alert_model->scWeeklyChallengeCron',
					'data'			=> [$result['book_id']],
					'site_id'		=> '1',
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);
			}
		}

		pr(count($results));
		// pr($results, 1);
	}

	public function getTopWeeklyChallengeWinners() {
		return;

		$event_id = 4;
		$event_challenge_id = 4;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('event/EventChallengeWinners_model', 'event_challenge_winners_model');

		$filter_data = [
			'event_challenge_id'	=> (int)$event_challenge_id,
			'event_id'				=> (int)$event_id,
			'start'					=> 0,
			'limit'					=> 100
		];

		$results = $this->ranking_model->get_all($filter_data)['rows'] ?? [];

		// Week 1 Awards
		/*$wc_winner_gifts = [
			'Lenovo Tab Yoga',
			'Kodak Mini shot',
			'Fujifilm Instax Mini 12',
			'Canon PIXMA',
			'Boat Xtend Smartwatch',
			'Rs. 1500 Amazon Voucher ',
			'Rs. 1250 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher'
		];*/

		// Week 2 Awards
		/*$wc_winner_gifts = [
			'HP Chromebook',
			'Amazon Kindle',
			'Lenovo Tab M8',
			'Echo Dot (5th Gen)',
			'HP Deskjet 1212 Printer',
			'Rs. 1500 Amazon Voucher ',
			'Rs. 1250 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher'
		];*/

		// Week 3 Awards
		/*$wc_winner_gifts = [
			'Amazon Echo Show 10',
			'Realme Pad Mini - WiFi Tablet(3/32)',
			'Fujifilm Instax Mini 12',
			'Rocketbook Everlast Smart Notebook',
			'Mi Smart Desk Lamp 1S',
			'Rs. 1500 Amazon Voucher ',
			'Rs. 1250 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher'
		];*/

		// Week 4 Awards
		$wc_winner_gifts = [
			'Amazon Kindle',
			'Rs. 2000 Amazon Voucher ',
			'Rs. 1500 Amazon Voucher ',
			'Rs. 1250 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 750 Amazon Voucher',
			'Rs. 750 Amazon Voucher',
			'Rs. 750 Amazon Voucher'
		];

		$results = array_slice($results, 0, count($wc_winner_gifts), true);

		pr($results, 1);

		foreach ($results as $key => $result) {
			if(empty($wc_winner_gifts[$key]))
				continue;

			if(($key < 1) && empty($this->cron_model->getByCode('scWC4Winners1To5Cron_' . $result['book_id']))) {
				$save = [
					'event_challenge_id'	=> $result['event_challenge_id'],
					'event_id'				=> $result['event_id'],
					'user_id'				=> $result['user_id'],
					'book_id'				=> $result['book_id'],
					'score'					=> $result['score'],
					'rank'					=> $key+1,
					'award_name'			=> $wc_winner_gifts[$key]
				];

				$filter_data = [
					'event_challenge_id'	=> $result['event_challenge_id'],
					'event_id'				=> $result['event_id'],
					'user_id'				=> $result['user_id'],
					'book_id'				=> $result['book_id'],
				];

				if(empty($event_challenge_winners_info = $this->event_challenge_winners_model->get_all($filter_data)['rows'][0])) {
					$save['event_challenge_winner_id'] = $this->event_challenge_winners_model->add($save);
				} else {
					$this->event_challenge_winners_model->edit($event_challenge_winners_info['id'], $save);

					$save['event_challenge_winner_id'] = $event_challenge_winners_info['id'];
				}

				/*$this->cron_model->add([
					'code'			=> 'scWC4Winners1To5Cron_' . $result['book_id'],
					'action'		=> 'alert_model->scWC4Winners1To5Cron',
					'data'			=> [$save],
					'site_id'		=> '1',
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);*/
			} else if(($key >= 1) && empty($this->cron_model->getByCode('scWC4Winners6To10Cron_' . $result['book_id']))) {
				$save = [
					'event_challenge_id'	=> $result['event_challenge_id'],
					'event_id'				=> $result['event_id'],
					'user_id'				=> $result['user_id'],
					'book_id'				=> $result['book_id'],
					'score'					=> $result['score'],
					'rank'					=> $key+1,
					'award_name'			=> $wc_winner_gifts[$key]
				];

				$filter_data = [
					'event_challenge_id'	=> $result['event_challenge_id'],
					'event_id'				=> $result['event_id'],
					'user_id'				=> $result['user_id'],
					'book_id'				=> $result['book_id'],
				];

				if(empty($event_challenge_winners_info = $this->event_challenge_winners_model->get_all($filter_data)['rows'][0])) {
					$save['event_challenge_winner_id'] = $this->event_challenge_winners_model->add($save);
				} else {
					$this->event_challenge_winners_model->edit($event_challenge_winners_info['id'], $save);

					$save['event_challenge_winner_id'] = $event_challenge_winners_info['id'];
				}

				/*$this->cron_model->add([
					'code'			=> 'scWC4Winners6To10Cron_' . $result['book_id'],
					'action'		=> 'alert_model->scWC4Winners6To10Cron',
					'data'			=> [$save],
					'site_id'		=> '1',
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);*/
			}
		}

		pr($results);
	}

	public function getTopStateChallengeWinners() {
		return;

		$top_sites = (ENVIRONMENT === 'production') ? [
			32,
			9,
			20,
			12,
			29,
			26,
			34,
			27,
			11,
			30,
			16,
			2,
		] : [
			2,
			27,
		];

		$event_id = 4;
		$event_challenge_state_id = 1;

		$this->load->model('ranking/RankingState_model', 'ranking_state_model');
		$this->load->model('event/EventChallengeWinnersState_model', 'event_challenge_winners_state_model');

		// Week 4 Awards
		$wc_winner_gifts = [
			'Amazon Kindle',
			'Rs. 2000 Amazon Voucher ',
			'Rs. 1500 Amazon Voucher ',
			'Rs. 1250 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 1000 Amazon Voucher',
			'Rs. 750 Amazon Voucher',
			'Rs. 750 Amazon Voucher',
			'Rs. 750 Amazon Voucher'
		];

		$state_results = [];

		foreach ($top_sites as $state_id) {
			$filter_data = [
				'event_challenge_state_id'	=> (int)$event_challenge_state_id,
				'event_id'					=> (int)$event_id,
				'state_id'					=> (int)$state_id,
				'start'						=> 0,
				'limit'						=> 100
			];

			$results = $this->ranking_state_model->get_all($filter_data)['rows'] ?? [];

			$state_results[$state_id] = array_slice($results, 0, count($wc_winner_gifts), true);
		}

		pr($state_results, 1);

		foreach ($state_results as $results) {
			foreach ($results as $key => $result) {
				if(empty($wc_winner_gifts[$key]))
					continue;

				if(($key < 1) && empty($this->cron_model->getByCode('scStateWinner1Cron_' . $result['book_id']))) {
					$save = [
						'event_challenge_state_id'	=> $result['event_challenge_state_id'],
						'state_id'				=> $result['state_id'],
						'event_id'				=> $result['event_id'],
						'user_id'				=> $result['user_id'],
						'book_id'				=> $result['book_id'],
						'score'					=> $result['score'],
						'rank'					=> $key+1,
						'award_name'			=> $wc_winner_gifts[$key]
					];

					$filter_data = [
						'event_challenge_state_id'	=> $result['event_challenge_state_id'],
						'state_id'				=> $result['state_id'],
						'event_id'				=> $result['event_id'],
						'user_id'				=> $result['user_id'],
						'book_id'				=> $result['book_id'],
					];

					if(empty($event_challenge_winners_state_info = $this->event_challenge_winners_state_model->get_all($filter_data)['rows'][0])) {
						$save['event_challenge_winner_state_id'] = $this->event_challenge_winners_state_model->add($save);
					} else {
						$this->event_challenge_winners_state_model->edit($event_challenge_winners_state_info['id'], $save);

						$save['event_challenge_winner_state_id'] = $event_challenge_winners_state_info['id'];
					}

					/*$this->cron_model->add([
						'code'			=> 'scStateWinner1Cron_' . $result['book_id'],
						'action'		=> 'alert_model->scStateWinner1Cron',
						'data'			=> [$save],
						'site_id'		=> '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);*/
				} else if(($key >= 1) && empty($this->cron_model->getByCode('scStateWinner2To10Cron_' . $result['book_id']))) {
					$save = [
						'event_challenge_state_id'	=> $result['event_challenge_state_id'],
						'state_id'				=> $result['state_id'],
						'event_id'				=> $result['event_id'],
						'user_id'				=> $result['user_id'],
						'book_id'				=> $result['book_id'],
						'score'					=> $result['score'],
						'rank'					=> $key+1,
						'award_name'			=> $wc_winner_gifts[$key]
					];

					$filter_data = [
						'event_challenge_state_id'	=> $result['event_challenge_state_id'],
						'state_id'				=> $result['state_id'],
						'event_id'				=> $result['event_id'],
						'user_id'				=> $result['user_id'],
						'book_id'				=> $result['book_id'],
					];

					if(empty($event_challenge_winners_state_info = $this->event_challenge_winners_state_model->get_all($filter_data)['rows'][0])) {
						$save['event_challenge_winner_state_id'] = $this->event_challenge_winners_state_model->add($save);
					} else {
						$this->event_challenge_winners_state_model->edit($event_challenge_winners_state_info['id'], $save);

						$save['event_challenge_winner_state_id'] = $event_challenge_winners_state_info['id'];
					}

					/*$this->cron_model->add([
						'code'			=> 'scStateWinner2To10Cron_' . $result['book_id'],
						'action'		=> 'alert_model->scStateWinner2To10Cron',
						'data'			=> [$save],
						'site_id'		=> '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);*/
				}
			}
		}

		pr($state_results);
	}

	public function uaeCertificatesTest() {
		return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventUser_model', 'event_user_model');

		$order_products = $this->order_product_model->getBookIdsByEventIdNotInCertificates(UAE_EVENT_ID);

		if(!empty($order_products)) {
			// pr(count($order_products));
			// pr($order_products, 1);

			$chunk_size = (ENVIRONMENT === 'production') ? 1 : 10;

			$data = [];
			$count_certificate = 0;
			$count_medallion = 0;

			foreach(array_chunk($order_products, $chunk_size) as $order_product) {
				foreach ($order_product as $book_info) {
					$event_user_info = $this->event_user_model->getEventUserByUserId(UAE_EVENT_ID, $book_info['user_id']);
					$certificate_info = []; // $this->certificate_model->getByUserId($book_info['user_id'], $book_info['book_id']);

					$data[$book_info['book_id']]['book_info'] = $book_info;
					$data[$book_info['book_id']]['event_user_info'] = $event_user_info;
					$data[$book_info['book_id']]['certificate_info'] = $certificate_info;

					if(empty($certificate_info)) {
						if(empty($cron_info = $this->cron_model->getByCode('genericMsgUAEBookSoldCron_' . $book_info['book_id']))) {
							$this->cron_model->add([
								'code'			=> 'createCertificateUAECron_' . $book_info['order_id'],
								'action'		=> 'alert_model->createCertificateUAECron',
								'data'			=> [$book_info['order_id']],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+0 minutes')),
							]);
							$this->cron_model->add([
								'code'			=> 'genericMsgUAEBookSoldCron_' . $book_info['book_id'],
								'action'		=> 'alert_model->genericMsgUAEBookSoldCron',
								'data'			=> [$book_info['book_id'], $book_info],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+15 minutes')),
							]);

							$count_certificate++;

							// pr('genericMsgUAEBookSoldCron');
							// pr($book_info);
						}
					}
				}

				// die;
			}

			pr($count_certificate);
			// pr($count_medallion);
			pr($order_products, 1);
		}
	}

	public function scTop200Processed() {
		return;

		$results = $this->db->query("
			SELECT sc_top_rankers.id, book.id AS book_id, book.user_id, sc_top_rankers.book_name, sc_top_rankers.author_name, sc_top_rankers.score, sc_top_rankers.user_rank
			FROM sc_top_rankers
			JOIN book on book.name=sc_top_rankers.book_name AND book.author_name=sc_top_rankers.author_name
			JOIN users on users.id=book.user_id
			WHERE sc_top_rankers.status=1
			ORDER BY sc_top_rankers.id ASC
		")->result_array();

		pr($results, 1);

		foreach ($results as $key => $item) {
			$update = [];
			$update['book_id']			= $item['book_id'];
			$update['user_id']			= $item['user_id'];
			$update['status']			= '1';
			$update['date_modified']	= date('Y-m-d H:i:s');

			// $this->db->where('id', (int)$item['id']);
			// $this->db->update('sc_top_rankers', $update);
		}

		pr(count($results), 1);
	}

	public function getSCTopRankAuthorsCron() {
		return;

		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		if(ENVIRONMENT === 'production') {
			$results = $this->db->query("
				SELECT * FROM sc_top_rankers
				JOIN `user_details_nyaf_invites` ON `user_details_nyaf_invites`.`user_id`=`sc_top_rankers`.`user_id` AND `user_details_nyaf_invites`.`book_id`=`sc_top_rankers`.`book_id` AND `user_details_nyaf_invites`.`status`=0
				WHERE sc_top_rankers.status=1
			")->result_array();
		} else {
			$results = $this->db->query('
				SELECT `event`.`id` as event_id, `users`.`id` AS `user_id`, `users`.`site_id`, `order_product`.`product_id` AS `book_id`, SUM(order_product.quantity) AS score
				FROM `order_product`
				LEFT JOIN `order` ON `order`.`id`=`order_product`.`order_id`
				LEFT JOIN `book` ON `book`.`id`=`order_product`.`product_id`
				LEFT JOIN `users` ON `users`.`id`=`book`.`user_id`
				LEFT JOIN `site` ON `site`.`id` = `users`.`site_id`
				JOIN `event_user` ON `event_user`.`user_id`=`users`.`id`
				JOIN `event` ON `event`.`id`=`event_user`.`event_id`
				WHERE `event`.`id` = 4
				AND `event_user`.`_deleted` = 0
				AND `order`.`_deleted` = 0
				AND `order_product`.`_deleted` = 0
				AND `book`.`_deleted` = 0
				AND `book`.`archived` = 0
				AND `order`.`status` NOT IN(0, 91, 92)
				AND `book`.`date_added` BETWEEN "2023-04-01 00:00:00" and "2023-06-15 00:00:00"
				GROUP BY `order_product`.`product_id`
				ORDER by score DESC
				LIMIT 10
			')->result_array();
		}

		pr($results, 1);

		$rank_data = [];
		$i = 0;
		foreach ($results as $key => $item) {
			if (
				empty($this->db->get_where('user_details_nyaf_guest', [
					'user_id'	=> $item['user_id'],
					'book_id'	=> $item['book_id']
				])->row_array())
			) {
				$user_info = $this->student_model->get($item['user_id']);

				$invite_id = 0;

				if (
					empty($user_details_invite_info = $this->db->get_where('user_details_nyaf_invites', [
						'user_id'	=> $item['user_id'],
						'book_id'	=> $item['book_id']
					])->row_array())
				) {
					/*$save = [
						'event_id'		=> 4,
						'user_id'		=> $item['user_id'],
						'site_id'		=> $user_info['site_id'],
						'book_id'		=> $item['book_id'],
						'status'		=> 0,
						'book_rank'		=> $item['user_rank'] ?? ($key+1),
						'book_sold'		=> $item['score'],
					];

					$invite_id = $this->user_details_invite_model->add($save);*/
				}

				$id = $user_details_invite_info['id'] ?? $invite_id;

				if(!$id) {
					continue;
				}

				$code = 'userDetailsAuthorInviteSC';

				if(empty($this->cron_model->getByCode($code . '_' . $id))) {
					/*$this->cron_model->add([
						'code'			=> $code . '_' . $id,
						'site_id'		=> 1,
						'action'		=> 'alert_model->' . $code,
						'data'			=> [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+10 minutes'
							: '+1 minutes'
						))
					]);*/
				} else {
					$this->cron_model->editByCode($code . '_' . $id, [
						'data' => [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+1 minutes'
							: '+1 minutes'
						)),
						'status' => 0
					]);
				}

				$rank_data[$i]['book_id'] = $item['book_id'];
				$rank_data[$i]['site_id'] = $user_info['site_id'];
				$rank_data[$i]['site_code'] = $user_info['source'];
				$rank_data[$i]['user_id'] = $item['user_id'];
				$rank_data[$i]['name'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
				$rank_data[$i]['email'] = $user_info['email'];
				$rank_data[$i]['mobile'] = $user_info['mobile'];
				$rank_data[$i]['quantity'] = $item['score'];
				$rank_data[$i]['user_rank'] = $item['user_rank'] ?? ($key+1);
				$rank_data[$i]['url'] = vsprintf(USER_URL . 'registration/submitdetail?uid=%s&code=%s&bid=%s&eid=%s', [
					$user_info['id'],
					$user_info['verification_code'],
					$item['book_id'],
					$item['event_id'],
				]);

				$i++;
			}
		}

		pr($rank_data, 1);
	}

	public function getSchoolInviteSC() {
		return;

		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		if(ENVIRONMENT === 'production') {
			$site_ids = [502,852,1935,1167,1249,351,363,73,1938,1464,1102,789,1636,203,162,1183,933,1665];
			// $site_ids = [502,512,1425,852,1935,1167,1249,351,363,73,1938,1464,1102,789,1636,2015,203,162,1183,933,1665,999];
		} else {
			$site_ids = [2266,727,887,122,894,900,893,162,883,270,50,777,305];
		}

		$filter_data = [];
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['country_code'] = 'in';
		// $filter_data['site_code'] = 'NYAFIND2022';
		// $filter_data['parent_id'] = '581';
		$filter_data['site_ids'] = $site_ids;

		$results = $this->site_model->get_all($filter_data)['rows'] ?? [];

		pr($results, 1);

		$school_data = [];
		$i = 0;
		foreach ($results as $site_info) {
			$school_info = $this->db->get_where('school_lead', [
				'site_id'			=> $site_info['id']
			])->row_array();

			if (empty($school_info)) {
				continue;
			}

			if (
				empty($this->db->get_where('school_certificate_address', [
					'site_id'	=> $site_info['id']
				])->row_array())
			) {
				$invite_id = 0;

				if (
					empty($school_details_invite_info = $this->db->get_where('school_details_nyaf_invites', [
						'site_id'	=> $site_info['id']
					])->row_array())
				) {
					/*$save = [
						'event_id'		=> 4,
						'site_id'		=> $site_info['id'],
						'status'		=> 0,
					];

					$invite_id = $this->school_details_invite_model->add($save);*/
				}

				$id = $school_details_invite_info['id'] ?? $invite_id;

				if(!$id) {
					continue;
				}

				$code = 'schoolDetailsInviteSC';

				if(empty($this->cron_model->getByCode($code . '_' . $id))) {
					/*$this->cron_model->add([
						'code'			=> $code . '_' . $id,
						'site_id'		=> 1,
						'action'		=> 'alert_model->' . $code,
						'data'			=> [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+10 minutes'
							: '+1 minutes'
						))
					]);*/
				} else {
					$this->cron_model->editByCode($code . '_' . $id, [
						'data' => [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+1 minutes'
							: '+1 minutes'
						)),
						'status' => 0
					]);
				}

				$school_data[$i]['site_id'] = $site_info['id'];
				$school_data[$i]['site_code'] = $site_info['site_code'];
				$school_data[$i]['school_name'] = $school_info['name'];
				$school_data[$i]['school_head'] = $school_info['school_head'];
				$school_data[$i]['email'] = $school_info['email'];
				$school_data[$i]['mobile'] = $school_info['mobile'];
				$school_data[$i]['url'] = vsprintf(USER_URL . 'schoolregistration?site_id=%s&code=%s&eid=4', [
					$site_info['id'],
					$site_info['site_code']
				]);

				$i++;
			}
		}

		pr($school_data, 1);
	}

	public function generateWritingProdigyCert() {
		return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$jury_book_ids = ['257456','164239','266012','224573','205211','195790','117176','247323','175932','103613','124333','206857','110723','137155','119744','257985','196997','97686','187352','255909','103908','136109','261862','246310','170830','187249','136759','193314','133324','249348','163794','189779','174806','111663','231580','212101','204292','211895','98377','204799','134975','192845','205782','207390','209723','235432','187769','110889','115040','162366','132736','239819','236100','232034','190418','114778','145009','196387','267117','201192','97752','248664','178370','158724','216641','119241','217328','198879','95493','157846','184489','195162','163851','261673','199398','201131','190999','197595','246030','105704','223876','264977','249685','101559','198372','132910','110999','256950','166642','197215','112534','152761','250947','137861','248187','122840','201318','107802','206954','176732','97279','213013','111184','178185','136618','137867'];

		$filter_data = [];
		$filter_data['book_ids'] = $jury_book_ids;

		$result = $this->ranking_model->getRanks($filter_data);

		pr($result, 1);

		foreach ($result['rows'] ?? [] as $key => $book_info) {
			// $this->certificate_model->createWritingProdigyCertificate($book_info, 'writing_prodigy_author_cert', $key+1);
		}

		pr($result, 1);
	}

	public function downloadSCAuthorImagesZip() {
		return;

		$this->load->library('zip');

		$results = [];

		if(0) {
			$book_names = ['Tiger, Light and ME','My Second Home','THE UNIQUE DREAMS','The Secret of Golden Coins','THE THUNDER BOY','Martin and the future earth','THE FUTURE of NATURE','The Battle For Timelines','strike of the unknown kingdom','The Cricket Dream of Mohul','Laughter Fills the Void!','Indian Railways','Unique Birthday Celebration','Illusion','Trip To Turkey','Mystery of The Missing Coin','Gili-Gili Boom','ENTANGLED ADVENTURES','The kingdom of fukachi','THE BIRTHDAY','\'Cease Bully\' Freedom-Step','THE WORLD FROM A TEEN\'S EYE','The last person on earth','The Dream Present','WHOSE SWEET IS BETTER','The Magical Paradise','Festivals & Legends','The Morrisons','Anu\'s summer escapade','THE RIDE TO THE HORIZON','A Bright Red Light','Magic of love and care','Lily & OREO','The Sinclair Sisters','THE JADE','Two faces of a person','Magical paradise','Mysteries Ahead...','The Fanta-Sea Duo','My FIRST Travel Diary','Home Sweet Home','The Choice','My Inspiration','The secret behind paintings','I AM A WIZARD','THE SIX MUSKETEERS: PARIS','My Answers to Life','A dream come true','The best graduation day ever','THE SEVEN SINS AND VIRTUES','STRANDED IN THE ISLAND','The tale of Mr. Choco','Astraliza','I met your Grandpa','Kindness is the greatest gift','THE HIDDEN BEAUTY','Future of My Life','The Dead Man\'s wish','Beyond Bahubali: Ahalya','HOPE IN POVERTY','THE HALLOWEEN NIGHT','STARRY EYED TRINA','Nature with treasure','vacation time to explore','The Crystal custodians','Sunmirk','my lovely pet','THE SOCCER KINGS','Amaira\'s Adventure','Lost to be Found','Paws Of Happiness','Temporary Immortality','The Earthquake to the Future','Importance of Girls education','The last human','Friends and Foe','Taksh and The Dinosaur','THE shadows deception','from a prince to a king','should we search for aliens','The old dead man and spirit','RiGEL QUADE   THE GOOD WIZARD','A brief walk through space','FOOD & FODDIES','Chosen by the moon and earth','Treasure of poetry','THE GIRL GOT HER WINGS AGAIN','The Story of 9F','The Paw Effect','life&death: destiny defied','Good Night... Sweet Dreams!','The Magical Forest','Wonders of science','The mystifying quest','That\'s how life is !','The tales of Diya','The garnet adventure!','The Dark Side of Light','The Magical Axe','Empire, Smokes And Nightmare','MADISON','The Tale of life','The Battle of the Guardians','Friends\' Ventures Attack 1','MY STORIES,  MY WAYS','THE MYSTERIOUS JOURNEYS','Window To My World','A Dreamy Night','Adventures of Revona!','The Tale of the great journey','Adventures of Moonbeam Land','FROM KING TO PAWN','The Shoe House','Saturday which went on and on','IkkU-Journey to wooden planet','THE NIGHTMARE','A Hero\'s Destiny, Everland','Strays -  To pet or not','FIND OUT WHO','MY BROTHER AND ME','Once upon a time...','MOM','The Space Chase','The Stellar Serendipity','Prakashi an Inspriration'];

			$books = implode('","', $book_names);

			$results = $this->db->query('SELECT `book`.*
				FROM `book`
				JOIN `user_details_nyaf_invites` ON `user_details_nyaf_invites`.`book_id`=`book`.`id`
				WHERE `book`.`name` IN ("'.$books.'")')->result_array();
		}

		if(1) {
			/*$book_ids = [
				30458,75236,17642,45564,13532,53203,32619,49697,25306,62117,
				85116,87378,101909,87897,99061,101529,102570,88184,86037,83105,
				103119,188212,94521,101329,175548,173288,105682,217470,257456,164239,
				266577,280436,269328,285540,285598,261270,285512,272762,239207,242234,
			];*/

			$book_ids = [
				30458,75236,17642,45564,13532,53203,32619,49697,25306,62117,
			];

			$books = implode('","', $book_ids);

			$results = $this->db->query('SELECT `book`.*
				FROM `book`
				WHERE `book`.`id` IN ("'.$books.'")')->result_array();
		}

		// pr($results, 1);

		$data = [];
		$book_results = [];
		$user_invite_image = [];
		$user_invite_without_image = [];
		foreach ($results ?? [] as $key => $book_info) {
			$book_results[] = $book_info['name'];

			/*$author_name_image = preg_replace("/[^a-zA-Z_]+/", "", str_replace(' ', '_', trim($book_info['author_name']))) . '.png';

			$user_image = 'user_' . (int)$book_info['user_id'] . '.png';

			if($result = @file_get_contents($this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_image))
				$this->zip->add_data($author_name_image, $result);*/

			$pdf_data = self::front_back($book_info['id'], false);
			// pr($pdf_data, 1);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				'paperback',
			]);

			$this->zip->add_data(vsprintf('covers/cover-%s.pdf', [
				$filename,
			]), $pdf_data);

			$data['qrcodes'][] =[
				'image'	=> base_url(self::_getQrCode($book_info)),
				'book'	=> $book_info,
			];
		}

		// pr($data['qrcodes'], 1);

		if(empty($results) || empty($data)) {
			die(_l('no_record_found'));
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'sc_book_data.zip';

		/*pr(array_diff($book_names, $book_results));

		pr($book_names);

		pr($book_results, 1);*/

		/*$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

		$results = $this->user_details_guest_model->get_user_details_guest();

		if(empty($results)) {
			die(_l('no_record_found'));
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_authors_images.zip';

		foreach ($results as $result) {
			$book_info = $this->book_model->get($result['book_id']);

			$author_name_image = preg_replace("/[^a-zA-Z_]+/", "", str_replace(' ', '_', trim($book_info['author_name']))) . '_' . (int)$result['user_id'] . '.png';

			$user_image = 'user_' . (int)$result['user_id'] . '.png';

			if($result = @file_get_contents($this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_image))
				$this->zip->add_data($author_name_image, $result);
		}*/

		$html = $this->load->view('backend/admin/event_book_poster/qrcodes', $data, true);
		// echo $html; die;

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper([
			0,
			0,
			400,
			460
		], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();
		$dompdf->stream('qr_codes.pdf');

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function downloadSCJuryZip() {
		return;

		$this->load->library('zip');

		$book_ids = [114189,141044,201659];

		$books = implode('","', $book_ids);

		$results = $this->db->query('SELECT `book`.*
			FROM `book`
			WHERE `book`.`id` IN ("'.$books.'")')->result_array();

		// pr($results, 1);

		$data = [];
		$book_results = [];
		$user_invite_image = [];
		$user_invite_without_image = [];
		foreach ($results ?? [] as $key => $book_info) {
			$book_results[] = $book_info['name'];

			$pdf_data = self::front_back($book_info['id'], false);
			// pr($pdf_data, 1);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				'paperback',
			]);

			$this->zip->add_data(vsprintf('jury_covers/cover-%s.pdf', [
				$filename,
			]), $pdf_data);

			$data['qrcodes'][] =[
				'image'	=> base_url(self::_getQrCode($book_info)),
				'book'	=> $book_info,
			];
		}

		if(empty($results) || empty($data)) {
			die(_l('no_record_found'));
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'sc_book_data.zip';

		/*pr(array_diff($book_names, $book_results));

		pr($book_names);

		pr($book_results, 1);*/

		/*$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

		$results = $this->user_details_guest_model->get_user_details_guest();

		if(empty($results)) {
			die(_l('no_record_found'));
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_authors_images.zip';

		foreach ($results as $result) {
			$book_info = $this->book_model->get($result['book_id']);

			$author_name_image = preg_replace("/[^a-zA-Z_]+/", "", str_replace(' ', '_', trim($book_info['author_name']))) . '_' . (int)$result['user_id'] . '.png';

			$user_image = 'user_' . (int)$result['user_id'] . '.png';

			if($result = @file_get_contents($this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_image))
				$this->zip->add_data($author_name_image, $result);
		}*/

		$html = $this->load->view('backend/admin/event_book_poster/qrcodes', $data, true);
		// echo $html; die;

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper([
			0,
			0,
			400,
			460
		], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();
		$dompdf->stream('jury_qr_codes.pdf');

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function front_back($book_id = 0, $download = true) {
		return;

		$this->load->model('design/Cover_model', 'cover_model');

		if ($book_info = $this->book_model->get($book_id)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$original_width 	= 285;
			$original_height 	= $original_width * 1.5;
			$book_width 		= 648;
			$book_bleed_width 	= 648;

			// $data['multiplier'] = $multiplier = 432 / 285;
			$data['multiplier'] = $book_width / $original_width;
			$data['bleed'] 		= 0;
			$data['fc_bleed'] 	= 0;

			$multiplier 		= $book_bleed_width / $original_width;
			$cover_info 		= !empty($book_info['cover_id'])
				? $this->cover_model->get($book_info['cover_id'])
				: [];
			$heading_style 		= !empty($cover_info['heading_style'])
				? json_decode($cover_info['heading_style'], true)
				: [];

			$data['cover_info'] 	= $cover_info;
			$data['heading_style'] 	= !empty($heading_style['style'])
				? $heading_style['style']
				: [];

			$book_info['isbn'] = !empty($book_info['isbn']) ? $book_info['isbn'] : $book_info['unique_id'];

			$data['book'] 		= $book_info;
			$data['book_code'] 	= _o_b_code($book_info['id'], $book_info['version'], 'paperback');

			$data['width'] 		= $original_width * $multiplier;
			$data['height'] 	= $original_height * $multiplier;

			$data['book_bleed_width'] 	= $book_bleed_width;

			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));

			$data['barcode'] 	= !empty($book_info['isbn'])
				// ? base_url(self::_getBarcode($book_info['isbn']))
				? self::_getFrontBackBarcode($book_info['isbn'], $multiplier)
				: $data['qrcode'];

			$html = $this->load->view('backend/admin/books/front_break', $data, true);
			// echo $html; die;

			$dompdf = new Dompdf([
				// 'debugLayout' 	=> true,
				// 'debugCss'		=> true,
				// 'debugPng'		=> true,
			]);

			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('dpi', 300);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper(
				[
					0,
					0,
					$data['width'],
					$data['height']
				],
				'portrait'
			);

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			if ($download) {
				$dompdf->stream(str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf');
			} else {
				return $dompdf->output();
			}
		}
	}

	private function _getFrontBackBarcode($data = 0, $multiplier = 1) {
		return;

		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			160 * 2.33 * $multiplier,
			40 * 2.33 * $multiplier,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();
	}
}
