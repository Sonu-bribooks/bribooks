<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait SiteDailyExport {
	public function dailySiteReportPdf($site_id = '') {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/Section_model', 'section_model');

		$data['site_info'] = $this->site_model->get($site_id);

		$data['total_registered'] = $this->student_model->get_all([
			'site_id' => $site_id
		])['total'] ?? 0;

		$grades = $this->grade_model->get_all([
			'site_id' 	=> $site_id,
			'sort'		=> 'site_grade.name',
			'order'		=> 'ASC',
		])['rows'] ?? [];

		$data['grades']	= [];

		foreach ($grades as $grade) {
			$sections = $this->section_model->get_all([
				'grade_id' 	=> $grade['id'],
				'sort'		=> 'site_section.name',
				'order'		=> 'ASC',
			])['rows'] ?? [];

			$section_data = [];

			foreach ($sections as $section) {
				$reg_students = $this->student_model->get_all([
					'section_id' 	=> $section['id'],
					'grade_id' 		=> $grade['id'],
				])['total'];

				if (!$reg_students) continue;

				$book_written = $this->book_model->get_all([
					'section_id' 	=> $section['id'],
					'grade_id' 		=> $grade['id'],
				])['total'];

				$book_published = $this->book_model->get_all([
					'section_id' 	=> $section['id'],
					'grade_id' 		=> $grade['id'],
					'ne_status' 	=> 0,
				])['total'];

				$section_data[] = [
					'name'				=> $section['name'],
					'reg_students'		=> $reg_students,
					'book_written'		=> $book_written,
					'book_published' 	=> $book_published,
				];
			}

			$data['grades'][] = [
				'name'		=> $grade['name'],
				'sections'	=> $section_data,
			];
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/school_pdf_template', $data, true);
		$dompdf = new Dompdf();

		// // // Load HTML content
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		// $dompdf->stream();

		$file = 'uploads/pdfs/daily_report_' . $site_id . '.pdf';
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return $file;

		// $this->load->view('frontend/' . get_frontend_settings('theme') . '/school_pdf_template', $data);
	}

	public function dailySiteStudentReportPdf($site_id = 0, $event_id = 0) {
		$this->load->library('Common_lib', 'common_lib');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/User_model', 'user_model');

		$site_info = $this->site_model->get($site_id);

		if (empty($site_info)) return;

		$results = $this->user_model->get_all([
			'site_id' 	=> $site_id,
			'role_id' 	=> 9,
			'status' 	=> 1
		])['rows'] ?? [];

		if (empty($user_id = $results[0]['id'])) return;

		$data = $this->common_lib->getGradeWiseData($user_id, $event_id);

		$html = $this->load->view('common/report/grade_wise_indian_student_pdf', $data, true);
		$new_data = $this->common_lib->getSchoolDashboardReport($site_id, $event_id);
		$html .= $this->load->view('common/report/student_pdf', $new_data, true);

		if (empty($html)) return;

		$dompdf = new Dompdf();
		// Load HTML content
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file 	= 'uploads/pdfs/daily_report_' . $site_id . '.pdf';
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return $file;
	}

	public function dailySiteReportIsrael() {
		if (ENVIRONMENT == 'production') {
			$this->load->model('school/SchoolLead_model', 'school_lead_model');
			$this->load->model('school/SchoolInput_model', 'schoolinput_model');

			$filter_data = [];
			$filter_data['country_id'] = 114;
			$filter_data['has_registered'] = 1;

			$results = $this->schoolinput_model->get_all($filter_data);

			$school_input = [];

			foreach ($results['rows'] ?? [] as $key => $result) {
				$school_info = $this->school_lead_model->getBySchoolId($result['id']);
				$city_info = $this->city_model->get($result['city_id']);

				$school_input[] = [
					'city'				=> $city_info['name'] ?? '',
					'school_name'		=> $result['name'] ?? '',
					'school_registered'	=> !empty($school_info['id']) ? 'Yes' : 'No'
				];
			}

			$attachment = self::_downloadCsv(array_values($school_input), 'school_lead_');

			self::email(
				'israel@bribooks.com',
				'Daily School Registration Report',
				'Daily School Registration Report',
				[],
				[],
				FCPATH . $attachment
			);

			unlink(FCPATH . $attachment);
		}
	}

	public function dailySchoolPocIsrael() {
		if (ENVIRONMENT == 'production') {
			if(time() > 1677745815)
				return;

			$this->load->model('school/SchoolLead_model', 'school_lead_model');
			$this->load->model('user/Lead_model', 'lead_model');

			$filter_data = [];
			$filter_data['state_id'] = 35;
			$filter_data['mobile_verified'] = 1;

			$schools = $this->school_lead_model->get_all($filter_data);

			$content = '<p>Dear {name},</p>
<p></p>
<p>Thank you for registering to Israel’s first National English Writing Festival. Beyond the actual writing, the key moments are when a student gets to physically hold a copy of the printed book, present the book to parents and friends, and hand a copy to the school library.</p>
<p></p>
<p>Event Schedule:</p>
<ul>
<li>
<p>Starting today, as your school is fully registered, you can send the writing assignment to the students. <strong>It is highly recommended that the book writing assignment will be defined as homework for the Passover/Spring break</strong> and announced as early as possible. The optimal ages are 5th grade (one sentence per page) to the 12th grade (full pages).</p>
</li>
<li>
<p><strong>Please find the attached draft announcement that you should share with the other teachers in your school</strong></p>
</li>
<li>
<p><strong>Please find the attached homework draft assignment</strong> that you and the English teachers should share with your students/parents via Email, Teams, and Whatsapp.</p>
</li>
<li>
<p>It\'s also highly recommended to share this information with the parents via whatsapp. <strong>Please find a short text in Hebrew for the parents to be sent via the parents WhatsApp groups.</strong></p>
</li>
<li>
<p>The last day of publishing is 22.4.23.</p>
</li>
<li>
<p>During the last month of the school year, exhibitions will be held in schools where the students will present the books, autographed copies, and add the books to the school library. In addition, a national event will be held by the ministry of education, and certificates of excellence will be awarded to the outstanding writers.</p>
</li>
</ul>
<p></p>
<p>Thank you for joining this mega event and being part of history in making! We are here to help with any questions you may have.</p>
<p></p>
<p>Team BriBook</p>
<p>israel@bribooks.com</p>
<p></p>
<p></p>';

			foreach ($schools['rows'] ?? [] as $school) {
				$message = str_replace('{name}', $school['authorized_person'], $content);

				$this->alert_model->email(
					$school['email'],
					'[Important Update] Student Registration Details for National English Writing Festival',
					$message,
					[],
					[]
				);
			}
		}
	}

	public function dailySchoolReportIsrael() {
		if (ENVIRONMENT == 'production') {
			$this->load->model('school/SchoolLead_model', 'school_lead_model');
			$this->load->model('user/Lead_model', 'lead_model');

			$filter_data = [];
			$filter_data['state_id'] = 35;
			$filter_data['mobile_verified'] = 1;

			$schools = $this->school_lead_model->get_all($filter_data);

			$content = '<p>Dear {name},</p>
<p></p>
<p>Thank you for your leadership in representing {school_name} in the National English Writing Festival.</p>
<p>Please find the student registration report attached along with this email for {school_name} for {date}.</p>
<p>Please share the student registration link https://www.yaf.bribooks.com/israel/student with all the students from grades 5-10 in your school.</p>
<p>Participation in the National English Writing Festival is COMPLETELY FREE for all students.</p>
<p>If you face any issues while registering, feel free to connect with us at support@bribooks.com</p>
<p></p>
<p>All the best!</p>
<p></p>
<p>Regards</p>
<p>Team BriBooks</p>';

			foreach ($schools['rows'] ?? [] as $school) {
				$school_grades = !empty($school['grades']) ? json_decode($school['grades'], 1) : '';
				sort($school_grades);

				$message = str_replace(['{name}','{school_name}','{date}'], [$school['authorized_person'],$school['name'],date('d.m.y')], $content);

				$filter_data = [];
				$filter_data['site_id'] = $school['site_id'];
				$filter_data['mobile_verified'] = 1;

				$leads = $this->lead_model->getCountByGrade($filter_data);

				$school_csv = [];

				foreach ($leads as $lead) {
					if(empty($lead['grade_name']))
						continue;

					$school_csv[$lead['grade_name']] = $lead['count'] ?? 0;
				}

				$gradesArr = [];

				foreach ($school_grades as $grade) {
					$gradesArr[] = [
						'grade' => $grade,
						'count' => !empty($school_csv[$grade]) ? $school_csv[$grade] : 0
					];
				}

				$attachment = self::_downloadCsv(array_values($gradesArr), 'lead_');

				self::email(
					$school['email'],
					'Daily Student Registration Report',
					'Daily Student Registration Report',
					[],
					[],
					FCPATH . $attachment
				);

				unlink(FCPATH . $attachment);
			}
		}
	}

	public function incompleteOrdersCron() {
		if (ENVIRONMENT == 'production') {
			$this->load->model('order/Order_model', 'order_model');

			$filter_data = [];
			$filter_data['ne_status'] = [3,4,91,92];
			$filter_data['startdate'] = date('2022-06-01');
			$filter_data['enddate'] = date('Y-m-d', strtotime('-10 days'));
			$filter_data['order_type'] = [1,2];

			$results = $this->order_model->searchProductName($filter_data)['rows'] ?? [];

			if(empty($results))
				return;

			$orders = [];

			$sn = 1;

			$this->load->model('user/Student_model', 'student_model');
			$this->load->model('address/Address_model', 'address_model');
			$this->load->model('user/User_model', 'user_model');
			$this->load->model('book/PageVersion_model', 'page_version_model');

			foreach ($results as $order) {
				$student_info = $this->student_model->get($order['user_id']);

				$products = $this->order_model->getProducts($order['id']);

				$address_info = $this->address_model->getByID($order['address_id']);

				$address = !empty($address_info) ? vsprintf('%s, %s, %s, %s, %s, %s, %s, - %s - %s', [
					$address_info['name'],
					$address_info['mobile'],
					$address_info['address'],
					$address_info['landmark'],
					$address_info['city'],
					$address_info['state'],
					$address_info['country'],
					$address_info['zipcode'],
					$address_info['type'],
				]) : '';

				$total = round($order['total'], 2);

				$printer_info = $this->user_model->get($order['assign_printer_id']);

				$shipping_info = json_decode($order['shipping_info'], true);

				foreach ($products as $key => $product) {
					$option = json_decode($product['option'], true);

					if (strtolower($option['name']) === 'ebook') continue;

					$total_pages 	= $this->page_version_model->get_all([
						'book_id'	=> $product['product_id'],
						'version'	=> $product['version'],
					])['total'] ?? 0;

					$orders[] = [
						'order_id'		=> $order['id'],
						'region'		=> strtolower($order['currency_code']) === 'inr'
							? _l('domestic')
							: _l('global'),
						'order_code'	=> $order['order_code'],
						'book_name'		=> $product['name'],
						'version'		=> $product['version'],
						'sku'			=> _o_b_code($product['product_id'], $product['version'], $option['name']),
						'option'		=> $option['name'],
						'pages'			=> $total_pages * 2 + 1,
						'author_name'	=> $product['author_name'],
						'status'		=> _os($order['status']),
						'quantity'		=> $product['quantity'],
						'buyer_name'	=> $address_info['name'],
						'buyer_mobile'	=> $address_info['mobile'],
						'buyer_email'	=> $student_info['email'],
						'address'		=> $address,
						'currency_code'	=> $order['currency_code'],
						'total'			=> $key == 0 ? $total : 0,
						'weight'		=> $product['weight'] . 'gm',
						'printer'		=> $printer_info['first_name'] ?? '',
						'shipping_info'	=> $shipping_info['courier_name'] ?? '',
						'date_added'	=> $order['date_added'],
					];

					$sn++;
				}
			}

			$attachment = self::_downloadCsv(array_values($orders), 'orders_');

			self::email(
				'adarsh@bribooks.com',
				'Incomplete Orders Report - ' . date('d/M/Y'),
				'Hi,<br />Please find the attached file as Incomplete Orders:',
				['rahul@bribooks.com'],
				[],
				FCPATH . $attachment
			);

			unlink(FCPATH . $attachment);
		}
	}

	private function _downloadCsv($results = [], $filename = 'download_') {
		$csv_path = 'uploads/csv';

		if (!is_dir($csv_path)) {
			mkdir($csv_path, 0777, TRUE);
			chmod($csv_path, 0777);
			@touch($csv_path . '/' . 'index.html');
		}

		$filename = $csv_path . '/' . $filename . date('Y_m_d_H_i_s') . '.csv';

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		$fp = fopen($filename, 'w');

		self::_writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		return $filename;
	}

	private function _writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}
}
