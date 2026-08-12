<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('whatsapp');

trait Nfwis2023
{
	use CommonWhatsapp;

	public function testElibleUsersForCertNfwis($user_id = '')
	{
		return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$books = $this->book_model->get_all([
			'user_id'	=> '165509',
			'site_code'	=> 'ge-NWFIS-',
			'start'		=> 5000 * (($page ?? 1) - 1),
			'limit' 	=> 5000,
			'status'	=> 1,
			'archived'	=> 0
		])['rows'] ?? [];

		$exclude = [];

		foreach ($books as $key => $item) {
			/*$order_products = $this->order_product_model->get_all([
				'product_id'=> $item['id'],
				'sort'	=> 'order_product.id',
				'order'	=> 'ASC'
			]);

			foreach ($order_products['rows'] ?? [] as $order_product) {
				self::createAwardsOnBookSoldNwfis($order_product['order_id']);
			}*/

			if (!in_array($item['user_id'], $exclude)) {

				$exclude[] = $item['user_id'];

				log_kb(['gen_cert_Nwfis:: ' => [$item['name'], $key]]);
			}
		}

		pr($books, 1);
		return;

		$id = 'all';
		$code = 'createCertificateNwfis';

		if(empty($this->cron_model->getByCode($code . '_' . $id))) {
			$this->cron_model->add([
				'code'			=> $code . '_' . $id,
				'action'		=> 'alert_model->' . $code,
				'data'			=> [$id],
				'alert_date'	=> date('Y-m-d H:i:s'),
			]);
		} else {
			$this->cron_model->editByCode($code . '_' . $id, ['status' => 0]);
		}

		if(empty($user_id))
			return;
	}

	public function testAwardsOnBookSoldNfwis($order_id = '')
	{
		return;

		if(empty($order_id))
			return;

		self::createAwardsOnBookSold($order_id);
	}

	public function sendSchoolWhatsappIsrael() {
		return;

		/*if(date('YmdH') >= '2023030923')
			return;*/

		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		$results = $this->school_lead_model->get_all([
			'state_id'	=> '35',
			'mobile_verified'	=> '1',
		]);

		$school_data = [];
		$i = 0;
		foreach ($results['rows'] ?? [] as $result) {
			$school_data[$i]['site_id'] = $result['site_id'];
			$school_data[$i]['school_id'] = $result['school_id'];
			$school_data[$i]['city_id'] = $result['city_id'];
			$school_data[$i]['name'] = $result['name'];
			$school_data[$i]['email'] = $result['email'];
			$school_data[$i]['mobile'] = $result['mobile'];
			$school_data[$i]['school_head'] = $result['school_head'];
			$school_data[$i]['authorized_person'] = $result['authorized_person'];

			self::_sendWhatsappDocument(
				$result['mobile'],
				[
					'template'		=> '734152628203183',
					'parameters'	=> [],
					'document'	=> [
						'name'	=> 'משימת הכתיבה.pdf',
						'link'	=> base_url('assets/backend/sendmail/nwfis/the_writing_task.pdf')
					]
				]
			);

			$i++;
		}

		pr($school_data, 1);
	}

