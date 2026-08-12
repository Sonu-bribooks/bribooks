<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait NyafUs2023
{
	public function nyafUsSitePDF() {
		return;

		$dir = FCPATH . 'uploads/school_nominations/ge-nyafus/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/school_nominations/ge-nyafus/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->get_where('site', ['id>' => 2273])->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/NYAF_USA_LetterHead.jpg');
			$font_path = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';
			$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Regular.ttf';
			$font_path_light = FCPATH . 'assets/global/fonts/Poppins-Light.ttf';

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';

				$address = trim($result['address']);

				$nomination_code = 'NYAF' . sprintf('%04d', $result['id']);

				$image_name = $nomination_code . '.jpeg';

				$p = 'Thank you for applying to the National Young Authors’ Fair (NYAF). It is our pleasure to announce that ' . $result['name'] . ' has been officially selected to participate in this event.';

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

				$sn_length = strlen($result['name']);
				$p_length = strlen($p);

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/NYAF_USA_LetterHead.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$white 		= imagecolorallocate($image, 255, 255, 255);

				imagettftext($image, 40, 0, 190, 580, $darkgrey, $font_path_regular, 'To:');
				imagettftext($image, 40, 0, 190, 670, $darkgrey, $font_path_regular, $result['name']);
				imagettftext($image, 40, 0, 190, 760, $darkgrey, $font_path_regular, $address);

				imagettftext($image, 38, 0, 180, 880, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 180, 960, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 180, 1040, $darkgrey, $font_path_regular, $str3);
				}

				$str1 = $str2 = $str3 = '';

				$p = 'Making history is not an everyday occurrence, but together, we will break a world record, and ' . $result['name'] . ' will become a part of the biggest writing event in world history.';

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

				imagettftext($image, 38, 0, 180, 2920, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 180, 2990, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 180, 3060, $darkgrey, $font_path_regular, $str3);
				}

				imagettftext($image, 38, 0, 2050, 3450, $white, $font_path_regular, $nomination_code);

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				self::_letterheadQrCodeSchool($result['id']);

				self::_mergeImage($image_name, $result['id'].'.png');

				// pr($image, 1);
				self::_generateNyafUsCertificate($image_name);

				if (file_exists('uploads/school_nominations/ge-nyafus/pdf/'.$nomination_code.'.pdf')) {
					unlink(FCPATH . 'uploads/school_nominations/ge-nyafus/'.$nomination_code.'.jpeg');
					unlink(FCPATH . 'uploads/school_nominations/ge-nyafus/qrcodes/qrcode_'.$result['id'].'.png');
				}

				// return;
			}

			/*return;*/
		}
	}

	public function nyafUsSchoolPDF() {
		return;

		$dir = FCPATH . 'uploads/school_nominations/ge-nyafus/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/school_nominations/ge-nyafus/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->get_where('school_nominations_nyafus', ['status' => 0])->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/NYAF_USA_LetterHead.jpg');
			$font_path = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';
			$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Regular.ttf';
			$font_path_light = FCPATH . 'assets/global/fonts/Poppins-Light.ttf';

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';

				$address = trim($result['street'] . ', ' . $result['locality'] . ', ' . $result['region'] . ', ' . $result['state'] . ', ' . $result['zipcode']);

				$nomination_code = 'NYAF' . sprintf('%04d', $result['id']);

				$image_name = $nomination_code . '.jpeg';

				$p = 'Thank you for applying to the National Young Authors’ Fair (NYAF). It is our pleasure to announce that ' . $result['school_name'] . ' has been officially selected to participate in this event.';

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

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/NYAF_USA_LetterHead.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$white 		= imagecolorallocate($image, 255, 255, 255);

				imagettftext($image, 40, 0, 190, 580, $darkgrey, $font_path_regular, 'To:');
				imagettftext($image, 40, 0, 190, 670, $darkgrey, $font_path_regular, $result['school_name']);
				imagettftext($image, 40, 0, 190, 760, $darkgrey, $font_path_regular, $address);

				imagettftext($image, 38, 0, 180, 880, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 180, 960, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 180, 1040, $darkgrey, $font_path_regular, $str3);
				}

				$str1 = $str2 = $str3 = '';

				$p = 'Making history is not an everyday occurrence, but together, we will break a world record, and ' . $result['school_name'] . ' will become a part of the biggest writing event in world history.';

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

				imagettftext($image, 38, 0, 180, 2920, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 180, 2990, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 180, 3060, $darkgrey, $font_path_regular, $str3);
				}

				imagettftext($image, 38, 0, 2050, 3450, $white, $font_path_regular, $nomination_code);

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				self::_letterheadQrCodeSchool($result['id']);

				self::_mergeImage($image_name, $result['id'].'.png');

				// pr($image, 1);
				self::_generateNyafUsCertificate($image_name);

				$this->db->where('id', (int)$result['id']);
				$this->db->update('school_nominations_nyafus', [
					'status'		=> 1,
					'date_added'	=> date('Y-m-d H:i:s'),
				]);

				if (file_exists('uploads/school_nominations/ge-nyafus/pdf/'.$nomination_code.'.pdf')) {
					unlink(FCPATH . 'uploads/school_nominations/ge-nyafus/'.$nomination_code.'.jpeg');
					unlink(FCPATH . 'uploads/school_nominations/ge-nyafus/qrcodes/qrcode_'.$result['id'].'.png');
				}

				return;
			}

			/*return;*/
		}
	}

	private function _letterheadQrCodeSchool($code = '') {
		if (file_exists('uploads/school_nominations/ge-nyafus/qrcodes/qrcode_'.$code.'.png'))
			return base_url().'uploads/school_nominations/ge-nyafus/qrcodes/qrcode_'.$code.'.png';

		$dir = FCPATH . 'uploads/school_nominations/ge-nyafus/qrcodes';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = 'uploads/school_nominations/ge-nyafus/qrcodes/qrcode_' . $code . '.png';

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
			urlencode('https://www.yaf.bribooks.com/us/school/' . $code),
		]));

		$qr_img_width = imagesx($qr_img);
		$qr_img_height = imagesy($qr_img);

		imagecopyresampled(
			$qr_img,
			$logo,
			($qr_img_width / 2 - 150),
			($qr_img_height / 2 - 150),
			0,
			0,
			300,
			300,
			$logo_width,
			$logo_height
		);

		imagepng($qr_img, $file);

		return base_url($file);
	}

	private function _mergeImage($img1 = '', $img2 = '') {
		if(empty($img1) || empty($img2))
			return;

		$file = 'uploads/school_nominations/ge-nyafus/' . $img1;

		$file1 = imagecreatefromjpeg(FCPATH . 'uploads/school_nominations/ge-nyafus/' . $img1);
		$file2 = imagecreatefrompng(FCPATH . 'uploads/school_nominations/ge-nyafus/qrcodes/qrcode_' . $img2);

		imagecopyresampled(
			$file1,
			$file2,
			1895,
			2413,
			0,
			0,
			400,
			400,
			500,
			500
		);

		imagejpeg($file1, $file);
	}

	private function _generateNyafUsCertificate($file = '') {
		if(empty($file))
			return;

		$html = '<style>@page{margin:0;padding:0;}</style><img
			src="' . site_url('uploads/school_nominations/ge-nyafus/') . $file . '"
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

		$dir = FCPATH . 'uploads/school_nominations/ge-nyafus/pdf/';

		file_put_contents(
			$dir . $path_info['filename'] . '.pdf',
			$dompdf->output()
		);
	}

	public function testCommunicationKitTeacherPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/teacher';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_teacher', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}'
			],
			[
				$site_info['name'],
				USER_URL . 'us/studentv2/' . $site_info['id']
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/teacher/Communication_Kit_Teachers_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	public function testCommunicationKitParentPdf($site_id = false) {
		if(empty($site_id) || empty($site_info = $this->site_model->get($site_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent', [], true);

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}'
			],
			[
				$site_info['name'],
				USER_URL . 'us/studentv2/' . $site_info['id'],
				_generate_qr_code($site_info['id'], 'uploads/communication_kit/qrcodes')
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'.$site_info['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

	public function getNyafUsDirectStudent() {
		return;

		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('event/EventUser_model', 'event_user_model');

		$event_id = 9;

		$users = $this->student_model->get_all([
			'location'			=> 'United States',
			'source'			=> 'signup',
			'startdate'			=> '2023-09-14',
			'enddate'			=> '2023-12-17',
			'email_verified'	=> '1'
		])['rows'] ?? [];

		foreach ($users as $key => $student_info) {
			// add to event
			if (0 &&
				($user_id = $student_info['id']) &&
				empty($this->event_user_model->getEventUserByUserId($event_id, $user_id))
			) {
				$this->event_user_model->add([
					'event_id'	=> (int)$event_id,
					'user_id'	=> (int)$user_id,
				]);

				$this->student_model->edit($user_id, [
					'site_id'			=> 2270,
					'source'			=> 'ge-NYAFUS-de',
				]);

				$this->lead_model->editByStudentId($user_id, [
					'event_id'			=> $event_id,
					'site_id'			=> 2270,
					'source'			=> 'ge-NYAFUS-de',
				]);

				// pr($user_id, 1);
			}
		}

		pr($users, 1);
	}

	public function deadlineExtendedForNyafUs() {
		return;

		if (ENVIRONMENT !== 'production') return;

		$this->load->model('event/EventUser_model', 'event_user_model');

		$event_id = 9;

		if (!empty($event_user_results = $this->event_user_model->get_all([
			'event_id'	=> $event_id,
			'sort'		=> 'event_user.id',
			'order'		=> 'ASC',
		])['rows'])) {
			foreach ($event_user_results as $event_user_info) {
				$user_info = $this->student_model->get($event_user_info['user_id']);

				$site_info = $this->site_model->get($user_info['site_id']);

				$full_name = ucfirst(trim($user_info['first_name']));
				// pr($user_info, 1);

				$subject = 'Important: Deadline Extended for NYAF';

				$content = '<p>Dear <strong>'.$full_name.'</strong>,</p>
<p>I wanted to update you on a crucial development regarding the National Young Authors’ Fair (NYAF) publishing deadline. Initially set for November 17th, 2023, we\'ve decided, upon multiple requests, to extend the deadline provisionally to <strong>November 26th, 2023</strong>.</p>
<p>Our aim is to ensure every student gets the chance to become a globally recognized author.<br />I hope this extension gives you ample time to finalize your book and reach your goals.</p>
<p>Your potential as a celebrated author from <strong>'.$site_info['name'].'</strong> is immense. Let’s etch your success in history with your published book.</p>
<p>For any inquiries or assistance, please reach out to us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>.</p>
<p>Best Regards,<br />Ami Dror</p>';

				if(ENVIRONMENT == 'production') {
					$mobile = $user_info['mobile'];
					$email = $user_info['email'];
				}

				$mobile && $this->alert_model->sms(
					$mobile,
					"Hi $full_name,\n\nGood news! The National Young Author's Fair book deadline is extended to Nov 26, 2023.\n\nHappy Writing\nTeam BriBooks",
					"vonage"
				);
			}
		}
	}

	public function introducingLeagueForNyafUs() {
		return;

		$results = $this->db->query("
			SELECT users.id AS user_id, users.first_name, users.mobile
			FROM `users`
			JOIN `event_user` on event_user.user_id=users.id
			WHERE `event_user`.`event_id` = 9
			AND users.mobile != ''
		")->result_array();

		pr($results, 1);
		foreach ($results as $user_info) {
			$user_id = $user_info['user_id'];
			$first_name = ucfirst(trim($user_info['first_name']));

			$mobile = '16039304464';

			if(0 && ENVIRONMENT == 'production') {
				$mobile = $user_info['mobile'];
			}

			/*$mobile && $this->alert_model->sms(
				$mobile,
				"Hey $first_name,\n\nIntroducing the National Best-Sellers League for Young Authors. Check your rank here: https://www.yaf.bribooks.com/us/dashboard?trid=$user_id",
				"vonage"
			);*/
		}
	}
}
