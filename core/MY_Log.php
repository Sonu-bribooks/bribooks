<?php defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'third_party/phpmailer/loader.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class My_Log extends CI_Log {
	protected $_levels = ['ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4, 'API' => 5, 'KB' => 6];

	public function __construct() {
		parent::__construct();
	}

	public function write_log($level, $msg) {
		parent::write_log($level, $msg);
		self::_sendAlert($level, $msg);
	}

	private function _sendAlert($level, $message = '') {
		if (ENVIRONMENT != 'production') return;
		if (empty($message)) return;
		if (strtoupper($level) !== 'ERROR') return;
		if (
			strpos(strtolower($message), 'severity: ') === false &&
			strpos(strtolower($message), 'unable to load the requested') === false &&
			strpos(strtolower($message), 'query error') === false
		) return;

		try {
			$log_file = APPPATH . 'logs/email_alert.php';

			if (is_file($log_file) && (time() - filemtime($log_file)) < 600) return;
			file_put_contents($log_file, date('Y-m-d H:i:s'));

			$mail 		= new PHPMailer(true);

			$to 		= 'abhishek@youbooks.co';
			$subject 	= strtoupper(ENVIRONMENT) . ': CMS ERROR';
			$message 	= '❗' . sprintf('Severe Error:: <br>%s<br>Server::%s<br>Post::%s',
				$message,
				json_encode($_SERVER, JSON_PRETTY_PRINT),
				json_encode($_POST, JSON_PRETTY_PRINT)
			);

			$mail->SMTPDebug 	= SMTP::DEBUG_OFF;
			$mail->Debugoutput 	= APPPATH . 'logs';

			$mail->Host			= EMAIL_ACCOUNTS[EMAIL_SERVICE]['host'];
			$mail->SMTPAuth   	= true;
			$mail->Username   	= EMAIL_ACCOUNTS[EMAIL_SERVICE]['username'];
			$mail->Password   	= EMAIL_ACCOUNTS[EMAIL_SERVICE]['password'];
			$mail->SMTPSecure 	= 'tls';
			$mail->Port	   		= '587';

			$mail->isSMTP();

			//Recipients
			$mail->setFrom(EMAIL_ACCOUNTS[EMAIL_SERVICE]['sender'], 'BriBooks');
			$mail->addAddress($to);
			$mail->addReplyTo('support@bribooks.com', 'BriBooks');

			$mail->addCC('pratyush@bribooks.com');
			// $mail->addBCC($item);

			//Content
			$mail->isHTML(true);
			$mail->Subject 	= $subject;
			$mail->Body		= $message;
			$mail->AltBody 	= $subject;

			$mail->send();

			return true;
		} catch (Exception $e) {
		}
	}
}
