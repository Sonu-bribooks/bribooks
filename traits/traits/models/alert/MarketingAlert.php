<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('marketing');
use Dompdf\Dompdf;

trait MarketingAlert {
	use MarketingData, EventDataFormatting;

	private function _applyFilters($info =  [], $override_table = '') {
		if (empty($info['filters'])) {
			return;
		}

		foreach ($info['filters'] as $key => $value) {
			if (is_array($value)) {
				$value = array_filter($value, function($item) {
					return !empty($item);
				});
			}

			if (!empty($value)) {
				list($table, $column) = explode('_', $key, 2);
				$field = vsprintf('%s.%s', [
					($override_table ? $override_table : $table),
					($override_table ? $key : $column)
				]);
				$this->db->where_in($field, $value);
			}
		}
	}

	public function marketingAlertCron($id) {
		$this->load->model('common/Marketing_model', 'marketing_model');
		$info = $this->marketing_model->get($id);

		if (empty($info)) return;

		log_kb(['marketingAlertCron::info:: ' => $info]);

		$rows = [];

		if ($info['frequency'] == 'daily') {
			$this->marketing_model->edit($id, [
				'status'		=> 1,
				'alert_date' 	=> date('Y-m-d H:i:s', strtotime('+1 days')),
				'date_sent'		=> date('Y-m-d H:i:s')
			]);
		} elseif ($info['frequency'] == 'weekly') {
			$this->marketing_model->edit($id, [
				'status'		=> 1,
				'alert_date' 	=> date('Y-m-d H:i:s', strtotime('+1 weeks')),
				'date_sent'		=> date('Y-m-d H:i:s')
			]);
		} else {
			$this->marketing_model->edit($id, [
				'status' 	=> 0,
				'date_sent'	=> date('Y-m-d H:i:s')
			]);
		}

		$rows = self::_getRows($info);

		$dataset_attachment = self::_getDataSetAttachment($info);

		$info['dataset_attachment'] = !empty($dataset_attachment) ? $dataset_attachment : '';

		$this->marketing_model->edit($id, [
			'total_users' 	=> count($rows),
			'sent_users' 	=> 0,
		]);

		if ($info['testing']) {
			if (strpos($info['user_type'], 'daily_registration_report_school') !== false) {
				if (!empty($filename = self::dailySiteStudentReportPdf($rows[0]['site_id'], $rows[0]['event_id']))) {
					$info['attachment_file'] = $info['email_attachment_file'] = $filename;
					$info['attachment_type'] = $info['email_attachment_type'] = 2;
				}
			}

			$rows = [
				array_merge($rows[0] ?? [], [
					'email'		=> get_settings('marketing_email') ? get_settings('marketing_email') : 'dm@bribooks.com',
					'mobile'	=> get_settings('marketing_mobile') ? get_settings('marketing_mobile') : '918794521181',
				])
			];

			self::_generateAlertChunk($rows, $info);
		} else {
			self::_generateAlertChunk($rows, $info);
		}
	}

	private function _generateAlertChunk($results = [], $info = []) {
		if (empty($results)) return;

		$no_threads = ceil(count($results) / (($info['thread_rate'] ?? 1) * 10));

		$this->load->model('common/AsyncTask_model', 'async_task_model');

		foreach (array_chunk($results, $no_threads, true) as $rows) {
			$this->async_task_model->add([
				'action'	=> 'Alert_model->marketingAlertThreadExecute',
				'data' 		=> [[
					'rows'		=> $rows,
					'info'		=> $info,
					'total'		=> count($results),
				]]
			]);
		}
	}

