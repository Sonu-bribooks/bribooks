<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BookExtra {
	public function qr_generate($param1 = '') {
		$book_info =  $this->book_model->get($param1);
		$url = USER_URL . 'bookstore/' . $book_info['slug'];
		// qr_with_logo($url, $book_info['name']);
		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);
		header('Content-type: image/jpeg');
		header('Content-Disposition: attachment; filename=logo.png');
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

		imagepng($qr_img);
	}

	public function multi_qr() {
		$site = $this->input->get('site_id');

		$filter_data = [];

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = (int)$this->input->get('site_id');
		}

		$results = $this->book_model->get_all($filter_data)['rows'];

		$this->load->library('zip');

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		foreach ($results as $key => $value) {
			$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
				urlencode(USER_URL . 'bookstore/' . $value['slug']),
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

			ob_start();
			imagepng($qr_img);

			$this->zip->add_data($value['name'] . '.png', ob_get_clean());

			if ($key > 3 && ENVIRONMENT !== 'production') break;
		}

		// $this->zip->archive(FCPATH . 'uploads/qrdata.zip');
		$this->zip->download('qrdata.zip');
		// unlink(FCPATH . 'uploads/qrdata.zip');
	}
}
