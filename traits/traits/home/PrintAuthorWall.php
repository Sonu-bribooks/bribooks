<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait PrintAuthorWall {
	public function printAuthorWall($book_id = 0, $download = true) {
		$wall_info = $this->db->get_where('author_wall', [
			'book_id' => (int)$book_id,
		])->row_array();

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('bbprivateimagesin');

		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');

		$book_info = $this->book_version_model->getByVersion($wall_info['book_id'], $wall_info['version'] ?? 1);

		if (empty($book_info)) {
			exit('book not found');
			return;
		}

		$book_info['name']					= $wall_info['book_name'];
		$book_info['author_name']			= $wall_info['author_name'];
		$book_info['book_desc'] 			= $wall_info['about_the_book'];
		$book_info['event_id'] 				= $wall_info['event_id'];
		$book_info['rank'] 					= $wall_info['type'] . '-' . $wall_info['book_rank'];

		if (!empty($wall_info['author_image'])) {
			$book_info['author_front_image'] = str_replace('https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/', '', $wall_info['author_image']);
		} else {
			$dir_name = (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test');
			$invite_guest_info = $this->event_user_invite_model->get_all([
				'book_id'	=> $wall_info['book_id'],
				'event_id'	=> $wall_info['event_id'],
			])['rows'][0] ?? [];
			$book_info['author_front_image'] = $this->s3_lib->getUrl($invite_guest_info['author_image'], $dir_name, false, 30);
			$book_info['full_url'] = true;
		}

		$multiplier = 0.75;
		// $multiplier = 0.25;

		$data['width'] 				= 3460 * $multiplier;
		$data['height'] 			= 9216 * $multiplier;
		$data['padding']			= 420 * $multiplier;
		$data['gap']				= [
			'author_image' 			=> [
				'h' => 175 * $multiplier,
				'v' => 250 * $multiplier,
			],
			'about_book' 			=> [
				'v' => 145 * $multiplier,
			],
			'cover_image' 			=> [
				'v' => 350 * $multiplier,
				'h'	=> 160 * $multiplier,
			],
		];
		$data['font_size']			= [
			'author_name'			=> 200 * $multiplier / (strlen($book_info['author_name']) > 15 ? 1.5 : 1),
			'author_of'				=> 100 * $multiplier,
			'book_name'				=> 190 * $multiplier / (strlen($book_info['name']) > 15 ? 1.5 : 1),
			'about_book'			=> 100 * $multiplier,
			'qr_code'				=> 180 * $multiplier,
			'tag'					=> 150 * $multiplier,
		];
		$data['image_size']			= [
			'author_image'	=> 680 * $multiplier,
			'cover_image_w'	=> 2280 * $multiplier,
			'cover_image_h'	=> 1680 * $multiplier,
			'qr_image'		=> 1250 * $multiplier,
		];
		$data['multiplier']			= $multiplier;
		$data['backcover']			= [
			'book_name'			=> (strlen($book_info['name']) > 15 ? 57 : 84) * $multiplier,
			'author_name'		=> (strlen($book_info['author_name']) > 10 ? 32 : 42) * $multiplier,
			'author_image_font'	=> (strlen($book_info['author_name']) > 10 ? 24 : 36) * $multiplier,
			'author_bio'		=> 30 * $multiplier,
		];

		$data['book'] 				= $book_info;
		$data['book_code'] 			= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');

		$data['qrcode'] 			= base_url(self::_getPrintWallQrCode($book_info));
		$data['barcode'] 			= self::_getPrintWallBarcode(!empty($book_info['isbn']) ? $book_info['isbn'] : $book_info['unique_id']);

		$html = $this->load->view('frontend/default/print_author_wall', $data, true);
		// echo $html;
		// die;

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
			$dompdf->stream(
				'author_wall_' . str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf',
				// ['Attachment' => false]
			);
		} else {
			return $dompdf->output();
		}
	}

	private function _getPrintWallBarcode($data = 0) {
		$data = str_replace(['-', ' '], '', $data);
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			480 * 3.6,
			120 * 3.6,
			'black',
			array(15, 15, 0, 15)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();
	}

	private function _getPrintWallQrCode($book_info = [], $size = 60) {
		$file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		return generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, $file);
	}
}
