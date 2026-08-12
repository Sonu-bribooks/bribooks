<?php
use \Firebase\JWT\JWT;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

final class Zoom_lib {
	private $zoom_api_key 		= NULL;
	private $zoom_api_secret	= NULL;
	private $api_url 			= NULL;

	public function __construct() {
		$this->CI =& get_instance();
		$this->config = $this->CI->config;

		$this->api_url			= 'https://api.zoom.us/v2/';
		$this->zoom_api_key 	= ZOOM_APIS[strtolower($this->config->item('site_country_code'))]['key'] ?? ZOOM_API_KEY;
		$this->zoom_api_secret 	= ZOOM_APIS[strtolower($this->config->item('site_country_code'))]['secret'] ?? ZOOM_API_SECRET;
	}

	private function _execute($endpoint, $data, $request = 'GET') {
		$headers = [
			'Authorization' => 'Bearer ' . self::_generateToken(),
			'Content-Type'  => 'application/json'
		];

		if (in_array($request, ['GET', 'DELETE', 'PATCH'])) {
			return self::curl($this->api_url . $endpoint, $data, $request, $headers);
		} else {
			return self::curl($this->api_url . $endpoint, $data, 'POST', $headers);
		}
	}

	private function _generateToken() {
		return JWT::encode([
			'iss' => $this->zoom_api_key,
			'exp' => time() + 60 //60 seconds as suggested
		], $this->zoom_api_secret);
	}

	public function createAUser($data = []) {
		$post_data['action']    = $data['action'];
		$post_data['user_info'] = [
			'email'      => $data['email'],
			'type'       => $data['type'],
			'first_name' => $data['first_name'],
			'last_name'  => $data['last_name']
		];

		return self::_execute('users', $post_data, 'POST');
	}

	public function listUsers() {
		$post_data['page_size'] = 300;

		return self::_execute('users', $post_data, 'GET');
	}

	public function getUserInfo($user_id) {
		return self::_execute('users/' . $user_id, []);
	}

	public function deleteAUser($userid) {
		$post_data['id'] = $userid;

		return self::_execute('users/' . $userid, false, 'DELETE');
	}

	public function listMeetings($host_id) {
		$post_data['page_size'] = 300;

		return self::_execute('users/' . $host_id . '/meetings', $post_data, 'GET');
	}

	public function createAMeeting($data = []) {
		$post_time  = $data['start_date'];
		$start_time = gmdate('Y-m-d\TH:i:s\Z', strtotime($post_time));
		// $start_time = date('Y-m-d\TH:i:s\Z', strtotime($post_time));

		if (!empty($data['alternative_host_ids'])) {
			if (count($data['alternative_host_ids']) > 1) {
				$alternative_host_ids = implode(',', $data['alternative_host_ids']);
			} else {
				$alternative_host_ids = $data['alternative_host_ids'][0];
			}
		}

		if (!empty($data['timezone'])) {
			$post_data['timezone']   	= $data['timezone'];
		}

		$post_data['topic']      		= $data['topic'];
		$post_data['agenda']     		= !empty($data['agenda'] ) ? $data['agenda'] : "";
		$post_data['type']       		= !empty($data['type']) ? $data['type'] : 2; //Scheduled
		$post_data['schedule_for']  	= !empty($data['schedule_for']) ? $data['schedule_for'] : '';
		$post_data['start_time'] 		= $start_time;
		$post_data['password']   		= !empty($data['password']) ? $data['password'] : "";
		$post_data['duration']   		= !empty($data['duration']) ? $data['duration'] : 60;
		$post_data['settings']   		= [
			'waiting_room'  	=> !empty($data['waiting_room']) ? true : false,
			'join_before_host'  => !empty($data['join_before_host']) ? true : false,
			'host_video'        => !empty($data['option_host_video']) ? true : false,
			'participant_video' => !empty($data['option_participants_video']) ? true : false,
			'mute_upon_entry'   => !empty($data['option_mute_participants']) ? true : false,
			'enforce_login'     => !empty($data['option_enforce_login']) ? true : false,
			'auto_recording'    => !empty($data['option_auto_recording']) ? $data['option_auto_recording'] : 'none',
			'alternative_hosts' => isset($alternative_host_ids) ? $alternative_host_ids : "",
			'approval_type'		=> 0,
		];

		$post_data['registrants_email_notification'] = !empty($data['registrants_email_notification']) ? true : false;

		!empty($data['recurrence']) && ($post_data['recurrence'] = $data['recurrence']);

		log_kb('Adding meeting::' . print_r($post_data, 1));

		return self::_execute('users/' . $data['userId'] . '/meetings', $post_data, 'POST');
	}

