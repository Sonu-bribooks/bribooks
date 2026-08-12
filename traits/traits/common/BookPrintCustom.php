<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait BookPrintCustom {
	private function _getBarcode($data = 0) {
		$data = str_replace(['-', ' '], '', $data);
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			160 * 3.6,
			40 * 3.6,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();

		file_put_contents(FCPATH . $file, $bobj->getPngData());
		return $file;
	}

	private function _getQrCode($book_info = [], $size = 60) {
		$file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		return generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, $file);

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
			urlencode(USER_URL . 'bookstore/' . $book_info['slug']),
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

		return $file;
	}

	public function printAuthorBook($book_id = 0, $version = 0) {
		self::printBook($book_id, $version, true);
	}

	public function printBookSingle($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$book_original_info = $this->book_model->get($book_id);
			$data['author_copy'] = $author_copy;

			// $data['multiplier'] = $multiplier = 432 / 285;
			$data['multiplier'] = 432 / 285;
			$data['bleed'] = 24;
			$data['fc_bleed'] = 24;
			$multiplier = 468 / 285;
			$cover_info = !empty($book_info['cover_id'])
				? $this->cover_model->get($book_info['cover_id'])
				: [];
			$heading_style = !empty($cover_info['heading_style'])
				? json_decode($cover_info['heading_style'], true)
				: [];
			$data['cover_info'] = $cover_info;
			$data['heading_style'] = !empty($heading_style['style'])
				? $heading_style['style']
				: [];

			$data['pages'] = $this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'];

			$data['book_original_info'] = $book_original_info;
			$data['width'] 		= 285 * $multiplier;
			$data['height'] 	= 427.5 * $multiplier;
			$data['book'] 		= $book_info;
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));
			$data['barcode'] 	= !empty($book_info['isbn'])
				// ? base_url(self::_getBarcode($book_info['isbn']))
				? self::_getBarcode($book_info['isbn'])
				: self::_getBarcode($book_info['unique_id']);


			$this->load->model('user/UserCover_model', 'user_cover_model');

			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];

			if (
				strtotime($book_info['date_published']) < strtotime('2024-05-09 12:30:00') ||
				empty($user_cover_info['design'])
			) {
				$data['old'] = true;
			} else {
				$data['old'] = false;
			}

			$html = $this->load->view('backend/admin/book_print/print_single', $data, true);
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

			// $dompdf->loadHtml($html);
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper(
				[
					0,
					0,
					285 * $multiplier,
					427.5 * $multiplier
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

	public function printBook($book_id = 0, $version = 0, $author_copy = false, $download = true, $mrp = false) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('zip');

			// Add Cover
			$pdf_data = self::printCover(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false,
				$mrp
			);

			self::_updateBookCoverInS3($book_info, $pdf_data, $mrp);

			$dir_name = vsprintf('%s-v%s', [
				$book_info['slug'],
				$book_info['version'],
			]);

			$cover_zip_file = vsprintf('%s/cover-%s.pdf', [
				$dir_name,
				$dir_name,
			]);

			if ($mrp) {
				$cover_zip_file = vsprintf('%s/cover-mrp-%s.pdf', [
					$dir_name,
					$dir_name,
				]);
			}

			$this->zip->add_data($cover_zip_file, $pdf_data);

			// Add pages
			$pdf_data = self::printBookPages(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			self::_updateBookPagesInS3($book_info, $pdf_data);

			$this->zip->add_data(vsprintf('%s/pages-%s.pdf', [
				$dir_name,
				$dir_name
			]), $pdf_data);

			$zip_name = vsprintf('%s.zip', [
				$dir_name
			]);

			// $this->zip->archive(FCPATH . vsprintf('uploads/%s', [
			// 	$zip_name,
			// ]));
			$this->zip->download($zip_name);
			unlink(FCPATH . 'uploads/' . $zip_name);
		}
	}

	public function printBookPages($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$this->load->model('book/CustomTheme_model', 'custom_theme_model');

			$data['author_copy'] = $author_copy;

			$original_width 	= 285;
			$original_height 	= $original_width * 1.5;
			$book_width 		= 432;
			$bleed_size			= 14.1732;
			$book_bleed_width 	= $book_width + $bleed_size * 2;
			$multiplier 		= $book_width / $original_width;

			$data['multiplier'] = $book_width / $original_width;
			$data['bleed'] 		= $bleed_size;
			$data['text_bleed'] = 0;
			$data['fc_bleed'] 	= $bleed_size;
			$data['width'] 		= $original_width * $multiplier + $bleed_size;
			$data['height'] 	= $original_height * $multiplier + $bleed_size * 2;

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

			$data['pages'] = $this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'];

			$data['book'] 		= $book_info;
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$html 				= $this->load->view('backend/admin/book_print/page_v4', $data, true);
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

			// $dompdf->loadHtml($html);
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			$dompdf->setPaper(
				[
					0,
					0,
					$data['width'] + $bleed_size,
					$data['height']
				],
				'portrait'
			);

			$dompdf->render();

			if ($download) {
				$dompdf->stream(str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf');
			} else {
				return $dompdf->output();
			}
		}
	}

	public function printCover($book_id = 0, $version = 0, $author_copy = false, $download = true, $mrp = false) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$book_info['unique_id'] = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_info['book_id']);

			/*if (empty($book_info['unique_id'])) {
				$book_info['unique_id'] = $this->book_model->addUniqueId($book_info['book_id']);
			}*/

			$data['author_copy'] 	= $author_copy;

			$data['spine_size'] 	= self::_getSpineSize($this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
			])['total']);

			$original_width 	= 285;
			$original_height 	= $original_width * 1.5;
			$book_width 		= 432;
			$bleed_size			= 14.1732;
			$book_bleed_width 	= $book_width + $bleed_size;

			$multiplier 		= $book_width / $original_width;
			$data['multiplier'] = $book_width / $original_width;
			$data['bleed'] 		= $bleed_size;
			$data['fc_bleed'] 	= $bleed_size;
			$data['fc_heading_bleed'] = 1.2;
			$data['width'] 		= $original_width * $multiplier * 2 + $bleed_size * 2 + $data['spine_size'];
			$data['height'] 	= $original_height * $multiplier + $bleed_size * 2;
			$data['price']		= !empty($mrp)
				? ($this->book_model->getPrice($book_info['book_id'])['total'] ?? 0)
				: 0;

			$data['book_bleed_width'] 	= $book_bleed_width;

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

			$data['book'] 		= $book_info;
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));
			$data['barcode'] 	= !empty($book_info['isbn'])
				// ? base_url(self::_getBarcode($book_info['isbn']))
				? self::_getBarcode($book_info['isbn'])
				: self::_getBarcode($book_info['unique_id']);

			$this->load->model('user/UserCover_model', 'user_cover_model');
			$this->load->model('event/EventBook_model', 'event_book_model');

			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];

			if (
				strtotime($book_info['date_published']) < strtotime('2024-05-09 12:30:00') ||
				empty($user_cover_info['design'])
			) {
				$html = $this->load->view('backend/admin/book_print/cover_v3', $data, true);
			} else {
				$data['is_sdg'] 	= $this->event_book_model->get_all([
					'event_id' 	=> 20,
					'book_id' 	=> $book_info['book_id']
				])['total'] > 0;

				$html = $this->load->view('backend/admin/book_print/cover_v4', $data, true);
			}

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

			$dompdf->setPaper(
				[
					0,
					0,
					$data['width'] + 10,
					$data['height'] + 10
				],
				'portrait'
			);

			$dompdf->render();

			if ($download) {
				$dompdf->stream(str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf');
			} else {
				return $dompdf->output();
			}
		}
	}

	private function _getSpineSize($total_pages = 0) {
		return ($total_pages * 130 * 1.35 / 2000 + 2) * 2.83465;
	}

	private function _updateBookCoverInS3($book_info = [], $pdf_data = null, $mrp = false) {
		$this->load->library('S3_lib', 's3_lib');

		$filename = vsprintf('%s-v%s-%s', [
			$book_info['slug'],
			$book_info['version'],
			'Paperback',
		]);

		$s3_dirname = vsprintf('%s/%s', [
			(ENVIRONMENT === 'production' ? '' : 'test') . 'bookpdfs',
			$filename,
		]);

		$cover_file = sprintf('cover-%s.pdf', $filename);

		if ($mrp) {
			$cover_file = sprintf('cover-mrp-%s.pdf', $filename);
		}

		// update paperback
		if ($this->s3_lib->doesExist($cover_file, $s3_dirname, false)) {
			$this->s3_lib->putData(
				$cover_file,
				$s3_dirname,
				$pdf_data,
				false
			);

		}

		// update hardcover
		$filename = vsprintf('%s-v%s-%s', [
			$book_info['slug'],
			$book_info['version'],
			'Hard Cover',
		]);

		if ($mrp) {
			$cover_file = sprintf('cover-mrp-%s.pdf', $filename);
		}

		if ($this->s3_lib->doesExist($cover_file, $s3_dirname, false)) {
			$this->s3_lib->putData(
				$cover_file,
				$s3_dirname,
				$pdf_data,
				false
			);
		}
	}

	private function _updateBookPagesInS3($book_info = [], $pdf_data = null) {
		$this->load->library('S3_lib', 's3_lib');

		$filename = vsprintf('%s-v%s-%s', [
			$book_info['slug'],
			$book_info['version'],
			'Paperback',
		]);

		$s3_dirname = vsprintf('%s/%s', [
			(ENVIRONMENT === 'production' ? '' : 'test') . 'bookpdfs',
			$filename,
		]);

		// update paperback pages
		if ($this->s3_lib->doesExist(sprintf('pages-%s.pdf', $filename), $s3_dirname, false)) {
			$this->s3_lib->putData(
				sprintf('pages-%s.pdf', $filename),
				$s3_dirname,
				$pdf_data,
				false
			);
		}

		// update hardcover pages
		$filename = vsprintf('%s-v%s-%s', [
			$book_info['slug'],
			$book_info['version'],
			'Hard Cover',
		]);

		if ($this->s3_lib->doesExist(sprintf('pages-%s.pdf', $filename), $s3_dirname, false)) {
			$this->s3_lib->putData(
				sprintf('pages-%s.pdf', $filename),
				$s3_dirname,
				$pdf_data,
				false
			);
		}
	}
}
