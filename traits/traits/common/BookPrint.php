<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait BookPrint {
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

	public function printBookOld($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
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

			$data['book'] = $book_info;
			$data['book_original_info'] = $book_original_info;
			$data['width'] = 285 * $multiplier;
			$data['height'] = 427.5 * $multiplier;
			$data['qrcode'] = base_url(self::_getQrCode($book_info));
			$data['barcode'] = !empty($book_original_info['isbn'])
				// ? base_url(self::_getBarcode($book_original_info['isbn']))
				? self::_getBarcode($book_original_info['isbn'])
				: $data['qrcode'];
			$html = $this->load->view('backend/admin/books/print', $data, true);
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

	public function printBook($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('zip');

			// Add Cover
			$pdf_data = self::printCover(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			self::_upadateBookCoverInS3($book_info, $pdf_data);

			$dir_name = vsprintf('%s-v%s', [
				$book_info['slug'],
				$book_info['version'],
			]);

			$this->zip->add_data(vsprintf('%s/cover-%s.pdf', [
				$dir_name,
				$dir_name,
			]), $pdf_data);

			// Add pages
			$pdf_data = self::printBookPages(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			self::_upadateBookPagesInS3($book_info, $pdf_data);

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
			$book_original_info = $this->book_model->get($book_id);

			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

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

			$data['book'] = $book_info;
			$data['book_code'] = _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$data['book_original_info'] = $book_original_info;
			$data['width'] = 285 * $multiplier;
			$data['height'] = 427.5 * $multiplier;
			$html = $this->load->view('backend/admin/books/print_page', $data, true);
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

	public function printCover($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$book_original_info 	= $this->book_model->get($book_id);

			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$data['author_copy'] 	= $author_copy;

			$data['spine_size'] 	= self::_getSpineSize($this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
			])['total']);

			// $data['multiplier'] = $multiplier = 432 / 285;
			$data['multiplier'] = 432 / 285;
			$data['bleed'] 		= 30;
			$data['fc_bleed'] 	= 30;

			$multiplier 		= 468 / 285;
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

			$data['book'] 				= $book_info;
			$data['book_code'] 			= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$data['book_original_info'] = $book_original_info;

			$data['width'] 	= 285 * $multiplier * 2 + $data['spine_size'];
			$data['height'] = 427.5 * $multiplier;

			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));
			$data['barcode'] 	= !empty($book_original_info['isbn'])
				// ? base_url(self::_getBarcode($book_original_info['isbn']))
				? self::_getBarcode($book_original_info['isbn'])
				: $data['qrcode'];


			$html = $this->load->view('backend/admin/books/book_cover', $data, true);
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
					$data['width'] + 10,
					$data['height'] + 10
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

	private function _getSpineSize($total_pages = 0) {
		return ($total_pages * 130 * 1.35 / 2000 + 2) * 2.83465;
	}

	private function _upadateBookCoverInS3($book_info = [], $pdf_data = null) {
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

		// update paperback
		if ($this->s3_lib->doesExist(sprintf('cover-%s.pdf', $filename), $s3_dirname, false)) {
			$this->s3_lib->putData(
				sprintf('cover-%s.pdf', $filename),
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

		if ($this->s3_lib->doesExist(sprintf('cover-%s.pdf', $filename), $s3_dirname, false)) {
			$this->s3_lib->putData(
				sprintf('cover-%s.pdf', $filename),
				$s3_dirname,
				$pdf_data,
				false
			);
		}
	}

	private function _upadateBookPagesInS3($book_info = [], $pdf_data = null) {
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
