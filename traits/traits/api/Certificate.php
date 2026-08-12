<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait Certificate {
	public function getCertificateBySlug() {
		if (!$this->json) {
			$slugs = explode('-', $this->input->post('slug'));
			$id = array_pop($slugs);

			if (($slugs[0] ?? '') === 'donation') {
				$this->load->model('certificate/DonationCertificate_model', 'donation_certificate_model');

				if ($certificate_info = $this->donation_certificate_model->get($id)) {
					$s3_dirname = 'authorcertificates';
					$s3_dirname = $s3_dirname . '/donation_certificates/';
					$s3_dirname = (ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . 'test/';

					$this->json['certificate'] = [
						'id' 	=> $certificate_info['id'],
						'slug'	=> 'donation-cert-' . $certificate_info['donation_type'] . '-' . $certificate_info['id'],
						'name'	=> trim(_li(str_replace('.jpeg', '', preg_replace('@\d@', '', $certificate_info['name'])))),
						'image'	=> S3_CERTIFICATE_URL . $s3_dirname . $certificate_info['name'],
						'pdf' 	=> S3_CERTIFICATE_URL . $s3_dirname . 'pdf/' . str_replace('jpeg', 'pdf', $certificate_info['name']),
					];
				}
			} else {
				if ($certificate_info = $this->certificate_model->get($id)) {
					$certificate_image 	= $this->config->item('cloudfront_url') .   $this->config->item('s3_author_certificates') . $certificate_info['image'] . '.png';
					$certififcate_pdf	= USER_INVOICE_URL . 'api/downloadAuthorCertfifcate/?code=' . urlencode($certificate_info['unique_id']);

					$this->json['certificate'] = [
						'id' 	=> $certificate_info['id'],
						'slug'	=> str_replace(
							['_cert', '_'],
							['_certificate', '-'],
							$certificate_info['type']
						) . '-' . $certificate_info['id'],
						'name'	=> strpos($certificate_info['name'], '_') !== false ? _l($certificate_info['name']) : $certificate_info['name'],
						'image' => $certificate_image,
						'pdf' 	=> $certififcate_pdf,
					];
				}
			}
		}
	}

	public function getCertificates() {
		if (!$this->json) {
			$this->json['certificates'] = [];
		}
	}

	public function getDonationCertificates() {
		if (!$this->json) {
			$this->load->model('certificate/DonationCertificate_model', 'donation_certificate_model');

			$s3_dirname = 'authorcertificates';
			$s3_dirname = $s3_dirname . '/donation_certificates/';
			$s3_dirname = (ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . 'test/';

			$this->json['certificates'] = array_map(function($item) use($s3_dirname) {
				return [
					'id'	=> $item['id'],
					'slug'	=> 'donation-cert-' . $item['donation_type'] . '-' . $item['id'],
					'name'	=> trim(_l(str_replace('.jpeg', '', preg_replace('@\d@', '', $item['name'])))),
					'image'	=> S3_CERTIFICATE_URL . $s3_dirname . $item['name'],
					'pdf' 	=> S3_CERTIFICATE_URL . $s3_dirname . 'pdf/' . str_replace('jpeg', 'pdf', $item['name']),
				];
			}, $this->donation_certificate_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
			])['rows'] ?? []);
		}
	}

	public function getAuthorCertificates() {
		$this->form_validation->set_rules('achievement', _l('achievement'), 'trim|required|numeric|in_list[0,1,2]');
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|numeric');
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required|numeric');
		self::_runFormValidation();

		if (!$this->json) {
			if (!$this->session->userdata('user_id')) {
				$this->json['login'] = true;
				return;
			}

			if (
				$this->input->post('event_id') &&
				empty($event_info = $this->event_model->get($this->input->post('event_id')))
			) {
				$this->json['error'] = _l('invalid_event');
				return;
			}

			$book_info 		= $this->book_model->get($this->input->post('book_id'));

			if (($this->input->post('achievement') != 2) && empty($book_info)) {
				$this->json['error'] = _l('invalid_book');
				return;
			}

			$author_info 	= $this->student_model->get($this->session->userdata('user_id'));

			if ($this->input->post('achievement') == 2) {
				$site_info = $this->site_model->get($author_info['site_id']);

				if (!empty($site_info) && strlen($site_info['name']) > 72) {
					$strcount 		= strlen($site_info['name']);
					$school_name 	= substr($site_info['name'],0,(72 - $strcount)) . '...';
				} else {
					$school_name 	= $site_info['name'];
				}
			}

			$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

			$certificate_templates = $this->certificate_template_model->get_all([
				'event_id'		=> (int)$this->input->post('event_id'),
				'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
				'achievement'	=> (int)$this->input->post('achievement'),
				'sort'			=> 'certificate_template.book_sold',
				'order'			=> 'ASC',
			])['rows'] ?? [];

			if (!empty($event_info['id'])) {
				$sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], ($book_info['id'] ?? 0));
			} else {
				$sold = $this->order_model->getTotalProductsByProductId($book_info['id'] ?? 0);
			}

			$this->json['certificates'] = [];

			foreach ($certificate_templates as $key => $template) {
				if (!empty($template['challenge_id'])) {
					$model = sprintf('ranking_%s_model', $template['challenge_type']);

					$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($template['challenge_type'])), $model);

					$book_league_info = $this->{$model}->get_all([
						'challenge_id'	=> $template['challenge_id'],
						'book_id'		=> $this->input->post('book_id')
					])['rows'][0] ?? [];

					if (empty($book_league_info)) continue;
				}

				$certificate = $this->certificate_model->get_all([
					'user_id' 					=> $this->session->userdata('user_id'),
					'book_id'					=> $this->input->post('book_id'),
					'event_id'					=> $this->input->post('event_id') ?? 0,
					'achievement'				=> (int)$this->input->post('achievement'),
					'certificate_template_id' 	=> $template['id'],
				])['rows'][0] ?? [];

				// if (!empty($template['achievement']) && empty($certificate)) continue;

				if (!empty($template['genre_ids'])) {
					$genres = json_decode($template['genre_ids'], true);

					if (
						!empty($genres) &&
						!empty($book_info['genre_id']) &&
						!in_array($book_info['genre_id'], $genres) &&
						empty($certificate)
					) {
						continue;
					}
				}

				if (empty($certificate) && empty($template['status'])) continue;

				$unlocked = !empty($certificate['id']);

				if (!empty($template['has_isbn']) && empty($book_info['isbn'] ?? '')) {
					$unlocked = false;
				}

				if (!empty($event_info) && strtotime($event_info['selling_end_date']) < time() && !$unlocked) {
					continue;
				}

				if (
					$unlocked &&
					empty(@getimagesize($this->config->item('cloudfront_url') .   $this->config->item('s3_author_certificates') . $certificate['image'] . '.png'))
				) {
					self::_genCertificatePdf([
						'event_id'		=> $template['event_id'],
						'image' 		=> $template['image'],
						'has_isbn' 		=> $template['has_isbn'],
						'has_rank' 		=> $template['has_rank'],
						'rank_x_axis' 	=> $template['rank_x_axis'],
						'book_id' 		=> $book_info['id'] ?? 0,
						'book_name' 	=> $this->input->post('achievement') == 2 ? $school_name : ($book_info['name'] ?? ''),
						'book_isbn' 	=> $book_info['isbn'] ?? '',
						'author_name' 	=> $this->input->post('achievement') == 2 ? ucwords($author_info['first_name'] . ' ' . $author_info['last_name']) : ($book_info['author_name'] ?? ''),
						'author_id' 	=> $book_info['user_id'] ?? 0,
						'date' 			=> $certificate['date_added'],
						'cert_unique_id'=> $certificate['unique_id'],
						'image_name'    => $certificate['image'],
						'rank'    		=> $certificate['rank'],
					], false);
				}

				$this->json['certificates'][] = $unlocked ? [
					'template_id'	=> $template['id'],
					'unlock' 		=> $unlocked,
					'id' 			=> $certificate['id'],
					'slug' 			=> str_replace(['_cert', '_'], ['_certificate', '-'], $certificate['type']) . '-' . $certificate['id'],
					'name' 			=> strpos($certificate['name'], '_') !== false ? _l($certificate['name']) : $certificate['name'],
					'image' 		=> $this->config->item('cloudfront_url') .   $this->config->item('s3_author_certificates') . $certificate['image'] . '.png',
					'pdf' 			=> USER_INVOICE_URL . 'api/downloadAuthorCertfifcate/?code=' . urlencode($certificate['unique_id']),
				] : [
					'template_id'	=> $template['id'],
					'unlock' 		=> $unlocked,
					'message'		=> self::_getCertificateMessage($template, $sold),
				];
			}
		}
	}

	private function _getCertificateMessage($template = [], $sold = 0) {
		$certificate_name	= _l(str_replace('_cert', '_certificate', $template['type']));
		$required_qty 		= $template['book_sold'] - $sold;

		if ($required_qty > 0) {
			$copy = sprintf(($required_qty == 1 ? '%scopy' : '%scopies'), !empty($sold) ? 'more ' : '');
			return sprintf(_li('Sell %s %s to unlock %s'), $required_qty, $copy, $certificate_name);
		}

		if (!empty($template['has_isbn'])) {
			return sprintf(_li('%s Unlocked! Awaiting for ISBN allotment.'), $certificate_name);
		}

		return sprintf(_li('Your relevant Certificate Generation is in process. Please refresh this page in 5 mins.'));
	}

	public function downloadAuthorCertfifcate() {
		if (!$this->json) {
			// if (!$this->session->userdata('user_id')) {
			// 	$this->json['error'] = 'unauthorized';
			// 	return;
			// }

			$certificate_code = urldecode($this->input->get('code'));

			$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
			$certificate_info = $this->certificate_model->getByCode($certificate_code);

			if (!empty($certificate_info)) {
				$author_info 	= $this->student_model->get($certificate_info['user_id']);

				if ($certificate_info['achievement'] == 2) {
					$site_info  = $this->site_model->get($author_info['site_id']);
					if (!empty($site_info) && strlen($site_info['name']) > 72) {
						$strcount 		= strlen($site_info['name']);
						$school_name 	= substr($site_info['name'],0,(72 - $strcount)) . '...';
					} else {
						$school_name 	= $site_info['name'];
					}
				}

				$book_info 		= $this->book_model->get($certificate_info['book_id']);

				$certificate_template_info = $this->certificate_template_model->get($certificate_info['certificate_template_id']);

				self::_genCertificatePdf([
					'event_id'		=> $certificate_template_info['event_id'],
					'image' 		=> $certificate_template_info['image'],
					'has_isbn' 		=> $certificate_template_info['has_isbn'],
					'has_rank' 		=> $certificate_template_info['has_rank'],
					'rank_x_axis' 	=> $certificate_template_info['rank_x_axis'],
					'book_id' 		=> $book_info['id'],
					'book_name' 	=> $certificate_info['achievement'] == 2 ? $school_name : $book_info['name'],
					'book_isbn' 	=> $book_info['isbn'],
					'author_name' 	=> $certificate_info['achievement'] == 2 ? ucwords($author_info['first_name'] . ' ' . $author_info['last_name']) : $book_info['author_name'],
					'author_id' 	=> $book_info['author_id'],
					'date' 			=> $certificate_info['date_added'],
					'cert_unique_id'=> $certificate_info['unique_id'],
					'image_name'    => $certificate_info['image'],
					'rank'    		=> $certificate_info['rank'],
				]);
			} else {
				$this->json['certificate'] = _l('certificate_not_found');
			}
		}
	}

	private function _generateCertQrCode($data = NULL) {
		if (empty($data)) return;

		$file = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $data['image']);

		return generateQrCode('http://www.bribooks.com/verifycertificate/' . $data['cert_unique_id'], 20,2, $file);
	}

	private function _genCertificatePdf($data = [], $gen_pdf = TRUE) {
		log_kb(['_genCertificatePdf' => $data]);

		if (!empty($data['event_id']) && $data['event_id'] < 9) {
			return self::_genCertificateOldPdf($data, $gen_pdf);
		}

		$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $data['image']);

		list($image_width, $image_height) = getimagesize($image_template);

		$image_type = exif_imagetype($image_template);

		if (!in_array($image_type, [2, 3])) return;

		$image 		= $image_type === 2 ? imagecreatefromjpeg($image_template) : imagecreatefrompng($image_template);

		$qr_file 	= generateQrCode('http://www.bribooks.com/verifycertificate/' . $data['cert_unique_id'], 20, 2);
		$qr_image 	= imagecreatefrompng(FCPATH . $qr_file);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$black 		= imagecolorallocate($image, 0, 0, 0);


		$image_width	= imagesx($image);
		$image_height	= imagesy($image);
		$qr_image_width		= imagesx($qr_image);
		$qr_image_height 	= imagesy($qr_image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

		if (!empty($data['book_name']) && strlen($data['book_name']) > 40) {
			$font_size 	= 20;
		} else {
			$font_size 	= 28;
		}

		if (!empty($data['has_isbn'])) {
			imagettftext($image, $font_size, 0, 120, 580, $darkgrey, $font_path, strtoupper($data['author_name']));
			imagettftext($image, $font_size, 0, 120, 740, $darkgrey, $font_path, strtoupper($data['book_name']));

			if (in_array($data['event_id'], [9])) {
				imagettftext($image, $font_size - 10, 0, 260, 860, $darkgrey, $font_path, strtoupper($data['book_isbn']));
			} else {
				imagettftext($image, $font_size - 10, 0, 260, 820, $darkgrey, $font_path, strtoupper($data['book_isbn']));
			}
		} else {
			imagettftext($image, $font_size, 0, 120, 610, $darkgrey, $font_path, strtoupper($data['author_name']));
			imagettftext($image, $font_size, 0, 120, 810, $darkgrey, $font_path, strtoupper($data['book_name']));
		}

		if (!empty($data['has_rank']) && !empty($data['rank'])) {
			$rank_x_axis = !empty($data['rank_x_axis']) ? $data['rank_x_axis'] : 1000;
			imagettftext($image, 22, 0, $rank_x_axis, 922, $black, $font_path, sprintf('%02d', strtoupper($data['rank'])));
		}

		if (in_array($data['event_id'], [10, 11, 12])) {
			imagettftext($image, 12, 0, 1450, 1010, $darkgrey, $font_path, $data['cert_unique_id']);
		} else {
			imagettftext($image, 12, 0, 1450, 1040, $darkgrey, $font_path, $data['cert_unique_id']);
		}

		imagettftext($image, 18, 0, 1450, 1070, $darkgrey, $font_path, date('d/m/Y', strtotime($data['date'])));

		$zoom = 5.5;

		if (in_array($data['event_id'], [10, 11, 12])) {
			imagecopyresampled(
				$image,
				$qr_image,
				($image_width - $qr_image_width / $zoom - 207),
				($image_height - $qr_image_height / $zoom - 330),
				0,
				0,
				$qr_image_width / $zoom,
				$qr_image_height / $zoom,
				$qr_image_width,
				$qr_image_height
			);
		} else {
			imagecopyresampled(
				$image,
				$qr_image,
				($image_width - $qr_image_width / $zoom - 207),
				($image_height - $qr_image_height / $zoom - 262),
				0,
				0,
				$qr_image_width / $zoom,
				$qr_image_height / $zoom,
				$qr_image_width,
				$qr_image_height
			);
		}

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

		// upload to s3 bucket and share the cloudfront url
		log_kb(['GenerareCertififcate::' => $this->s3->amazonS3Upload(
			$data['image_name'] . '.png',
			$filename,
			rtrim($this->config->item('s3_author_certificates'), '/')
		)]);

		if ($gen_pdf) {
			$html = sprintf('<style>@page{margin:0;padding:0;}</style><img
				src="%s"
				style="width:100%%;max-height:100%%;"
			/>', base_url(str_replace(FCPATH, '', $filename)));

			$dompdf = new Dompdf();
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();
			$dompdf->stream($data['image_name'] . '.pdf');
		}

		unlink($filename);
	}

	private function _genCertificateOldPdf($data = [], $gen_pdf = TRUE) {
		log_kb(['_genCertificateOldPdf' => $data]);

		$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $data['image']);

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);

		$image_width	= imagesx($image);
		$image_height	= imagesy($image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';
		$font_size 	= 32;

		$author_name_length = (int)((mb_strlen($data['author_name'], 'utf-8') * ($font_size - 7)) / 2) ?? 400;
		$book_name_length 	= (int)((mb_strlen($data['book_name'], 'utf-8') * ($font_size - 7)) / 2) ?? 400;

		if (!empty($data['has_isbn'])) {
			if (in_array($data['event_id'], [2, 3])) {
				imagettftext($image, $font_size, 0, 900 - $author_name_length, 695, $darkgrey, $font_path, strtoupper($data['author_name']));
				imagettftext($image, $font_size, 0, 900 - $book_name_length, 885, $darkgrey, $font_path, strtoupper($data['book_name']));
				imagettftext($image, $font_size, 0, 775, 995, $darkgrey, $font_path, strtoupper($data['book_isbn']));
			} else {
				imagettftext($image, $font_size, 0, 900 - $author_name_length, 700, $darkgrey, $font_path, strtoupper($data['author_name']));
				imagettftext($image, $font_size, 0, 900 - $book_name_length, 845, $darkgrey, $font_path, strtoupper($data['book_name']));
				imagettftext($image, $font_size, 0, 775, 935, $darkgrey, $font_path, strtoupper($data['book_isbn']));
			}
		} else {
			imagettftext($image, $font_size, 0, 900 - $author_name_length, 700, $darkgrey, $font_path, strtoupper($data['author_name']));
			imagettftext($image, $font_size, 0, 900 - $book_name_length, 870, $darkgrey, $font_path, strtoupper($data['book_name']));
		}

		if (in_array($data['event_id'], [2, 3])) {
			if (!empty($data['has_isbn'])) {
				imagettftext($image, 18, 0, 540, 1170, $darkgrey, $font_path, date('d/m/Y', strtotime($data['date'])));
			} else {
				imagettftext($image, 18, 0, 540, 1140, $darkgrey, $font_path, date('d/m/Y', strtotime($data['date'])));
			}
		} else {
			imagettftext($image, 18, 0, 540, 1140, $darkgrey, $font_path, date('d/m/Y', strtotime($data['date'])));
		}

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

		// upload to s3 bucket and share the cloudfront url
		log_kb(['GenerareCertififcate::' => $this->s3->amazonS3Upload(
			$data['image_name'] . '.png',
			$filename,
			rtrim($this->config->item('s3_author_certificates'), '/')
		)]);

		if ($gen_pdf) {
			$html = sprintf('<style>@page{margin:0;padding:0;}</style><img
				src="%s"
				style="width:100%%;max-height:100%%;"
			/>', base_url(str_replace(FCPATH, '', $filename)));

			$dompdf = new Dompdf();
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();
			$dompdf->stream($data['image_name'] . '.pdf');
		}

		unlink($filename);
	}
}