	public function marketingAlertThreadExecute($data = []) {
		$exclude_mobiles = $exclude_emails = [];

		$info 	= $data['info'] ?? [];
		$rows 	= $data['rows'] ?? [];
		$total 	= $data['total'] ?? 0;
		$flush_count = 0;

		foreach ($rows as $key => $row) {
			log_kb([
				'key'								=> $key,
				'sending::marketingAlertCron::info' => $info,
				'sending::marketingAlertCron::row' 	=> $row,
			]);

			if (empty($row['email']) && empty($row['mobile'])) continue;

			if (!empty($row['mobile']) && in_array($row['mobile'], $exclude_mobiles)) {
				$row['mobile'] = '';
			}

			if (!empty($row['email']) && in_array($row['email'], $exclude_emails)) {
				$row['email'] = '';
			}

			$exclude_mobiles[] 	= $row['mobile'];
			$exclude_emails[] 	= $row['email'];

			$mobile = preg_replace('/[^\d]/', '', ($row['mobile'] ?? ''));

			$filename = '';

			if (strpos($info['user_type'], 'daily_registration_report_school') !== false) {
				if (!empty($filename = self::dailySiteStudentReportPdf($row['site_id'], $info['event_id']))) {
					$info['attachment_file'] = $info['email_attachment_file'] = $filename;
					$info['attachment_type'] = $info['email_attachment_type'] = 2;
				}
			}

			if (!empty($row['attachment_details']) &&
				in_array(strtolower($row['attachment_details']), ['school_report_pdf']) &&
				!empty($info['event_id']) &&
				!empty($row['site_id'])
			) {
				$filename = self::_getSchoolReport($info['event_id'], $row['site_id']);

				if (!empty($filename)) {
					$info['attachment_file'] 		= $filename;
					$info['email_attachment_file'] 	= str_replace('/var/www/html', '', $filename);
					$info['attachment_type'] 		= $info['email_attachment_type'] = 2;
				}
			}

			self::_sendSingleData($row, $info, $key);

			$flush_count++;

			if ($flush_count >= 100) {
				self::_updateMarketingStats($flush_count, $info['id']);
				$flush_count = 0;
			}

			// usleep(30000);
		}

		if ($flush_count > 0) {
			self::_updateMarketingStats($flush_count, $info['id']);
		}
	}

	private function _updateMarketingStats($sent_count = 0, $id = 0) {
		$sent_count = (int)$sent_count;
		$this->db->set('sent_users', "sent_users + {$sent_count}", FALSE);
		$this->db->update('marketing', [
			'date_sent'		=> date('Y-m-d H:i:s'),
			'date_modified' => date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id,
		]);
	}

	private function _sendSingleData($row = [], $info = [], $key = 0) {
		if (!empty($row['attachment_file'])) {
			$attachment_file = $row['attachment_file'];
		} else {
			$attachment_file = $info['attachment_file'];
		}

		if (!empty($row['attachment_type'])) {
			$attachment_type = $row['attachment_type'];
		} else {
			$attachment_type = $info['attachment_type'];
		}

		log_kb([
			'_sendSingleData::data::info' 	=> $info,
			'_sendSingleData::data::row' 	=> $row,
		]);

		self::_marketingAlert([
			'subject'			=> self::_formatMarketingEmailMessage($info['subject'], $row, $info),
			'template_id'		=> $info['template_id'] ?? '',
			'email_template_id'	=> $info['email_template_id'] ?? 3,
			'message'			=> self::_formatMarketingEmailMessage($info['message'], $row, $info),
			'whatsapp_message'	=> self::_formatMarketingWhatsappMessage($info['whatsapp_message'], $row, $info),
			'whatsapp_cta_type'	=> $info['whatsapp_cta_type'] ?? 'URL',
			'whatsapp_cta'		=> self::_formatMarketingEmailMessage($info['whatsapp_cta'], $row, $info),
			'whatsapp_cta_var'	=> self::_getCurlyVariables($info['whatsapp_cta'], $row, $info),
			'sms'				=> self::_formatMarketingEmailMessage($info['sms'], $row, $info),
			'site_id'			=> $row['site_id'] ?? '',
			'student_id'		=> $row['user_id'] ?? '',
			'email'				=> $row['email'] ?? '',
			'mobile'			=> $row['mobile'] ?? '',
			'type'				=> $info['type'],
			'receiver_type'		=> $info['receiver_type'] ?? '',
			'unsubscribe_url'	=> $row['unsubscribe_url'] ?? '',
			'attachment_type'	=> $attachment_type,
			'attachment_file'	=> $attachment_file,
			'email_attachment_type'	=> $info['email_attachment_type'],
			'email_attachment_file'	=> $info['email_attachment_file'],
			'dataset_attachment'	=> !empty($info['dataset_attachment']) ? $info['dataset_attachment'] : '',
			'attachment_details'	=> !empty($info['attachment_details']) ? $info['attachment_details'] : '',
			'parent_kit'		=> $info['parent_kit'],
			'teacher_kit'		=> $info['teacher_kit'],
			'brochure'			=> $info['brochure'],
			'leaflet'			=> $info['leaflet'],
			'school_report_pdf'	=> $info['school_report_pdf'],
			'email_bcc_to'		=> $info['email_bcc_to'],
			'email_sender'		=> $info['email_sender'],
			'email_sender_name'	=> $info['email_sender_name'],
			'email_reply_to'	=> $info['email_reply_to'],
			'event_id'			=> $info['event_id'],
			'id'				=> $info['id'],
			'user_type'			=> $info['user_type'],
			'testing'			=> $info['testing'],
			'campaign_name'		=> $info['name'],
			'url'				=> $info['url'],
			'sms_gateway'		=> $info['sms_gateway'],
			'whatsapp_gateway'	=> $info['whatsapp_gateway'],
		], $key);

		// sleep(0.05);
	}