	public function updateMeetingInfo($data = []) {
		$post_time  = $data['start_date'];
		$start_time = gmdate('Y-m-d\TH:i:s\Z', strtotime($post_time));
		// $start_time = date('Y-m-d\TH:i:s\Z', strtotime($post_time));

		if (!empty( $data['alternative_host_ids'])) {
			if (count($data['alternative_host_ids']) > 1) {
				$alternative_host_ids = implode(',', $data['alternative_host_ids']);
			} else {
				$alternative_host_ids = $data['alternative_host_ids'][0];
			}
		}

		$post_data['topic']      		= $data['topic'];
		$post_data['agenda']    		= !empty($data['agenda']) ? $data['agenda'] : "";
		$post_data['type']       		= !empty($data['type']) ? $data['type'] : 2; //Scheduled
		$post_data['schedule_for']  	= !empty($data['schedule_for']) ? $data['schedule_for'] : '';
		$post_data['start_time'] 		= $start_time;
		$post_data['timezone']   		= $data['timezone'];
		$post_data['password']   		= !empty($data['password']) ? $data['password'] : "";
		$post_data['duration']   		= !empty($data['duration']) ? $data['duration'] : 60;
		$post_data['settings']   		= [
			'waiting_room'  	=> !empty($data['waiting_room']) ? true : false,
			'join_before_host'  => !empty($data['join_before_host']) ? true : false,
			'host_video'        => !empty($data['option_host_video']) ? true : false,
			'participant_video' => !empty($data['option_participants_video']) ? true : false,
			'mute_upon_entry'   => !empty($data['option_mute_participants']) ? true : false,
			'enforce_login'     => !empty($data['option_enforce_login']) ? true : false,
			'auto_recording'    => !empty($data['option_auto_recording']) ? $data['option_auto_recording'] : 'none',
			'alternative_hosts' => isset($alternative_host_ids) ? $alternative_host_ids : "",
			'approval_type'		=> 0,
		];

		$post_data['registrants_email_notification'] = !empty($data['registrants_email_notification']) ? true : false;

		!empty($data['recurrence']) && ($post_data['recurrence'] = $data['recurrence']);

		return self::_execute('meetings/' . $data['meeting_id'], $post_data, 'PATCH');
	}

	public function getMeetingInfo($id) {
		return self::_execute('meetings/' . $id, [], 'GET');
	}

	public function deleteAMeeting($meeting_id) {
		return self::_execute('meetings/' . $meeting_id, [], 'DELETE');
	}

	public function getDailyReport($month, $year) {
		$post_data['year']  = $year;
		$post_data['month'] = $month;

		return self::_execute('report/daily', $post_data, 'GET');
	}

	public function getAccountReport($zoom_account_from, $zoom_account_to) {
		$post_data['from']      = $zoom_account_from;
		$post_data['to']        = $zoom_account_to;
		$post_data['page_size'] = 300;

		return self::_execute('report/users', $post_data, 'GET');
	}

	public function registerWebinarParticipants($webinar_id, $first_name, $last_name, $email) {
		$post_data['first_name'] = $first_name;
		$post_data['last_name']  = $last_name;
		$post_data['email']      = $email;

		return self::_execute('webinars/' . $webinar_id . '/registrants', $post_data, 'POST');
	}

	public function listWebinar($userId) {
		$post_data['page_size'] = 300;

		return self::_execute('users/' . $userId . '/webinars', $post_data, 'GET');
	}

	public function listWebinarParticipants($webinar_id) {
		$post_data['page_size'] = 300;

		return self::_execute('webinars/' . $webinar_id . '/registrants', $post_data, 'GET');
	}

	public function recordingsByMeeting($meeting_id) {
		return self::_execute('meetings/' . $meeting_id . '/recordings', false, 'GET');
	}

	public function listRecording($host_id, $data = []) {
		$from     = date('Y-m-d', strtotime('-1 year', time()));
		$to       = date('Y-m-d');

		$post_data['from'] = !empty($data['from']) ? $data['from'] : $from;
		$post_data['to']   = !empty($data['to']) ? $data['to'] : $to;

		return self::_execute('users/' . $host_id . '/recordings', $post_data, 'GET');
	}

	private function curl($endpoint, $data = NULL, $type = 'GET', $headers = []) {
		$ch = curl_init();

		if ($type == 'GET' && $data) {
			$endpoint .= '?' . http_build_query($data);
		}

		$option[CURLOPT_URL] = $endpoint;

		if ($data && ($type == 'POST' || $type == 'PATCH')) {
			$option[CURLOPT_POSTFIELDS] = json_encode($data);
		}

		if ($type == 'GET') {
			$option[CURLOPT_CUSTOMREQUEST] = 'GET';
		} elseif ($type == 'POST') {
			$option[CURLOPT_CUSTOMREQUEST] = 'POST';
		} elseif ($type == 'PATCH') {
			$option[CURLOPT_CUSTOMREQUEST] = 'PATCH';
		}

		if ($headers) {
			$_headers = [];

			foreach ($headers as $k => $v) {
				$_headers[] = $k . ': ' . trim($v);
			}

			$option[CURLOPT_HTTPHEADER] = $_headers;
		}

		curl_setopt_array($ch, [
			CURLOPT_HEADER		 	=> 0,
			CURLOPT_SSL_VERIFYPEER 	=> 0,
			CURLOPT_RETURNTRANSFER 	=> 1,
			CURLOPT_FOLLOWLOCATION 	=> 1,
			CURLOPT_FORBID_REUSE   	=> 1,
			CURLOPT_FRESH_CONNECT  	=> 1,
			CURLOPT_CONNECTTIMEOUT 	=> 10,
			CURLOPT_TIMEOUT			=> 20,
		] + $option);

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;
	}
}
