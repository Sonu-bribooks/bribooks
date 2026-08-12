<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait BookPrintUnPublished {
	public function printUnPublishedBook($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_model->get($book_id)) {
			$this->load->library('zip');

			// Add Cover
			$pdf_data = self::printUnPublishedBookCover(
				$book_info['id'],
				$book_info['version'],
				false,
				false
			);

			$dir_name = vsprintf('%s-v%s', [
				$book_info['slug'],
				$book_info['version'],
			]);

			$this->zip->add_data(vsprintf('%s/cover-%s.pdf', [
				$dir_name,
				$dir_name,
			]), $pdf_data);

			// Add pages
			$pdf_data = self::printUnPublishedBookPages(
				$book_info['id'],
				$book_info['version'],
				false,
				false
			);

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

	public function printUnPublishedBookPages($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_model->get($book_id)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$this->load->model('book/CustomTheme_model', 'custom_theme_model');

			$data['author_copy'] = $author_copy;

			$original_width 	= 285;
			$original_height 	= $original_width * 1.5;
			$book_width 		= 432;
			$bleed_size			= 9;
			$book_bleed_width 	= $book_width + $bleed_size * 2;
			$multiplier 		= $book_width / $original_width;

			$data['multiplier'] = $book_width / $original_width;
			$data['bleed'] 		= $bleed_size;
			$data['text_bleed'] = 0;
			$data['fc_bleed'] 	= $bleed_size;
			$data['width'] 		= $original_width * $multiplier + $bleed_size;
			$data['height'] 	= $original_height * $multiplier + $bleed_size * 2;

			$data['pages'] = $this->page_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['id'],
				'sort'		=> 'page.sort_order',
				'order'		=> 'ASC',
			])['rows'];

			$data['book'] 		= $book_info;
			$data['book_code'] 	= _o_b_code($book_info['id'], $book_info['version'], 'paperback');
			$html 				= $this->load->view('backend/admin/book_print/page_kdp', $data, true);
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
					$data['width'],
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

	public function printUnPublishedBookCover($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		if ($book_info = $this->book_model->get($book_id)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$book_info['unique_id'] = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_info['id']);

			/*if (empty($book_info['unique_id'])) {
				$book_info['unique_id'] = $this->book_model->addUniqueId($book_info['id']);
			}*/

			$data['author_copy'] 	= $author_copy;

			$data['spine_size'] 	= self::_getUnPublishedSpineSize($this->page_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['id'],
			])['total']);

			$original_width 	= 285;
			$original_height 	= $original_width * 1.5;
			$book_width 		= 432;
			$bleed_size			= 9;
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
			$data['book_code'] 	= _o_b_code($book_info['id'], $book_info['version'], 'paperback');
			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));
			$data['barcode'] 	= !empty($book_info['isbn'])
				// ? base_url(self::_getBarcode($book_info['isbn']))
				? self::_getBarcode($book_info['isbn'])
				: self::_getBarcode($book_info['unique_id']);


			$html = $this->load->view('backend/admin/book_print/cover_kdp', $data, true);
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
					$data['width'],
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

	private function _getUnPublishedSpineSize($total_pages = 0) {
		return 2 * $total_pages * 0.162;
	}
}
