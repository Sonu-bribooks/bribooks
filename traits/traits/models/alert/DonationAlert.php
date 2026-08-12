<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait DonationAlert
{
	public function donationRequestCron($user_credit_request_id) {
		self::createDonationCertificate($user_credit_request_id);
	}

	public function createDonationCertificate($user_credit_request_id) {
		$this->load->model('user/UserCreditRequest_model', 'user_credit_request_model');
		$this->load->model('certificate/DonationCertificate_model', 'donation_certificate_model');

		if(empty($user_credit_request_info = $this->user_credit_request_model->get($user_credit_request_id)))
			return;

		if(empty($user_credit_request_info['type']) || ($user_credit_request_info['type'] != 2))
			return;

		if(empty($user_info = $this->student_model->get($user_credit_request_info['user_id'])))
			return;

		$donation_type = '';

		switch ($user_credit_request_info['donation_type']) {
			case '1':
				$donation_type = 'edesia';
				break;

			case '2':
				$donation_type = 'sunshine';
				break;

			default:
				break;
		}

		if(empty($donation_type))
			return;

		$image_name = $donation_type . '_foundation_certificate_' . $user_info['id'] . '_' . $user_credit_request_info['id'] . '.jpeg';

		// pr($image_name);

		$certificate_info = $this->donation_certificate_model->getByName($image_name);
		if(empty($certificate_info)) {
			$this->donation_certificate_model->add([
				'user_id'					=> $user_info['id'],
				'user_credit_request_id'	=> $user_credit_request_info['id'],
				'donation_type'				=> $user_credit_request_info['donation_type'],
				'name'						=> $image_name
			]);
		}

		// pr($certificate_info);

		$dir = FCPATH . 'uploads/donation_certificates';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/donation_certificates/'.$donation_type.'.jpg');

		$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/donation_certificates/'.$donation_type.'.jpg');
		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);

		$font_path = FCPATH . 'assets/global/fonts/MYRIADPRO-BOLD.OTF';

		$font_size = 60;
		$font_size_amount = 48;

		$student_name = strtoupper(trim($user_info['first_name'] . ' ' . $user_info['last_name']));
		$credit_amount = strtoupper($user_credit_request_info['currency_code'] . ' ' . $user_credit_request_info['credit']);

		$student_name_length = (int)((mb_strlen($student_name, 'utf-8') * ($font_size-7))/2) ?? 400;
		$credit_amount_length = (int)((mb_strlen($credit_amount, 'utf-8') * ($font_size_amount-7))/2) ?? 400;
		$user_credit_request_id_length = (int)((mb_strlen($user_credit_request_id, 'utf-8') * ($font_size-7))/2) ?? 400;

		$donation_id = strtoupper('#BBDN'. substr($donation_type, 0, 2) . $user_credit_request_id);

		imagettftext($image, $font_size, 0, 1700 - $student_name_length, 1400, $darkgrey, $font_path, $student_name);

		if ($donation_type == 'sunshine') {
			imagettftext($image, $font_size_amount, 0, 1800 - $credit_amount_length, 1615, $darkgrey, $font_path, $credit_amount);
		} else {
			imagettftext($image, $font_size_amount, 0, 2020 - $credit_amount_length, 1615, $darkgrey, $font_path, $credit_amount);
		}

		imagettftext($image, $font_size, 0, 2040 - $user_credit_request_id_length, 1780, $darkgrey, $font_path, $donation_id);
		imagettftext($image, $font_size, 0, 2025, 2220, $darkgrey, $font_path, date('d/m/Y'));

		imagejpeg($image, $dir . '/' . $image_name);

		imagedestroy($image);

		self::_generatePDFDonationCertificate($image_name);
		self::_uploadDonationCertificateS3($image_name);

		return $image_name;
	}

	private function _generatePDFDonationCertificate($file = '') {
		if(empty($file))
			return;

		$html = '<style>@page{margin:0;padding:0;}</style><img
			src="' . site_url('uploads/donation_certificates/') . $file . '"
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

		$dir = FCPATH . 'uploads/donation_certificates/pdf/';

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

	private function _uploadDonationCertificateS3($file = '') {
		if(empty($file))
			return;

		$path_info = pathinfo($file);

		$s3_dirname = 'authorcertificates';

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket($s3_dirname);

		$s3_dirname = $s3_dirname . '/donation_certificates';

		$s3_dirname = (ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . '/test';

		$this->s3_lib->put(FCPATH . 'uploads/donation_certificates/' . $path_info['filename'] . '.jpeg', $s3_dirname, false);
		$this->s3_lib->put(FCPATH . 'uploads/donation_certificates/pdf/' . $path_info['filename'] . '.pdf', $s3_dirname . '/pdf', false);

		unlink(FCPATH . 'uploads/donation_certificates/' . $path_info['filename'] . '.jpeg');
		unlink(FCPATH . 'uploads/donation_certificates/pdf/' . $path_info['filename'] . '.pdf');
	}
}
