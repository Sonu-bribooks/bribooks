<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventLogin {
	private function eventRegister($data = []) {
		$course_id = 0;

		if (empty($data['course_id'])) {
			if (!($enrols = $this->crud_model->enrol_history_by_user_id($this->session->userdata('user_id'))->result_array())) return;

			foreach ($enrols as $enrol) {
				if (($enrol['emi_type'] === 'premium' || $enrol['emi_type'] === 'base') && $enrol['status']) {
					$course_id = $enrol['course_id'];
				}
			}

			if (!$course_id) return;
		} else {
			$course_id = (int)$data['course_id'];
		}

		log_kb([
			'enrol' 			=> $enrols,
			'data'				=> $data,
		]);

		$data['game_id'] = EVENT_2021[$course_id]['game_code'];

		$result = $this->icode_lib->setEndpoint(
			in_array($data['email'], TESTING_EMAILS)
			? $this->config->item('event_api_icode_new')
			: $this->config->item('event_api_icode')
		)->setHeader([
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/userRegister', [
			'firstName'		=> $data['first_name'],
			'lastName'		=> $data['last_name'],
			'email'			=> $data['email'],
			'mobile'		=> $data['mobile'],
			'grade'			=> $data['grade'],
			'schoolName'	=> $this->config->item('site_name'),
			'password'		=> md5($data['password']),
			'countryCode'	=> str_replace('+', '', $this->config->item('site_tel_code')),
			'gameId'		=> $data['game_id'],
		])->rows();

		log_kb([
			'event_register' 	=> $result,
			'data'				=> $data,
		]);
	}

	private function eventLogin($data = [], $redirect = true) {
		$result = $this->icode_lib->setEndpoint(
			in_array($data['email'], TESTING_EMAILS)
			? $this->config->item('event_api_icode_new')
			: $this->config->item('event_api_icode')
		)->setHeader([
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/channel/userLogin', [
			'eventCode'		=> $data['event_code'],
			'email'			=> $data['email'],
			'password'		=> md5($data['password']),
		]);

		$cookie1 = http_parse_cookie($result->resHeaders()['Set-Cookie'][0] ?? '');
		$cookie2 = http_parse_cookie($result->resHeaders()['Set-Cookie'][1] ?? '');

		$url = sprintf(
			EVENT_URL,
			$cookie2['SESSION'],
			$cookie1['USERID'],
			$data['event_code']
		);

		$login_info = $result->rows();

		log_kb([
			'event_login' 	=> $login_info,
			'data'			=> $data,
			'session'		=> [
				$cookie2['SESSION'],
				$cookie1['USERID'],
			]
		]);

		if ($login_info['code'] == 46) {
			$row = $this->user_model->get($this->session->userdata('user_id'));

			// register user again
			self::eventRegister([
				'first_name'	=> $row->first_name,
				'last_name'		=> $row->last_name ? $row->last_name : $row->first_name,
				'email'			=> $row->email,
				'mobile'		=> $row->mobile,
				'grade'			=> (int)$row->grade,
				'password'		=> $row->password,
			]);
		}

		if (!empty($cookie2['SESSION']) && !empty($cookie1['USERID'])) {
			$this->input->set_cookie('event_token', $cookie2['SESSION'], 4 * 3600);
			$this->input->set_cookie('event_uid', $cookie1['USERID'], 4 * 3600);

			// New game token
			$this->input->set_cookie('SESSION', $cookie2['SESSION'], 4 * 3600, GAME_COOKIE_DOMAIN);
			$this->input->set_cookie('USERID', $cookie1['USERID'], 4 * 3600, GAME_COOKIE_DOMAIN);
			$this->input->set_cookie('lang', 'en', 4 * 3600, GAME_COOKIE_DOMAIN);
			$this->input->set_cookie('zhOren', 'en', 4 * 3600, GAME_COOKIE_DOMAIN);

			if ($redirect) {
				redirect($url);
			} else {
				return [
					'event_token'	=> $cookie2['SESSION'],
					'event_uid'		=> $cookie1['USERID'],
				];
			}
		} else {
			if ($redirect) {
				$this->session->set_flashdata('error_message', _li('Please retry once more'));

				redirect(base_url());
			} else {
				return [];
			}
		}
	}

	public function joinRace($course_id = 0) {
		if (isset(EVENT_2021[$course_id])) {
			$user_info = $this->user_model->get($this->session->userdata('user_id'));

			self::eventLogin([
				'event_code'	=> EVENT_2021[$course_id]['event_code'],
				'game_code'		=> EVENT_2021[$course_id]['game_code'],
				'email'			=> $user_info['email'],
				'password'		=> $user_info['password'],
			]);
		} else {
			redirect(base_url());
		}
	}

	private function getEventRank($course_id = 0, $all = false) {
		$user_info = $this->user_model->get($this->session->userdata('user_id'));

		if ($this->input->cookie('event_uid') && $this->input->cookie('event_token')) {
			$token_info = [
				'event_uid'		=> (int)$this->input->cookie('event_uid'),
				'event_token'	=> $this->input->cookie('event_token'),
			];
		} else {
			$token_info = self::eventLogin([
				'event_code'	=> EVENT_2021[$course_id]['event_code'],
				'game_code'		=> EVENT_2021[$course_id]['game_code'],
				'email'			=> $user_info['email'],
				'password'		=> $user_info['password'],
			], false);
		}

		$results = $this->icode_lib->setEndpoint($this->config->item('event_api_icode'))->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				$token_info['event_uid'],
				$token_info['event_token'],
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->get_data(vsprintf('user/queryUserRank?pageStart=1&pageSize=1000&gameId=%s&type=0&rankType=0&eventCode=%s', [
			EVENT_2021[$course_id]['game_code'],
			EVENT_2021[$course_id]['event_code'],
		]), [])->rows();

		if ($results['code'] != 0) {
			$this->input->set_cookie('event_uid', '', 4 * 3600);
			$this->input->set_cookie('event_token', '', 4 * 3600);
		}

		// log_kb([
		// 	'event_rank' 	=> $result,
		// 	'session'		=> [
		// 		$this->input->cookie('event_uid'),
		// 		$this->input->cookie('event_token'),
		// 	]
		// ]);

		$user_rank = [];

		foreach ($results['data'] ?? [] as $key => $item) {
			if ($item['userId'] == $token_info['event_uid']) {
				$user_rank = $item;
				break;
			}
		}

		// !$user_rank && log_kb([
		// 	'user_rank' 	=> $user_rank,
		// 	'event_ranks' 	=> $results,
		// 	'session'		=> [
		// 		$this->input->cookie('event_uid'),
		// 		$this->input->cookie('event_token'),
		// 	],
		// 	'token_info'	=> $token_info,
		// ]);

		return $all ? $results : $user_rank;
	}

	public function getEventUrl() {
		$json = [];

		if ($this->input->post('course_id') && ($course_info = $this->course_model->get((int)$this->input->post('course_id'))->row_array())) {
			if (time() > strtotime(EVENT_START_DATE) && time() < strtotime(EVENT_END_DATE)) {
				$json['event_url'] = base_url('home/joinRace/' . $course_info['id']);
			}

			if (
				time() > strtotime(EVENT_ME_START_DATE) &&
				time() < strtotime(EVENT_ME_END_DATE) &&
				in_array($this->session->userdata('user_email'), EVENT_ME_EMAILS)
			) {
				$json['event_url'] = base_url('home/joinRace/' . $course_info['id']);
			}

			if (time() > strtotime(EVENT_END_DATE)) {
				$json['certificate_url'] = base_url('home/downloadCertificate/'. (strpos(mb_strtolower(trim($course_info['title'])), 'python') !== false ? 1 : 0) . '/' . $course_info['id']);
			}
		} else {
			$json['error'] = _l('inavlid_course');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
