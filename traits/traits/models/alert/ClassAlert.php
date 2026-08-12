<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ClassAlert {
	public function classAlert($site_id = 0) {
		// Alert 15m , 1hr, 2hrs
		$results_15m = $results_1hr = $results_2hrs = [];

		// $results_15m = $this->schedule_model->get_all([
		// 	'datetime_start'	=> date('Y-m-d H:i', strtotime('+15 minutes')),
		// 	'site_id'			=> (int)$site_id
		// ])->result_array();

		$results_1hr = $this->schedule_model->get_all([
			'datetime_start'	=> date('Y-m-d H:i', strtotime('+1 hour')),
			'site_id'			=> (int)$site_id
		])->result_array();

		$results_2hrs = $this->schedule_model->get_all([
			'datetime_start'	=> date('Y-m-d H:i', strtotime('+2 hour')),
			'site_id'			=> (int)$site_id
		])->result_array();

		// log_kb(array_merge($results_15m, $results_1hr, $results_2hrs));

		foreach (array_merge($results_15m, $results_1hr, $results_2hrs) as $schedule) {
			// Step 1: Alert the teacher
			//$schedule['mobile'] && self::sms($schedule['mobile'], "");

			// Step 2: Alert the student
			self::classAlertStudents($schedule);
		}
	}

	private function classAlertStudents($schedule_info) {
		$demo_students 	= $this->lead_model->getDemoStudents($schedule_info['id']);
		$results 		= $this->class_model->get_all_students($schedule_info['class_id']);

		if ($schedule_info['is_demo']) {
			if ($schedule_info['mode'] == 'online') {
				$results = $demo_students;
			} else {
				$results = array_unique(array_merge($results, $demo_students));
			}
		}

		if ($schedule_info['mode'] == 'online') {
			$zoom_link = $this->schedule_model->getScheduleLink([
				'schedule_id'	=> $schedule_info['id']
			]);
		}

		foreach ($results as $key => $result) {
			$student_info 	= $this->student_model->get($result)->row_array();

			if (!$student_info) continue;

			// Global configuration
			$this->site_model->initConfig($student_info['site_id'] ?? 0);

			$location 		= $schedule_info['mode'] == 'online' ? _l('online') : '';
			$explode 		= explode(' ', $schedule_info['schedule'], 2);
			$slot 			= array_pop($explode);
			$schedule_date 	= array_pop($explode);

			if ($schedule_info['mode'] == 'offline') {
				$center_info 	= $this->center_model->get($schedule_info['center_id'])->row_array();

				$location		= "{$center_info['name']} {$center_info['city']}";
			}

			// if ($schedule_info['is_demo']) {
			// 	$student_info['mobile'] && self::sms($student_info['mobile'], self::formatMessage('sms_demo_reminder', [
			// 		'student_name'		=> $student_info['first_name'],
			// 		'parent_name'		=> $student_info['parent_name'],
			// 		'course_name'		=> $schedule_info['course'],
			// 		'datetime'			=> $schedule_info['schedule'],
			// 	]));
			// } else {
			// 	$student_info['mobile'] && self::sms($student_info['mobile'], self::formatMessage('sms_class_alert', [
			// 		'student_name'		=> $student_info['first_name'],
			// 		'parent_name'		=> $student_info['parent_name'],
			// 		'course_name'		=> $schedule_info['course'],
			// 		'datetime'			=> $schedule_info['schedule'],
			// 	]));
			// }

			// Share zoom link
			if ($student_info['email']) {
				$time_diff = (new DateTime($schedule_info['schedule']))->diff(new DateTime(date('Y-m-d H:i:s')));

				if ($time_diff->h == 2) {
					$slot_type = _l('2 hours');
				} elseif (($time_diff->s > 0 || $time_diff->i > 0) && $time_diff->h == 1) {
					$slot_type = _l('2 hours');
				} else {
					$slot_type = _l('1 hour');
				}

				$data['title']			= sprintf(_li('iCode Global Hackathon %s: Prep Webinar Invitation!'), date('Y'));
				$data['heading']		= sprintf(_li('iCode Global Hackathon %s: Prep Webinar Invitation!'), date('Y'));
				$data['subheading']		= '';

				if ($schedule_info['is_demo']) {
					$data['content']		= $this->load->view('common/mail/part/class_alert_1', [
						'student_name'		=> "{$student_info['first_name']} {$student_info['last_name']}",
						'slot_type'			=> $slot_type,
					], true);
				} else {
					$data['content']		= $this->load->view('common/mail/part/class_alert_1', [
						'student_name'		=> "{$student_info['first_name']} {$student_info['last_name']}",
						'slot_type'			=> $slot_type,
					], true);
				}

				$data['link']			= site_url(); //$zoom_link;
				$data['link_text']		= _l('go_live');

				$message 				= $this->load->view('common/mail/general', $data, true);

				if ($key === 0) {
					$bcc = [
						$schedule_info['email'],
					];
				} else {
					$bcc = [];
				}

				// usleep(200000);

				self::email(
					$student_info['email'],
					$data['title'],
					$message,
					[],
					$bcc
				);
			}
		}
	}
}
