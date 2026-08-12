<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait CommunicationKit {
	public function getSchoolBrochure($site_id = 0, $event_id = 0) {
		$this->load->model('event/BrochureTemplate_model', 'brochure_template_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		if (empty($event_info = $this->event_model->get($event_id))) return;
		if (empty($site_info = $this->site_model->get($site_id))) return;

		$brochure_info 			= $this->event_brochure_model->get_all([
			'event_id' => $event_id
		])['rows'][0] ?? [];

		if (empty($brochure_info)) return;

		if ($event_info['id'] == 22) {
			if ($site_info['site_type'] == 5) {
				$student_url 			= vsprintf('https://www.yaf.bribooks.com/us/2024/v2/student/signup/%s', [
					$site_info['id']
				]);
			} else {
				$student_url 			= vsprintf('https://www.yaf.bribooks.com/us/2024/student/signup/%s', [
					$site_info['id']
				]);
			}
		} elseif (($event_info['id'] == 23)) {
			$student_url 			= vsprintf('https://www.yaf.bribooks.com/ae/2024/student/signup/%s', [
				$site_info['id']
			]);
		}  elseif (($event_info['id'] == 24)) {
			$student_url 			= vsprintf('https://www.yaf.bribooks.com/us/2024/ne/student/signup/%s', [
				$site_info['id']
			]);
		} else {
			$student_url 			= vsprintf('%s/events/student/signup/%s?sid=%d', [
				$event_info['url'],
				$event_info['slug'],
				$site_info['id']
			]);
		}

		$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

		$data = [
			'site_id'	  			=> $site_info['id'],
			'student_url' 			=> $student_url,
			'qrcode_url' 			=> $qrcode_url,
			'qrcode_file' 			=> sprintf('<div class="text-center"><img style="width: 100px;" src="%s" alt="Registration QR Code"></div>', $qrcode_url),
			'student_url_link' 		=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
		];


		$dir = FCPATH . 'uploads/communication_kit/ebrochure';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');
		$data['brochures'] 	= json_decode($brochure_info['ebrochure'], true);
		$data['dynamic'] 	= $brochure_info['ebrochure_dynamic'];

		$data['student_url'] = str_replace('https://', '', $data['student_url']);

		$data['qrcode_url'] 	= base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id'])));
		
		if (in_array($event_info['id'], [22,24])) {
			$html = $this->load->view('common/brochure/brochure_v2', $data, true);
		} else {
			$html = $this->load->view('common/communication_kit/ebrochure/v1', $data, true);
		}

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
			$event_id,
			$site_id
		]);

		$dompdf->stream($file);
	}

	public function getSchoolParentKit($site_id = 0, $event_id = 0) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		if (!empty($site_info = $this->site_model->get($site_id))) {
			$event_info             = $this->event_model->get($event_id);

			$this->load->model('localisation/State_model', 'state_model');
            $this->load->model('localisation/City_model', 'city_model');

            $state_info = $this->state_model->get($site_info['state_id']);
            $city_info  = $this->city_model->get($site_info['city_id']);

            $template_filter = [
                'event_id'      => $event_id,
                'template_type' => 'school_signup_email',
            ];

            $designation = '';

            if (!empty($school_info = $this->school_model->get_all([
                'site_id' => $site_info['id']
            ])['rows'][0] ?? '')) {
                $template_filter['type']    = $school_info['tag'] ?? '';
                $designation                =  strtolower($school_info['designation']) == 'others' ? '' : $school_info['designation'];
            }

			$school_dashboard_url   = 'https://www.schools.bribooks.com/login';
			$teacher_dashboard_url  = 'https://schools.bribooks.com/teacher/login';

			$school_name		= explode(',', $site_info['name'])[0] ?? '';

			$variables = [
				'site_id'	  	        => $site_info['id'],
				'school_id'	  	        => $school_info['id'] ?? 0,
				'event_id'	  	        => $event_id,
				'authorized_person'	  	=> $site_info['authorized_person'],
				'school_name'	  	    => $school_name,
				'owner_name'	  	    => $site_info['owner_name'],
				'email'	  	            => $site_info['owner_email'],
				'mobile'	  	        => $site_info['owner_mobile'],
				'state' 				=> $state_info['name'],
				'city' 					=> $city_info['name'],
				'designation' 			=> $designation,
				// 'student_url' 			=> $student_url,
				// 'qrcode_url' 			=> $qrcode_url,
				'school_dashboard_url' 	=> $school_dashboard_url,
				'teacher_dashboard_url' => $teacher_dashboard_url
			];

			if ($event_id > 30) {
				$brochure_info 			= $this->event_brochure_model->get_all([
					'event_id' => $event_id
				])['rows'][0] ?? [];

				if (empty($brochure_info)) return;

				$variables['student_url']	= vsprintf('%s/events/student/signup/%s?sid=%d', [
					$event_info['url'],
					$event_info['slug'],
					$site_info['id']
				]);

				$variables['student_url_link'] 	= sprintf('<a href="%s" target="_blank">%s</a>', ($variables['student_url'] ?? ''), ($variables['student_url'] ?? ''));

				$variables['qrcode_url']	= base_url(generateQrCode($variables['student_url'], 25, 2, sprintf('uploads/test/schooltestqr_%s.png', $site_id)));
				$variables['qrcode_file'] 	= sprintf('<div class="text-center"><img style="width: 100px;" src="%s" alt="Registration QR Code"></div>', $variables['qrcode_url']);

				$html = $this->load->view(
					'common/communication_kit/user/content',
					[
						'base_url' 	=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery'),
						'content' 	=> $this->alert_model->formatCommonEmailContent($brochure_info['user_content'], $variables),
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
		
				$file_name = 'Student_notification_' . (int)$site_id . '_' . $event_id . '.pdf';
	
				$dompdf->stream($file_name);
			} else {
				if (!empty($school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? [])) {
					if ($site_info['site_type'] == 5) {
						$variables['student_url']	= $event_info['url'] . '/v2/student/signup/' . $site_info['id'];
					} else {
						$variables['student_url']	= $event_info['url'] . '/student/signup/' . $site_info['id'];
					}
	
					$variables['qrcode_url']	= base_url(generateQrCode($variables['student_url'], 25, 2, sprintf('uploads/test/schooltestqr_%s.png', $site_id)));
	
					$content = $this->alert_model->formatCommonEmailContent($school_template_info['parent_kit_body'], $variables) ?? '';
	
					$data['content'] = $content;
	
					$html = $this->load->view('frontend/default/communication_kit/content', $data, true);
	
					$dompdf = new Dompdf([
						// 'debugLayout' 	=> true,
					]);
					$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
					$dompdf->set_option('isJavascriptEnabled', true);
					$dompdf->set_option('isRemoteEnabled', true);
					$dompdf->set_option('isHtml5ParserEnabled', true);
					$dompdf->setPaper('A4', 'potrait');
	
					$dompdf->render();
	
					$file_name = 'Student_notification_' . (int)$site_id . '_' . $event_id . '.pdf';
	
					$dompdf->stream($file_name);
				}
			}
		}
	}

	public function getSchoolTeacherKit($site_id = 0, $event_id = 0) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');
		$this->load->model('Alert_model', 'alert_model');

		if (!empty($site_info = $this->site_model->get($site_id))) {
		    $this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');

            $template_filter = [
                'event_id'      => $event_id,
                'template_type' => 'school_signup_email',
            ];

            $designation = '';

            if (!empty($school_info = $this->school_model->get_all([
                'site_id' => $site_info['id']
            ])['rows'][0] ?? '')) {
                $template_filter['type']    = $school_info['tag'] ?? '';
				$designation                =  strtolower($school_info['designation']) == 'others' ? '' : $school_info['designation'];
            }

            $school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';
			$event_info             = $this->event_model->get($event_id);

            if (!empty($school_template_info)) {
                $this->load->model('localisation/State_model', 'state_model');
                $this->load->model('localisation/City_model', 'city_model');

                $state_info = $this->state_model->get($site_info['state_id']);
                $city_info  = $this->city_model->get($site_info['city_id']);

                $student_url    = $event_info['url'] . '/student/' . $site_info['id'];
			    $qrcode_url 	= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/teachertestqr_%s.png', $site_id)));

                $school_dashboard_url   = 'https://www.schools.bribooks.com/login';
                $teacher_dashboard_url  = 'https://schools.bribooks.com/teacher/login';

			    $school_name		= explode(',', $site_info['name'])[0] ?? '';

                $variables = [
					'site_id'	  	        => $site_info['id'],
					'school_id'	  	        => $school_info['id'] ?? 0,
					'event_id'	  	        => $event_id,
					'authorized_person'	  	=> $site_info['authorized_person'],
					'school_name'	  	    => $school_name,
					'owner_name'	  	    => $site_info['owner_name'],
					'email'	  	            => $site_info['owner_email'],
					'mobile'	  	        => $site_info['owner_mobile'],
                    'state' 				=> $state_info['name'],
                    'city' 					=> $city_info['name'],
                    'designation' 			=> $designation,
                    'student_url' 			=> $student_url,
                    'qrcode_url' 			=> $qrcode_url,
                    'school_dashboard_url' 	=> $school_dashboard_url,
                    'teacher_dashboard_url' => $teacher_dashboard_url
				];


				$content = self::createEventTeacherCommunicationKitTest($variables, [] , $school_template_info) ?? '';
				// $content = $this->alert_model->formatCommonEmailContent($school_template_info['teacher_kit_body'], $variables) ?? '';

				// $data['content'] = $content;

				// $html = $this->load->view('frontend/default/communication_kit/content', $data, true);

				// $dompdf = new Dompdf([
				// 	// 'debugLayout' 	=> true,
				// ]);
				// $dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				// $dompdf->set_option('isJavascriptEnabled', true);
				// $dompdf->set_option('isRemoteEnabled', true);
				// $dompdf->set_option('isHtml5ParserEnabled', true);
				// $dompdf->setPaper('A4', 'potrait');

				// $dompdf->render();

				// $file_name = 'school_parent_communication_kit_' . (int)$site_id . '.pdf';

				// $dompdf->stream($file_name);
			}
		}
	}

	private function createEventTeacherCommunicationKitTest($site_info = [], $event_info = [], $school_template_info = []) {
		if (empty($site_info)) return;

		$dir = FCPATH . 'uploads/communication_kit/teacher';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}
		// echo 'here';die;

        $content = $this->alert_model->formatCommonEmailContent($school_template_info['teacher_kit_body'], $site_info) ?? '';

		$data['content'] = $content;

        $html = $this->load->view('frontend/default/communication_kit/content', $data, true);

		// echo $html;die;

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/teacher/Communication_Kit_Teacher_'. $site_info['event_id'] . '_' .$site_info['site_id'].'.pdf';
		// $output = $dompdf->output();
		// file_put_contents(FCPATH.$file, $output);

						$dompdf->render();

				$dompdf->stream($file);
	}

	public function getSchoolStudentLeaflet($site_id = 0, $event_id = 0) {
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		if (empty($event_info = $this->event_model->get($event_id))) return;
		if (empty($site_info = $this->site_model->get($site_id))) return;

		$dir = FCPATH . 'uploads/communication_kit/leaflet';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$brochure_info 			= $this->event_brochure_model->get_all([
			'event_id' => $event_id
		])['rows'][0] ?? [];

		if (empty($brochure_info)) return;

		if ($event_info['id'] == 22) {
			if ($site_info['site_type'] == 5) {
				$student_url 			= vsprintf('https://www.yaf.bribooks.com/us/2024/v2/student/signup/%s', [
					$site_info['id']
				]);
			} else {
				$student_url 			= vsprintf('https://www.yaf.bribooks.com/us/2024/student/signup/%s', [
					$site_info['id']
				]);
			}
		} elseif (($event_info['id'] == 24)) {
			$student_url 			= vsprintf('https://www.yaf.bribooks.com/us/2024/ne/student/signup/%s', [
				$site_info['id']
			]);
		} else {
			$student_url 			= vsprintf('%s/events/student/signup/%s?sid=%d', [
				$event_info['url'],
				$event_info['slug'],
				$site_info['id']
			]);
		}

		$qrcode_url 			= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

		$data = [
			'site_id'	  			=> $site_info['id'],
			'school_name'	  		=> $site_info['name'],
			'student_url' 			=> $student_url,
			'qrcode_url' 			=> $qrcode_url,
			'leaflet' 				=> $brochure_info['leaflet'],
			'base_url' 				=> $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery')
		];

		$data['student_url'] = str_replace('https://', '', $data['student_url']);

		$data['qrcode_url'] 	= base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id'])));

		if (in_array($event_info['id'], [22,24])) {
			$html = $this->load->view('common/leaflet/leaflet', $data, true);
		} else {
			$html = $this->load->view('common/communication_kit/leaflet/v1', $data, true);
		}

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper('A4', 'potrait');

		$dompdf->render();

		$file 	= vsprintf('uploads/communication_kit/leaflet/Student_notification_%s_%s.pdf',[
			$event_id,
			$site_id
		]);

		$dompdf->stream($file);
	}
}
