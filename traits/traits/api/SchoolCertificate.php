<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait SchoolCertificate {
    public function getSchoolCertificateBySlug() {
		if (!$this->json) {
			$slugs = explode('-', $this->input->post('slug'));
			$id = array_pop($slugs);
            
            $this->load->model('certificate/SchoolCertificate_model', 'school_certificate_model');

            if ($certificate_info = $this->school_certificate_model->get($id)) {
                $certificate_image 	= $this->config->item('cloudfront_url') .   $this->config->item('s3_school_certificates') . $certificate_info['image'] . '.png';
                $certififcate_pdf	= USER_INVOICE_URL . 'api/downloadSchoolCertfifcate/?code=' . urlencode($certificate_info['unique_id']);

                $this->json['certificate'] = [
                    'id' 	=> $certificate_info['id'],
                    'slug' => preg_replace('/-+/', '-',
                            trim(str_replace(['_'], ['-'],
                                    preg_replace('/\s+/', '-', strtolower($certificate_info['name']))
                                ), '-')
                        ) . '-' . $certificate_info['id'],
                    'name'	=> strpos($certificate_info['name'], '_') !== false ? _l($certificate_info['name']) : $certificate_info['name'],
                    'image' => $certificate_image,
                    'pdf' 	=> $certififcate_pdf,
                ];
            }
		}
	}

	public function getSchoolCertificates() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|numeric');
		// $this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		self::_runFormValidation();

		if (!$this->json) {
            $this->session->set_userdata('user_id', 166115);
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

			$author_info 	= $this->school_user_model->get($this->session->userdata('user_id'));
            $site_info      = $this->site_model->get($author_info['site_id']);
            $state_info 	= $this->state_model->get($site_info['state_id'] ?? 0);
		    $city_info 		= $this->city_model->get($site_info['city_id'] ?? 0);

            if (!empty($site_info) && strlen($site_info['name']) > 72) {
                $strcount 		= strlen($site_info['name']);
                $school_name 	= substr($site_info['name'],0,(72 - $strcount)) . '...';
            } else {
                $school_name 	= $site_info['name'];
            }
			
			$this->load->model('certificate/SchoolCertificateTemplate_model', 'school_certificate_template_model');
            $this->load->model('certificate/SchoolCertificate_model', 'school_certificate_model');

			$certificate_templates = $this->school_certificate_template_model->get_all([
				'event_id'		=> (int)$this->input->post('event_id'),
				'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
				'sort'			=> 'school_certificate_template.published_author_count',
				'order'			=> 'ASC',
			])['rows'] ?? [];

			$published_author = count(array_unique(array_column($this->event_book_model->get_all([
                'event_id' 	=> $event_info['id'],
                'site_id' 	=> $site_info['id'],
            ])['rows'] ?? [], 'user_id'))) ?? 0;

			$this->json['certificates'] = [];

			foreach ($certificate_templates as $key => $template) {
				$certificate = $this->school_certificate_model->get_all([
					'event_id'					        => $this->input->post('event_id') ?? 0,
					'school_certificate_template_id' 	=> $template['id'],
                    'site_id'                           => $site_info['id'],
                    'name'                              => $template['name']
				])['rows'][0] ?? [];

                // if (empty($certificate) && $published_author >= $template['published_author_count']) {
				// 	$certificate_key = sprintf('%s_school_%s', $template['name'], $author_info['site_id']);

				// 	$certificate_id = $this->school_certificate_model->add([
                //         'site_id'					        => $author_info['site_id'],
                //         'event_id'					        => $this->input->post('event_id'),
                //         'school_certificate_template_id'	=> $template['id'],
                //         'achievement'				        => $template['achievement'],
                //         'unique_id'					        => $template['id'],
                //         'name'						        => $template['name'],
                //         'image'						        => $certificate_key,
                //     ]);

				// } else {
				// 	continue;
				// } 

				if (empty($certificate) && empty($template['status'])) continue;

				$unlocked = !empty($certificate['id']);

				if (!empty($event_info) && strtotime($event_info['selling_end_date']) < time() && !$unlocked) {
					continue;
				}

				if (
					$unlocked &&
					empty(@getimagesize($this->config->item('cloudfront_url') .   $this->config->item('s3_school_certificates') . $certificate['image'] . '.png'))
				) {
					self::_genSchoolCertificatePdf([
						'event_id'		    => $template['event_id'],
						'image' 		    => $template['image'],
                        'design' 		    => !empty($template['design']) ? json_decode($template['design'], true) : [],
						'site_id' 		    => $site_info['id'] ?? 0,
						'school_name' 	    => $school_name  ?? '',
                        'authorized_person'	=> $site_info['authorized_person'] ?? '',
						'date' 			    => $certificate['date_added'],
						'cert_unique_id'    => $certificate['unique_id'],
						'image_name'        => $certificate['image'],
                        'state'				=> $state_info['name'] ?? 'state',
			            'city'				=> $city_info['name'] ?? 'city',
					], false);
				}

				$this->json['certificates'][] = $unlocked ? [
					'template_id'	=> $template['id'],
					'unlock' 		=> $unlocked,
					'id' 			=> $certificate['id'],
					'slug'      => preg_replace('/-+/', '-',
                            trim(str_replace(['_'], ['-'],
                                    preg_replace('/\s+/', '-', strtolower($certificate['name']))
                                ), '-')
                        ) . '-' . $certificate['id'],
                    'name' 			=> strpos($certificate['name'], '_') !== false ? _l($certificate['name']) : $certificate['name'],
					'image' 		=> $this->config->item('cloudfront_url') .   $this->config->item('s3_school_certificates') . $certificate['image'] . '.png',
					'pdf' 			=> USER_INVOICE_URL . 'api/downloadSchoolCertfifcate/?code=' . urlencode($certificate['unique_id']),
				] : [
					'template_id'	=> $template['id'],
					'unlock' 		=> $unlocked,
					'message'		=> self::_getSchoolCertificateMessage($template, $published_author),
				];
			}
		}
	}

	private function _getSchoolCertificateMessage($template = [], $published_author = 0) {
		$certificate_name	= _l(str_replace('_cert', '_certificate', $template['name']));
		$required_qty 		= $template['published_author_count'] - $published_author;

		if ($required_qty > 0) {
			$author = sprintf(($required_qty == 1 ? '%sauthor' : '%sauthors'), !empty($published_author) ? 'more ' : '');
			return sprintf(_li('Register %s %s to unlock %s'), $required_qty, $author, $certificate_name);
		}

		return sprintf(_li('Your relevant Certificate Generation is in process. Please refresh this page in 5 mins.'));
	}

	public function downloadSchoolCertfifcate() {
		if (!$this->json) {
			if (!$this->session->userdata('user_id')) {
				$this->json['error'] = 'unauthorized';
				return;
			}

			$certificate_code = urldecode($this->input->get('code'));

			$this->load->model('certificate/SchoolCertificateTemplate_model', 'school_certificate_template_model');
			$this->load->model('certificate/SchoolCertificate_model', 'school_certificate_model');

            $certificate_info = $this->school_certificate_model->getByCode($certificate_code);

			if (!empty($certificate_info)) {
				$author_info 	= $this->school_user_model->get($this->session->userdata('user_id'));

                $site_info      = $this->site_model->get($author_info['site_id']);
                $state_info 	= $this->state_model->get($site_info['state_id'] ?? 0);
		        $city_info 		= $this->city_model->get($site_info['city_id'] ?? 0);

                if (!empty($site_info) && strlen($site_info['name']) > 72) {
                    $strcount 		= strlen($site_info['name']);
                    $school_name 	= substr($site_info['name'],0,(72 - $strcount)) . '...';
                } else {
                    $school_name 	= $site_info['name'];
                }

				$certificate_template_info = $this->school_certificate_template_model->get($certificate_info['school_certificate_template_id']);

				self::_genSchoolCertificatePdf([
					'event_id'		    => $certificate_template_info['event_id'],
					'image' 		    => $certificate_template_info['image'],
					'design' 		    => !empty($certificate_template_info['design']) ? json_decode($certificate_template_info['design'], true) : [],
					'site_id' 		    => $site_info['id'] ?? 0,
                    'school_name' 	    => $school_name  ?? '',
                    'authorized_person'	=> $site_info['authorized_person'] ?? '',
					'date' 			    => $certificate_info['date_added'],
					'cert_unique_id'    => $certificate_info['unique_id'],
					'image_name'        => $certificate_info['image'],
					'state'				=> $state_info['name'] ?? 'state',
			        'city'				=> $city_info['name'] ?? 'city',
				]);
			} else {
				$this->json['certificate'] = _l('certificate_not_found');
			}
		}
	}

	private function _generateSchoolCertQrCode($data = NULL) {
		if (empty($data)) return;

		$file = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $data['image']);

		return generateQrCode('http://www.bribooks.com/verifycertificate/' . $data['cert_unique_id'], 20,2, $file);
	}

	private function _genSchoolCertificatePdf($data = [], $gen_pdf = TRUE) {
		if (empty($data['design'])) {
			return self::_genSchoolCertificateWithoutDesignPdf($data, $gen_pdf);
		}

		self::_genSchoolCertificateWithDesignPdf($data, $gen_pdf);
	}

	private function _genSchoolCertificateWithDesignPdf($data = [], $gen_pdf = TRUE) {
		log_kb(['_genSchoolCertificatePdf' => $data]);

		$design = $data['design'] ?? [];

		if (!empty($data['event_id']) && $data['event_id'] < 9) {
			return self::_genSchoolCertificateOldPdf($data, $gen_pdf);
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

		$image_width		= imagesx($image);
		$image_height		= imagesy($image);
		$qr_image_width		= imagesx($qr_image);
		$qr_image_height 	= imagesy($qr_image);

		$font_path 			= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';
		$font_size 			= $design['font_size'] ?? 28;

        self::_renderSchoolText(
            $image,
            $font_size,
            $font_path,
            $darkgrey,
            $design['school_name']['x'],
            $design['school_name']['y'],
            strtoupper($data['school_name']),
            $design['school_name']['align']
        );
        self::_renderSchoolText(
            $image,
            $font_size,
            $font_path,
            $darkgrey,
            $design['city']['x'],
            $design['city']['y'],
            strtoupper($data['city']),
            $design['city']['align']
        );
        self::_renderSchoolText(
            $image,
            $font_size,
            $font_path,
            $darkgrey,
            $design['state']['x'],
            $design['state']['y'],
            strtoupper($data['state']),
            $design['state']['align']
        );
		self::_renderSchoolText(
			$image,
			$font_size * 0.4,
			$font_path,
			$darkgrey,
			$design['unique_id']['x'],
			$design['unique_id']['y'],
			$data['cert_unique_id']
		);
		self::_renderSchoolText(
			$image,
			$font_size * 0.6,
			$font_path,
			$darkgrey,
			$design['date']['x'],
			$design['date']['y'],
			date('d/m/Y', strtotime($data['date']))
		);

		$zoom = 5.5;

		imagecopyresampled(
            $image,
            $qr_image,
            (int)$design['qr']['x'],
            (int)$design['qr']['y'],
            0,
            0,
            (int)round($qr_image_width / $zoom),
            (int)round($qr_image_height / $zoom),
            (int)$qr_image_width,
            (int)$qr_image_height
        );

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

        if (ENVIRONMENT === 'production') {
            // upload to s3 bucket and share the cloudfront url
            log_kb(['SchoolGenerareCertififcate::Withdesign::' => $this->s3->amazonS3Upload(
                $data['image_name'] . '.png',
                $filename,
                rtrim($this->config->item('s3_school_certificates'), '/')
            )]);
		}

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
			$dompdf->setPaper('A4', $design['layout'] ?? 'landscape');

			// Render the HTML as PDF
			$dompdf->render();
			$dompdf->stream($data['image_name'] . '.pdf');
		}

		unlink($filename);
	}

	private function _renderSchoolText($image, $font_size = 28, $font_path = '', $color = '', $x = 0, $y = 0, $text = '', $align = 'left') {
		if ($align == 'center') {
			$image_width= imagesx($image);
			$bbox 		= imagettfbbox($font_size, 0, $font_path, $text);
			$text_width = $bbox[2] - $bbox[0];
			$x 			= ($image_width - $text_width) / 2;
		} elseif ($align == 'right') {
			$image_width= imagesx($image);
			$bbox 		= imagettfbbox($font_size, 0, $font_path, $text);
			$text_width = $bbox[2] - $bbox[0];
			$x 			= $image_width - $text_width - $x;
		}

		imagettftext($image, $font_size, 0, $x, $y, $color, $font_path, $text);
	}

	private function _genSchoolCertificateWithoutDesignPdf($data = [], $gen_pdf = TRUE) {
		log_kb(['_genSchoolCertificatePdf' => $data]);

		if (!empty($data['event_id']) && $data['event_id'] < 9) {
			return self::_genSchoolCertificateOldPdf($data, $gen_pdf);
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

		$image_width	    = imagesx($image);
		$image_height	    = imagesy($image);
		$qr_image_width		= imagesx($qr_image);
		$qr_image_height 	= imagesy($qr_image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

		if (!empty($data['school_name']) && strlen($data['school_name']) > 40) {
			$font_size 	= 20;
		} else {
			$font_size 	= 28;
		}

        imagettftext($image, $font_size, 0, 120, 610, $darkgrey, $font_path, strtoupper($data['school_name']));
        imagettftext($image, $font_size, 0, 120, 810, $darkgrey, $font_path, strtoupper($data['city']));
        imagettftext($image, $font_size, 0, 120, 910, $darkgrey, $font_path, strtoupper($data['state']));

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

        if (ENVIRONMENT === 'production') {
            // upload to s3 bucket and share the cloudfront url
            log_kb(['SchoolGenerareCertififcate::WithoutDesign::' => $this->s3->amazonS3Upload(
                $data['image_name'] . '.png',
                $filename,
                rtrim($this->config->item('s3_school_certificates'), '/')
            )]);
        }

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

	private function _genSchoolCertificateOldPdf($data = [], $gen_pdf = TRUE) {
		log_kb(['_genSchoolCertificateOldPdf' => $data]);

		$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $data['image']);

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);

		$image_width	= imagesx($image);
		$image_height	= imagesy($image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';
		$font_size 	= 32;

		$school_name_length = (int)((mb_strlen($data['school_name'], 'utf-8') * ($font_size - 7)) / 2) ?? 400;
		$city_name_length 	= (int)((mb_strlen($data['city'], 'utf-8') * ($font_size - 7)) / 2) ?? 400;
        $state_name_length 	= (int)((mb_strlen($data['state'], 'utf-8') * ($font_size - 7)) / 2) ?? 400;
    
        imagettftext($image, $font_size, 0, 900 - $school_name_length, 700, $darkgrey, $font_path, strtoupper($data['author_name']));
        imagettftext($image, $font_size, 0, 900 - $city_name_length, 870, $darkgrey, $font_path, strtoupper($data['book_name']));
        imagettftext($image, $font_size, 0, 900 - $state_name_length, 935, $darkgrey, $font_path, strtoupper($data['state']));
		imagettftext($image, 18, 0, 540, 1140, $darkgrey, $font_path, date('d/m/Y', strtotime($data['date'])));
		
		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

        if (ENVIRONMENT === 'production') {
            // upload to s3 bucket and share the cloudfront url
            log_kb(['SchoolGenerareCertififcate::OldPdf::' => $this->s3->amazonS3Upload(
                $data['image_name'] . '.png',
                $filename,
                rtrim($this->config->item('s3_school_certificates'), '/')
            )]);
        }

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
