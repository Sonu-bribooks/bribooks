<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('whatsapp');

trait NyafInd2023
{
	use CommonWhatsapp;

	public function nyafInSitePDF() {

		// $letterHeads = $this->db->get('letterhead_site')->result_array();
		// pr($letterHeads,1);
		// echo "nyafInSitePDF";die;

		$dir = FCPATH . 'uploads/school_nominations/in-nyaf23/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/school_nominations/in-nyaf23/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->get('letterhead_site')->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/NYAF_IN_LetterHead.jpg');
			$font_path = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';
			$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Regular.ttf';
			$font_path_light = FCPATH . 'assets/global/fonts/Poppins-Light.ttf';

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';

				$address = trim($result['city']);

				$nomination_code = 'NYAF' . sprintf('%04d', $result['id']);

				$image_name = $nomination_code . '.jpeg';

				// $p = 'Thank you for applying to the National Young Authors’ Fair (NYAF). It is our pleasure to announce that ' . $result['school_name']. ' has been officially selected to participate in this event.';
				$p = 'We are happy to invite, '.$result['school_name'].', in the 2023 edition of National Young Authors’ FairTM - the world’s largest book writing competition for school students.';

				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 80) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 80) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 80) {
						$str3 .= ' ' . $school;
					}
				}

				$sn_length = strlen($result['school_name']);
				$p_length = strlen($p);

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/NYAF_IN_LetterHead.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$white 		= imagecolorallocate($image, 255, 255, 255);

				imagettftext($image, 40, 0, 190, 580, $darkgrey, $font_path_regular, 'To:');
				imagettftext($image, 40, 0, 190, 670, $darkgrey, $font_path_regular, $result['owner']);
				imagettftext($image, 40, 0, 190, 760, $darkgrey, $font_path_regular, $address);

				imagettftext($image, 38, 0, 180, 880, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 180, 960, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 180, 1040, $darkgrey, $font_path_regular, $str3);
				}

				// $str1 = $str2 = $str3 = '';

				// $p = 'We are happy to invite, '.$result['school_name'].', in the 2023 edition of National Young Authors’ FairTM - the world’s largest book writing competition for school students.';

				// $school_arr = explode(" ", $p);
				// foreach ($school_arr as $school) {
				// 	if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 80) {
				// 		$str1 .= ' ' . $school;
				// 	} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 80) {
				// 		$str2 .= ' ' . $school;
				// 	} else if((strlen($str3) + strlen($school)) < 80) {
				// 		$str3 .= ' ' . $school;
				// 	}
				// }

				// imagettftext($image, 38, 0, 180, 2920, $darkgrey, $font_path_regular, $str1);
				// imagettftext($image, 38, 0, 180, 2990, $darkgrey, $font_path_regular, $str2);

				// if($str3) {
				// 	imagettftext($image, 38, 0, 180, 3060, $darkgrey, $font_path_regular, $str3);
				// }

				// imagettftext($image, 38, 0, 2050, 3450, $white, $font_path_regular, $nomination_code);

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				// self::_letterheadQrCodeSchool($result['id']);

				// self::_mergeInImage($image_name, $result['id'].'.png');

				// pr($image, 1);
				// self::_generateNyafInCertificate($image_name);

				// if (file_exists('uploads/school_nominations/in-nyaf23/pdf/'.$nomination_code.'.pdf')) {
				// 	unlink(FCPATH . 'uploads/school_nominations/in-nyaf23/'.$nomination_code.'.jpeg');
				// 	unlink(FCPATH . 'uploads/school_nominations/in-nyaf23/qrcodes/qrcode_'.$result['id'].'.png');
				// }

				echo "letterHead";die;;
			}

			/*return;*/
		}
	}

	private function _mergeInImage($img1 = '', $img2 = '') {
		if(empty($img1) || empty($img2))
			return;

		$file = 'uploads/school_nominations/in-nyaf23/' . $img1;

		$file1 = imagecreatefromjpeg(FCPATH . 'uploads/school_nominations/in-nyaf23/' . $img1);
		// $file2 = imagecreatefrompng(FCPATH . 'uploads/school_nominations/in-nyaf23/qrcodes/qrcode_' . $img2);

		// imagecopyresampled(
		// 	$file1,
		// 	$file2,
		// 	1895,
		// 	2413,
		// 	0,
		// 	0,
		// 	400,
		// 	400,
		// 	500,
		// 	500
		// );

		imagejpeg($file1, $file);
	}

	private function _generateNyafInCertificate($file = '') {
		if(empty($file))
			return;

		$html = '<style>@page{margin:0;padding:0;}</style><img
			src="' . site_url('uploads/school_nominations/in-nyaf23/') . $file . '"
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

		$dir = FCPATH . 'uploads/school_nominations/in-nyaf23/pdf/';

		file_put_contents(
			$dir . $path_info['filename'] . '.pdf',
			$dompdf->output()
		);
	}

	public function generateNyafInSchoolReport() {
		return;

		/*self::_sendWhatsappDocument(
			'919716120257',
			[
				'template'		=> '1535804283840040',
				'parameters'	=> [
					'authorized_person',
					'school_name',
					'total_registered',
					'total_published',
					'school_name'
				],
				'document'	=> [
					'name'	=> 'daily_report_438',
					'link'	=> 'https://cms.bribooks.com/uploads/pdfs/daily_report_438.pdf'
				]
			],
		);

		return;*/

		$this->load->model('common/Cron_model', 'cron_model');

		$this->load->library('Common_lib', 'common_lib');

		$results = $this->db->query("
			SELECT `event_site`.event_id, `users`.`site_id`, `site`.`name` as school_name,
			`site`.authorized_person, `site`.owner_email, `site`.owner_mobile,
			COUNT(`users`.`id`) as total_students, `site`.`site_code`, `site`.`verified`,
			(SELECT id FROM `users` WHERE email=`site`.`owner_email` AND role_id=9 AND _deleted=0 LIMIT 1) as user_id
			FROM `users`
			JOIN `site` on `site`.`id`=`users`.`site_id`
			JOIN `event_site` on `event_site`.`site_id`=`site`.`id`
			WHERE `site`.`id` NOT IN (1,265,727,71588)
			AND `site`.`verified`=1
			AND `site`.`_deleted` = 0
			AND `users`.`_deleted` = 0
			AND `event_site`.event_id=10
			GROUP BY `users`.`site_id`
			having total_students >= 10
			ORDER BY total_students DESC
		")->result_array();

		// pr($results, 1);
		foreach ($results as $site_info) {
			// pr($site_info);

			$data = $this->common_lib->getGradeWiseData($site_info['user_id'], $site_info['event_id']);
			// pr($data, 1);

			$html = $this->load->view('common/report/grade_wise_indian_student_pdf', $data, true);
			$new_data = $this->common_lib->getSchoolDashboardReport($site_info['site_id'], $site_info['event_id']);
			$html .= $this->load->view('common/report/student_pdf', $new_data, true);

			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$output = $dompdf->output();

			$attachment = sprintf('uploads/pdfs/event_report_%s_%s_%s.pdf', time(), (int)$site_info['user_id'], (int)$site_info['event_id']);

			file_put_contents(FCPATH . $attachment, $output);

			if(0 && ENVIRONMENT === 'production') {
				$email = $site_info['owner_email'];
				$mobile = $site_info['owner_mobile'];
			}

			$subject = 'Important Update: National Young Authors’ Fair 2023 - Student Publishing Data';

			$content = '<p>Dear '.$site_info['authorized_person'].'!</p>
<p>We hope this message finds you well. We would like to bring to your attention an critical update regarding the book publishing data for National Young Authors’ Fair 2023 related to ' . $site_info['school_name'] . '.</p>
<p>Total Students Registered: ' . $data['total_registered'] . '</p>
<p>Total Published Authors: ' . $data['total_published'] . '</p>
<p>Enclosed herewith is the grade-wise publishing data, including the names of students yet to publish their work.</p>
<p>To enhance ' . $site_info['school_name'] . '\'s standing in the Literary Leadership Awards, it is crucial for all registered students to engage in book writing and publishing.</p>
<p>We are pleased to inform you that, in addition to the existing option available on Laptop/Desktops/Tabs, we now offer an Android App. This development ensures that students can enjoy a delightful writing experience on the App.</p>
<p>The App is available on Google Play Store at<br /><a href="https://play.google.com/store/apps/details?id=com.bribooks">https://play.google.com/store/apps/details?id=com.bribooks</a></p>
<p>Should you have any inquiries or require further clarification, please do not hesitate to reach out to us at <a href="mailto:schools@bribooks.com">schools@bribooks.com</a>.</p>
<p>Best Regards,</p>
<p>Publishing Team,<br />National Young Authors’ Fair (India)</p>';

			$this->alert_model->email(
				$email,
				$subject,
				$content,
				[],
				[],
				$attachment
			);

			!empty($mobile) && self::_sendWhatsappDocument(
				$mobile,
				[
					'template'		=> '1535804283840040',
					'parameters'	=> [
						$site_info['authorized_person'],
						$site_info['school_name'],
						$data['total_registered'],
						$data['total_published'],
						$site_info['school_name']
					],
					'document'	=> [
						'name'	=> sprintf('event_report_%s_%s_%s.pdf', time(), (int)$site_info['user_id'], (int)$site_info['event_id']),
						'link'	=> base_url($attachment)
					]
				]
			);

			die;
		}
	}
}