	public function sendDailySchoolReportIsrael() {
		@unlink(FCPATH . 'uploads/csv/' . date('Y-m-d', strtotime('-1 day')));

		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('Alert_model', 'alert_model');

		$filter_data = [];
		$filter_data['state_id'] = 35;
		$filter_data['mobile_verified'] = 1;

		$schools = $this->school_lead_model->get_all($filter_data);

		$sort_order = [];
		$gradesIsrael = [];

		$i = $rank = 0;

		$dir = FCPATH . 'uploads/csv/'.date('Y-m-d');

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$date1 = date_create("2023-04-22");
		$date2 = date_create("today");
		$diff = date_diff($date2, $date1);
		$days_left = $diff->format("%a");

		foreach ($schools['rows'] ?? [] as $school) {
			$total_written = $this->ranking_model->get_books([
				'site_id'	=> $school['site_id']
			])['total'];

			$total_published = $this->ranking_model->get_books([
				'site_id'	=> $school['site_id'],
				'status'	=> 1
			])['total'];

			$total_sold = $this->ranking_model->getTotalSolds([
				'site_id'	=> $school['site_id']
			]);

			$rank = ($total_written * 0.2) + ($total_published * 0.35) + ($total_sold * 0.45);

			$gradesIsrael[$i]['rank'] = $rank;
			$gradesIsrael[$i]['site_id'] = $school['site_id'];
			$gradesIsrael[$i]['school_name'] = $school['name'];
			$gradesIsrael[$i]['school_email'] = $school['email'];
			$gradesIsrael[$i]['spoc_name'] = $school['school_head'];
			$gradesIsrael[$i]['grades'] = $school['grades'];
			$gradesIsrael[$i]['total_written'] = $total_written ?? 0;
			$gradesIsrael[$i]['total_published'] = $total_published ?? 0;
			$gradesIsrael[$i]['total_sold'] = $total_sold ?? 0;

			$school_grades = !empty($school['grades']) ? json_decode($school['grades'], 1) : '';
			sort($school_grades);

			$filter_data = [];
			$filter_data['site_id'] = $school['site_id'];
			$filter_data['mobile_verified'] = 1;

			$leads = $this->lead_model->getCountByGrade($filter_data);

			$school_csv = [];

			foreach ($leads as $lead) {
				if(empty($lead['grade_name']))
					continue;

				$school_csv[$lead['grade_name']]['count'] = $lead['count'] ?? 0;
				$school_csv[$lead['grade_name']]['students'] = $lead['lead_names'] ?? '';
			}

			$gradesArr = [];

			$tr = '';

			foreach ($school_grades as $grade) {
				$grade_info = $this->grade_model->get_all([
					'site_id'	=> $school['site_id'],
					'name'	=> $grade
				]);

				$grade_info = !empty($grade_info['rows'][0]) ? $grade_info['rows'][0] : [];

				$grade_id = $grade_info['id'];

				if(empty($grade_id))
					continue;

				$book_written = $this->ranking_model->get_books([
					'site_id'	=> $school['site_id'],
					'grade_id'	=> $grade_id
				])['total'];

				$book_published = $this->ranking_model->get_books([
					'site_id'	=> $school['site_id'],
					'grade_id'	=> $grade_id,
					'status'	=> 1
				])['total'];

				$total_sold = $this->ranking_model->getTotalSolds([
					'site_id'	=> $school['site_id'],
					'grade_id'	=> $grade_id
				]);

				$registered_students = $school_csv[$grade]['count'] ?? 0;

				$registered_students_name = '<strong style="font-size: 18px;">Total Students: (' . $registered_students . ')</strong><br /><br />';

				if(!empty($school_csv[$grade]['students'])) {
					$students = explode(",", $school_csv[$grade]['students']);

					$k = 1;
					foreach ($students as $student) {
						if ($registered_students == $k) {
							$registered_students_name .= trim($student);
						} else if($k%2 == 0) {
							$registered_students_name .= trim($student) . ',<br />';
						} else {
							$registered_students_name .= trim($student) . ', ';
						}

						$k++;
					}
				}

				$book_written = $book_written ?? 0;
				$book_published = $book_published ?? 0;
				$total_sold = $total_sold ?? 0;

				$tr .= '<tr>
	                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$grade.'</td>
	                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px; font-size: 14px;">'.$registered_students_name.'</td>
	                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$book_written.'</td>
	                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$book_published.'</td>
	                <td style="border-bottom: 1px solid #000; padding: 8px;">'.$total_sold.'</td>
	            </tr>';

				$gradesArr[] = [
					'grade_id' => $grade_id,
					'class' => $grade,
					'registered_students' => $registered_students,
					'registered_students_name' => $registered_students_name,
					'books_written' => $book_written,
					'books_published' => $book_published,
					'books_sold' => $total_sold
				];
			}

			$gradesIsrael[$i]['data'] = $gradesArr;
			$gradesIsrael[$i]['tr'] = $tr;

			$sort_order[] = $rank;

			$i++;
		}

		array_multisort($sort_order, SORT_DESC, $gradesIsrael);

		if(ENVIRONMENT === 'production') {
			foreach ($gradesIsrael as $key => $grade_israel) {
				if(strtolower($grade_israel['school_email']) === 'efratmich18@gmail.com')
					continue;

				$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/israel_school_report_pdf_template', [], true);

				$html = str_replace(
					[
						'{variable1}',
						'{variable2}',
						'{variable3}',
						'{variable4}',
						'{variable5}',
						'{variable6}'
					],
					[
						date('d/m/Y'),
						$days_left,
						$grade_israel['school_name'],
						$key+1,
						$schools['total'],
						$grade_israel['tr']
					],
					$html
				);

				$dompdf = new Dompdf();
				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('isHtml5ParserEnabled', true);
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$file = 'uploads/csv/'.date('Y-m-d').'/daily_school_israel_report_'.$grade_israel['site_id'].'.pdf';
				$output = $dompdf->output();
				file_put_contents(FCPATH.$file, $output);

				$email = $grade_israel['school_email'];

				$subject = 'Israel National Writing Festival - your schools daily report';

				$content = '<p>Dear Educator,</p>
<p>Please find the attached daily report for your school.</p>
<p>Regards,</p>
<p>Team BriBooks</p>';

				$this->alert_model->email(
					$email,
					$subject,
					$content,
					[],
					[],
					FCPATH . $file
				);

				// unlink(FCPATH . $file);
				// pr($grade_israel, 1);
			}
		}

		/*pr('Count => ' . count($gradesIsrael));
		pr($gradesIsrael, 1);*/
	}

