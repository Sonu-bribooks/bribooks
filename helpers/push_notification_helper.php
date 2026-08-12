<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Google\Auth\Credentials\ServiceAccountCredentials;

if (!function_exists('send_webpush_notification')) {
	function send_webpush_notification($to, $notification_data = [], $topic = FALSE) {
		if (!empty($to)) {
			$api_token = _get_fcm_access_token();

			if (empty($api_token)) return;

			$headers = [
				'Authorization: Bearer ' . $api_token,
				'Content-Type: application/json'
			];

			$message = [
				'title'			 	=> $notification_data['title'] ?? 'Notification',
				'body'			  	=> $notification_data['body'] ?? 'Notification',
				'icon'			  	=> '',
				'sound'			 	=> 'mySound',
				'type'			  	=> $notification_data['type'] ?? '',
				'id'   				=> $notification_data['id'] ?? '',
				'image'			 	=> $notification_data['image'] ?? '',
				'url'			 	=> $notification_data['url'] ?? '',
			];

			$payload = [
				'token' 			=> $to,
				'notification'		=> [
					// 'icon'			  	=> '',
					'title'				=> $message['title'] ?? '',
					'body'				=> $message['body'] ?? '',
				],
				'webpush' => [
					'notification'		=> [
						// 'icon'			  	=> '',
						'title'				=> $message['title'] ?? '',
						'body'				=> $message['body'] ?? '',
					],
				],
			];

			if (!empty($message['image'])) {
				$payload['webpush']['notification']['image'] = $message['image'];
			}

			if (!empty($message['url'])) {
				$payload['webpush']['notification']['click_action'] = $message['url'];
			}

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/youbooksi/messages:send');
			curl_setopt($ch,CURLOPT_POST, true);
			curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode(['message' => $payload]));

			$result = curl_exec($ch);

			if ($result === false) {
				log_kb(['send_webpush_notification::error::' => curl_errno($ch)]);
			}

			log_kb(['send_webpush_notification::payload::' => [$payload, $result, $api_token]]);

			curl_close($ch);

			return $result;
		}
	}
}

if (!function_exists('send_android_notification')) {
	function send_android_notification($to, $notification_data = [], $topic = FALSE) {
		if (!empty($to)) {
			$api_token = _get_fcm_access_token();

			if (empty($api_token)) return;

			$headers = [
				'Authorization: Bearer ' . $api_token,
				'Content-Type: application/json'
			];

			$message = [
				'title'			 	=> $notification_data['title'] ?? 'Notification',
				'body'			  	=> $notification_data['body'] ?? 'Notification',
				'icon'			  	=> '',
				'sound'			 	=> 'mySound',
				'type'			  	=> $notification_data['type'] ?? '',
				'id'   				=> $notification_data['id'] ?? '',
				'image'			 	=> $notification_data['image'] ?? '',
				'video'			 	=> $notification_data['video'] ?? '',
				'listing'			=> json_encode($notification_data['data'] ?? []),
			];

			$payload = [
				'token' 			=> $to,
				'data'				=> $message,
				'notification'		=> [
					// 'icon'			  	=> '',
					'title'				=> $message['title'] ?? '',
					'body'				=> $message['body'] ?? '',
				],
			];

			if ($topic) {
				unset($payload['token'], $payload['data']['listing']);

				$payload['to'] = $registration_ids[0] ?? '';
				$payload['data']['action'] = $notification_data['action'] ?? null;
			}

			if (!empty($message['image'])) {
				$payload['notification']['image'] = $message['image'];
			}

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/youbooksi/messages:send');
			curl_setopt($ch,CURLOPT_POST, true);
			curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode(['message' => $payload]));

			$result = curl_exec($ch);

			if ($result === false) {
				log_kb(['send_android_notification::error::' => curl_errno($ch)]);
			}

			log_kb(['send_android_notification::payload::' => $payload]);

			curl_close($ch);

			return $result;
		}
	}
}

