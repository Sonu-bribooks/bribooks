<?php

defined('BASEPATH') or exit('No direct script access allowed');
use Dompdf\Dompdf;

Trait Downloads{

    public function printAuthorBook($book_id = 0)
	{
		self::printBook($book_id, true);
	}

	public function printBook($book_id = 0, $author_copy = false)
	{

		if ($book_info = $this->book_model->get($book_id)) {
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

			$data['pages'] = $this->page_model->get_all([
				'book_id'	=> $book_info['id'],
				'sort'		=> 'page.sort_order',
				'order'		=> 'ASC',
			])['rows'];


			$data['book'] = $book_info;
			$data['width'] = 285 * $multiplier;
			$data['height'] = 427.5 * $multiplier;
			$data['barcode'] = base_url(self::_getBarcode(
				!empty($book_info['isbn'])
					? $book_info['isbn']
					: $this->config->item('default_isbn')
			));
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
			$dompdf->stream('book_' . str_replace('-', '_', $book_info['slug']) . date('Y_m_d_H_i_s') . '.pdf');
		}
	}
    private function _getBarcode($isbn = 0)
	{
		$file = 'uploads/pdfs/' . $isbn . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$isbn,
			140,
			30,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');
		file_put_contents(FCPATH . $file, $bobj->getPngData());
		return $file;
	}

}


?>
