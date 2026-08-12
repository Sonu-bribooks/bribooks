<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventBookPoster {
	private function _getFrontBackBarcode($data = 0, $multiplier = 1) {
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			160 * 2.33 * $multiplier,
			40 * 2.33 * $multiplier,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();
	}

	public function get_top200_rankers_book_pdfs() {
		$this->load->library('zip');

		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 0;
		$filter_data['limit'] = 200;
		$filter_data['quantity_ge'] = 50;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$results = $this->ranking_model->getRanks($filter_data)['rows'] ?? [];

		foreach ($results as $book_info) {
			$pdf_data = self::front_back($book_info['id'], false);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				'paperback',
			]);

			$this->zip->add_data(vsprintf('covers/cover-%s.pdf', [
				$filename,
			]), $pdf_data);
		}

		$this->zip->download('covers.zip');
	}

	public function get_jury_book_pdfs() {
		$this->load->library('zip');

		$results = $this->db->get_where('user_details_nyaf_invites', [
			'book_rank'	=> 0
		])->result_array();

		foreach ($results as $item) {
			$book_info = $this->book_model->get($item['book_id']);

			$pdf_data = self::front_back($book_info['id'], false);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				'paperback',
			]);

			$this->zip->add_data(vsprintf('covers/cover-%s.pdf', [
				$filename,
			]), $pdf_data);
		}

		$this->zip->download('Jury_covers.zip');
	}

	public function front_back($book_id = 0, $download = true) {
		if ($book_info = $this->book_model->get($book_id)) {
			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$original_width 	= 285;
			$original_height 	= $original_width * 1.5;
			$book_width 		= 648;
			$book_bleed_width 	= 648;

			// $data['multiplier'] = $multiplier = 432 / 285;
			$data['multiplier'] = $book_width / $original_width;
			$data['bleed'] 		= 0;
			$data['fc_bleed'] 	= 0;

			$multiplier 		= $book_bleed_width / $original_width;
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

			$data['width'] 		= $original_width * $multiplier;
			$data['height'] 	= $original_height * $multiplier;

			$data['book_bleed_width'] 	= $book_bleed_width;

			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));
			$data['barcode'] 	= !empty($book_info['isbn'])
				// ? base_url(self::_getBarcode($book_info['isbn']))
				? self::_getFrontBackBarcode($book_info['isbn'], $multiplier)
				: $data['qrcode'];


			$html = $this->load->view('backend/admin/books/front_break', $data, true);
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
				$dompdf->stream(str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf');
			} else {
				return $dompdf->output();
			}
		}
	}

	public function get_top200_rankers_book_qrs($download = true) {
		$this->load->library('zip');

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('book/Book_model', 'book_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 0;
		$filter_data['limit'] = 200;
		$filter_data['quantity_ge'] = 50;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		// $results = $this->ranking_model->getRanks($filter_data)['rows'] ?? [];

		$results = $this->db->query("
		SELECT user_details_nyaf_invites.id ,`event_id`, user_details_nyaf_invites.`user_id`, `book_id`, `book_rank`, `book_sold`, `no_of_guest`, user_details_nyaf_invites.`status`, `is_jury`
		FROM `user_details_nyaf_invites`
		join book on book.id = user_details_nyaf_invites.book_id
		WHERE `event_id` = '10' AND user_details_nyaf_invites.`status` = '1' AND `is_jury` = '0' AND `book_rank` >= '1' AND `book_rank` <= '200'
		")->result_array();

		$data['qrcodes'] = [];

		foreach ($results as $result) {
			$book_info = $this->book_model->get($result['book_id']);
			$data['qrcodes'][] =[
				'image'	=> base_url(self::_getQrCode($book_info)),
				'book'	=> $book_info,
			];
		}

		$html = $this->load->view('backend/admin/event_book_poster/qrcodes', $data, true);
		// echo $html;
		// die;

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper([
			0,
			0,
			400,
			460
		], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		if ($download) {
			$dompdf->stream('QrCodes.pdf');
		} else {
			return $dompdf->output();
		}

		$this->zip->download('covers.zip');
	}

	public function get_jury_book_qrs($download = true) {
		$this->load->library('zip');
		$this->load->model('book/Book_model', 'book_model');

		$results = $this->db->query("
		SELECT user_details_nyaf_invites.id ,`event_id`, user_details_nyaf_invites.`user_id`, `book_id`, `book_rank`, `book_sold`, `no_of_guest`, user_details_nyaf_invites.`status`, `is_jury`
		FROM `user_details_nyaf_invites`
		WHERE `event_id` = '10' AND user_details_nyaf_invites.`status` = '1' AND `is_jury` = '1'
		")->result_array();

		$data['qrcodes'] = [];

		foreach ($results as $item) {
			$book_info = $this->book_model->get($item['book_id']);

			$data['qrcodes'][] =[
				'image'	=> base_url(self::_getQrCode($book_info)),
				'book'	=> $book_info,
			];
		}

		$html = $this->load->view('backend/admin/event_book_poster/qrcodes', $data, true);
		// echo $html;
		// die;

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper([
			0,
			0,
			400,
			460
		], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		if ($download) {
			$dompdf->stream('JuryQrCodes.pdf');
		} else {
			return $dompdf->output();
		}

		$this->zip->download('covers.zip');
	}

	public function get_invite_book_qrs($id = true) {
		$this->load->library('zip');

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('book/Book_model', 'book_model');

		$invite_info = $this->db->get_where('user_details_nyaf_invites', [
			'id' 	=> (int)$id,
		])->row_array();


		$book_info = $this->book_model->get($invite_info['book_id']);

		$data['qrcodes'][] =[
			'image'	=> base_url(self::_getQrCode($book_info)),
			'book'	=> $book_info,
		];


		$html = $this->load->view('backend/admin/event_book_poster/qrcodes', $data, true);

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper([
			0,
			0,
			400,
			460
		], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($book_info['name'] . '.pdf');
		// if ($download) {
		// 	$dompdf->stream('QrCodes.pdf');
		// } else {
		// 	return $dompdf->output();
		// }

		// $this->zip->download('covers.zip');

					// $dompdf->render();
					// $dompdf->stream('book_' . str_replace('-', '_', $book_info['slug']) . date('Y_m_d_H_i_s') . '.pdf');
	}
}
