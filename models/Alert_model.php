<?php defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . 'third_party/phpmailer/loader.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Twilio\Rest\Client;

load_trait('models/alert');

class Alert_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/teacher/TeacherLead_model', 'teacher_lead_model');

		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('common/Schedule_model', 'schedule_model');
		$this->load->model('common/Otp_model', 'otp_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/AddTemplate_model', 'addtemplate_model');

		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/Center_model', 'center_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/GroupRegion_model', 'group_region_model');

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('event/EventPdf_model', 'event_pdf_model');

		$this->load->model('school/School_model', 'school_model');
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');

		$this->load->model('ranking/RankingCountry_model', 'ranking_country_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('medallion//MedallionOrder_model', 'medallion_order_model');

		$this->load->library('Cron_lib', 'cron_lib');

		$this->admin_mobiles = [];

		$this->admin_emails = [
			'communication@bribooks.com',
		];

		if (ENVIRONMENT !== 'production') {
			$this->admin_emails = [];
		}

		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
	}

	use CommonAlert,
		BookAlert,
		SignupAlert,
		InvoiceAlert,
		WhatsappAlert,
		CouponAlert,
		GenericAlert,
		WebhookOrderAlert,
		DailyAlert,
		SchoolAutoApproved,
		SchoolSignupAlert,
		CampaignAlert,
		NotRegisterAlert,
		MarketingAlert,
		SchoolRequestAlert,
		SiteDailyExport,
		ForgotPasswordAlert,
		OrderProcessAlert,
		RoyaltyAlert,
		IsbnAssignAlert,
		PrinterAlert,
		AuthorAlert,
		CustomThemeAlert,
		CustomCoverAlert,
		DonationAlert,
		SCAuthorAlert,
		EventAlert,
		DirectShipmentsAlert,
		AmazonKdpOrderAlert,
		CartAlert,
		CertififcateMessageAlert,
		MedallionOrderProcessAlert,
		EventAppAlert,
		DemoAlert,
		OrderStatusUpdateAlert,
		AutoEscalateOrderAlert,
		RankingAlert,
		DataCleaningAlert,
		TeacherSignupAlert,
		ContentFormatAlert,
		EventSchoolSignupAlert,
		EventTaskAlert,
		EventSignupAlert,
		InternalAlert,
		SubscriptionAlert,
		ExchangeRateAlert,
		BookAiReviewAlert,
		BuildRankAlert,
		EnrolAlert,
		MessageTemplateAlert,
		AutoLeagueClosingAlert,
		InviteAlert,
		AcknowledgeAlert,
		BMMessageTemplateAlert
	;

	public function email($to, $subject, $message, $cc = NULL, $bcc = NULL, $attachment = NULL, $sender = NULL, $sender_name = 'BriBooks', $reply_to = 'support@bribooks.com', $headers = []) {
		if (empty($to)) return false;

		try {
			$to   	= ENVIRONMENT === 'production' ? $to : get_settings('testing_email');
			$sender = !empty($sender) ? $sender : EMAIL_ACCOUNTS[EMAIL_SERVICE]['sender'];

			$mail 	= new PHPMailer(true);

			$mail->CharSet 		= PHPMailer::CHARSET_UTF8;
			$mail->SMTPDebug 	= SMTP::DEBUG_OFF;
			$mail->Debugoutput 	= $this->config->item('log_path');

			if (strtolower(get_settings('protocol')) == 'smtp') {
				$mail->isSMTP();
			}

			$mail->Host			= EMAIL_ACCOUNTS[EMAIL_SERVICE]['host'];
			$mail->SMTPAuth   	= true;
			$mail->Username   	= EMAIL_ACCOUNTS[EMAIL_SERVICE]['username'];
			$mail->Password   	= EMAIL_ACCOUNTS[EMAIL_SERVICE]['password'];
			$mail->SMTPSecure 	= get_settings('smtp_crypto') ?? 'tls';
			$mail->Port	   		= get_settings('smtp_port');

			//Recipients
			$mail->setFrom($sender, ($sender_name ?? get_settings('system_name')));
			$mail->addAddress($to);
			$mail->addReplyTo($reply_to, get_settings('system_name'));

			// SES config set
			if (EMAIL_ACCOUNTS[EMAIL_SERVICE]['webhook']) {
				$mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'config-bb-ses');
			}

			if (!empty($headers) && is_array($headers)) {
				foreach ($headers as $key => $value) {
					$mail->addCustomHeader($key, $value);
				}
			}

			if ($cc) {
				if (!is_array($cc)) {
					$cc = explode(',', $cc);
				}

				foreach ($cc as $item) {
					$mail->addCC($item);
				}
			}

			if ($bcc) {
				if (!is_array($bcc)) {
					$bcc = explode(',', $bcc);
				}

				foreach ($bcc as $item) {
					$mail->addBCC($item);
				}
			}

			//Attachments
			if ($attachment) {
				if (!is_array($attachment)) {
					$attachment = [$attachment];
				}

				foreach ($attachment as $key => $value) {
					if (strpos($value, 'http') === 0) {
						$file_content = file_get_contents($value);
						$filename = basename(parse_url($value, PHP_URL_PATH));
						$mail->addStringAttachment($file_content, $filename);
					} else {
						$mail->addAttachment($value);
					}
				}
			}

			//Content
			$mail->isHTML(true);

			$mail->Subject 	= $subject;
			$mail->Body		= $message;
			$mail->AltBody 	= $subject;

			$mail->send();

			update_thirdparty_status(EMAIL_SERVICE, true, '');

			return true;
		} catch (Exception $e) {
			log_message('KB', print_r(['Email Error:: ' => $mail->ErrorInfo, 'Email To:: ' => $to], 1));

			update_thirdparty_status(EMAIL_SERVICE, false, $mail->ErrorInfo);

			return false;
		}
	}

	public function sms($args) {
		extract($args);

		$exclude = TESTING_MOBILES;

		if (in_array($mobile, $exclude)) {
			return;
		}

		$gateway = !empty($gateway) ? $gateway : strtolower($this->config->item('site_sms_gateway'));

		if (!empty($mobile) &&
			strlen($mobile) == 12 &&
			substr($mobile, 0, 2) == 91
		) {
			$gateway = in_array($gateway, ['2factor', 'routemobile']) ? $gateway : '2factor';
		}

		if (strtolower($gateway) === 'textlocal') {
			$gateway = '2factor';
		}

		$response 	= [];
		$status 	= false;
		$error 		= '';

		if ($gateway == '2factor') {
			$response 	= self::twoFactor($mobile, $message, $template_id);
			$status		= !empty($response['Status']) && $response['Status'] == 'Success';
			$error		= $response['Details'] ?? '';
		} elseif ($gateway == 'routemobile') {
			$response 	= self::routemobile($mobile, $message, $template_id);
			$explode	= explode('|', $response, 2);
			$status		= !empty($explode[0]) && $explode[0] == 1701;
			$error		= $explode[1] ?? '';
		} else {
			$response 	= self::vonage($mobile, $message);
			$status		= isset($response['status']) && $response['status'] == 0;
			$error		= $response['error_text'] ?? '';
		}

		log_message('KB', 'SMS=>' . print_r($response, 1) . ' Mobile=>' . $mobile . ' Message=>' . $message);

		update_thirdparty_status($gateway, $status, $error);

		return $response;
	}

	// BRIMINDS SMS
	public function bmsms($args) {
		extract($args);

		$exclude = TESTING_MOBILES;

		if (in_array($mobile, $exclude)) {
			return;
		}

		$gateway = 'routemobile';

		if (!empty($mobile) &&
			strlen($mobile) == 12 &&
			substr($mobile, 0, 2) == 91
		) {
			$gateway = in_array($gateway, ['routemobile']) ? $gateway : 'routemobile';
		}

		$output = $response = [];

		$response 	= self::bmroutemobile($mobile, $message, $template_id);
		$output 	= $response;

		log_message('KB', 'SMS=>' . print_r($response, 1) . ' Mobile=>' . $mobile . ' Message=>' . $message);

		return $response;
	}

	private function twoFactor($mobile, $message, $template_id = '') {
		preg_match('/\d{4,6}/ims', $message, $output);

		$otp		= $output[0] ?? '';
		$api_key 	= SMS['2factor']['api_key'];

		if (empty($otp)) return;

		$url = vsprintf('https://2factor.in/API/V1/%s/SMS/%s/%s/%s', [
			$api_key,
			$mobile,
			$otp,
			$template_id,
		]);

		$response = _curl($url, [], 'GET', []);

		log_kb([compact('mobile', 'message', 'output', 'response', 'otp')]);

		return $response;
	}

	private function vonage($mobile, $message) {
		if ((strpos(strtolower($message), 'otp ') !== false) || (strpos(strtolower($message), 'verification code ') !== false)
		) {
			preg_match_all('!\d+!', $message, $matches);

			if (!empty($otp = trim($matches[0][0]))) {
				$response = self::vonage_verify_otp($mobile, $otp, 'BriBooks', 180);
				return $response;
			}

			return;
		}

		$payload = [
			'to'		=> $mobile,
			'text'		=> $message,
			'from'		=> '18334298227',
			'api_key'	=> '',
			'api_secret'=> '',
		];

		$response = _curl('https://rest.nexmo.com/sms/json', $payload, 'POST', [], 'form-data');

		return $response;
	}

	private function routemobile($mobile, $message, $template_id = '') {
		$url = vsprintf('https://sms6.rmlconnect.net:8443/bulksms/bulksms?username=%s&password=%s&type=0&dlr=1&destination=%s&source=%s&message=%s&entityid=%s&tempid=%s', [
			SMS['routemobile']['username'],
			SMS['routemobile']['password'],
			$mobile,
			SMS['routemobile']['source'],
			urlencode($message),
			SMS['routemobile']['entityid'],
			$template_id,
		]);

		$response = _curl($url, [], 'GET', []);

		return $response;
	}

	private function bmroutemobile($mobile, $message, $template_id = '') {
		$url = vsprintf('https://sms6.rmlconnect.net:8443/bulksms/bulksms?username=%s&password=%s&type=0&dlr=1&destination=%s&source=%s&message=%s&entityid=%s&tempid=%s', [
			SMS['bmroutemobile']['username'],
			SMS['bmroutemobile']['password'],
			$mobile,
			SMS['bmroutemobile']['source'],
			urlencode($message),
			SMS['bmroutemobile']['entityid'],
			$template_id,
		]);

		$response = _curl($url, [], 'GET', []);

		return $response;
	}

	private function vonage_verify_otp($mobile, $otp, $brand_name = 'BriBooks', $expiry = 180) {
		if (!empty($mobile) &&
			strlen($mobile) == 12 &&
			substr($mobile, 0, 2) == 91
		) {
			return;
		}

		$payload = [
			'locale'			=> 'en-us',
			'brand'				=> $brand_name ?? 'BriBooks',
			'code_length'		=> 6,
			'channel_timeout'	=> $expiry ?? 180,
			'code'				=> $otp,
			'workflow'			=> [
				[
					'channel'	=> 'sms',
					'to'		=> str_replace('+', '', $mobile)
				]
			]
		];

		$response = _curl('https://api.nexmo.com/v2/verify', $payload, 'POST', ['Authorization: Bearer ' . VONAGE_JWT_TOKEN], 'json');

		if (!empty($request_id = $response['request_id'] ?? 0)) {
			$payload = [
				'code' => $otp
			];

			$response = _curl('https://api.nexmo.com/v2/verify/' . $request_id, $payload, 'POST', ['Authorization: Bearer ' . VONAGE_JWT_TOKEN], 'json');
		}

		return $response;
	}

	public function marketing_sms($mobile, $message, $gateway = '2factor') {
		log_kb(['marketing_sms:: ' => [
			'sms'			=> $message,
			'mobile'		=> $mobile,
			'gateway'		=> $gateway,
		]]);

		$exclude = TESTING_MOBILES;

		if (in_array($mobile, $exclude)) {
			return;
		}

		$gateway = strtolower($gateway);

		$output = $response = [];

		if ($gateway == '2factor') {
			$response 	= self::marketing_two_factor($mobile, $message);
			$output 	= json_decode($response, true);

			isset($output['status']) &&  $output['status'] === 'failure' && log_message('KB', 'SMS=>' . print_r($response, 1) . ' Mobile=>' . $mobile . ' Message=>' . $message);
		}

		return $response;
	}

	private function marketing_two_factor($mobile = '', $message = '') {
		log_kb([compact('mobile', 'message')]);

		if (empty($mobile) || empty($message)) return;

		$api_key = SMS['2factor']['api_key'];
		$payload = vsprintf('module=TRANS_SMS&apikey=%s&to=%s&from=BRBOKS&msg=%s', [
			$api_key,
			$mobile,
			urlencode($message)
		]);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL 			=> 'https://2factor.in/API/R1/',
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> '',
			CURLOPT_MAXREDIRS 		=> 10,
			CURLOPT_TIMEOUT 		=> 0,
			CURLOPT_FOLLOWLOCATION 	=> true,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> 'POST',
			CURLOPT_POSTFIELDS 		=> $payload,
		));

		$response = curl_exec($curl);

		curl_close($curl);

		return $response;
	}
}
