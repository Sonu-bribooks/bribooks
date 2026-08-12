<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BMMessageTemplateAlert {
	public function genericBMMessageTemplate($data = []) {
		if (empty($data['code']) || empty($data['data'])) return;

		log_kb(['genericBMMessageTemplate::' => $data]);

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('briminds/common/BMMessageTemplate_model', 'bm_message_template_model');

		$country_code			= $data['country_code'] ?? 'IN';
		$template_info 		= $this->bm_message_template_model->getByCode($data['code'], $country_code);
		if (empty($template_info['status'])) return;

		$id			 		= $data['id'] ?? 0;
		$code		   		= $data['code'] ?? '';
		$schedule_time  	= $template_info['schedule'] ?? 0;
		$email		  		= $data['email'] ?? '';
		$mobile		 		= $data['mobile'] ?? '';
		$template_id		= $template_info['id'] ?? '';

		if ($schedule_time == 0) {
			log_kb(['genericBMMessageTemplate::Instant' => $data]);

			$this->genericBMMessageTemplateCron([
				'email'			=> $email,
				'mobile'		=> $mobile,
				'template_id'	=> $template_id,
				'type'			=> $code,
				'includes'		=> $data['includes'] ?? [],
				'data'			=> $data['data'],
			]);
		} else {
			$this->cron_model->add([
				'code'			=> sprintf('genericBMMessageTemplateCron_%s_%s', $code, $id),
				'action'		=> 'alert_model->genericBMMessageTemplateCron',
				'country_code'	=> $country_code,
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

	public function genericBMMessageTemplateCron($data = []) {
		if (empty($data['template_id']) || empty($data['data'])) return;

		$this->load->model('briminds/common/BMMessageTemplate_model', 'bm_message_template_model');

		if (empty($template_info = $this->bm_message_template_model->get($data['template_id']))) return;

		$includes = !empty($data['includes']) ? $data['includes'] : ['email', 'sms', 'whatsapp'];

		$email_template_info	= json_decode($template_info['email'], true);
		$whatsapp_template_info = json_decode($template_info['whatsapp'], true);
		$sms_template_info	  	= json_decode($template_info['sms'], true);

		$email				  	= $data['email'];
		$mobile				 	= $data['mobile'];

		log_kb([
			'genericBMMessageTemplateCron::temp-info' => $template_info,
			'genericBMMessageTemplateCron::email' => $email_template_info
		]);

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

			log_kb([
				'genericBMMessageTemplateCron::subject' => $subject,
				'genericBMMessageTemplateCron::message' => $message,
			]);

			self::email(
				$email,
				$subject,
				$message,
				[],
				$bcc,
				[],
				'no-reply@briminds.ai', 
				'BriMinds.AI', 
				'support@briminds.ai'
			);
		}

		// Whatsapp
		// if (
		// 	!empty($whatsapp_template_info['template_id'] ?? '') &&
		// 	!empty($whatsapp_template_info['message'] ?? '') &&
		// 	in_array('whatsapp', $includes)
		// ) {
		// 	$whatsapp_data = [
		// 		'template_id' => $whatsapp_template_info['template_id'] ?? '',
		// 	];

		// 	if (!empty($whatsapp_template_info['message'])) {
		// 		$whatsapp_data['parameters'] = format_whatsapp_sms_message($whatsapp_template_info['message'], $data['data']);
		// 	}

		// 	$type = WHATSAPP_ATTACHMENT_TYPES['onextel'][$whatsapp_template_info['type']] ?? '';

		// 	if ($whatsapp_template_info['attachment_file']) {
		// 		if (!empty($type)) {
		// 			$path_parts = pathinfo(strpos($whatsapp_template_info['attachment_file'] , 'uploads') !== false
		// 				? $whatsapp_template_info['attachment_file']
		// 				: 'uploads/gallery/' . $whatsapp_template_info['attachment_file']
		// 			);

		// 			$extension  = strtolower($path_parts['extension'] ?? '');

		// 			if (empty($extension)) {
		// 				$attachment_extension = [
		// 					'NONE' 	=> 'pdf',
		// 					'DOC' 	=> 'pdf',
		// 					'IMAGE' => 'png',
		// 					'VIDEO' => 'mp4',
		// 				];

		// 				$extension = $attachment_extension[$type] ?? 'pdf';
		// 			}

		// 			$file_name = sprintf('%s_%s.%s', date('Y_m_d'), time(), $extension);

		// 			$whatsapp_data['media'] = [
		// 				'type' 		=> $type,
		// 				'url'		=> strpos($whatsapp_template_info['attachment_file'], 'http') !== false
		// 					? $whatsapp_template_info['attachment_file']
		// 					: str_replace('/var/www/html', '', base_url(strpos($whatsapp_template_info['attachment_file'] , 'uploads') !== false
		// 						? $whatsapp_template_info['attachment_file']
		// 						: 'uploads/gallery/' . $whatsapp_template_info['attachment_file']
		// 					)),
		// 				'fileName'	=> $file_name,
		// 			];
		// 		}
		// 	}

		// 	if (!empty($whatsapp_template_info['cta_type']) && !empty($whatsapp_template_info['cta_var'])) {
		// 		$cta_variable = format_message_with_data($whatsapp_template_info['cta_var'], $data['data']);

		// 		$whatsapp_data['buttons'][] = [
		// 			'type'		=> $whatsapp_template_info['cta_type'],
		// 			'payload' 	=> (string)$cta_variable
		// 		];
		// 	}

		// 	!empty($mobile) && self::_sendOnextelWhatsapp($mobile, $whatsapp_data);
		// }

		//SMS
		if (
			(!empty($sms_template_info['gateway'] ?? '') || !empty($sms_template_info['message'] ?? '')) &&
			in_array('sms', $includes)
		) {
			$sms_message = format_message_with_data($sms_template_info['message'], $data['data']);
			log_kb(['bmsms::' => [
				'mobile' 		=> $mobile,
				'message' 		=> $sms_message,
				'gateway' 		=> $sms_template_info['gateway'],
				'template_id'	=> $sms_template_info['template_id'],
			]]);
            
			self::bmsms([
				'mobile' 		=> $mobile,
				'message' 		=> $sms_message,
				'gateway' 		=> $sms_template_info['gateway'],
				'template_id'	=> $sms_template_info['template_id'],
			]);
		}
	}
}
