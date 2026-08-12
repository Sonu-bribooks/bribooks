<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait WebinarSchedule {
	public function ajax_webinar_schedule($type = 'upcoming', $time_offset = 0) {
		$json = [];

		$time_offset = -$time_offset;

		if ($this->session->userdata('user_id')) {
			if ($type === 'event') {
				$schedules = $this->schedule_model->get_all([
					'student_id'	=> (int)$this->session->userdata('user_id'),
					'sort'			=> 'schedule',
					'order'			=> 'ASC',
				])->result_array();

				foreach ($schedules as $schedule) {
					$json[] = self::_formatEvents($schedule, $time_offset);
				}
			} else {
				$schedules = $this->schedule_model->get_all([
					'student_id'	=> (int)$this->session->userdata('user_id'),
					'sort'			=> 'schedule',
					'order'			=> 'ASC',
					'limit'			=> 1,
					'date_start'	=> date('Y-m-d H:i:s'),
				])->result_array();

				$upcoming_schedule = array_pop($schedules);

				$json['upcoming_schedule'] = !empty($upcoming_schedule['schedule'])
					? gmdate('Y-m-d H:i:s', strtotime($upcoming_schedule['schedule']))
					: '';
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($json));
	}

	private function _formatEvents($schedule, $time_offset = 0) {
		$start = gmdate('Y-m-d H:i:s',  strtotime("+{$time_offset} minutes", strtotime($schedule['schedule'])));
		$class_time = $time_offset + 30;
		$end = gmdate('Y-m-d H:i:s', strtotime("+{$class_time} minutes", strtotime($schedule['schedule'])));

		return [
			'id'				=> $schedule['id'],
			'class_id'			=> $schedule['class_id'],
			'title'				=> $schedule['course'],
			'start'				=> $start,
			'end'				=> $end,
			'link'				=> site_url(),
			'slot'				=> gmdate('H:i:s', strtotime("+{$time_offset} minutes", strtotime($schedule['schedule']))),
			'className'			=> 'bg-info',
			'description'		=> "{$schedule['class']}<br>{$start} {$end}",
		];
	}
}
