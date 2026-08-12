<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventSchoolSignupAlert {
	public function eventSchoolSignupMail($site_id = 0, $event_id = 0) {
		if (!empty($site_info = $this->site_model->get($site_id))) {
			$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');

			$template_filter = [
				'event_id'	  	=> $event_id,
				'template_type' => 'school_signup_email',
			];

			$designation = '';

			if (!empty($school_info = $this->school_model->get_all([
				'site_id' => $site_info['id']
			])['rows'][0] ?? '')) {
				$template_filter['type']	= $school_info['tag'] ?? '';
				$designation				=  strtolower($school_info['designation']) == 'others' ? '' : $school_info['designation'];
			}

			$school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';
			$event_info			 	= $this->event_model->get($event_id);

			if (!empty($school_template_info)) {
				$this->load->model('localisation/State_model', 'state_model');
				$this->load->model('localisation/City_model', 'city_model');

				$state_info = $this->state_model->get($site_info['state_id']);
				$city_info  = $this->city_model->get($site_info['city_id']);

				if ($site_info['site_type'] == 5) {
					$student_url	= $event_info['url'] . '/v2/student/signup/' . $site_info['id'] . '?utm_source=nyaf2024_SN_' . $event_info['id'];
				} else {
					$student_url	= $event_info['url'] . '/student/signup/' . $site_info['id'] . '?utm_source=nyaf2024_SN_' . $event_info['id'];
				}

				$qrcode_url 	= base_url(generateQrCode($student_url, 25, 2, sprintf('uploads/test/schoolteachertestqr_%s.png', $site_info['id'])));

				$school_dashboard_url   = USER_SCHOOL_URL . 'login';
				$teacher_dashboard_url  = USER_SCHOOL_URL . 'teacher/login';

				$school_name		= explode(',', $site_info['name'])[0] ?? '';

				$variables = [
					'site_id'	  			=> $site_info['id'],
					'school_id'	  			=> $school_info['id'] ?? 0,
					'event_id'	  			=> $event_id,
					'authorized_person'	  	=> $site_info['authorized_person'],
					'school_name'	  		=> $school_name,
					'owner_name'	  		=> $site_info['owner_name'],
					'email'	  				=> $site_info['owner_email'],
					'mobile'	  			=> $site_info['owner_mobile'],
					'state' 				=> $state_info['name'],
					'city' 					=> $city_info['name'],
					'designation' 			=> $designation,
					'student_url' 			=> $student_url,
					'qrcode_url' 			=> $qrcode_url,
					'school_dashboard_url' 	=> $school_dashboard_url,
					'teacher_dashboard_url' => $teacher_dashboard_url
				];

				$subject = self::formatCommonEmailSubject($school_template_info['subject'], $variables) ?? '';

				$content = self::formatCommonEmailContent($school_template_info['body'], $variables) ?? '';

				$data['title']		  	= $subject;
				$data['heading']		= '';
				$data['subheading']	 	= '';
				$data['subheading']		= '';
				$data['content']		= $content;
				$data['link']		   	= '';
				$data['link_text']	  	= '';
				$message				= $this->load->view('common/mail/templates/site/general', $data, true);

				$attachment = [];

				if (!empty($school_template_info['parent_kit_body'])) {
					$attachment[] = self::_createEventCommunicationKit($variables, $school_template_info, 'parent');
				}

				if (!empty($school_template_info['teacher_kit_body'])) {
					if ($site_info['site_type'] == 5) {
						$teacher_url			= $event_info['url'] . '/v2/teacher/signup/' . $site_info['id'] . '?utm_source=nyaf2024_TN_' . $event_info['id'];
					} else {
						$teacher_url			= $event_info['url'] . '/teacher/signup/' . $site_info['id'] . '?utm_source=nyaf2024_TN_' . $event_info['id'];
					}
					$teacher_qrcode_url 	= base_url(generateQrCode($teacher_url, 25, 2));

					$variables['teacher_url'] 	= $teacher_url;
					$variables['qrcode_url'] 	= $teacher_qrcode_url;

					$attachment[] = self::_createEventCommunicationKit($variables, $school_template_info, 'teacher');
				}

				if (!empty($school_template_info['has_student_leaflet']) && !empty($school_template_info['leaflet_image'])) {
					$attachment[] = self::_createSchoolStudentLeaflet($site_id, $event_id);
				}

				if (!empty($school_template_info['has_brochure'])) {
					$attachment[] = self::_createEventSchoolBrochure($site_id, $event_id);
				}

				$email  = $school_info['owner_email'];
				$mobile = $school_info['owner_mobile'];

				if (!empty($subject) && !empty($content)) {
					self::email(
						$email,
						$subject,
						$message,
						[],
						ENVIRONMENT === 'production'
							? ['communication@bribooks.com']
							: [],
						$attachment
					);
				}

				if (!empty($school_template_info['whatsapp_template_id'])) {
					if ($school_template_info['has_pdf']) {
						$document	= [
							'name'	=> 'Communication Kit',
							'link'	=> base_url('uploads/communication_kit/parent/communication_kit_parent_'. $event_id . '_' .$site_info['id'].'.pdf'),
						];

						self::_sendWhatsappDocument(
							$mobile,
							[
								'template'		=> $school_template_info['whatsapp_template_id'],
								'parameters'	=> self::_formatMarketingWhatsappMessage($school_template_info['whatsapp_message'], $variables),
								'document'		=> $document
							]
						);
					} else {
						self::_sendWhatsappText(
							$mobile,
							[
								'template'		=> $school_template_info['whatsapp_template_id'],
								'parameters'	=> self::_formatMarketingWhatsappMessage($school_template_info['whatsapp_message'], $variables),
							]
						);
					}
				}
			}
		}
	}

	private function _createEventCommunicationKit($parameters = [], $school_template_info = [], $type = 'parent') {
		if (empty($parameters)) return;

		$dir = FCPATH . 'uploads/communication_kit/' . $type;

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$data['content'] = self::formatCommonEmailContent($school_template_info[$type . '_kit_body'], $parameters) ?? '';

		$html = $this->load->view('frontend/default/communication_kit/content', $data, true);

		$dompdf = new Dompdf([
			// 'debugLayout' 	=> true,
		]);
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file 	= vsprintf('uploads/communication_kit/%s/communication_kit_%s_%s_%s.pdf',[
			$type,
			$type,
			$parameters['event_id'],
			$parameters['site_id']
		]);
		$output = $dompdf->output();

		file_put_contents(FCPATH . $file, $output);

		return FCPATH . $file;
	}

	private function _createEventSchoolBrochure($site_id = 0, $event_id = 0) {
		$this->load->model('event/BrochureTemplate_model', 'brochure_template_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		if (!empty($site_id) && !empty($site_info = $this->site_model->get($site_id)) && !empty($event_info = $this->event_model->get($event_id))) {
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
				'student_url' 			=> $student_url,
				'qrcode_url' 			=> $qrcode_url,
				'qrcode_file' 			=> sprintf('<div class="text-center"><img style="width: 100px;" src="%s" alt="Registration QR Code"></div>', $qrcode_url),
				'student_url_link' 		=> sprintf('<a href="%s" target="_blank">%s</a>', $student_url, $student_url),
			];

			$data['base_url'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery');
			$data['brochures'] 	= json_decode($brochure_info['ebrochure'], true);
			$data['dynamic'] 	= $brochure_info['ebrochure_dynamic'];

			$data['student_url'] = str_replace('https://', '', $data['student_url']);

			$data['qrcode_url'] 	= base_url(generateQrCode(($data['student_url']), 20, 2, sprintf('uploads/test/testqr_brochure_%s.png', $data['site_id'])));

			$html = $this->load->view('common/brochure/brochure_v2', $data, true);

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

	private function _createSchoolStudentLeaflet($site_id = 0, $event_id = 0) {
		$this->load->model('event/BrochureTemplate_model', 'brochure_template_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');


		if (!empty($site_id) && !empty($site_info = $this->site_model->get($site_id)) && !empty($event_info = $this->event_model->get($event_id))) {

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
}
