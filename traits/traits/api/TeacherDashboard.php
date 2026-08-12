<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait TeacherDashboard {
	public function getTeacherEvents() {
		if (!$this->json) {
			$active = [];

			$this->json['events'] = array_map(function($item) use(&$active) {
				$is_active = strtotime($item['start_date']) < time() && strtotime($item['end_date']) > time();

				$data = [
					'id'			=> $item['event_id'],
					'name'			=> $item['event_name'],
					'start_date'	=> $item['start_date'],
					'end_date'		=> $item['end_date'],
					'active'		=> $is_active,
				];

				if ($is_active) {
					$active = $data;
				}

				return $data;
			}, $this->event_teacher_model->get_all([
				'teacher_id'	=> (int)$this->session->userdata('user_id'),
			])['rows'] ?? []);

			CI_Events::trigger('access_log', [
				'module'	=> 'teacher_dashboard'
			]);

			$this->json['active'] = $active;
		}
	}

	public function getTeacherData() {
		if (!empty($this->input->post('event_id'))) {
			$this->form_validation->set_rules('event_id', _l('event_id'), [
				'trim',
				'required',
				'numeric',
				['event', [$this->validate_model, 'event']]
			]);
			self::_runFormValidation();
		}

		if (!$this->json) {
			$user_id = (int)$this->session->userdata('user_id');

			if (
				$user_id &&
				$user_info = $this->db->get_where('users', [
					'id'		=> $user_id,
					'role_id'	=> 3,
					'status'	=> 1,
				])->row_array()
			) {
				$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

				$this->json['teacher_data'] = $this->schooldashboard_lib->getStudentWiseData(
					$user_id,
					$this->input->post('event_id'),
					3
				);
			} else {
				$this->json['error'] = _l('session_expired');
				$this->json['unauthorized'] = true;
			}
		}
	}

	public function sendTeacherEmailReport() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (
			!$this->json &&
			!empty($user_id = $this->session->userdata('user_id')) &&
			!empty($event_id = $this->input->post('event_id')) &&
			$user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 3,
				'status'	=> 1,
			])->row_array()
		) {
			$this->load->model('common/Cron_model', 'cron_model');
			$this->cron_model->add([
				'code'			=> 'sendEmailReportCron_' . $event_id . '_' . $user_id,
				'action'		=> 'alert_model->sendEmailReportCron',
				'data'			=> [$event_id, $user_id],
				'site_id'		=> 1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
			]);
		}
	}

	public function downloadTeacherReport($event_id = 0) {
		self::_getTeacherReport($event_id, true, $this->session->userdata('user_id'));
	}

	private function _getTeacherReport($event_id = 0, $download = true, $user_id = 0) {
		$user_id = !empty($user_id) ? (int)$user_id : (int)$this->session->userdata('user_id');

		if (
			$user_id && $event_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> $user_id,
				'role_id'	=> 3,
				'status'	=> 1,
			])->row_array()
		) {

			$this->load->library('SchoolDashboard_lib', 'schooldashboard_lib');

			$data = $this->schooldashboard_lib->getGradeWiseData(
				$user_id,
				$event_id,
				3
			);
			$data['event_id'] = $event_id;

			$new_html = '';

			if (in_array($event_id, [NYAF_IN_EVENT_ID, YABWF_EVENT_ID, 14])) {
				$html = $this->load->view('common/report/grade_wise_indian_student_pdf', $data, true);
				$new_data = $this->schooldashboard_lib->getTeacherDashboardReport($user_info['site_id'], $event_id);
				$new_html = $this->load->view('common/report/student_pdf', $new_data, true);
			} else {
				$html = $this->load->view('common/report/grade_wise_student_pdf', $data, true);
			}

			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$file_name = sprintf('uploads/pdfs/%s-%s.pdf', date('Y-m-d'), $event_id);

			if ($download) {
				$dompdf->stream($file_name);
			} else {
				return $dompdf->output();
			}
		}
	}
}
