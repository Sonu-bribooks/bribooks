<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Zoom {
	public function zoomLink() {
		exit;
		$json = [];

		$this->load->library('Zoom_lib', 'zoom_lib');

		// 1. Get users from zoom
		$users = json_decode($this->zoom_lib->listUsers(), 1);

		// 2. Generate Static data
		$user_id			= $users['users'][0]['id'] ?? '';
		$password 			= mt_rand(1000000000, 9999999999);
		$start_date			= date('Y-m-d H:i');
		$end_date			= date('Y-m-d H:i', strtotime('+1 month'));
		$meeting_topic		= 'Test Topic';
		$agenda				= 'Test Agenda';

		// 3. Add Meeting for enrolment
		$data = [
			'userId'					=> $user_id,
			'topic'			  		=> $meeting_topic,
			'agenda'					=> $agenda,
			'start_date'				=> $start_date, // '2020-02-13 12:16'
			'timezone'				  => 'Asia/Calcutta',
			'password'				  => $password,
			'duration'				  => 40,
			'join_before_host'		  => 1,
			'option_host_video'		 => 1,
			'option_participants_video' => 1,
			'option_mute_participants'  => 0,
			'option_enforce_login'	  => 1,
			'option_auto_recording'	 => 'none',
			'alternative_host_ids'	  => '',
			'recurrence'				=> [
				//'type'						=> 1,
				//'repeat_interval'			=> 1,
				//'weekly_days'				=> '1,7',
				//'monthly_day'				=> 1,
				//'monthly_week'				=> 1,
				//'monthly_week_day'			=> 1,
				//'end_times'					=> 1,
				//'end_date_time'				=> gmdate('Y-m-d\TH:i:s', strtotime($end_date)),
			],
		];

		$json['respose'] = json_decode($this->zoom_lib->createAMeeting($data), 1);


		pr($json);

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function zoom() {
		$link = $this->schedule_model->getScheduleLink([
			'schedule_id' => $this->input->get('schedule_id')
		]);

		preg_match('/(?P<meeting_id>\d+?)\?pwd\=(?P<meeting_password>\w+?)$/', $link, $matches);

		$student_info = $this->student_model->get($this->session->user_id)->row_array();

		$data['schedule_id']		= (int)$this->input->get('schedule_id');
		$data['debug']				= false;
		$data['name']				= $this->session->name;
		$data['email']				= !empty($student_info['email']) ? $student_info['email'] : uniqid() . '@leaplearner.co';
		$data['meeting_id']			= $matches['meeting_id'] ?? '';
		$data['meeting_password']	= $matches['meeting_password'] ?? '';

		$data['action']				= site_url('home/zoom') . '?schedule_id=' . (int)$this->input->get('schedule_id');

		// pr($data);

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/zoom', $data);
	}
}