	public function sendDailyReportIsrael() {
		@unlink(FCPATH . 'uploads/csv/' . date('Y-m-d', strtotime('-1 day')));

		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('Alert_model', 'alert_model');

		$filter_data = [];
		$filter_data['state_id'] = 35;
		$filter_data['mobile_verified'] = 1;

		$schools = $this->school_lead_model->get_all($filter_data);

		$sort_order = [];
		$gradesIsrael = [];
		$i = 0;
		$tr = '';

		foreach ($schools['rows'] ?? [] as $school) {
			$school_name = $school['name'];

			$total_written = $this->ranking_model->get_books([
				'site_id'	=> $school['site_id']
			])['total'];

			$total_published = $this->ranking_model->get_books([
				'site_id'	=> $school['site_id'],
				'status'	=> 1
			])['total'];

			$total_sold = $this->ranking_model->getTotalSolds([
				'site_id'	=> $school['site_id']
			]);

			$total_sold = !empty($total_sold) ? $total_sold : 0;

			$total_students = $this->lead_model->get_all([
				'site_id'			=> $school['site_id'],
				'mobile_verified'	=> 1
			])['total'];

			$rank = ($total_written * 0.2) + ($total_published * 0.35) + ($total_sold * 0.45);

			$gradesIsrael[$i]['rank'] = $rank;
			$gradesIsrael[$i]['site_id'] = $school['site_id'];
			$gradesIsrael[$i]['school_name'] = $school_name;
			$gradesIsrael[$i]['school_email'] = $school['email'];
			$gradesIsrael[$i]['school_mobile'] = $school['mobile'];
			$gradesIsrael[$i]['spoc_name'] = $school['school_head'];
			$gradesIsrael[$i]['grades'] = $school['grades'];
			$gradesIsrael[$i]['total_students'] = $total_students ?? 0;
			$gradesIsrael[$i]['total_written'] = $total_written ?? 0;
			$gradesIsrael[$i]['total_published'] = $total_published ?? 0;
			$gradesIsrael[$i]['total_sold'] = $total_sold ?? 0;
			$gradesIsrael[$i]['tr'] = '<tr>
                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$school_name.'</td>
                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$total_students.'</td>
                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$total_written.'</td>
                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">'.$total_published.'</td>
                <td style="border-bottom: 1px solid #000; padding: 8px;">'.$total_sold.'</td>
            </tr>';

			$sort_order[] = $rank;

			$i++;
		}

		array_multisort($sort_order, SORT_DESC, $gradesIsrael);

		if(ENVIRONMENT === 'production' && !empty($gradesIsrael)) {
			foreach ($gradesIsrael ?? [] as $school_israel) {
				$tr .= $school_israel['tr'];

				if(date('YmdH') === '2023032214') {
					self::_sendWhatsappText(
						$school_israel['school_mobile'],
						[
							'template'		=> '885133746110792',
							'parameters'	=> []
						]
					);
				}
			}

			$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/israel_report_pdf_template', [], true);

			$html = str_replace(
				[
					'{variable1}',
					'{variable2}'
				],
				[
					date('d/m/Y'),
					$tr
				],
				$html
			);

			/*$dompdf = new Dompdf();
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$file = 'uploads/csv/'.date('Y-m-d').'/daily_israel_report.pdf';
			$output = $dompdf->output();
			file_put_contents(FCPATH.$file, $output);*/

			$email = '';

			$subject = 'Israel National Writing Festival - schools daily report';

			$content = '<p>Dear Ami,</p>
<p>Please find the daily school report below:</p><br />
'.$html.'
<p>Regards,</p>
<p>Team BriBooks</p>';

			$this->alert_model->email(
				$email,
				$subject,
				$content,
				[],
				[],
				/*FCPATH . $file*/
			);

			// unlink(FCPATH . $file);
		}

		/*pr('Count => ' . count($gradesIsrael));
		pr($gradesIsrael, 1);*/
	}

