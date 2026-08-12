<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait CustomThemeAlert
{
	public function customThemeAlert($id) {
		if(empty($id))
			return;

		self::customThemeAlertCron($id);
	}

	public function customThemeAlertCron($id) {
		$this->load->model('book/CustomThemeLog_model', 'custom_theme_log_model');

		if (empty($custom_theme_log_info = $this->custom_theme_log_model->get($id)) || empty($student_info = $this->student_model->get($custom_theme_log_info['user_id']))) {
			return;
		}

		$dir = FCPATH . 'uploads/custom_theme_document/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$author_name = trim($student_info['first_name'] . ' ' . $student_info['last_name']);

		$duration = rand(3, 9);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/custom_theme_document', [], true);

		$html = str_replace(
			[
				'{variable1}',
				'{variable2}',
				'{variable3}',
				'{variable4}',
				'{variable5}',
				'{variable6}',
				'{variable7}'
			],
			[
				date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($custom_theme_log_info['date_modified']))),
				'Copy of the signed Terms & Conditions for usage of My Own Image Module on <a href="https://www.bribooks.com">BriBooks.com</a>',
				$custom_theme_log_info['document_id'],
				date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($custom_theme_log_info['date_modified']))),
				$author_name,
				$custom_theme_log_info['ip_address'],
				date('M d Y, H:i:s A', strtotime(sprintf('+%d seconds', $duration), strtotime($custom_theme_log_info['date_modified'])))
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/custom_theme_document/'.$custom_theme_log_info['document_id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);


		$email = $student_info['email'];

		$subject = 'Copy of the signed Terms & Conditions for usage of My Own Image on BriBooks';

		$content = '<p>Dear '.$author_name.',</p>
		<p>Thank you for accepting the Terms & Conditions for the usage of <b>My Own Image Module</b> on BriBooks.com</p>
		<p>Please find the copy of the signed Terms & Conditions as an attachment in the email.</p><br />
		<p>Regards,</p>
		<p>Publishing Team</p>
		<p><a href="https://www.bribooks.com">BriBooks.com</a></p>';

		$this->alert_model->email(
			$email,
			$subject,
			$content,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			FCPATH . $file
		);
	}
}
