<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait Certificate {
	public function certificate() {
		$data['page_name'] 		= 'certificate';
		$data['page_title'] 	= _l('certificate');

		$data['certificate']	= false;

		$assessment_code_info 	= $this->lr_assessment_code_model->get($this->session->userdata('quiz_code_id'));

		$assessment_info = $this->lr_assessment_model->get_all([
			'assessment_code_id'	=> $this->session->userdata('quiz_code_id'),
			'user_id'				=> $this->session->userdata('user_id'),
			'sort'					=> 'marks',
			'order'					=> 'DESC',
		])['rows'][0] ?? [];

		if ($assessment_info) {
			$file = md5('user_' . $assessment_code_info['code']) . '.png';

			$data['score']			= $assessment_info['marks'];
			$data['total']			= $assessment_info['total_questions'];

			// unlink(FCPATH . 'uploads/certificate/' . $file);

			if (!is_file(FCPATH . 'uploads/certificate/' . $file)) {
				$program = $assessment_info['category'] . ', Level ' . $assessment_code_info['level'];

				if ($this->session->userdata('role_id') == 3) {
					$program = _l('coding_traning');
				}

				$file = $this->tool_model->createCertificate([
					'program'	=> $program,
					'name'		=> $this->session->userdata('name'),
					'date'		=> formatDate($assessment_info['date_added']),
					'score'		=> $assessment_info['marks'] . ' on Scale of ' . $assessment_info['total_questions'],
					'otp'		=> $assessment_code_info['code'],
					'user_id'	=> $this->session->userdata('user_id'),
				]);
			}

			$data['certificate'] 	= base_url('uploads/certificate/' . $file);
		} else {
			$data['score']			= 0;
			$data['total']			= 10;
			$data['certificate'] 	= base_url('uploads/icode.png');
		}

		$this->load->view('lr/index', $data);
	}

	public function downloadCertificate() {
		$assessment_code_info = $this->lr_assessment_code_model->get($this->session->userdata('quiz_code_id'));

		$file = md5('user_' . $assessment_code_info['code']) . '.png';

		if (is_file(FCPATH . 'uploads/certificate/' . $file)) {
			// $html = '<img src="' . FCPATH . 'uploads/certificate/' . $file . '" />';

			$html = '<img
				src="' . FCPATH . 'uploads/certificate/' . $file . '"
				style="margin-left:60px;max-width:100%;max-height:99%;"
			/>';

			$dompdf = new Dompdf();
			$dompdf->loadHtml($html);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream('certificate.pdf');
		}
	}
}