	public function sendOneTimeSchoolAlertIsrael($start = '', $limit = 1) {
		return;

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		$filter_data = [];
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['state_id'] = 35;
		$filter_data['mobile_verified'] = 1;

		$schools = $this->school_lead_model->get_all($filter_data);

		foreach ($schools['rows'] ?? [] as $school) {
			$mobile = $school['mobile'];
			$email = $school['email'];
			/*self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> '1362669761242563',
					'parameters'	=> []
				]
			);*/

			$this->alert_model->email(
				$email,
				'Certifications of Excellence for Schools and Students',
				'<div align="right">
<p>מורות יקרות</p>
<p>בלינק המצורף תוכלו למצוא את תעודות ההצטיינות לבתי הספר המצטיינים וגם לתלמידים שספריהם זכו בתעודת הצטיינות על איכות הכתיבה. התלמידים שלכם לא יודעים שזכו ונשמח אם אתם תבשרו להם אודות הזכייה כדי</p>
<p>שההודעה תהיה אישית וחינוכית</p>
<p>ברכות לזוכים</p>
<p>נתראה בשנה הבאה</p>
<p>https://drive.google.com/drive/folders/1722bjlKo4YVEdWgscEzQUQd9hx2X8vXT</p>
				</div>',
				[],
				[]
			);
		}

		pr($schools['total'], 1);
	}

	public function sendStudentAlertIsrael($start = '', $limit = 1) {
		return;

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('user/Student_model', 'student_model');

		$filter_data = [];
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['source'] = 'ge-NWFIS';
		$filter_data['email_verified'] = 1;

		$students = $this->student_model->get_all($filter_data);

		foreach ($students['rows'] ?? [] as $student) {
			$mobile = $student['mobile'];
			$email = $student['email'];

			self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> '986159009181701',
					'parameters'	=> []
				]
			);

