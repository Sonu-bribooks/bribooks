<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait ExportCertificate {
	public function downloadRankCertificate() {
		$rank_7 = self::_getRankByCourseId(7, true);
		self::_generateRank($rank_7);

		$rank_26 = self::_getRankByCourseId(26, true);
		self::_generateRank($rank_26);

		$rank_27 = self::_getRankByCourseId(27, true);
		self::_generateRank($rank_27);

		$rank_29 = self::_getRankByCourseId(29, true);
		self::_generateRank($rank_29);

		$path = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '_'], EVENT_2021_CERTIFICATE['event']) . '/pdf/';

		self::_downloadZipCertificate($path);
	}

	private function _generateRank($rank_data = []) {
		foreach ($rank_data as $rank => $item) {
			$user_info = $this->db->get_where('users', [
				'email'	=> $item['email']
			])->row_array();

			$file = self::_generateCertificate([
				'user_id'	=> !empty($user_info['id']) ? $user_info['id'] : $item['user_id'],
				'game_id'	=> $item['game_id'],
				'rank'		=> $rank + 1,
				'mode'		=> $item['mode'],
				'name'		=> $item['first_name'] . ' ' . $item['last_name'],
				'course'	=> $item['game'],
			]);

			self::_generatePDFCertificate($file);

			// break;
		}
	}

	private function _generateCertificate($data = []) {
		$path = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '_'], EVENT_2021_CERTIFICATE['event']);

		$code = $data['user_id'] . '/' . (int)$data['mode'] . '/' . $data['game_id'];

		$file = md5('user_' . $code) . '.png';

		if (!is_file(FCPATH . 'uploads/global_certificate/' . $path . '/' . $file)) {
			$file = $this->tool_model->createGlobalCertificate([
				'date'					=> date('d/m/Y', strtotime(EVENT_START_DATE)),
				'name'					=> $data['name'],
				'score'					=> sprintf(
					'and Scored %s Rank out of %s Students',
					number_format($data['rank']),
					number_format(EVENT_2021_TOTAL)
				),
				'course'				=> $data['course'] ?? '',
				'event'					=> EVENT_2021_CERTIFICATE['event'],
				'event_description'		=> EVENT_2021_CERTIFICATE['event_description'],
				'code'					=> $code,
			], $path);
		}

		return $path . '/' . $file;
	}

	private function _generatePDFCertificate($file) {
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

		$path_info = pathinfo($file);

		$dir = FCPATH . 'uploads/global_certificate/' . $path_info['dirname'] . '/pdf/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		file_put_contents(
			$dir . $path_info['filename'] . '.pdf',
			$dompdf->output()
		);
	}

	private function _downloadZipCertificate($path = NULL) {
		$this->load->library('zip');

		$this->zip->read_dir(
			FCPATH . 'uploads/global_certificate/' . $path,
			FALSE,
			FCPATH . 'uploads/global_certificate'
		);
		$this->zip->download($path . '.zip');
	}

	private function _downloadCertificate($mode = 0, $course_id = 0) {
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
				'score'					=> sprintf('and Scored %s Rank out of %s Students', number_format($rank_data['rank']), number_format(500)),
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
