<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait ImportAuthorWall {
	private function _importAuthorWall($rows = [], $map = [], $job_id = 0) {
		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$skipped = $uploaded = 0;
		$headers = [];
		$front_page = $page_1 = $page_2 = $page_3 = $page_4 = $page_5 = $page_6 = null;
		$start	 = $end = null;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($headers)) {
				$headers = array_map('trim', array_keys($row ?? []));
			}

			if (empty($data['id'])) continue;

			if (!isset($start)) {
				$start = $index;
			}

			$end = $index;

			self::_updateCounter($job_id);

			self::_generateAuthorWall($data, $index);

			$uploaded++;
		}

		$this->s3_lib->setBucket('bbpdfenginefiles');
		$zip_data = $this->zip->get_zip();

		if (!empty($zip_data)) {
			$s3_filename = $this->s3_lib->putData(
				sprintf('authorwall_%s_%s.zip', $start, $end),
				sprintf('%sauthorwall_%s/%s', (ENVIRONMENT === 'production' ? '' : 'test'), date('Y'), $job_id),
				$zip_data,
				false
			);
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	public function _generateAuthorWall($data = [], $index = 0) {
		$wall_info = $this->author_wall_model->get($data['id']);

		log_kb(['_generateAuthorWall' => [$data, $wall_info]]);

		$this->s3_lib->setBucket('bbprivateimagesin');

		$book_info = $this->book_version_model->getByVersion($wall_info['book_id'], $wall_info['version'] ?? 1);

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

		$data['qrcode'] 			= base_url(self::_getAuthorWallQrCode($book_info));
		$data['barcode'] 			= self::_getAuthorWallBarcode(!empty($book_info['isbn']) ? $book_info['isbn'] : $book_info['unique_id']);

		$html 						= $this->load->view(sprintf('common/author_wall/index'), $data, true);


		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper(
			[
				0,
				0,
				$data['width'],
				$data['height']
			],
			'portrait'
		);

		$dompdf->render();
		$pdf_data = $dompdf->output();

		$filename = vsprintf('author_wall_%s_%s_%s.pdf', [
			$book_info['slug'],
			$wall_info['book_id'],
			$data['id'],
		]);

		$this->zip->add_data($filename, $pdf_data);
	}

	private function _getAuthorWallBarcode($data = 0) {
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

	private function _getAuthorWallQrCode($book_info = [], $size = 60) {
		$file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		return generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, $file);
	}
}
