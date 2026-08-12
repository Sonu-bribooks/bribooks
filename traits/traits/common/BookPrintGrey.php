<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait BookPrintGrey {
	private function _getGreyBarcode($data = 0) {
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
	}

	private function _getGreyQrCode($book_info = [], $size = 60) {
		$file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		return generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, $file);
	}

	public function printGreyAuthorBook($book_id = 0, $version = 0) {
		self::printGreyBook($book_id, $version, true);
	}

	public function printGreyBook($book_id = 0, $version = 0, $author_copy = false, $download = true, $mrp = false) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('zip');

			// Add Cover
			$pdf_data = self::printGreyCover(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false,
				$mrp
			);

			self::_updateGreyBookCoverInS3($book_info, $pdf_data, $mrp);

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
			$pdf_data = self::printGreyBookPages(
				$book_info['book_id'],
				$book_info['version'],
				false,
				false
			);

			self::_updateGreyBookPagesInS3($book_info, $pdf_data);

			$this->zip->add_data(vsprintf('%s/pages-%s.pdf', [
				$dir_name,
				$dir_name
			]), $pdf_data);

			$zip_name = vsprintf('%s.zip', [
				$dir_name
			]);

			$this->zip->download($zip_name);
		}
	}

	public function printGreyBookPages($book_id = 0, $version = 0, $author_copy = false, $download = true) {
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
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'blackwhite');
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

			return self::_convertToGrey($dompdf->output());
		}
	}

	public function printGreyCover($book_id = 0, $version = 0, $author_copy = false, $download = true, $mrp = false) {
		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$book_info['unique_id'] = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_info['book_id']);

			/*if (empty($book_info['unique_id'])) {
				$book_info['unique_id'] = $this->book_model->addUniqueId($book_info['book_id']);
			}*/

			$data['author_copy'] 	= $author_copy;

			$data['spine_size'] 	= self::_getGreySpineSize($this->page_version_model->get_all([
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
				? $this->book_model->getPrice($book_info['book_id'])['black_white_total']
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
			$data['book_code'] 	= _o_b_code($book_info['book_id'], $book_info['version'], 'blackwhite');
			$data['qrcode'] 	= base_url(self::_getGreyQrCode($book_info));
			$data['barcode'] 	= !empty($book_info['isbn'])
				? self::_getGreyBarcode($book_info['isbn'])
				: self::_getGreyBarcode($book_info['unique_id']);

			$this->load->model('user/UserCover_model', 'user_cover_model');
			$this->load->model('event/EventBook_model', 'event_book_model');

			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];

			if (
				strtotime($book_info['date_published']) < strtotime('2024-05-09 12:30:00') ||
				empty($user_cover_info['design'])
			) {
				$html = $this->load->view('backend/admin/book_print/cover_v3', $data, true);
			} else {
				$data['is_sdg'] = $this->event_book_model->get_all([
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

			return $dompdf->output();
		}
	}

	private function _getGreySpineSize($total_pages = 0) {
		$cover_thickness 	= 0.6;
		$page_thickness 	= 0.055;
		$error_fraction 	= 0.2;

		return ($total_pages * $page_thickness + $cover_thickness + $error_fraction) * 2.83465;
	}

	private function _updateGreyBookCoverInS3($book_info = [], $pdf_data = null, $mrp = false) {
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

		$cover_file = sprintf('cover-%s.pdf', $filename);

		if ($mrp) {
			$cover_file = sprintf('cover-mrp-%s.pdf', $filename);
		}

		// update blackwhite
		if ($this->s3_lib->doesExist($cover_file, $s3_dirname, false)) {
			$this->s3_lib->putData(
				$cover_file,
				$s3_dirname,
				$pdf_data,
				false
			);

		}
	}

	private function _updateGreyBookPagesInS3($book_info = [], $pdf_data = null) {
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
	}

	private function _convertToGrey($pdf_data = NULL) {
		$id = uniqid();
		$input_file = sprintf(FCPATH . 'uploads/test/bw_%s.pdf', $id);
		$output_file = sprintf(FCPATH . 'uploads/test/bw_%s_out.pdf', $id);

		file_put_contents($input_file, $pdf_data);

		$command = 'gs -q -dNOPAUSE -sDEVICE=pdfwrite -sColorConversionStrategy=Gray -dProcessColorModel=/DeviceGray -dPDFSETTINGS=/prepress -dCompatibilityLevel=1.7 -sOutputFile=%s %s';

		exec(sprintf($command, $output_file, $input_file), $output, $retval);

		// log_kb(['_convertToGrey' => [
		// 	sprintf($command, $output_file, $input_file),
		// 	$output, $retval
		// ]]);

		$output = file_get_contents($output_file);

		unlink($input_file);
		unlink($output_file);

		return $output;
	}
}