			$this->alert_model->email(
				$email,
				'We made it to the finish line! - Israel National Writing Festival',
				'<div align="right">
<p>תלמידים יקרים</p>
<p>בשבוע הקרוב נפרסם את רשימת הספרים שזכו בפרס השופטים, אבל כל ספר הוא פרס, לכם, לבית הספר, ולמשפחה שלכם</p><br />
<p>אתם כמובן יכולים להמשיך למכור את הספרים שלכם, לקבל תעודות ופרסים, להרוויח תגמולים, ולכתוב ספרים חדשים. מערכת התגמולים (קבלת הרווחים שלכם ממכירת הספרים) עדיין לא פעילה בישראל ותפעל בעוד שבועיים. בנוסף, אם אתם לא רואים את כל הרווחים שלכם, זה בגלל שהרווחים נרשמים במערכת רק שבועיים לאחר מכירת הספר. רווחים ממכירת הספרים שלכם באמזון מופיעים רק לאחר 6 שבועות. אנחנו תמיד לשירותכם, ונתראה בשנת הלימודים הבאה</p><br />
<p>צוות בריבוקס</p>
				</div>',
				[],
				[]
			);
		}

		pr($students['total'], 1);
	}

	public function sendUnPublishedAuthorAlertIsrael() {
		$date1 = date_create("2023-05-17");
		$date2 = date_create("today");
		$diff = date_diff($date2, $date1);
		$days_left = $diff->format("%a");

		if(!in_array($days_left, [1,3,5]))
			return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$results = $this->db->query("SELECT book.user_id, book.id AS book_id, book.name AS book_name, book.author_name, book.slug, users.first_name, users.last_name, users.email, users.mobile, book.date_added AS book_date_added
		FROM `book`
		JOIN users on users.id=book.user_id
		WHERE `users`.`source` LIKE 'ge-NWFIS-%'
		AND `book`.`archived` = 0
		AND `book`.`status` != 0
		AND `book`.`id` NOT IN (SELECT DISTINCT(order_product.product_id) AS product_id from order_product WHERE order_product._deleted = 0)
		GROUP BY `book`.`user_id`, `book`.`id`
		ORDER BY `book`.`id` ASC")->result_array();

		foreach ($results as $key => $item) {
			$order_products = $this->order_product_model->get_all([
				'product_id'=> $item['book_id'],
				'sort'	=> 'order_product.id',
				'order'	=> 'ASC'
			]);

			if(empty($order_products['rows'])) {
				$mobile = $item['mobile'];
				$email = $item['email'];

				$author_name = explode(" ", trim($item['author_name']));

				$author_first_name = ucfirst($author_name[0]);

				$book_url = USER_URL . 'bookstore/' . $item['slug'];

				$book_date_added = date('M j, Y', strtotime($item['book_date_added']));

				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> '1351871385372378',
						'parameters'	=> [
							$author_first_name,
							$item['book_name'],
							$book_date_added,
							$days_left,
							$book_url
						]
					]
				);

				$subject = $author_first_name . ', You can join the League of Young Published Authors!';

				$content = '<p>Hey '.$author_first_name.'!</p>
		<p>We have a VERY IMPORTANT UPDATE for you!</p>
		<p>Congratulations on publishing your book '.$item['book_name'].' on '.$book_date_added.'.</p>
		<p>However, The National English Writing Festival will end in '.$days_left.' days and we notice that you haven\'t sold any copies of your book yet.</p>
		<p>We encourage you to share your book '.$book_url.' with your social network or order the first copy of your book to receive the coveted <strong>Published Author Certificate</strong>, and most importantly secure your book for the future.</p><br />
		<p>Thank you</p>
		<p>Israel’s National English Writing Festival Team.</p>';

				$this->alert_model->email(
					$email,
					$subject,
					$content,
					[],
					[]
				);
			}
		}

		pr($results, 1);
	}

	public function generateNwfisCertificate($start = '', $limit = 1) {
		return;

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		/*$schools = array(
			653 => 'Shazar School',
			717 => 'Goldtech School',
			588 => 'Ahavat Tzion School',
			700 => 'Revivim School',
			671 => 'Beit Tzuri School',
			640 => 'Beeri School',
			587 => 'Arik Einstein School',
			622 => 'Yahalom School',
			718 => 'Kochav Hatzafon School',
			702 => 'Gavrieli-Carmel School'
		);

		foreach ($schools as $school) {
			$data = [];
			$data['school_name'] = $school;
			$data['date'] = date('d/m/Y');

			self::createCertificate_NWFIS($data, 'literary_leadership_award_cert');
		}*/

		/*$books = $this->db->query("SELECT * FROM `book_bkup`")->result_array();

		foreach ($books as $book) {
			$data = [];
			$data['author_name'] = $book['author_name'];
			$data['book_name'] = $book['name'];
			$data['isbn'] = $book['isbn'];
			$data['date'] = date('d/m/Y');

			self::createCertificate_NWFIS($data, (!empty($book['isbn']) ? 'national_certificate_of_excellence_with_isbn_cert' : 'national_certificate_of_excellence_cert'));
		}*/
	}

	public function createCertificate_NWFIS($data = [], $type = '') {
		$dir = FCPATH . 'uploads/all_certificate_nwfis_manual';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/ge-nwfis/'.$type.'.jpg');

		$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/ge-nwfis/'.$type.'.jpg');
		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);

		$font_path = FCPATH . 'assets/global/fonts/MYRIADPRO-BOLD.OTF';

		$font_size = 32;

		$image_name = '';

		if(!empty($data['school_name'])) {
			$image_name = strtoupper(str_replace(array(' ','-'), array('_','_'), $data['school_name'])) . '.jpeg';

			$school_name_length = (int)((mb_strlen($data['school_name'], 'utf-8') * ($font_size-9))/2) ?? 400;

			imagettftext($image, $font_size, 0, 900 - $school_name_length, 780, $darkgrey, $font_path, strtoupper($data['school_name']));
			imagettftext($image, $font_size, 0, 520, 1120, $darkgrey, $font_path, $data['date']);
		} else {
			$image_name = strtoupper(str_replace(array(' ','-'), array('_','_'), $data['author_name'])) . '-' . strtoupper(str_replace(array(' ','-'), array('_','_'), $data['book_name'])) . '.jpeg';

			$author_name_length = (int)((mb_strlen($data['author_name'], 'utf-8') * ($font_size-9))/2) ?? 400;
			$book_name_length = (int)((mb_strlen($data['book_name'], 'utf-8') * ($font_size-9))/2) ?? 400;

			imagettftext($image, $font_size, 0, 900 - $author_name_length, 700, $darkgrey, $font_path, strtoupper($data['author_name']));
			imagettftext($image, $font_size, 0, 900 - $book_name_length, 880, $darkgrey, $font_path, strtoupper($data['book_name']));
			if(!empty($data['isbn'])) {
				imagettftext($image, $font_size, 0, 750, 1000, $darkgrey, $font_path, strtoupper($data['isbn']));
				imagettftext($image, $font_size, 0, 520, 1170, $darkgrey, $font_path, $data['date']);
			} else {
				imagettftext($image, $font_size, 0, 520, 1120, $darkgrey, $font_path, $data['date']);
			}
		}

		imagejpeg($image, $dir . '/' . $image_name);
		imagedestroy($image);

		self::_generatePDFCertificateManual($image_name, 'all_certificate_nwfis_manual');

		return $image_name;
	}

	private function _generatePDFCertificateManual($file = '', $folder_name = 'all_certificate') {
		if(empty($file))
			return;

		$html = '<style>@page{margin:0;padding:0;}</style><img
			src="' . site_url('uploads/'.$folder_name.'/') . $file . '"
			style="width:100%;max-height:100%;"
		/>';

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		$path_info = pathinfo($file);

		$dir = FCPATH . 'uploads/'.$folder_name.'/pdf/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		file_put_contents(
			$dir . $path_info['filename'] . '.pdf',
			$dompdf->output()
		);
	}

	public function israelOrdersManuallyProcessed() {
		return;

		$this->load->model('book/Book_model', 'book_model');

		$results = $this->db->query("
			SELECT israel_order.id, book.id AS book_id, book.version, book.user_id, users.site_id, israel_order.book_name, israel_order.author_name,
			israel_order.quantity, users.source, users.location
			FROM israel_order
			LEFT JOIN book on book.name=israel_order.book_name AND book.author_name=israel_order.author_name
			LEFT JOIN users on users.id=book.user_id
			WHERE israel_order.status=1
			ORDER BY israel_order.id ASC
		")->result_array();

		pr($results, 1);

		foreach ($results as $key => $item) {
			$total_pages = $this->book_model->getTotalPages($item['book_id']) * 2 + 5;

			// pr($item);
			// pr($total_pages);

			$base_price = '15.00';
			$free_page_limit = 80;
			$price_per_page = '0.10';

			if ($total_pages > $free_page_limit) {
				$ppp_total = (
					$total_pages - $free_page_limit
				) * $price_per_page;

				$total = $base_price + $ppp_total;

				$book_price = [
					'price' 		=> round($base_price, 2),
					'total' 		=> round($total, 2),
					'ppp_total' 	=> round($ppp_total, 2),
					'total_pages' 	=> $total_pages,
				];
			} else {
				$book_price = [
					'price' 		=> round($base_price, 2),
					'total' 		=> round($base_price, 2),
					'ppp_total' 	=> 0,
					'total_pages' 	=> $total_pages,
				];
			}

			// pr($book_price, 1);

			/*$update = [];
			$update['book_id']			= $item['book_id'];
			$update['version']			= $item['version'];
			$update['user_id']			= $item['user_id'];
			$update['site_id']			= $item['site_id'];
			$update['currency_code']	= 'USD';
			$update['price']			= $book_price['total'];
			$update['pages']			= $total_pages;
			$update['status']			= '1';
			$update['date_modified']	= date('Y-m-d H:i:s');

			$this->db->where('id', (int)$item['id']);
			$this->db->update('israel_order', $update);*/
		}

		pr(count($results), 1);
	}

	public function createOrdersByData() {
		return;

		$this->load->library('Royalty_lib', 'royalty_lib');

		$results = $this->db->query("
			SELECT * FROM israel_order
			WHERE israel_order.status=1
			ORDER BY israel_order.id ASC
		")->result_array();

		pr($results, 1);

		foreach ($results as $key => $item) {
			$total_pages = $item['pages'];

			$base_price = '15.00';
			if($item['quantity'] >= 3) {
				$base_price = '12.00';
			}

			$free_page_limit = 80;
			$price_per_page = '0.10';

			if ($total_pages > $free_page_limit) {
				$ppp_total = (
					$total_pages - $free_page_limit
				) * $price_per_page;

				$total = $base_price + $ppp_total;

				$book_price = [
					'price' 		=> round($base_price, 2),
					'total' 		=> round($total, 2),
					'ppp_total' 	=> round($ppp_total, 2),
					'total_pages' 	=> $total_pages,
				];
			} else {
				$book_price = [
					'price' 		=> round($base_price, 2),
					'total' 		=> round($base_price, 2),
					'ppp_total' 	=> 0,
					'total_pages' 	=> $total_pages,
				];
			}

			pr($item);
			pr($book_price);

			$weight = (
				$total_pages * BOOK_WEIGHT['page'] * 2 +
				BOOK_WEIGHT['cover']['paperback']
			) * $item['quantity'];

			$user_id = 82;

			$ppp_total = $book_price['ppp_total'] * $item['quantity'];
			$shipping_cost = $item['quantity'] * SHIPPING_FLAT_PRICE['IL'];
			$total = round(((($book_price['price'] + SHIPPING_FLAT_PRICE['IL']) * $item['quantity']) + $ppp_total), 2);

			$order_data = [
				'user_id'				=> $user_id,
				'site_id'				=> 2,
				'address_id'			=> 55493,
				'currency_id'			=> 2,
				'currency_code'			=> 'USD',
				'currency_symbol'		=> '$',
				'coupon_id'				=> 0,
				'ppp_total'				=> (double)$ppp_total,
				'credit_discount'		=> '0.00',
				'tax'					=> '0.00',
				'shipping_cost'			=> (double)$shipping_cost,
				'subtotal'				=> (double)$total,
				'total'					=> (double)$total,
				'weight'				=> (double)$weight,
				'shipping_info'			=> '{"id": 9876543210, "rate": '.$shipping_cost.', "courier_name": "BriBooks Flat Shipping"}',
				'ip'					=> $this->input->ip_address(),
				'provider'				=> 'stripe',
				'status'				=> 1,
				'order_type'			=> 1,
				'ext_order_id'			=> 'pi_3MzhDuGfHyFVxD3I1kxwpbEx_secret_h2PRb9HEwZZTjPyljnbJUnELl',
				'ext_transaction_id'	=> 'pi_3MzhDuGfHyFVxD3I1kxwpbEx',
				'date_added'			=> date('Y-m-d H:i:s'),
				'date_modified'			=> date('Y-m-d H:i:s')
			];

			pr($order_data);

			$order_id = 0;

			if(0) {
				$this->db->insert('order', $order_data);
				$order_id = $this->db->insert_id();

				$update = [];
				$update['order_code']		= 'BB-' . time() . '-' . $order_id . 'I' . $user_id;
				$update['status']			= 4;
				$update['shipping_status']	= 1;
				$update['date_modified']	= date('Y-m-d H:i:s');
				$update['date_completed']	= date('Y-m-d H:i:s');

				$this->db->where('id', (int)$order_id);
				$this->db->update('order', $update);
			}

			pr($order_id);

			$total_op = round((($book_price['total'] * $item['quantity'])), 2);

			$order_product_data = [
				'version'			=> (int)$item['version'],
				'order_id'			=> (int)$order_id,
				'product_id'		=> (int)$item['book_id'],
				'quantity'			=> (int)$item['quantity'],
				'price'				=> (double)$book_price['price'],
				'credit'			=> 0,
				'used_credit'		=> 0,
				'credit_discount'	=> '0.00',
				'ppp_total'			=> (double)$ppp_total,
				'subtotal'			=> (double)$total_op,
				'total'				=> (double)$total_op,
				'weight'			=> $weight,
				'option'			=> '{"name":"Paperback","price":0}'
			];

			pr($order_product_data);

			$order_comment_data = [
				'order_id'			=> (int)$order_id,
				'description'		=> 'Manually Created & Delivered',
				'status'			=> 4,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			];

			pr($order_comment_data);

			$order_history_data = [
				'order_id'			=> (int)$order_id,
				'description'		=> 'Offline Order Completed',
				'status'			=> 4,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			];

			pr($order_history_data);

			$payment_data = [
				'site_id'			=> 2,
				'user_id'			=> $user_id,
				'order_id'			=> (int)$order_id,
				'currency_id'		=> 2,
				'currency_code'		=> 'USD',
				'currency_symbol'	=> '$',
				'provider'			=> 'stripe',
				'amount'			=> (double)$total,
				'status'			=> 1,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			];

			pr($payment_data);

			if(0 && $order_id) {
				$this->db->insert('order_product', $order_product_data);

				$this->db->insert('order_comment', $order_comment_data);

				$this->db->insert('order_history', $order_history_data);

				$this->db->insert('payment', $payment_data);

				$this->royalty_lib->applyRoyalty($order_id);

				$update = [];
				$update['order_id']			= $order_id;
				$update['status']			= '2';
				$update['date_modified']	= date('Y-m-d H:i:s');

				$this->db->where('id', (int)$item['id']);
				$this->db->update('israel_order', $update);
			}
		}

		pr(count($results), 1);
	}

	public function createOrdersAuthorEarning() {
		return;

		$this->load->library('Royalty_lib', 'royalty_lib');

		$this->load->model('user/AuthorEarning_model', 'author_earning_model');
		$this->load->model('user/UserCredit_model', 'user_credit_model');
		$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');

		$user_id_ge = (ENVIRONMENT === 'production') ? 157740 : 1046;

		$results = $this->db->query("
			SELECT book.id as book_id, book.user_id, book.name as book_name,
			book.author_name, order_product.order_id, order_product.quantity,
			author_earning.user_id as author_earning_user_id, author_earning.author_id as author_earning_author_id, author_earning.amount
			FROM `users`
			JOIN book on book.user_id=users.id
			JOIN order_product on order_product.product_id=book.id
			JOIN `order` on `order`.id=order_product.order_id AND `order`._deleted=0
			LEFT JOIN author_earning on order_product.product_id=author_earning.book_id AND order_product.order_id=author_earning.order_id AND book.user_id=author_earning.author_id
			WHERE users.state_id = '35' AND users.id >= $user_id_ge AND author_earning.amount IS NULL
		")->result_array();

		foreach ($results as $key => $item) {
			// pr($item);

			// $this->royalty_lib->applyRoyalty($item['order_id']);

			$filter_data = [
				'order_id'	=> $item['order_id'],
				'book_id'	=> $item['book_id'],
				'user_id'	=> $item['user_id'],
				'author_id'	=> $item['user_id'],
				'status'	=> 0,
			];

			$author_earning_results = $this->author_earning_model->get_all($filter_data)['rows'] ?? [];

			if(!empty($author_earning_result = $author_earning_results[0])) {
				// self::_genCreditIL($author_earning_result);
			}

			// pr($author_earning_results, 1);
		}

		pr($results, 1);
	}

	private function _genCreditIL($info = []) {
		return;

		$this->load->model('user/AuthorEarning_model', 'author_earning_model');
		$this->load->model('user/UserCredit_model', 'user_credit_model');
		$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');

		$this->author_earning_model->edit($info['id'], [
			'status' 			=> 1,
			'processing_by' 	=> -1,
			'processed_by' 		=> -1,
			'date_processing'	=> date('Y-m-d H:i:s'),
			'date_processed'	=> date('Y-m-d H:i:s'),
		]);

		$author_currency_code = get_author_currency_code($info['author_id']);

		if (empty($author_currency_code)) return;

		$info['amount'] = convert_to_local_currency($info['amount'], $info['author_id'], $info['currency_code']);

		$credit_info = $this->user_credit_model->getByUserId($info['author_id']);

		if (!empty($credit_info)) {
			/*$this->user_credit_model->edit($credit_info['id'], [
				'credit'	=> (double)($credit_info['credit'] + $info['amount']),
			]);*/
		} else {
			/*$this->user_credit_model->add([
				'currency_code'	=> $author_currency_code,
				'user_id'		=> (int)$info['author_id'],
				'credit'		=> (double)$info['amount'],
			]);*/
		}

		/*$this->user_credit_history_model->add([
			'type'					=> 1,
			'currency_code'			=> $author_currency_code,
			'user_id'				=> (int)$info['author_id'],
			'credit'				=> (double)$info['amount'],
			'order_id'				=> (int)$info['order_id'],
			'note'					=> (int)$info['id'],
		]);*/
	}

	public function sendUserCreditMsg() {
		return;

		$results = $this->db->query("
			SELECT book.id as book_id, book.user_id, book.name as book_name, book.author_name,
			users.mobile, users.email, order_product.order_id, order_product.quantity,
			author_earning.user_id as author_earning_user_id, author_earning.author_id as author_earning_author_id,
			SUM(author_earning.amount) as amount, `order`.status
			FROM `users`
			JOIN book on book.user_id=users.id
			JOIN order_product on order_product.product_id=book.id
			JOIN `order` on `order`.id=order_product.order_id AND `order`._deleted=0
			LEFT JOIN author_earning on order_product.product_id=author_earning.book_id AND order_product.order_id=author_earning.order_id AND book.user_id=author_earning.author_id
			WHERE users.state_id = '35' AND author_earning.amount IS NOT NULL
			GROUP BY book.user_id
		")->result_array();

		pr($results, 1);

		foreach ($results as $key => $item) {
			$mobile = '917303234240';
			// $mobile = '972546301878';

			if(ENVIRONMENT === 'production') {
				$mobile = $item['mobile'];
			}

			if(0 && $mobile) {
				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> '1306500200290599',
						'parameters'	=> []
					]
				);
			}
		}

		pr(count($results), 1);
	}

	public function getTopRankAuthorsIsraelCron($start = '', $limit = 1) {
		/*if(date('Ymd') > '20230329')
			return;*/

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [];
		$filter_data['site_code'] = 'ge-NWFIS';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 1;
		// $filter_data['end_date'] = '2023-05-17 23:30:00';

		$result = $this->ranking_model->getRanks($filter_data);
		pr($result, 1);
	}
}
