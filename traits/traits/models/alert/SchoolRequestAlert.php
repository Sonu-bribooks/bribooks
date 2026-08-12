<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait SchoolRequestAlert {
	public function approveSchoolRequestCron($id = 0) {
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('school/SchoolInput_model', 'schoolinput_model');

		$lead_info = $this->school_lead_model->get($id);

		if (
			$lead_info &&
			empty($lead_info['site_id']) &&
			!($site_info = $this->site_model->getByCode('NYAFIND2022' . $lead_info['id']))
		) {
			self::_addSite($lead_info);
			self::_schoolRequestApprovedAlert($lead_info['id']);
		}
	}

	public function schoolRequestAlert($id = 0) {
		self::cron($id, 'schoolRequestAlertCron');
	}

	public function schoolRequestAlertCron($id = 0) {
		log_kb(['schoolRequestAlertCron=> '  => $id]);

		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		if ($info = $this->school_lead_model->get($id)) {
			$data['title']		  	= vsprintf(_li('Attention: School Registrations for the National Young Authors Fair are now closed!'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('yaf_acknowledgement', [
				'author_name'	  	=> $info['authorized_person'],
				'name'	  			=> $info['name'],
			]);
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/3/general', $data, true);

			$mobile = $info['mobile'];
			$email = $info['email'];

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}

	public function _schoolRequestApprovedAlert($id = 0) {
		log_kb(['schoolRequestApprovedAlert=> '  => $id]);

		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		if ($info = $this->school_lead_model->get($id)) {
			$data['title']		  	= vsprintf(_li('The application of '.$info['name'].' has been accepted for the National Young Authors Fair'), [
				get_settings('system_name')
			]);
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= self::formatEmailMessage('yaf_request_autoapproval_24hr', [
				'author_name'	  	=> $info['authorized_person'],
				'name'	  			=> $info['name'],
			]);
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/3/general', $data, true);
			$attachment          	= [
				FCPATH . '/assets/backend/sendmail/NYAF_India_School_Communication.pdf',
				FCPATH . '/assets/backend/sendmail/Poster.png'
			];

			$mobile = $info['mobile'];
			$email = $info['email'];

			self::email(
				$email,
				$data['title'],
				$message,
				[],
				[],
				$attachment
			);

			!empty($info['mobile']) && self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> '1096613584379813',
					'parameters'	=> [
						$info['authorized_person'],
						$info['email'],
						$info['name'],
					]
				]
			);
		}
	}

	public function sendEmailReportCron($event_id = false, $user_id = false) {
		log_kb([
			'sendEmailReportCron=> '  => [
				'event_id'	=> $event_id,
				'user_id'	=> $user_id
			]
		]);

		$this->load->model('event/Event_model', 'event_model');

		if (
			($user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 9,
				'status'	=> 1,
			])->row_array()) &&
			($event_info = $this->event_model->get($event_id)) &&
			($site_info = $this->site_model->get($user_info['site_id']))
		) {
			$this->load->library('Common_lib', 'common_lib');

			$data = $this->common_lib->getGradeWiseData($user_id, $event_id);

			$new_html = '';
			if(in_array($event_id, [NYAF_IN_EVENT_ID, YABWF_EVENT_ID, 14])){
				$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/gradeWiseIndianStudentPDF', $data, true);
				$new_data = $this->common_lib->getSchoolDashboardReport($user_info['site_id'], $event_id);
				$new_html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/StudentPDF', $new_data, true);

			}else{
				$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/GradeWiseStudentPDF', $data, true);
			}

			// pr($html, 1);
			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$output = $dompdf->output();

			$attachment = sprintf('uploads/pdfs/event_report_%s_%s_%s.pdf', time(), (int)$user_id, (int)$event_id);

			file_put_contents(FCPATH . $attachment, $output);

			$email = $user_info['email'];

			$subject = 'Daily Report for ' . $user_info['first_name'] . ' - ' . $event_info['name'];

			$content = '<p>Dear '.$site_info['authorized_person'].'!</p>
<p>Your school is doing great in the ' . $event_info['name'] . '.<br />Please check the attached daily report for ' . $user_info['first_name'] . ' here!</p>
<p>We are happy to help you track your progress and wish to see you succeed as a literary leader.<br />In case of queries, please write to us at <a href="mailto:schools@bribooks.com">schools@bribooks.com</a>.</p>
<p>All the Best!</p>
<p>Best Regards,<br />Team BriBooks</p>';

			$this->alert_model->email(
				$email,
				$subject,
				$content,
				[],
				[],
				$attachment
			);
		}
	}

	public function sendSchoolLeadWelcomeMail($lead_id = 0) {
		if (!empty($lead_info = $this->school_lead_model->get($lead_id))) {

			$state_info =  $this->state_model->get($lead_info['state_id']);
			$city_info 	=  $this->city_model->get($lead_info['city_id']);

			$subject 						= "Next Steps for Your Application to the National Young Authors' Fair 2024-25";
			$message						= $this->load->view('common/mail/part/school_lead_request', [
				'authorized_person' 		=> ucwords($lead_info['authorized_person']),
				'school_name' 				=> ucwords($lead_info['name']),
				'state' 					=> $state_info['name'] ?? '',
				'city' 						=> $city_info['name'] ?? '',
				'application_id' 			=> 'NYAF/India/2024-25/' . $lead_info['id']
			], true);

			$email = $lead_info['email'];

			$this->alert_model->email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				[]
			);
		}
	}

	public function sendSchoolLeadVerifyMail($school_id = 0) {
		$this->load->model('school/School_model', 'school_model');

		if (!empty($school_info = $this->school_model->get($school_id))) {

			$state_info =  $this->state_model->get($school_info['state_id']);
			$city_info 	=  $this->city_model->get($school_info['city_id']);

			$subject 						= sprintf("Congratulations! %s Has Been Accepted into the NYAF 2024-25", ucwords($school_info['name']));
			$message						= $this->load->view('common/mail/part/school_lead_verify', [
				'authorized_person' 		=> ucwords($school_info['authorized_person']),
				'school_name' 				=> ucwords($school_info['name']),
				'state' 					=> $state_info['name'] ?? '',
				'city' 						=> $city_info['name'] ?? '',
				'application_id' 			=> 'NYAF/India/2024-25/' . $school_info['id']
			], true);

			$email = $school_info['owner_email'];

			log_kb([
				'sendSchoolLeadVerifyMail' => $school_info,
				'email' => $email,
				'subject' => $subject,
				'message' => $message,
			]);

			$this->alert_model->email(
				$email,
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
				[]
			);
		}
	}
}
