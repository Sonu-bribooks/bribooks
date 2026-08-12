<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait GameApi {
	public function getGameList() {
		$json['code']	= 0;
		$json['data'][] = [
			'gameId' 	=> WEBINAR_GAME[(int)$this->input->post('course_id')]['game_code'],
			'gameMode' 	=> WEBINAR_GAME[(int)$this->input->post('course_id')]['game_mode'],
			'gameLevel' => 1
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function getGameListOld() {
		$this->load->helper('cookie');

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				get_cookie('USERID'),
				get_cookie('SESSION'),
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/getGameList', [
			'mode'			=> $this->input->post('mode'),
			'level'			=> $this->input->post('level'),
		])->rows();

		// log_message('KB', 'getGameList:: ' . print_r($result, 1));

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	public function enterGame() {
		$user_info = $this->user_model->get($this->session->userdata('user_id'));

		$token_info = self::eventLogin([
			'event_code'	=> WEBINAR_GAME[(int)$this->input->post('course_id')]['event_code'],
			'email'			=> $user_info['email'],
			'password'		=> $user_info['password'],
		], false);

		$result = $this->icode_lib->setEndpoint(
			in_array($user_info['email'], TESTING_EMAILS)
			? $this->config->item('api_icode_new')
			: $this->config->item('api_icode')
		)->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				$token_info['event_uid'],
				$token_info['event_token'],
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->get_data('/user/chooseMode?mode=' . (int)$this->input->post('mode'), [
		])->rows();

		log_kb(['mode' => $result]);

		$result = $this->icode_lib->setEndpoint(
			in_array($user_info['email'], TESTING_EMAILS)
			? $this->config->item('api_icode_new')
			: $this->config->item('api_icode')
		)->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				$token_info['event_uid'],
				$token_info['event_token'],
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/enterGame', [
			'mode'			=> (int)$this->input->post('mode'),
			'gameId'		=> (int)$this->input->post('gameId'),
		])->rows();

		log_kb(['enterGame' => $result]);

		$result['token'] = $token_info['event_token'];
		$result['uid'] = $token_info['event_uid'];

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	public function enterGameOld() {
		$this->load->helper('cookie');

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				get_cookie('USERID'),
				get_cookie('SESSION'),
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/enterGame', [
			'mode'			=> $this->input->post('mode'),
			'gameId'		=> $this->input->post('gameId'),
		])->rows();

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	public function getCheckPointer() {
		$this->load->helper('cookie');

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				get_cookie('USERID'),
				get_cookie('SESSION'),
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('checkPointer/getCheckPointer', $this->input->post())->rows();

		$redirect = true;

		$enrols = $this->enrol_model->getAll([
			'user_id'		=> $this->session->userdata('user_id'),
			'status'		=> 1,
			'parent_name'	=> $this->session->userdata('parent_name'),
			'site_id'		=> $this->config->item('site_id')
		]);

		// log_message('KB', 'getCheckPointer:: ' . print_r([
		// 	'results' 	=> $result,
		// 	'enrols' 	=> $enrols,
		// ], 1));

		if ($this->input->post('mode') == 1) {
			foreach ($enrols as $enrol) {
				if (strpos(strtolower($enrol['course']), 'python') !== false) {
					if ($enrol['emi_type'] === 'premium') {
						$redirect 	= false;
					} elseif ($enrol['emi_type'] === 'base') {
						if ($result['data']['stageId'] <= BASE_LEVEL) {
							$redirect = false;
						}
					} else {
						$enrol_info = $enrol;
					}
				}
			}
		} elseif ($this->input->post('mode') == 0) {
			foreach ($enrols as $enrol) {
				if (strpos(strtolower($enrol['course']), 'blockly') !== false) {
					if ($enrol['emi_type'] === 'premium') {
						$redirect 	= false;
					} elseif ($enrol['emi_type'] === 'base') {
						if ($result['data']['stageId'] <= BASE_LEVEL) {
							$redirect = false;
						}
					} else {
						$enrol_info = $enrol;
					}
				}
			}
		}

		if ($this->config->item('site_country_code') != 'IN') {
			if ($result['data']['stageId'] <= BASE_LEVEL) {
				$redirect = false;
			}
		} else {
			if ($result['data']['stageId'] <= FREE_LEVEL) {
				$redirect = false;
			}
		}

		if (!$redirect) {
			$this->json = $result;
		} else {
			$amount = EMI_CHARGE['premium'];

			$users = $this->user_model->get($this->session->userdata('user_id'));

			$name = $users['first_name'] . " " . $users['last_name'];
			$parent_name = $users['parent_name'];

			$message = "Dear $parent_name<br/>$name has done brilliantly well so far and has the potential to be ranked amongst top 1% global kids Coders.<br/>Please upgrade to the Premium plan, to unlock the learning potential of you child.<br/><br/>Happy Learning !<br/>Team ICode";

			$this->json['message'] 	= $message;
			$this->json['redirect'] = site_url('home/renewal/' . $this->enrol_model->generatePaymentLink($enrol_info['id'] ?? 0));
		}

		$this->setGameOutput();
	}

	public function queryUserGameInfo() {
		$this->load->helper('cookie');

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				get_cookie('USERID'),
				get_cookie('SESSION'),
			]),
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('game/queryUserGameInfo', $this->input->post())->rows();

		$redirect = false;

		if (!$redirect) {
			$this->json = $result;
		} else {
			$this->json['redirect'] = site_url('home/parent_dashboard');
		}

		$this->setGameOutput();
	}

	public function game(...$endpoint) {
		$this->load->helper('cookie');

		$data = $this->input->post();
		$json = false;

		if (strpos($this->input->get_request_header('Content-Type'), 'application/json') !== false) {
			$data = $this->input->raw_input_stream;
			$json = true;
		}

		$result = $this->icode_lib->setEndpoint($this->config->item('api_icode'))->setJson($json)->setHeader([
			'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
				get_cookie('USERID'),
				get_cookie('SESSION'),
			]),
			'Content-Type'	=> $this->input->get_request_header('Content-Type'), // 'application/x-www-form-urlencoded',
		])->insert(implode('/', $endpoint), $data)->rows();

		$redirect = false;

		if (!$redirect) {
			$this->json = $result;
		} else {
			$this->json['redirect'] = site_url('home/parent_dashboard');
		}

		$this->setGameOutput();
	}
}
