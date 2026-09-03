<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait MessageTemplateAlert {
	public function genericMessageTemplate($data = []) {
		if (empty($data['code']) || empty($data['data'])) return;

		log_kb(['genericMessageTemplate::' => $data]);

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('common/MessageTemplate_model', 'message_template_model');

		$site_id			= $data['site_id'] ?? 1;
		$template_info 		= $this->message_template_model->getByCode($data['code'], $site_id);
		if (empty($template_info['status'])) return;

		$id			 		= $data['id'] ?? 0;
		$code		   		= $data['code'] ?? '';
		$schedule_time  	= $template_info['schedule_time'] ?? 0;
		$email		  		= $data['email'] ?? '';
		$mobile		 		= $data['mobile'] ?? '';
		$template_id		= $template_info['id'] ?? '';

		if ($schedule_time == 0) {
			$this->genericMessageTemplateCron([
				'email'			=> $email,
				'mobile'		=> $mobile,
				'template_id'	=> $template_id,
				'type'			=> $code,
				'includes'		=> $data['includes'] ?? [],
				'data'			=> $data['data'],
			]);
		} else {
			$code = sprintf('genericMessageTemplateCron_%s_%s', $code, $id);

			if (!empty($data['alert_once'])) {
				$code_info = $this->cron_model->getByCode($code);
				if (!empty($code_info)) return;
			}

			$this->cron_model->add([
				'code'			=> $code,
				'action'		=> 'alert_model->genericMessageTemplateCron',
				'site_id'		=> 1,
				'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes', $schedule_time))),
				'data'			=> [[
					'email'			=> $email,
					'mobile'		=> $mobile,
					'template_id'	=> $template_id,
					'type'			=> $code,
					'includes'		=> $data['includes'] ?? [],
					'data'			=> $data['data'],
				]],
			]);
		}
	}

	public function genericMessageTemplateCron($data = []) {
		if (empty($data['template_id']) || empty($data['data'])) return;

		$this->load->model('common/MessageTemplate_model', 'message_template_model');

		if (empty($template_info = $this->message_template_model->get($data['template_id']))) return;

		$includes = !empty($data['includes']) ? $data['includes'] : ['email', 'sms', 'whatsapp'];

		$email_template_info	= json_decode($template_info['email'], true);
		$whatsapp_template_info = json_decode($template_info['whatsapp'], true);
		$sms_template_info	  	= json_decode($template_info['sms'], true);

		$email				  	= $data['email'];
		$mobile				 	= $data['mobile'];

		// Email part
		if (
			!empty($email_template_info['subject'] ?? '') &&
			!empty($email_template_info['message'] ?? '') &&
			!empty($email) &&
			in_array('email', $includes)
		) {
			$bcc		= !empty($email_template_info['bcc']) ? explode(',', $email_template_info['bcc']) : [];
			$subject 	= format_message_with_data($email_template_info['subject'], $data['data']);
			$message 	= format_message_with_data($email_template_info['message'], $data['data']);
			$attachment = self::_generateEmailAttachmentPDF($email_template_info['attachment'] ?? '', $email_template_info['attachment_name'] ?? '', $data['data']);

			self::email(
				$email,
				$subject,
				$message,
				[],
				$bcc,
				$attachment,
			);
		}

		// Whatsapp
		if (
			!empty($whatsapp_template_info['template_id'] ?? '') &&
			!empty($whatsapp_template_info['message'] ?? '') &&
			in_array('whatsapp', $includes)
		) {
			$whatsapp_data = [
				'template_id' => $whatsapp_template_info['template_id'] ?? '',
			];

			if (!empty($whatsapp_template_info['message'])) {
				$whatsapp_data['parameters'] = format_whatsapp_sms_message($whatsapp_template_info['message'], $data['data']);
			}

			$type = WHATSAPP_ATTACHMENT_TYPES['onextel'][$whatsapp_template_info['type']] ?? '';

			if ($whatsapp_template_info['attachment_file']) {
				if (!empty($type)) {
					$path_parts = pathinfo(strpos($whatsapp_template_info['attachment_file'] , 'uploads') !== false
						? $whatsapp_template_info['attachment_file']
						: 'uploads/gallery/' . $whatsapp_template_info['attachment_file']
					);

					$extension  = strtolower($path_parts['extension'] ?? '');

					if (empty($extension)) {
						$attachment_extension = [
							'NONE' 	=> 'pdf',
							'DOC' 	=> 'pdf',
							'IMAGE' => 'png',
							'VIDEO' => 'mp4',
						];

						$extension = $attachment_extension[$type] ?? 'pdf';
					}

					$file_name = sprintf('%s_%s.%s', date('Y_m_d'), time(), $extension);

					$whatsapp_data['media'] = [
						'type' 		=> $type,
						'url'		=> strpos($whatsapp_template_info['attachment_file'], 'http') !== false
							? $whatsapp_template_info['attachment_file']
							: str_replace('/var/www/html', '', base_url(strpos($whatsapp_template_info['attachment_file'] , 'uploads') !== false
								? $whatsapp_template_info['attachment_file']
								: 'uploads/gallery/' . $whatsapp_template_info['attachment_file']
							)),
						'fileName'	=> $file_name,
					];
				}
			}

			if (!empty($whatsapp_template_info['cta_type']) && !empty($whatsapp_template_info['cta_var'])) {
				$cta_variable = format_message_with_data($whatsapp_template_info['cta_var'], $data['data']);

				$whatsapp_data['buttons'][] = [
					'type'		=> $whatsapp_template_info['cta_type'],
					'payload' 	=> (string)$cta_variable
				];
			}

			!empty($mobile) && self::_sendOnextelWhatsapp($mobile, $whatsapp_data);
		}

		//SMS
		if (
			(!empty($sms_template_info['gateway'] ?? '') || !empty($sms_template_info['message'] ?? '')) &&
			in_array('sms', $includes)
		) {
			$sms_message = format_message_with_data($sms_template_info['message'], $data['data']);
			log_kb(['sms::' => [
				'mobile' 		=> $mobile,
				'message' 		=> $sms_message,
				'gateway' 		=> $sms_template_info['gateway'],
				'template_id'	=> $sms_template_info['template_id'],
			]]);
			self::sms([
				'mobile' 		=> $mobile,
				'message' 		=> $sms_message,
				'gateway' 		=> $sms_template_info['gateway'],
				'template_id'	=> $sms_template_info['template_id'],
			]);
		}
	}

	private function _generateEmailAttachmentPDF($attachment, $attachment_name, $data = []) {
		if (empty($attachment)) return;
		if (empty($data)) return;

		log_kb(['genericMessageTemplateCron::_generateEmailAttachmentPDF'=>[
			'attachment_name' 	=> $attachment_name,
			'data'				=> $data
		]]);

		$attachment = format_message_with_data($attachment, $data);

		$attachment_name = !empty($attachment_name)
			? format_message_with_data($attachment_name, $data)
			: sprintf('document_%s_%s.pdf', date('Y_m_d'), time());

		if (strtolower(pathinfo($attachment_name, PATHINFO_EXTENSION)) !== 'pdf') {
			$attachment_name .= '.pdf';
		}

		$attachment_name = preg_replace(
			'/[^A-Za-z0-9._-]/',
			'_',
			$attachment_name
		);

		$dir = FCPATH . 'uploads/email_attachments/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = $dir . $attachment_name;

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $attachment));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		file_put_contents($file, $dompdf->output());

		log_kb(['genericMessageTemplateCron::Dompdf' => ['file' => $file]]);

		return $file;
	}
}
