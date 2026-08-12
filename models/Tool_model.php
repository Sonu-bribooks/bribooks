<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tool_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function createCertificate($data = []) {
		$dir = FCPATH . 'uploads/certificate';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$image = imagecreatefrompng(FCPATH . 'uploads/icode.png');
		$darkgrey = imagecolorallocate($image, 70, 70, 70);
		$px = (imagesx($image) - 0 * strlen($data['program'])) / 2;

		$font_path = FCPATH . 'assets/global/fonts/MYRIADPRO-REGULAR.OTF';

		imagettftext($image, 32, 0, 273, 255, $darkgrey, $font_path, $data['name']);
		imagettftext($image, 8, 0, 590, 578, $darkgrey, $font_path, $data['date']);
		imagettftext($image, 20, 0, 510, 395, $darkgrey, $font_path, $data['score']);
		imagettftext($image, 30, 0, 273, 363, $darkgrey, $font_path, $data['program']);
		imagettftext($image, 8, 0, 445, 578, $darkgrey, $font_path, 'IC' . date('Y') . $data['otp']);

		imagepng($image, $dir . '/' . md5('user_' . $data['otp']) . '.png');
		imagedestroy($image);

		return md5('user_' . $data['otp']) . '.png';
	}

	public function createGlobalCertificate($data = [], $path = NULL) {
		$multiplier = 3.3;

		$dir = FCPATH . 'uploads/global_certificate';

		$dir .= ($path ? '/' . $path : '');

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/global_cert.png');

		$image 		= imagecreatefrompng(FCPATH . 'assets/images/global_cert.png');
		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$px 		= (imagesx($image) - 0 * strlen($data['program'])) / 2;

		$font_path = FCPATH . 'assets/global/fonts/Signika/Signika-VariableFont_wght.ttf';

		imagettftext($image, 20 * $multiplier, 0, 60 * $multiplier, 215 * $multiplier, $darkgrey, $font_path, $data['author_name']);
		imagettftext($image, 16 * $multiplier, 0, 160 * $multiplier, 265 * $multiplier, $darkgrey, $font_path, $data['book_name']);
		imagettftext($image, 20 * $multiplier, 0, 200 * $multiplier, 320 * $multiplier, $darkgrey, $font_path, $data['isbn']);
		imagettftext($image, 14 * $multiplier, 0, 130 * $multiplier, 370 * $multiplier, $darkgrey, $font_path, $data['date']);
		// imagettftext($image, 14 * $multiplier, 0, 100 * $multiplier, 530 * $multiplier, $grey, $font_path, $data['score']);
		// imagettftext($image, 10 * $multiplier, 0, ($image_width - (strlen($certificate_number) * $multiplier) - (255 * $multiplier)), ($image_height - (30 * $multiplier)), $grey, $font_path, $certificate_number);

		self::_addQrData($image, $data['qrdata'], $multiplier);

		imagepng($image, $dir . '/' . md5('user_' . $data['code']) . '.png');
		imagedestroy($image);

		return md5('user_' . $data['code']) . '.png';
	}

	private function _addQrData(&$image, $data = NULL, $multiplier = 1) {
		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$width = 512;
		$height = 512;

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=%sx%s&chl=%s', [
			$width,
			$height,
			urlencode($data),
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

		$qrpath = FCPATH . 'uploads/global_certificate/qr.png';

		// header('Content-type: image/jpeg');
		// header('Content-Disposition: attachment; filename=logo.png');
		// imagepng($qr_img);
		// exit;

		imagepng($qr_img, $qrpath);

		$qr_png = imagecreatefrompng($qrpath);
		$qr_png_width = imagesx($qr_png);
		$qr_png_height = imagesy($qr_png);

		$image_width = imagesx($image);
		$image_height = imagesy($image);

		imagecopyresampled(
			$image,
			$qr_png,
			($image_width - 250 - 280),
			($image_height - 250 - 120),
			0,
			0,
			250,
			250,
			$qr_png_width,
			$qr_png_height
		);

		// unlink($qrpath);
	}

	public function upload($name, $filename = '', $dir = 'uploads', $allowed_types = [], $encrypt = FALSE, $overwrite = FALSE) {
		ini_set('upload_max_filesize', '20M');
		ini_set('post_max_size', '20M');
		set_time_limit(0);

		if (!is_dir(FCPATH . $dir)) {
			mkdir(FCPATH . $dir, 0777, TRUE);
			chmod(FCPATH . $dir, 0777);
			@touch(FCPATH . $dir . '/' . 'index.html');
		}

		$config['upload_path']          = FCPATH . $dir;
		$config['allowed_types']        = $allowed_types ? $allowed_types : 'gif|jpg|png|jpeg';
		$config['max_size']             = 20480;
		$config['encrypt_name']         = $filename || !$encrypt ? FALSE : TRUE;
		$config['max_filename']         = 192;
		$config['overwrite']         	= $overwrite;
		$config['file_ext_tolower']     = TRUE;

		if ($filename) {
			$config['file_name']     	= $filename;
		}
		// $config['max_width']            = 1024;
		// $config['max_height']           = 768;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload($name)) {
			log_message('KB', 'upload error::' . $this->upload->display_errors());
			return ['error' => $this->upload->display_errors()];
		} else {
			return $this->upload->data();
		}
	}

	public function sms($mobile, $message) {
		if (empty($message)) return;

		if (in_array($mobile, TESTING_MOBILES)) {
			return;
		}

		$apiKey 	= urlencode(SMS['api_key']);
		$numbers 	= [$mobile];
		$sender 	= urlencode(SMS['sender']);
		$numbers 	= implode(',', $numbers);

		$data = [
			'apikey' 	=> $apiKey,
			'numbers' 	=> $numbers,
			'sender' 	=> $sender,
			'message' 	=> rawurlencode($message)
		];

		$ch = curl_init('https://api.textlocal.in/send/');

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		curl_close($ch);

		/*$output = json_decode($response, true);

		isset($output['status']) &&  $output['status'] === 'failure' && log_message(
			'KB',
			'SMS=>' . print_r($response, 1) . ' Mobile=>' . $mobile . ' Message=>' . $message
		);*/

		return $response;
	}

	public function whatsapp($destination = [], $message = []) {
		// $destination = [[
		// 		'waid'	=> [$lead_info['mobile']]
		// ]];
		//
		// $message = [
		// 	'template' 		=> '1208451082944248',
		// 	'parameters' 	=> [
		// 		'variable1' => $lead_info['parent_name'],
		// 		'variable2' => $lead_info['name'],
		// 		'variable3' => strpos(mb_strtolower($lead_info['course']), 'logical') !== false ? $lead_info['course'] : 'coding',
		// 		'variable4' => date('F j, Y', strtotime($lead_info['schedule'])),
		// 		'variable5' => date('h:i A', strtotime($lead_info['schedule'])),
		// 		'variable6' => $lead_info['mobile'],
		// 		'variable7' => $lead_info['telecaller_name'],
		// 		'variable8' => $lead_info['telecaller_mobile']
		// 	]
		// ];

		// if (ENVIRONMENT !== 'production') return;

		$service_key 	= WHATSAPP['SERVICE_KEY'];
		$appid 			= WHATSAPP['APP_ID'];

		$headers 		= [
			"Content-Type: application/json",
			"key: {$service_key}"
		];

		$data = [
			'appid' 			=> $appid,
			'deliverychannel' 	=> "whatsapp",
			'message' 			=> $message,
			'destination' 		=> $destination
		];

		$ch = curl_init('https://api.imiconnect.in/resources/v1/messaging');

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		curl_close($ch);

		$output = json_decode($response, true);

		log_message(
			'KB',
			'Whatsapp=> ' . print_r([
				'input' => $data,
				'output' => $output
			], 1)
		);

		return $output;
	}
}