if (!function_exists('send_ios_notification')) {
	function send_ios_notification($ios_device_token, $notification_data = []) {
		$live = 1;

		if (is_array($ios_device_token) && !empty($ios_device_token)) {
			$keyfile = ''; # <- Your AuthKey file
			$keyid = ''; # <- Your Key ID
			$teamid = ''; # <- Your Team ID (see Developer Portal)
			$bundleid = ''; # <- Your Bundle ID
			$url =  'https://api.push.apple.com'; // 'https://api.development.push.apple.com';

			$key = openssl_pkey_get_private('file://'. $keyfile);

			$header = ['alg' => 'ES256', 'kid' => $keyid];
			$claims = ['iss' => $teamid, 'iat' => time()];

			$header_encoded = _push_base64($header);
			$claims_encoded = _push_base64($claims);

			$signature = '';
			openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
			$jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

			// only needed for PHP prior to 5.5.24
			if (!defined('CURL_HTTP_VERSION_2_0')) {
				define('CURL_HTTP_VERSION_2_0', 3);
			}

			foreach($ios_device_token as $device_token) {
				$body['aps'] = [
					'alert'				=> [
						'title' 	=> (isset($notification_data['notification_title'])) ? $notification_data['notification_title'] : 'Title',
						'body'  	=> (isset($notification_data['notification_text']) && $notification_data['notification_text']) ? $notification_data['notification_text'] : 'Body'
					],
					'badge'				=> '1',
					'sound'				=> 'default',
					'type'				=> (isset($notification_data['notification_type'])) ? $notification_data['notification_type'] : '',
					'notification_id'   => (isset($notification_data['notification_id'])) ? $notification_data['notification_id'] : '',
					'image'			 	=> (isset($notification_data['notification_img'])) ? $notification_data['notification_img'] : '',
					'video'			 	=> (isset($notification_data['notification_video']) && $notification_data['notification_video']) ? $notification_data['notification_video'] : '',
					'event_id'		 	=> (isset($notification_data['event_id'])) ? $notification_data['event_id'] : '',
					'event_tab_id'	  	=> (isset($notification_data['event_tab_id'])) ? $notification_data['event_tab_id'] : '',
					'listing'		   	=> (isset($notification_data['listing'])) ? $notification_data['listing'] : []
				];

				$ch = curl_init();

				curl_setopt_array($ch, [
					CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_2_0,
					CURLOPT_URL 			=> "$url/3/device/$device_token",
					CURLOPT_PORT			=> 443,
					CURLOPT_HTTPHEADER		=> [
						"apns-topic: {$bundleid}",
						"authorization: bearer $jwt"
					],
					CURLOPT_POST 			=> TRUE,
					CURLOPT_POSTFIELDS 		=> json_encode($body),
					CURLOPT_RETURNTRANSFER	=> TRUE,
					CURLOPT_TIMEOUT			=> 30,
					CURLOPT_HEADER			=> 1
				]);

				$result = curl_exec($ch);

				if ($result === false) {
					log_kb(['send_ios_notification::error::' => curl_errno($ch)]);
				}

				curl_close($ch);

				return $result;
			}
		}
	}
}

if (!function_exists('_push_base64')) {
	function _push_base64($data) {
		return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
	}
}

if (!function_exists('_generate_fcm_access_token')) {
	function _get_fcm_access_token() {
		$CI =& get_instance();

		$CI->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$cache_ttl = ENVIRONMENT === 'production' ? 3000 : 600;
		$cache_key = ENVIRONMENT === 'production' ? 'live_firebase_access_token' : 'test_firebase_access_token';

		$access_token = $CI->cache->get($cache_key);

		if (!empty($access_token)) return $access_token;

		try {
			$credentials_file_path = FCPATH . 'assets/csv/bb_fgfhgfg_65465465_786576576youbooksi-firebase-adminsdk-h347h-287df064cd.json';

			$credentials = new ServiceAccountCredentials(
				['https://www.googleapis.com/auth/firebase.messaging'],
				$credentials_file_path
			);

			$token = $credentials->fetchAuthToken();
			$access_token = $token['access_token'];

			$CI->cache->save($cache_key, $access_token, $cache_ttl);

			return $access_token;
		} catch (\Exception $e) {
			log_kb('Error generating firebase access token: ' . $e->getMessage());
			return null;
		}
	}
}