	private function _marketingAlert($row = [], $key = 0) {
		log_kb([
			'_marketingAlertRow' => $row
		]);

		if ($this->db->get_where('unsubscribed', [
			'email'	=> $row['mobile']
		])->row_array()) {
			return;
		}

		if ($this->db->get_where('unsubscribed', [
			'email'	=> $row['email']
		])->row_array()) {
			return;
		}

		if (in_array($row['type'], ['all', 'email', 'email_annoncement', 'email_sms', 'whatsapp_email', 'email_referral', 'email_whatsapp_referral'])) {
			$unsubscribe_url = '';

			if (!empty($row['receiver_type'])) {
				$unsubscribe_url = sprintf('%s?code=%s&utm_campaign=%s',
					UNSUBSCRIBE_URL,
					encrypt_data($row['receiver_type'] . '|' . $row['email']),
					$row['id']
				);
			}

			$data['title']			= $row['subject'];
			$data['heading']		= '';
			$data['subheading']		= '';
			$data['subheading']		= '';
			$data['content']		= $row['message'];
			$data['link']			= '';
			$data['link_text']		= '';
			$data['unsubscribe_url']= $unsubscribe_url;
			$data['footer_img']		= $row['event_id'] ? 'footer_' . $row['event_id'] . '.png' : '';
			$data['header_img']		= $row['event_id'] ? 'header_' . $row['event_id'] . '.png' : '';
			$message				= $this->load->view('common/mail/templates/' . $row['email_template_id'] . '/general', $data, true);

			$attachment 			= self::_getEventAttachments($row, $key);

			if (!empty($row['dataset_attachment'])) {
				$attachment[] = $row['dataset_attachment'];
			}

			if (!empty($row['email_bcc_to'])) {
				$bcc[] = $row['email_bcc_to'];
			}

			if ($key == 0) {
				$bcc[] = 'abhishek@youbooks.co';
			}

			!empty($row['email']) && self::email(
				$row['email'],
				$data['title'],
				$message,
				[],
				$bcc,
				$attachment,
				!empty($row['email_sender']) ? $row['email_sender'] : 'no-reply@bribooks.info',
				!empty($row['email_sender_name']) ? $row['email_sender_name'] : 'BriBooks',
				!empty($row['email_reply_to']) ? $row['email_reply_to'] : 'support@bribooks.com',
				[
					'X-BBCampaign' => $row['id'],
				]
			);
		}

		if (in_array(
			$row['type'],
			[
				'all',
				'whatsapp',
				'whatsapp_annoncement',
				'whatsapp_sms',
				'whatsapp_email',
				'whatsapp_referral',
				'email_whatsapp_referral'
			]
		)) {
			$whatsapp_gateway = strtolower($row['whatsapp_gateway'] ?? 'imiconnect');

			if (in_array($whatsapp_gateway, ['onextel', 'onextel_brisharks','onextel_briminds'])) {
				$data = [
					'template_id' => $row['template_id'] ?? '',
				];

				if (!empty($row['whatsapp_message'])) {
					$data['parameters'] = $row['whatsapp_message'];
				}

				if ($row['attachment_type']) {
					$type = WHATSAPP_ATTACHMENT_TYPES['onextel'][$row['attachment_type']] ?? '';

					log_kb([
						'WHATSAPP_GATEWAY_TYPE' => $type,
					]);

					if (!empty($type)) {
						$path_parts = pathinfo(strpos($row['attachment_file'] , 'uploads') !== false
							? $row['attachment_file']
							: 'uploads/gallery/' . $row['attachment_file']
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

						$data['media'] = [
							'type' 		=> $type,
							'url'		=> strpos($row['attachment_file'], 'http') !== false
								? $row['attachment_file']
								: str_replace('/var/www/html', '', base_url(strpos($row['attachment_file'] , 'uploads') !== false
									? $row['attachment_file']
									: 'uploads/gallery/' . $row['attachment_file']
								)),
							'fileName'	=> $file_name,
						];
					}
				}

				if (!empty($row['whatsapp_cta_type']) && !empty($row['whatsapp_cta_var'])) {

					$data['buttons'] = [];

					foreach ($row['whatsapp_cta_var'] as $cta_variable) {
						$data['buttons'][] = [
							'type'    => $row['whatsapp_cta_type'],
							'text'    => $cta_variable,
							'payload' => $cta_variable
						];
					}
				}

				// $gateway_company = $whatsapp_gateway == 'onextel_brisharks' ? 'brisharks' : 'bribooks';onextel_briminds
				if ($whatsapp_gateway == 'onextel_brisharks') {
					$gateway_company = 'brisharks';
				} elseif ($whatsapp_gateway == 'onextel_briminds') {
					$gateway_company = 'briminds';
				} else {
					$gateway_company = 'bribooks';
				}
				!empty($row['mobile']) && self::_sendOnextelWhatsapp($row['mobile'], $data, $gateway_company);
			} else {
				if ($row['attachment_type']) {
					$path_parts = pathinfo(strpos($row['attachment_file'] , 'uploads') !== false
						? $row['attachment_file']
						: 'uploads/gallery/' . $row['attachment_file']
					);

					// $type = ATTACHMENT_TYPES[$row['attachment_type']] ?? 'text';
					$type = WHATSAPP_ATTACHMENT_TYPES[$whatsapp_gateway][$row['attachment_type']] ?? 'text';

					log_kb([
						'WHATSAPP_MESSAGE_GATEWAY' => $whatsapp_gateway,
						'WHATSAPP_MESSAGE_GATEWAY_TYPE' => $type
					]);

					!empty($row['mobile']) && self::{'_sendWhatsapp' . $type}(
						$row['mobile'],
						[
							'template'		=> $row['template_id'],
							'parameters'	=> $row['whatsapp_message'],
							'url_parameters'=> $row['whatsapp_cta'],
							'document'	=> [
								'name'	=> $path_parts['filename'] ?? ('wh_camp_' . $key),
								'link'	=> strpos($row['attachment_file'], 'http') !== false
									? $row['attachment_file']
									: str_replace('/var/www/html', '', base_url(strpos($row['attachment_file'] , 'uploads') !== false
										? $row['attachment_file']
										: 'uploads/gallery/' . $row['attachment_file']
									)),
							]
						]
					);
				} else {
					!empty($row['mobile']) && self::_sendWhatsappText(
						$row['mobile'],
						[
							'template'		=> $row['template_id'],
							'parameters'	=> $row['whatsapp_message'],
							'url_parameters'=> $row['whatsapp_cta'],
						]
					);
				}

			}
		}

		if (
			!empty($row['sms_gateway']) &&
			in_array(
				$row['type'],
				[
					'all',
					'sms',
					'sms_annoncement',
					'whatsapp_sms',
					'email_sms',
					'sms_referral'
				]
			)
		) {
			$gateway = '';
			$api_key = '';

			switch (strtolower($row['sms_gateway'])) {
				case 'textlocal':
					$gateway = 'textlocal';
					$api_key = 'NzQ0YTZmNGM3MjMzNTU2YTUwNTg1MTc0NjE2Yjc5NjE=';
					break;

				case 'twilio':
					$gateway = 'twilio';
					$api_key = '';
					break;

				case 'vonage':
					$gateway = 'vonage';
					$api_key = '';
					break;

				case 'all':
					if (!empty($row['mobile']) &&
						strlen($row['mobile']) == 12 &&
						substr($row['mobile'], 0, 2) == 91
					) {
						$gateway = '2factor';
						$api_key = 'NzQ0YTZmNGM3MjMzNTU2YTUwNTg1MTc0NjE2Yjc5NjE=';
					} else {
						$gateway = 'twilio';
						$api_key = '';
					}
					break;

				default:
					$gateway = '2factor';
					break;
			}

			log_kb(['annoncement::sms:: ' => [
				'type'			=> $row['type'],
				'sms'			=> $row['sms'],
				'mobile'		=> $row['mobile'],
				'gateway'		=> $gateway,
				'api_key'		=> $api_key ?? ''
			]]);

			$gateway && !empty($row['mobile']) && self::marketing_sms(
				$row['mobile'],
				$row['sms'],
				$gateway,
			);
		}

		if (in_array($row['type'], ['email_annoncement', 'sms_annoncement', 'whatsapp_annoncement'])) {
			log_kb(['annoncement:: ' => [
				'type'			=> $row['type'],
				'template'		=> $row['id'],
				'user_id'		=> $row['student_id']
			]]);

			$this->load->model('user/UserAnnouncements_model', 'user_announcement_model');

			if ($row['testing']) {
				$row['student_id'] = 178;
			}

			if (!empty($row['student_id']) && empty($user_announcement_info = $this->user_announcement_model->getByUserId($row['student_id']))) {
				$type = WHATSAPP_ATTACHMENT_TYPES[$whatsapp_gateway][$row['attachment_type']] ?? 'text';

				$this->user_announcement_model->add([
					'template_id' 		=> $row['id'],
					'user_id' 			=> $row['student_id'],
					'subject' 			=> $row['subject'],
					'message' 			=> $row['message'],
					'url' 				=> $row['url'],
					'attachment_type' 	=> WHATSAPP_ATTACHMENT_TYPES[$whatsapp_gateway][$row['email_attachment_type']],
					'attachment_file' 	=> USER_INVOICE_URL . 'uploads/gallery/' . $row['email_attachment_file'],
					'status' 			=> 1,
					'_deleted' 			=> 0,
				]);
			} else {
				$this->user_announcement_model->edit($user_announcement_info['id'], [
					'template_id' 		=> $row['id'],
					'subject' 			=> $row['subject'],
					'message' 			=> $row['message'],
					'url' 				=> $row['url'],
					'attachment_type' 	=> WHATSAPP_ATTACHMENT_TYPES[$whatsapp_gateway][$row['email_attachment_type']],
					'attachment_file' 	=> USER_INVOICE_URL . 'uploads/gallery/' . $row['email_attachment_file'],
					'status' 			=> 1,
					'_deleted' 			=> 0,
				]);
			}
		}

		if (in_array($row['type'], ['email_referral', 'sms_referral', 'whatsapp_referral', 'email_whatsapp_referral'])) {
			log_kb(['referral:: ' => [
				'type'			=> $row['type'],
				'template'		=> $row['id'],
				'event_id'		=> $row['event_id;'],
				'user_id'		=> $row['student_id']
			]]);

			$this->load->model('user/UserReferral_model', 'user_referral_model');

			if ($row['testing']) {
				$row['student_id'] = 178;
			}

			if (!empty($row['student_id']) && empty($user_referral_info = $this->user_referral_model->getByUserId($row['student_id']))) {
				$this->user_referral_model->add([
					'event_id' 		=> $row['event_id'],
					'user_id' 		=> $row['student_id']
				]);
			} else {
				$this->user_referral_model->edit($user_referral_info['id'], [
					'event_id' 		=> $row['event_id'],
					'status' 		=> 1
				]);
			}
		}

		if (in_array($row['type'], ['app_notifications'])) {
			$this->load->model('user/UserDeviceToken_model', 'user_device_token_model');
			$this->load->model('user/UserAppNotification_model', 'user_app_notification_model');

			if ($token_info = $this->user_device_token_model->getByUser($row['student_id'])) {
				$payload = [
					'title'	=> $row['subject'],
					'body'	=> $row['sms'],
				];

				if ($row['attachment_type']) {
					$attachment_types = [
						1 => 'image',
						2 => 'document',
						3 => 'video',
					];

					$payload[$attachment_types[$row['attachment_type']]] = base_url(strpos($row['attachment_file'] , 'uploads') !== false
						? $row['attachment_file']
						: 'uploads/gallery/' . $row['attachment_file']
					);
				}

				$user_app_notification_id = $this->user_app_notification_model->add([
					'user_id'			=> $row['student_id'],
					'title'				=> $row['subject'],
					'url'				=> $row['url'],
					'body'				=> $row['sms'],
					'message'			=> $row['message'],
					'attachment_type'	=> $row['attachment_type'],
					'attachment_file'	=> $row['attachment_file'],
				]);

				$notification_info = $this->user_app_notification_model->get($user_app_notification_id);

				$payload['data'] = [
					'id'				=> $notification_info['id'],
					'title'				=> $notification_info['title'],
					'body'				=> $notification_info['body'],
					'message'			=> $notification_info['message'],
					'url'				=> $notification_info['url'],
					'attachment_type'	=> $notification_info['attachment_type'],
					'attachment_file'	=> $notification_info['attachment_type'] ? base_url(strpos($notification_info['attachment_file'] , 'uploads') !== false
						? $notification_info['attachment_file']
						: 'uploads/gallery/' . $row['attachment_file']
					) : '',
					'date_added'		=> $notification_info['date_added'],
				];

				$result = send_android_notification($token_info['device_token'], $payload);

				log_kb(['app_notifications:: ' => [
					'type'			=> $row['type'],
					'user_id'		=> $row['student_id'],
					'token'			=> $token_info['device_token'],
				]]);

				log_kb(['SendPush::' => $result]);
			}
		}

		if (in_array($row['type'], ['push_notifications'])) {
			$this->load->model('common/WebPushSubscriber_model', 'web_push_subscriber_model');

			if ($token_info = $this->web_push_subscriber_model->getByUser($row['student_id'])) {
				$payload = [
					'title'	=> $row['subject'],
					'body'	=> $row['sms'],
					'url'	=> $row['url'],
				];

				if ($row['attachment_type']) {
					$attachment_types = [
						1 => 'image',
					];

					$payload[$attachment_types[$row['attachment_type']]] = base_url(strpos($row['attachment_file'] , 'uploads') !== false
						? $row['attachment_file']
						: 'uploads/gallery/' . $row['attachment_file']
					);
				}

				$payload['data'] = [
					'id'				=> $row['student_id'] ?? 0,
					'title'				=> $row['title'],
					'body'				=> $row['body'],
					'message'			=> $row['message'],
					'url'				=> $row['url'],
					'attachment_type'	=> $row['attachment_type'],
					'attachment_file'	=> $row['attachment_type'] ? base_url(strpos($row['attachment_file'] , 'uploads') !== false
						? $row['attachment_file']
						: 'uploads/gallery/' . $row['attachment_file']
					) : '',
					'date_added'		=> date('Y-m-d H:i:s'),
				];

				$result = send_webpush_notification(trim($token_info['token']), $payload);

				log_kb(['push_notifications:: ' => [
					'type'			=> $row['type'],
					'user_id'		=> $row['student_id'],
					'token'			=> $token_info['token'],
				]]);

				log_kb(['SendWebPush::' => $result]);
			}
		}
	}

	private function _formatMarketingWhatsappMessage($message, $data = [], $info = []) {
		// preg_match_all('/\{(.+?)\}/ims', $message, $output);
		preg_match_all('/\{([^{}]+)\}/ims', $message, $output);
		$message_data = [];

		foreach ($output[1] ?? [] as $key) {
			// $value = isset($data[$key]) ? $data[$key] : $key;
			if (array_key_exists($key, $data)) {
				$value = $data[$key];
			} else {
				$value = preg_replace_callback('/\[\[([^\[\]]+)\]\]/', function($matches) use($data) {
					$key = trim($matches[1]);
					return $data[$key] ?? $matches[0];
				}, $key);
			}

			// if ($key === 'url' || strpos($key, '_url') !== false) {
			// 	$campaign_info = vsprintf('utm_source=%s&utm_medium=%s&utm_campaign=%s', [
			// 		'W' . $info['id'],
			// 		'whatsapp',
			// 		$info['name'],
			// 	]);
			//
			// 	$value = vsprintf('%s%s%s', [
			// 		$value,
			// 		strpos($value, '?') !== false ? '&' : '?',
			// 		$campaign_info
			// 	]);
			// }

			$message_data[] = $value;
		}

		return $message_data;
	}

	private function _formatMarketingEmailMessage($message, $data = [], $info = []) {
		$find = array_map(function($item) {
			return '{' . $item . '}';
		}, array_keys($data));

		// foreach ($data as $key => &$value) {
		// 	if ($key === 'url' || strpos($key, '_url') !== false) {
		// 		$campaign_info = vsprintf('utm_source=%s&utm_medium=%s&utm_campaign=%s', [
		// 			'E' . $info['id'],
		// 			'email',
		// 			$info['name'],
		// 		]);
		//
		// 		$value = vsprintf('%s%s%s', [
		// 			$value,
		// 			strpos($value, '?') !== false ? '&' : '?',
		// 			$campaign_info
		// 		]);
		// 	}
		// }

		$replace = $data;

		return str_replace($find, $replace, $message);
	}

	private function _getCurlyVariables($message = '', $data = [], $info = []){
		if (empty($message)) return [];

		preg_match_all('/\{(.*?)\}/', $message, $matches);

		if (empty($matches[1])) {
			return [];
		}

		$keys = $matches[1];

		$variables = array_map(fn($k) => $data[$k] ?? $k, $keys);

		return $variables;
	}

	private function _getSchoolReport($event_id = 0, $site_id = 0) {
		if (
			$site_id && $event_id &&
			$user_info = $this->db->get_where('users', [
				'site_id'	=> $site_id,
				'role_id'	=> 9,
				'status'	=> 1,
				'_deleted'	=> 0,
			])->row_array()
		) {
			$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

			$data 				= $this->schooldashboard_lib->getGradeWiseData($user_info['id'], $event_id);
			$new_data 			= $this->schooldashboard_lib->getSchoolDashboardReport($user_info['site_id'], $event_id);
			$data['event_id'] 	= $event_id;
			$event_info 		= $this->event_model->get($event_id);

			$grade_label = 'Grade';
			if (!empty($event_info) && $event_info['country_code'] == 'GB') {
				$grade_label = 'Year';
			}

			$new_html = '';

			if (
				strtolower($user_info['location']) != 'india'
			) {
				$data['grade_label'] 		= $grade_label;
				$new_data['grade_label'] 	= $grade_label;

				$html	= $this->load->view('common/report/school_report_us', $data, true);
				$new_html 	= $this->load->view('common/report/student_pdf_us', $new_data, true);
			} else {
				$html 		= $this->load->view('common/report/school_report', $data, true);
				$new_html 	= $this->load->view('common/report/student_pdf', $new_data, true);
			}


			$dompdf = new Dompdf();
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$file_name = sprintf('uploads/pdfs/Marketing_School_Report_%s_%s.pdf', $user_info['site_id'], $event_id);

			file_put_contents($file_name, $dompdf->output());

			return FCPATH . $file_name;
		}
	}

}
