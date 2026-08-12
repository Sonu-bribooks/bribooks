<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait Certificate {
	public function downloadCertificate($mode = 0, $course_id = 0) {
		if (
			time() < strtotime(EVENT_END_DATE) &&
			!in_array($this->session->userdata('user_email'), TESTING_EMAILS)
		) {
			$this->session->set_flashdata('error_message', _li('event_not_finished_yet'));
			redirect(base_url());
		}

		$rank_data = self::getEventRank($course_id);

		log_kb([
			'user_rank' 	=> $rank_data,
			'user_email'	=> $this->session->userdata('user_email'),
		]);

		if (empty($rank_data)) {
			$this->session->set_flashdata('error_message', _li('Certificate_not_found'));
			redirect(base_url());
		}

		if (empty($course_id) || empty($this->course_model->get($course_id)->row_array())) {
			$this->session->set_flashdata('error_message', _li('invalid_course'));
			redirect(base_url());
		}

		$code = $this->session->userdata('user_id') . '/' . (int)$mode . '/' . EVENT_2021[$course_id]['game_code'];
		$file = md5('user_' . $code) . '.png';

		if (!is_file(FCPATH . 'uploads/global_certificate/' . $file)) {
			$course_info = $this->course_model->get($course_id)->row_array();

			$file = $this->tool_model->createGlobalCertificate([
				'date'					=> date('d/m/Y', strtotime(EVENT_START_DATE)),
				'name'					=> $this->session->userdata('name'),
				'score'					=> sprintf(
					'and Scored %s Rank out of %s Students',
					number_format($rank_data['rank']),
					number_format(EVENT_2021_TOTAL)
				),
				'course'				=> $course_info['title'] ?? '',
				'event'					=> EVENT_2021_CERTIFICATE['event'],
				'event_description'		=> EVENT_2021_CERTIFICATE['event_description'],
				'code'					=> $code,
			]);
		}

		$html = '<img
			src="' . FCPATH . 'uploads/global_certificate/' . $file . '"
			style="margin-left:60px;max-width:100%;max-height:99%;"
		/>';

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream('icode_certificate_' . date('Y_m_d_H_i_s', strtotime(EVENT_START_DATE)) . '.pdf');
	}
}
