<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait CustomCoverAlert {
	public function customCoverAlert($id = 0) {
		if (empty($id)) return;

		self::customCoverAlertCron($id);
	}

	public function customCoverAlertCron($id) {
		$this->load->model('book/CustomCoverLog_model', 'custom_cover_log_model');
		$this->load->model('common/MessageTemplate_model', 'message_template_model');

		if (
			empty($info = $this->custom_cover_log_model->get($id)) ||
			empty($user_info = $this->user_model->get($info['user_id']))
		) {
			return;
		}

		$author_name 			= trim($user_info['first_name'] . ' ' . $user_info['last_name']);
		$duration 				= rand(3, 9);
		$site_id 				= $user_info['site_id'] ?? 1;

		if(!empty($this->message_template_model->getByCode('tnc_user_image', $site_id))) {
			$data['document_date'] 	= date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($info['date_modified'])));
			$data['document_title']	= _li('Copy of the signed Terms & Conditions for usage of Custom Cover on <a href="https://www.bribooks.com">BriBooks.com</a>');
			$data['document_id']	= $info['document_id'];
			$data['created_date']	= date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($custom_theme_log_info['date_modified'])));
			$data['signed_by'] 		= $author_name;
			$data['ip_address'] 	= $info['ip_address'];
			$data['signed_date'] 	= date('M d Y, H:i:s A', strtotime(sprintf('+%d seconds', $duration), strtotime($info['date_modified'])));

			CI_Events::trigger('tnc_user_image', [
				'user_id'	=> $user_info['id'],
				'data' 		=> $data
			]);

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('tnc_user_image_%d', (int)$user_info['id'])
			]);
		} else {
			$dir = FCPATH . 'uploads/custom_cover_document/';

			if (!is_dir($dir)) {
				mkdir($dir, 0777, TRUE);
				chmod($dir, 0777);
				@touch($dir . '/' . 'index.html');
			}

			$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/custom_cover_document', [], true);

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
					date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($info['date_modified']))),
					'Copy of the signed Terms & Conditions for usage of Custom Cover on <a href="https://www.bribooks.com">BriBooks.com</a>',
					$info['document_id'],
					date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($info['date_modified']))),
					$author_name,
					$info['ip_address'],
					date('M d Y, H:i:s A', strtotime(sprintf('+%d seconds', $duration), strtotime($info['date_modified'])))
				],
				$html
			);

			// $dompdf = new Dompdf();
			// $dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			// $dompdf->set_option('isJavascriptEnabled', true);
			// $dompdf->set_option('isRemoteEnabled', true);
			// $dompdf->set_option('isHtml5ParserEnabled', true);
			// $dompdf->setPaper('A4', 'potrait');
			// $dompdf->render();

			// $file = 'uploads/custom_cover_document/' . $info['document_id'] . '.pdf';

			// $output = $dompdf->output();

			// file_put_contents(FCPATH . $file, $output);

			$subject = _li('Copy of the signed Terms & Conditions for usage of Custom Cover on BriBooks');

			$content = $this->load->view('common/mail/part/custom_cover', [
				'author_name' => $author_name
			], true);

			self::email(
				$user_info['email'],
				$subject,
				$content,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				// FCPATH . $file
				[]
			);
		}
	}
}
