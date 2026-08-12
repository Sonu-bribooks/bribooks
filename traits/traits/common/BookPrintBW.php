<?php defined('BASEPATH') or exit('No direct script access allowed');

use Spatie\Browsershot\Browsershot;

trait BookPrintBW {
	private function _getBWBarcode($data = 0) {
		$data = str_replace(['-', ' '], '', $data);
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			160 * 1.05,
			40 * 1.05,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();
	}

	private function _getBWQrCode($book_info = [], $size = 60) {
		$file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		return generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, $file);
	}

	public function printBWAuthorBook($book_id = 0, $version = 0) {
		self::printBook($book_id, $version, true);
	}

	public function printBWBook($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('zip');

			// Add Cover
			$pdf_data = self::printBWCover(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			self::_updateBWBookCoverInS3($book_info, $pdf_data);

			$dir_name = vsprintf('%s-v%s', [
				$book_info['slug'],
				$book_info['version'],
			]);

			$this->zip->add_data(vsprintf('%s/cover-%s.pdf', [
				$dir_name,
				$dir_name,
			]), $pdf_data);

			// Add pages
			$pdf_data = self::printBWBookPages(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			self::_updateBWBookPagesInS3($book_info, $pdf_data);

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

	public function printBWBookPages($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$this->load->model('book/CustomTheme_model', 'custom_theme_model');

			$data['author_copy'] = $author_copy;

			$original_width 	= 285;
			// $original_height 	= $original_width * 1.5454545455;
			$original_height 	= $original_width * 1.5;
			// $book_width 		= 396;
			$book_width 		= 432;
			$bleed_size			= 14.1732;
			$book_bleed_width 	= $book_width + $bleed_size * 2;
			$multiplier 		= $book_width / $original_width;

			$data['multiplier'] = $book_width / $original_width;
			$data['bleed'] 		= $bleed_size;
			$data['text_bleed'] = 0;
			$data['fc_bleed'] 	= $bleed_size;
			$data['page_width'] = $original_width * $multiplier + $bleed_size;
			$data['width'] 		= $original_width * $multiplier + $bleed_size * 2;
			$data['height'] 	= $original_height * $multiplier + $bleed_size * 2;

			$data['pages'] = $this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'];

			$data['book'] 		= $book_info;
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'black_white');

			$html = $this->load->view('backend/admin/book_print/page_bw_v3', $data, true);
			// echo $html;
			// die;

			$pdf = Browsershot::html($html)
				->newHeadless()
				->setChromePath('/home/ubuntu/.cache/puppeteer/chrome/linux-119.0.6045.105/chrome-linux64/chrome')
				->showBackground()
				->hideHeader()
				->hideFooter()
				->timeout(120)
				->setOption('args', ['--disable-web-security'])
				->margins(0, 0, 0, 0)
				->paperSize($data['width'] * 0.0138889, $data['height'] * 0.0138889, 'in')
				->pdf();

			if ($download) {
				$filename = 'pages_' . str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf';

				$this->output
					->set_header('Content-Type: application/octet-stream')
					->set_header('Content-Disposition: attachment; filename="' .  $filename . '"')
					->set_header('Expires: 0')
					->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0')
					->set_header('Pragma: public')
					->set_output($pdf);
			} else {
				return $pdf;
			}
		}
	}

	public function printBWCover($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$book_info['unique_id'] = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_info['book_id']);

			/*if (empty($book_info['unique_id'])) {
				$book_info['unique_id'] = $this->book_model->addUniqueId($book_info['book_id']);
			}*/

			$data['author_copy'] 	= $author_copy;

			$data['spine_size'] 	= self::_getBWSpineSize($this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
			])['total']);

			$original_width 	= 285;
			// $original_height 	= $original_width * 1.5454545455;
			$original_height 	= $original_width * 1.5;
			// $book_width 		= 396;
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
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'black_white');
			$data['qrcode'] 	= base_url(self::_getBWQrCode($book_info));
			$data['barcode'] 	= !empty($book_info['isbn'])
				// ? base_url(self::_getBWBarcode($book_info['isbn']))
				? self::_getBWBarcode($book_info['isbn'])
				: self::_getBWBarcode($book_info['unique_id']);


			$html = $this->load->view('backend/admin/book_print/cover_bw_v3', $data, true);
			// echo $html;
			// die;

			$pdf = Browsershot::html($html)
				->newHeadless()
				->setChromePath('/home/ubuntu/.cache/puppeteer/chrome/linux-119.0.6045.105/chrome-linux64/chrome')
				->showBackground()
				->hideHeader()
				->hideFooter()
				->timeout(120)
				->setOption('args', ['--disable-web-security'])
				->margins(0, 0, 0, 0)
				->paperSize(($data['width'] + 10) * 0.0138889, ($data['height'] + 10) * 0.0138889, 'in')
				->pdf();

			if ($download) {
				$filename = 'cover_' . str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf';

				$this->output
					->set_header('Content-Type: application/octet-stream')
					->set_header('Content-Disposition: attachment; filename="' .  $filename . '"')
					->set_header('Expires: 0')
					->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0')
					->set_header('Pragma: public')
					->set_output($pdf);
			} else {
				return $pdf;
			}
		}
	}

	private function _getBWSpineSize($total_pages = 0) {
		$cover_thickness 	= 0.6;
		$page_thickness 	= 0.055;
		$error_fraction 	= 0.2;

		return ($total_pages * $page_thickness + $cover_thickness + $error_fraction) * 2.83465;
	}

	private function _updateBWBookCoverInS3($book_info = [], $pdf_data = null) {
		$this->load->library('S3_lib', 's3_lib');

		$filename = vsprintf('%s-v%s-%s', [
			$book_info['slug'],
			$book_info['version'],
			'BlackWhite',
		]);

		$s3_dirname = vsprintf('%s/%s', [
			(ENVIRONMENT === 'production' ? '' : 'test') . 'bookpdfs',
			$filename,
		]);

		// update black_white
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

	private function _updateBWBookPagesInS3($book_info = [], $pdf_data = null) {
		$this->load->library('S3_lib', 's3_lib');

		$filename = vsprintf('%s-v%s-%s', [
			$book_info['slug'],
			$book_info['version'],
			'BlackWhite',
		]);

		$s3_dirname = vsprintf('%s/%s', [
			(ENVIRONMENT === 'production' ? '' : 'test') . 'bookpdfs',
			$filename,
		]);

		// update black_white pages
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
