<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventDataFormatting {
	private function _getEventAttachments($row = [], $key = 0) {
		$attachment = [];

		if ($row['email_attachment_type']) {
			$attachment	= [
				FCPATH . (strpos($row['attachment_file'] , 'uploads') !== false
					? $row['email_attachment_file']
					: 'uploads/gallery/' . $row['email_attachment_file']
				),
			];
		} elseif (
			!empty($row['event_id']) &&
			(
				!empty($row['parent_kit']) ||
				!empty($row['teacher_kit']) ||
				!empty($row['brochure']) ||
				!empty($row['leaflet']) ||
				!empty($row['school_report_pdf'])
			)
		) {
			$site_info 	= $this->site_model->get($row['site_id']);
			$event_info	= $this->event_model->get($row['event_id']);
			$state_info = $this->state_model->get($site_info['state_id'] ?? 0);
			$city_info  = $this->city_model->get($site_info['city_id'] ?? 0);

			$designation= '';

			if (!empty($school_info = $this->school_model->getBySiteID($row['site_id']))) {
				$template_filter['type']	= $school_info['tag'] ?? '';
				$designation				=  strtolower($school_info['designation']) == 'others' ? '' : $school_info['designation'];
			}

			if ($row['event_id'] > 30) {
				$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
				$this->load->model('event/EventBrochure_model', 'event_brochure_model');

				$communication_kit_info = $this->event_communication_kit_model->get_all([
					'event_id' => $row['event_id']
				])['rows'][0]['school'] ?? '';

				$brochure_info 			= $this->event_brochure_model->get_all([
					'event_id' => $row['event_id']
				])['rows'][0] ?? [];

				if (empty($communication_kit_info)) return;

				$communication_kit_info = json_decode($communication_kit_info, true);

				$communication_kit_info['site_id'] 	= $site_info['id'];
				$communication_kit_info['state_id'] = $site_info['state_id'];

				if (empty($communication_kit_info['email'])) {
					$communication_kit_info = self::_filterMarketingSchoolCommunication($communication_kit_info);
					if (empty($communication_kit_info)) return;
				}

				$student_url 			= vsprintf('%s/events/student/signup/%s?sid=%d', [
					$event_info['url'],
					$event_info['slug'],
					$site_info['id']
				]);

				$school_dashboard_url   = USER_SCHOOL_URL . 'login';
				$teacher_dashboard_url  = USER_SCHOOL_URL . 'teacher/login';
				$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));
				$qrcode_url_path 		= str_replace('var/www/html/', '', $qrcode_url);

				$kit_data = [
					'site_id'	  			=> $site_info['id'],
					'school_id'	  			=> $lead_info['school_id'] ?? 0,
					'event_id'	  			=> $row['event_id'],
					'authorized_person'	  	=> $site_info['authorized_person'],
					'school_name'	  		=> $site_info['name'],
					'owner_name'	  		=> $site_info['owner_name'],
					'email'	  				=> $site_info['owner_email'],
					'mobile'	  			=> $site_info['owner_mobile'],
					'state' 				=> $city_info['state'] ?? '',
					'city' 					=> $city_info['name'] ?? '',
					'designation' 			=> $designation,
					'student_url' 			=> $student_url,
					'qrcode_url' 			=> $qrcode_url,
					'qrcode_file' 			=> sprintf('<div class="text-center"><img style="width: 100px;" src="%s" alt="Registration QR Code"></div>', $qrcode_url_path),
					'student_url_link' 		=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
					'school_dashboard_url' 	=> $school_dashboard_url,
					'teacher_dashboard_url' => $teacher_dashboard_url
				];

				if (!empty($row['parent_kit'])) {
					$attachment[] 	= self::_createMArketingStudentCommunicationKitPDF($kit_data, $communication_kit_info['email']['attachment'], $brochure_info);
				}

				if (!empty($row['teacher_kit'])) {
					$attachment[] 	= self::_createMarketingTeacherCommunicationKitPDF($kit_data, $communication_kit_info['email']['attachment'], $brochure_info);
				}

				if (!empty($row['brochure'])) {
					$attachment[] 	= self::_createMarketingEbrochureCommunicationKitPDF($kit_data, $communication_kit_info['email']['attachment'], $brochure_info);
				}

				if (!empty($row['leaflet'])) {
					$attachment[] 	= self::_createMarketingLeafletPDF($kit_data, $communication_kit_info['email']['attachment'], $brochure_info);
				}
			} else {
				$template_filter = [
					'event_id'	  	=> $row['event_id'],
					'template_type' => 'school_signup_email',
				];

				$row['designation'] = $designation;

				$school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';

				if (!empty($school_template_info)) {
					$row['school_name'] 	= $site_info['name'];
					$row['state'] 			= $state_info['name'];
					$row['city'] 			= $city_info['name'];
					$row['authorized_person'] 			= $site_info['authorized_person'];

					if (!empty($row['parent_kit']) && !empty($school_template_info['parent_kit_body'])) {
						$student_url	= $event_info['url'] . '/student/signup/' . $row['site_id'] . '?utm_source=nyaf2024_9_9_SN_' . $row['event_id'];
						$qrcode_url 	= str_replace('var/www/html/', '', base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schooltestqr_%s.png', $site_info['id']))));

						$row['student_url'] 	= $student_url;
						$row['qrcode_url'] 		= $qrcode_url;

						$attachment[] 			= self::_createEventCommunicationKit($row, $school_template_info, 'parent');
					}

					if (!empty($row['parent_kit']) && !empty($school_template_info['has_student_leaflet']) && !empty($school_template_info['leaflet_image'])) {
						$attachment[] = self::_createSchoolStudentLeafletMarketing($row['site_id'], $row['event_id']);
					}

					if (!empty($row['teacher_kit']) && !empty($school_template_info['teacher_kit_body'])) {
						$teacher_url			= $event_info['url'] . '/teacher/signup/' . $row['site_id'] . '?utm_source=nyaf2024_9_9_TN';
						$teacher_qrcode_url 	= str_replace('var/www/html/', '', base_url(generateQrCode($teacher_url, 25, 2, sprintf('uploads/test/teachertestqr_%s.png', $site_info['id']))));

						$row['teacher_url'] 	= $teacher_url;
						$row['qrcode_url'] 		= $teacher_qrcode_url;

						$attachment[] 			= self::_createEventCommunicationKit($row, $school_template_info, 'teacher');
					}

					if (!empty($row['brochure'])) {
						$attachment[] 			= self::_createEventSchoolBrochureMarketing($row['site_id'], $row['event_id']);
					}
				}
			}

			if (!empty($row['school_report_pdf'])) {
				if (
					!empty($user_info = $this->db->get_where('users', [
						'site_id'	=>  $row['site_id'],
						'role_id'	=> 9,
						'status'	=> 1,
						'_deleted'	=> 0,
					])->row_array())
				) {
					$attachment[] = self::_getMarketingSchoolReport($row['event_id'], $row['site_id']);
				}
			}
		} else {
			$attachment = [];
		}

		return $attachment;
	}

	private function _getVariable($total) {
		if ($total < 100) {
			return 100 - $total;
		} elseif ($total < 250) {
			return 250 - $total;
		}
	}

	private function _getMedals($total = 0) {
		if ($total >= 1 && $total <= 9) {
			return [
				'sold'	=> 10 - $total,
				'medal'	=> 'Silver Star Medallion and  Emerging Author Certificate',
			];
		} elseif ($total >= 10 && $total <= 19) {
			return [
				'sold'	=> 20 - $total,
				'medal'	=> 'Gold Star Medallion and  Amazing Author Certificate',
			];
		} elseif ($total >= 20 && $total <= 29) {
			return [
				'sold'	=> 30 - $total,
				'medal'	=> 'Platinum Star Medallion and Emerging Entreprenuer Author Certificate',
			];
		} elseif ($total >= 30 && $total <= 49) {
			return [
				'sold'	=> 50 - $total,
				'medal'	=> 'ISBN and Nationally Published Author Certificate',
			];
		} elseif ($total >= 50 && $total <= 69) {
			return [
				'sold'	=> 70 - $total,
				'medal'	=> 'Amazon Listing and Globally Published Author',
			];
		} elseif ($total >= 70) {
			return [
				'sold'	=> 0,
				'medal'	=> 'You are a champion Entrepreneur Author',
			];
		}
	}

	private function _getEarnedMedals($total = 0) {
		$medals = [];

		if ($total >= 1) {
			$medals[] = 'Published Author Certificate';
		}

		if ($total >= 10) {
			$medals[] = 'Silver Star  Medallion and  Emerging Author Certificate';
		}

		if ($total >= 20) {
			$medals[] = 'Gold Star Medallion and  Amazing Author Certificate';
		}

		if ($total >= 30) {
			$medals[] = 'Platinum Star Medallion and Emerging Entreprenuer Author Certificate';
		}

		if ($total >= 50) {
			$medals[] = 'ISBN by Government of Education and  Nationally Published Author Certificate';
		}

		if ($total >= 70) {
			$medals[] = 'Amazon Listing and  Globally Published Author Certificate';
		}

		return implode(',', $medals);
	}

	private function _getNationalMedals($ranks, $user_rank = [], $total = 0) {
		$medals = [
			'ndtv'		=> 'Not Qualified',
			'crossword'	=> 'Not Qualified',
			'disney'	=> 'Not Qualified',
			'stage'		=> 'Not Qualified',
		];

		if ($total < 10 || empty($user_rank['rank'])) {
			return $medals;
		}

		if ($user_rank['rank'] <= 10) {
			$medals = [
				'ndtv'		=> 'Already Qualified',
				'crossword'	=> 'Already Qualified',
				'disney'	=> 'Already Qualified',
				'stage'		=> 'Already Qualified',
			];
		}

		if ($user_rank['rank'] > 10 && $user_rank['rank'] <= 40) {
			$medals = [
				'ndtv'		=> $ranks['variable_10'] . ' Copies Away',
				'crossword'	=> 'Already Qualified',
				'disney'	=> 'Already Qualified',
				'stage'		=> 'Already Qualified',
			];
		}

		if ($user_rank['rank'] > 40 && $user_rank['rank'] <= 100) {
			$medals = [
				'ndtv'		=> $ranks['variable_10'] . ' Copies Away',
				'crossword'	=> $ranks['variable_40'] . ' Copies Away',
				'disney'	=> 'Already Qualified',
				'stage'		=> 'Already Qualified',
			];
		}

		if ($user_rank['rank'] > 100 && $user_rank['rank'] <= 200) {
			$medals = [
				'ndtv'		=> $ranks['variable_10'] . ' Copies Away',
				'crossword'	=> $ranks['variable_40'] . ' Copies Away',
				'disney'	=> $ranks['variable_100'] . ' Copies Away',
				'stage'		=> 'Already Qualified',
			];
		}

		if ($user_rank['rank'] > 200) {
			$medals = [
				'ndtv'		=> $ranks['variable_10'] . ' Copies Away',
				'crossword'	=> $ranks['variable_40'] . ' Copies Away',
				'disney'	=> $ranks['variable_100'] . ' Copies Away',
				'stage'		=> $ranks['variable_200'] . ' Copies Away',
			];
		}

		log_kb([
			'user_rank'	=> $user_rank,
			'medals'	=> $medals,
		]);

		return $medals;
	}

	private function _createSchoolStudentLeafletMarketing($site_id = 0, $event_id = 0) {
		$this->load->model('event/BrochureTemplate_model', 'brochure_template_model');

		if (
			!empty($site_id) &&
			!empty($site_info = $this->site_model->get($site_id)) &&
			!empty($event_info = $this->event_model->get($event_id))
		) {
			$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');

			$template_filter = [
				'event_id'	  	=> $event_id,
				'template_type' => 'school_signup_email',
			];

			$designation = '';

			if (!empty($school_info = $this->school_model->getBySiteID($site_info['id']))) {
				$template_filter['type']	= $school_info['tag'] ?? '';
				$designation				=  strtolower($school_info['designation']) == 'others' ? '' : $school_info['designation'];
			}

			$school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';
			$event_info			 	= $this->event_model->get($event_id);
			$image 					= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . $school_template_info['leaflet_image'];

			if ($site_info['site_type'] == 5) {
				$student_url	= $event_info['url'] . '/v2/student/signup/' . $site_info['id'];
			} elseif ($site_info['site_type'] == 9) {
				$student_url	= $event_info['url'] . '/ne/student/signup/' . $site_info['id'];
				$image 			= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . 'leaflet/event22/ne_leaflet.jpg';
			} else {
				$student_url	= $event_info['url'] . '/student/signup/' . $site_info['id'];
			}

			if (!empty($school_template_info)) {
				$data['school_name'] 	= ucwords($site_info['authorized_person'] . ', ' . $site_info['name']);
				$data['image'] 			= $image;
				$data['url'] 			= str_replace('https://', '', $student_url);
				// $data['qr_file'] 		= str_replace('var/www/html/', '', base_url(generateQrCode(($student_url))));
				$data['qr_file'] 		= str_replace('var/www/html/', '', base_url(generateQrCode(($student_url), 20, 2, sprintf('uploads/test/testqr_leaflet_%s.png', $site_id))));
			} else {
				return;
			}

			$html = $this->load->view('common/leaflet/leaflet', $data, true);

			$dompdf = new Dompdf([]);

			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('dpi', 300);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			$dompdf->setPaper('A4', 'potrait');

			$dompdf->render();

			$file 	= vsprintf('uploads/pdfs/Student_notification_%s_%s.pdf',[
				$event_info['id'],
				$site_info['id']
			]);

			$output = $dompdf->output();

			file_put_contents(FCPATH . $file, $output);

			return FCPATH . $file;
		}
	}

	private function _createEventSchoolBrochureMarketing($site_id = 0, $event_id = 0) {
		$this->load->model('event/BrochureTemplate_model', 'brochure_template_model');

		if (!empty($site_id) && !empty($site_info = $this->site_model->get($site_id)) && !empty($event_info = $this->event_model->get($event_id))) {
			$brochure_info  = $this->brochure_template_model->get_all([
				'event_id' 	=> $event_id,
				'site_type' => $site_info['site_type'] ?? 1,
				'sort' 		=> 'sort_order',
				'order'		=> 'ASC'
			])['rows'] ?? [];

			$student_url	= $brochure_info[0]['student_url'] . 'student/signup/' . $site_info['id'];

			if (!empty($brochure_info)) {
				$type				= $brochure_info[0]['type'];
				$data['brochures'] 	= $brochure_info;
				$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');
				$data['url'] 		= $student_url;
				$data['qr_file'] 	= str_replace('var/www/html/', '', base_url(generateQrCode(($student_url), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $site_id))));
			}  else {
				return;
			}

			$html = $this->load->view(sprintf('common/brochure/brochure_%s', $type), $data, true);

			$dompdf = new Dompdf([]);

			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('dpi', 300);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			$dompdf->setPaper(
				[
					0,
					0,
					390,
					780
				],
				'portrait'
			);

			$dompdf->render();

			$file 	= vsprintf('uploads/pdfs/Student_brochure_%s_%s.pdf',[
				$event_info['id'],
				$site_info['id']
			]);

			$output = $dompdf->output();

			file_put_contents(FCPATH . $file, $output);

			return FCPATH . $file;
		}
	}

	private function _getMarketingSchoolReport($event_id = 0, $site_id = 0) {
		if (
			!empty($site_id) &&
			!empty($event_id) &&
			!empty($user_info = $this->db->get_where('users', [
				'site_id'	=> $site_id,
				'role_id'	=> 9,
				'status'	=> 1,
				'_deleted'	=> 0,
			])->row_array())
		) {
			$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

			$data 				= $this->schooldashboard_lib->getGradeWiseData($user_info['id'], $event_id);
			$new_data 			= $this->schooldashboard_lib->getSchoolDashboardReport($user_info['site_id'], $event_id);
			$data['event_id'] 	= $event_id;

			$new_html 			= '';

			if (
				strtolower($user_info['location']) != 'india'
			) {
				$html	= $this->load->view('common/report/school_report_us', $data, true);
				$new_html 	= $this->load->view('common/report/student_pdf_us', $new_data, true);
				// $html	= $this->load->view('common/report/school_report', $data, true);
			} else {
				$html 		= $this->load->view('common/report/school_report', $data, true);
				$new_html 	= $this->load->view('common/report/student_pdf', $new_data, true);
			}

			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$file 	= vsprintf('uploads/pdfs/School_report_%s_%s.pdf',[
				$event_id,
				$user_info['site_id']
			]);

			$output = $dompdf->output();

			file_put_contents(FCPATH . $file, $output);

			return FCPATH . $file;
		}

		return '';
	}

	private function _createMArketingStudentCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		if (empty($data)) return;

		$dir = FCPATH . 'uploads/communication_kit/user';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view(
			'common/communication_kit/user/content',
			[
				'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
				'content' 	=> self::formatCommonEmailContent($brochure_info['user_content'], $data),
				'header' 	=> $brochure_info['user_header'],
				'footer' 	=> $brochure_info['user_footer'],
			],
			true
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file = vsprintf('uploads/communication_kit/user/communication_kit_user_%d_%d.pdf',[
			$data['event_id'],
			$data['site_id']
		]);
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _createMarketingEbrochureCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		$dir = FCPATH . 'uploads/communication_kit/ebrochure';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');
		$data['brochures'] 	= json_decode($brochure_info['ebrochure'], true);
		$data['dynamic'] 	= $brochure_info['ebrochure_dynamic'];

		// $qrcode_url 	= str_replace('var/www/html/', '', base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schooltestqr_%s.png', $site_info['id']))));

		$data['student_url'] = str_replace('https://', '', $data['student_url']);

		$data['qrcode_url']  = str_replace('var/www/html/', '', base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id']))));


		$html = $this->load->view('common/communication_kit/ebrochure/v1', $data, true);

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper(
			[
				0,
				0,
				390,
				780
			],
			'portrait'
		);

		$dompdf->render();

		$file 	= vsprintf('uploads/communication_kit/ebrochure/Student_brochure_%s_%s.pdf',[
			$data['event_id'],
			$data['site_id']
		]);

		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _createMarketingTeacherCommunicationKitPDF($data = [], $attachment_types = [], $brochure_info = []) {
		if (empty($data)) return;

		$dir = FCPATH . 'uploads/communication_kit/teacher';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view(
			'common/communication_kit/teacher/content',
			[
				'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
				'content' 	=> self::formatCommonEmailContent($brochure_info['teacher_content'], $data),
				'header' 	=> $brochure_info['teacher_header'],
				'footer' 	=> $brochure_info['teacher_footer'],
			],
			true
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file = vsprintf('uploads/communication_kit/teacher/communication_kit_teacher_%d_%d.pdf',[
			$data['event_id'],
			$data['site_id']
		]);
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _filterMarketingSchoolCommunication($communication_kits = []) {
		$format_message = [];

		if (!empty($communication_kits)) {
			$format_message = array_filter($communication_kits, function($item) {
				return isset($item['region']) && $item['region'] === 'ALL';
			})[0] ?? [];

			$this->load->model('localisation/GroupRegion_model', 'group_region_model');

			foreach($communication_kits as $kit_info) {
				if (($kit_info['region'] ?? '') != 'ALL') {
					if (!empty($region_info = $this->group_region_model->get_all([
						'region_id' => $kit_info['region'] ?? 0,
						'state_id' 	=> $communication_kits['state_id']
					])['rows'][0] ?? []) && (!empty($region_info['state_name'] ?? ''))) {
						$format_message = $kit_info;
						break;
					}
				}
			}
		}

		return $format_message;
	}

	private function _createMarketingLeafletPDF($data = [], $attachment_types = [], $brochure_info = []) {
		$dir = FCPATH . 'uploads/communication_kit/leaflet';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$data['student_url'] 	= str_replace('https://', '', $data['student_url']);
		$data['qrcode_url'] 	= base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id'])));

		$data['leaflet'] 	= $brochure_info['leaflet'];
		$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');

		$html = $this->load->view('common/communication_kit/leaflet/v1', $data, true);

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper('A4', 'potrait');

		$dompdf->render();

		$file 	= vsprintf('uploads/communication_kit/leaflet/Student_notification_%s_%s.pdf',[
			$data['event_id'],
			$data['site_id']
		]);

		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}
}
